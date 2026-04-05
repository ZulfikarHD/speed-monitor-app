<script setup lang="ts">
/**
 * StatCard Component
 *
 * Reusable statistics display card showing a metric value with SVG icon and label.
 * Designed for dashboard statistics grids with theme-aware color variants.
 *
 * Features:
 * - Large number display with formatted values
 * - SVG icon components instead of emojis
 * - Title and unit labels
 * - Theme-aware gradient styling (light/dark)
 * - Subtle hover effects
 * - Responsive design
 */

import { IconCar, IconMap, IconZap, IconAlert } from '@/components/icons';
import type { StatCardProps } from '@/types/statistics';

// ========================================================================
// Component Props
// ========================================================================

const props = defineProps<StatCardProps>();

// ========================================================================
// Computed Colors (Theme-Aware)
// ========================================================================

/**
 * Get color classes based on card variant with full theme support.
 */
function getColorClasses(color: StatCardProps['color']): {
    card: string;
    icon: string;
    text: string;
} {
    const colorMap = {
        blue: {
            card: 'border-zinc-200 bg-white dark:border-white/5 dark:bg-zinc-800/50',
            icon: 'from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600',
            text: 'text-cyan-600 dark:text-cyan-400',
        },
        green: {
            card: 'border-zinc-200 bg-white dark:border-white/5 dark:bg-zinc-800/50',
            icon: 'from-green-600 to-emerald-700 dark:from-green-500 dark:to-emerald-600',
            text: 'text-green-600 dark:text-green-400',
        },
        purple: {
            card: 'border-zinc-200 bg-white dark:border-white/5 dark:bg-zinc-800/50',
            icon: 'from-purple-600 to-pink-700 dark:from-purple-500 dark:to-pink-600',
            text: 'text-purple-600 dark:text-purple-400',
        },
        red: {
            card: 'border-zinc-200 bg-white dark:border-white/5 dark:bg-zinc-800/50',
            icon: 'from-red-600 to-rose-700 dark:from-red-500 dark:to-rose-600',
            text: 'text-red-600 dark:text-red-400',
        },
        orange: {
            card: 'border-zinc-200 bg-white dark:border-white/5 dark:bg-zinc-800/50',
            icon: 'from-orange-600 to-amber-700 dark:from-orange-500 dark:to-amber-600',
            text: 'text-orange-600 dark:text-orange-400',
        },
    };

    return colorMap[color];
}

const colors = getColorClasses(props.color);

/**
 * Get icon component based on icon prop.
 */
function getIconComponent(iconName: string) {
    const iconMap: Record<string, any> = {
        car: IconCar,
        map: IconMap,
        zap: IconZap,
        alert: IconAlert,
    };

    return iconMap[iconName] || IconCar;
}

const IconComponent = getIconComponent(props.icon);
</script>

<template>
    <!-- ======================================================================
        Stat Card
        Display card for a single statistic metric
    ======================================================================= -->
    <div
        class="rounded-lg border p-5 transition-all hover:-translate-y-1 hover:shadow-md"
        :class="colors.card"
    >
        <div class="flex items-start justify-between">
            <!-- Left: Value and Label -->
            <div class="flex-1">
                <!-- Title -->
                <p class="text-sm font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
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
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                    {{ unit }}
                </p>
            </div>

            <!-- Right: Icon Badge -->
            <div
                class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white"
                :class="colors.icon"
                aria-hidden="true"
            >
                <component :is="IconComponent" :size="24" />
            </div>
        </div>
    </div>
</template>
