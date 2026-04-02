/**
 * Chart Type Definitions
 *
 * Defines TypeScript interfaces for Chart.js configuration used in speed
 * visualization components. These types ensure type safety when working with
 * speed log charts and violation markers.
 *
 * @example
 * ```ts
 * import type { SpeedChartProps, ChartPoint } from '@/types/chart';
 *
 * const props: SpeedChartProps = {
 *   speedLogs: [...],
 *   speedLimit: 60,
 *   isLoading: false
 * };
 * ```
 */

import type { SpeedLog } from './trip';

/**
 * Chart.js dataset configuration for speed visualization.
 *
 * Represents a single dataset in the Chart.js configuration, typically used
 * for the speed line chart or violation scatter plot overlay.
 */
export interface SpeedChartDataset {
    /** Dataset label shown in legend */
    label: string;

    /** Array of speed values (km/h) for Y-axis */
    data: number[];

    /** Line/border color (hex or rgba) */
    borderColor: string;

    /** Fill color under line (hex or rgba, supports gradients) */
    backgroundColor: string;

    /** Line curve tension (0=straight, 0.4=smooth curve) */
    tension: number;

    /** Whether to fill area under the line */
    fill: boolean;
}

/**
 * Chart point representing a single speed measurement.
 *
 * Used for plotting individual data points on the speed chart with
 * violation status for color coding.
 */
export interface ChartPoint {
    /** Time label (HH:MM:SS format) for X-axis */
    x: string;

    /** Speed in km/h for Y-axis */
    y: number;

    /** Whether this measurement exceeded the speed limit */
    isViolation: boolean;
}

/**
 * Speed chart component props.
 *
 * Props interface for SpeedChart.vue component that displays speed logs
 * as a line chart with violation markers and speed limit reference line.
 */
export interface SpeedChartProps {
    /** Array of speed log measurements to display */
    speedLogs: SpeedLog[];

    /** Speed limit threshold for reference line (km/h) */
    speedLimit: number;

    /** Whether chart is in loading state (shows skeleton) */
    isLoading?: boolean;
}
