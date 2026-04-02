<script setup lang="ts">
/**
 * Employee Layout Component
 *
 * Reusable layout wrapper for all employee pages with responsive navigation.
 * Provides consistent navigation structure across mobile and desktop.
 *
 * Navigation Structure:
 * - Mobile (≤768px): Bottom navigation bar with 4 items
 * - Desktop (>768px): Top navigation bar with horizontal menu
 * - Profile dropdown accessible on both layouts
 *
 * Features:
 * - Responsive navigation (bottom mobile, top desktop)
 * - Content slot for page content
 * - Proper spacing for fixed navigation
 * - VeloTrack dark theme styling
 * - Accessible navigation landmarks
 */

import { Head } from '@inertiajs/vue3';

import BottomNav from '@/components/navigation/BottomNav.vue';
import TopNav from '@/components/navigation/TopNav.vue';

// ========================================================================
// Component Props
// ========================================================================

interface Props {
    /** Page title for document head */
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'VeloTrack',
});
</script>

<template>
    <!-- Document Head -->
    <Head :title="props.title" />

    <!-- ======================================================================
        Employee Layout
        Responsive layout with navigation and content area
    ======================================================================= -->
    <div class="min-h-screen bg-[#0a0c0f]">
        <!-- Top Navigation (Desktop/Tablet) -->
        <TopNav />

        <!-- Main Content Area -->
        <main
            class="pb-20 md:pb-0"
            role="main"
        >
            <!-- Page Content Slot -->
            <slot />
        </main>

        <!-- Bottom Navigation (Mobile) -->
        <BottomNav />
    </div>
</template>

<style>
/**
 * Global styles for employee layout.
 *
 * Ensures proper spacing and prevents content from being
 * hidden behind fixed navigation elements.
 */

/* Mobile: Add bottom padding for fixed bottom nav */
@media (max-width: 767px) {
    main {
        /* 80px = bottom nav height + safe area buffer */
        padding-bottom: calc(80px + env(safe-area-inset-bottom));
    }
}
</style>
