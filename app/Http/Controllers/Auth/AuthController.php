<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Authentication Controller
 *
 * Handles API authentication endpoints for login, logout, and
 * user retrieval with Sanctum token-based authentication.
 */
class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * Authenticate user and return access token.
     *
     * Validates credentials and returns Sanctum API token for stateless
     * authentication. Frontend should store token in localStorage and
     * include it in Authorization header for protected endpoints.
     *
     * @param  LoginRequest  $request  Validated login credentials
     * @return JsonResponse Token and user data (200) or error (401/403)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'token' => $result['token'],
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'role' => $result['user']->role,
                    'is_active' => $result['user']->is_active,
                ],
            ], 200);
        } catch (AuthenticationException $e) {
            $statusCode = $e->getMessage() === 'Account is inactive' ? 403 : 401;

            return response()->json([
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Revoke current access token and logout user.
     *
     * Deletes the token used for current request. Token cannot be
     * reused after logout. Frontend should clear stored token.
     *
     * @param  Request  $request  Authenticated request
     * @return JsonResponse Success message (200)
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

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
