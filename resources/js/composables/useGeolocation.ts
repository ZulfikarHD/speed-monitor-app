/**
 * Geolocation Composable for Speed Tracking (Singleton)
 *
 * Shared GPS-based speed tracking instance ensuring all consumers
 * (Speedometer, TripControls) read from the same GPS data source.
 *
 * Features:
 * - Singleton pattern via effectScope (all components share one GPS watcher)
 * - EMA smoothing to reduce GPS noise
 * - Accuracy-based filtering (rejects readings > 25m accuracy)
 * - Minimum speed threshold (< 0.5 m/s treated as stationary)
 * - Cross-validation of API speed vs position-derived speed
 * - DEV: Mock GPS mode for testing without physical movement
 */

import { useGeolocation as useVueGeolocation } from '@vueuse/core';
import { computed, effectScope, ref, watch } from 'vue';
import type { Ref, WatchStopHandle } from 'vue';

import type {
    GeolocationError,
    GeolocationState,
    PermissionResult,
    SpeedWatchCallback,
} from '@/types/geolocation';

// ========================================================================
// Singleton Management
// ========================================================================

let sharedInstance: ReturnType<typeof createGeolocationInstance> | null = null;

function haversineQuick(lat1: number, lon1: number, lat2: number, lon2: number): number {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) ** 2;

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function createGeolocationInstance() {
    const scope = effectScope(true);

    return scope.run(() => {
        // ========================================================================
        // VueUse Integration
        // ========================================================================

        const {
            coords,
            locatedAt,
            error: geoError,
            resume,
            pause,
        } = useVueGeolocation({
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 1000,
            immediate: false,
        });

        // ========================================================================
        // Reactive State
        // ========================================================================

        const isTracking: Ref<boolean> = ref(false);
        const error: Ref<GeolocationError | null> = ref(null);
        const permissionStatus: Ref<
            'granted' | 'denied' | 'prompt' | 'unsupported'
        > = ref('prompt');

        let speedWatchCallback: SpeedWatchCallback | null = null;
        let speedWatchStopHandle: WatchStopHandle | null = null;

        // ========================================================================
        // Speed Smoothing
        // ========================================================================

        const smoothedSpeedMps = ref(0);

        const EMA_ALPHA = 0.3;
        const MIN_SPEED_MPS = 0.5;
        const MAX_ACCURACY_FOR_SPEED = 25;

        let lastGpsTime = 0;
        let lastGpsLat = 0;
        let lastGpsLon = 0;
        let hasLastGpsPosition = false;

        watch(locatedAt, (timestamp) => {
            if (!isTracking.value || !timestamp) {
return;
}

            const lat = coords.value.latitude;
            const lon = coords.value.longitude;
            const acc = coords.value.accuracy;
            const rawSpeed = coords.value.speed;

            if (lat === Infinity || lon === Infinity) {
return;
}

            if (acc && acc > MAX_ACCURACY_FOR_SPEED) {
return;
}

            let speedMps = rawSpeed != null && rawSpeed >= 0 ? rawSpeed : 0;

            if (hasLastGpsPosition) {
                const dt = (timestamp - lastGpsTime) / 1000;

                if (dt > 0.5 && dt < 30) {
                    const dist = haversineQuick(lastGpsLat, lastGpsLon, lat, lon);
                    const calcSpeed = dist / dt;

                    if (speedMps > 0 && calcSpeed > 0) {
                        const ratio = speedMps / calcSpeed;

                        if (ratio > 3 || ratio < 0.33) {
                            speedMps = Math.min(speedMps, calcSpeed);
                        }
                    }

                    if (speedMps === 0 && calcSpeed > MIN_SPEED_MPS) {
                        speedMps = calcSpeed;
                    }
                }
            }

            lastGpsTime = timestamp;
            lastGpsLat = lat;
            lastGpsLon = lon;
            hasLastGpsPosition = true;

            if (speedMps < MIN_SPEED_MPS) {
                speedMps = 0;
            }

            if (smoothedSpeedMps.value === 0 && speedMps > 0) {
                smoothedSpeedMps.value = speedMps;
            } else if (speedMps === 0) {
                smoothedSpeedMps.value *= 0.5;

                if (smoothedSpeedMps.value < 0.1) {
                    smoothedSpeedMps.value = 0;
                }
            } else {
                smoothedSpeedMps.value =
                    EMA_ALPHA * speedMps + (1 - EMA_ALPHA) * smoothedSpeedMps.value;
            }
        });

        // ========================================================================
        // Mock GPS (Dev Mode)
        // ========================================================================

        const isMockMode = ref(false);
        const mockSpeedKmh = ref(0);
        const mockAccuracy = ref(5);
        let mockInterval: ReturnType<typeof setInterval> | null = null;
        let mockLat = -6.2088;
        let mockLon = 106.8456;
        let mockHeading = 45;

        function injectMockPosition(): void {
            const speedMps = mockSpeedKmh.value / 3.6;
            const distance = speedMps * 1;

            const headingRad = mockHeading * Math.PI / 180;
            mockLat += (distance * Math.cos(headingRad)) / 111320;
            mockLon += (distance * Math.sin(headingRad)) / (111320 * Math.cos(mockLat * Math.PI / 180));

            // Gentle heading drift for realism
            mockHeading += (Math.random() - 0.5) * 3;

            const mockCoords = {
                latitude: mockLat,
                longitude: mockLon,
                altitude: null,
                accuracy: mockAccuracy.value,
                altitudeAccuracy: null,
                heading: mockHeading,
                speed: speedMps,
            };

            // Inject into VueUse refs — triggers the locatedAt watcher
            (coords as Ref<any>).value = mockCoords;
            locatedAt.value = Date.now();
        }

        function startMockInterval(): void {
            if (mockInterval) {
clearInterval(mockInterval);
}

            mockInterval = setInterval(injectMockPosition, 1000);
        }

        function stopMockInterval(): void {
            if (mockInterval) {
                clearInterval(mockInterval);
                mockInterval = null;
            }
        }

        function enableMockGps(): void {
            isMockMode.value = true;
            mockLat = -6.2088;
            mockLon = 106.8456;
            mockHeading = 45;
        }

        function disableMockGps(): void {
            isMockMode.value = false;
            stopMockInterval();
        }

        function setMockSpeed(kmh: number): void {
            mockSpeedKmh.value = Math.max(0, kmh);
        }

        function setMockAccuracy(acc: number): void {
            mockAccuracy.value = Math.max(1, acc);
        }

        // ========================================================================
        // Computed Properties
        // ========================================================================

        const speedKmh = computed<number>(() => {
            return Math.round(smoothedSpeedMps.value * 3.6 * 10) / 10;
        });

        const speedMps = computed<number>(() => {
            return smoothedSpeedMps.value;
        });

        const accuracy = computed<number | null>(() => {
            const acc = coords.value.accuracy;

            return acc !== undefined && acc !== 0 ? acc : null;
        });

        const state = computed<GeolocationState>(() => ({
            speed: speedKmh.value,
            accuracy: accuracy.value,
            latitude: coords.value.latitude === Infinity ? null : coords.value.latitude,
            longitude: coords.value.longitude === Infinity ? null : coords.value.longitude,
            heading: coords.value.heading ?? null,
            timestamp: locatedAt.value ?? null,
            isTracking: isTracking.value,
            error: error.value,
            permissionStatus: permissionStatus.value,
        }));

        // ========================================================================
        // Error Handling
        // ========================================================================

        function mapGeolocationError(geoErrorCode: number): GeolocationError | null {
            switch (geoErrorCode) {
                case 1:
                    return {
                        code: 'PERMISSION_DENIED',
                        message:
                            'Akses lokasi ditolak. Mohon aktifkan izin lokasi di pengaturan browser Anda.',
                    };
                case 2:
                    return {
                        code: 'POSITION_UNAVAILABLE',
                        message:
                            'Sinyal GPS tidak tersedia. Pastikan GPS aktif dan Anda berada di area terbuka.',
                    };
                case 3:
                    return {
                        code: 'TIMEOUT',
                        message:
                            'Waktu tunggu habis saat mendapatkan lokasi. Silakan coba lagi.',
                    };
                default:
                    return null;
            }
        }

        watch(
            geoError,
            (newError) => {
                if (newError) {
                    error.value = mapGeolocationError(newError.code);

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

        function getCurrentSpeed(): number {
            return speedKmh.value;
        }

        function watchSpeed(callback: SpeedWatchCallback): void {
            if (!('geolocation' in navigator) && !isMockMode.value) {
                error.value = {
                    code: 'NOT_SUPPORTED',
                    message:
                        'Geolocation tidak didukung oleh browser Anda. Silakan gunakan browser yang lebih baru.',
                };
                permissionStatus.value = 'unsupported';

                return;
            }

            if (speedWatchStopHandle) {
                speedWatchStopHandle();
                speedWatchStopHandle = null;
            }

            speedWatchCallback = callback;
            error.value = null;
            isTracking.value = true;

            smoothedSpeedMps.value = 0;
            hasLastGpsPosition = false;

            if (isMockMode.value) {
                startMockInterval();
            } else {
                resume();
            }

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

        function stopTracking(): void {
            isTracking.value = false;

            if (isMockMode.value) {
                stopMockInterval();
            } else {
                pause();
            }

            smoothedSpeedMps.value = 0;
            hasLastGpsPosition = false;

            if (speedWatchStopHandle) {
                speedWatchStopHandle();
                speedWatchStopHandle = null;
            }

            speedWatchCallback = null;
        }

        async function requestPermission(): Promise<PermissionResult> {
            if (isMockMode.value) {
                permissionStatus.value = 'granted';
                error.value = null;

                return { granted: true, status: 'granted' };
            }

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

            return new Promise((resolve) => {
                navigator.geolocation.getCurrentPosition(
                    () => {
                        permissionStatus.value = 'granted';
                        error.value = null;
                        resolve({ granted: true, status: 'granted' });
                    },
                    (positionError) => {
                        const mappedError = mapGeolocationError(positionError.code);
                        error.value = mappedError;

                        if (positionError.code === 1) {
                            permissionStatus.value = 'denied';
                        }

                        resolve({
                            granted: false,
                            status: permissionStatus.value,
                            error: mappedError?.message,
                        });
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
                );
            });
        }

        // ========================================================================
        // Return Public API
        // ========================================================================

        return {
            getCurrentSpeed,
            watchSpeed,
            stopTracking,
            requestPermission,

            state,
            isTracking,
            error,
            permissionStatus,

            speedKmh,
            speedMps,
            accuracy,
            coords,
            locatedAt,

            // Mock GPS (dev)
            isMockMode,
            mockSpeedKmh,
            mockAccuracy,
            enableMockGps,
            disableMockGps,
            setMockSpeed,
            setMockAccuracy,
        };
    })!;
}

/**
 * Shared geolocation composable (singleton).
 *
 * All consumers get the same GPS instance so Speedometer and TripControls
 * read from a single source of truth.
 */
export function useGeolocation() {
    if (!sharedInstance) {
        sharedInstance = createGeolocationInstance();
    }

    return sharedInstance;
}
