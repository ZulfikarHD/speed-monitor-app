<script setup lang="ts">
/**
 * All Trips Page - Superuser trip monitoring view.
 *
 * Comprehensive trip monitoring page for superusers and admins with
 * advanced filtering, sorting, and pagination capabilities.
 *
 * Features:
 * - Server-side pagination (20 per page)
 * - Advanced filtering (employee, date range, status, violations)
 * - Sorting (date, violations, distance, duration)
 * - Responsive design (table on desktop, cards on mobile)
 * - Lightweight opacity/y animations
 * - Empty states with SVG icons
 * - Full light/dark theme support
 *
 * @example Route: /superuser/trips
 */

import { Deferred, router } from '@inertiajs/vue3';
import { Road } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref } from 'vue';

import { showWeb } from '@/actions/App/Http/Controllers/TripController';
import AvgSpeedChart from '@/components/charts/AvgSpeedChart.vue';
import MaxSpeedChart from '@/components/charts/MaxSpeedChart.vue';
import VehicleDistributionChart from '@/components/charts/VehicleDistributionChart.vue';
import ViolationChart from '@/components/charts/ViolationChart.vue';
import Pagination from '@/components/trips/Pagination.vue';
import SuperuserTripFilters from '@/components/trips/SuperuserTripFilters.vue';
import SuperuserLayout from '@/layouts/SuperuserLayout.vue';
import type { EmployeeSummary, Trip, TripStatus } from '@/types/trip';
import {
    formatDate,
    formatDuration,
    formatShortDate,
    formatTime,
} from '@/utils/date';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Speed limit from settings */
    speedLimit: number;

    /** Trips for current page */
    trips: Trip[];

    /** List of employees for filtering */
    employees: EmployeeSummary[];

    /** Pagination metadata */
    meta: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };

    /** Current filter values */
    filters: {
        user_id: number | null;
        date_from: string;
        date_to: string;
        status: TripStatus | '';
        violations_only: boolean;
        vehicle_type: string;
    };

    /** Current sort configuration */
    sort: {
        by: string;
        order: 'asc' | 'desc';
    };

    /** Chart data (deferred) */
    chartData?: {
        avgSpeedVsStandard: Array<{ label: string; date: string; avg_speed: number; speed_limit: number }>;
        maxSpeedVsStandard: Array<{ label: string; date: string; max_speed: number; speed_limit: number }>;
        violationsByEmployee: Array<{ name: string; violations: number }>;
        vehicleDistribution: { mobil: number; motor: number };
    };
}

const props = defineProps<Props>();

// ========================================================================
// Local State
// ========================================================================

/** Local filter state (synced with props) */
const localFilters = ref({
    employee: props.filters.user_id,
    dateFrom: props.filters.date_from,
    dateTo: props.filters.date_to,
    status: props.filters.status,
    violationsOnly: props.filters.violations_only,
    vehicleType: props.filters.vehicle_type,
});

/** Local sort state (synced with props) */
const localSort = ref({
    by: props.sort.by,
    order: props.sort.order as 'asc' | 'desc',
});

// ========================================================================
// Computed
// ========================================================================

/** Check if there are any active filters. */
const hasActiveFilters = computed(() => {
    return !!(
        props.filters.user_id ||
        props.filters.date_from ||
        props.filters.date_to ||
        props.filters.status ||
        props.filters.violations_only ||
        props.filters.vehicle_type
    );
});

/** Check if showing empty state. */
const showEmptyState = computed(() => {
    return props.trips.length === 0;
});

/**
 * Get employee name by ID.
 *
 * @param userId - Employee user ID
 * @returns Employee name or "Unknown"
 */
function getEmployeeName(userId: number): string {
    const employee = props.employees.find((emp) => emp.id === userId);

    return employee ? employee.name : 'Unknown';
}

/**
 * Get employee email by ID.
 *
 * @param userId - Employee user ID
 * @returns Employee email or empty string
 */
