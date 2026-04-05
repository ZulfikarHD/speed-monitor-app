<script setup lang="ts">
/**
 * EmptyState Component
 *
 * Displays a friendly empty state message with optional icon and call-to-action.
 * Reusable across different pages where data might be empty.
 *
 * @example
 * ```vue
 * <!-- No trips at all -->
 * <EmptyState
 *   icon="📋"
 *   title="Belum Ada Perjalanan"
 *   message="Anda belum memiliki riwayat perjalanan. Mulai perjalanan pertama Anda dengan menggunakan speedometer."
 *   cta-text="Mulai Perjalanan"
 *   cta-href="/employee/speedometer"
 * />
 *
 * <!-- No results from filters -->
 * <EmptyState
 *   icon="🔍"
 *   title="Tidak Ada Hasil"
 *   message="Tidak ada perjalanan yang cocok dengan filter Anda."
 * />
 * ```
 */

import { Link } from '@inertiajs/vue3';

/**
 * EmptyState component props.
 */
interface EmptyStateProps {
    /** Icon to display (emoji or unicode character) */
    icon?: string;

    /** Title text for empty state */
    title: string;

    /** Descriptive message explaining the empty state */
    message: string;

    /** Optional CTA button text */
    ctaText?: string;

    /** Optional CTA button link (required if ctaText is provided) */
    ctaHref?: string;
}

const props = defineProps<EmptyStateProps>();
</script>

<template>
    <!-- ======================================================================
        Empty State Container (Theme-Aware)
        Centered empty state with icon, title, message, and optional CTA
    ======================================================================= -->
    <div
        class="flex flex-col items-center justify-center rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-sm p-12 text-center"
    >
        <!-- Icon -->
        <div
            v-if="props.icon"
            class="mb-4 text-6xl opacity-50"
            aria-hidden="true"
        >
            {{ props.icon }}
        </div>

        <!-- Title -->
        <h3 class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white">
            {{ props.title }}
        </h3>

        <!-- Message -->
        <p class="mb-6 max-w-md text-sm text-zinc-600 dark:text-zinc-400">
            {{ props.message }}
        </p>

        <!-- Optional CTA Button -->
        <Link
            v-if="props.ctaText && props.ctaHref"
            :href="props.ctaHref"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 px-6 py-3 font-medium text-white transition-all duration-200 hover:shadow-lg hover:shadow-zinc-200 dark:hover:shadow-cyan-500/25 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
        >
            {{ props.ctaText }}
            <svg
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                />
            </svg>
        </Link>
    </div>
</template>
