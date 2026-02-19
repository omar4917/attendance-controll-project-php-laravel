<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DjangoAdminAuth
{
    /**
     * Handle an incoming request.
     * Check if user is authenticated via Django admin credentials.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Session::get('authenticated')) {
            \Log::debug("DjangoAdminAuth: Session 'authenticated' missing. Redirecting to login.", ['url' => $request->fullUrl()]);
            return redirect()->route('login');
        }

        // Check if session has expired
        $expires = Session::get('auth_expires');
        if ($expires && now()->timestamp > $expires) {
            \Log::debug("DjangoAdminAuth: Session expired.", ['expires' => $expires, 'now' => now()->timestamp]);
            Session::forget(['authenticated', 'admin_user', 'is_admin', 'auth_expires']);
            return redirect()->route('login')->withErrors(['login' => 'Your session has expired. Please log in again.']);
        }

        return $next($request);
    }
}
