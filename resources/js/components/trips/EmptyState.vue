<script setup lang="ts">
/**
 * EmptyState Component
 *
 * Displays a friendly empty state message with optional Lucide icon and call-to-action.
 * Reusable across different pages where data might be empty.
 *
 * The `icon` prop accepts named keys only; unknown values render no icon (no emoji text).
 *
 * @example
 * ```vue
 * <EmptyState
 *   icon="clipboard"
 *   title="Belum Ada Perjalanan"
 *   message="Anda belum memiliki riwayat perjalanan."
 *   cta-text="Mulai Perjalanan"
 *   :cta-href="speedometerUrl"
 * />
 * ```
 */

import { Link } from '@inertiajs/vue3';
import { ArrowRight, BarChart3, ClipboardList, Gauge, Motorbike, Search } from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';

/**
 * EmptyState component props.
 */
interface EmptyStateProps {
    /**
     * Icon key matching the internal map (e.g. `clipboard`, `search`).
     * WHY: Legacy emoji strings no longer render as text; use named keys for visuals.
     */
    icon?: string;

    /** Title text for empty state */
    title: string;

    /** Descriptive message explaining the empty state */
    message: string;

    /** Optional CTA button text */
    ctaText?: string;

    /** Optional CTA button link (required if ctaText is provided) */
    ctaHref?: string;

    /**
     * When true, shows a secondary control to clear filters (My Trips).
     */
    hasFilters?: boolean;
}

const props = withDefaults(defineProps<EmptyStateProps>(), {
    hasFilters: false,
});

const emit = defineEmits<{
    'reset-filters': [];
}>();

// ========================================================================
// Icon resolution
// ========================================================================

/**
 * Map of display keys to Lucide components for empty-state illustration.
 */
function resolveIconComponent(key: string | undefined): Component | null {
    if (!key) {
        return null;
    }

    const iconMap: Record<string, Component> = {
        clipboard: ClipboardList,
        search: Search,
        'bar-chart': BarChart3,
        route: Motorbike,
        gauge: Gauge,
    };

    return iconMap[key] ?? null;
}

const iconComponent = computed(() => resolveIconComponent(props.icon));
</script>

<template>
    <!-- ======================================================================
        Empty State Container (fake glass)
    ======================================================================= -->
    <div
        class="flex flex-col items-center justify-center rounded-lg border border-zinc-200/80 bg-white/95 p-12 text-center ring-1 ring-white/20 shadow-lg shadow-zinc-900/5 dark:border-white/10 dark:bg-zinc-800/95 dark:ring-white/5 dark:shadow-cyan-500/5"
    >
        <!-- Icon (64px circle) -->
        <div
            v-if="iconComponent"
            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
            aria-hidden="true"
        >
            <component :is="iconComponent" :size="32" :stroke-width="2" class="shrink-0" />
        </div>

        <h3 class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white">
            {{ props.title }}
        </h3>

        <p class="mb-6 max-w-md text-sm text-zinc-600 dark:text-zinc-400">
            {{ props.message }}
        </p>

        <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            <Link
                v-if="props.ctaText && props.ctaHref"
                :href="props.ctaHref"
                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-6 py-3 font-medium text-white transition-all duration-200 hover:shadow-lg hover:shadow-zinc-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:from-cyan-500 dark:to-blue-600 dark:hover:shadow-cyan-500/25 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
            >
                {{ props.ctaText }}
                <ArrowRight class="h-5 w-5 shrink-0" :stroke-width="2" aria-hidden="true" />
            </Link>

            <button
                v-if="props.hasFilters"
                type="button"
                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg border border-zinc-200/80 bg-zinc-50 px-6 py-3 text-sm font-medium text-zinc-800 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:border-white/10 dark:bg-zinc-900/80 dark:text-zinc-100 dark:hover:bg-zinc-800 dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-950"
                @click="emit('reset-filters')"
            >
                Reset filter
            </button>
        </div>
    </div>
</template>
