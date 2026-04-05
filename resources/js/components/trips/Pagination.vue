<script setup lang="ts">
/**
 * Pagination Component
 *
 * Provides pagination controls with previous/next buttons and page information.
 * Responsive design with mobile-first approach.
 *
 * @example
 * ```vue
 * <Pagination
 *   :current-page="meta.current_page"
 *   :last-page="meta.last_page"
 *   :per-page="meta.per_page"
 *   :total="meta.total"
 *   @page-change="handlePageChange"
 * />
 * ```
 */

import { motion } from 'motion-v';
import { computed } from 'vue';

/**
 * Pagination component props.
 */
interface PaginationProps {
    /** Current page number (1-indexed) */
    currentPage: number;

    /** Total number of pages */
    lastPage: number;

    /** Number of items per page */
    perPage: number;

    /** Total number of items */
    total: number;
}

/**
 * Pagination component emits.
 */
interface PaginationEmits {
    (event: 'page-change', page: number): void;
}

const props = defineProps<PaginationProps>();
const emit = defineEmits<PaginationEmits>();

/**
 * Check if previous page button should be disabled.
 */
const canGoPrevious = computed(() => props.currentPage > 1);

/**
 * Check if next page button should be disabled.
 */
const canGoNext = computed(() => props.currentPage < props.lastPage);

/**
 * Calculate the range of items being displayed.
 *
 * @returns String like "1-20 of 150"
 */
const itemRange = computed(() => {
    const start = (props.currentPage - 1) * props.perPage + 1;
    const end = Math.min(props.currentPage * props.perPage, props.total);

    return `${start}-${end} dari ${props.total}`;
});

/**
 * Handle previous page button click.
 */
function goToPrevious(): void {
    if (canGoPrevious.value) {
        emit('page-change', props.currentPage - 1);
    }
}

/**
 * Handle next page button click.
 */
function goToNext(): void {
    if (canGoNext.value) {
        emit('page-change', props.currentPage + 1);
    }
}

/**
 * Handle jump to specific page.
 */
function goToPage(page: number): void {
    if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
        emit('page-change', page);
    }
}

/**
 * Get array of page numbers to display.
 *
 * Shows current page with 2 pages before and after when possible.
 * Always includes first and last page.
 */
const visiblePages = computed<(number | string)[]>(() => {
    const pages: (number | string)[] = [];
    const { currentPage, lastPage } = props;

    if (lastPage <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= lastPage; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);

        // Show ellipsis if current page is far from start
        if (currentPage > 3) {
            pages.push('...');
        }

        // Show pages around current page
        const start = Math.max(2, currentPage - 1);
        const end = Math.min(lastPage - 1, currentPage + 1);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        // Show ellipsis if current page is far from end
        if (currentPage < lastPage - 2) {
            pages.push('...');
        }

        // Always show last page
        pages.push(lastPage);
    }

    return pages;
});
</script>

<template>
    <!-- ======================================================================
        Pagination Container (Theme-Aware)
        Mobile-first responsive pagination controls
    ======================================================================= -->
    <div
        class="flex flex-col items-center justify-between gap-4 sm:flex-row"
        role="navigation"
        aria-label="Pagination"
    >
        <!-- Item Range Info (Left) -->
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            Menampilkan {{ itemRange }}
        </div>

        <!-- Pagination Controls (Center/Right) -->
        <div class="flex items-center gap-2">
            <!-- Previous Button -->
            <motion.button
                @click="goToPrevious"
                :disabled="!canGoPrevious"
                :whileHover="canGoPrevious ? { scale: 1.05, x: -2 } : {}"
                :whilePress="canGoPrevious ? { scale: 0.95 } : {}"
                :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                class="flex min-h-[44px] items-center gap-1 rounded-lg border border-zinc-300 dark:border-white/10 bg-zinc-100 dark:bg-zinc-800/50 px-3 text-sm font-medium text-zinc-900 dark:text-white transition-colors hover:bg-zinc-200 dark:hover:bg-zinc-700/50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-zinc-100 dark:disabled:hover:bg-zinc-800/50"
                aria-label="Previous page"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                <span class="hidden sm:inline">Sebelumnya</span>
            </motion.button>

            <!-- Page Numbers (Desktop Only) -->
            <div class="hidden items-center gap-1 md:flex">
                <motion.button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="typeof page === 'number' ? goToPage(page) : null"
                    :disabled="typeof page !== 'number'"
                    :whileHover="typeof page === 'number' && page !== currentPage ? { scale: 1.1, y: -2 } : {}"
                    :whilePress="typeof page === 'number' && page !== currentPage ? { scale: 0.95 } : {}"
                    :animate="{ scale: page === currentPage ? 1.05 : 1 }"
                    :transition="{ type: 'spring', bounce: 0.5, duration: 0.4 }"
                    :class="[
                        'flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg px-3 text-sm font-medium transition-colors duration-200',
                        page === currentPage
                            ? 'bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-zinc-200 dark:shadow-cyan-500/25'
                            : typeof page === 'number'
                              ? 'border border-zinc-300 dark:border-white/10 bg-zinc-100 dark:bg-zinc-800/50 text-zinc-900 dark:text-white hover:bg-zinc-200 dark:hover:bg-zinc-700/50'
                              : 'cursor-default text-zinc-500 dark:text-zinc-500',
                    ]"
                    :aria-label="
                        typeof page === 'number' ? `Go to page ${page}` : ''
                    "
                    :aria-current="page === currentPage ? 'page' : undefined"
                >
                    {{ page }}
                </motion.button>
            </div>

            <!-- Page Info (Mobile) -->
            <div
                class="flex min-h-[44px] items-center rounded-lg border border-zinc-300 dark:border-white/10 bg-zinc-100 dark:bg-zinc-800/50 px-3 text-sm font-medium text-zinc-900 dark:text-white md:hidden"
            >
                {{ currentPage }} / {{ lastPage }}
            </div>

            <!-- Next Button -->
            <motion.button
                @click="goToNext"
                :disabled="!canGoNext"
                :whileHover="canGoNext ? { scale: 1.05, x: 2 } : {}"
                :whilePress="canGoNext ? { scale: 0.95 } : {}"
                :transition="{ type: 'spring', bounce: 0.5, duration: 0.3 }"
                class="flex min-h-[44px] items-center gap-1 rounded-lg border border-zinc-300 dark:border-white/10 bg-zinc-100 dark:bg-zinc-800/50 px-3 text-sm font-medium text-zinc-900 dark:text-white transition-colors hover:bg-zinc-200 dark:hover:bg-zinc-700/50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-zinc-100 dark:disabled:hover:bg-zinc-800/50"
                aria-label="Next page"
            >
                <span class="hidden sm:inline">Selanjutnya</span>
                <svg
                    class="h-4 w-4"
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
            </motion.button>
        </div>
    </div>
</template>
