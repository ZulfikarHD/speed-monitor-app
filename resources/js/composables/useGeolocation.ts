/**
 * Geolocation Composable for Speed Tracking
 *
 * Provides GPS-based speed tracking functionality with proper error handling,
 * permission management, and km/h conversion. Wraps VueUse's useGeolocation
 * to provide speed-specific features for the speedometer interface.
 *
 * Features:
 * - Real-time speed tracking in km/h
 * - Permission request and status management
 * - Error handling with user-friendly messages
 * - Start/stop tracking controls
 * - Reactive state with Vue composition API
 *
 * @example
 * ```ts
 * // Basic usage in a Vue component
 * import { useGeolocation } from '@/composables/useGeolocation';
 *
 * const {
 *   getCurrentSpeed,
 *   watchSpeed,
 *   stopTracking,
 *   state,
 *   requestPermission
 * } = useGeolocation();
 *
 * // Request permission first
 * const result = await requestPermission();
 * if (result.granted) {
 *   // Start tracking with callback
 *   watchSpeed((speed, geoState) => {
 *     console.log(`Current speed: ${speed} km/h`);
 *   });
 * }
 *
 * // Stop tracking when done
 * stopTracking();
 * ```
 *
 * @example
 * ```ts
 * // Integration with trip management
 * const { watchSpeed, stopTracking } = useGeolocation();
 *
 * function startTrip() {
 *   watchSpeed((speed, state) => {
 *     // Log speed to trip store every update
 *     tripStore.addSpeedLog({
 *       speed,
 *       timestamp: state.timestamp,
 *       accuracy: state.accuracy
 *     });
 *   });
 * }
 *
 * function endTrip() {
 *   stopTracking();
 * }
 * ```
 */

import { useGeolocation as useVueGeolocation } from '@vueuse/core';
import { computed, ref, watch   } from 'vue';
import type {Ref, WatchStopHandle} from 'vue';

import type {
    GeolocationError,
    GeolocationState,
    PermissionResult,
    SpeedWatchCallback,
} from '@/types/geolocation';

/**
 * Composable for GPS-based speed tracking.
 *
 * Provides methods to track user speed using browser Geolocation API,
 * with automatic conversion from m/s to km/h and comprehensive error handling.
 *
 * @returns Object containing tracking methods and reactive state
 */
