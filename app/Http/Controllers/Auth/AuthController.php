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
     * Accepts either NPK or email as the identifier.
     * Detects format by checking for '@' character.
     *
     * @param  LoginRequest  $request  Validated identifier and password credentials
     * @return RedirectResponse Redirects to dashboard or back with errors
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $identifier = $request->input('identifier');
        $password = $request->input('password');

        // WHY: NPK never contains '@', emails always do
        $field = str_contains($identifier, '@') ? 'email' : 'npk';

        if (auth()->attempt([$field => $identifier, 'password' => $password])) {
            $user = auth()->user();

            $redirectUrl = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'superuser' => route('superuser.dashboard'),
                default => route('employee.dashboard'),
            };

            return redirect()->intended($redirectUrl);
        }

        return back()->withErrors([
            'identifier' => 'NPK atau email dan kata sandi tidak cocok.',
        ])->withInput($request->only('identifier'));
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
