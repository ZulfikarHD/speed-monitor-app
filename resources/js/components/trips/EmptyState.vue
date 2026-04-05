<script setup lang="ts">
/**
 * EmptyState Component
 *
 * Displays a friendly empty state message with optional icon and call-to-action.
 * Reusable across different pages where data might be empty.
 *
 * Features:
 * - Theme-aware styling with full light/dark support
 * - Optional SVG icon or icon component
 * - Optional CTA button with proper styling
 * - Professional empty state design
 * - Responsive layout
 *
 * @example
 * ```vue
 * <!-- No trips at all -->
 * <EmptyState
 *   icon-name="clipboard"
 *   title="Belum Ada Perjalanan"
 *   message="Anda belum memiliki riwayat perjalanan. Mulai perjalanan pertama Anda dengan menggunakan speedometer."
 *   cta-text="Mulai Perjalanan"
 *   cta-href="/employee/speedometer"
 * />
 *
 * <!-- No results from filters -->
 * <EmptyState
 *   icon-name="search"
 *   title="Tidak Ada Hasil"
 *   message="Tidak ada perjalanan yang cocok dengan filter Anda."
 * />
 * ```
 */

import { Link } from '@inertiajs/vue3';

import { IconClipboard, IconAlert } from '@/components/icons';

/**
 * EmptyState component props.
 */
interface EmptyStateProps {
    /** Icon name to display */
    iconName?: 'clipboard' | 'search' | 'alert';

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
        Empty State Container
        Centered empty state with icon, title, message, and optional CTA
    ======================================================================= -->
    <div
        class="flex flex-col items-center justify-center rounded-lg border border-zinc-200 bg-white backdrop-blur-sm p-12 text-center dark:border-white/5 dark:bg-zinc-800/50"
    >
        <!-- Icon -->
        <div
            v-if="props.iconName"
            class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-900 dark:text-zinc-600"
            aria-hidden="true"
        >
            <IconClipboard
                v-if="props.iconName === 'clipboard'"
                :size="40"
            />
            <IconAlert
                v-else-if="props.iconName === 'alert'"
                :size="40"
            />
            <svg
                v-else-if="props.iconName === 'search'"
                class="h-10 w-10"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
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
            class="inline-flex min-h-[44px] items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-6 py-3 font-medium text-white shadow-lg shadow-cyan-200 transition-all hover:shadow-xl hover:shadow-cyan-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/25 dark:hover:shadow-cyan-500/40 dark:focus:ring-offset-zinc-900"
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
