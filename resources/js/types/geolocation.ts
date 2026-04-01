/**
 * Geolocation Type Definitions
 *
 * Defines TypeScript interfaces for geolocation tracking functionality.
 * These types provide type safety for GPS-based speed tracking with proper
 * error handling and permission management.
 *
 * @example
 * ```ts
 * import type { GeolocationState, SpeedWatchCallback } from '@/types/geolocation';
 *
 * const callback: SpeedWatchCallback = (speed, state) => {
 *   console.log(`Current speed: ${speed} km/h`);
 *   console.log(`Accuracy: ${state.accuracy} meters`);
 * };
 * ```
 */

/**
 * Geolocation error codes with descriptive messages.
 *
 * Maps browser Geolocation API errors to user-friendly error states.
 */
export interface GeolocationError {
    /**
     * Error type identifier.
     *
     * - PERMISSION_DENIED: User denied location permission
     * - POSITION_UNAVAILABLE: GPS signal unavailable
     * - TIMEOUT: Location request timed out
     * - NOT_SUPPORTED: Browser doesn't support Geolocation API
     */
    code:
        | 'PERMISSION_DENIED'
        | 'POSITION_UNAVAILABLE'
        | 'TIMEOUT'
        | 'NOT_SUPPORTED';

    /** Human-readable error message for display to user */
    message: string;
}

/**
 * Complete geolocation state for speed tracking.
 *
 * Contains all relevant GPS data, tracking status, and error information.
 * All values are nullable to handle cases where GPS data is unavailable.
 */
export interface GeolocationState {
    /** Current speed in kilometers per hour (converted from m/s) */
    speed: number | null;

    /** GPS accuracy in meters (lower is better) */
    accuracy: number | null;

    /** Current latitude in decimal degrees */
    latitude: number | null;

    /** Current longitude in decimal degrees */
    longitude: number | null;

    /**
     * Direction of travel in degrees (0-360).
     * 0 = North, 90 = East, 180 = South, 270 = West
     */
    heading: number | null;

    /** Unix timestamp (milliseconds) of last GPS update */
    timestamp: number | null;

    /** Whether GPS tracking is currently active */
    isTracking: boolean;

    /** Current error state, null if no error */
    error: GeolocationError | null;

    /**
     * Browser location permission status.
     *
     * - granted: Permission granted, can access GPS
     * - denied: Permission denied by user
     * - prompt: Permission not yet requested
     * - unsupported: Browser doesn't support Geolocation API
     */
    permissionStatus: 'granted' | 'denied' | 'prompt' | 'unsupported';
}

/**
 * Callback function type for speed tracking.
 *
 * Called whenever GPS speed data updates during active tracking.
 * Receives current speed in km/h and complete geolocation state.
 *
 * @param speed - Current speed in kilometers per hour
 * @param state - Complete geolocation state with GPS data
 *
 * @example
 * ```ts
 * const onSpeedUpdate: SpeedWatchCallback = (speed, state) => {
 *   if (speed > 60) {
 *     console.warn('Speed limit exceeded!');
 *   }
 *   console.log(`Lat: ${state.latitude}, Lng: ${state.longitude}`);
 * };
 * ```
 */
export type SpeedWatchCallback = (
    speed: number,
    state: GeolocationState,
) => void;

/**
 * Permission request result.
 *
 * Returned by requestPermission() method to indicate permission status.
 */
export interface PermissionResult {
    /** Whether permission was granted */
    granted: boolean;

    /** Current permission status */
    status: 'granted' | 'denied' | 'prompt' | 'unsupported';

    /** Error message if permission was denied */
    error?: string;
}
