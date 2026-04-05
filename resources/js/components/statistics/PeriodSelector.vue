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
 * - Touch-friendly (44px minimum height)
 * - Smooth transitions
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
        Period Selector (Theme-Aware)
        Segmented control for time period selection
    ======================================================================= -->
    <div class="flex flex-wrap rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-100 dark:bg-zinc-800/50 backdrop-blur-sm p-1">
        <button
            v-for="option in periodOptions"
            :key="option.value"
            @click="selectPeriod(option.value)"
            type="button"
            class="min-h-[44px] min-w-[80px] rounded-md px-4 py-3 text-sm font-medium transition-all duration-200 sm:min-w-[100px]"
            :class="
                isActive(option.value)
                    ? 'bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-zinc-200 dark:shadow-cyan-500/25'
                    : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-white/5'
            "
            :aria-pressed="isActive(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
