<!--
TripControls Demo Page

Testing and demonstration page for the TripControls component.
Provides integrated testing with SpeedGauge and real GPS tracking.

Usage: Navigate to this page during development to test the component.
Route: /test/trip-controls-demo

Testing Scenarios:
1. Start Trip Flow
   - GPS permission request
   - API call to create trip
   - Speed tracking starts
   - Duration display updates every second

2. Active Trip
   - Speed logs buffered (check console)
   - Auto-sync every 10 logs (50 seconds)
   - Duration updates in real-time
   - Speed gauge shows current speed

3. Stop Trip Flow
   - Confirmation dialog appears
   - Remaining logs synced
   - Trip ended via API
   - GPS tracking stops

4. Error Scenarios
   - Permission denied
   - Network errors during start/end
   - Sync failures (check retry logic)

5. Loading States
   - Buttons disabled during operations
   - Loading spinners visible
   - Can't start multiple trips

6. Responsive Design
   - Mobile (portrait)
   - Tablet
   - Desktop
-->

<script setup lang="ts">
import { computed, ref } from 'vue';

import SpeedGauge from '@/components/speedometer/SpeedGauge.vue';
import TripControls from '@/components/speedometer/TripControls.vue';
import { useGeolocation } from '@/composables/useGeolocation';
import { useTripStore } from '@/stores/trip';

// ========================================================================
// Store and Composable Integration
// ========================================================================

const tripStore = useTripStore();
const { speedKmh, state: geoState, isTracking } = useGeolocation();

// ========================================================================
// Local State
// ========================================================================

/** Show debug information */
const showDebug = ref<boolean>(true);

// ========================================================================
// Computed Properties
// ========================================================================

/** Current speed zone for display */
const speedZone = computed(() => {
    if (speedKmh.value > 60) {
        return { name: 'VIOLATION', color: 'text-red-600', icon: '⛔' };
    }

    if (speedKmh.value > 51) {
        return { name: 'WARNING', color: 'text-yellow-600', icon: '⚠️' };
    }

    return { name: 'SAFE', color: 'text-green-600', icon: '✅' };
});

