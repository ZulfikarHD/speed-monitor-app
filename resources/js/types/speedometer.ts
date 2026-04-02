/**
 * Type definitions for Speedometer components.
 *
 * Provides TypeScript interfaces and types for speed gauge components,
 * including props, color schemes, and SVG coordinate calculations.
 */

/**
 * Props interface for SpeedGauge component.
 *
 * Defines all configurable properties for the speedometer gauge,
 * including speed data, limits, and visual presentation options.
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import type { SpeedGaugeProps } from '@/types/speedometer';
 *
 * const props: SpeedGaugeProps = {
 *   speed: 45,
 *   speedLimit: 60,
 *   maxSpeed: 120,
 *   size: 'lg'
 * };
 * </script>
 * ```
 */
export interface SpeedGaugeProps {
    /**
     * Current speed in kilometers per hour.
     *
     * WHY: Primary data point for gauge display.
     * Range: 0 to maxSpeed (typically 0-120 km/h)
     */
    speed: number;

    /**
     * Speed limit threshold in kilometers per hour.
     *
     * WHY: Determines color zones and violation detection.
     * Default: 60 km/h (Indonesia urban speed limit)
     */
    speedLimit?: number;

    /**
     * Maximum speed shown on gauge in kilometers per hour.
     *
     * WHY: Defines gauge scale range.
     * Default: 120 km/h (typical speedometer maximum)
     */
    maxSpeed?: number;

    /**
     * Gauge size preset.
     *
     * WHY: Responsive sizing for different layouts.
     * - sm: 192px (mobile compact)
     * - md: 256px mobile, 320px tablet (default)
     * - lg: 320px mobile, 384px tablet (prominent display)
     */
    size?: 'sm' | 'md' | 'lg';
}

/**
 * Color scheme for gauge states.
 *
 * Defines colors for different speed zones and UI elements.
 *
 * @example
 * ```ts
 * const colors: GaugeColors = {
 *   safe: 'rgb(34 197 94)',      // green-500
 *   warning: 'rgb(234 179 8)',    // yellow-500
 *   danger: 'rgb(239 68 68)',     // red-500
 *   background: 'rgb(229 231 235)' // gray-200
 * };
 * ```
 */
export interface GaugeColors {
    /**
     * Safe zone color (0-85% of speed limit).
     *
     * WHY: Green indicates safe driving speed.
     */
    safe: string;

    /**
     * Warning zone color (85-100% of speed limit).
     *
     * WHY: Yellow warns driver approaching limit.
     */
    warning: string;

    /**
     * Danger zone color (>100% of speed limit).
     *
     * WHY: Red indicates speed limit violation.
     */
    danger: string;

    /**
     * Background arc color.
     *
     * WHY: Gray shows full gauge range subtly.
     */
    background: string;
}

/**
 * Cartesian coordinates for SVG arc positioning.
 *
 * Used for calculating arc endpoints and marker positions
 * on the circular gauge.
 *
 * @example
 * ```ts
 * const markerPos: ArcCoordinates = {
 *   startX: 50,
 *   startY: 150,
 *   endX: 150,
 *   endY: 50,
 *   radius: 80
 * };
 * ```
 */
export interface ArcCoordinates {
    /**
     * Arc starting point X coordinate.
     */
    startX: number;

    /**
     * Arc starting point Y coordinate.
     */
    startY: number;

    /**
     * Arc ending point X coordinate.
     */
    endX: number;

    /**
     * Arc ending point Y coordinate.
     */
    endY: number;

    /**
     * Arc radius in SVG units.
     */
    radius: number;
}

/**
 * Speed zone thresholds for color coding.
 *
 * Defines speed ranges that determine gauge color.
 *
 * @example
 * ```ts
 * const zones: SpeedZones = {
 *   safe: { min: 0, max: 51 },
 *   warning: { min: 51, max: 60 },
 *   danger: { min: 60, max: Infinity }
 * };
 * ```
 */
export interface SpeedZones {
    /**
     * Safe speed range.
     */
    safe: {
        min: number;
        max: number;
    };

    /**
     * Warning speed range.
     */
    warning: {
        min: number;
        max: number;
    };

    /**
     * Danger speed range.
     */
    danger: {
        min: number;
        max: number;
    };
}

/**
 * Gauge animation configuration.
 *
 * Defines timing and easing for gauge animations.
 *
 * @example
 * ```ts
 * const animConfig: GaugeAnimationConfig = {
 *   arcTransitionMs: 500,
 *   colorTransitionMs: 500,
 *   textTransitionMs: 300,
 *   easing: 'ease-in-out'
 * };
 * ```
 */
export interface GaugeAnimationConfig {
    /**
     * Arc sweep animation duration in milliseconds.
     *
     * WHY: Smooth arc fill as speed changes.
     */
    arcTransitionMs: number;

    /**
     * Color transition duration in milliseconds.
     *
     * WHY: Smooth fade between color zones.
     */
    colorTransitionMs: number;

    /**
     * Text update animation duration in milliseconds.
     *
     * WHY: Smooth speed number updates.
     */
    textTransitionMs: number;

    /**
     * CSS easing function.
     *
     * WHY: Natural-looking acceleration/deceleration.
     */
    easing: 'linear' | 'ease' | 'ease-in' | 'ease-out' | 'ease-in-out';
}
