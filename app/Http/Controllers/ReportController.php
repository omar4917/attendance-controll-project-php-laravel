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
        $isSuperAdmin = $userRole === 'super_admin';
        
        // Add organization filtering
        $orgId = $this->getOrganizationId();
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
        
        // Log the view action
        try {
            $api->logAction([
                'action' => 'export',
                'resource_type' => 'salary_report',
                'organization_id' => $orgId,
                'user_email' => \Session::get('admin_email', \Session::get('admin_user', 'unknown')),
                'user_name' => \Session::get('admin_name', \Session::get('admin_user', 'unknown')),
                'details' => [
                    'view_type' => 'index',
                    'month' => $month,
                    'year' => $year,
                    'department' => $filters['department'] ?? 'all',
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log salary report view: ' . $e->getMessage());
        }
        
        return view('reports.index', compact(
            'reports', 'totals', 'defaults', 'departments', 
            'months', 'years', 'month', 'year', 'monthName', 'error',
            'isSuperAdmin', 'userRole', 'organizations', 'orgId'
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
