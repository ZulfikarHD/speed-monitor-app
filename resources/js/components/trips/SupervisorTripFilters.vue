<script setup lang="ts">
/**
 * SupervisorTripFilters Component
 *
 * Advanced filtering controls for supervisor trip list with employee selection,
 * date range, status, violations filter, and sorting options.
 *
 * Features:
 * - Employee dropdown with search
 * - Date range filtering (from/to)
 * - Status dropdown
 * - Violations-only checkbox
 * - Sort by dropdown (date, violations, distance, duration)
 * - Sort order toggle (asc/desc)
 * - Apply/Reset buttons
 * - Active filter count badge
 *
 * @example
 * ```vue
 * <SupervisorTripFilters
 *   :employees="employeesList"
 *   v-model:employee="filters.employee"
 *   v-model:date-from="filters.dateFrom"
 *   v-model:date-to="filters.dateTo"
 *   v-model:status="filters.status"
 *   v-model:violations-only="filters.violationsOnly"
 *   v-model:sort-by="sort.by"
 *   v-model:sort-order="sort.order"
 *   @apply="handleApplyFilters"
 *   @reset="handleResetFilters"
 * />
 * ```
 */

import { computed, ref, watch } from 'vue';

import type { EmployeeSummary, TripStatus } from '@/types/trip';
import { getTodayDate } from '@/utils/date';

// ========================================================================
// Component Props
// ========================================================================

interface SupervisorTripFiltersProps {
    /** List of employees for dropdown */
    employees: EmployeeSummary[];

    /** Selected employee ID */
    employee?: number | null;

    /** Filter: date from (YYYY-MM-DD format) */
    dateFrom?: string;

    /** Filter: date to (YYYY-MM-DD format) */
    dateTo?: string;

    /** Filter: trip status */
    status?: TripStatus | '';

    /** Filter: violations only */
    violationsOnly?: boolean;

    /** Sort field */
    sortBy?: string;

    /** Sort order */
    sortOrder?: 'asc' | 'desc';
}

const props = withDefaults(defineProps<SupervisorTripFiltersProps>(), {
    employee: null,
    dateFrom: '',
    dateTo: '',
    status: '',
    violationsOnly: false,
    sortBy: 'started_at',
    sortOrder: 'desc',
});

// ========================================================================
// Component Emits
// ========================================================================

interface SupervisorTripFiltersEmits {
    (event: 'update:employee', value: number | null): void;
    (event: 'update:dateFrom', value: string): void;
    (event: 'update:dateTo', value: string): void;
    (event: 'update:status', value: TripStatus | ''): void;
    (event: 'update:violationsOnly', value: boolean): void;
    (event: 'update:sortBy', value: string): void;
    (event: 'update:sortOrder', value: 'asc' | 'desc'): void;
    (event: 'apply'): void;
    (event: 'reset'): void;
}

const emit = defineEmits<SupervisorTripFiltersEmits>();

// ========================================================================
// Local State
// ========================================================================

const localEmployee = ref<number | null>(props.employee);
const localDateFrom = ref(props.dateFrom);
const localDateTo = ref(props.dateTo);
const localStatus = ref<TripStatus | ''>(props.status);
const localViolationsOnly = ref(props.violationsOnly);
const localSortBy = ref(props.sortBy);
const localSortOrder = ref<'asc' | 'desc'>(props.sortOrder);

// ========================================================================
// Watchers
// ========================================================================

watch(
    () => props.employee,
    (newValue) => {
        localEmployee.value = newValue;
    }
);

watch(
    () => props.dateFrom,
    (newValue) => {
        localDateFrom.value = newValue;
    }
);

watch(
    () => props.dateTo,
    (newValue) => {
        localDateTo.value = newValue;
    }
);

watch(
    () => props.status,
    (newValue) => {
        localStatus.value = newValue;
    }
);

watch(
    () => props.violationsOnly,
    (newValue) => {
        localViolationsOnly.value = newValue;
    }
);

watch(
    () => props.sortBy,
    (newValue) => {
        localSortBy.value = newValue;
    }
);

watch(
    () => props.sortOrder,
    (newValue) => {
        localSortOrder.value = newValue;
    }
);

// ========================================================================
// Computed
// ========================================================================

/**
 * Today's date in YYYY-MM-DD format for input max attribute.
 */
const todayDate = getTodayDate();

/**
 * Check if any filter is active.
 */
const hasActiveFilters = computed(() => {
    return !!(
        localEmployee.value ||
        localDateFrom.value ||
        localDateTo.value ||
        localStatus.value ||
        localViolationsOnly.value
    );
});

/**
 * Count of active filters (for badge).
 */
const activeFilterCount = computed(() => {
    let count = 0;

    if (localEmployee.value) {
count++;
}

    if (localDateFrom.value) {
count++;
}

    if (localDateTo.value) {
count++;
}

    if (localStatus.value) {
count++;
}

    if (localViolationsOnly.value) {
count++;
}

    return count;
});

/**
 * Sort options for dropdown.
 */
const sortOptions = [
    { value: 'started_at', label: 'Tanggal' },
    { value: 'violation_count', label: 'Pelanggaran' },
    { value: 'total_distance', label: 'Jarak' },
    { value: 'duration_seconds', label: 'Durasi' },
];

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle apply filters button click.
 */
