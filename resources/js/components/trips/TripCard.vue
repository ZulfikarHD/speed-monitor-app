<script setup lang="ts">
/**
 * TripCard Component
 *
 * Displays a summary of a single trip with key metrics and sync status.
 * Clickable card that navigates to trip detail page via Inertia router.
 *
 * Features:
 * - Trip summary (date, time, duration, distance, speeds)
 * - Status badge (in_progress, completed, auto_stopped)
 * - Sync status badge with animated icon
 * - Motion-v animations for interactivity
 *
 * @example
 * ```vue
 * <TripCard
 *   v-for="trip in trips"
 *   :key="trip.id"
 *   :trip="trip"
 * />
 * ```
 */

import { router } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';

import type { Trip } from '@/types/trip';
import { formatDate, formatTime, formatDuration } from '@/utils/date';

/**
 * TripCard component props.
 */
interface TripCardProps {
    /** Trip data to display */
    trip: Trip;
}

const props = defineProps<TripCardProps>();

/**
 * Format distance for display.
 *
 * @param distance - Distance in kilometers (number or string from DB)
 * @returns Formatted distance string (e.g., "12.5 km")
 */
function formatDistance(distance: number | string | null): string {
    if (distance === null) {
        return '-';
    }

    const numDistance = typeof distance === 'string' ? parseFloat(distance) : distance;

    return `${numDistance.toFixed(2)} km`;
}

/**
 * Format speed for display.
 *
 * @param speed - Speed in km/h (number or string from DB)
 * @returns Formatted speed string (e.g., "65 km/h")
 */
function formatSpeed(speed: number | string | null): string {
    if (speed === null) {
        return '-';
    }

    const numSpeed = typeof speed === 'string' ? parseFloat(speed) : speed;

    return `${numSpeed.toFixed(1)} km/h`;
}

/**
 * Get status display text.
 *
 * @param status - Trip status
 * @returns Indonesian status text
 */
function getStatusText(status: string): string {
    const statusMap: Record<string, string> = {
        in_progress: 'Sedang Berjalan',
        completed: 'Selesai',
        auto_stopped: 'Berhenti Otomatis',
    };

    return statusMap[status] || status;
}

/**
 * Get status badge color classes.
 *
 * @param status - Trip status
 * @returns Tailwind CSS classes for status badge
 */
function getStatusColor(status: string): string {
    const colorMap: Record<string, string> = {
        in_progress: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        completed: 'bg-green-500/10 text-green-400 border-green-500/20',
        auto_stopped: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
    };

    return colorMap[status] || 'bg-gray-500/10 text-gray-400 border-gray-500/20';
}

/**
 * Get violation badge color.
 *
 * @param count - Violation count
 * @returns Tailwind CSS classes for violation badge
 */
function getViolationColor(count: number): string {
    if (count === 0) {
        return 'bg-green-500/10 text-green-400 border-green-500/20';
    }

    return 'bg-red-500/10 text-red-400 border-red-500/20';
}

/**
 * Handle card click - navigate to trip detail page.
 */
function handleClick(): void {
    router.visit(`/employee/trips/${props.trip.id}`);
}

// ========================================================================
// Sync Status Logic
// ========================================================================

/**
 * Check if trip is synced to backend.
 *
 * @returns True if trip has synced_at timestamp
 */
const isSynced = computed<boolean>(() => {
    return props.trip.synced_at !== null;
});

/**
 * Get sync status text.
 *
 * @returns Indonesian sync status text
 */
function getSyncStatusText(): string {
    return isSynced.value ? 'Tersinkronisasi' : 'Menunggu Sync';
}

/**
 * Get sync status badge color classes.
 *
 * @returns Tailwind CSS classes for sync badge
 */
function getSyncStatusColor(): string {
    return isSynced.value
        ? 'bg-green-500/10 text-green-400 border-green-500/20'
        : 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
}
</script>

<template>
    <!-- ======================================================================
        Trip Card (Theme-Aware)
        Interactive card displaying trip summary with hover effects
    ======================================================================= -->
    <motion.button
        @click="handleClick"
        :whileHover="{ scale: 1.02, y: -4 }"
        :whilePress="{ scale: 0.98 }"
        :transition="{ type: 'spring', bounce: 0.4, duration: 0.4 }"
        class="w-full rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-5 text-left transition-all duration-300 hover:border-cyan-500/50 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 hover:shadow-lg hover:shadow-zinc-200 dark:hover:shadow-cyan-500/10 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
        :aria-label="`View trip details from ${formatDate(trip.started_at)}`"
    >
        <!-- Header: Date and Status -->
        <div class="mb-4 flex items-start justify-between gap-4">
            <div class="flex-1">
                <h3
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ formatDate(trip.started_at) }}
                </h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ formatTime(trip.started_at) }}
                    <span v-if="trip.ended_at">
                        - {{ formatTime(trip.ended_at) }}
                    </span>
                </p>
            </div>

            <!-- Badges Container -->
            <div class="flex flex-col items-end gap-2">
                <!-- Status Badge -->
                <span
                    :class="[
                        'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium',
                        getStatusColor(trip.status),
                    ]"
                >
                    {{ getStatusText(trip.status) }}
                </span>

                <!-- Sync Status Badge -->
                <span
                    :class="[
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium',
                        getSyncStatusColor(),
                    ]"
                >
                    <!-- Animated Sync Icon (pending) or Static Checkmark (synced) -->
                    <motion.div
                        v-if="!isSynced"
                        :animate="{
                            scale: [1, 1.15, 1],
                            opacity: [0.7, 1, 0.7],
                        }"
                        :transition="{
                            duration: 2,
                            repeat: Infinity,
                            ease: 'easeInOut',
                        }"
                        class="flex items-center"
                    >
                        <!-- Cloud Upload Icon (pending sync) -->
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                    </motion.div>
                    <div v-else class="flex items-center">
                        <!-- Checkmark Icon (synced) -->
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                    <span>{{ getSyncStatusText() }}</span>
                </span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <!-- Duration -->
            <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-3">
                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Durasi</div>
                <div
                    class="font-mono text-sm font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatDuration(trip.duration_seconds) }}
                </div>
            </div>

            <!-- Distance -->
            <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-3">
                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Jarak</div>
                <div
                    class="font-mono text-sm font-semibold text-cyan-600 dark:text-cyan-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatDistance(trip.total_distance) }}
                </div>
            </div>

            <!-- Max Speed -->
            <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-3">
                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Kec. Maks</div>
                <div
                    class="font-mono text-sm font-semibold text-red-600 dark:text-red-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatSpeed(trip.max_speed) }}
                </div>
            </div>

            <!-- Violations -->
            <div class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-3">
                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Pelanggaran</div>
                <span
                    :class="[
                        'inline-flex items-center rounded-full border px-2 py-0.5 font-mono text-xs font-semibold',
                        getViolationColor(trip.violation_count),
                    ]"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ trip.violation_count }}
                </span>
            </div>
        </div>

        <!-- Optional Notes Preview -->
        <div v-if="trip.notes" class="mt-4 border-t border-zinc-200 dark:border-white/5 pt-4">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Catatan:</div>
            <p class="mt-1 line-clamp-2 text-sm text-zinc-900 dark:text-white">
                {{ trip.notes }}
            </p>
        </div>

        <!-- Click Indicator -->
        <div class="mt-4 flex items-center justify-end text-xs text-cyan-600 dark:text-cyan-400">
            <span>Lihat Detail</span>
            <svg
                class="ml-1 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                />
            </svg>
        </div>
    </motion.button>
</template>
