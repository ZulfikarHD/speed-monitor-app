<script setup lang="ts">
/**
 * TripCard Component
 *
 * Displays a summary of a single trip with key metrics.
 * Click-able card that navigates to trip detail page (US-4.2 future).
 *
 * @example
 * ```vue
 * <TripCard
 *   v-for="trip in trips"
 *   :key="trip.id"
 *   :trip="trip"
 *   @click="handleTripClick(trip.id)"
 * />
 * ```
 */

import type { Trip } from '@/types/trip';
import { formatDate, formatTime, formatDuration } from '@/utils/date';

/**
 * TripCard component props.
 */
interface TripCardProps {
    /** Trip data to display */
    trip: Trip;
}

/**
 * TripCard component emits.
 */
interface TripCardEmits {
    (event: 'click', tripId: number): void;
}

const props = defineProps<TripCardProps>();
const emit = defineEmits<TripCardEmits>();

/**
 * Format distance for display.
 *
 * @param distance - Distance in kilometers
 * @returns Formatted distance string (e.g., "12.5 km")
 */
function formatDistance(distance: number | null): string {
    if (distance === null) {
return '-';
}

    return `${distance.toFixed(2)} km`;
}

/**
 * Format speed for display.
 *
 * @param speed - Speed in km/h
 * @returns Formatted speed string (e.g., "65 km/h")
 */
function formatSpeed(speed: number | null): string {
    if (speed === null) {
return '-';
}

    return `${speed.toFixed(1)} km/h`;
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
 * Handle card click.
 */
function handleClick(): void {
    // TODO: Navigate to trip detail page (US-4.2)
    emit('click', props.trip.id);
}
</script>

<template>
    <!-- ======================================================================
        Trip Card
        Interactive card displaying trip summary with hover effects
    ======================================================================= -->
    <button
        @click="handleClick"
        class="w-full rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-5 text-left transition-all hover:border-cyan-500/50 hover:bg-[#2a2d33] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
        :aria-label="`View trip details from ${formatDate(trip.started_at)}`"
    >
        <!-- Header: Date and Status -->
        <div class="mb-4 flex items-start justify-between gap-4">
            <div class="flex-1">
                <h3
                    class="text-lg font-semibold text-[#e5e7eb]"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ formatDate(trip.started_at) }}
                </h3>
                <p class="mt-1 text-sm text-[#9ca3af]">
                    {{ formatTime(trip.started_at) }}
                    <span v-if="trip.ended_at">
                        - {{ formatTime(trip.ended_at) }}
                    </span>
                </p>
            </div>

            <!-- Status Badge -->
            <span
                :class="[
                    'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium',
                    getStatusColor(trip.status),
                ]"
            >
                {{ getStatusText(trip.status) }}
            </span>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <!-- Duration -->
            <div class="rounded-lg bg-[#0a0c0f] p-3">
                <div class="mb-1 text-xs text-[#9ca3af]">Durasi</div>
                <div
                    class="font-mono text-sm font-semibold text-[#e5e7eb]"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatDuration(trip.duration_seconds) }}
                </div>
            </div>

            <!-- Distance -->
            <div class="rounded-lg bg-[#0a0c0f] p-3">
                <div class="mb-1 text-xs text-[#9ca3af]">Jarak</div>
                <div
                    class="font-mono text-sm font-semibold text-cyan-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatDistance(trip.total_distance) }}
                </div>
            </div>

            <!-- Max Speed -->
            <div class="rounded-lg bg-[#0a0c0f] p-3">
                <div class="mb-1 text-xs text-[#9ca3af]">Kec. Maks</div>
                <div
                    class="font-mono text-sm font-semibold text-red-400"
                    style="font-family: 'Share Tech Mono', monospace"
                >
                    {{ formatSpeed(trip.max_speed) }}
                </div>
            </div>

            <!-- Violations -->
            <div class="rounded-lg bg-[#0a0c0f] p-3">
                <div class="mb-1 text-xs text-[#9ca3af]">Pelanggaran</div>
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
        <div v-if="trip.notes" class="mt-4 border-t border-[#3E3E3A] pt-4">
            <div class="text-xs text-[#9ca3af]">Catatan:</div>
            <p class="mt-1 line-clamp-2 text-sm text-[#e5e7eb]">
                {{ trip.notes }}
            </p>
        </div>

        <!-- Click Indicator -->
        <div class="mt-4 flex items-center justify-end text-xs text-cyan-500">
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
    </button>
</template>
