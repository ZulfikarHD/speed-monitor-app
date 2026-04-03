/**
 * Trip Domain Type Definitions
 *
 * Defines TypeScript interfaces for trip management, speed logging, and statistics.
 * These types ensure type safety when working with trip data across the application
 * and match the backend Laravel models exactly.
 *
 * @example
 * ```ts
 * import type { Trip, SpeedLog, TripStats } from '@/types/trip';
 *
 * const trip: Trip = {
 *   id: 1,
 *   user_id: 1,
 *   started_at: '2026-04-02T10:00:00Z',
 *   status: 'in_progress',
 *   // ... other fields
 * };
 *
 * const stats: TripStats = {
 *   currentSpeed: 45,
 *   maxSpeed: 65,
 *   averageSpeed: 52,
 *   distance: 12.5,
 *   duration: 1800,
 *   violationCount: 3
 * };
 * ```
 */

/**
 * Trip status enum.
 *
 * Matches backend TripStatus enum from App\Enums\TripStatus.
 *
 * - in_progress: Trip is currently active and tracking speed
 * - completed: Trip was manually ended by user
 * - auto_stopped: Trip was automatically stopped due to inactivity
 */
export type TripStatus = 'in_progress' | 'completed' | 'auto_stopped';

/**
 * Trip entity - matches backend Trip model.
 *
 * Represents a single trip session with speed tracking, statistics,
 * and violation monitoring for employee speed compliance.
 */
export interface Trip {
    /** Unique trip identifier */
    id: number;

    /** User who owns this trip */
    user_id: number;

    /** Trip start timestamp (ISO 8601 format) */
    started_at: string;

    /** Trip end timestamp, null if trip is still in progress */
    ended_at: string | null;

    /** Current trip status */
    status: TripStatus;

    /** Total distance traveled in kilometers, null until calculated (string from DB) */
    total_distance: number | string | null;

    /** Maximum speed recorded in km/h, null until calculated (string from DB) */
    max_speed: number | string | null;

    /** Average speed in km/h, null until calculated (string from DB) */
    average_speed: number | string | null;

    /** Number of speed limit violations detected */
    violation_count: number;

    /** Trip duration in seconds, null until trip ends */
    duration_seconds: number | null;

    /** Optional user notes about the trip */
    notes: string | null;

    /** Timestamp of last successful sync (for offline sync tracking) */
    synced_at: string | null;

    /** Record creation timestamp */
    created_at: string;

    /** Record last update timestamp */
    updated_at: string;
}

/**
 * Speed log entity - matches backend SpeedLog model.
 *
 * Represents a single speed measurement recorded during a trip.
 * Speed logs are recorded every 5 seconds during active tracking.
 */
export interface SpeedLog {
    /** Unique speed log identifier (optional for new logs not yet synced) */
    id?: number;

    /** Associated trip ID (optional for local logs not yet synced) */
    trip_id?: number;

    /** Speed in kilometers per hour (string from DB) */
    speed: number | string;

    /** Timestamp when speed was recorded (ISO 8601 format) */
    recorded_at: string;

    /** Whether this speed exceeded the configured speed limit */
    is_violation: boolean;

    /** Record creation timestamp (optional for new logs) */
    created_at?: string;
}

/**
 * Real-time trip statistics.
 *
 * Computed in real-time from speed logs during active trip tracking.
 * These values are calculated on the frontend for immediate feedback
 * and recalculated on the backend when trip ends for accuracy.
 */
export interface TripStats {
    /** Current speed in km/h (most recent reading) */
    currentSpeed: number;

    /** Maximum speed recorded in km/h */
    maxSpeed: number;

    /** Average speed in km/h */
    averageSpeed: number;

    /** Total distance traveled in kilometers */
    distance: number;

    /** Trip duration in seconds */
    duration: number;

    /** Number of speed limit violations */
    violationCount: number;
}

/**
 * Request payload for starting a new trip.
 *
 * POST /api/trips
 */
export interface StartTripRequest {
    /** Optional notes about the trip */
    notes?: string;
}

/**
 * Response data from starting a new trip.
 *
 * Returned by POST /api/trips
 */
export interface StartTripResponse {
    /** Newly created trip with in_progress status */
    trip: Trip;
}

/**
 * Request payload for ending an active trip.
 *
 * PUT /api/trips/{id}
 */
export interface EndTripRequest {
    /** Optional notes to add/update for the trip */
    notes?: string;
}

/**
 * Response data from ending a trip.
 *
 * Returned by PUT /api/trips/{id}
 */
export interface EndTripResponse {
    /** Updated trip with completed status and calculated statistics */
    trip: Trip;
}

/**
 * Request payload for bulk inserting speed logs.
 *
 * POST /api/trips/{id}/speed-logs
 *
 * Used for syncing batched speed logs to the backend,
 * particularly important for offline sync functionality.
 */
export interface BulkSpeedLogsRequest {
    /** Array of speed log entries to insert */
    speed_logs: Array<{
        /** Speed in km/h */
        speed: number;
        /** ISO 8601 timestamp when speed was recorded */
        recorded_at: string;
    }>;
}

/**
 * Response data from bulk speed log insertion.
 *
 * Returned by POST /api/trips/{id}/speed-logs
 */
export interface BulkSpeedLogsResponse {
    /** Success message */
    message: string;

    /** Number of speed logs successfully created */
    created_count: number;

    /** Updated trip statistics after speed logs insertion */
    trip: {
        /** Trip ID */
        id: number;
        /** Recalculated maximum speed in km/h */
        max_speed: number;
        /** Recalculated average speed in km/h */
        average_speed: number;
        /** Recalculated total distance in km */
        total_distance: number;
        /** Recalculated violation count */
        violation_count: number;
        /** Timestamp of successful sync */
        synced_at: string;
    };
}

/**
 * Trip list query parameters.
 *
 * Used for filtering and paginating trip lists via GET /api/trips
 */
export interface TripListParams {
    /** Filter by specific user ID (supervisor/admin only) */
    user_id?: number;

    /** Filter by trip status */
    status?: TripStatus;

    /** Filter trips starting from this date (YYYY-MM-DD) */
    date_from?: string;

    /** Filter trips starting up to this date (YYYY-MM-DD) */
    date_to?: string;

    /** Number of results per page (default: 20) */
    per_page?: number;

    /** Page number for pagination (default: 1) */
    page?: number;
}

/**
 * Paginated trip list response.
 *
 * Returned by GET /api/trips
 */
export interface TripListResponse {
    /** Array of trips matching the query */
    data: Trip[];

    /** Pagination metadata */
    meta: {
        /** Current page number */
        current_page: number;
        /** Results per page */
        per_page: number;
        /** Total number of results */
        total: number;
        /** Total number of pages */
        last_page: number;
    };
}

/**
 * Employee summary for filter dropdown.
 *
 * Minimal user data for displaying employee selection
 * in supervisor trip filtering interface.
 */
export interface EmployeeSummary {
    /** Unique user identifier */
    id: number;
    /** Employee full name */
    name: string;
    /** Employee email address */
    email: string;
}

/**
 * Trip list query parameters for supervisors.
 *
 * Extends base TripListParams with additional filtering
 * and sorting options available to supervisors and admins.
 */
export interface SupervisorTripListParams extends TripListParams {
    /** Filter to show only trips with violations */
    violations_only?: boolean;

    /** Sort trips by field */
    sort_by?: 'started_at' | 'violation_count' | 'total_distance' | 'duration_seconds';

    /** Sort order direction */
    sort_order?: 'asc' | 'desc';
}
