<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Speed Tracker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for speed tracking system behavior, anti-gaming rules,
    | and monitoring parameters.
    |
    */

    /**
     * Minimum trip duration in minutes.
     *
     * WHY: Prevents employees from gaming the system by:
     * - Cherry-picking only short, slow trips
     * - Stopping trip before speeding and restarting after
     * - Recording only parking lot movements (5 km/h)
     *
     * RECOMMENDED: 5-15 minutes depending on your use case
     * - Delivery drivers: 15 minutes (full route)
     * - Field workers: 10 minutes (site visits)
     * - General use: 5 minutes (minimum meaningful data)
     *
     * SET TO 0 TO DISABLE (not recommended for compliance)
     */
    'minimum_trip_duration' => env('SPEEDTRACKER_MIN_TRIP_DURATION', 5),

    /**
     * Maximum trips allowed per shift (hours).
     *
     * WHY: Prevents excessive trip splitting to hide speeding.
     * Example: If max is 5 trips per 8 hours, employee can't create
     * 20 short trips to cherry-pick only slow segments.
     *
     * RECOMMENDED: 3-5 trips per 8 hour shift
     *
     * SET TO 0 TO DISABLE
     */
    'max_trips_per_shift' => env('SPEEDTRACKER_MAX_TRIPS_PER_SHIFT', 5),

    /**
     * Shift duration in hours for trip counting.
     *
     * Used with max_trips_per_shift to calculate rolling window.
     */
    'shift_duration_hours' => env('SPEEDTRACKER_SHIFT_DURATION', 8),

    /**
     * Alert threshold for suspicious trip patterns.
     *
     * WHY: Notify supervisors when employee creates too many trips
     * in short period, indicating potential gaming behavior.
     *
     * Example: 3 trips within 2 hours = suspicious
     */
    'suspicious_pattern_trips' => env('SPEEDTRACKER_SUSPICIOUS_TRIPS', 3),
    'suspicious_pattern_hours' => env('SPEEDTRACKER_SUSPICIOUS_HOURS', 2),

    /**
     * Minimum distance threshold in meters.
     *
     * WHY: Trips with very low distance (e.g., parking lot only)
     * may indicate gaming. Flag trips under this threshold.
     *
     * RECOMMENDED: 500m - 1000m (0.5km - 1km)
     *
     * SET TO 0 TO DISABLE
     */
    'minimum_distance_meters' => env('SPEEDTRACKER_MIN_DISTANCE', 500),

    /**
     * Speed log interval in seconds.
     *
     * How often GPS speed is recorded during active trip.
     * More frequent = more accurate but more data.
     *
     * RECOMMENDED: 5 seconds (balance of accuracy and efficiency)
     */
    'speed_log_interval' => env('SPEEDTRACKER_LOG_INTERVAL', 5),

    /**
     * Auto-stop duration in seconds.
     *
     * Trip automatically stops after this duration of no movement.
     * Prevents forgotten trips from running indefinitely.
     *
     * RECOMMENDED: 1800 seconds (30 minutes)
     */
    'auto_stop_duration' => env('SPEEDTRACKER_AUTO_STOP_DURATION', 1800),

];
