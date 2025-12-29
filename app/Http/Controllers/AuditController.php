<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuditController extends Controller
{
    use HasOrganizationContext;

    /**
     * Display audit logs page with filters.
     * Super admins see all logs, org admins see only their org.
     */
    public function index(Request $request, DjangoApi $api)
    {
        $filters = $request->only([
            'user_email', 'action', 'resource_type', 
            'start_date', 'end_date', 'page'
        ]);
        
        // Add organization filtering based on role
        $role = Session::get('user_role');
        $orgId = $this->getOrganizationId();
        
        if (!in_array($role, ['super_admin', 'shadow_admin']) && $orgId) {
            // Org admins can only see their own org's logs
            $filters['organization_id'] = $orgId;
        } elseif (in_array($role, ['super_admin', 'shadow_admin']) && $request->has('organization_id')) {
            // Super admins can filter by any org
            $filters['organization_id'] = $request->input('organization_id');
        }
        
        $data = $api->auditLogs($filters);
        
        $logs = $data['logs'] ?? [];
        $total = $data['total'] ?? 0;
        $page = $data['page'] ?? 1;
        $perPage = $data['per_page'] ?? 50;
        $totalPages = $data['total_pages'] ?? 1;
        $error = $data['error'] ?? null;
        
        // Get organizations for filter dropdown (super admins only)
        $organizations = [];
        if (in_array($role, ['super_admin', 'shadow_admin'])) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
        }
        
        // Action types for filter dropdown
        $actionTypes = [
            'login' => 'Login',
            'logout' => 'Logout',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'view' => 'View',
            'export' => 'Export',
            'import' => 'Import',
        ];
        
        // Resource types for filter dropdown
        $resourceTypes = [
            'employee' => 'Employee',
            'attendance' => 'Attendance',
            'holiday' => 'Holiday',
            'shift' => 'Shift',
            'salary' => 'Salary',
            'company_info' => 'Company Info',
            'settings' => 'Settings',
            'user' => 'User',
            'organization' => 'Organization',
        ];
        
        return view('audit.index', compact(
            'logs', 'total', 'page', 'perPage', 'totalPages',
            'organizations', 'actionTypes', 'resourceTypes', 
            'error', 'filters', 'role'
        ));
    }
}
