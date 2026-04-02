<script setup lang="ts">
/**
 * StatCard Component
 *
 * Reusable statistics display card showing a metric value with icon and label.
 * Designed for dashboard statistics grids with color-coded variants.
 *
 * Features:
 * - Large number display
 * - Icon badge with color variants
 * - Title and unit labels
 * - Gradient background styling
 * - VeloTrack dark theme
 */

import type { StatCardProps } from '@/types/statistics';

// ========================================================================
// Component Props
// ========================================================================

const props = defineProps<StatCardProps>();

// ========================================================================
// Computed Colors
// ========================================================================

/**
 * Get color classes based on card variant.
 */
function getColorClasses(color: StatCardProps['color']): {
    gradient: string;
    border: string;
    icon: string;
    text: string;
} {
    const colorMap = {
        blue: {
            gradient: 'from-blue-500/10 to-indigo-500/10',
            border: 'border-blue-500/30',
            icon: 'from-blue-500 to-indigo-600',
            text: 'text-blue-400',
        },
        green: {
            gradient: 'from-green-500/10 to-emerald-500/10',
            border: 'border-green-500/30',
            icon: 'from-green-500 to-emerald-600',
            text: 'text-green-400',
        },
        purple: {
            gradient: 'from-purple-500/10 to-pink-500/10',
            border: 'border-purple-500/30',
            icon: 'from-purple-500 to-pink-600',
            text: 'text-purple-400',
        },
        red: {
            gradient: 'from-red-500/10 to-rose-500/10',
            border: 'border-red-500/30',
            icon: 'from-red-500 to-rose-600',
            text: 'text-red-400',
        },
        orange: {
            gradient: 'from-orange-500/10 to-amber-500/10',
            border: 'border-orange-500/30',
            icon: 'from-orange-500 to-amber-600',
            text: 'text-orange-400',
        },
    };

    return colorMap[color];
}

const colors = getColorClasses(props.color);
</script>

<template>
    <!-- ======================================================================
        Stat Card
        Display card for a single statistic metric
    ======================================================================= -->
    <div
        class="rounded-lg border bg-gradient-to-br p-6 transition-all hover:scale-[1.02]"
        :class="[colors.gradient, colors.border]"
    >
        <div class="flex items-start justify-between">
            <!-- Left: Value and Label -->
            <div class="flex-1">
                <!-- Title -->
                <p class="text-sm font-medium text-[#9ca3af]">
                    {{ title }}
                </p>

                <!-- Value -->
                <p
                    class="mt-2 text-4xl font-bold"
                    :class="colors.text"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ value.toLocaleString('id-ID') }}
                </p>

                <!-- Unit -->
                <p class="mt-1 text-xs text-[#9ca3af]">
                    {{ unit }}
                </p>
            </div>

            <!-- Right: Icon Badge -->
            <div
                class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-2xl shadow-lg"
                :class="colors.icon"
                aria-hidden="true"
            >
                {{ icon }}
            </div>
        </div>
    </div>
</template>
