<script setup lang="ts">
/**
 * TrendStatCard - Displays a metric with trend indicator.
 *
 * Shows a statistic value with a visual trend indicator (SVG icon + percentage)
 * comparing current value against previous period. Color-coded for quick
 * identification of positive/negative trends. Uses design system colors.
 */

import IconTrendDown from '@/components/icons/IconTrendDown.vue';
import IconTrendUp from '@/components/icons/IconTrendUp.vue';

type CardColor = 'blue' | 'green' | 'red' | 'cyan' | 'orange' | 'purple' | 'default';

interface Props {
    /** Card title/label */
    title: string;

    /** Main statistic value to display */
    value: number | string;

    /** Percentage change from previous period (positive = increase, negative = decrease) */
    trendPercentage: number;

    /** Icon component name (optional) */
    icon?: string;

    /** Loading state for skeleton UI */
    isLoading?: boolean;

    /** Accent color for the card */
    color?: CardColor;
}

const props = withDefaults(defineProps<Props>(), {
    icon: undefined,
    isLoading: false,
    color: 'default',
});

const colorClasses: Record<CardColor, { border: string; gradient: string }> = {
    blue: {
        border: 'border-l-4 border-l-blue-500',
        gradient: 'from-blue-500/15 dark:from-blue-500/20',
    },
    green: {
        border: 'border-l-4 border-l-emerald-500',
        gradient: 'from-emerald-500/15 dark:from-emerald-500/20',
    },
    red: {
        border: 'border-l-4 border-l-red-500',
        gradient: 'from-red-500/15 dark:from-red-500/20',
    },
    cyan: {
        border: 'border-l-4 border-l-cyan-500',
        gradient: 'from-cyan-500/15 dark:from-cyan-500/20',
    },
    orange: {
        border: 'border-l-4 border-l-orange-500',
        gradient: 'from-orange-500/15 dark:from-orange-500/20',
    },
    purple: {
        border: 'border-l-4 border-l-purple-500',
        gradient: 'from-purple-500/15 dark:from-purple-500/20',
    },
    default: {
        border: '',
        gradient: 'from-cyan-500/10 dark:from-cyan-500/20',
    },
};

/**
 * Determine trend direction and styling.
 *
 * @param percentage - The percentage change value
 * @returns Object with color class and trend type
 */
function getTrendStyle(percentage: number) {
    if (percentage > 0) {
        return {
            color: 'text-emerald-600 dark:text-emerald-400',
            bgColor: 'bg-emerald-500/20 dark:bg-emerald-500/15',
            borderColor: 'border-emerald-500/30',
            isUp: true,
            isFlat: false,
        };
    } else if (percentage < 0) {
        return {
            color: 'text-red-600 dark:text-red-400',
            bgColor: 'bg-red-500/20 dark:bg-red-500/15',
            borderColor: 'border-red-500/30',
            isUp: false,
            isFlat: false,
        };
    } else {
        return {
            color: 'text-zinc-600 dark:text-zinc-400',
            bgColor: 'bg-zinc-500/20 dark:bg-zinc-500/15',
            borderColor: 'border-zinc-500/30',
            isUp: false,
            isFlat: true,
        };
    }
}
</script>

<template>
    <div
        :class="[
            'relative overflow-hidden rounded-xl border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 p-6 shadow-lg shadow-zinc-200 dark:shadow-cyan-500/5 transition-all duration-200 hover:border-cyan-500/50 dark:hover:border-cyan-500/50',
            colorClasses[props.color].border,
        ]"
    >
        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="animate-pulse space-y-3">
            <div class="h-4 w-24 rounded bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="h-8 w-20 rounded bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="h-4 w-16 rounded bg-zinc-200 dark:bg-zinc-700"></div>
        </div>

        <!-- Content -->
        <div v-else class="relative z-10">
            <!-- Title -->
            <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                {{ title }}
            </h3>

            <!-- Value -->
            <div class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                {{ value }}
            </div>

            <!-- Trend Indicator -->
            <div class="mt-3 flex items-center gap-1.5">
                <span
                    :class="[getTrendStyle(trendPercentage).bgColor, getTrendStyle(trendPercentage).color, getTrendStyle(trendPercentage).borderColor]"
                    class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium"
                >
                    <IconTrendUp v-if="getTrendStyle(trendPercentage).isUp" :size="14" />
                    <IconTrendDown v-else-if="!getTrendStyle(trendPercentage).isFlat" :size="14" />
                    <span>{{ Math.abs(trendPercentage) }}%</span>
                </span>
                <span class="text-xs text-zinc-600 dark:text-zinc-400">vs yesterday</span>
            </div>
        </div>

        <!-- Decorative gradient (theme-aware) -->
        <div
            :class="[
                'pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-gradient-to-br to-transparent blur-2xl',
                colorClasses[props.color].gradient,
            ]"
        ></div>
    </div>
</template>
