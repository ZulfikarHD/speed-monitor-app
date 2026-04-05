<script setup lang="ts">
/**
 * Trip Detail Page - Employee trip history detail view.
 *
 * Displays comprehensive trip information including speed chart visualization,
 * summary statistics, violation markers, and trip metadata. Allows employees
 * to review their driving history in detail with interactive speed chart.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * Features:
 * - Back navigation to trip list
 * - Summary statistics cards (duration, distance, speeds)
 * - Interactive speed chart with violation markers
 * - Violation summary badge
 * - Trip metadata display (times, notes, sync status)
 * - SpeedoMontor dark theme design
 * - Mobile-responsive layout
 *
 * @example Route: /employee/trips/{id}
 */

import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';

import SpeedChart from '@/components/trips/SpeedChart.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import type { Trip, SpeedLog } from '@/types/trip';
import { formatDate, formatTime, formatDuration } from '@/utils/date';

// ========================================================================
// Component Props
// ========================================================================

interface Props {
    /** Trip data with eager-loaded speed logs */
    trip: Trip & { speed_logs: SpeedLog[] };
    /** Speed limit setting for chart reference line */
    speedLimit: number;
}

const props = defineProps<Props>();

// ========================================================================
// Computed Properties
// ========================================================================

/**
 * Get status display text in Indonesian.
 */
const statusText = computed(() => {
    const statusMap: Record<string, string> = {
        in_progress: 'Sedang Berjalan',
        completed: 'Selesai',
        auto_stopped: 'Berhenti Otomatis',
    };

    return statusMap[props.trip.status] || props.trip.status;
});

/**
 * Get status badge color classes.
 */
const statusColor = computed(() => {
    const colorMap: Record<string, string> = {
        in_progress: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        completed: 'bg-green-500/10 text-green-400 border-green-500/20',
        auto_stopped: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
    };

    return (
        colorMap[props.trip.status] ||
        'bg-gray-500/10 text-gray-400 border-gray-500/20'
    );
});

/**
 * Get violation badge color based on count.
 */
const violationColor = computed(() => {
    if (props.trip.violation_count === 0) {
        return 'bg-green-500/10 text-green-400 border-green-500/20';
    }

    return 'bg-red-500/10 text-red-400 border-red-500/20';
});

/**
 * Format distance for display.
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
 */
function formatSpeed(speed: number | string | null): string {
    if (speed === null) {
        return '-';
    }

    const numSpeed = typeof speed === 'string' ? parseFloat(speed) : speed;

    return `${numSpeed.toFixed(1)} km/h`;
}

</script>

