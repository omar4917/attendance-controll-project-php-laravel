<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class OrgUsersController extends Controller
{
    use HasOrganizationContext;

    /**
     * List organization users
     */
    public function index(Request $request, DjangoApi $api)
    {
        $role = session('user_role', '');
        
        // Only org_main_admin and org_admin can access this page
        if (!in_array($role, ['super_admin', 'shadow_admin', 'org_main_admin', 'org_admin'])) {
            return redirect()->route('employees.index')->with('error', 'Access denied');
        }
        
        $orgId = $this->getOrganizationId();
        $response = $api->orgUsers($orgId);
        
        $orgUsers = $response['org_users'] ?? [];
        $canManage = in_array($role, ['super_admin', 'shadow_admin', 'org_main_admin']);
        
        // Check if current org already has a main admin
        $hasMainAdmin = collect($orgUsers)->contains(function ($user) use ($orgId) {
            return $user['role'] === 'org_main_admin' && 
                   (!$orgId || ($user['organization_id'] ?? null) == $orgId);
        });
        
        // Track which orgs have main admins (for super admin viewing all)
        $orgsWithMainAdmin = collect($orgUsers)
            ->where('role', 'org_main_admin')
            ->pluck('organization_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        
        // Sorting
        $sortField = $request->input('sort', 'username');
        $sortDir = $request->input('dir', 'asc');
        $validSortFields = ['username', 'email', 'organization_name', 'first_name', 'role'];
        
        if (in_array($sortField, $validSortFields) && !empty($orgUsers)) {
            $orgUsers = collect($orgUsers)->sortBy(function ($u) use ($sortField) {
                return strtolower((string) ($u[$sortField] ?? ''));
            }, SORT_REGULAR, $sortDir === 'desc')->values()->all();
        }
        
        // If super_admin and viewing "All Organizations", get list of orgs for the "Add User" modal
        $organizations = [];
        if (in_array($role, ['super_admin', 'shadow_admin']) && !$orgId) {
            $organizationsResponse = $api->organizations();
            $organizations = $organizationsResponse['organizations'] ?? [];
        }
        
        return view('org-users.index', compact('orgUsers', 'canManage', 'organizations', 'orgId', 'hasMainAdmin', 'orgsWithMainAdmin'));
    }

    /**
     * Store a new org user
     */
    public function store(Request $request, DjangoApi $api)
    {
        $role = session('user_role', '');
        
        // Only org_main_admin can create users
        if (!in_array($role, ['super_admin', 'shadow_admin', 'org_main_admin'])) {
            return redirect()->back()->with('error', 'Only main admins can create users');
        }
        
        $orgId = $this->getOrganizationId() ?: $request->input('organization_id');
        
        if (!$orgId) {
            return redirect()->back()->with('error', 'Organization is required');
        }

        $payload = [
            'organization_id' => $orgId,
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'first_name' => $request->input('first_name', ''),
            'last_name' => $request->input('last_name', ''),
            'role' => $request->input('role', 'org_viewer'),
        ];
        
        $response = $api->createOrgUser($payload);
        
        if (!empty($response['error'])) {
            return redirect()->back()->with('error', $response['error']);
        }
        
        return redirect()->route('org-users.index')->with('success', 'User created successfully');
    }

    /**
     * Update an org user
     */
    public function update(Request $request, int $id, DjangoApi $api)
    {
        $role = session('user_role', '');
        
        // Only org_main_admin can update users
        if (!in_array($role, ['super_admin', 'shadow_admin', 'org_main_admin'])) {
            return redirect()->back()->with('error', 'Only main admins can update users');
        }
        
        $payload = [
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'first_name' => $request->input('first_name', ''),
            'last_name' => $request->input('last_name', ''),
            // 'role' => $request->input('role'), // Removed to prevent sending null
        ];
        
        if ($request->filled('role')) {
            $payload['role'] = $request->input('role');
        }
        
        // Only include password if provided
        if ($request->filled('password')) {
            $payload['password'] = $request->input('password');
        }
        
        $response = $api->updateOrgUser($id, $payload);
        
        if (!empty($response['error'])) {
            return redirect()->back()->with('error', $response['error']);
        }
        
        return redirect()->route('org-users.index')->with('success', 'User updated successfully');
    }

    /**
     * Delete an org user
     */
    public function destroy(int $id, DjangoApi $api)
    {
        $role = session('user_role', '');
        
        // Only org_main_admin can delete users
        if (!in_array($role, ['super_admin', 'shadow_admin', 'org_main_admin'])) {
            return redirect()->back()->with('error', 'Only main admins can delete users');
        }
        
        $response = $api->deleteOrgUser($id);
        
        if (!empty($response['error'])) {
            return redirect()->back()->with('error', $response['error']);
        }
        
        return redirect()->route('org-users.index')->with('success', 'User deleted successfully');
    }
}
