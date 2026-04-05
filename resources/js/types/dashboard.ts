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
 * today's summary, historical trends, currently active trips, top violators,
 * average speed, employee summary, and recent alerts.
 */
export interface DashboardOverview {
    /** Today's summary statistics (total trips and violations) */
    today_summary: TodaySummary;

    /** Yesterday's summary for trend comparison */
    yesterday_summary: TodaySummary;

    /** Trend indicators (percentage changes from yesterday) */
    trends: TrendIndicators;

    /** Currently active trips with user info and duration */
    active_trips: ActiveTrip[];

    /** Top violators for today (max 5) */
    top_violators: TopViolator[];

    /** Average speed across all employees today (km/h) */
    average_speed: number;

    /** Employee summary statistics */
    employee_summary: EmployeeSummary;

    /** Recent high-violation alerts from last hour */
    recent_alerts: RecentAlert[];
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
 * Trend indicators for dashboard metrics.
 *
 * Percentage changes comparing today's metrics against yesterday's
 * for at-a-glance trend visualization (positive = increase, negative = decrease).
 */
export interface TrendIndicators {
    /** Percentage change in total trips (today vs yesterday) */
    trips_change: number;

    /** Percentage change in violations (today vs yesterday) */
    violations_change: number;
}

/**
 * Employee summary statistics.
 *
 * Aggregated employee metrics including total employee count,
 * active employees today, and top performer identification.
 */
export interface EmployeeSummary {
    /** Total number of employees in the system */
    total_employees: number;

    /** Number of employees with trips today */
    active_today: number;

    /** Top performer by trip count today (null if no trips) */
    top_performer: {
        /** Employee name */
        name: string;

        /** Number of trips completed today */
        trip_count: number;
    } | null;
}

/**
 * Recent alert for high-violation trips.
 *
 * Alert entry for trips with high violation counts from the last hour,
 * requiring supervisor attention and potential intervention.
 */
export interface RecentAlert {
    /** Trip ID */
    id: number;

    /** Employee information */
    user: {
        /** Employee full name */
        name: string;

        /** Employee email address */
        email: string;
    };

    /** Number of violations in this trip */
    violation_count: number;

    /** Trip start timestamp (ISO8601 format) */
    started_at: string;
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

/**
 * Violation leaderboard entry showing employee violation statistics.
 *
 * Complete ranking entry for violation leaderboard page, including employee
 * information, violation counts, trip statistics, and calculated rates for
 * comprehensive driver compliance monitoring.
 */
export interface ViolationLeaderboardEntry {
    /** Leaderboard rank (1-indexed) */
    rank: number;

    /** Employee information */
    user: {
        /** Unique user identifier */
        id: number;

        /** Employee full name */
        name: string;

        /** Employee email address */
        email: string;
    };

    /** Total violation count in period */
    violation_count: number;

    /** Total trips in period */
    total_trips: number;

    /** Violation rate (violations per trip) */
    violation_rate: number;
}

/**
 * Leaderboard page filter state.
 *
 * Date range filters for violation leaderboard, controlling which
 * time period's data is displayed in the ranking.
 */
export interface LeaderboardFilters {
    /** Start date filter (YYYY-MM-DD format) */
    date_from: string;

    /** End date filter (YYYY-MM-DD format) */
    date_to: string;
}
