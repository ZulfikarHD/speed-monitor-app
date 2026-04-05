<script setup lang="ts">
/**
 * TripListFilters Component
 *
 * Provides filtering controls for trip list including date range and status.
 * Responsive design with compact mobile layout and expanded desktop layout.
 *
 * @example
 * ```vue
 * <TripListFilters
 *   v-model:date-from="filters.date_from"
 *   v-model:date-to="filters.date_to"
 *   v-model:status="filters.status"
 *   @apply="handleApplyFilters"
 *   @reset="handleResetFilters"
 * />
 * ```
 */

import { Filter, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import type { TripStatus } from '@/types/trip';
import { getTodayDate } from '@/utils/date';

/**
 * TripListFilters component props.
 */
interface TripListFiltersProps {
    /** Filter: date from (YYYY-MM-DD format) */
    dateFrom?: string;

    /** Filter: date to (YYYY-MM-DD format) */
    dateTo?: string;

    /** Filter: trip status */
    status?: TripStatus | '';
}

/**
 * TripListFilters component emits.
 */
interface TripListFiltersEmits {
    (event: 'update:dateFrom', value: string): void;
    (event: 'update:dateTo', value: string): void;
    (event: 'update:status', value: TripStatus | ''): void;
    (event: 'apply'): void;
    (event: 'reset'): void;
}

const props = withDefaults(defineProps<TripListFiltersProps>(), {
    dateFrom: '',
    dateTo: '',
    status: '',
});

const emit = defineEmits<TripListFiltersEmits>();

/**
 * Local state for date from input.
 */
const localDateFrom = ref(props.dateFrom);

/**
 * Local state for date to input.
 */
const localDateTo = ref(props.dateTo);

/**
 * Local state for status select.
 */
const localStatus = ref<TripStatus | ''>(props.status);

/**
 * Watch for external prop changes.
 */
watch(
    () => props.dateFrom,
    (newValue) => {
        localDateFrom.value = newValue;
    },
);

watch(
    () => props.dateTo,
    (newValue) => {
        localDateTo.value = newValue;
    },
);

watch(
    () => props.status,
    (newValue) => {
        localStatus.value = newValue;
    },
);

/**
 * Get today's date in YYYY-MM-DD format for input max attribute.
 */
const todayDate = getTodayDate();

/**
 * Handle apply filters button click.
 */
function handleApply(): void {
    emit('update:dateFrom', localDateFrom.value);
    emit('update:dateTo', localDateTo.value);
    emit('update:status', localStatus.value);
    emit('apply');
}

/**
 * Handle reset filters button click.
 */
function handleReset(): void {
    localDateFrom.value = '';
    localDateTo.value = '';
    localStatus.value = '';
    emit('update:dateFrom', '');
    emit('update:dateTo', '');
    emit('update:status', '');
    emit('reset');
}

/**
 * Check if any filter is active.
 */
const hasActiveFilters = computed(() => {
    return !!(
        localDateFrom.value ||
        localDateTo.value ||
        localStatus.value
    );
});
</script>

<template>
    <!-- ======================================================================
        Filters — heading, grid inputs, actions
    ======================================================================= -->
    <div
        class="rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5"
        role="search"
        aria-label="Filter trips"
    >
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Filter</h3>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label
                    for="filter-date-from"
                    class="mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-400"
                >
                    Dari Tanggal
                </label>
                <input
                    id="filter-date-from"
                    v-model="localDateFrom"
                    type="date"
                    :max="localDateTo || todayDate"
                    class="w-full rounded-lg border border-zinc-300 bg-zinc-100/90 px-3 py-2 text-sm text-zinc-900 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:focus:border-cyan-400 dark:focus:ring-cyan-400/50"
                    aria-label="Filter from date"
                />
            </div>

            <div>
                <label
                    for="filter-date-to"
                    class="mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-400"
                >
                    Sampai Tanggal
                </label>
                <input
                    id="filter-date-to"
                    v-model="localDateTo"
                    type="date"
                    :min="localDateFrom"
                    :max="todayDate"
                    class="w-full rounded-lg border border-zinc-300 bg-zinc-100/90 px-3 py-2 text-sm text-zinc-900 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:focus:border-cyan-400 dark:focus:ring-cyan-400/50"
                    aria-label="Filter to date"
                />
            </div>

            <div>
                <label
                    for="filter-status"
                    class="mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-400"
                >
                    Status
                </label>
                <select
                    id="filter-status"
                    v-model="localStatus"
                    class="w-full rounded-lg border border-zinc-300 bg-zinc-100/90 px-3 py-2 text-sm text-zinc-900 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:focus:border-cyan-400 dark:focus:ring-cyan-400/50"
                    aria-label="Filter by status"
                >
                    <option value="">Semua Status</option>
                    <option value="in_progress">Sedang Berjalan</option>
                    <option value="completed">Selesai</option>
                    <option value="auto_stopped">Berhenti Otomatis</option>
                </select>
            </div>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button
                    type="button"
                    class="flex min-h-11 min-w-11 flex-1 items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-4 py-2.5 text-sm font-medium text-white shadow-lg shadow-zinc-900/10 transition-all duration-200 hover:shadow-lg hover:shadow-zinc-900/15 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/20 dark:hover:shadow-cyan-500/30 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
                    aria-label="Apply filters"
                    @click="handleApply"
                >
                    <Filter class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
                    <span>Terapkan</span>
                </button>

                <button
                    v-if="hasActiveFilters"
                    type="button"
                    class="flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-100/90 text-zinc-900 shadow-sm ring-1 ring-white/10 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-200/90 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:ring-white/5 dark:hover:bg-zinc-800 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
                    title="Reset filters"
                    aria-label="Reset filters"
                    @click="handleReset"
                >
                    <X class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
                </button>
            </div>
        </div>
    </div>
</template>
