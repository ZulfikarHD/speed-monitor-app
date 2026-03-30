<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

/**
 * Trip Policy
 *
 * Defines authorization rules for trip operations ensuring employees
 * can only manage their own trips while supervisors and admins have
 * broader access for monitoring purposes.
 */
class TripPolicy
{
    /**
     * Determine if the user can view any trips.
     *
     * Employees can view their own trips, while supervisors and admins
     * can view all trips for monitoring purposes.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can view trips
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view a specific trip.
     *
     * Trip owners can view their own trips. Supervisors and admins
     * can view any trip for monitoring and reporting purposes.
     *
     * @param  User  $user  The authenticated user
     * @param  Trip  $trip  The trip to view
     * @return bool True if user can view the trip
     */
    public function view(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id
            || $user->isSupervisor()
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create trips.
     *
     * All authenticated users (employees, supervisors, admins)
     * can start their own trips.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if user can create trips
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update a specific trip.
     *
     * Only the trip owner can end their own trip. This ensures
     * employees cannot modify other users' trips.
     *
     * @param  User  $user  The authenticated user
     * @param  Trip  $trip  The trip to update
     * @return bool True if user can update the trip
     */
    public function update(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id;
    }

    /**
     * Determine if the user can delete a specific trip.
     *
     * Only supervisors and admins can delete trips for data management
     * and correction purposes. Employees cannot delete their own trips.
     *
     * @param  User  $user  The authenticated user
     * @param  Trip  $trip  The trip to delete
     * @return bool True if user can delete the trip
     */
    public function delete(User $user, Trip $trip): bool
    {
        return $user->isSupervisor() || $user->isAdmin();
    }
}
