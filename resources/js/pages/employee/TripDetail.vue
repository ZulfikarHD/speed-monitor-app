<script setup lang="ts">
/**
 * Trip Detail Page - Employee trip history detail view.
 *
 * Displays comprehensive trip information including speed chart visualization,
 * summary statistics, violation markers, and trip metadata.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 *
 * @example Route: /employee/trips/{id}
 */

import { Link } from '@inertiajs/vue3';
import { AlertTriangle, Check, ChevronLeft, CircleCheck, FileText, ShieldAlert } from '@lucide/vue';
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

/** Get status display text in Indonesian. */
const statusText = computed(() => {
    const statusMap: Record<string, string> = {
        in_progress: 'Sedang Berjalan',
        completed: 'Selesai',
        auto_stopped: 'Berhenti Otomatis',
    };

    return statusMap[props.trip.status] || props.trip.status;
});

/** Get status badge color classes. */
const statusColor = computed(() => {
    const colorMap: Record<string, string> = {
        in_progress: 'bg-blue-500/10 text-blue-600 border-blue-500/20 dark:text-blue-400',
        completed: 'bg-green-500/10 text-green-600 border-green-500/20 dark:text-green-400',
        auto_stopped: 'bg-yellow-500/10 text-yellow-600 border-yellow-500/20 dark:text-yellow-400',
    };

    return colorMap[props.trip.status] || 'bg-gray-500/10 text-gray-400 border-gray-500/20';
});

/** Get violation badge color based on count. */
const violationColor = computed(() => {
    if (props.trip.violation_count === 0) {
        return 'bg-green-500/10 text-green-600 border-green-500/20 dark:text-green-400';
    }

    return 'bg-red-500/10 text-red-600 border-red-500/20 dark:text-red-400';
});

/** Format distance for display. */
function formatDistance(distance: number | string | null): string {
    if (distance === null) {
        return '-';
    }

    const numDistance = typeof distance === 'string' ? parseFloat(distance) : distance;

    return `${numDistance.toFixed(2)} km`;
}