<template>
    <EmployeeLayout :title="`Trip Detail - ${formatDate(trip.started_at)}`">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <motion.div
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
            >
                <Link
                    href="/employee/my-trips"
                    class="mb-6 inline-flex min-h-[44px] items-center gap-2 px-4 py-3 text-sm text-cyan-600 dark:text-cyan-400 transition-colors hover:text-cyan-700 dark:text-cyan-300"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Back to My Trips
                </Link>
            </motion.div>

            <!-- Page Header -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6, delay: 0.1 }"
                class="mb-6 flex items-center gap-3"
            >
                <h1
                    class="text-3xl font-bold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ formatDate(trip.started_at) }}
                </h1>

                <!-- Status Badge -->
                <motion.span
                    :initial="{ opacity: 0, scale: 0.8 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.5, delay: 0.2 }"
                    :class="[
                        'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium',
                        statusColor,
                    ]"
                >
                    {{ statusText }}
                </motion.span>
            </motion.div>

            <motion.p
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ delay: 0.25, duration: 0.5 }"
                class="mb-6 text-sm text-zinc-600 dark:text-zinc-400"
            >
                Dimulai: {{ formatTime(trip.started_at) }}
                <span v-if="trip.ended_at">
                    • Selesai: {{ formatTime(trip.ended_at) }}
                </span>
            </motion.p>

            <!-- Statistics Grid -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.3 }"
                class="grid grid-cols-2 gap-4 sm:grid-cols-4"
            >
                <!-- Duration -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :whileHover="{ scale: 1.03, y: -4 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5, delay: 0.35 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-4"
                >
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Durasi</div>
                    <div
                        class="font-mono text-xl font-semibold text-zinc-900 dark:text-white"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatDuration(trip.duration_seconds) }}
                    </div>
                </motion.div>

                <!-- Distance -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :whileHover="{ scale: 1.03, y: -4 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5, delay: 0.4 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-4"
                >
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Jarak</div>
                    <div
                        class="font-mono text-xl font-semibold text-cyan-600 dark:text-cyan-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatDistance(trip.total_distance) }}
                    </div>
                </motion.div>

                <!-- Max Speed -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :whileHover="{ scale: 1.03, y: -4 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5, delay: 0.45 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-4"
                >
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">
                        Kecepatan Maksimal
                    </div>
                    <div
                        class="font-mono text-xl font-semibold text-red-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatSpeed(trip.max_speed) }}
                    </div>
                </motion.div>

                <!-- Average Speed -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :whileHover="{ scale: 1.03, y: -4 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5, delay: 0.5 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-4"
                >
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">
                        Kecepatan Rata-rata
                    </div>
                    <div
                        class="font-mono text-xl font-semibold text-green-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatSpeed(trip.average_speed) }}
                    </div>
                </motion.div>
            </motion.div>

            <!-- Speed Chart -->
            <SpeedChart
                :speedLogs="trip.speed_logs || []"
                :speedLimit="speedLimit"
                :isLoading="false"
            />

            <!-- Violation Summary -->
            <div
                class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-6 text-center"
            >
                <h3
                    class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Ringkasan Pelanggaran
                </h3>

                <div class="flex items-center justify-center gap-4">
                    <div class="text-6xl" aria-hidden="true">
                        {{ trip.violation_count > 0 ? '⚠️' : '✅' }}
                    </div>

                    <div class="text-left">
                        <div
                            :class="[
                                'inline-flex items-center rounded-full border px-4 py-2 font-mono text-2xl font-bold',
                                violationColor,
                            ]"
                            style="font-family: 'Share Tech Mono', monospace"
                        >
                            {{ trip.violation_count }}
                        </div>

                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{
                                trip.violation_count === 0
                                    ? 'Tidak ada pelanggaran'
                                    : trip.violation_count === 1
                                      ? '1 kali melampaui batas kecepatan'
                                      : `${trip.violation_count} kali melampaui batas kecepatan`
                            }}
                        </p>
                    </div>
                </div>

                <!-- Violation Details (if any) -->
                <div
                    v-if="trip.violation_count > 0"
                    class="mt-4 rounded-lg bg-red-500/10 p-4"
                >
                    <p class="text-sm text-red-400">
                        Perhatian: Anda telah melampaui batas kecepatan
                        <strong>{{ speedLimit }} km/h</strong> sebanyak
                        {{ trip.violation_count }} kali. Mohon berkendara dengan
                        lebih hati-hati.
                    </p>
                </div>
            </div>

            <!-- Trip Metadata -->
            <div
                class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-6 space-y-4"
            >
                <h3
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Informasi Perjalanan
                </h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Start Time -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">
                            Waktu Mulai
                        </div>
                        <div class="text-sm text-zinc-900 dark:text-white">
                            {{
                                new Date(trip.started_at).toLocaleString(
                                    'id-ID',
                                    {
                                        dateStyle: 'full',
                                        timeStyle: 'short',
                                        timeZone: 'Asia/Jakarta',
                                    },
                                )
                            }}
                        </div>
                    </div>

                    <!-- End Time -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">
                            Waktu Selesai
                        </div>
                        <div class="text-sm text-zinc-900 dark:text-white">
                            {{
                                trip.ended_at
                                    ? new Date(trip.ended_at).toLocaleString(
                                          'id-ID',
                                          {
                                              dateStyle: 'full',
                                              timeStyle: 'short',
                                              timeZone: 'Asia/Jakarta',
                                          },
                                      )
                                    : 'Belum selesai'
                            }}
                        </div>
                    </div>

                    <!-- Sync Status -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">
                            Status Sinkronisasi
                        </div>
                        <div class="text-sm text-zinc-900 dark:text-white">
                            <span
                                v-if="trip.synced_at"
                                class="inline-flex items-center gap-1 text-green-400"
                            >
                                <svg
                                    class="h-4 w-4"
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
                                Tersinkronisasi
                            </span>
                            <span v-else class="text-zinc-600 dark:text-zinc-400">
                                Tidak ada data sinkronisasi
                            </span>
                        </div>
                    </div>

                    <!-- Speed Logs Count -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">
                            Jumlah Log Kecepatan
                        </div>
                        <div
                            class="font-mono text-sm text-zinc-900 dark:text-white"
                            style="font-family: 'Share Tech Mono', monospace"
                        >
                            {{ trip.speed_logs?.length || 0 }} log
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="trip.notes" class="border-t border-zinc-200 dark:border-white/5 pt-4">
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Catatan</div>
                    <div
                        class="rounded-lg bg-zinc-100 dark:bg-zinc-900/50 p-4 text-sm text-zinc-900 dark:text-white"
                    >
                        {{ trip.notes }}
                    </div>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>

