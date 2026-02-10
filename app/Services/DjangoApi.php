<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class DjangoApi
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        // Priority: Session > Cache (persistent) > ENV > fallback
        // Cache persists across sessions, session is per-login
        $this->baseUrl = rtrim(
            Session::get('django_base_url')
            ?: Cache::get('django_base_url')
            ?: env('DJANGO_BASE_URL')
            ?: 'http://127.0.0.1:8000',
            '/'
        );
        $this->apiKey = config('django.api_key', env('DJANGO_API_KEY'));

        $headers = [
            'Accept' => 'application/json',
            'X-User-Email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
            'X-User-Name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
            'X-User-Role' => Session::get('user_role', 'org_viewer'),
            'X-Organization-Id' => Session::get('organization_id'),
        ];
        if ($this->apiKey) {
            $headers['Authorization'] = "Key {$this->apiKey}";
        }

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => config('django.timeout', 10),
            'headers' => $headers,
            'verify' => false, // Disable SSL verification for development (ngrok, etc.)
        ]);
    }

    /**
     * Get the Django base URL for use by controllers that need to make direct requests
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get headers with current session data
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'X-User-Email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
            'X-User-Name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
            'X-User-Role' => Session::get('user_role', 'org_viewer'),
            'X-Organization-Id' => Session::get('organization_id'),
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = "Key {$this->apiKey}";
        }

        return $headers;
    }

    protected function get(string $path): array
    {
        try {
            $options = ['headers' => $this->getHeaders()];
            $resp = $this->client->get($path, $options);
            return json_decode((string) $resp->getBody(), true) ?: [];
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e);
        }
    }

    protected function send(string $method, string $path, array $payload): array
    {
        \Log::info("DJANGO_API: {$method} {$path} - Payload: " . json_encode($payload));
        try {
            $options = [
                'json' => $payload,
                'headers' => $this->getHeaders()
            ];
            $resp = $this->client->request($method, $path, $options);
            $body = (string) $resp->getBody();
            \Log::info("DJANGO_API: Response: " . $body);
            return json_decode($body, true) ?: [];
        } catch (GuzzleException $e) {
            \Log::error("DJANGO_API: Error: " . $e->getMessage());
            $error = $this->handleGuzzleException($e);
            $error['payload'] = Arr::except($payload, []);
            return $error;
        }
    }

    protected function handleGuzzleException(GuzzleException $e): array
    {
        $code = $e->getCode();

        if ($code === 403) {
            // Try to extract detail from response
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
                $json = json_decode($body, true);
                if (!empty($json['detail'])) {
                    return ['error' => "Permission Denied: " . $json['detail']];
                }
            }
            return ['error' => 'Permission Denied: You do not have permission to perform this action.'];
        }

        // For other errors, try to get a clean message if possible
        if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
            $body = (string) $e->getResponse()->getBody();
            $json = json_decode($body, true);
            if (!empty($json['detail'])) {
                return ['error' => "Error: " . $json['detail']];
            }
            if (!empty($json['error'])) {
                return ['error' => "Error: " . $json['error']];
            }
        }

        return ['error' => $e->getMessage()];
    }

    /**
     * Validate admin credentials against Django backend
     * Uses HTTP Basic Auth to authenticate with Django
     */
    public function validateAdmin(string $username, string $password): array
    {
        try {
            // Create a new client with Basic Auth for this specific request
            $client = new Client([
                'base_uri' => $this->baseUrl,
                'timeout' => 10,
                'auth' => [$username, $password],
                'headers' => ['Accept' => 'application/json'],
                'verify' => false,
            ]);

            // Use POST to validate credentials (Django API expects POST or any method)
            $resp = $client->post('/api/validate-admin/');
            $data = json_decode((string) $resp->getBody(), true) ?: [];

            // Return full response including role and organization data
            return [
                'success' => $data['success'] ?? true,
                'id' => $data['id'] ?? null,
                'user' => $data['user'] ?? $username,
                'is_admin' => $data['is_admin'] ?? false,
                'is_staff' => $data['is_staff'] ?? false,
                'role' => $data['role'] ?? 'org_admin',
                'organization_id' => $data['organization_id'] ?? null,
                'organization_name' => $data['organization_name'] ?? null,
                'organization_logo' => $data['organization_logo'] ?? null,
                'organizations' => $data['organizations'] ?? [],
            ];
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
            if ($statusCode === 401 || $statusCode === 403) {
                return ['success' => false, 'error' => 'Invalid username or password'];
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function employees(?int $organizationId = null): array
    {
        $path = '/api/employees/';
        if ($organizationId) {
            $path .= '?organization_id=' . $organizationId;
        }
        return $this->get($path);
    }

    public function messageSettings(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/message-settings/' . $query);
    }

    public function voiceSettings(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/voice-settings/' . $query);
    }

    public function attendance(): array
    {
        return $this->get('/api/attendance/');
    }

    public function getAttendance($id): ?array
    {
        $data = $this->get('/api/attendance/?id=' . $id);
        return $data['attendance'][0] ?? null;
    }

    public function attendanceList(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/attendance/?' . $queryStr);
    }

    public function attendanceGrid(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/attendance-grid/?' . $queryStr);
    }

    public function upsertAttendance(array $payload, $files = []): array
    {
        // For multipart requests (files), we MUST use POST because Django doesn't parse
        // multipart body for PUT requests into request.POST/request.FILES automatically.
        // The View handles both POST and PUT and uses 'id' to distinguish update vs create.
        if (!empty($files)) {
            $method = 'POST';
        } else {
            $method = !empty($payload['id']) ? 'PUT' : 'POST';
        }
        \Log::info("=== UPSERT_ATTENDANCE called ===", [
            'method' => $method,
            'files_count' => count($files),
            'files_empty' => empty($files),
            'employee_id' => $payload['employee_id'] ?? 'MISSING'
        ]);

        if (!empty($files)) {
            \Log::info("=== UPSERT: Using MULTIPART path ===");
            try {
                $multipart = [];
                foreach ($files as $key => $file) {
                    if ($file) {
                        $multipart[] = [
                            'name' => $key,
                            'contents' => fopen($file->getPathname(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                        ];
                        \Log::info("Added file to multipart: {$key} = " . $file->getClientOriginalName());
                    }
                }
                foreach ($payload as $key => $value) {
                    $multipart[] = ['name' => $key, 'contents' => (string) $value];
                    \Log::info("Added field to multipart: {$key} = " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value));
                }
                \Log::info("Sending MULTIPART request to /api/attendance/ with " . count($multipart) . " parts");
                $options = [
                    'multipart' => $multipart,
                    'headers' => $this->getHeaders()
                ];
                $resp = $this->client->request($method, '/api/attendance/', $options);
                $body = (string) $resp->getBody();
                \Log::info("MULTIPART response: " . $body);
                return json_decode($body, true) ?: [];
            } catch (GuzzleException $e) {
                \Log::error("MULTIPART request failed: " . $e->getMessage());
                return $this->handleGuzzleException($e);
            }
        }

        \Log::info("=== UPSERT: Using JSON/send path ===");
        return $this->send($method, '/api/attendance/', $payload);
    }

    public function deleteAttendance($id): array
    {
        return $this->send('DELETE', '/api/attendance/', ['id' => $id]);
    }

    public function bulkAction(string $action, array $ids): array
    {
        return $this->post('/api/attendance-bulk-action/', ['action' => $action, 'ids' => $ids]);
    }

    public function holidays(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/holidays/' . $query);
    }

    public function upsertHoliday(array $payload): array
    {
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
        return $this->send($method, '/api/holidays/', $payload);
    }

    public function deleteHoliday($id): array
    {
        return $this->send('DELETE', '/api/holidays/', ['id' => $id]);
    }

    public function generateBulkHolidays($year, ?int $organizationId = null): array
    {
        $payload = ['year' => $year];
        if ($organizationId) {
            $payload['organization_id'] = $organizationId;
        }
        return $this->post('/api/bulk-holidays-generate/', $payload);
    }

    public function salaryStatistics(?int $organizationId = null, $month = null, $year = null): array
    {
        $query = [];
        if ($organizationId)
            $query['organization_id'] = $organizationId;
        if ($month)
            $query['month'] = $month;
        if ($year)
            $query['year'] = $year;

        $queryStr = http_build_query($query);
        return $this->get('/api/salary-statistics/?' . $queryStr);
    }

    public function upsertSalaryStatistic(array $payload): array
    {
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
        return $this->send($method, '/api/salary-statistics/', $payload);
    }

    public function deleteSalaryStatistic($id): array
    {
        return $this->send('DELETE', '/api/salary-statistics/', ['id' => $id]);
    }

    public function generateSalaryStatistics(array $payload): array
    {
        return $this->post('/api/salary-statistics/generate/', $payload);
    }

    public function reports(): array
    {
        return $this->get('/api/reports/');
    }

    public function salaryReportDetailed(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/salary-report-detailed/?' . $queryStr);
    }

    public function upsertEmployee(array $payload, $file = null): array
    {
        // Always use POST because Guzzle/Django has issues with PUT + Multipart
        // The backend handles update_or_create logic on POST requests
        $method = 'POST';

        if ($file) {
            try {
                $multipart = [
                    [
                        'name' => 'employee_image',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ]
                ];
                foreach ($payload as $key => $value) {
                    $multipart[] = ['name' => $key, 'contents' => $value];
                }
                // Guzzle request with multipart
                $options = [
                    'multipart' => $multipart,
                    'headers' => $this->getHeaders()
                ];
                $resp = $this->client->request($method, '/api/employees/', $options);
                return json_decode((string) $resp->getBody(), true) ?: [];
            } catch (GuzzleException $e) {
                return $this->handleGuzzleException($e);
            }
        }

        return $this->send($method, '/api/employees/', $payload);
    }

    public function deleteEmployee($id): array
    {
        return $this->send('DELETE', '/api/employees/', ['id' => $id]);
    }

    public function saveVoiceSettings(array $payload): array
    {
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
        return $this->send($method, '/api/voice-settings/', $payload);
    }

    public function saveMessageSettings(array $payload): array
    {
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
        return $this->send($method, '/api/message-settings/', $payload);
    }

    public function livefeedList(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/livefeed-list/?' . $queryStr);
    }

    public function livefeedAction(array $payload): array
    {
        return $this->post('/api/livefeed-action/', $payload);
    }

    public function shifts(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/shifts/' . $query);
    }

    public function saveShift(array $payload): array
    {
        return $this->post('/api/shifts/', $payload);
    }

    public function deleteShift($id): array
    {
        return $this->send('DELETE', '/api/shifts/', ['id' => $id]);
    }

    public function salaryDefaults(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/salary-defaults/' . $query);
    }

    public function saveSalaryDefaults(array $payload): array
    {
        return $this->post('/api/salary-defaults/', $payload);
    }

    public function companyInfo(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/company-info/' . $query);
    }

    public function saveCompanyInfo(array $payload, $file = null): array
    {
        if ($file) {
            // Multipart upload for logo
            try {
                $multipart = [
                    [
                        'name' => 'logo',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ]
                ];
                foreach ($payload as $key => $value) {
                    $multipart[] = ['name' => $key, 'contents' => $value];
                }
                $options = [
                    'multipart' => $multipart,
                    'headers' => $this->getHeaders()
                ];
                $resp = $this->client->post('/api/company-info/', $options);
                return json_decode((string) $resp->getBody(), true) ?: [];
            } catch (GuzzleException $e) {
                return $this->handleGuzzleException($e);
            }
        }
        return $this->post('/api/company-info/', $payload);
    }

    public function contextSettings(?int $organizationId = null): array
    {
        $query = $organizationId ? '?organization_id=' . $organizationId : '';
        return $this->get('/api/context-settings/' . $query);
    }

    public function saveContextSettings(array $payload): array
    {
        return $this->post('/api/context-settings/', $payload);
    }

    public function integrationSettings(): array
    {
        return $this->get('/api/integration-settings/');
    }

    public function saveIntegrationSettings(array $payload): array
    {
        return $this->post('/api/integration-settings/', $payload);
    }

    protected function post(string $path, array $payload): array
    {
        return $this->send('POST', $path, $payload);
    }

    public function export(array $payload)
    {
        try {
            return $this->client->post('/api/export/', [
                'form_params' => $payload,
                'stream' => true,
                'headers' => $this->getHeaders()
            ]);
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e);
        }
    }

    public function import($file, array $data)
    {
        try {
            $multipart = [
                [
                    'name' => 'import_file',
                    'contents' => fopen($file->getPathname(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
                [
                    'name' => 'year',
                    'contents' => $data['year'],
                ],
                [
                    'name' => 'month',
                    'contents' => $data['month'],
                ],
                [
                    'name' => 'type',
                    'contents' => $data['type'] ?? 'attendance',
                ],
            ];

            $resp = $this->client->post('/api/import/', [
                'multipart' => $multipart,
                'headers' => $this->getHeaders()
            ]);

            return json_decode((string) $resp->getBody(), true) ?: [];
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e);
        }
    }

    // =========================================================================
    // MULTI-TENANT ORGANIZATION & DEVICE APIs
    // =========================================================================

    /**
     * Get all organizations
     */
    public function organizations(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/organizations/' . ($queryStr ? '?' . $queryStr : ''));
    }

    /**
     * Get single organization by ID
     */
    public function organization(int $id): array
    {
        return $this->get("/api/organizations/{$id}/");
    }

    /**
     * Create new organization
     */
    public function createOrganization(array $payload): array
    {
        return $this->post('/api/organizations/', $payload);
    }

    /**
     * Update organization
     */
    public function updateOrganization(int $id, array $payload): array
    {
        return $this->send('PUT', "/api/organizations/{$id}/", $payload);
    }

    /**
     * Delete organization
     */
    public function deleteOrganization(int $id): array
    {
        return $this->send('DELETE', "/api/organizations/{$id}/", []);
    }

    /**
     * Get organization statistics
     */
    public function organizationStats(int $id): array
    {
        return $this->get("/api/organizations/{$id}/stats/");
    }

    /**
     * Get devices for an organization
     */
    public function organizationDevices(int $orgId): array
    {
        return $this->get("/api/organizations/{$orgId}/devices/");
    }

    /**
     * Get all devices
     */
    public function devices(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/devices/' . ($queryStr ? '?' . $queryStr : ''));
    }

    /**
     * Get single device by ID
     */
    public function device(int $id): array
    {
        return $this->get("/api/devices/{$id}/");
    }

    /**
     * Create new device
     */
    public function createDevice(array $payload): array
    {
        return $this->post('/api/devices/', $payload);
    }

    /**
     * Update device
     */
    public function updateDevice(int $id, array $payload): array
    {
        return $this->send('PUT', "/api/devices/{$id}/", $payload);
    }

    /**
     * Delete device
     */
    public function deleteDevice(int $id): array
    {
        return $this->send('DELETE', "/api/devices/{$id}/", []);
    }

    /**
     * Validate device ID
     */
    public function validateDevice(string $deviceId): array
    {
        return $this->get("/api/devices/validate/?device_id=" . urlencode($deviceId));
    }

    // ============================================================================
    // Organization User Management APIs
    // ============================================================================

    /**
     * Get organization users
     */
    public function orgUsers(?int $organizationId = null): array
    {
        $query = $organizationId ? "?organization_id={$organizationId}" : '';
        return $this->get('/api/org-users/' . $query);
    }

    /**
     * Create new organization user
     */
    public function createOrgUser(array $payload): array
    {
        return $this->send('POST', '/api/org-users/', $payload);
    }

    /**
     * Delete organization user
     */
    public function deleteOrgUser(int $id): array
    {
        return $this->send('DELETE', "/api/org-users/{$id}/", []);
    }

    /**
     * Update organization user
     */
    public function updateOrgUser(int $id, array $payload): array
    {
        return $this->send('PUT', "/api/org-users/{$id}/", $payload);
    }

    // ============================================================================
    // SaaS APIs: Audit Logs and Subscription Plans
    // ============================================================================

    /**
     * Get audit logs with filters
     */
    public function auditLogs(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/audit-logs/' . ($queryStr ? '?' . $queryStr : ''));
    }

    /**
     * Log an action (creates audit log entry)
     */
    public function logAction(array $payload): array
    {
        return $this->post('/api/log-action/', $payload);
    }

    /**
     * Get available subscription plans
     */
    public function subscriptionPlans(): array
    {
        return $this->get('/api/subscription-plans/');
    }

    /**
     * Alias for subscriptionPlans() - simpler name
     */
    public function plans(): array
    {
        return $this->subscriptionPlans();
    }

    public function upsertPlan(array $payload): array
    {
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
        // If ID is present in payload for PUT, append it to URL if your API expects /api/subscription-plans/{id}/
        // But some APIs (like your upsertAttendance) assume /api/subscription-plans/ handles both with ID in body.
        // Let's check attendance/urls.py... it uses one view `subscription_plans_api`.
        // So keeping the URL as /api/subscription-plans/ is correct if the view handles method dispatch.
        return $this->send($method, '/api/subscription-plans/', $payload);
    }

    public function deletePlan($id): array
    {
        return $this->send('DELETE', '/api/subscription-plans/', ['id' => $id]);
    }

    // ============================================================================
    // Data Export/Import
    // ============================================================================

    /**
     * Export organization data (JSON format - for backups)
     */
    public function exportData(int $orgId, string $include = 'employees,attendance,shifts,holidays', ?int $month = null, ?int $year = null): array
    {
        $query = http_build_query(array_filter([
            'organization_id' => $orgId,
            'include' => $include,
            'month' => $month,
            'year' => $year,
        ]));
        return $this->get('/api/export-data/?' . $query);
    }

    /**
     * Import organization data (JSON format - for backups)
     */
    public function importData(array $data): array
    {
        return $this->post('/api/import-data/', $data);
    }

    /**
     * Get analytics data
     */
    public function analytics(?int $orgId = null): array
    {
        $query = $orgId ? '?organization_id=' . $orgId : '';
        return $this->get('/api/analytics/' . $query);
    }
}
