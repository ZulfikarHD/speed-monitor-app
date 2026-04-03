<?php

namespace App\Policies;

use App\Models\User;

/**
 * Setting Policy
 *
 * Defines authorization rules for settings management ensuring all users
 * can view settings (employees need to know speed limit) but only admins
 * can modify configuration values.
 */
class SettingPolicy
{
    /**
     * Determine if the user can view any settings.
     *
     * All authenticated users can view settings because employees need
     * to know the current speed limit for their speedometer and other
     * configuration values that affect their trip tracking.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can view settings
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update settings.
     *
     * Supervisors and admins can modify application settings to allow
     * operational flexibility in adjusting speed limits, tracking intervals,
     * and auto-stop duration based on business needs.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can update settings
     */
    public function update(User $user): bool
    {
        return $user->isSupervisor() || $user->isAdmin();
    }
}
