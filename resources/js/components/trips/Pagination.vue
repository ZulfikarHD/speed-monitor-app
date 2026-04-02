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
        Pagination Container
        Mobile-first responsive pagination controls
    ======================================================================= -->
    <div
        class="flex flex-col items-center justify-between gap-4 sm:flex-row"
        role="navigation"
        aria-label="Pagination"
    >
        <!-- Item Range Info (Left) -->
        <div class="text-sm text-[#9ca3af]">
            Menampilkan {{ itemRange }}
        </div>

        <!-- Pagination Controls (Center/Right) -->
        <div class="flex items-center gap-2">
            <!-- Previous Button -->
            <button
                @click="goToPrevious"
                :disabled="!canGoPrevious"
                class="flex h-10 items-center gap-1 rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-3 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d33] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-[#1a1d23]"
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
            </button>

            <!-- Page Numbers (Desktop Only) -->
            <div class="hidden items-center gap-1 md:flex">
                <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="typeof page === 'number' ? goToPage(page) : null"
                    :disabled="typeof page !== 'number'"
                    :class="[
                        'flex h-10 min-w-[40px] items-center justify-center rounded-lg px-3 text-sm font-medium transition-colors',
                        page === currentPage
                            ? 'bg-cyan-600 text-white'
                            : typeof page === 'number'
                              ? 'border border-[#3E3E3A] bg-[#1a1d23] text-[#e5e7eb] hover:bg-[#2a2d33]'
                              : 'cursor-default text-[#9ca3af]',
                    ]"
                    :aria-label="
                        typeof page === 'number' ? `Go to page ${page}` : ''
                    "
                    :aria-current="page === currentPage ? 'page' : undefined"
                >
                    {{ page }}
                </button>
            </div>

            <!-- Page Info (Mobile) -->
            <div
                class="flex h-10 items-center rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-3 text-sm font-medium text-[#e5e7eb] md:hidden"
            >
                {{ currentPage }} / {{ lastPage }}
            </div>

            <!-- Next Button -->
            <button
                @click="goToNext"
                :disabled="!canGoNext"
                class="flex h-10 items-center gap-1 rounded-lg border border-[#3E3E3A] bg-[#1a1d23] px-3 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d33] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-[#1a1d23]"
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
            </button>
        </div>
    </div>
</template>
