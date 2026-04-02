<!--
TripStats Component

Displays real-time trip statistics during active tracking sessions.
Integrates with trip store for live updates of speed, distance, duration, and violations.

Features:
- Real-time reactive updates from trip store
- Formatted metrics with proper units and precision
- Color-coded violation indicator
- Responsive grid layout (mobile-first)
- Empty state when no trip active
- Smooth CSS transitions for value changes

Usage:
  <TripStats />
  <TripStats compact />

Integration:
- Trip Store: useTripStore().stats (reactive)
- No props required (reads from store directly)
- Automatically shows/hides based on hasActiveTrip

Props:
- compact?: boolean - Enable compact mode (smaller padding/text)
-->

<script setup lang="ts">
import { computed } from 'vue';

import { useTripStore } from '@/stores/trip';
import type { TripStatsProps } from '@/types/speedometer';

// ========================================================================
// Props
// ========================================================================

/**
 * Component props with defaults.
 *
 * WHY: Provide compact mode for different layout requirements.
 */
const props = withDefaults(defineProps<TripStatsProps>(), {
    compact: false,
});

// ========================================================================
// Store Integration
// ========================================================================

const tripStore = useTripStore();

// ========================================================================
// Formatting Utilities
// ========================================================================

/**
 * Format duration seconds to HH:MM:SS or MM:SS.
 *
 * WHY: User-friendly time display.
 * WHY: Show hours only if trip duration exceeds 1 hour.
 *
 * @param seconds - Duration in seconds
 * @returns Formatted duration string
 *
 * @example
 * ```ts
 * formatDuration(125)    // "02:05"
 * formatDuration(3665)   // "01:01:05"
 * ```
 */
