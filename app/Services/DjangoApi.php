<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class DjangoApi
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(Session::get('django_base_url', config('django.base_url', env('DJANGO_BASE_URL'))), '/');
        $this->apiKey = config('django.api_key', env('DJANGO_API_KEY'));

        $headers = [
            'Accept' => 'application/json',
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

    protected function get(string $path): array
    {
        try {
            $resp = $this->client->get($path);
            return json_decode((string) $resp->getBody(), true) ?: [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function send(string $method, string $path, array $payload): array
    {
        try {
            $resp = $this->client->request($method, $path, ['json' => $payload]);
            return json_decode((string) $resp->getBody(), true) ?: [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage(), 'payload' => Arr::except($payload, [])];
        }
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
            ]);
            
            // Try to access a protected endpoint to validate credentials
            $resp = $client->get('/api/validate-admin/');
            $data = json_decode((string) $resp->getBody(), true) ?: [];
            
            return [
                'success' => true,
                'user' => $data['user'] ?? $username,
                'is_admin' => $data['is_admin'] ?? true,
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

    public function messageSettings(): array
    {
        return $this->get('/api/message-settings/');
    }

    public function voiceSettings(): array
    {
        return $this->get('/api/voice-settings/');
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
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
        
        if (!empty($files)) {
            try {
                $multipart = [];
                foreach ($files as $key => $file) {
                    if ($file) {
                        $multipart[] = [
                            'name' => $key,
                            'contents' => fopen($file->getPathname(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                        ];
                    }
                }
                foreach ($payload as $key => $value) {
                    $multipart[] = ['name' => $key, 'contents' => $value];
                }
                $resp = $this->client->request($method, '/api/attendance/', ['multipart' => $multipart]);
                return json_decode((string) $resp->getBody(), true) ?: [];
            } catch (GuzzleException $e) {
                return ['error' => $e->getMessage()];
            }
        }

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

    public function holidays(): array
    {
        return $this->get('/api/holidays/');
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

    public function generateBulkHolidays($year): array
    {
        return $this->post('/api/bulk-holidays-generate/', ['year' => $year]);
    }

    public function salaryStatistics(): array
    {
        return $this->get('/api/salary-statistics/');
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

    public function reports(): array
    {
        return $this->get('/api/reports/');
    }

    public function salaryReportDetailed(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/salary-report-detailed/?' . $queryStr);
    }

    public function upsertEmployee(array $payload): array
    {
        $method = !empty($payload['id']) ? 'PUT' : 'POST';
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

    public function moderatorLabels(array $query = []): array
    {
        $queryStr = http_build_query($query);
        return $this->get('/api/moderator-labels/?' . $queryStr);
    }

    public function saveModeratorLabel(array $payload): array
    {
        return $this->post('/api/moderator-labels/', $payload);
    }

    public function shifts(): array
    {
        return $this->get('/api/shifts/');
    }

    public function saveShift(array $payload): array
    {
        return $this->post('/api/shifts/', $payload);
    }

    public function deleteShift($id): array
    {
        return $this->send('DELETE', '/api/shifts/', ['id' => $id]);
    }

    public function salaryDefaults(): array
    {
        return $this->get('/api/salary-defaults/');
    }

    public function saveSalaryDefaults(array $payload): array
    {
        return $this->post('/api/salary-defaults/', $payload);
    }

    public function companyInfo(): array
    {
        return $this->get('/api/company-info/');
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
                $resp = $this->client->post('/api/company-info/', ['multipart' => $multipart]);
                return json_decode((string) $resp->getBody(), true) ?: [];
            } catch (GuzzleException $e) {
                return ['error' => $e->getMessage()];
            }
        }
        return $this->post('/api/company-info/', $payload);
    }

    public function contextSettings(): array
    {
        return $this->get('/api/context-settings/');
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
            ]);
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
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
            ];

            $resp = $this->client->post('/api/import/', [
                'multipart' => $multipart,
            ]);
            
            return json_decode((string) $resp->getBody(), true) ?: [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
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
}