/** Permission status badge color */
const permissionBadgeColor = computed(() => {
    switch (geoState.value.permissionStatus) {
        case 'granted':
            return 'bg-green-100 text-green-800';
        case 'denied':
            return 'bg-red-100 text-red-800';
        case 'prompt':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    TripControls Component Demo
                </h1>
                <p class="text-gray-600">
                    Testing page for trip controls with integrated SpeedGauge and GPS tracking
                </p>
            </div>

            <!-- Main Demo Area -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Left Column: SpeedGauge + Controls -->
                <div class="space-y-6">
                    <!-- SpeedGauge Component -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Speedometer
                        </h2>
                        <div class="flex justify-center">
                            <SpeedGauge
                                :speed="speedKmh"
                                :speed-limit="60"
                                size="lg"
                            />
                        </div>
                        <div class="mt-4 flex items-center justify-center space-x-2">
                            <span class="text-2xl">{{ speedZone.icon }}</span>
                            <span :class="['font-semibold', speedZone.color]">
                                {{ speedZone.name }}
                            </span>
                        </div>
                    </div>

                    <!-- TripControls Component -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Trip Controls
                        </h2>
                        <TripControls />
                    </div>
                </div>

                <!-- Right Column: Debug Info -->
                <div class="space-y-6">
                    <!-- Status Panel -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Status
                            </h2>
                            <button
                                type="button"
                                class="text-sm text-blue-600 hover:text-blue-700"
                                @click="showDebug = !showDebug"
                            >
                                {{ showDebug ? 'Hide' : 'Show' }} Debug
                            </button>
                        </div>

                        <div class="space-y-3">
                            <!-- Trip Status -->
                            <div class="flex items-center justify-between py-2 border-b">
                                <span class="text-sm text-gray-600">Trip Status:</span>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        tripStore.hasActiveTrip
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ tripStore.hasActiveTrip ? 'Active' : 'Idle' }}
                                </span>
                            </div>

                            <!-- GPS Tracking -->
                            <div class="flex items-center justify-between py-2 border-b">
                                <span class="text-sm text-gray-600">GPS Tracking:</span>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        isTracking
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ isTracking ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- Permission Status -->
                            <div class="flex items-center justify-between py-2 border-b">
                                <span class="text-sm text-gray-600">GPS Permission:</span>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        permissionBadgeColor
                                    ]"
                                >
                                    {{ geoState.permissionStatus }}
                                </span>
                            </div>

                            <!-- Speed Logs Pending -->
                            <div class="flex items-center justify-between py-2 border-b">
                                <span class="text-sm text-gray-600">Pending Logs:</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ tripStore.pendingLogCount }}
                                    <span
                                        v-if="tripStore.needsSync"
                                        class="text-yellow-600"
                                    >
                                        (needs sync)
                                    </span>
                                </span>
                            </div>

                            <!-- Current Speed -->
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-gray-600">Current Speed:</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ speedKmh.toFixed(1) }} km/h
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Trip Statistics (Active Trip Only) -->
                    <div
                        v-if="tripStore.hasActiveTrip"
                        class="bg-white rounded-lg shadow-md p-6"
                    >
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Trip Statistics
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-1">Max Speed</p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ tripStore.stats.maxSpeed.toFixed(1) }}
                                    <span class="text-sm font-normal text-gray-600">km/h</span>
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-1">Avg Speed</p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ tripStore.stats.averageSpeed.toFixed(1) }}
                                    <span class="text-sm font-normal text-gray-600">km/h</span>
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-1">Distance</p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ tripStore.stats.distance.toFixed(2) }}
                                    <span class="text-sm font-normal text-gray-600">km</span>
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-1">Violations</p>
                                <p class="text-xl font-bold text-red-600">
                                    {{ tripStore.stats.violationCount }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Debug Information -->
                    <div
                        v-if="showDebug"
                        class="bg-white rounded-lg shadow-md p-6"
                    >
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Debug Information
                        </h2>
                        <div class="space-y-2">
                            <details class="text-xs">
                                <summary class="cursor-pointer font-medium text-gray-700 mb-2">
                                    Trip Store State
                                </summary>
                                <pre class="bg-gray-50 p-3 rounded overflow-x-auto text-xs">{{ {
                                    hasActiveTrip: tripStore.hasActiveTrip,
                                    isStarting: tripStore.isStarting,
                                    isEnding: tripStore.isEnding,
                                    isSyncing: tripStore.isSyncing,
                                    pendingLogCount: tripStore.pendingLogCount,
                                    needsSync: tripStore.needsSync,
                                    currentTrip: tripStore.currentTrip,
                                    stats: tripStore.stats,
                                    error: tripStore.error,
                                } }}</pre>
                            </details>
                            <details class="text-xs">
                                <summary class="cursor-pointer font-medium text-gray-700 mb-2">
                                    Geolocation State
                                </summary>
                                <pre class="bg-gray-50 p-3 rounded overflow-x-auto text-xs">{{ geoState }}</pre>
                            </details>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testing Checklist -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Testing Checklist
                </h2>
                <div class="space-y-2 text-sm">
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Start Trip:</strong> GPS permission requested → Trip created → Speed tracking starts
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Duration Display:</strong> Updates every second in HH:MM:SS format
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Speed Logging:</strong> Check console for speed logs every ~1 second
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Auto-sync:</strong> After 10 logs (50 seconds), "Menyinkronkan data..." appears
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Stop Trip:</strong> Confirmation dialog → Remaining logs synced → Trip ended
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Loading States:</strong> Buttons disabled during isStarting/isEnding/isSyncing
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Error Handling:</strong> Permission denied shows error message
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Mobile Responsive:</strong> Test on mobile device or DevTools mobile view
                        </span>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" class="mt-1 mr-2" />
                        <span class="text-gray-700">
                            <strong>Integration:</strong> Speed gauge updates in real-time with GPS data
                        </span>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-2">
                    📝 Testing Instructions
                </h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>This page requires GPS/location access to function</li>
                    <li>Open browser DevTools console to see speed log events</li>
                    <li>For realistic testing, use a mobile device or GPS simulator</li>
                    <li>Check Network tab in DevTools to verify API calls (POST /api/trips, etc.)</li>
                    <li>Test error scenarios by denying location permission</li>
                    <li>Verify trip data in database after ending trip</li>
                </ul>
            </div>
        </div>
    </div>
</template>