function formatDuration(seconds: number): string {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

/**
 * Format distance to 2 decimal places with unit.
 *
 * WHY: Consistent precision across UI.
 * WHY: 2 decimals provides adequate precision for km measurements.
 *
 * @param km - Distance in kilometers
 * @returns Formatted distance string with unit
 *
 * @example
 * ```ts
 * formatDistance(12.456)  // "12.46 km"
 * formatDistance(0.123)   // "0.12 km"
 * ```
 */
function formatDistance(km: number): string {
    return `${km.toFixed(2)} km`;
}

/**
 * Format speed to 1 decimal place with unit.
 *
 * WHY: Match SpeedGauge precision for consistency.
 * WHY: 1 decimal provides balance between precision and readability.
 *
 * @param kmh - Speed in kilometers per hour
 * @returns Formatted speed string with unit
 *
 * @example
 * ```ts
 * formatSpeed(45.67)   // "45.7 km/h"
 * formatSpeed(60)      // "60.0 km/h"
 * ```
 */
function formatSpeed(kmh: number): string {
    return `${kmh.toFixed(1)} km/h`;
}

// ========================================================================
// Computed Properties
// ========================================================================

/**
 * Formatted current speed for display.
 *
 * WHY: Reactive formatting updates automatically on stats change.
 */
const currentSpeedFormatted = computed<string>(() =>
    formatSpeed(tripStore.stats.currentSpeed),
);

/**
 * Formatted maximum speed for display.
 */
const maxSpeedFormatted = computed<string>(() =>
    formatSpeed(tripStore.stats.maxSpeed),
);

/**
 * Formatted average speed for display.
 */
const avgSpeedFormatted = computed<string>(() =>
    formatSpeed(tripStore.stats.averageSpeed),
);

/**
 * Formatted distance for display.
 */
const distanceFormatted = computed<string>(() =>
    formatDistance(tripStore.stats.distance),
);

/**
 * Formatted duration for display.
 */
const durationFormatted = computed<string>(() =>
    formatDuration(tripStore.stats.duration),
);

/**
 * Violation count for display.
 */
const violationCount = computed<number>(() => tripStore.stats.violationCount);

/**
 * Whether there are any violations.
 *
 * WHY: Conditionally render violations section.
 */
const hasViolations = computed<boolean>(() => violationCount.value > 0);

/**
 * CSS classes for violation badge color.
 *
 * WHY: Red for violations, green for clean record.
 */
const violationBadgeClass = computed<string>(() => {
    if (violationCount.value === 0) {
        return 'bg-green-100 text-green-800';
    }

    return 'bg-red-100 text-red-800';
});

/**
 * Dynamic padding classes based on compact mode.
 *
 * WHY: Flexible sizing for different layout contexts.
 */
const paddingClass = computed<string>(() => {
    return props.compact ? 'p-3' : 'p-4 md:p-6';
});

/**
 * Dynamic gap classes based on compact mode.
 */
const gapClass = computed<string>(() => {
    return props.compact ? 'gap-3' : 'gap-4';
});
</script>

<template>
    <!-- Trip Statistics Card -->
    <div
        :class="[
            'bg-white rounded-lg shadow-md transition-all duration-300',
            paddingClass,
        ]"
    >
        <!-- ============================================================ -->
        <!-- Header -->
        <!-- ============================================================ -->

        <h3
            :class="[
                'font-semibold text-gray-800 mb-4',
                compact ? 'text-base' : 'text-lg md:text-xl',
            ]"
        >
            📊 Statistik Perjalanan
        </h3>

        <!-- Show empty state if no active trip -->
        <div
            v-if="!tripStore.hasActiveTrip"
            class="py-8 text-center text-gray-500"
        >
            <p class="text-sm md:text-base">
                Belum ada perjalanan aktif. Mulai perjalanan untuk melihat
                statistik.
            </p>
        </div>

        <!-- Show stats when trip is active -->
        <div v-else class="space-y-4">
            <!-- ======================================================== -->
            <!-- Speed Metrics Row (3 columns) -->
            <!-- ======================================================== -->

            <div :class="['grid grid-cols-3', gapClass]">
                <!-- Current Speed -->
                <div class="text-center">
                    <div
                        :class="[
                            'text-gray-500 mb-1',
                            compact ? 'text-xs' : 'text-xs md:text-sm',
                        ]"
                    >
                        ⚡ Sekarang
                    </div>
                    <div
                        :class="[
                            'font-bold text-blue-600 transition-all duration-300',
                            compact
                                ? 'text-lg'
                                : 'text-xl md:text-2xl lg:text-3xl',
                        ]"
                        :aria-label="`Kecepatan sekarang ${currentSpeedFormatted}`"
                    >
                        {{ tripStore.stats.currentSpeed.toFixed(1) }}
                    </div>
                    <div
                        :class="[
                            'text-gray-400 mt-1',
                            compact ? 'text-[10px]' : 'text-xs',
                        ]"
                    >
                        km/h
                    </div>
                </div>

                <!-- Maximum Speed -->
                <div class="text-center">
                    <div
                        :class="[
                            'text-gray-500 mb-1',
                            compact ? 'text-xs' : 'text-xs md:text-sm',
                        ]"
                    >
                        📊 Maksimal
                    </div>
                    <div
                        :class="[
                            'font-bold text-red-600 transition-all duration-300',
                            compact
                                ? 'text-lg'
                                : 'text-xl md:text-2xl lg:text-3xl',
                        ]"
                        :aria-label="`Kecepatan maksimal ${maxSpeedFormatted}`"
                    >
                        {{ tripStore.stats.maxSpeed.toFixed(1) }}
                    </div>
                    <div
                        :class="[
                            'text-gray-400 mt-1',
                            compact ? 'text-xs' : 'text-xs sm:text-sm',
                        ]"
                    >
                        km/h
                    </div>
                </div>

                <!-- Average Speed -->
                <div class="text-center">
                    <div
                        :class="[
                            'text-gray-500 mb-1',
                            compact ? 'text-xs' : 'text-xs md:text-sm',
                        ]"
                    >
                        📈 Rata-rata
                    </div>
                    <div
                        :class="[
                            'font-bold text-green-600 transition-all duration-300',
                            compact
                                ? 'text-lg'
                                : 'text-xl md:text-2xl lg:text-3xl',
                        ]"
                        :aria-label="`Kecepatan rata-rata ${avgSpeedFormatted}`"
                    >
                        {{ tripStore.stats.averageSpeed.toFixed(1) }}
                    </div>
                    <div
                        :class="[
                            'text-gray-400 mt-1',
                            compact ? 'text-xs' : 'text-xs sm:text-sm',
                        ]"
                    >
                        km/h
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Distance & Duration Row (2 columns) -->
            <!-- ======================================================== -->

            <div :class="['grid grid-cols-2', gapClass]">
                <!-- Distance -->
                <div
                    class="bg-gray-50 rounded-lg p-3 text-center transition-all duration-300"
                >
                    <div
                        :class="[
                            'text-gray-500 mb-2',
                            compact ? 'text-xs' : 'text-xs md:text-sm',
                        ]"
                    >
                        📏 Jarak
                    </div>
                    <div
                        :class="[
                            'font-bold text-purple-600',
                            compact ? 'text-base' : 'text-lg md:text-xl',
                        ]"
                        :aria-label="`Jarak tempuh ${distanceFormatted}`"
                    >
                        {{ tripStore.stats.distance.toFixed(2) }}
                    </div>
                    <div
                        :class="[
                            'text-gray-400 mt-1',
                            compact ? 'text-xs' : 'text-xs sm:text-sm',
                        ]"
                    >
                        km
                    </div>
                </div>

                <!-- Duration -->
                <div
                    class="bg-gray-50 rounded-lg p-3 text-center transition-all duration-300"
                >
                    <div
                        :class="[
                            'text-gray-500 mb-2',
                            compact ? 'text-xs' : 'text-xs md:text-sm',
                        ]"
                    >
                        ⏱️ Durasi
                    </div>
                    <div
                        :class="[
                            'font-bold text-orange-600 font-mono',
                            compact ? 'text-base' : 'text-lg md:text-xl',
                        ]"
                        :aria-label="`Durasi perjalanan ${durationFormatted}`"
                    >
                        {{ durationFormatted }}
                    </div>
                    <div
                        :class="[
                            'text-gray-400 mt-1',
                            compact ? 'text-xs' : 'text-xs sm:text-sm',
                        ]"
                    >
                        {{ tripStore.stats.duration >= 3600 ? 'jam' : 'menit' }}
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- Violations Section -->
            <!-- ======================================================== -->

            <div
                class="pt-3 border-t border-gray-200 flex items-center justify-between"
            >
                <span
                    :class="[
                        'text-gray-700 font-medium',
                        compact ? 'text-xs' : 'text-sm',
                    ]"
                >
                    ⛔ Pelanggaran Batas Kecepatan:
                </span>
                <span
                    :class="[
                        'px-3 py-1 rounded-full font-bold transition-all duration-300',
                        violationBadgeClass,
                        compact ? 'text-xs' : 'text-sm',
                    ]"
                    :aria-label="`Jumlah pelanggaran ${violationCount}`"
                >
                    {{ violationCount }}
                </span>
            </div>

            <!-- Optional: Violation message -->
            <div
                v-if="hasViolations"
                class="text-xs text-red-600 text-center bg-red-50 rounded p-2"
            >
                ⚠️ Anda telah melampaui batas kecepatan {{ violationCount }}
                kali. Harap berkendara lebih hati-hati.
            </div>
        </div>
    </div>
</template>
