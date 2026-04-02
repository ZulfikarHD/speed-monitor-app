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
        Filters Container
        Responsive filter controls with date range and status
    ======================================================================= -->
    <div
        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
        role="search"
        aria-label="Filter trips"
    >
        <div class="mb-4">
            <h3 class="text-sm font-medium text-[#e5e7eb]">Filter</h3>
        </div>

        <!-- Filter Controls Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

            <!-- Action Buttons -->
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button
                    @click="handleApply"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
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
                    class="flex items-center justify-center rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-4 py-2 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d33] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#0a0c0f]"
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
    </div>
</template>
