<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/*
|--------------------------------------------------------------------------
| Welcome Controller
|--------------------------------------------------------------------------
|
| Handles root route redirect. Redirects unauthenticated users to login
| page and authenticated users to their role-based dashboard.
|
*/
class WelcomeController extends Controller
{
    /**
     * Handle root route request.
     *
     * Redirects to login page for guests or role-based dashboard for
     * authenticated users. Ensures proper landing page based on auth state.
     *
     * @return RedirectResponse Redirect to login or dashboard
     */
    public function index(): RedirectResponse
    {
        // If user is authenticated, redirect to role-based dashboard
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->isSupervisor()) {
                return redirect()->route('supervisor.dashboard');
            }

            return redirect()->route('employee.dashboard');
        }

        // Guest users go to login
        return redirect()->route('login');
    }
}
