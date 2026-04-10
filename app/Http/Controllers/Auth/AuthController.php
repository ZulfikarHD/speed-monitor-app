<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Authentication Controller
|--------------------------------------------------------------------------
|
| Handles authentication endpoints for login, logout, and user retrieval
| using Laravel Sanctum token-based authentication. Supports both Inertia
| form submissions and traditional API JSON requests.
|
*/
class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * Authenticate user and redirect to dashboard.
     *
     * Validates credentials and logs user in using session auth.
     * Redirects to role-based dashboard on success.
     *
     * @param  LoginRequest  $request  Validated email and password credentials
     * @return RedirectResponse Redirects to dashboard or back with errors
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Redirect to role-based dashboard
            $redirectUrl = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'superuser' => route('superuser.dashboard'),
                default => route('employee.dashboard'),
            };

            return redirect()->intended($redirectUrl);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout user and redirect to login page.
     *
     * @param  Request  $request  Authenticated request
     * @return RedirectResponse Redirects to login page
     */
    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();

        return redirect('/login');
    }

    /**
     * Get current authenticated user data.
     *
     * Returns user information for authenticated token holder.
     * Useful for refreshing user data or verifying token validity.
     *
     * @param  Request  $request  Authenticated request
     * @return JsonResponse User data with role and status (200)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->getCurrentUser($request->user());

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ], 200);
    }
}