/** Format speed for display. */
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
            <Link
                href="/employee/my-trips"
                class="mb-6 inline-flex min-h-[44px] items-center gap-2 px-4 py-3 text-sm font-medium text-cyan-600 transition-colors duration-200 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300"
            >
                <ChevronLeft :size="16" :stroke-width="2" aria-hidden="true" />
                Kembali ke Perjalanan Saya
            </Link>

            <!-- Page Header -->
            <div class="mb-6 flex items-center gap-3">
                <h1
                    class="text-3xl font-bold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ formatDate(trip.started_at) }}
                </h1>
                <span
                    :class="[
                        'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium',
                        statusColor,
                    ]"
                >
                    {{ statusText }}
                </span>
            </div>

            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                Dimulai: {{ formatTime(trip.started_at) }}
                <span v-if="trip.ended_at">
                    &bull; Selesai: {{ formatTime(trip.ended_at) }}
                </span>
            </p>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <!-- Duration -->
                <div class="rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Durasi</div>
                    <div
                        class="font-mono text-xl font-semibold text-zinc-900 dark:text-white"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatDuration(trip.duration_seconds) }}
                    </div>
                </div>

                <!-- Distance -->
                <div class="rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Jarak</div>
                    <div
                        class="font-mono text-xl font-semibold text-cyan-600 dark:text-cyan-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatDistance(trip.total_distance) }}
                    </div>
                </div>

                <!-- Max Speed -->
                <div class="rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Kecepatan Maksimal</div>
                    <div
                        class="font-mono text-xl font-semibold text-red-600 dark:text-red-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatSpeed(trip.max_speed) }}
                    </div>
                </div>

                <!-- Average Speed -->
                <div class="rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                    <div class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Kecepatan Rata-rata</div>
                    <div
                        class="font-mono text-xl font-semibold text-emerald-600 dark:text-emerald-400"
                        style="font-family: 'Share Tech Mono', monospace"
                    >
                        {{ formatSpeed(trip.average_speed) }}
                    </div>
                </div>
            </div>

            <!-- Speed Chart -->
            <div class="mt-6">
                <SpeedChart
                    :speedLogs="trip.speed_logs || []"
                    :speedLimit="speedLimit"
                    :isLoading="false"
                />
            </div>

            <!-- Violation Summary -->
            <div class="mt-6 rounded-lg border border-zinc-200/80 bg-white/95 p-6 text-center shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                <h3
                    class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Ringkasan Pelanggaran
                </h3>

                <div class="flex items-center justify-center gap-4">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full"
                        :class="trip.violation_count > 0 ? 'bg-red-100 dark:bg-red-500/15' : 'bg-emerald-100 dark:bg-emerald-500/15'"
                        aria-hidden="true"
                    >
                        <ShieldAlert
                            v-if="trip.violation_count > 0"
                            :size="32"
                            :stroke-width="2"
                            class="text-red-600 dark:text-red-400"
                        />
                        <CircleCheck
                            v-else
                            :size="32"
                            :stroke-width="2"
                            class="text-emerald-600 dark:text-emerald-400"
                        />
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

                <!-- Violation Details -->
                <div
                    v-if="trip.violation_count > 0"
                    class="mt-4 flex items-start gap-2 rounded-lg bg-red-50 p-4 text-left dark:bg-red-500/10"
                >
                    <AlertTriangle :size="16" :stroke-width="2" class="mt-0.5 shrink-0 text-red-500 dark:text-red-400" aria-hidden="true" />
                    <p class="text-sm text-red-700 dark:text-red-300">
                        Perhatian: Anda telah melampaui batas kecepatan
                        <strong>{{ speedLimit }} km/h</strong> sebanyak
                        {{ trip.violation_count }} kali. Mohon berkendara dengan lebih hati-hati.
                    </p>
                </div>
            </div>

            <!-- Trip Metadata -->
            <div class="mt-6 rounded-lg border border-zinc-200/80 bg-white/95 p-6 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                <h3
                    class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Informasi Perjalanan
                </h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Start Time -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">Waktu Mulai</div>
                        <div class="text-sm text-zinc-900 dark:text-white">
                            {{ new Date(trip.started_at).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short', timeZone: 'Asia/Jakarta' }) }}
                        </div>
                    </div>

                    <!-- End Time -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">Waktu Selesai</div>
                        <div class="text-sm text-zinc-900 dark:text-white">
                            {{ trip.ended_at
                                ? new Date(trip.ended_at).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short', timeZone: 'Asia/Jakarta' })
                                : 'Belum selesai' }}
                        </div>
                    </div>

                    <!-- Sync Status -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">Status Sinkronisasi</div>
                        <div class="text-sm text-zinc-900 dark:text-white">
                            <span v-if="trip.synced_at" class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                <Check :size="16" :stroke-width="2" aria-hidden="true" />
                                Tersinkronisasi
                            </span>
                            <span v-else class="text-zinc-600 dark:text-zinc-400">
                                Tidak ada data sinkronisasi
                            </span>
                        </div>
                    </div>

                    <!-- Speed Logs Count -->
                    <div>
                        <div class="mb-1 text-xs text-zinc-600 dark:text-zinc-400">Jumlah Log Kecepatan</div>
                        <div
                            class="font-mono text-sm text-zinc-900 dark:text-white"
                            style="font-family: 'Share Tech Mono', monospace"
                        >
                            {{ trip.speed_logs?.length || 0 }} log
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="trip.notes" class="mt-4 border-t border-zinc-200/80 pt-4 dark:border-white/10">
                    <div class="mb-2 flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                        <FileText :size="14" :stroke-width="2" aria-hidden="true" />
                        <span>Catatan</span>
                    </div>
                    <div class="rounded-lg bg-zinc-100/90 p-4 text-sm text-zinc-900 dark:bg-zinc-900/50 dark:text-white">
                        {{ trip.notes }}
                    </div>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>
