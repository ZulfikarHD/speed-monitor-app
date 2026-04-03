/**
 * Dashboard Type Definitions
 *
 * TypeScript interfaces for supervisor dashboard API data including overview
 * statistics, active trips monitoring, and violation tracking.
 */

/**
 * Complete dashboard overview response from backend.
 *
 * Contains all dashboard metrics for supervisor/admin monitoring including
 * today's summary, currently active trips, top violators, and average speed.
 */
export interface DashboardOverview {
    /** Today's summary statistics (total trips and violations) */
    today_summary: TodaySummary;

    /** Currently active trips with user info and duration */
    active_trips: ActiveTrip[];

    /** Top violators for today (max 5) */
    top_violators: TopViolator[];

    /** Average speed across all employees today (km/h) */
    average_speed: number;
}

/**
 * Today's summary statistics.
 *
 * Aggregated metrics for current day monitoring including total trips
 * and total violation count across all employees.
 */
export interface TodaySummary {
    /** Total number of trips started today */
    total_trips: number;

    /** Total number of speed violations recorded today */
    violations_count: number;
}

/**
 * Active trip information.
 *
 * Represents a currently in-progress trip with employee details and
 * real-time duration tracking for supervisor monitoring.
 */
export interface ActiveTrip {
    /** Trip ID */
    id: number;

    /** Employee information */
    user: {
        /** Employee full name */
        name: string;

        /** Employee email address */
        email: string;
    };

    /** Trip start timestamp (ISO8601 format) */
    started_at: string;

    /** Trip duration in seconds (calculated from started_at) */
    duration_seconds: number;
}

/**
 * Top violator information.
 *
 * Employee with violation count for leaderboard display, showing
 * employees who exceeded speed limits most frequently today.
 */
export interface TopViolator {
    /** Employee information */
    user: {
        /** Employee full name */
        name: string;

        /** Employee email address */
        email: string;
    };

    /** Number of speed violations for this employee today */
    violation_count: number;
}