function getEmployeeEmail(userId: number): string {
    const employee = props.employees.find((emp) => emp.id === userId);

    return employee ? employee.email : '';
}

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle filter apply - trigger server-side refetch.
 */
function handleApplyFilters(): void {
    router.get(
        '/superuser/trips',
        {
            user_id: localFilters.value.employee || undefined,
            date_from: localFilters.value.dateFrom || undefined,
            date_to: localFilters.value.dateTo || undefined,
            status: localFilters.value.status || undefined,
            violations_only: localFilters.value.violationsOnly || undefined,
            vehicle_type: localFilters.value.vehicleType || undefined,
            sort_by: localSort.value.by,
            sort_order: localSort.value.order,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
}

/**
 * Handle filter reset - clear all filters.
 */
function handleResetFilters(): void {
    localFilters.value = {
        employee: null,
        dateFrom: '',
        dateTo: '',
        status: '',
        violationsOnly: false,
        vehicleType: '',
    };

    localSort.value = {
        by: 'started_at',
        order: 'desc',
    };

    router.get(
        '/superuser/trips',
        { page: 1 },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
}

/**
 * Handle page change from pagination.
 *
 * @param page - New page number
 */
function handlePageChange(page: number): void {
    router.get(
        '/superuser/trips',
        {
            page,
            user_id: props.filters.user_id || undefined,
            date_from: props.filters.date_from || undefined,
            date_to: props.filters.date_to || undefined,
            status: props.filters.status || undefined,
            violations_only: props.filters.violations_only || undefined,
            vehicle_type: props.filters.vehicle_type || undefined,
            sort_by: props.sort.by,
            sort_order: props.sort.order,
        },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
}

/**
 * Navigate to trip detail page.
 *
 * @param tripId - Trip ID to view
 */
function navigateToTrip(tripId: number): void {
    router.visit(showWeb.url({ trip: tripId }));
}

// ========================================================================
// Formatting Helpers
// ========================================================================

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

/** Get status display text. */
function getStatusText(status: string): string {
    const statusMap: Record<string, string> = {
        in_progress: 'Sedang Berjalan',
        completed: 'Selesai',
        auto_stopped: 'Berhenti Otomatis',
    };

    return statusMap[status] || status;
}

/** Get status badge color classes (theme-aware). */
function getStatusColor(status: string): string {
    const colorMap: Record<string, string> = {
        in_progress: 'bg-blue-500/20 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400 border-blue-500/30',
        completed: 'bg-emerald-500/20 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
        auto_stopped: 'bg-amber-500/20 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-500/30',
    };

    return colorMap[status] || 'bg-zinc-500/20 dark:bg-zinc-500/15 text-zinc-700 dark:text-zinc-400 border-zinc-500/30';
}

/** Get shift type display text. */
function getShiftLabel(shiftType: string | null): string {
    const map: Record<string, string> = {
        non_shift: 'Non Shift',
        shift_pagi: 'Shift Pagi',
        shift_malam: 'Shift Malam',
    };

    return shiftType ? map[shiftType] ?? '-' : '-';
}

/** Get vehicle type display text. */
function getVehicleLabel(vehicleType: string | null): string {
    const map: Record<string, string> = {
        mobil: 'Mobil',
        motor: 'Motor',
    };

    return vehicleType ? map[vehicleType] ?? '-' : '-';
}

/** Get violation badge color (theme-aware). */
function getViolationColor(count: number): string {
    if (count === 0) {
        return 'bg-emerald-500/20 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30';
    }

    return 'bg-red-500/20 dark:bg-red-500/15 text-red-700 dark:text-red-400 border-red-500/30';
}
</script>

<template>
    <SuperuserLayout title="Semua Perjalanan">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <motion.div
                :initial="{ opacity: 0, y: -12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3 }"
                class="mb-6 flex items-start justify-between gap-4"
            >
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white md:text-4xl">
                        Semua Perjalanan
                    </h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Pantau semua perjalanan karyawan dengan filter lanjutan
                    </p>
                </div>
            </motion.div>

            <!-- Filters Section -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.05, duration: 0.3 }"
                class="mb-6"
            >
                <SuperuserTripFilters
                    :employees="employees"
                    v-model:employee="localFilters.employee"
                    v-model:date-from="localFilters.dateFrom"
                    v-model:date-to="localFilters.dateTo"
                    v-model:status="localFilters.status"
                    v-model:violations-only="localFilters.violationsOnly"
                    v-model:vehicle-type="localFilters.vehicleType"
                    v-model:sort-by="localSort.by"
                    v-model:sort-order="localSort.order"
                    @apply="handleApplyFilters"
                    @reset="handleResetFilters"
                />
            </motion.div>

            <!-- Results Info -->
            <AnimatePresence>
                <motion.div
                    v-if="hasActiveFilters && meta.total > 0"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="{ duration: 0.2 }"
                    class="mb-4 flex items-center justify-between rounded-lg border border-cyan-500/30 bg-cyan-100 dark:bg-cyan-500/10 px-4 py-3"
                >
                    <span class="text-sm text-cyan-700 dark:text-cyan-400">
                        Ditemukan {{ meta.total }} perjalanan
                    </span>
                </motion.div>
            </AnimatePresence>

            <!-- Trips Content -->
            <div class="space-y-6">
                <!-- Empty State -->
                <motion.div
                    v-if="showEmptyState"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ duration: 0.3 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 px-6 py-12 text-center shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                >
                    <Road :size="64" :stroke-width="1.5" class="mx-auto mb-4 text-zinc-400 dark:text-zinc-600" aria-hidden="true" />
                    <p class="text-lg font-medium text-zinc-900 dark:text-white">
                        Tidak Ada Perjalanan
                    </p>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ hasActiveFilters ? 'Tidak ada perjalanan yang sesuai dengan filter' : 'Belum ada perjalanan tercatat' }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 transition-all duration-200 hover:shadow-xl active:scale-95"
                        @click="handleResetFilters"
                    >
                        Reset Filter
                    </button>
                </motion.div>

                <!-- Desktop Table View -->
                <div
                    v-if="!showEmptyState"
                    class="hidden overflow-hidden rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 md:block"
                >
                    <table
                        class="w-full"
                        role="table"
                        aria-label="Semua perjalanan karyawan"
                    >
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Karyawan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Jarak</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Kec. Rata-Rata</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Kec. Maks</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Kec. Standar</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Shift</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Kendaraan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Pelanggaran</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400" scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-white/5">
                            <motion.tr
                                v-for="(trip, index) in trips"
                                :key="trip.id"
                                :initial="{ opacity: 0, y: 8 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: index * 0.03, duration: 0.25 }"
                                class="cursor-pointer transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-white/5"
                                @click="navigateToTrip(trip.id)"
                            >
                                <td class="px-4 py-4">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ getEmployeeName(trip.user_id) }}</p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ getEmployeeEmail(trip.user_id) }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div>
                                        <p class="text-sm text-zinc-900 dark:text-white">{{ formatShortDate(trip.started_at) }}</p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ formatTime(trip.started_at) }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono text-sm text-zinc-900 dark:text-white">{{ formatDuration(trip.duration_seconds) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono text-sm text-cyan-600 dark:text-cyan-400">{{ formatDistance(trip.total_distance) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono text-sm text-blue-600 dark:text-blue-400">{{ formatSpeed(trip.average_speed) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono text-sm text-red-600 dark:text-red-400">{{ formatSpeed(trip.max_speed) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono text-sm text-emerald-600 dark:text-emerald-400">{{ speedLimit }} km/h</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-zinc-900 dark:text-white">{{ getShiftLabel(trip.shift_type) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-zinc-900 dark:text-white">{{ getVehicleLabel(trip.vehicle_type) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        :class="['inline-flex items-center rounded-full border px-2 py-0.5 font-mono text-xs font-semibold', getViolationColor(trip.violation_count)]"
                                    >
                                        {{ trip.violation_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        :class="['inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium', getStatusColor(trip.status)]"
                                    >
                                        {{ getStatusText(trip.status) }}
                                    </span>
                                </td>
                            </motion.tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div
                    v-if="!showEmptyState"
                    class="grid gap-4 md:hidden"
                >
                    <motion.div
                        v-for="(trip, index) in trips"
                        :key="trip.id"
                        :initial="{ opacity: 0, y: 8 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: index * 0.03, duration: 0.25 }"
                        class="cursor-pointer rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 transition-all duration-200 hover:border-cyan-500/50 dark:hover:border-cyan-500/50"
                        @click="navigateToTrip(trip.id)"
                    >
                        <!-- Employee Info -->
                        <div class="mb-3 flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-medium text-zinc-900 dark:text-white">{{ getEmployeeName(trip.user_id) }}</h3>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ getEmployeeEmail(trip.user_id) }}</p>
                            </div>
                            <span :class="['inline-flex items-center rounded-full border px-2 py-1 text-xs font-medium', getStatusColor(trip.status)]">
                                {{ getStatusText(trip.status) }}
                            </span>
                        </div>

                        <!-- Date/Time -->
                        <div class="mb-3 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ formatDate(trip.started_at) }}, {{ formatTime(trip.started_at) }}
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Durasi</div>
                                <div class="font-mono text-sm font-semibold text-zinc-900 dark:text-white">{{ formatDuration(trip.duration_seconds) }}</div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Jarak</div>
                                <div class="font-mono text-sm font-semibold text-cyan-600 dark:text-cyan-400">{{ formatDistance(trip.total_distance) }}</div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Kec. Rata-Rata</div>
                                <div class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">{{ formatSpeed(trip.average_speed) }}</div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Kec. Maks</div>
                                <div class="font-mono text-sm font-semibold text-red-600 dark:text-red-400">{{ formatSpeed(trip.max_speed) }}</div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Kec. Standar</div>
                                <div class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ speedLimit }} km/h</div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Pelanggaran</div>
                                <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 font-mono text-xs font-semibold', getViolationColor(trip.violation_count)]">
                                    {{ trip.violation_count }}
                                </span>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Shift</div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ getShiftLabel(trip.shift_type) }}</div>
                            </div>

                            <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-2">
                                <div class="mb-1 text-xs text-zinc-500 dark:text-zinc-400">Kendaraan</div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ getVehicleLabel(trip.vehicle_type) }}</div>
                            </div>
                        </div>
                    </motion.div>
                </div>

                <!-- Pagination -->
                <div v-if="!showEmptyState && meta.last_page > 1" class="mt-8">
                    <Pagination
                        :current-page="meta.current_page"
                        :last-page="meta.last_page"
                        :total="meta.total"
                        @page-change="handlePageChange"
                    />
                </div>

                <!-- Charts Section -->
                <Deferred data="chartData">
                    <template #fallback>
                        <div class="mt-8 grid gap-6 lg:grid-cols-2">
                            <div v-for="i in 4" :key="i" class="rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 p-5 shadow-lg">
                                <div class="mb-4">
                                    <div class="h-5 w-40 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                    <div class="mt-2 h-3 w-56 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                </div>
                                <div class="flex h-48 items-center justify-center">
                                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <motion.div
                        v-if="chartData"
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.4 }"
                        class="mt-8"
                    >
                        <h2 class="mb-4 text-xl font-bold text-zinc-900 dark:text-white">Grafik Analisis</h2>
                        <div class="grid gap-6 lg:grid-cols-2">
                            <AvgSpeedChart :data="chartData.avgSpeedVsStandard" />
                            <MaxSpeedChart :data="chartData.maxSpeedVsStandard" />
                            <ViolationChart :data="chartData.violationsByEmployee" />
                            <VehicleDistributionChart :data="chartData.vehicleDistribution" />
                        </div>
                    </motion.div>
                </Deferred>
            </div>
        </div>
    </SuperuserLayout>
</template>
