<script setup lang="ts">
/**
 * PeriodSelector Component
 *
 * Segmented control for selecting time period filter (week/month/year).
 * Mobile-optimized with touch-friendly buttons and active state indicators.
 *
 * Features:
 * - Three period options: Week, Month, Year
 * - Active state highlighting
 * - v-model support for two-way binding
 * - Touch-friendly (44px minimum target size)
 * - Smooth CSS transitions (200ms)
 */

import type { Period, PeriodSelectorProps } from '@/types/statistics';

// ========================================================================
// Component Props & Emits
// ========================================================================

const props = defineProps<PeriodSelectorProps>();

const emit = defineEmits<{
    'update:modelValue': [value: Period];
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
];

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle period selection change.
 */
function selectPeriod(period: Period): void {
    if (period !== props.modelValue) {
        emit('update:modelValue', period);
    }
}

/**
 * Check if period is currently selected.
 */
function isActive(period: Period): boolean {
    return props.modelValue === period;
}
</script>

<template>
    <!-- ======================================================================
        Period Selector (fake glass segmented control)
    ======================================================================= -->
    <div
        role="group"
        aria-label="Statistics time period"
        class="flex flex-wrap rounded-lg border border-zinc-200/80 bg-white/95 p-1 ring-1 ring-white/20 shadow-lg shadow-zinc-900/5 dark:border-white/10 dark:bg-zinc-800/95 dark:ring-white/5 dark:shadow-cyan-500/5"
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
</template>
