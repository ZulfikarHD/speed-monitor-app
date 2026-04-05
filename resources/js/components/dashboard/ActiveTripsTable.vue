<script setup lang="ts">
/**
 * ActiveTripsTable Component
 *
 * Real-time active trips monitoring table for supervisor dashboard.
 * Displays currently in-progress trips with live duration updates.
 *
 * Features:
 * - Responsive layout (table on desktop, cards on mobile)
 * - Live duration counter (updates every second)
 * - Employee name/email display
 * - Empty state when no active trips
 * - motion-v stagger animations
 * - ARIA accessibility with proper table semantics
 * - Skeleton loading state
 */

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
    // Update current time every second for live duration display
    intervalId = window.setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    // Cleanup interval to prevent memory leaks
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
 * Converts seconds to "Xh Ym Zs" format for easy readability.
 * Updates every second based on current time.
 *
 * @param startedAt - ISO8601 timestamp when trip started
 * @returns Formatted duration string
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
    <div class="overflow-hidden rounded-lg border border-[#3E3E3A] bg-[#161615]">
        <!-- ======================================================================
            Header
        ======================================================================= -->
        <div class="flex items-start justify-between border-b border-[#3E3E3A] px-6 py-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-[#EDEDEC]">
                    Active Trips
                </h3>
                <p class="mt-1 text-sm text-[#A1A09A]">
                    Real-time monitoring of ongoing employee trips
                </p>
            </div>
            <div v-if="$slots.actions" class="ml-4">
                <slot name="actions" />
            </div>
        </div>

        <!-- ======================================================================
            Loading State (Skeleton)
        ======================================================================= -->
        <div
            v-if="isLoading"
            class="divide-y divide-[#3E3E3A]"
        >
            <div
                v-for="i in 3"
                :key="`skeleton-${i}`"
                class="px-6 py-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-1/3 animate-pulse rounded bg-[#3E3E3A]"></div>
                        <div class="h-3 w-1/4 animate-pulse rounded bg-[#3E3E3A]"></div>
                    </div>
                    <div class="h-8 w-20 animate-pulse rounded bg-[#3E3E3A]"></div>
                </div>
            </div>
        </div>

        <!-- ======================================================================
            Empty State
        ======================================================================= -->
        <div
            v-else-if="!hasTrips"
            class="px-6 py-12 text-center"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ duration: 0.4 }"
            >
                <div class="mb-4 text-5xl">🚗</div>
                <p class="text-lg font-medium text-[#EDEDEC]">
                    No Active Trips
                </p>
                <p class="mt-2 text-sm text-[#A1A09A]">
                    All employees have completed their trips
                </p>
            </motion.div>
        </div>

        <!-- ======================================================================
            Data Views (Desktop Table + Mobile Cards)
        ======================================================================= -->
        <template v-else>
            <!-- Desktop Table View (≥768px) -->
            <div class="hidden md:block">
                <table
                    class="w-full"
                    role="table"
                    aria-label="Active trips table"
                >
                    <thead>
                        <tr class="border-b border-[#3E3E3A] bg-[#0a0a0a]">
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                scope="col"
                            >
                                Employee
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                scope="col"
                            >
                                Duration
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3E3E3A]">
                        <motion.tr
                            v-for="(trip, index) in trips"
                            :key="trip.id"
                            :initial="{ opacity: 0, x: -20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{
                                delay: index * 0.05,
                                duration: 0.4,
                                type: 'spring',
                                bounce: 0.3,
                            }"
                            class="transition-colors hover:bg-[#1a1a19]"
                        >
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-[#EDEDEC]">
                                        {{ trip.user.name }}
                                    </p>
                                    <p class="mt-1 text-sm text-[#A1A09A]">
                                        {{ trip.user.email }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-400"
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

            <!-- Mobile Card View (<768px) -->
            <div class="divide-y divide-[#3E3E3A] md:hidden">
            <motion.div
                v-for="(trip, index) in trips"
                :key="trip.id"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{
                    delay: index * 0.05,
                    duration: 0.4,
                    type: 'spring',
                    bounce: 0.3,
                }"
                class="px-4 py-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="font-medium text-[#EDEDEC]">
                            {{ trip.user.name }}
                        </p>
                        <p class="mt-1 text-sm text-[#A1A09A]">
                            {{ trip.user.email }}
                        </p>
                    </div>
                    <div>
                        <span
                            class="inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-xs font-medium text-blue-400"
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
