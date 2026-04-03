<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

/**
 * User Service
 *
 * Handles business logic for user management operations including
 * listing, creating, updating, and deactivating user accounts.
 */
class UserService
{
    /**
     * Get all users with filtering and pagination.
     *
     * Supports filtering by search term (name/email), role, and status.
     * Returns paginated results for optimal performance with large datasets.
     *
     * @param  array<string, mixed>  $filters  Filter parameters (search, role, status)
     * @return LengthAwarePaginator Paginated user collection
     */
    public function getAllUsers(array $filters): LengthAwarePaginator
    {
        $query = User::query()->orderBy('created_at', 'desc');

        // Search by name or email
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Filter by status (active/inactive)
        if (! empty($filters['status'])) {
            $isActive = $filters['status'] === 'active';
            $query->where('is_active', $isActive);
        }

        // Paginate results (default 20 per page)
        $perPage = $filters['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Create a new user account.
     *
     * Creates user with hashed password and assigns role and status.
     * Default status is active unless specified otherwise.
     *
     * @param  array<string, mixed>  $data  User data (name, email, password, role, is_active)
     * @return User Newly created user instance
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an existing user's information.
     *
     * Updates user profile, role, status, and optionally password.
     * Only provided fields are updated (partial updates supported).
     *
     * @param  User  $user  User instance to update
     * @param  array<string, mixed>  $data  Update data (name, email, password, role, is_active)
     * @return User Updated user instance
     */
    public function updateUser(User $user, array $data): User
    {
        $updateData = [];

        // Update name if provided
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        // Update email if provided
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        // Update password if provided
        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        // Update role if provided
        if (isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }

        // Update status if provided
        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Deactivate a user account.
     *
     * Soft deactivation by setting is_active to false.
     * User can be reactivated later if needed.
     *
     * @param  User  $user  User instance to deactivate
     * @return User Updated user instance
     */
    public function deactivateUser(User $user): User
    {
        $user->update(['is_active' => false]);

        return $user->fresh();
    }

    /**
     * Reactivate a previously deactivated user account.
     *
     * Sets is_active to true, restoring user access.
     *
     * @param  User  $user  User instance to reactivate
     * @return User Updated user instance
     */
    public function reactivateUser(User $user): User
    {
        $user->update(['is_active' => true]);

        return $user->fresh();
    }
}
