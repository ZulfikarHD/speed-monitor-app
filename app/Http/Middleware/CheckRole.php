<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check if authenticated user has the required role.
 *
 * Middleware to restrict route access based on user roles (employee, supervisor, admin).
 * Redirects unauthorized users to the login page.
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has one of the required roles.
     * Supports comma-separated roles for flexible access control.
     * If the user is not authenticated or doesn't have any of the roles, redirect to login.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure(Request): (Response)  $next  The next middleware/handler
     * @param  string  $role  The required role(s) (employee, supervisor, admin or comma-separated like "supervisor,admin")
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // WHY: Support multiple roles with comma-separated values for flexible access control
        // This allows routes to be accessible by multiple roles (e.g., role:supervisor,admin)
        $allowedRoles = array_map('trim', explode(',', $role));

        if (! in_array($request->user()->role, $allowedRoles, true)) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
