/**
 * Statistics Type Definitions
 *
 * TypeScript interfaces for employee statistics API data including summary metrics,
 * chart data points, and period information.
 */

/**
 * User statistics response from backend.
 *
 * Contains all statistics data for displaying employee performance metrics
 * including summary totals and chart visualization data.
 */
export interface UserStatistics {
    /** Summary statistics (total trips, distance, speed, violations) */
    summary: StatisticsSummary;

    /** Chart data for visualizations */
    charts: {
        /** Trip count over time (bar chart data) */
        trips_over_time: ChartDataPoint[];

        /** Violation count over time (line chart data) */
        violations_over_time: ChartDataPoint[];
    };

    /** Period information (date range and label) */
    period: PeriodInfo;
}

/**
 * Summary statistics for the selected period.
 *
 * Aggregated metrics showing totals and averages across all trips.
 */
export interface StatisticsSummary {
    /** Total number of completed trips */
    total_trips: number;

    /** Total distance traveled in kilometers */
    total_distance: number;

    /** Average speed across all trips in km/h */
    average_speed: number;

    /** Total number of speed limit violations */
    violation_count: number;
}

/**
 * Single data point for chart visualization.
 *
 * Represents a count value for a specific date.
 * Used for both trips over time and violations over time charts.
 */
export interface ChartDataPoint {
    /** Date string in YYYY-MM-DD or YYYY-MM format */
    date: string;

    /** Count value (trips or violations) */
    count: number;
}

/**
 * Period information for the selected time range.
 *
 * Provides date boundaries and human-readable label for display.
 */
export interface PeriodInfo {
    /** Start date of period (YYYY-MM-DD) */
    start: string;

    /** End date of period (YYYY-MM-DD) */
    end: string;

    /** Human-readable period label (e.g., "March 2026", "Week 12, 2026") */
    label: string;
}

/**
 * Period selector options.
 *
 * Available time period filters for statistics view.
 */
export type Period = 'week' | 'month' | 'year' | 'custom';

/**
 * Props for StatCard component.
 */
export interface StatCardProps {
    /** Card title/label */
    title: string;

    /** Numeric value to display */
    value: number;

    /** Unit of measurement (e.g., "trips", "km", "km/h") */
    unit: string;

    /** Lucide icon name for visual identification (e.g., 'car', 'gauge', 'zap', 'shield-alert') */
    icon: string;

    /** Color variant for card styling */
    color: 'blue' | 'green' | 'purple' | 'red' | 'orange';
}

/**
 * Props for PeriodSelector component.
 */
export interface PeriodSelectorProps {
    /** Currently selected period */
    modelValue: Period;
}

/**
 * Props for TripsChart component (Bar chart).
 */
export interface TripsChartProps {
    /** Chart data points */
    data: ChartDataPoint[];

    /** Period type for X-axis formatting */
    period: Period;

    /** Loading state */
    isLoading?: boolean;
}

/**
 * Props for ViolationsChart component (Line chart).
 */
export interface ViolationsChartProps {
    /** Chart data points */
    data: ChartDataPoint[];

    /** Period type for X-axis formatting */
    period: Period;

    /** Loading state */
    isLoading?: boolean;
}
