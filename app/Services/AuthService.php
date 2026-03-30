<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/**
 * Authentication Service
 *
 * Handles user authentication business logic including login,
 * logout, and user retrieval with Sanctum token management.
 */
class AuthService
{
    /**
     * Authenticate user and create access token.
     *
     * Validates user credentials and account status before generating
     * a Sanctum API token for stateless authentication. Inactive accounts
     * are prevented from logging in for security compliance.
     *
     * @param  array  $credentials  User credentials (email, password)
     * @return array{token: string, user: User} Token and user data
     *
     * @throws AuthenticationException When credentials invalid or account inactive
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (! $user->is_active) {
            throw new AuthenticationException('Account is inactive');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Revoke user's current access token.
     *
     * Deletes the token used for the current request, effectively
     * logging out the user. Token cannot be reused after revocation.
     *
     * @param  User  $user  Authenticated user
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Get authenticated user data.
     *
     * Returns the currently authenticated user with all attributes.
     * Useful for fetching fresh user data after token validation.
     *
     * @param  User  $user  Authenticated user
     * @return User User model with role and status
     */
    public function getCurrentUser(User $user): User
    {
        return $user;
    }
}
