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
 * - Theme-aware design with light/dark mode support
 */

import { motion } from 'motion-v';
import type { Component } from 'vue';

import {
    IconCar,
    IconChart,
    IconClipboard,
    IconGauge,
} from '@/components/icons';
import type { StatCardProps } from '@/types/statistics';

// ========================================================================
// Component Props
// ========================================================================

const props = defineProps<StatCardProps>();

// ========================================================================
// Icon Mapping
// ========================================================================

/**
 * Get icon component based on icon string.
 *
 * Maps icon strings to SVG icon components.
 */
function getIconComponent(icon: string): Component | null {
    const iconMap: Record<string, Component> = {
        '🚗': IconCar,
        '📍': IconGauge,
        '⚡': IconChart,
        '⚠️': IconClipboard,
        'car': IconCar,
        'gauge': IconGauge,
        'chart': IconChart,
        'clipboard': IconClipboard,
    };

    return iconMap[icon] || null;
}

// ========================================================================
// Computed Colors
// ========================================================================

/**
 * Get theme-aware color classes based on card variant.
 */
function getColorClasses(color: StatCardProps['color']): {
    gradient: string;
    border: string;
    icon: string;
    text: string;
} {
    const colorMap = {
        blue: {
            gradient: 'from-blue-500/10 to-indigo-500/10 dark:from-blue-500/10 dark:to-indigo-500/10',
            border: 'border-blue-200 dark:border-blue-500/30',
            icon: 'from-blue-600 to-indigo-700 dark:from-blue-500 dark:to-indigo-600',
            text: 'text-blue-600 dark:text-blue-400',
        },
        green: {
            gradient: 'from-green-500/10 to-emerald-500/10 dark:from-green-500/10 dark:to-emerald-500/10',
            border: 'border-green-200 dark:border-green-500/30',
            icon: 'from-green-600 to-emerald-700 dark:from-green-500 dark:to-emerald-600',
            text: 'text-green-600 dark:text-green-400',
        },
        purple: {
            gradient: 'from-purple-500/10 to-pink-500/10 dark:from-purple-500/10 dark:to-pink-500/10',
            border: 'border-purple-200 dark:border-purple-500/30',
            icon: 'from-purple-600 to-pink-700 dark:from-purple-500 dark:to-pink-600',
            text: 'text-purple-600 dark:text-purple-400',
        },
        red: {
            gradient: 'from-red-500/10 to-rose-500/10 dark:from-red-500/10 dark:to-rose-500/10',
            border: 'border-red-200 dark:border-red-500/30',
            icon: 'from-red-600 to-rose-700 dark:from-red-500 dark:to-rose-600',
            text: 'text-red-600 dark:text-red-400',
        },
        orange: {
            gradient: 'from-orange-500/10 to-amber-500/10 dark:from-orange-500/10 dark:to-amber-500/10',
            border: 'border-orange-200 dark:border-orange-500/30',
            icon: 'from-orange-600 to-amber-700 dark:from-orange-500 dark:to-amber-600',
            text: 'text-orange-600 dark:text-orange-400',
        },
    };

    return colorMap[color];
}

const colors = getColorClasses(props.color);
const iconComponent = getIconComponent(props.icon);
</script>

<template>
    <!-- ======================================================================
        Stat Card (Theme-Aware)
        Display card for a single statistic metric with SVG icon
    ======================================================================= -->
    <motion.div
        :whileHover="{ scale: 1.02, y: -4 }"
        :transition="{ type: 'spring', bounce: 0.4, duration: 0.3 }"
        class="rounded-lg border bg-gradient-to-br backdrop-blur-sm p-6 transition-all duration-300 hover:shadow-lg hover:shadow-zinc-200 dark:hover:shadow-none"
        :class="[colors.gradient, colors.border]"
    >
        <div class="flex items-start justify-between">
            <!-- Left: Value and Label -->
            <div class="flex-1">
                <!-- Title -->
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
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
                class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br shadow-lg shadow-zinc-200 dark:shadow-none"
                :class="colors.icon"
                aria-hidden="true"
            >
                <component
                    v-if="iconComponent"
                    :is="iconComponent"
                    :size="28"
                    class="text-white"
                />
                <span v-else class="text-2xl">{{ icon }}</span>
            </div>
        </div>
    </motion.div>
</template>
