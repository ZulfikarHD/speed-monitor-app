<!--
TripStats Demo Page

Testing and demonstration page for the TripStats component.
Provides isolated testing with mock data controls and integration testing
with TripControls + SpeedGauge components.

Usage: Navigate to this page during development to test the component.
Route: /test/trip-stats-demo

Testing Scenarios:
1. Empty State
   - No active trip → shows empty state message

2. Mock Data Controls
   - Adjust individual metrics with sliders
   - Test formatting at various values
   - Verify decimal precision

3. Full Integration
   - Start real trip with TripControls
   - Watch stats update in real-time
   - Verify sync with SpeedGauge

4. Responsive Design
   - Mobile (portrait)
   - Tablet
   - Desktop

5. Compact Mode
   - Toggle compact mode to test smaller layout
-->

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';

import SpeedGauge from '@/components/speedometer/SpeedGauge.vue';
import TripControls from '@/components/speedometer/TripControls.vue';
import TripStats from '@/components/speedometer/TripStats.vue';
import { useGeolocation } from '@/composables/useGeolocation';
import { useTripStore } from '@/stores/trip';

// ========================================================================
// Store and Composable Integration
// ========================================================================

const tripStore = useTripStore();
const { speedKmh } = useGeolocation();

// ========================================================================
// Local State
// ========================================================================

/** Show debug information */
const showDebug = ref<boolean>(true);

/** Enable compact mode for TripStats */
const compactMode = ref<boolean>(false);

/** Mock mode: manually control stats */
const mockMode = ref<boolean>(false);

/** Mock trip stats (only used when mockMode is true) */
const mockStats = ref({
    currentSpeed: 45,
    maxSpeed: 65,
    averageSpeed: 52,
    distance: 12.5,
    duration: 1845, // 30:45
    violationCount: 3,
});

/** Simulate trip running interval */
let simulateInterval: ReturnType<typeof setInterval> | null = null;

// ========================================================================
// Computed Properties
// ========================================================================

/** Currently displayed stats (mock or real) */
const displayStats = computed(() => {
    if (mockMode.value) {
        return mockStats.value;
    }

    return tripStore.stats;
});

/** Format duration for display */
const durationFormatted = computed<string>(() => {
    const seconds = displayStats.value.duration;
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
});

// ========================================================================
// Mock Data Control Functions
// ========================================================================

/**
 * Reset mock stats to default values.
 */
function resetMockStats(): void {
    mockStats.value = {
        currentSpeed: 45,
        maxSpeed: 65,
        averageSpeed: 52,
        distance: 12.5,
        duration: 1845,
        violationCount: 3,
    };
}

/**
 * Simulate a running trip with incrementing values.
 *
 * WHY: Test real-time updates without actual GPS.
 */
function simulateTrip(): void {
    if (simulateInterval) {
        clearInterval(simulateInterval);
        simulateInterval = null;

        return;
    }

    mockMode.value = true;
    resetMockStats();

    simulateInterval = setInterval(() => {
        // Increment duration
        mockStats.value.duration += 1;

        // Randomly adjust current speed (40-70 km/h)
        mockStats.value.currentSpeed =
            40 + Math.random() * 30;

        // Update max speed if current exceeds it
        if (mockStats.value.currentSpeed > mockStats.value.maxSpeed) {
            mockStats.value.maxSpeed = mockStats.value.currentSpeed;
        }

        // Recalculate average speed
        mockStats.value.averageSpeed =
            (mockStats.value.maxSpeed + mockStats.value.currentSpeed) / 2;

        // Increment distance (assume 5-second interval)
        mockStats.value.distance +=
            (mockStats.value.currentSpeed * 5) / 3600;

        // Increment violations if speed > 60
        if (mockStats.value.currentSpeed > 60) {
            mockStats.value.violationCount += 1;
        }
    }, 1000);
}

/**
 * Set mock stats to zero values.
 *
 * WHY: Test display with minimal data.
 */
function setZeroStats(): void {
    mockMode.value = true;
    mockStats.value = {
        currentSpeed: 0,
        maxSpeed: 0,
        averageSpeed: 0,
        distance: 0,
        duration: 0,
        violationCount: 0,
    };
}

/**
 * Set mock stats to edge case values.
 *
 * WHY: Test formatting with large numbers.
 */
function setEdgeCaseStats(): void {
    mockMode.value = true;
    mockStats.value = {
        currentSpeed: 99.9,
        maxSpeed: 120,
        averageSpeed: 85.5,
        distance: 999.99,
        duration: 359999, // 99:59:59
        violationCount: 150,
    };
}

// ========================================================================
// Lifecycle
// ========================================================================

