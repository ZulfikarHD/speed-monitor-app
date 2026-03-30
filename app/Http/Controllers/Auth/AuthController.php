<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
     * Authenticate user and return access token.
     *
     * Validates credentials via AuthService and generates Sanctum API token.
     * For Inertia requests, returns user data as props to be handled by
     * frontend (stored in localStorage). On failure, redirects back with
     * validation errors.
     *
     * @param  LoginRequest  $request  Validated email and password credentials
     * @return InertiaResponse User data and token as props, or back with errors
     *
     * @throws AuthenticationException When credentials are invalid or account is inactive
     */
    public function login(LoginRequest $request): InertiaResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            // Return user data and token as Inertia props
            // Frontend will store these in localStorage for persistence
            return Inertia::render('auth/Login', [
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'role' => $result['user']->role,
                    'is_active' => $result['user']->is_active,
                ],
                'token' => $result['token'],
            ]);
        } catch (AuthenticationException $e) {
            // Redirect back to login with error message
            return back()->withErrors([
                'email' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Revoke current access token and logout user.
     *
     * Deletes the Sanctum token used for current request. For Inertia requests,
     * redirects to login page. For API requests, returns JSON success message.
     *
     * @param  Request  $request  Authenticated request with valid Sanctum token
     * @return RedirectResponse|JsonResponse Redirects to login (Inertia) or returns success JSON (API)
     */
    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        $this->authService->logout($request->user());

        // Inertia requests: redirect to login page
        if ($request->inertia()) {
            return redirect('/login');
        }

        // API requests: return JSON success message
        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
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
