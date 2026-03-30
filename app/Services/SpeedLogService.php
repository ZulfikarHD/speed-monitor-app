<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SpeedLog;
use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Speed Log Service
 *
 * Manages speed log operations including bulk inserts, violation detection,
 * and speed limit configuration retrieval.
 */
class SpeedLogService
{
    /**
     * Bulk insert speed logs for a trip.
     *
     * Accepts an array of speed log data, calculates violation flags,
     * performs efficient bulk insert, and updates trip statistics.
     *
     * @param  Trip  $trip  The trip to add speed logs to
     * @param  array<int, array{speed: float, recorded_at: string}>  $speedLogData  Array of speed log records
     * @return Collection<int, SpeedLog> Collection of created speed logs
     *
     * @throws InvalidArgumentException If speedLogData is invalid
     */
    public function bulkInsert(Trip $trip, array $speedLogData): Collection
    {
        if (empty($speedLogData)) {
            return collect();
        }

        $this->validateSpeedLogData($speedLogData);

        $speedLimit = $this->getSpeedLimit();
        $now = now();

        $preparedData = collect($speedLogData)->map(function ($log) use ($trip, $speedLimit, $now) {
            return [
                'trip_id' => $trip->id,
                'speed' => $log['speed'],
                'recorded_at' => $log['recorded_at'],
                'is_violation' => $this->calculateViolationFlag($log['speed'], $speedLimit),
                'created_at' => $now,
            ];
        })->all();

        DB::table('speed_logs')->insert($preparedData);

        $createdLogs = SpeedLog::where('trip_id', $trip->id)
            ->where('created_at', $now)
            ->orderBy('recorded_at')
            ->get();

        $tripService = new TripService;
        $tripService->updateTripStats($trip);
        $trip->save();

        return $createdLogs;
    }

    /**
     * Calculate whether a speed constitutes a violation.
     *
     * Returns true if speed exceeds the configured speed limit.
     *
     * @param  float  $speed  Speed in km/h
     * @param  float  $speedLimit  Speed limit in km/h
     * @return bool True if violation, false otherwise
     */
    public function calculateViolationFlag(float $speed, float $speedLimit): bool
    {
        return $speed > $speedLimit;
    }

    /**
     * Get the current speed limit setting.
     *
     * Retrieves from settings table, falls back to default (60 km/h).
     *
     * @return float Speed limit in km/h
     */
    public function getSpeedLimit(): float
    {
        return Setting::getSpeedLimit();
    }

    /**
     * Validate speed log data array structure.
     *
     * Ensures each entry has required fields with correct types.
     *
     * @param  array<int, array{speed: float, recorded_at: string}>  $speedLogData  Data to validate
     *
     * @throws InvalidArgumentException If validation fails
     */
    private function validateSpeedLogData(array $speedLogData): void
    {
        foreach ($speedLogData as $index => $log) {
            if (! is_array($log)) {
                throw new InvalidArgumentException("Speed log at index {$index} must be an array");
            }

            if (! isset($log['speed'])) {
                throw new InvalidArgumentException("Speed log at index {$index} is missing 'speed' field");
            }

            if (! isset($log['recorded_at'])) {
                throw new InvalidArgumentException("Speed log at index {$index} is missing 'recorded_at' field");
            }

            if (! is_numeric($log['speed'])) {
                throw new InvalidArgumentException("Speed log at index {$index} has invalid 'speed' value");
            }

            if ($log['speed'] < 0) {
                throw new InvalidArgumentException("Speed log at index {$index} has negative speed");
            }
        }
    }
}
