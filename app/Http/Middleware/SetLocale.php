<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Sets the application locale from session or defaults to 'en'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'en'));
        
        if (in_array($locale, ['en', 'bn', 'hi', 'es'])) {
            app()->setLocale($locale);
        }
        
        return $next($request);
    }
}
