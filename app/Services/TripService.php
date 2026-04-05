<?php

namespace App\Services;

use App\Enums\TripStatus;
use App\Models\Trip;
use App\Models\User;
use InvalidArgumentException;

/**
 * Trip Service
 *
 * Manages trip lifecycle and statistics calculation including starting/ending trips,
 * computing speed metrics, calculating distances, and tracking violations.
 */
class TripService
{
    /**
     * Speed log interval in seconds.
     *
     * Speed logs are recorded every 5 seconds during active trips.
     */
    private const SPEED_LOG_INTERVAL_SECONDS = 5;

    public function __construct(
        private TripAnalysisService $analysisService
    ) {}

    /**
     * Start a new trip for the specified user.
     *
     * Creates a trip record with InProgress status and sets the start timestamp.
     * Trip will be ready to receive speed logs for tracking.
     *
     * @param  User  $user  The user starting the trip
     * @param  string|null  $notes  Optional notes about the trip
     * @return Trip Newly created trip with InProgress status
     */
    public function startTrip(User $user, ?string $notes = null): Trip
    {
        return Trip::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => TripStatus::InProgress,
            'notes' => $notes,
            'violation_count' => 0,
        ]);
    }

    /**
     * End an active trip and calculate final statistics.
     *
     * Updates trip status to Completed, sets end timestamp, calculates duration,
     * and computes all trip statistics from speed logs.
     *
     * @param  Trip  $trip  The trip to end
     * @return Trip Updated trip with Completed status and calculated stats
     *
     * @throws InvalidArgumentException If trip is not in InProgress status
     */
    public function endTrip(Trip $trip): Trip
    {
        if ($trip->status !== TripStatus::InProgress) {
            throw new InvalidArgumentException('Only trips with InProgress status can be ended');
        }

        $trip->ended_at = now();
        $trip->duration_seconds = $trip->ended_at->diffInSeconds($trip->started_at);
        $trip->status = TripStatus::Completed;

        $this->updateTripStats($trip);

        $trip->save();

        // Analyze trip for suspicious patterns
        $this->analysisService->analyzeTripOnEnd($trip);

        return $trip->fresh();
    }

    /**
     * Auto-stop a trip that exceeded inactivity duration.
     *
     * Similar to endTrip but marks status as AutoStopped to indicate
     * the trip was terminated automatically by the system rather than manually.
     *
     * @param  Trip  $trip  The trip to auto-stop
     * @return Trip Updated trip with AutoStopped status and calculated stats
     *
     * @throws InvalidArgumentException If trip is not in InProgress status
     */
    public function autoStopTrip(Trip $trip): Trip
    {
        if ($trip->status !== TripStatus::InProgress) {
            throw new InvalidArgumentException('Only trips with InProgress status can be auto-stopped');
        }

        $trip->ended_at = now();
        $trip->duration_seconds = $trip->ended_at->diffInSeconds($trip->started_at);
        $trip->status = TripStatus::AutoStopped;

        $this->updateTripStats($trip);

        $trip->save();

        return $trip->fresh();
    }

    /**
     * Calculate and update trip statistics from speed logs.
     *
     * Computes max speed, average speed, total distance traveled, and violation count
     * based on all speed logs associated with the trip. Distance calculation assumes
     * speed logs are recorded at 5-second intervals.
     *
     * @param  Trip  $trip  The trip to calculate statistics for
     * @return Trip Trip instance with updated statistics (not yet saved to database)
     */
    public function updateTripStats(Trip $trip): Trip
    {
        $speedLogs = $trip->speedLogs()->orderBy('recorded_at')->get();

        if ($speedLogs->isEmpty()) {
            $trip->max_speed = 0;
            $trip->average_speed = 0;
            $trip->total_distance = 0;
            $trip->violation_count = 0;

            return $trip;
        }

        $trip->max_speed = $speedLogs->max('speed');
        $trip->average_speed = round($speedLogs->avg('speed'), 2);
        $trip->violation_count = $speedLogs->where('is_violation', true)->count();

        $totalDistance = $speedLogs->sum(function ($log) {
            return ($log->speed * self::SPEED_LOG_INTERVAL_SECONDS) / 3600;
        });

        $trip->total_distance = round($totalDistance, 2);

        return $trip;
    }
}
