<script setup lang="ts">
/**
 * All Trips Page - Supervisor trip monitoring view.
 *
 * Comprehensive trip monitoring page for supervisors and admins with
 * advanced filtering, sorting, and pagination capabilities. Displays
 * trips from all employees with detailed metrics and status information.
 *
 * Features:
 * - Server-side pagination (20 per page)
 * - Advanced filtering (employee, date range, status, violations)
 * - Sorting (date, violations, distance, duration)
 * - Responsive design (table on desktop, cards on mobile)
 * - CSV export button (placeholder for US-6.7)
 * - motion-v animations following Law of UX
 * - Empty states
 * - Loading states
 *
 * @example Route: /supervisor/trips
 */

import { router } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref } from 'vue';

import { index as allTripsIndex } from '@/actions/App/Http/Controllers/Supervisor/AllTripsController';
import Pagination from '@/components/trips/Pagination.vue';
import SupervisorTripFilters from '@/components/trips/SupervisorTripFilters.vue';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
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
    };

    /** Current sort configuration */
    sort: {
        by: string;
        order: 'asc' | 'desc';
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
});

/** Local sort state (synced with props) */
const localSort = ref({
    by: props.sort.by,
    order: props.sort.order as 'asc' | 'desc',
});

// ========================================================================
// Computed
// ========================================================================

/**
 * Check if there are any active filters.
 */
const hasActiveFilters = computed(() => {
    return !!(
        props.filters.user_id ||
        props.filters.date_from ||
        props.filters.date_to ||
        props.filters.status ||
        props.filters.violations_only
    );
});

/**
 * Check if showing empty state.
 */
const showEmptyState = computed(() => {
    return props.trips.length === 0;
});

/**
 * Get employee name by ID.
 */
function getEmployeeName(userId: number): string {
    const employee = props.employees.find((emp) => emp.id === userId);

    return employee ? employee.name : 'Unknown';
}

/**
 * Get employee email by ID.
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
        '/supervisor/trips',
        {
            user_id: localFilters.value.employee || undefined,
            date_from: localFilters.value.dateFrom || undefined,
            date_to: localFilters.value.dateTo || undefined,
            status: localFilters.value.status || undefined,
            violations_only: localFilters.value.violationsOnly || undefined,
            sort_by: localSort.value.by,
            sort_order: localSort.value.order,
            page: 1, // Reset to first page
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
    };

    localSort.value = {
        by: 'started_at',
        order: 'desc',
    };

    router.get(
        '/supervisor/trips',
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
        '/supervisor/trips',
        {
            page,
            user_id: props.filters.user_id || undefined,
            date_from: props.filters.date_from || undefined,
            date_to: props.filters.date_to || undefined,
            status: props.filters.status || undefined,
            violations_only: props.filters.violations_only || undefined,
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
    router.visit(`/employee/trips/${tripId}`);
}

/** Loading state for CSV export */
const isExporting = ref(false);

/**
 * Handle CSV export with applied filters.
 *
 * Triggers browser download via direct navigation to export URL with
 * current filter parameters. Shows loading state during export.
 */
function handleExport(): void {
    if (isExporting.value) {
        return;
    }

    isExporting.value = true;

    // Build export URL with current filters
    const params = new URLSearchParams();

    if (props.filters.user_id) {
        params.append('user_id', String(props.filters.user_id));
    }

    if (props.filters.date_from) {
        params.append('date_from', props.filters.date_from);
    }

    if (props.filters.date_to) {
        params.append('date_to', props.filters.date_to);
    }

    if (props.filters.status) {
        params.append('status', props.filters.status);
    }

    if (props.filters.violations_only) {
        params.append('violations_only', 'true');
    }

    // Trigger download via window.location
    const exportUrl = '/supervisor/trips/export?' + params.toString();
    window.location.href = exportUrl;

    // Reset loading state after delay (download doesn't trigger unload)
    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
}

/**
 * Clear employee filter and keep other filters.
 *
 * Navigates back to all trips view while preserving date range filters.
 * Used when dismissing the contextual banner.
 */
function clearEmployeeFilter(): void {
    router.visit(allTripsIndex.url({
        query: {
            date_from: props.filters.date_from || undefined,
            date_to: props.filters.date_to || undefined,
        },
    }));
}

// ========================================================================
// Formatting Helpers
// ========================================================================

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

/**
 * Get status display text.
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
 */
function getViolationColor(count: number): string {
    if (count === 0) {
        return 'bg-green-500/10 text-green-400 border-green-500/20';
    }

    return 'bg-red-500/10 text-red-400 border-red-500/20';
}
</script>

