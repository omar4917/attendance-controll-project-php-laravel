<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        // Get organization-filtered employees
        $orgId = $this->getOrganizationId();
        $data = $api->employees($orgId);
        $employees = $data['employees'] ?? [];
        $error = $data['error'] ?? null;

        // Extract unique departments and designations for filters
        $departments = collect($employees)->pluck('department')->filter()->unique()->values()->all();
        $designations = collect($employees)->pluck('designation')->filter()->unique()->values()->all();

        // Filter by active status - use filled() to check for non-empty value
        $activeFilter = $request->input('active');
        if ($request->filled('active')) {
            $isActive = $activeFilter === 'yes';
            $employees = collect($employees)->filter(function ($emp) use ($isActive) {
                return ($emp['is_active'] ?? false) === $isActive;
            })->values()->all();
        }

        // Filter by Department
        $dept = $request->input('department');
        if ($dept && $dept !== '' && $dept !== 'All') {
            $employees = collect($employees)->filter(function ($emp) use ($dept) {
                return ($emp['department'] ?? '') === $dept;
            })->values()->all();
        }

        // Filter by Designation
        $desig = $request->input('designation');
        if ($desig && $desig !== '' && $desig !== 'All') {
            $employees = collect($employees)->filter(function ($emp) use ($desig) {
                return ($emp['designation'] ?? '') === $desig;
            })->values()->all();
        }

        // Sorting
        $sortField = $request->input('sort', 'name');
        $sortDir = $request->input('dir', 'asc');
        $validSortFields = ['employee_id', 'name', 'organization_name', 'department', 'designation', 'monthly_salary', 'is_active'];
        
        if (in_array($sortField, $validSortFields)) {
            $employees = collect($employees)->sortBy(function ($emp) use ($sortField) {
                $value = $emp[$sortField] ?? '';
                // Handle numeric sorting for salary
                if ($sortField === 'monthly_salary') {
                    return (float) $value;
                }
                // Handle boolean sorting
                if ($sortField === 'is_active') {
                    return $value ? 1 : 0;
                }
                // String sorting (case-insensitive)
                return strtolower((string) $value);
            }, SORT_REGULAR, $sortDir === 'desc')->values()->all();
        }

        $editing = [
            'employee_id' => $request->query('employee_id'),
            'name' => $request->query('name'),
            'department' => $request->query('department'),
            'designation' => $request->query('designation'),
            'email' => $request->query('email'),
            'phone' => $request->query('phone'),
        ];
        
        $organizationName = $this->getOrganizationName();
        
        return view('employees.index', compact('employees', 'error', 'editing', 'departments', 'designations', 'organizationName'));
    }

    public function create(DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $organizationName = $this->getOrganizationName();
        $isSuperAdmin = \Session::get('user_role') === 'super_admin';
        
        // For super admin, get all organizations for selection
        $organizations = [];
        if ($isSuperAdmin) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
        }
        
        return view('employees.form', compact('orgId', 'organizationName', 'isSuperAdmin', 'organizations'));
    }

    public function edit($id, DjangoApi $api)
    {
        $orgId = $this->getOrganizationId();
        $organizationName = $this->getOrganizationName();
        $isSuperAdmin = \Session::get('user_role') === 'super_admin';
        
        // For super admin, get all organizations for selection
        $organizations = [];
        if ($isSuperAdmin) {
            $orgsData = $api->organizations();
            $organizations = $orgsData['organizations'] ?? [];
        }
        
        $data = $api->employees($isSuperAdmin ? null : $orgId); // Super admin can see all
        $employees = $data['employees'] ?? [];
        $employee = collect($employees)->firstWhere('id', $id);
        
        if (!$employee) {
            return redirect()->route('employees.index')->with('error', 'Employee not found');
        }
        
        return view('employees.form', compact('employee', 'orgId', 'organizationName', 'isSuperAdmin', 'organizations'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only([
            'id','employee_id','name','email','department','phone','designation',
            'monthly_salary', 'is_active', 'hire_date', 'bank_account', 'branch', 'date_inactive',
            'organization_id'
        ]);
        
        // Handle checkbox
        $payload['is_active'] = $request->has('is_active');
        
        // Use form-submitted organization_id, fallback to session org for org admins
        if (empty($payload['organization_id'])) {
            $orgId = $this->getOrganizationId();
            if ($orgId) {
                $payload['organization_id'] = $orgId;
            }
        }
        
        // Sanitize dates
        if (empty($payload['hire_date'])) $payload['hire_date'] = null;
        if (empty($payload['date_inactive'])) $payload['date_inactive'] = null;
        
        $resp = $api->upsertEmployee($payload, $request->file('employee_image'));
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        if ($request->has('popup')) {
             return "<script>window.parent.location.href = window.parent.location.href;</script>";
        }
        
        return redirect()->route('employees.index')->with('success', 'Employee saved');
    }

    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = $request->only([
            'employee_id','name','email','department','phone','designation',
            'monthly_salary', 'is_active', 'hire_date', 'bank_account', 'branch', 'date_inactive',
            'organization_id'
        ]);
        $payload['id'] = $id;
        
        // Handle checkbox
        $payload['is_active'] = $request->has('is_active');
        
        // Sanitize dates
        if (empty($payload['hire_date'])) $payload['hire_date'] = null;
        if (empty($payload['date_inactive'])) $payload['date_inactive'] = null;
        
        $resp = $api->upsertEmployee($payload, $request->file('employee_image'));
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        
        if ($request->has('popup')) {
             return "<script>window.parent.location.href = window.parent.location.href;</script>";
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deleteEmployee($id);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->route('employees.index')->with('success', 'Employee deleted');
    }
}
