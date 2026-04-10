<script setup lang="ts">
/**
 * TripListFilters Component
 *
 * Collapsible filtering controls for trip list including date range and status.
 * CTA toggle button with amber/orange accent for contrast against dark theme.
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

import { ChevronDown, ChevronUp, Filter, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

import type { TripStatus } from '@/types/trip';
import { getTodayDate } from '@/utils/date';

interface TripListFiltersProps {
    dateFrom?: string;
    dateTo?: string;
    status?: TripStatus | '';
    vehicleType?: string;
}

interface TripListFiltersEmits {
    (event: 'update:dateFrom', value: string): void;
    (event: 'update:dateTo', value: string): void;
    (event: 'update:status', value: TripStatus | ''): void;
    (event: 'update:vehicleType', value: string): void;
    (event: 'apply'): void;
    (event: 'reset'): void;
}

const props = withDefaults(defineProps<TripListFiltersProps>(), {
    dateFrom: '',
    dateTo: '',
    status: '',
    vehicleType: '',
});

const emit = defineEmits<TripListFiltersEmits>();

const isOpen = ref(false);
const localDateFrom = ref(props.dateFrom);
const localDateTo = ref(props.dateTo);
const localStatus = ref<TripStatus | ''>(props.status);
const localVehicleType = ref(props.vehicleType);

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

watch(
    () => props.vehicleType,
    (newValue) => {
        localVehicleType.value = newValue;
    },
);

const todayDate = getTodayDate();

function handleApply(): void {
    emit('update:dateFrom', localDateFrom.value);
    emit('update:dateTo', localDateTo.value);
    emit('update:status', localStatus.value);
    emit('update:vehicleType', localVehicleType.value);
    emit('apply');
}

function handleReset(): void {
    localDateFrom.value = '';
    localDateTo.value = '';
    localStatus.value = '';
    localVehicleType.value = '';
    emit('update:dateFrom', '');
    emit('update:dateTo', '');
    emit('update:status', '');
    emit('update:vehicleType', '');
    emit('reset');
}

const hasActiveFilters = computed(() => {
    return !!(localDateFrom.value || localDateTo.value || localStatus.value || localVehicleType.value);
});

const activeFilterCount = computed(() => {
    let count = 0;

    if (localDateFrom.value) {
count++;
}

    if (localDateTo.value) {
count++;
}

    if (localStatus.value) {
count++;
}

    if (localVehicleType.value) {
count++;
}

    return count;
});
</script>

<template>
    <div role="search" aria-label="Filter perjalanan">
        <!-- CTA Toggle Button -->
        <button
            type="button"
            class="flex w-full items-center justify-between gap-3 rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-200 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 shadow-amber-500/20 hover:shadow-amber-500/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-950"
            :aria-expanded="isOpen"
            aria-controls="trip-filter-panel"
            @click="isOpen = !isOpen"
        >
            <span class="flex items-center gap-2">
                <Filter :size="16" :stroke-width="2" aria-hidden="true" />
                <span>Filter Perjalanan</span>
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
            id="trip-filter-panel"
            class="grid transition-all duration-300 ease-in-out"
            :class="isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
        >
            <div class="overflow-hidden">
                <div class="mt-3 rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
                                aria-label="Filter dari tanggal"
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
                                aria-label="Filter sampai tanggal"
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
                                aria-label="Filter berdasarkan status"
                            >
                                <option value="">Semua Status</option>
                                <option value="in_progress">Sedang Berjalan</option>
                                <option value="completed">Selesai</option>
                                <option value="auto_stopped">Berhenti Otomatis</option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="filter-vehicle-type"
                                class="mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-400"
                            >
                                Kendaraan
                            </label>
                            <select
                                id="filter-vehicle-type"
                                v-model="localVehicleType"
                                class="w-full rounded-lg border border-zinc-300 bg-zinc-100/90 px-3 py-2 text-sm text-zinc-900 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:focus:border-cyan-400 dark:focus:ring-cyan-400/50"
                                aria-label="Filter berdasarkan kendaraan"
                            >
                                <option value="">Semua Kendaraan</option>
                                <option value="mobil">Mobil</option>
                                <option value="motor">Motor</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                            <button
                                type="button"
                                class="flex min-h-11 min-w-11 flex-1 items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-4 py-2.5 text-sm font-medium text-white shadow-lg shadow-zinc-900/10 transition-all duration-200 hover:shadow-lg hover:shadow-zinc-900/15 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/20 dark:hover:shadow-cyan-500/30 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
                                aria-label="Terapkan filter"
                                @click="handleApply"
                            >
                                <Filter class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
                                <span>Terapkan</span>
                            </button>

                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                class="flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-100/90 text-zinc-900 shadow-sm ring-1 ring-white/10 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-200/90 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:ring-white/5 dark:hover:bg-zinc-800 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
                                title="Reset filter"
                                aria-label="Reset filter"
                                @click="handleReset"
                            >
                                <X class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
