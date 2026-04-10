<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Policy
 *
 * Defines authorization rules for user-related operations including
 * dashboard access and user management functionality.
 */
class UserPolicy
{
    /**
     * Determine if the user can view the dashboard.
     *
     * Only superusers and admins have access to the monitoring dashboard
     * which displays aggregate statistics and employee trip data.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can access dashboard
     */
    public function viewDashboard(User $user): bool
    {
        return $user->isSuperuser() || $user->isAdmin();
    }

    /**
     * Determine if the user can view any users.
     *
     * Superusers and admins have access to the employee management page
     * which lists all users in the system for operational flexibility.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can view users list
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperuser() || $user->isAdmin();
    }

    /**
     * Determine if the user can create new users.
     *
     * Superusers and admins can create new user accounts to allow
     * operational flexibility in onboarding employees.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can create new users
     */
    public function create(User $user): bool
    {
        return $user->isSuperuser() || $user->isAdmin();
    }

    /**
     * Determine if the user can update another user.
     *
     * Superusers and admins can update user details, but they cannot
     * update their own account to prevent accidental lockouts.
     *
     * @param  User  $user  The authenticated user
     * @param  User  $targetUser  The user being updated
     * @return bool True if user can update target user
     */
    public function update(User $user, User $targetUser): bool
    {
        return ($user->isSuperuser() || $user->isAdmin()) && $user->id !== $targetUser->id;
    }

    /**
     * Determine if the user can deactivate another user.
     *
     * Superusers and admins can deactivate users, but they cannot
     * deactivate their own account to prevent self-lockout.
     *
     * @param  User  $user  The authenticated user
     * @param  User  $targetUser  The user being deactivated
     * @return bool True if user can deactivate target user
     */
    public function deactivate(User $user, User $targetUser): bool
    {
        return ($user->isSuperuser() || $user->isAdmin()) && $user->id !== $targetUser->id;
    }
}