<template>
    <SupervisorLayout title="All Trips">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <motion.div
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="mb-6 flex items-start justify-between gap-4"
            >
                <div class="flex-1">
                    <h1
                        class="text-3xl font-bold text-[#e5e7eb]"
                        style="font-family: 'Bebas Neue', sans-serif"
                    >
                        All Trips
                    </h1>
                    <p class="mt-1 text-sm text-[#9ca3af]">
                        Monitor all employee trips with advanced filtering
                    </p>
                </div>

                <!-- Export Button -->
                <motion.button
                    @click="handleExport"
                    :disabled="isExporting || showEmptyState"
                    :animate="{
                        scale: isExporting ? 0.95 : 1,
                        opacity: isExporting ? 0.7 : 1,
                    }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.3 }"
                    :class="[
                        'flex h-12 items-center gap-2 rounded-lg border px-4 text-sm font-medium transition-all',
                        isExporting || showEmptyState
                            ? 'cursor-not-allowed border-[#3E3E3A] bg-[#1a1d23] text-[#9ca3af] opacity-50'
                            : 'border-cyan-500/30 bg-cyan-500/10 text-cyan-400 hover:border-cyan-500/50 hover:bg-cyan-500/20',
                    ]"
                    :title="
                        showEmptyState
                            ? 'Tidak ada data untuk di-export'
                            : 'Export trips to CSV'
                    "
                    aria-label="Export trips to CSV"
                >
                    <!-- Loading Spinner (when exporting) -->
                    <svg
                        v-if="isExporting"
                        class="h-5 w-5 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>

                    <!-- Download Icon (when idle) -->
                    <svg
                        v-else
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>

                    <span class="hidden sm:inline">
                        {{ isExporting ? 'Exporting...' : 'Export CSV' }}
                    </span>
                </motion.button>
            </motion.div>

            <!-- Filters Section -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.1 }"
                class="mb-6"
            >
                <SupervisorTripFilters
                    :employees="employees"
                    v-model:employee="localFilters.employee"
                    v-model:date-from="localFilters.dateFrom"
                    v-model:date-to="localFilters.dateTo"
                    v-model:status="localFilters.status"
                    v-model:violations-only="localFilters.violationsOnly"
                    v-model:sort-by="localSort.by"
                    v-model:sort-order="localSort.order"
                    @apply="handleApplyFilters"
                    @reset="handleResetFilters"
                />
            </motion.div>

            <!-- Contextual Banner: Viewing Employee Violations -->
            <AnimatePresence>
                <motion.div
                    v-if="filters.user_id && filters.violations_only"
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -20 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.5 }"
                    class="mb-4 rounded-lg border border-cyan-500/30 bg-cyan-500/10 p-4"
                    role="status"
                    aria-live="polite"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <!-- Info Icon -->
                            <svg
                                class="mt-0.5 h-5 w-5 shrink-0 text-cyan-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                            <div>
                                <p class="text-sm font-medium text-cyan-300">
                                    Menampilkan perjalanan pelanggaran untuk: {{ getEmployeeName(filters.user_id) }}
                                </p>
                                <p class="mt-1 text-xs text-cyan-400/80">
                                    Dari leaderboard • {{ filters.date_from }} sampai {{ filters.date_to }}
                                </p>
                            </div>
                        </div>

                        <!-- Close Button -->
                        <button
                            @click="clearEmployeeFilter"
                            type="button"
                            class="shrink-0 text-cyan-400 transition-colors hover:text-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
                            title="Tampilkan semua perjalanan"
                            aria-label="Tutup filter karyawan"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- Results Info -->
            <AnimatePresence>
                <motion.div
                    v-if="hasActiveFilters && meta.total > 0"
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :exit="{ opacity: 0, x: 20 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="mb-4 flex items-center justify-between rounded-lg border border-cyan-500/20 bg-cyan-500/5 px-4 py-3"
                >
                    <span class="text-sm text-cyan-400">
                        Ditemukan {{ meta.total }} perjalanan
                    </span>
                </motion.div>
            </AnimatePresence>

            <!-- Trips Content -->
            <div class="space-y-6">
                <!-- Empty State -->
                <motion.div
                    v-if="showEmptyState"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.4 }"
                    class="rounded-lg border border-[#3E3E3A] bg-[#161615] px-6 py-12 text-center"
                >
                    <div class="mb-4 text-5xl">🚗</div>
                    <p class="text-lg font-medium text-[#EDEDEC]">
                        Tidak Ada Perjalanan
                    </p>
                    <p class="mt-2 text-sm text-[#A1A09A]">
                        {{ hasActiveFilters ? 'Tidak ada perjalanan yang sesuai dengan filter' : 'Belum ada perjalanan tercatat' }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        @click="handleResetFilters"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-500"
                    >
                        Reset Filter
                    </button>
                </motion.div>

                <!-- Desktop Table View (≥768px) -->
                <div
                    v-if="!showEmptyState"
                    class="hidden overflow-hidden rounded-lg border border-[#3E3E3A] bg-[#161615] md:block"
                >
                    <table
                        class="w-full"
                        role="table"
                        aria-label="All employee trips"
                    >
                        <thead>
                            <tr class="border-b border-[#3E3E3A] bg-[#0a0a0a]">
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Karyawan
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Tanggal/Waktu
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Durasi
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Jarak
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Kec. Maks
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Pelanggaran
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#A1A09A]"
                                    scope="col"
                                >
                                    Status
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
                                @click="navigateToTrip(trip.id)"
                                class="cursor-pointer transition-colors hover:bg-[#1a1a19]"
                            >
                                <!-- Employee -->
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-[#EDEDEC]">
                                            {{ getEmployeeName(trip.user_id) }}
                                        </p>
                                        <p class="mt-1 text-xs text-[#A1A09A]">
                                            {{ getEmployeeEmail(trip.user_id) }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Date/Time -->
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm text-[#EDEDEC]">
                                            {{ formatShortDate(trip.started_at) }}
                                        </p>
                                        <p class="mt-1 text-xs text-[#A1A09A]">
                                            {{ formatTime(trip.started_at) }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Duration -->
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-sm text-[#EDEDEC]"
                                        style="font-family: 'Share Tech Mono', monospace"
                                    >
                                        {{ formatDuration(trip.duration_seconds) }}
                                    </span>
                                </td>

                                <!-- Distance -->
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-sm text-cyan-400"
                                        style="font-family: 'Share Tech Mono', monospace"
                                    >
                                        {{ formatDistance(trip.total_distance) }}
                                    </span>
                                </td>

                                <!-- Max Speed -->
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-sm text-red-400"
                                        style="font-family: 'Share Tech Mono', monospace"
                                    >
                                        {{ formatSpeed(trip.max_speed) }}
                                    </span>
                                </td>

                                <!-- Violations -->
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full border px-2 py-0.5 font-mono text-xs font-semibold',
                                            getViolationColor(trip.violation_count),
                                        ]"
                                        style="font-family: 'Share Tech Mono', monospace"
                                    >
                                        {{ trip.violation_count }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium',
                                            getStatusColor(trip.status),
                                        ]"
                                    >
                                        {{ getStatusText(trip.status) }}
                                    </span>
                                </td>
                            </motion.tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (<768px) -->
                <div
                    v-if="!showEmptyState"
                    class="grid gap-4 md:hidden"
                >
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
                        @click="navigateToTrip(trip.id)"
                        class="cursor-pointer rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4 transition-all hover:border-cyan-500/50 hover:bg-[#2a2d33]"
                    >
                        <!-- Employee Info -->
                        <div class="mb-3 flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#e5e7eb]">
                                    {{ getEmployeeName(trip.user_id) }}
                                </h3>
                                <p class="mt-1 text-xs text-[#9ca3af]">
                                    {{ getEmployeeEmail(trip.user_id) }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full border px-2 py-1 text-xs font-medium',
                                    getStatusColor(trip.status),
                                ]"
                            >
                                {{ getStatusText(trip.status) }}
                            </span>
                        </div>

                        <!-- Date/Time -->
                        <div class="mb-3 text-sm text-[#9ca3af]">
                            {{ formatDate(trip.started_at) }}, {{ formatTime(trip.started_at) }}
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-[#0a0c0f] p-2">
                                <div class="mb-1 text-xs text-[#9ca3af]">Durasi</div>
                                <div
                                    class="font-mono text-sm font-semibold text-[#e5e7eb]"
                                    style="font-family: 'Share Tech Mono', monospace"
                                >
                                    {{ formatDuration(trip.duration_seconds) }}
                                </div>
                            </div>

                            <div class="rounded-lg bg-[#0a0c0f] p-2">
                                <div class="mb-1 text-xs text-[#9ca3af]">Jarak</div>
                                <div
                                    class="font-mono text-sm font-semibold text-cyan-400"
                                    style="font-family: 'Share Tech Mono', monospace"
                                >
                                    {{ formatDistance(trip.total_distance) }}
                                </div>
                            </div>

                            <div class="rounded-lg bg-[#0a0c0f] p-2">
                                <div class="mb-1 text-xs text-[#9ca3af]">Kec. Maks</div>
                                <div
                                    class="font-mono text-sm font-semibold text-red-400"
                                    style="font-family: 'Share Tech Mono', monospace"
                                >
                                    {{ formatSpeed(trip.max_speed) }}
                                </div>
                            </div>

                            <div class="rounded-lg bg-[#0a0c0f] p-2">
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
            </div>
        </div>
    </SupervisorLayout>
</template>
