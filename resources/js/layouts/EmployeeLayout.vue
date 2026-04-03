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
 * - Global update notification for PWA updates
 */

import { Head } from '@inertiajs/vue3';

import UpdateNotification from '@/components/common/UpdateNotification.vue';
import BottomNav from '@/components/navigation/BottomNav.vue';
import TopNav from '@/components/navigation/TopNav.vue';
import InstallPrompt from '@/components/pwa/InstallPrompt.vue';
import { useInstallPrompt } from '@/composables/useInstallPrompt';
import { useServiceWorker } from '@/composables/useServiceWorker';

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

// ========================================================================
// Service Worker Update Management
// ========================================================================

const { hasUpdate, applyUpdate } = useServiceWorker();

/**
 * Handle update button click.
 * Applies pending Service Worker update.
 */
const handleUpdate = async (): Promise<void> => {
    try {
        await applyUpdate();
        // Page will reload automatically after SW activation
    } catch (error) {
        console.error('[EmployeeLayout] Failed to apply update:', error);
    }
};

/**
 * Handle dismiss button click.
 * User will get update on next page load.
 */
const handleDismiss = (): void => {
    // Update notification will be hidden
    // Update will be applied on next page load automatically
    console.log('[EmployeeLayout] Update notification dismissed');
};

// ========================================================================
// PWA Install Prompt Management
// ========================================================================

const { showPrompt: showInstallPrompt, isInstalling, install, dismiss } = useInstallPrompt();

/**
 * Handle PWA install button click.
 *
 * Triggers native browser install prompt.
 */
const handleInstall = async (): Promise<void> => {
    try {
        await install();
        console.log('[EmployeeLayout] PWA installation triggered');
    } catch (error) {
        console.error('[EmployeeLayout] PWA installation failed:', error);
    }
};

/**
 * Handle PWA install prompt dismiss.
 *
 * Hides prompt and stores dismissal preference.
 */
const handleInstallDismiss = (): void => {
    dismiss();
    console.log('[EmployeeLayout] PWA install prompt dismissed');
};
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

        <!-- PWA Install Prompt (Optional Enhancement) -->
        <InstallPrompt
            v-if="showInstallPrompt"
            :is-installing="isInstalling"
            @install="handleInstall"
            @dismiss="handleInstallDismiss"
        />

        <!-- Service Worker Update Notification (Global) -->
        <UpdateNotification
            :show="hasUpdate"
            @update="handleUpdate"
            @dismiss="handleDismiss"
        />
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
