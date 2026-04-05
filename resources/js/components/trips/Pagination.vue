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

import { ChevronLeft, ChevronRight } from '@lucide/vue';
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
    perPage?: number;

    /** Total number of items */
    total: number;
}

/**
 * Pagination component emits.
 */
interface PaginationEmits {
    (event: 'page-change', page: number): void;
}

const props = withDefaults(defineProps<PaginationProps>(), {
    perPage: 20,
});
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
        for (let i = 1; i <= lastPage; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1);

        if (currentPage > 3) {
            pages.push('...');
        }

        const start = Math.max(2, currentPage - 1);
        const end = Math.min(lastPage - 1, currentPage + 1);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        if (currentPage < lastPage - 2) {
            pages.push('...');
        }

        pages.push(lastPage);
    }

    return pages;
});
</script>

<template>
    <!-- ======================================================================
        Pagination — range text, prev/next, page numbers / mobile summary
    ======================================================================= -->
    <div
        class="flex flex-col items-center justify-between gap-4 sm:flex-row"
        role="navigation"
        aria-label="Pagination"
    >
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            Menampilkan {{ itemRange }}
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                :disabled="!canGoPrevious"
                class="flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-lg border border-zinc-200/80 bg-white/95 px-3 text-sm font-medium text-zinc-900 shadow-sm ring-1 ring-white/20 transition-all duration-200 hover:border-cyan-500/40 hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-zinc-200/80 disabled:hover:bg-white/95 dark:border-white/10 dark:bg-zinc-800/95 dark:text-white dark:ring-white/5 dark:hover:border-cyan-500/30 dark:hover:bg-zinc-800 dark:disabled:hover:border-white/10 dark:disabled:hover:bg-zinc-800/95"
                aria-label="Previous page"
                @click="goToPrevious"
            >
                <ChevronLeft class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
                <span class="hidden sm:inline">Sebelumnya</span>
            </button>

            <div class="hidden items-center gap-1 md:flex">
                <template v-for="(page, pageIndex) in visiblePages" :key="`pagination-page-${pageIndex}-${page}`">
                    <span
                        v-if="typeof page !== 'number'"
                        class="flex min-h-11 min-w-11 items-center justify-center px-2 text-sm text-zinc-500 dark:text-zinc-400"
                        aria-hidden="true"
                    >
                        {{ page }}
                    </span>
                    <button
                        v-else
                        type="button"
                        :class="[
                            'flex min-h-11 min-w-11 items-center justify-center rounded-lg px-3 text-sm font-medium transition-all duration-200',
                            page === currentPage
                                ? 'bg-gradient-to-r from-cyan-600 to-blue-700 text-white shadow-md shadow-zinc-900/10 dark:from-cyan-500 dark:to-blue-600 dark:shadow-cyan-500/20'
                                : 'border border-zinc-200/80 bg-white/95 text-zinc-900 shadow-sm ring-1 ring-white/20 hover:border-cyan-500/40 hover:bg-zinc-50 dark:border-white/10 dark:bg-zinc-800/95 dark:text-white dark:ring-white/5 dark:hover:border-cyan-500/30 dark:hover:bg-zinc-800',
                        ]"
                        :aria-label="`Go to page ${page}`"
                        :aria-current="page === currentPage ? 'page' : undefined"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>
                </template>
            </div>

            <div
                class="flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-zinc-200/80 bg-white/95 px-3 text-sm font-medium text-zinc-900 shadow-sm ring-1 ring-white/20 dark:border-white/10 dark:bg-zinc-800/95 dark:text-white dark:ring-white/5 md:hidden"
            >
                {{ currentPage }} / {{ lastPage }}
            </div>

            <button
                type="button"
                :disabled="!canGoNext"
                class="flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-lg border border-zinc-200/80 bg-white/95 px-3 text-sm font-medium text-zinc-900 shadow-sm ring-1 ring-white/20 transition-all duration-200 hover:border-cyan-500/40 hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-zinc-200/80 disabled:hover:bg-white/95 dark:border-white/10 dark:bg-zinc-800/95 dark:text-white dark:ring-white/5 dark:hover:border-cyan-500/30 dark:hover:bg-zinc-800 dark:disabled:hover:border-white/10 dark:disabled:hover:bg-zinc-800/95"
                aria-label="Next page"
                @click="goToNext"
            >
                <span class="hidden sm:inline">Selanjutnya</span>
                <ChevronRight class="h-4 w-4 shrink-0" :stroke-width="2" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>
