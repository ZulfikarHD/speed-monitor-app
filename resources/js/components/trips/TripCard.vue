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
 * - Sync status badge with CSS pulse for pending sync
 * - CSS hover transitions (no motion-v)
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
import { Check, ChevronRight, Clock, CloudUpload } from '@lucide/vue';
import { computed } from 'vue';

import { showWeb } from '@/actions/App/Http/Controllers/TripController';
import type { Trip } from '@/types/trip';
import { formatDate, formatDuration, formatTime } from '@/utils/date';

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
    router.visit(showWeb.url({ trip: props.trip.id }));
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
        Trip Card — summary, badges, stats, footer
    ======================================================================= -->
    <button
        type="button"
        class="w-full rounded-lg border border-zinc-200/80 bg-white/95 p-5 text-left text-zinc-900 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 transition-all duration-200 hover:-translate-y-1 hover:border-cyan-500/50 hover:shadow-xl hover:shadow-zinc-900/10 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:border-white/10 dark:bg-zinc-800/95 dark:text-white dark:shadow-cyan-500/5 dark:ring-white/5 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
        :aria-label="`View trip details from ${formatDate(trip.started_at)}`"
        @click="handleClick"
    >
        <!-- Header: date, time, status & sync -->
        <div class="mb-4 flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h3
                    class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-2xl"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ formatDate(trip.started_at) }}
                </h3>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ formatTime(trip.started_at) }}
                    <span v-if="trip.ended_at"> — {{ formatTime(trip.ended_at) }}</span>
                </p>
            </div>

            <div class="flex shrink-0 flex-col items-end gap-2">
                <span
                    :class="[
                        'inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold',
                        getStatusColor(trip.status),
                    ]"
                >
                    {{ getStatusText(trip.status) }}
                </span>

                <span
                    :class="[
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold',
                        getSyncStatusColor(),
                    ]"
                >
                    <span
                        v-if="!isSynced"
                        class="inline-flex items-center text-current"
                        :class="{ 'animate-pulse': !isSynced }"
                        aria-hidden="true"
                    >
                        <CloudUpload class="h-3.5 w-3.5 shrink-0" :stroke-width="2" />
                    </span>
                    <span v-else class="inline-flex items-center text-current" aria-hidden="true">
                        <Check class="h-3.5 w-3.5 shrink-0" :stroke-width="2" />
                    </span>
                    <span>{{ getSyncStatusText() }}</span>
                </span>
            </div>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div
                class="rounded-lg border border-zinc-200/60 bg-zinc-100/90 p-3 dark:border-white/5 dark:bg-zinc-900/60"
            >
                <div class="mb-1 flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                    <Clock class="h-3.5 w-3.5 shrink-0" :stroke-width="2" aria-hidden="true" />
                    <span>Durasi</span>
                </div>
                <div
                    class="font-mono text-sm font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatDuration(trip.duration_seconds) }}
                </div>
            </div>

            <div
                class="rounded-lg border border-zinc-200/60 bg-zinc-100/90 p-3 dark:border-white/5 dark:bg-zinc-900/60"
            >
                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Jarak</div>
                <div
                    class="font-mono text-sm font-semibold text-cyan-700 dark:text-cyan-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatDistance(trip.total_distance) }}
                </div>
            </div>

            <div
                class="rounded-lg border border-zinc-200/60 bg-zinc-100/90 p-3 dark:border-white/5 dark:bg-zinc-900/60"
            >
                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Kec. Maks</div>
                <div
                    class="font-mono text-sm font-semibold text-red-700 dark:text-red-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatSpeed(trip.max_speed) }}
                </div>
            </div>

            <div
                class="rounded-lg border border-zinc-200/60 bg-zinc-100/90 p-3 dark:border-white/5 dark:bg-zinc-900/60"
            >
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

        <!-- Notes preview -->
        <div
            v-if="trip.notes"
            class="mt-4 border-t border-zinc-200/80 pt-4 dark:border-white/10"
        >
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Catatan:</div>
            <p class="mt-1 line-clamp-2 text-sm text-zinc-900 dark:text-white">
                {{ trip.notes }}
            </p>
        </div>

        <!-- Detail affordance -->
        <div
            class="mt-4 flex items-center justify-end gap-1 text-xs font-medium text-cyan-700 dark:text-cyan-400"
        >
            <span>Lihat Detail</span>
            <ChevronRight class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
        </div>
    </button>
</template>
