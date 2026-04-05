<script setup lang="ts">
/**
 * TrendStatCard - Displays a metric with trend indicator.
 *
 * Shows a statistic value with a visual trend indicator (arrow + percentage)
 * comparing current value against previous period. Color-coded for quick
 * identification of positive/negative trends.
 */

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
}

withDefaults(defineProps<Props>(), {
    icon: undefined,
    isLoading: false,
});

/**
 * Determine trend direction and styling.
 *
 * @param percentage - The percentage change value
 * @returns Object with color class and arrow direction
 */
function getTrendStyle(percentage: number) {
    if (percentage > 0) {
        return {
            color: 'text-emerald-400',
            arrow: '↑',
            bgColor: 'bg-emerald-500/10',
        };
    } else if (percentage < 0) {
        return {
            color: 'text-red-400',
            arrow: '↓',
            bgColor: 'bg-red-500/10',
        };
    } else {
        return {
            color: 'text-gray-400',
            arrow: '→',
            bgColor: 'bg-gray-500/10',
        };
    }
}
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl border border-[#3E3E3A] bg-gradient-to-br from-[#1C1C1A] to-[#2A2A28] p-6 transition-all duration-200 hover:border-[#4E4E4A]"
    >
        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="animate-pulse space-y-3">
            <div class="h-4 w-24 rounded bg-[#3E3E3A]"></div>
            <div class="h-8 w-20 rounded bg-[#3E3E3A]"></div>
            <div class="h-4 w-16 rounded bg-[#3E3E3A]"></div>
        </div>

        <!-- Content -->
        <div v-else class="relative z-10">
            <!-- Title -->
            <h3 class="text-sm font-medium text-[#A1A09A]">
                {{ title }}
            </h3>

            <!-- Value -->
            <div class="mt-2 text-3xl font-bold text-[#EDEDEC]">
                {{ value }}
            </div>

            <!-- Trend Indicator -->
            <div class="mt-3 flex items-center gap-1.5">
                <span
                    :class="[getTrendStyle(trendPercentage).bgColor, getTrendStyle(trendPercentage).color]"
                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                >
                    <span class="text-sm">{{ getTrendStyle(trendPercentage).arrow }}</span>
                    <span>{{ Math.abs(trendPercentage) }}%</span>
                </span>
                <span class="text-xs text-[#A1A09A]">vs yesterday</span>
            </div>
        </div>

        <!-- Decorative gradient -->
        <div
            class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-gradient-to-br from-[#3E3E3A]/20 to-transparent blur-2xl"
        ></div>
    </div>
</template>
