<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Profile Service
 *
 * Handles user profile management business logic including profile
 * information updates and password changes with validation.
 */
class ProfileService
{
    /**
     * Update user profile information.
     *
     * Updates user's name and email with duplicate email validation.
     * Prevents email conflicts by checking uniqueness before updating.
     *
     * @param  User  $user  User to update
     * @param  array  $data  Profile data (name, email)
     * @return User Updated user with fresh data
     *
     * @throws ValidationException When email already exists for another user
     */
    public function updateProfile(User $user, array $data): User
    {
        // Check if email changed and already exists for another user
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = User::where('email', $data['email'])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($existingUser) {
                throw ValidationException::withMessages([
                    'email' => 'Email already taken by another user.',
                ]);
            }
        }

        // Update user profile
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return $user->fresh();
    }

    /**
     * Update user password.
     *
     * Changes user's password after current password verification.
     * Current password validation is handled in ChangePasswordRequest.
     *
     * @param  User  $user  User to update
     * @param  array  $data  Password data (new_password)
     */
    public function updatePassword(User $user, array $data): void
    {
        // Hash and update password
        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);
    }
}
