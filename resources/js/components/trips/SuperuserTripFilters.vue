<script setup lang="ts">
/**
 * SuperuserTripFilters Component
 *
 * Advanced filtering controls for superuser trip list with employee selection,
 * date range, status, violations filter, and sorting options.
 *
 * Features:
 * - Employee dropdown with search
 * - Date range filtering (from/to)
 * - Status dropdown
 * - Violations-only checkbox
 * - Sort by dropdown (date, violations, distance, duration)
 * - Sort order toggle (asc/desc)
 * - Apply/Reset buttons with lucide icons
 * - Active filter count badge
 * - Full light/dark theme support
 */

import { ChevronDown, ChevronUp, Filter, SlidersHorizontal, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import type { EmployeeSummary, TripStatus } from '@/types/trip';
import { getTodayDate } from '@/utils/date';

// ========================================================================
// Component Props
// ========================================================================

interface SuperuserTripFiltersProps {
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

const props = withDefaults(defineProps<SuperuserTripFiltersProps>(), {
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

interface SuperuserTripFiltersEmits {
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

const emit = defineEmits<SuperuserTripFiltersEmits>();

// ========================================================================
// Local State
// ========================================================================

const isOpen = ref(false);
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

/** Today's date in YYYY-MM-DD format for input max attribute. */
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

/** Sort options for dropdown. */
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
    <div role="search" aria-label="Filter perjalanan karyawan">
        <!-- CTA Toggle Button -->
        <button
            type="button"
            class="flex w-full items-center justify-between gap-3 rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-200 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 shadow-amber-500/20 hover:shadow-amber-500/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-950"
            :aria-expanded="isOpen"
            aria-controls="superuser-filter-panel"
            @click="isOpen = !isOpen"
        >
            <span class="flex items-center gap-2">
                <SlidersHorizontal :size="16" :stroke-width="2" aria-hidden="true" />
                <span>Filter & Urutkan</span>
                <span
                    v-if="activeFilterCount > 0 && !isOpen"
                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-white/25 px-1.5 text-xs font-bold"
                >
                    {{ activeFilterCount }}
                </span>
            </span>
            <ChevronUp v-if="isOpen" :size="16" :stroke-width="2" aria-hidden="true" />
            <ChevronDown v-else :size="16" :stroke-width="2" aria-hidden="true" />
        </button>

        <!-- Collapsible Filter Panel -->
        <div
            id="superuser-filter-panel"
            class="grid transition-all duration-300 ease-in-out"
            :class="isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
        >
            <div class="overflow-hidden">
                <div class="mt-3 rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5">
                    <!-- Filter Controls Grid -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <!-- Employee Dropdown -->
                        <div>
                            <label
                                for="filter-employee"
                                class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400"
                            >
                                Karyawan
                            </label>
                            <select
                                id="filter-employee"
                                v-model="localEmployee"
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
                                aria-label="Filter berdasarkan karyawan"
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
                                class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400"
                            >
                                Dari Tanggal
                            </label>
                            <input
                                id="filter-date-from"
                                v-model="localDateFrom"
                                type="date"
                                :max="localDateTo || todayDate"
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
                                aria-label="Filter dari tanggal"
                            />
                        </div>

                        <!-- Date To Input -->
                        <div>
                            <label
                                for="filter-date-to"
                                class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400"
                            >
                                Sampai Tanggal
                            </label>
                            <input
                                id="filter-date-to"
                                v-model="localDateTo"
                                type="date"
                                :min="localDateFrom"
                                :max="todayDate"
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
                                aria-label="Filter sampai tanggal"
                            />
                        </div>

                        <!-- Status Select -->
                        <div>
                            <label
                                for="filter-status"
                                class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400"
                            >
                                Status
                            </label>
                            <select
                                id="filter-status"
                                v-model="localStatus"
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
                                aria-label="Filter berdasarkan status"
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
                                class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400"
                            >
                                Urutkan
                            </label>
                            <div class="flex gap-2">
                                <select
                                    id="filter-sort"
                                    v-model="localSortBy"
                                    class="flex-1 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-colors duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
                                    aria-label="Urutkan berdasarkan"
                                >
                                    <option
                                        v-for="option in sortOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>

                                <button
                                    type="button"
                                    class="flex items-center justify-center rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-3 py-2 text-zinc-700 dark:text-zinc-300 transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50"
                                    :title="localSortOrder === 'asc' ? 'Ascending' : 'Descending'"
                                    :aria-label="`Urutan: ${localSortOrder}`"
                                    @click="toggleSortOrder"
                                >
                                    <ChevronUp
                                        v-if="localSortOrder === 'asc'"
                                        :size="16"
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                    <ChevronDown
                                        v-else
                                        :size="16"
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Violations Only Checkbox -->
                    <div class="mt-4">
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input
                                v-model="localViolationsOnly"
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-cyan-500 transition-colors focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                                aria-label="Hanya tampilkan perjalanan dengan pelanggaran"
                            />
                            <span>Hanya tampilkan perjalanan dengan pelanggaran</span>
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 flex items-center gap-2">
                        <button
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 transition-all duration-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                            aria-label="Terapkan filter"
                            @click="handleApply"
                        >
                            <Filter :size="16" :stroke-width="2" aria-hidden="true" />
                            <span>Terapkan</span>
                        </button>

                        <button
                            v-if="hasActiveFilters"
                            class="flex items-center justify-center rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                            title="Reset filter"
                            aria-label="Reset filter"
                            @click="handleReset"
                        >
                            <X :size="16" :stroke-width="2" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