export function useGeolocation() {
    // ========================================================================
    // VueUse Integration
    // ========================================================================

    /**
     * Initialize VueUse geolocation with optimized settings.
     *
     * WHY: immediate=false prevents auto-start, allowing explicit permission request.
     * WHY: enableHighAccuracy=true provides better speed accuracy at cost of battery.
     * WHY: maximumAge=0 ensures fresh GPS data for real-time tracking.
     */
    const {
        coords,
        locatedAt,
        error: geoError,
        resume,
        pause,
    } = useVueGeolocation({
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
        immediate: false,
    });

    // ========================================================================
    // Reactive State
    // ========================================================================

    /** Whether GPS tracking is currently active */
    const isTracking: Ref<boolean> = ref(false);

    /** Current error state, null if no error */
    const error: Ref<GeolocationError | null> = ref(null);

    /**
     * Current location permission status.
     *
     * WHY: Track permission separately from error state for better UX.
     * Allows showing permission prompts before attempting to track.
     */
    const permissionStatus: Ref<
        'granted' | 'denied' | 'prompt' | 'unsupported'
    > = ref('prompt');

    /** Active speed watch callback, null when not watching */
    let speedWatchCallback: SpeedWatchCallback | null = null;

    /** Watch stop handle for cleanup */
    let speedWatchStopHandle: WatchStopHandle | null = null;

    // ========================================================================
    // Computed Properties
    // ========================================================================

    /**
     * Current speed in kilometers per hour.
     *
     * WHY: Geolocation API returns speed in meters per second (m/s).
     * WHY: 1 m/s = 3.6 km/h (conversion factor: 60 * 60 / 1000)
     * WHY: Round to 1 decimal place for display accuracy.
     */
    const speedKmh = computed<number>(() => {
        if (!coords.value.speed || coords.value.speed < 0) {
            return 0;
        }

        // Convert m/s to km/h and round to 1 decimal
        return Math.round(coords.value.speed * 3.6 * 10) / 10;
    });

    /**
     * Complete geolocation state object.
     *
     * Combines all GPS data, tracking status, and error information
     * into a single reactive state object for easy access.
     */
    const state = computed<GeolocationState>(() => ({
        speed: speedKmh.value,
        accuracy: coords.value.accuracy ?? null,
        latitude: coords.value.latitude,
        longitude: coords.value.longitude,
        heading: coords.value.heading ?? null,
        timestamp: locatedAt.value ?? null,
        isTracking: isTracking.value,
        error: error.value,
        permissionStatus: permissionStatus.value,
    }));

    // ========================================================================
    // Error Handling
    // ========================================================================

    /**
     * Map Geolocation API error codes to user-friendly messages.
     *
     * WHY: Browser error codes (1, 2, 3) are not user-friendly.
     * WHY: Provide specific guidance for each error type.
     *
     * @param geoErrorCode - GeolocationPositionError code (1=PERMISSION_DENIED, 2=POSITION_UNAVAILABLE, 3=TIMEOUT)
     * @returns Structured error object with code and message
     */
    function mapGeolocationError(
        geoErrorCode: number,
    ): GeolocationError | null {
        switch (geoErrorCode) {
            case 1: // PERMISSION_DENIED
                return {
                    code: 'PERMISSION_DENIED',
                    message:
                        'Akses lokasi ditolak. Mohon aktifkan izin lokasi di pengaturan browser Anda.',
                };
            case 2: // POSITION_UNAVAILABLE
                return {
                    code: 'POSITION_UNAVAILABLE',
                    message:
                        'Sinyal GPS tidak tersedia. Pastikan GPS aktif dan Anda berada di area terbuka.',
                };
            case 3: // TIMEOUT
                return {
                    code: 'TIMEOUT',
                    message:
                        'Waktu tunggu habis saat mendapatkan lokasi. Silakan coba lagi.',
                };
            default:
                return null;
        }
    }

    /**
     * Watch for VueUse geolocation errors and map them to our error state.
     *
     * WHY: VueUse exposes errors reactively, need to transform them for our use case.
     */
    watch(
        geoError,
        (newError) => {
            if (newError) {
                error.value = mapGeolocationError(newError.code);

                // Update permission status if permission denied
                if (newError.code === 1) {
                    permissionStatus.value = 'denied';
                }
            } else {
                error.value = null;
            }
        },
        { immediate: true },
    );

    // ========================================================================
    // Public Methods
    // ========================================================================

    /**
     * Get current speed in kilometers per hour.
     *
     * Returns the most recent speed reading. If tracking is not active
     * or GPS data is unavailable, returns 0.
     *
     * @returns Current speed in km/h, or 0 if unavailable
     *
     * @example
     * ```ts
     * const { getCurrentSpeed } = useGeolocation();
     * const speed = getCurrentSpeed();
     * console.log(`Current speed: ${speed} km/h`);
     * ```
     */
    function getCurrentSpeed(): number {
        return speedKmh.value;
    }

    /**
     * Start continuous speed tracking with callback.
     *
     * Resumes GPS tracking and calls the provided callback function
     * whenever speed data updates. The callback receives both the
     * current speed in km/h and the complete geolocation state.
     *
     * WHY: Callback pattern allows flexible integration with trip logging,
     * violation detection, and UI updates.
     *
     * @param callback - Function called on each speed update
     *
     * @example
     * ```ts
     * watchSpeed((speed, state) => {
     *   console.log(`Speed: ${speed} km/h`);
     *   console.log(`Accuracy: ${state.accuracy} meters`);
     *
     *   // Check for violations
     *   if (speed > 60) {
     *     alert('Speed limit exceeded!');
     *   }
     * });
     * ```
     */
    function watchSpeed(callback: SpeedWatchCallback): void {
        // Check browser support
        if (!('geolocation' in navigator)) {
            error.value = {
                code: 'NOT_SUPPORTED',
                message:
                    'Geolocation tidak didukung oleh browser Anda. Silakan gunakan browser yang lebih baru.',
            };
            permissionStatus.value = 'unsupported';

            return;
        }

        // Store callback for access in watch handler
        speedWatchCallback = callback;

        // Clear any existing error
        error.value = null;

        // Start GPS tracking
        isTracking.value = true;
        resume();

        /**
         * Watch for speed changes and execute callback.
         *
         * WHY: Watch speedKmh instead of coords.speed to automatically
         * get converted km/h values and proper null handling.
         */
        speedWatchStopHandle = watch(
            speedKmh,
            (newSpeed) => {
                if (speedWatchCallback && isTracking.value) {
                    speedWatchCallback(newSpeed, state.value);
                }
            },
            { immediate: true },
        );
    }

    /**
     * Stop GPS tracking and cleanup resources.
     *
     * Pauses the geolocation watcher, stops executing callbacks,
     * and cleans up watch handlers. Does not clear error state
     * or permission status to allow UI to show last known state.
     *
     * WHY: Stopping tracking but preserving state allows users to
     * see why tracking stopped (e.g., permission denied).
     *
     * @example
     * ```ts
     * const { watchSpeed, stopTracking } = useGeolocation();
     *
     * // Start tracking
     * watchSpeed((speed) => console.log(speed));
     *
     * // Stop tracking when trip ends
     * setTimeout(() => {
     *   stopTracking();
     *   console.log('Tracking stopped');
     * }, 60000); // Stop after 1 minute
     * ```
     */
    function stopTracking(): void {
        isTracking.value = false;
        pause();

        // Cleanup watch handler
        if (speedWatchStopHandle) {
            speedWatchStopHandle();
            speedWatchStopHandle = null;
        }

        // Clear callback reference
        speedWatchCallback = null;
    }

    /**
     * Request location permission from user.
     *
     * Explicitly requests location permission before starting tracking.
     * This provides better UX than letting permission request happen
     * automatically on first GPS access.
     *
     * WHY: Explicit permission request allows showing explanation UI
     * before browser's permission prompt appears.
     *
     * @returns Promise resolving to permission result
     *
     * @example
     * ```ts
     * const { requestPermission, watchSpeed } = useGeolocation();
     *
     * // Request permission with user context
     * const result = await requestPermission();
     *
     * if (result.granted) {
     *   console.log('Permission granted, starting tracking');
     *   watchSpeed((speed) => console.log(speed));
     * } else {
     *   console.error('Permission denied:', result.error);
     *   alert('Tidak dapat melacak kecepatan tanpa izin lokasi');
     * }
     * ```
     */
    async function requestPermission(): Promise<PermissionResult> {
        // Check browser support first
        if (!('geolocation' in navigator)) {
            error.value = {
                code: 'NOT_SUPPORTED',
                message:
                    'Geolocation tidak didukung oleh browser Anda. Silakan gunakan browser yang lebih baru.',
            };
            permissionStatus.value = 'unsupported';

            return {
                granted: false,
                status: 'unsupported',
                error: error.value.message,
            };
        }

        /**
         * Request permission by attempting to get current position.
         *
         * WHY: No direct permission request API exists for Geolocation.
         * WHY: First position request triggers browser permission prompt.
         */
        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                // Success: Permission granted
                () => {
                    permissionStatus.value = 'granted';
                    error.value = null;

                    resolve({
                        granted: true,
                        status: 'granted',
                    });
                },
                // Error: Permission denied or other error
                (positionError) => {
                    const mappedError = mapGeolocationError(
                        positionError.code,
                    );
                    error.value = mappedError;

                    // Update permission status
                    if (positionError.code === 1) {
                        permissionStatus.value = 'denied';
                    }

                    resolve({
                        granted: false,
                        status: permissionStatus.value,
                        error: mappedError?.message,
                    });
                },
                // Options for permission request
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0,
                },
            );
        });
    }

    // ========================================================================
    // Return Public API
    // ========================================================================

    return {
        // Methods
        getCurrentSpeed,
        watchSpeed,
        stopTracking,
        requestPermission,

        // Reactive State
        state,
        isTracking,
        error,
        permissionStatus,

        // Computed Values
        speedKmh,
    };
}
