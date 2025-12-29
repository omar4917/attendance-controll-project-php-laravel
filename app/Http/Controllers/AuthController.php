<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (Session::get('authenticated')) {
            return redirect()->route('attendance.index');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login attempt - supports both PHP users and Django admin
     */
    public function login(Request $request, DjangoApi $api)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Method 1: Try PHP local user first (by email or name)
        $user = User::where('email', $username)->orWhere('name', $username)->first();
        
        if ($user && Hash::check($password, $user->password)) {
            // PHP user authenticated successfully
            Session::put('authenticated', true);
            Session::put('admin_id', $user->id); // Store local user ID
            Session::put('admin_user', $user->name);
            Session::put('admin_email', $user->email);
            Session::put('admin_name', $user->name);
            Session::put('auth_type', 'php');
            Session::put('auth_expires', now()->addHours(8)->timestamp);
            // For PHP users, default to super_admin (since they're local admins)
            Session::put('user_role', 'super_admin');
            Session::put('is_superuser', true);
            Session::put('organization_id', null);
            Session::put('organization_name', null);
            
            // Fetch organizations from Django for super_admin dropdown
            try {
                $orgsData = $api->organizations();
                $organizations = collect($orgsData['organizations'] ?? [])->map(function($org) {
                    return ['id' => $org['id'], 'name' => $org['name'], 'slug' => $org['slug'] ?? ''];
                })->toArray();
                Session::put('organizations', $organizations);

                // Log successful login for PHP user
                $api->logAction([
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'action' => 'login',
                    'resource_type' => 'user',
                    'resource_id' => $user->id,
                    'resource_name' => $user->name,
                    'details' => ['auth_method' => 'php_local']
                ]);
            } catch (\Exception $e) {
                Session::put('organizations', []);
                \Log::error("Failed to log PHP user login or fetch orgs: " . $e->getMessage());
            }
            
            return redirect()->intended(route('attendance.index'));
        }

        // Method 2: Try Django admin authentication
        $result = $api->validateAdmin($username, $password);

        // DEBUG: Log the result
        \Log::info('Django auth result', $result);

        if ($result['success'] ?? false) {
            // Django admin authenticated successfully
            Session::put('authenticated', true);
            Session::put('admin_id', $result['id'] ?? null); // Store remote user ID
            Session::put('admin_user', $result['user'] ?? $username);
            Session::put('admin_email', $result['email'] ?? $username);
            Session::put('admin_name', $result['user'] ?? $username);
            Session::put('is_admin', $result['is_admin'] ?? false);
            Session::put('is_superuser', $result['is_admin'] ?? false);
            Session::put('auth_type', 'django');
            Session::put('auth_expires', now()->addHours(8)->timestamp);
            
            // Store multi-tenant role info
            Session::put('user_role', $result['role'] ?? 'org_admin');
            Session::put('organization_id', $result['organization_id'] ?? null);
            Session::put('organization_name', $result['organization_name'] ?? null);
            Session::put('organizations', $result['organizations'] ?? []);
            
            // If super_admin with no org selected, they'll see all data
            // If org_admin, they only see their organization's data

            return redirect()->intended(route('attendance.index'));
        }

        // DEBUG: Show actual error in message
        $errorMsg = $result['error'] ?? 'Invalid username/email or password';
        
        // Both authentication methods failed
        return back()->withErrors([
            'login' => $errorMsg,
        ])->withInput(['username' => $username]);
    }

    /**
     * Handle logout
     */
    public function logout(DjangoApi $api)
    {
        // Log logout event via API
        try {
            $api->logAction([
                'user_email' => Session::get('admin_email'),
                'user_name' => Session::get('admin_name'),
                'action' => 'logout',
                'resource_type' => 'user',
                'resource_id' => Session::get('admin_id'),
                'resource_name' => Session::get('admin_user'),
                'organization_id' => Session::get('organization_id'),
                'details' => ['session_id' => session()->getId()]
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to log logout action: " . $e->getMessage());
        }

        Session::forget([
            'authenticated', 'admin_user', 'is_admin', 'auth_type', 'auth_expires',
            'user_role', 'organization_id', 'organization_name', 'organizations'
        ]);
        Session::flush();
        
        return redirect()->route('login');
    }

    /**
     * Switch organization (for super_admin only)
     */
    public function switchOrganization(Request $request)
    {
        // Allow both super_admin and shadow_admin to switch organizations
        if (!in_array(Session::get('user_role'), ['super_admin', 'shadow_admin'])) {
            return back()->with('error', 'Only super admins can switch organizations');
        }

        $orgId = $request->input('organization_id');
        
        if ($orgId === '' || $orgId === null || $orgId === 'all') {
            // Clear org selection - show all data
            Session::put('selected_organization_id', null);
            Session::put('selected_organization_name', 'All Organizations');
            Session::put('selected_organization_logo', null);
        } else {
            // Find org in available list
            $organizations = Session::get('organizations', []);
            $selectedOrg = collect($organizations)->firstWhere('id', (int) $orgId);
            
            if ($selectedOrg) {
                Session::put('selected_organization_id', $selectedOrg['id']);
                Session::put('selected_organization_name', $selectedOrg['name']);
                Session::put('selected_organization_logo', $selectedOrg['logo'] ?? null);
            }
        }

        return back()->with('success', 'Organization switched');
    }
}
