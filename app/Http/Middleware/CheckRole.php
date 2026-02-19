<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\DjangoApi;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles  Comma separated list of allowed roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Session::has('authenticated')) {
            \Log::debug("CheckRole: Session authenticated key missing. Redirecting to login.");
            return redirect()->route('login');
        }

        $userRole = Session::get('user_role') ?? '';
        \Log::debug("CheckRole: Request start", [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'userRole' => $userRole,
            'required_roles' => $roles
        ]);

        // If no roles specified, just allow authenticated users
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has one of the required roles
        // shadow_admin always bypasses role checks (like super_admin but hidden)
        if ($userRole === 'shadow_admin' || in_array($userRole, $roles)) {
            return $next($request);
        }

        // User is not authorized - log the attempt to Django audit log
        \Log::debug("CheckRole: Unauthorized attempt", ['userRole' => $userRole, 'required_roles' => $roles]);
        $this->logUnauthorizedAttempt($request, $userRole, $roles);

        // Special restriction: org_viewer can NEVER perform non-GET requests on protected resources
        if ($userRole === 'org_viewer' && !$request->isMethod('get')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Viewers are not allowed to perform this action.'], 403);
            }
            return redirect()->back()->with('error', 'Viewers are not allowed to perform this action.');
        }

        // Return 403 Forbidden for unauthorized access
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        abort(403, 'You do not have permission to access following resource.');
    }

    /**
     * Log unauthorized attempt to Django's audit log.
     */
    protected function logUnauthorizedAttempt(Request $request, string $userRole, array $requiredRoles): void
    {
        try {
            $api = app(DjangoApi::class);
            $intendedAction = $this->getIntendedAction($request);

            $api->logAction([
                'action' => 'unauthorized_attempt',
                'resource_type' => $this->getResourceType($request),
                'user_email' => Session::get('admin_email', Session::get('admin_user', 'unknown')),
                'user_name' => Session::get('admin_name', Session::get('admin_user', 'unknown')),
                'organization_id' => Session::get('organization_id'),
                'details' => [
                    'reason' => "Role '{$userRole}' does not have permission for this action.",
                    'intended_action' => $intendedAction,
                    'required_roles' => $requiredRoles,
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'source' => 'php_middleware'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log unauthorized attempt to Django: ' . $e->getMessage());
        }
    }

    /**
     * Determine the intended action from HTTP method.
     */
    protected function getIntendedAction(Request $request): string
    {
        $method = strtoupper($request->method());
        $path = $request->path();

        switch ($method) {
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            case 'GET':
                // Check if it's an edit page
                if (str_contains($path, '/edit')) {
                    return 'view_edit_form';
                }
                if (str_contains($path, '/create')) {
                    return 'view_create_form';
                }
                return 'view';
            default:
                return 'unknown';
        }
    }

    /**
     * Determine resource type from request path.
     */
    protected function getResourceType(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'employees'))
            return 'employee';
        if (str_contains($path, 'attendance'))
            return 'attendance';
        if (str_contains($path, 'holidays'))
            return 'holiday';
        if (str_contains($path, 'salary'))
            return 'salary';
        if (str_contains($path, 'shifts'))
            return 'shift';
        if (str_contains($path, 'settings'))
            return 'settings';
        if (str_contains($path, 'organizations'))
            return 'organization';
        if (str_contains($path, 'livefeed'))
            return 'livefeed';

        return 'unknown';
    }
}
