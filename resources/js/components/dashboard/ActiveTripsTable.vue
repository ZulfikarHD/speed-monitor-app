<script setup lang="ts">
/**
 * ActiveTripsTable Component
 *
 * Real-time active trips monitoring table for superuser dashboard.
 * Displays currently in-progress trips with live duration updates.
 *
 * Features:
 * - Responsive layout (table on desktop, cards on mobile)
 * - Live duration counter (updates every second)
 * - Employee name/email display
 * - Empty state with SVG icon
 * - Stagger animations (lightweight opacity/y)
 * - ARIA accessibility with proper table semantics
 * - Skeleton loading state
 * - Full light/dark theme support
 */

import { Motorbike } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import type { ActiveTrip } from '@/types/dashboard';

// ========================================================================
// Component Props
// ========================================================================

interface Props {
    /** List of active trips to display */
    trips: ActiveTrip[];

    /** Loading state for skeleton UI */
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

// ========================================================================
// Local State
// ========================================================================

/** Current timestamp for duration calculation (updates every second) */
const now = ref(Date.now());

/** Interval ID for cleanup */
let intervalId: number | null = null;

// ========================================================================
// Lifecycle
// ========================================================================

onMounted(() => {
    intervalId = window.setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
});

// ========================================================================
// Computed
// ========================================================================

/**
 * Format duration in human-readable format.
 *
 * @param startedAt - ISO8601 timestamp when trip started
 * @returns Formatted duration string (e.g., "1h 23m 45s")
 */
function formatDuration(startedAt: string): string {
    const startTime = new Date(startedAt).getTime();
    const durationMs = now.value - startTime;
    const durationSeconds = Math.floor(durationMs / 1000);

    const hours = Math.floor(durationSeconds / 3600);
    const minutes = Math.floor((durationSeconds % 3600) / 60);
    const seconds = durationSeconds % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m ${seconds}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${seconds}s`;
    } else {
        return `${seconds}s`;
    }
}

/**
 * Check if table has data.
 */
const hasTrips = computed(() => props.trips.length > 0);
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-900/50 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-zinc-200 dark:border-white/5 px-6 py-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    Trip Aktif
                </h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Pemantauan perjalanan karyawan yang sedang berlangsung
                </p>
            </div>
            <div v-if="$slots.actions" class="ml-4">
                <slot name="actions" />
            </div>
        </div>

        <!-- Loading State (Skeleton) -->
        <div
            v-if="isLoading"
            class="divide-y divide-zinc-200 dark:divide-zinc-800"
        >
            <div
                v-for="i in 3"
                :key="`skeleton-${i}`"
                class="px-6 py-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-1/3 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                        <div class="h-3 w-1/4 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>
                    <div class="h-8 w-20 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="!hasTrips"
            class="px-6 py-12 text-center"
        >
            <motion.div
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.3 }"
            >
                <Motorbike
                    :size="48"
                    :stroke-width="1.5"
                    class="mx-auto mb-4 text-zinc-400 dark:text-zinc-600"
                    aria-hidden="true"
                />
                <p class="text-lg font-medium text-zinc-900 dark:text-white">
                    Tidak Ada Trip Aktif
                </p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Semua karyawan telah menyelesaikan perjalanan mereka
                </p>
            </motion.div>
        </div>

        <!-- Data Views (Desktop Table + Mobile Cards) -->
        <template v-else>
            <!-- Desktop Table View -->
            <div class="hidden md:block">
                <table
                    class="w-full"
                    role="table"
                    aria-label="Tabel trip aktif"
                >
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50">
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400"
                                scope="col"
                            >
                                Karyawan
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400"
                                scope="col"
                            >
                                Durasi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <motion.tr
                            v-for="(trip, index) in trips"
                            :key="trip.id"
                            :initial="{ opacity: 0, y: 8 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{
                                delay: index * 0.05,
                                duration: 0.3,
                            }"
                            class="transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-white/5"
                        >
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        {{ trip.user.name }}
                                    </p>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ trip.user.email }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full border border-blue-500/30 bg-blue-500/20 dark:bg-blue-500/15 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-400"
                                    role="timer"
                                    :aria-live="'polite'"
                                >
                                    {{ formatDuration(trip.started_at) }}
                                </span>
                            </td>
                        </motion.tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="divide-y divide-zinc-200 dark:divide-zinc-800 md:hidden">
                <motion.div
                    v-for="(trip, index) in trips"
                    :key="trip.id"
                    :initial="{ opacity: 0, y: 8 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{
                        delay: index * 0.05,
                        duration: 0.3,
                    }"
                    class="px-4 py-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-zinc-900 dark:text-white">
                                {{ trip.user.name }}
                            </p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ trip.user.email }}
                            </p>
                        </div>
                        <div>
                            <span
                                class="inline-flex items-center rounded-full border border-blue-500/30 bg-blue-500/20 dark:bg-blue-500/15 px-3 py-1 text-xs font-medium text-blue-700 dark:text-blue-400"
                                role="timer"
                                :aria-live="'polite'"
                            >
                                {{ formatDuration(trip.started_at) }}
                            </span>
                        </div>
                    </div>
                </motion.div>
            </div>
        </template>
    </div>
</template>
