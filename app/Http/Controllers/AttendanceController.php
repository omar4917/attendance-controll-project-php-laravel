<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AttendanceController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        $queryParams = $request->query();
        
        // Add organization filtering
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $queryParams['organization_id'] = $orgId;
        }
        
        // Handle date filter (YYYY-MM) -> month/year
        if ($request->has('date')) {
            try {
                $date = Carbon::parse($request->query('date'));
                $queryParams['month'] = $date->month;
                $queryParams['year'] = $date->year;
            } catch (\Exception $e) {
                // Fallback to current if invalid date
                $queryParams['month'] = Carbon::now()->month;
                $queryParams['year'] = Carbon::now()->year;
            }
        }

        $data = $api->attendanceGrid($queryParams);
        $records = $data['attendance'] ?? ($data['records'] ?? []);
        // Check if 'employees' key exists to distinguish between "empty result" and "not provided"
        $gridEmployees = array_key_exists('employees', $data) ? $data['employees'] : null;
        $days = $data['days'] ?? [];
        $error = $data['error'] ?? null;
        $pdfUrls = $data['pdf_urls'] ?? [];

        // fetch employees to enrich rows (with org filtering)
        $empData = $api->employees($orgId);
        $empList = $empData['employees'] ?? [];
        $empMap = [];
        foreach ($empList as $e) {
            if (empty($e['employee_id'])) {
                continue;
            }
            $empMap[$e['employee_id']] = $e;
        }

        // simple rollups for dashboard cards
        $total = count($records);
        $present = collect($records)->where('status', 'Present')->count();
        $absent = collect($records)->where('status', 'Absent')->count();
        $late = collect($records)->where('status', 'Late')->count();

        // filters
        $month = (int)($queryParams['month'] ?? Carbon::now()->month);
        $year = (int)($queryParams['year'] ?? Carbon::now()->year);
        $departments = collect($empList)->pluck('department')->filter(fn($v) => !empty($v) && $v !== 'All')->unique()->values()->all();
        $designations = collect($empList)->pluck('designation')->filter(fn($v) => !empty($v) && $v !== 'All')->unique()->values()->all();
        
        // Months and Years for filter dropdowns
        $months = range(1, 12);
        $years = range(Carbon::now()->year - 5, Carbon::now()->year + 1);

        // prefer grid-provided days/employees when available
        if (!empty($data['month'])) {
            $month = (int) $data['month'];
        }
        if (!empty($data['year'])) {
            $year = (int) $data['year'];
        }

        $employeesForGrid = !is_null($gridEmployees) ? $gridEmployees : [];
        $daysForGrid = !empty($days) ? $days : [];

        // build days of month if not provided
        if (empty($daysForGrid)) {
            $daysLocal = [];
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::create($year, $month, $d);
                $daysLocal[] = [
                    'num' => $d,
                    'short' => strtoupper($date->format('D')),
                    'full' => $date->format('Y-m-d'),
                ];
            }
            $daysForGrid = $daysLocal;
        }

        // group records by employee if not provided (fallback only if API didn't send employees)
        if (is_null($gridEmployees)) {
            $employees = [];
            // Initialize with all employees from API
            foreach ($empList as $emp) {
                $eid = $emp['employee_id'];
                $employees[$eid] = [
                    'employee_id' => $eid,
                    'emp_pk' => $emp['id'] ?? $eid,
                    'name' => $emp['name'],
                    'designation' => $emp['designation'] ?? '',
                    'department' => $emp['department'] ?? '',
                    'emp_image_url' => $emp['face_image'] ? "data:image/jpeg;base64,".$emp['face_image'] : null,
                    'statuses' => array_fill(0, count($daysForGrid), ['status' => '']), // Initialize empty statuses
                    'totals' => [
                        'Present' => 0,
                        'Late' => 0,
                        'On_Leave' => 0,
                        'Holiday' => 0,
                        'Absent' => 0,
                        'Half_Day' => 0,
                        'Early_Leave' => 0,
                    ],
                ];
            }

            // Fill with records
            foreach ($records as $rec) {
                $eid = $rec['employee_id'] ?? null;
                if (!$eid || !isset($employees[$eid])) {
                    continue;
                }
                
                $date = $rec['date'] ?? null;
                if ($date) {
                    $dayNum = (int)Carbon::parse($date)->day;
                    $idx = $dayNum - 1;
                    if (isset($employees[$eid]['statuses'][$idx])) {
                        $employees[$eid]['statuses'][$idx] = [
                            'status' => $rec['status'] ?? '',
                            'checkin_time' => $rec['checkin_time'] ?? null,
                            'checkout_time' => $rec['checkout_time'] ?? null,
                            'is_late' => ($rec['status'] === 'Late'),
                            'icon' => $this->getStatusIcon($rec['status'] ?? ''),
                        ];
                    }

                    $statusKey = str_replace(' ', '_', $rec['status'] ?? '');
                    if (isset($employees[$eid]['totals'][$statusKey])) {
                        $employees[$eid]['totals'][$statusKey] += 1;
                    }
                }
            }
            
            // Convert to array
            $employeesForGrid = array_values($employees);
            usort($employeesForGrid, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
        }

        $editing = [
            'id' => $request->query('id'),
            'employee_id' => $request->query('employee_id'),
            'date' => $request->query('date'),
            'status' => $request->query('status'),
            'checkin_time' => $request->query('checkin_time'),
            'checkout_time' => $request->query('checkout_time'),
        ];

        $djangoBaseUrl = config('django.base_url');
        
        // Navigation query strings
        $prevDate = Carbon::create($year, $month, 1)->subMonth();
        $nextDate = Carbon::create($year, $month, 1)->addMonth();
        
        $prevParams = $request->query();
        $prevParams['month'] = $prevDate->month;
        $prevParams['year'] = $prevDate->year;
        $prevParams['date'] = $prevDate->format('Y-m');

        $nextParams = $request->query();
        $nextParams['month'] = $nextDate->month;
        $nextParams['year'] = $nextDate->year;
        $nextParams['date'] = $nextDate->format('Y-m');

        $prev_qs = http_build_query($prevParams);
        $next_qs = http_build_query($nextParams);
        
        // For super admins, get list of organizations for batch downloads
        $organizations = [];
        $isSuperAdmin = session('is_superuser', false);
        if ($isSuperAdmin) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
        }

        return view('attendance.index', compact(
            'records',
            'error',
            'total',
            'present',
            'absent',
            'late',
            'editing',
            'month',
            'year',
            'months',
            'years',
            'departments',
            'designations',
            'djangoBaseUrl',
            'daysForGrid',
            'employeesForGrid',
            'prev_qs',
            'next_qs',
            'pdfUrls',
            'organizations',
            'isSuperAdmin'
        ));
    }

    private function getStatusIcon($status)
    {
        // Map status to icon path (relative to public)
        // Assuming icons are in public/icons/
        switch ($status) {
            case 'Present': return 'icons/present.png';
            case 'Absent': return 'icons/absent.png';
            case 'Late': return 'icons/late.png';
            case 'On Leave': return 'icons/on_leave.png';
            case 'Holiday': return 'icons/holidays.png';
            default: return 'icons/pendings.png';
        }
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only(['id','employee_id','date','status','checkin_time','checkout_time']);
        $resp = $api->upsertAttendance($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('attendance.index')->with('success', 'Attendance saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->only(['employee_id','date','status','checkin_time','checkout_time']);
        $payload['id'] = $id;
        $resp = $api->upsertAttendance($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('attendance.index')->with('success', 'Attendance updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteAttendance($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('attendance.index')->with('success', 'Attendance deleted');
    }

    public function export(Request $request, DjangoApi $api)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $orgId = $this->getOrganizationId();
        $type = $request->input('type', 'attendance');
        
        // If triggered from the form button
        if ($request->has('export_data')) {
            $response = $api->export([
                'year' => $year, 
                'month' => $month, 
                'export_data' => '1', 
                'organization_id' => $orgId,
                'type' => $type
            ]);
            
            // Check if it's an error array
            if (is_array($response) && !empty($response['error'])) {
                return back()->with('error', $response['error']);
            }

            if (!$response) {
                return back()->with('error', 'Export failed - no response from server');
            }

            // Check if response is a Guzzle Response object
            if (is_object($response) && method_exists($response, 'getBody')) {
                $contentType = $response->getHeaderLine('Content-Type');
                $body = $response->getBody()->getContents();
                
                // Check if response is JSON (error) instead of ZIP
                if (strpos($contentType, 'application/json') !== false) {
                    $errorData = json_decode($body, true);
                    return back()->with('error', $errorData['error'] ?? 'Export failed - server returned an error');
                }
                
                $filename = "{$type}_export_{$year}_{$month}.zip";
                if ($type === 'employees') {
                    $filename = "employees_export_" . date('Y-m-d') . ".zip";
                }
                
                return response($body, 200, [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Content-Length' => strlen($body),
                ]);
            }

            return back()->with('error', 'Export failed - invalid response type');
        }
        return back();
    }

    public function import(Request $request, DjangoApi $api)
    {
        $request->validate([
            'import_file' => 'required|file',
        ]);

        // We need year/month from query or session or input. 
        // The form in blade doesn't send them explicitly as hidden fields, 
        // but we can grab them from the referer or just default to now.
        // Ideally the form should include them. I will update the blade to include them.
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $result = $api->import($request->file('import_file'), ['year' => $year, 'month' => $month]);

        if (!empty($result['error'])) {
             return back()->with('error', $result['error']);
        }

        if (!empty($result['errors'])) {
            return back()->with('import_errors', $result['errors']);
        }

        return back()->with('success', 'Import successful');
    }

    /**
     * Proxy PDF download from Django to fix Chrome cross-origin Content-Disposition issue
     */
    public function downloadPdf(Request $request, DjangoApi $api)
    {
        $type = $request->query('type', 'pdf'); // pdf, bulk, combined
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);
        $department = $request->query('department', '');
        $designation = $request->query('designation', '');
        $orgId = $request->query('organization_id', $this->getOrganizationId());
        
        // Build query string for Django
        $queryParams = array_filter([
            'month' => $month,
            'year' => $year,
            'department' => $department,
            'designation' => $designation,
            'organization_id' => $orgId,
        ]);
        $qs = http_build_query($queryParams);
        
        // Determine Django endpoint and filename
        $djangoBase = rtrim(Session::get('django_base_url', config('django.base_url', 'http://localhost:8001')), '/');
        
        switch ($type) {
            case 'bulk':
                $endpoint = "/attendance-dashboard/pdf/bulk/?{$qs}";
                $filename = "attendance-individual-{$year}-{$month}.zip";
                $contentType = 'application/zip';
                break;
            case 'combined':
                $endpoint = "/attendance-dashboard/pdf/combined/?{$qs}";
                $filename = "attendance-combined-{$year}-{$month}.pdf";
                $contentType = 'application/pdf';
                break;
            default:
                $endpoint = "/attendance-dashboard/pdf/?{$qs}";
                $filename = "attendance-{$year}-{$month}.pdf";
                $contentType = 'application/pdf';
        }
        
        // Log the export action
        try {
            $api->logAction([
                'action' => 'export',
                'resource_type' => 'attendance',
                'organization_id' => $orgId,
                'user_email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
                'user_name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
                'details' => [
                    'type' => $type,
                    'year' => $year,
                    'month' => $month,
                    'filename' => $filename,
                    'source' => 'php_frontend'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log PDF export: ' . $e->getMessage());
        }
        
        try {
            $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 60]);
            $response = $client->get($djangoBase . $endpoint);
            
            $content = $response->getBody()->getContents();
            
            return response($content)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($content));
        } catch (\Exception $e) {
            \Log::error("PDF Download Error: " . $e->getMessage());
            return back()->with('error', 'Failed to download PDF: ' . $e->getMessage());
        }
    }

}