onBeforeUnmount(() => {
    if (simulateInterval) {
        clearInterval(simulateInterval);
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <!-- ============================================================ -->
        <!-- Page Header -->
        <!-- ============================================================ -->

        <div class="mx-auto max-w-4xl">
            <div class="mb-8">
                <h1 class="mb-2 text-3xl font-bold text-gray-900">
                    TripStats Component Demo
                </h1>
                <p class="text-gray-600">
                    Testing and demonstration for US-3.5: Trip Stats Display
                </p>
                <div class="mt-4 flex gap-2">
                    <span
                        class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"
                    >
                        US-3.5
                    </span>
                    <span
                        class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800"
                    >
                        Sprint 3
                    </span>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Component Preview Section -->
            <!-- ======================================================== -->

            <div class="mb-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Component Preview
                    </h2>

                    <!-- Mode toggles -->
                    <div class="flex gap-2">
                        <button
                            type="button"
                            :class="[
                                'rounded-lg px-3 py-1 text-sm font-medium transition-colors',
                                compactMode
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
                            @click="compactMode = !compactMode"
                        >
                            {{ compactMode ? '📦 Compact' : '📦 Normal' }}
                        </button>
                    </div>
                </div>

                <!-- TripStats Component Preview -->
                <TripStats
                    v-if="!mockMode"
                    :compact="compactMode"
                />

                <!-- Mock TripStats (manual override) -->
                <div
                    v-else
                    :class="[
                        'rounded-lg bg-white p-4 shadow-md',
                        compactMode ? 'p-3' : 'p-4 md:p-6',
                    ]"
                >
                    <h3
                        :class="[
                            'mb-4 font-semibold text-gray-800',
                            compactMode ? 'text-base' : 'text-lg md:text-xl',
                        ]"
                    >
                        📊 Statistik Perjalanan (Mock Mode)
                    </h3>

                    <div class="space-y-4">
                        <!-- Speed row -->
                        <div
                            :class="[
                                'grid grid-cols-3',
                                compactMode ? 'gap-3' : 'gap-4',
                            ]"
                        >
                            <div class="text-center">
                                <div
                                    :class="[
                                        'mb-1 text-gray-500',
                                        compactMode
                                            ? 'text-xs'
                                            : 'text-xs md:text-sm',
                                    ]"
                                >
                                    ⚡ Sekarang
                                </div>
                                <div
                                    :class="[
                                        'font-bold text-blue-600',
                                        compactMode
                                            ? 'text-lg'
                                            : 'text-xl md:text-2xl lg:text-3xl',
                                    ]"
                                >
                                    {{ mockStats.currentSpeed.toFixed(1) }}
                                </div>
                                <div
                                    :class="[
                                        'mt-1 text-gray-400',
                                        compactMode ? 'text-[10px]' : 'text-xs',
                                    ]"
                                >
                                    km/h
                                </div>
                            </div>

                            <div class="text-center">
                                <div
                                    :class="[
                                        'mb-1 text-gray-500',
                                        compactMode
                                            ? 'text-xs'
                                            : 'text-xs md:text-sm',
                                    ]"
                                >
                                    📊 Maksimal
                                </div>
                                <div
                                    :class="[
                                        'font-bold text-red-600',
                                        compactMode
                                            ? 'text-lg'
                                            : 'text-xl md:text-2xl lg:text-3xl',
                                    ]"
                                >
                                    {{ mockStats.maxSpeed.toFixed(1) }}
                                </div>
                                <div
                                    :class="[
                                        'mt-1 text-gray-400',
                                        compactMode ? 'text-[10px]' : 'text-xs',
                                    ]"
                                >
                                    km/h
                                </div>
                            </div>

                            <div class="text-center">
                                <div
                                    :class="[
                                        'mb-1 text-gray-500',
                                        compactMode
                                            ? 'text-xs'
                                            : 'text-xs md:text-sm',
                                    ]"
                                >
                                    📈 Rata-rata
                                </div>
                                <div
                                    :class="[
                                        'font-bold text-green-600',
                                        compactMode
                                            ? 'text-lg'
                                            : 'text-xl md:text-2xl lg:text-3xl',
                                    ]"
                                >
                                    {{ mockStats.averageSpeed.toFixed(1) }}
                                </div>
                                <div
                                    :class="[
                                        'mt-1 text-gray-400',
                                        compactMode ? 'text-[10px]' : 'text-xs',
                                    ]"
                                >
                                    km/h
                                </div>
                            </div>
                        </div>

                        <!-- Distance & Duration -->
                        <div
                            :class="[
                                'grid grid-cols-2',
                                compactMode ? 'gap-3' : 'gap-4',
                            ]"
                        >
                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <div
                                    :class="[
                                        'mb-2 text-gray-500',
                                        compactMode
                                            ? 'text-xs'
                                            : 'text-xs md:text-sm',
                                    ]"
                                >
                                    📏 Jarak
                                </div>
                                <div
                                    :class="[
                                        'font-bold text-purple-600',
                                        compactMode
                                            ? 'text-base'
                                            : 'text-lg md:text-xl',
                                    ]"
                                >
                                    {{ mockStats.distance.toFixed(2) }}
                                </div>
                                <div
                                    :class="[
                                        'mt-1 text-gray-400',
                                        compactMode ? 'text-[10px]' : 'text-xs',
                                    ]"
                                >
                                    km
                                </div>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-3 text-center">
                                <div
                                    :class="[
                                        'mb-2 text-gray-500',
                                        compactMode
                                            ? 'text-xs'
                                            : 'text-xs md:text-sm',
                                    ]"
                                >
                                    ⏱️ Durasi
                                </div>
                                <div
                                    :class="[
                                        'font-mono font-bold text-orange-600',
                                        compactMode
                                            ? 'text-base'
                                            : 'text-lg md:text-xl',
                                    ]"
                                >
                                    {{ durationFormatted }}
                                </div>
                                <div
                                    :class="[
                                        'mt-1 text-gray-400',
                                        compactMode ? 'text-[10px]' : 'text-xs',
                                    ]"
                                >
                                    {{ mockStats.duration >= 3600 ? 'jam' : 'menit' }}
                                </div>
                            </div>
                        </div>

                        <!-- Violations -->
                        <div
                            class="flex items-center justify-between border-t border-gray-200 pt-3"
                        >
                            <span
                                :class="[
                                    'font-medium text-gray-700',
                                    compactMode ? 'text-xs' : 'text-sm',
                                ]"
                            >
                                ⛔ Pelanggaran Batas Kecepatan:
                            </span>
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 font-bold',
                                    mockStats.violationCount === 0
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                    compactMode ? 'text-xs' : 'text-sm',
                                ]"
                            >
                                {{ mockStats.violationCount }}
                            </span>
                        </div>

                        <div
                            v-if="mockStats.violationCount > 0"
                            class="rounded bg-red-50 p-2 text-center text-xs text-red-600"
                        >
                            ⚠️ Anda telah melampaui batas kecepatan
                            {{ mockStats.violationCount }} kali. Harap berkendara
                            lebih hati-hati.
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Mock Data Controls -->
            <!-- ======================================================== -->

            <div class="mb-8 rounded-lg bg-white p-6 shadow-md">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">
                    Mock Data Controls
                </h2>

                <div class="mb-4 flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        @click="simulateTrip"
                    >
                        {{ simulateInterval ? '⏸️ Stop Simulation' : '▶️ Simulate Trip' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                        @click="resetMockStats"
                    >
                        🔄 Reset
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700"
                        @click="setZeroStats"
                    >
                        0️⃣ Zero Values
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        @click="setEdgeCaseStats"
                    >
                        🔥 Edge Cases
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                        @click="mockMode = false"
                    >
                        ✅ Use Real Trip Store
                    </button>
                </div>

                <div
                    v-if="mockMode"
                    class="space-y-4"
                >
                    <!-- Current Speed -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Current Speed: {{ mockStats.currentSpeed.toFixed(1) }} km/h
                        </label>
                        <input
                            v-model.number="mockStats.currentSpeed"
                            type="range"
                            min="0"
                            max="120"
                            step="0.1"
                            class="w-full"
                        />
                    </div>

                    <!-- Max Speed -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Max Speed: {{ mockStats.maxSpeed.toFixed(1) }} km/h
                        </label>
                        <input
                            v-model.number="mockStats.maxSpeed"
                            type="range"
                            min="0"
                            max="120"
                            step="0.1"
                            class="w-full"
                        />
                    </div>

                    <!-- Average Speed -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Average Speed: {{ mockStats.averageSpeed.toFixed(1) }} km/h
                        </label>
                        <input
                            v-model.number="mockStats.averageSpeed"
                            type="range"
                            min="0"
                            max="120"
                            step="0.1"
                            class="w-full"
                        />
                    </div>

                    <!-- Distance -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Distance: {{ mockStats.distance.toFixed(2) }} km
                        </label>
                        <input
                            v-model.number="mockStats.distance"
                            type="range"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full"
                        />
                    </div>

                    <!-- Duration -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Duration: {{ durationFormatted }}
                        </label>
                        <input
                            v-model.number="mockStats.duration"
                            type="range"
                            min="0"
                            max="7200"
                            step="1"
                            class="w-full"
                        />
                    </div>

                    <!-- Violations -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Violations: {{ mockStats.violationCount }}
                        </label>
                        <input
                            v-model.number="mockStats.violationCount"
                            type="range"
                            min="0"
                            max="50"
                            step="1"
                            class="w-full"
                        />
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-lg bg-blue-50 p-4 text-center text-sm text-blue-800"
                >
                    Using real trip store data. Enable mock mode to control
                    values manually.
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Full Integration Test -->
            <!-- ======================================================== -->

            <div class="mb-8 rounded-lg bg-white p-6 shadow-md">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">
                    Full Integration Test
                </h2>
                <p class="mb-4 text-sm text-gray-600">
                    Test TripStats with real GPS tracking and trip controls.
                </p>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Left: SpeedGauge & Controls -->
                    <div class="space-y-4">
                        <SpeedGauge
                            :speed="speedKmh"
                            size="md"
                        />
                        <TripControls />
                    </div>

                    <!-- Right: TripStats -->
                    <div>
                        <TripStats />
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Testing Checklist -->
            <!-- ======================================================== -->

            <div class="mb-8 rounded-lg bg-white p-6 shadow-md">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">
                    Testing Checklist
                </h2>

                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <input
                            id="check-1"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-1"
                            class="text-gray-700"
                        >
                            ☐ Shows all 6 metrics (current/max/avg speed,
                            distance, duration, violations)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-2"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-2"
                            class="text-gray-700"
                        >
                            ☐ Real-time updates during active trip
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-3"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-3"
                            class="text-gray-700"
                        >
                            ☐ Duration formats correctly (MM:SS or HH:MM:SS)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-4"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-4"
                            class="text-gray-700"
                        >
                            ☐ Distance shows 2 decimals (XX.XX km)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-5"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-5"
                            class="text-gray-700"
                        >
                            ☐ Speed shows 1 decimal (XX.X km/h)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-6"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-6"
                            class="text-gray-700"
                        >
                            ☐ Violations color-coded (green: 0, red: >0)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-7"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-7"
                            class="text-gray-700"
                        >
                            ☐ Empty state shows when no trip active
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-8"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-8"
                            class="text-gray-700"
                        >
                            ☐ Responsive on mobile (375px)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-9"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-9"
                            class="text-gray-700"
                        >
                            ☐ Compact mode works correctly
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="check-10"
                            type="checkbox"
                            class="h-4 w-4"
                        />
                        <label
                            for="check-10"
                            class="text-gray-700"
                        >
                            ☐ Smooth transitions for value changes
                        </label>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Debug Information -->
            <!-- ======================================================== -->

            <div
                v-if="showDebug"
                class="rounded-lg bg-gray-900 p-6 text-white shadow-md"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Debug Information</h2>
                    <button
                        type="button"
                        class="text-sm text-gray-400 hover:text-white"
                        @click="showDebug = false"
                    >
                        ✕ Hide
                    </button>
                </div>

                <div class="space-y-2 font-mono text-xs">
                    <div>
                        <span class="text-gray-400">Mode:</span>
                        {{ mockMode ? 'Mock' : 'Real Trip Store' }}
                    </div>
                    <div>
                        <span class="text-gray-400">Has Active Trip:</span>
                        {{ tripStore.hasActiveTrip }}
                    </div>
                    <div>
                        <span class="text-gray-400">Current Speed:</span>
                        {{ displayStats.currentSpeed.toFixed(2) }} km/h
                    </div>
                    <div>
                        <span class="text-gray-400">Max Speed:</span>
                        {{ displayStats.maxSpeed.toFixed(2) }} km/h
                    </div>
                    <div>
                        <span class="text-gray-400">Avg Speed:</span>
                        {{ displayStats.averageSpeed.toFixed(2) }} km/h
                    </div>
                    <div>
                        <span class="text-gray-400">Distance:</span>
                        {{ displayStats.distance.toFixed(2) }} km
                    </div>
                    <div>
                        <span class="text-gray-400">Duration:</span>
                        {{ displayStats.duration }}s ({{ durationFormatted }})
                    </div>
                    <div>
                        <span class="text-gray-400">Violations:</span>
                        {{ displayStats.violationCount }}
                    </div>
                    <div>
                        <span class="text-gray-400">GPS Speed:</span>
                        {{ speedKmh.toFixed(2) }} km/h
                    </div>
                </div>
            </div>

            <button
                v-else
                type="button"
                class="w-full rounded-lg bg-gray-900 py-2 text-white hover:bg-gray-800"
                @click="showDebug = true"
            >
                Show Debug Info
            </button>
        </div>
    </div>
</template>
