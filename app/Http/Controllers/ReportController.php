<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        // Get filter parameters
        $filters = $request->only(['month', 'year', 'department']);
        if (!isset($filters['month'])) {
            $filters['month'] = date('n');
        }
        if (!isset($filters['year'])) {
            $filters['year'] = date('Y');
        }
        
        // Role detection
        $userRole = \Session::get('user_role', 'org_admin');
        $isSuperAdmin = in_array($userRole, ['super_admin', 'shadow_admin']);
        
        // Add organization filtering
        // Use session value from header dropdown (selected_organization_id)
        $orgId = null;
        if ($isSuperAdmin) {
            // Super admins use the header dropdown selection
            $orgId = \Session::get('selected_organization_id');
        } else {
            // Regular org admins - always filtered to their org
            $orgId = $this->getOrganizationId();
        }
        
        if ($orgId) {
            $filters['organization_id'] = $orgId;
        }
        
        // For super admins, get list of organizations
        $organizations = [];
        if ($isSuperAdmin) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
        }
        
        // Fetch salary report data (same as salaryReport method)
        $data = $api->salaryReportDetailed($filters);
        
        $reports = $data['salary_data'] ?? [];
        $totals = $data['totals'] ?? [];
        $defaults = $data['defaults'] ?? [];
        $departments = $data['departments'] ?? [];
        $months = $data['months'] ?? range(1, 12);
        $years = $data['years'] ?? [date('Y') - 1, date('Y'), date('Y') + 1];
        $month = $filters['month'];
        $year = $filters['year'];
        $monthName = $data['month_name'] ?? date('F Y');
        $error = $data['error'] ?? null;
        $debug = $data['debug'] ?? [];
        
        // Client-side sorting
        $sortField = $request->input('sort', 'employee_name');
        $sortDir = $request->input('dir', 'asc');
        
        $sortMapping = [
            'employee_name' => ['employee_name', 'employee', 'employee_id'],
            'joining_date' => ['joining_date', 'join_date'],
            'basic_salary' => ['basic_salary', 'base_salary'],
            'gross_salary' => ['gross_salary'],
            'attendance_days' => ['attendance_days', 'attended_days'],
            'late_days' => ['late_days', 'late_count'],
            'final_salary' => ['payable', 'final_salary'],
        ];
        
        $fields = $sortMapping[$sortField] ?? [$sortField];
        
        usort($reports, function($a, $b) use ($fields, $sortDir) {
            $valA = null;
            $valB = null;
            foreach ($fields as $f) {
                if (isset($a[$f]) && $valA === null) $valA = $a[$f];
                if (isset($b[$f]) && $valB === null) $valB = $b[$f];
            }
            $valA = $valA ?? '';
            $valB = $valB ?? '';
            
            if (is_numeric($valA) && is_numeric($valB)) {
                $cmp = $valA <=> $valB;
            } else {
                $cmp = strcasecmp((string)$valA, (string)$valB);
            }
            return $sortDir === 'desc' ? -$cmp : $cmp;
        });
        
        // Log the view action

        
        return view('reports.index', compact(
            'reports', 'totals', 'defaults', 'departments', 
            'months', 'years', 'month', 'year', 'monthName', 'error',
            'isSuperAdmin', 'userRole', 'organizations', 'orgId', 'debug'
        ));
    }

    public function salaryReport(Request $request, DjangoApi $api)
    {
        $filters = $request->only(['month', 'year', 'department']);
        
        // Add organization filtering
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $filters['organization_id'] = $orgId;
        }
        
        $data = $api->salaryReportDetailed($filters);
        
        $salaryData = $data['salary_data'] ?? [];
        $totals = $data['totals'] ?? [];
        $defaults = $data['defaults'] ?? [];
        $departments = $data['departments'] ?? [];
        $months = $data['months'] ?? range(1, 12);
        $years = $data['years'] ?? [date('Y')];
        $monthName = $data['month_name'] ?? date('F Y');
        $error = $data['error'] ?? null;

        // Log the view action
        try {
            $api->logAction([
                'action' => 'export',
                'resource_type' => 'salary_report',
                'organization_id' => $orgId,
                'user_email' => \Session::get('admin_email', \Session::get('admin_user', 'unknown')),
                'user_name' => \Session::get('admin_name', \Session::get('admin_user', 'unknown')),
                'details' => [
                    'view_type' => 'detailed',
                    'month' => $filters['month'] ?? date('n'),
                    'year' => $filters['year'] ?? date('Y'),
                    'department' => $filters['department'] ?? 'all',
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log salary report view: ' . $e->getMessage());
        }

        return view('reports.salary', compact(
            'salaryData', 'totals', 'defaults', 'departments', 
            'months', 'years', 'monthName', 'error'
        ));
    }
}
