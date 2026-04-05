<script setup lang="ts">
/**
 * StatCard Component
 *
 * Reusable statistics display card showing a metric value with icon and label.
 * Designed for dashboard statistics grids with color-coded variants.
 *
 * Features:
 * - Large number display
 * - Lucide icon badge with color variants
 * - Title and unit labels
 * - Gradient overlay on fake-glass surface
 * - Theme-aware design with light/dark mode support
 */

import {
    BarChart3,
    Car,
    ClipboardList,
    Gauge,
    Route,
    ShieldAlert,
    Zap,
} from '@lucide/vue';
import type { Component } from 'vue';

import type { StatCardProps } from '@/types/statistics';

// ========================================================================
// Component Props
// ========================================================================

const props = defineProps<StatCardProps>();

// ========================================================================
// Icon Mapping
// ========================================================================

/**
 * Resolves the Lucide icon component for the given `icon` string key.
 */
function getIconComponent(icon: string): Component | null {
    const iconMap: Record<string, Component> = {
        car: Car,
        gauge: Gauge,
        'bar-chart': BarChart3,
        'shield-alert': ShieldAlert,
        route: Route,
        zap: Zap,
        clipboard: ClipboardList,
        chart: BarChart3,
    };

    return iconMap[icon] ?? null;
}

// ========================================================================
// Computed Colors
// ========================================================================

/**
 * Get theme-aware color classes based on card variant.
 */
function getColorClasses(color: StatCardProps['color']): {
    gradient: string;
    accent: string;
    icon: string;
    text: string;
} {
    const colorMap = {
        blue: {
            gradient:
                'from-blue-500/10 to-indigo-500/10 dark:from-blue-500/10 dark:to-indigo-500/10',
            accent: 'border-l-blue-500 dark:border-l-blue-400',
            icon: 'from-blue-600 to-indigo-700 dark:from-blue-500 dark:to-indigo-600',
            text: 'text-blue-600 dark:text-blue-400',
        },
        green: {
            gradient:
                'from-green-500/10 to-emerald-500/10 dark:from-green-500/10 dark:to-emerald-500/10',
            accent: 'border-l-green-500 dark:border-l-green-400',
            icon: 'from-green-600 to-emerald-700 dark:from-green-500 dark:to-emerald-600',
            text: 'text-green-600 dark:text-green-400',
        },
        purple: {
            gradient:
                'from-purple-500/10 to-pink-500/10 dark:from-purple-500/10 dark:to-pink-500/10',
            accent: 'border-l-purple-500 dark:border-l-purple-400',
            icon: 'from-purple-600 to-pink-700 dark:from-purple-500 dark:to-pink-600',
            text: 'text-purple-600 dark:text-purple-400',
        },
        red: {
            gradient: 'from-red-500/10 to-rose-500/10 dark:from-red-500/10 dark:to-rose-500/10',
            accent: 'border-l-red-500 dark:border-l-red-400',
            icon: 'from-red-600 to-rose-700 dark:from-red-500 dark:to-rose-600',
            text: 'text-red-600 dark:text-red-400',
        },
        orange: {
            gradient:
                'from-orange-500/10 to-amber-500/10 dark:from-orange-500/10 dark:to-amber-500/10',
            accent: 'border-l-orange-500 dark:border-l-orange-400',
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
        Stat Card (fake glass + gradient overlay, CSS hover lift)
    ======================================================================= -->
    <div
        class="relative overflow-hidden rounded-lg border border-zinc-200/80 bg-white/95 p-6 ring-1 ring-white/20 transition-all duration-200 hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-zinc-800/95 dark:ring-white/5 dark:shadow-lg dark:shadow-cyan-500/5 dark:hover:shadow-cyan-500/10"
        :class="[colors.accent, 'border-l-4 shadow-lg shadow-zinc-900/5']"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-gradient-to-br"
            :class="colors.gradient"
            aria-hidden="true"
        />

        <div class="relative flex items-start justify-between">
            <!-- Left: Value and Label -->
            <div class="flex-1">
                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    {{ title }}
                </p>

                <p
                    class="mt-2 text-4xl font-bold"
                    :class="colors.text"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    {{ value.toLocaleString('id-ID') }}
                </p>

                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                    {{ unit }}
                </p>
            </div>

            <!-- Right: Icon Badge (44×44 touch-friendly) -->
            <div
                v-if="iconComponent"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br shadow-lg shadow-zinc-200 dark:shadow-none"
                :class="colors.icon"
                aria-hidden="true"
            >
                <component
                    :is="iconComponent"
                    :size="28"
                    :stroke-width="2"
                    class="text-white"
                />
            </div>
        </div>
    </div>
</template>
