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
     * Only supervisors and admins have access to the monitoring dashboard
     * which displays aggregate statistics and employee trip data.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can access dashboard
     */
    public function viewDashboard(User $user): bool
    {
        return $user->isSupervisor() || $user->isAdmin();
    }
}
