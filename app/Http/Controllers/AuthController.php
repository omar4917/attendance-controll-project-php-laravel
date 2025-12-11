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
            Session::put('admin_user', $user->name);
            Session::put('auth_type', 'php');
            Session::put('auth_expires', now()->addHours(8)->timestamp);
            
            return redirect()->intended(route('attendance.index'));
        }

        // Method 2: Try Django admin authentication
        $result = $api->validateAdmin($username, $password);

        if ($result['success'] ?? false) {
            // Django admin authenticated successfully
            Session::put('authenticated', true);
            Session::put('admin_user', $result['user'] ?? $username);
            Session::put('is_admin', $result['is_admin'] ?? true);
            Session::put('auth_type', 'django');
            Session::put('auth_expires', now()->addHours(8)->timestamp);

            return redirect()->intended(route('attendance.index'));
        }

        // Both authentication methods failed
        return back()->withErrors([
            'login' => 'Invalid username/email or password',
        ])->withInput(['username' => $username]);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        Session::forget(['authenticated', 'admin_user', 'is_admin', 'auth_type', 'auth_expires']);
        Session::flush();
        
        return redirect()->route('login');
    }
}