function handleApply(): void {
    emit('update:employee', localEmployee.value);
    emit('update:dateFrom', localDateFrom.value);
    emit('update:dateTo', localDateTo.value);
    emit('update:status', localStatus.value);
    emit('update:violationsOnly', localViolationsOnly.value);
    emit('update:sortBy', localSortBy.value);
    emit('update:sortOrder', localSortOrder.value);
    emit('apply');
}

/**
 * Handle reset filters button click.
 */
function handleReset(): void {
    localEmployee.value = null;
    localDateFrom.value = '';
    localDateTo.value = '';
    localStatus.value = '';
    localViolationsOnly.value = false;
    localSortBy.value = 'started_at';
    localSortOrder.value = 'desc';

    emit('update:employee', null);
    emit('update:dateFrom', '');
    emit('update:dateTo', '');
    emit('update:status', '');
    emit('update:violationsOnly', false);
    emit('update:sortBy', 'started_at');
    emit('update:sortOrder', 'desc');
    emit('reset');
}

/**
 * Toggle sort order.
 */
function toggleSortOrder(): void {
    localSortOrder.value = localSortOrder.value === 'asc' ? 'desc' : 'asc';
}
</script>

<template>
    <!-- ======================================================================
        Filters Container
        Responsive filter controls with employee, date, status, and sort
    ======================================================================= -->
    <div
        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
        role="search"
        aria-label="Filter employee trips"
    >
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-medium text-[#e5e7eb]">Filter & Sort</h3>
            <span
                v-if="activeFilterCount > 0"
                class="inline-flex items-center rounded-full bg-cyan-500/20 px-2 py-0.5 text-xs font-medium text-cyan-400"
            >
                {{ activeFilterCount }} aktif
            </span>
        </div>

        <!-- Filter Controls Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Employee Dropdown -->
            <div>
                <label
                    for="filter-employee"
                    class="mb-2 block text-xs font-medium text-[#9ca3af]"
                >
                    Karyawan
                </label>
                <select
                    id="filter-employee"
                    v-model="localEmployee"
                    class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    aria-label="Filter by employee"
                >
                    <option :value="null">Semua Karyawan</option>
                    <option
                        v-for="emp in employees"
                        :key="emp.id"
                        :value="emp.id"
                    >
                        {{ emp.name }}
                    </option>
                </select>
            </div>

            <!-- Date From Input -->
            <div>
                <label
                    for="filter-date-from"
                    class="mb-2 block text-xs font-medium text-[#9ca3af]"
                >
                    Dari Tanggal
                </label>
                <input
                    id="filter-date-from"
                    v-model="localDateFrom"
                    type="date"
                    :max="localDateTo || todayDate"
                    class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    aria-label="Filter from date"
                />
            </div>

            <!-- Date To Input -->
            <div>
                <label
                    for="filter-date-to"
                    class="mb-2 block text-xs font-medium text-[#9ca3af]"
                >
                    Sampai Tanggal
                </label>
                <input
                    id="filter-date-to"
                    v-model="localDateTo"
                    type="date"
                    :min="localDateFrom"
                    :max="todayDate"
                    class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    aria-label="Filter to date"
                />
            </div>

            <!-- Status Select -->
            <div>
                <label
                    for="filter-status"
                    class="mb-2 block text-xs font-medium text-[#9ca3af]"
                >
                    Status
                </label>
                <select
                    id="filter-status"
                    v-model="localStatus"
                    class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    aria-label="Filter by status"
                >
                    <option value="">Semua Status</option>
                    <option value="in_progress">Sedang Berjalan</option>
                    <option value="completed">Selesai</option>
                    <option value="auto_stopped">Berhenti Otomatis</option>
                </select>
            </div>

            <!-- Sort Controls -->
            <div>
                <label
                    for="filter-sort"
                    class="mb-2 block text-xs font-medium text-[#9ca3af]"
                >
                    Urutkan
                </label>
                <div class="flex gap-2">
                    <select
                        id="filter-sort"
                        v-model="localSortBy"
                        class="flex-1 rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-sm text-[#e5e7eb] transition-colors focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        aria-label="Sort by field"
                    >
                        <option
                            v-for="option in sortOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <!-- Sort Order Toggle Button -->
                    <button
                        @click="toggleSortOrder"
                        type="button"
                        class="flex items-center justify-center rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-3 py-2 text-[#e5e7eb] transition-colors hover:bg-[#1a1d23] focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        :title="localSortOrder === 'asc' ? 'Ascending' : 'Descending'"
                        :aria-label="`Sort order: ${localSortOrder}`"
                    >
                        <!-- Ascending Icon -->
                        <svg
                            v-if="localSortOrder === 'asc'"
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
                                d="M5 15l7-7 7 7"
                            />
                        </svg>

                        <!-- Descending Icon -->
                        <svg
                            v-else
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
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Violations Only Checkbox -->
        <div class="mt-4">
            <label class="inline-flex items-center gap-2 text-sm text-[#e5e7eb]">
                <input
                    v-model="localViolationsOnly"
                    type="checkbox"
                    class="h-4 w-4 rounded border-[#3E3E3A] bg-[#0a0c0f] text-cyan-500 transition-colors focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                    aria-label="Show only trips with violations"
                />
                <span>Hanya tampilkan perjalanan dengan pelanggaran</span>
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex items-center gap-2">
            <button
                @click="handleApply"
                class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                aria-label="Apply filters"
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
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                    />
                </svg>
                <span>Terapkan</span>
            </button>

            <button
                v-if="hasActiveFilters"
                @click="handleReset"
                class="flex items-center justify-center rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-4 py-2 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d33] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                title="Reset filters"
                aria-label="Reset filters"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
