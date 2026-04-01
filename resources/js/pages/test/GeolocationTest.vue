<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- ========== Page Header ========== -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h1 class="text-2xl font-bold text-gray-900">
                    Geolocation Composable Test
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Test page for useGeolocation composable (US-3.1)
                </p>
            </div>

            <!-- ========== Permission Status ========== -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">
                    Permission Status
                </h2>
                <div class="mt-4 flex items-center gap-3">
                    <div
                        :class="[
                            'h-3 w-3 rounded-full',
                            permissionStatusColor,
                        ]"
                    ></div>
                    <span class="text-sm font-medium text-gray-700">
                        {{ permissionStatus }}
                    </span>
                </div>
                <button
                    type="button"
                    class="mt-4 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="permissionStatus === 'granted'"
                    @click="handleRequestPermission"
                >
                    Request Permission
                </button>
            </div>

            <!-- ========== Tracking Controls ========== -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">
                    Tracking Controls
                </h2>
                <div class="mt-4 flex gap-3">
                    <button
                        type="button"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isTracking || permissionStatus !== 'granted'"
                        @click="handleStartTracking"
                    >
                        Start Tracking
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!isTracking"
                        @click="handleStopTracking"
                    >
                        Stop Tracking
                    </button>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div
                        :class="[
                            'h-2 w-2 rounded-full',
                            isTracking ? 'bg-green-500' : 'bg-gray-300',
                        ]"
                    ></div>
                    <span class="text-sm text-gray-600">
                        {{ isTracking ? 'Tracking Active' : 'Not Tracking' }}
                    </span>
                </div>
            </div>

            <!-- ========== Current Speed Display ========== -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">
                    Current Speed
                </h2>
                <div class="mt-4 text-center">
                    <div class="text-6xl font-bold text-blue-600">
                        {{ speedKmh }}
                    </div>
                    <div class="mt-2 text-sm text-gray-600">km/h</div>
                </div>
            </div>

            <!-- ========== Geolocation State ========== -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">
                    Geolocation State
                </h2>
                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-600">
                            Latitude
                        </dt>
                        <dd class="text-sm text-gray-900">
                            {{ state.latitude?.toFixed(6) ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-600">
                            Longitude
                        </dt>
                        <dd class="text-sm text-gray-900">
                            {{ state.longitude?.toFixed(6) ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-600">
                            Accuracy
                        </dt>
                        <dd class="text-sm text-gray-900">
                            {{ state.accuracy?.toFixed(2) ?? 'N/A' }} meters
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-600">
                            Heading
                        </dt>
                        <dd class="text-sm text-gray-900">
                            {{ state.heading?.toFixed(2) ?? 'N/A' }}°
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-600">
                            Last Update
                        </dt>
                        <dd class="text-sm text-gray-900">
                            {{ formattedTimestamp }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- ========== Speed Log ========== -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">
                    Speed Log (Last 10 readings)
                </h2>
                <div class="mt-4">
                    <div v-if="speedLog.length === 0" class="text-sm text-gray-500">
                        No speed readings yet. Start tracking to see data.
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="(log, index) in speedLog"
                            :key="index"
                            class="flex justify-between rounded border border-gray-200 p-3"
                        >
                            <span class="text-sm font-medium text-gray-900">
                                {{ log.speed }} km/h
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ log.time }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== Error Display ========== -->
            <div
                v-if="error"
                class="rounded-lg border border-red-300 bg-red-50 p-6 shadow"
            >
                <h2 class="text-lg font-semibold text-red-900">Error</h2>
                <div class="mt-2">
                    <div class="text-sm font-medium text-red-800">
                        {{ error.code }}
                    </div>
                    <div class="mt-1 text-sm text-red-700">
                        {{ error.message }}
                    </div>
                </div>
            </div>

            <!-- ========== Test Instructions ========== -->
            <div class="rounded-lg bg-blue-50 p-6 shadow">
                <h2 class="text-lg font-semibold text-blue-900">
                    Testing Instructions
                </h2>
                <ol class="mt-4 list-inside list-decimal space-y-2 text-sm text-blue-800">
                    <li>Click "Request Permission" to grant location access</li>
                    <li>Click "Start Tracking" to begin speed monitoring</li>
                    <li>Move your device to test speed detection</li>
                    <li>
                        Verify speed conversion (should match GPS speed apps)
                    </li>
                    <li>Check accuracy and timestamp updates</li>
                    <li>Click "Stop Tracking" to pause monitoring</li>
                    <li>Test permission denial by blocking location</li>
                    <li>Test on mobile devices (iOS Safari, Android Chrome)</li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

import { useGeolocation } from '@/composables/useGeolocation';

/**
 * Geolocation Test Page
 *
 * Manual testing interface for useGeolocation composable (US-3.1).
 * Provides controls and displays for testing all composable features.
 */

// ========================================================================
// Composable Setup
// ========================================================================

const {
    watchSpeed,
    stopTracking,
    requestPermission,
    state,
    isTracking,
    error,
    permissionStatus,
    speedKmh,
} = useGeolocation();

// ========================================================================
// Speed Log
// ========================================================================

interface SpeedLogEntry {
    speed: number;
    time: string;
}

const speedLog = ref<SpeedLogEntry[]>([]);

/**
 * Add speed reading to log.
 *
 * Keeps only last 10 readings for display.
 */
function logSpeed(speed: number): void {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('id-ID');

    speedLog.value.unshift({
        speed,
        time: timeStr,
    });

    // Keep only last 10 readings
    if (speedLog.value.length > 10) {
        speedLog.value = speedLog.value.slice(0, 10);
    }
}

// ========================================================================
// Computed Properties
// ========================================================================

/**
 * Permission status indicator color.
 */
const permissionStatusColor = computed(() => {
    switch (permissionStatus.value) {
        case 'granted':
            return 'bg-green-500';
        case 'denied':
            return 'bg-red-500';
        case 'prompt':
            return 'bg-yellow-500';
        case 'unsupported':
            return 'bg-gray-500';
        default:
            return 'bg-gray-300';
    }
});

/**
 * Formatted timestamp for display.
 */
const formattedTimestamp = computed(() => {
    if (!state.value.timestamp) {
        return 'N/A';
    }

    const date = new Date(state.value.timestamp);

    return date.toLocaleTimeString('id-ID');
});

// ========================================================================
// Event Handlers
// ========================================================================

/**
 * Handle permission request button click.
 */
async function handleRequestPermission(): Promise<void> {
    const result = await requestPermission();

    if (result.granted) {
        console.log('✅ Permission granted');
    } else {
        console.error('❌ Permission denied:', result.error);
    }
}

/**
 * Handle start tracking button click.
 */
function handleStartTracking(): void {
    console.log('🚀 Starting speed tracking...');

    watchSpeed((speed, geoState) => {
        console.log(`📍 Speed: ${speed} km/h`, geoState);
        logSpeed(speed);
    });
}

/**
 * Handle stop tracking button click.
 */
function handleStopTracking(): void {
    console.log('🛑 Stopping speed tracking...');
    stopTracking();
}
</script>
