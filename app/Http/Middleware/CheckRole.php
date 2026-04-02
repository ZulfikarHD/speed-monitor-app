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
     * Checks if the authenticated user has the required role.
     * If the user is not authenticated or doesn't have the role, redirect to login.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure(Request): (Response)  $next  The next middleware/handler
     * @param  string  $role  The required role (employee, supervisor, admin)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== $role) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
