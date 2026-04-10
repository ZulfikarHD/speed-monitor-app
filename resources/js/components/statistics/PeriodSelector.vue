<script setup lang="ts">
/**
 * PeriodSelector Component
 *
 * Segmented control for selecting time period filter (week/month/year/custom).
 * When "Custom" is selected, shows date range inputs for manual date selection.
 *
 * Features:
 * - Four period options: Week, Month, Year, Custom
 * - Date range inputs for custom period
 * - Active state highlighting
 * - v-model support for two-way binding
 * - Touch-friendly (44px minimum target size)
 */

import { Calendar } from '@lucide/vue';
import { ref, watch } from 'vue';

import type { Period } from '@/types/statistics';
import { getTodayDate } from '@/utils/date';

// ========================================================================
// Component Props & Emits
// ========================================================================

interface Props {
    modelValue: Period;
    dateFrom?: string;
    dateTo?: string;
}

const props = withDefaults(defineProps<Props>(), {
    dateFrom: '',
    dateTo: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: Period];
    'update:dateFrom': [value: string];
    'update:dateTo': [value: string];
    apply: [];
}>();

// ========================================================================
// Period Options
// ========================================================================

interface PeriodOption {
    value: Period;
    label: string;
}

const periodOptions: PeriodOption[] = [
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
    { value: 'custom', label: 'Custom' },
];

// ========================================================================
// Local State
// ========================================================================

const localDateFrom = ref(props.dateFrom);
const localDateTo = ref(props.dateTo);
const todayDate = getTodayDate();

watch(
    () => props.dateFrom,
    (v) => {
        localDateFrom.value = v;
    },
);

watch(
    () => props.dateTo,
    (v) => {
        localDateTo.value = v;
    },
);

// ========================================================================
// Methods
// ========================================================================

function selectPeriod(period: Period): void {
    if (period !== props.modelValue) {
        emit('update:modelValue', period);

        // For presets, trigger immediately
        if (period !== 'custom') {
            emit('apply');
        }
    }
}

function isActive(period: Period): boolean {
    return props.modelValue === period;
}

function handleApplyCustom(): void {
    if (localDateFrom.value && localDateTo.value) {
        emit('update:dateFrom', localDateFrom.value);
        emit('update:dateTo', localDateTo.value);
        emit('update:modelValue', 'custom');
        emit('apply');
    }
}
</script>

<template>
    <!-- ======================================================================
        Period Selector (fake glass segmented control)
    ======================================================================= -->
    <div class="flex flex-col gap-3">
        <div
            role="group"
            aria-label="Statistics time period"
            class="flex flex-wrap rounded-lg border border-zinc-200/80 bg-white/95 p-1 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5"
        >
            <button
                v-for="option in periodOptions"
                :key="option.value"
                type="button"
                class="min-h-[44px] min-w-[44px] rounded-md px-4 py-3 text-sm font-medium transition-all duration-200 sm:min-w-[100px]"
                :class="
                    isActive(option.value)
                        ? 'bg-gradient-to-r from-cyan-600 to-blue-700 text-white shadow-lg shadow-zinc-200 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25'
                        : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-200'
                "
                :aria-pressed="isActive(option.value)"
                :aria-label="`${option.label} statistics period`"
                @click="selectPeriod(option.value)"
            >
                {{ option.label }}
            </button>
        </div>

        <!-- Custom Date Range Inputs -->
        <div
            v-if="modelValue === 'custom'"
            class="flex flex-col gap-3 rounded-lg border border-zinc-200/80 bg-white/95 p-4 shadow-lg shadow-zinc-900/5 ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:shadow-cyan-500/5 dark:ring-white/5 sm:flex-row sm:items-end"
        >
            <div class="flex-1">
                <label
                    for="stat-date-from"
                    class="mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-400"
                >
                    Dari Tanggal
                </label>
                <input
                    id="stat-date-from"
                    v-model="localDateFrom"
                    type="date"
                    :max="localDateTo || todayDate"
                    class="w-full rounded-lg border border-zinc-300 bg-zinc-100/90 px-3 py-2 text-sm text-zinc-900 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:focus:border-cyan-400 dark:focus:ring-cyan-400/50"
                    aria-label="Dari tanggal"
                />
            </div>

            <div class="flex-1">
                <label
                    for="stat-date-to"
                    class="mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-400"
                >
                    Sampai Tanggal
                </label>
                <input
                    id="stat-date-to"
                    v-model="localDateTo"
                    type="date"
                    :min="localDateFrom"
                    :max="todayDate"
                    class="w-full rounded-lg border border-zinc-300 bg-zinc-100/90 px-3 py-2 text-sm text-zinc-900 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-white/10 dark:bg-zinc-900/60 dark:text-white dark:focus:border-cyan-400 dark:focus:ring-cyan-400/50"
                    aria-label="Sampai tanggal"
                />
            </div>

            <button
                type="button"
                :disabled="!localDateFrom || !localDateTo"
                class="flex min-h-11 items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-zinc-900/10 transition-all duration-200 hover:shadow-lg hover:shadow-zinc-900/15 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-50 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/20 dark:hover:shadow-cyan-500/30 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
                aria-label="Terapkan rentang tanggal"
                @click="handleApplyCustom"
            >
                <Calendar :size="16" :stroke-width="2" aria-hidden="true" />
                <span>Terapkan</span>
            </button>
        </div>
    </div>
</template>
