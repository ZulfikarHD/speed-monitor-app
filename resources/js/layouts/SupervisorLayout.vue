<script setup lang="ts">
/**
 * Supervisor Layout Component
 *
 * Reusable layout wrapper for all supervisor/admin pages with responsive navigation.
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
 * - SpeedoMontor dark theme styling
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
    title: 'SpeedMonitor - Supervisor',
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
        console.error('[SupervisorLayout] Failed to apply update:', error);
    }
};

/**
 * Handle dismiss button click.
 * User will get update on next page load.
 */
const handleDismiss = (): void => {
    // Update notification will be hidden
    // Update will be applied on next page load automatically
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
    } catch (error) {
        console.error('[SupervisorLayout] PWA installation failed:', error);
    }
};

/**
 * Handle PWA install prompt dismiss.
 *
 * Hides prompt and stores dismissal preference.
 */
const handleInstallDismiss = (): void => {
    dismiss();
};
</script>

<template>
    <!-- Document Head -->
    <Head :title="props.title" />

    <!-- ======================================================================
        Supervisor Layout
        Theme-aware professional layout with extra dark mode
    ======================================================================= -->
    <div class="relative min-h-screen bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black">
        <!-- Tech Grid Background (theme-aware) -->
        <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.08)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.02)_1px,transparent_1px)] bg-[size:64px_64px]"></div>

        <!-- Radial Gradient Overlay (theme-aware) -->
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(6,182,212,0.05),transparent_40%)] dark:bg-[radial-gradient(ellipse_at_top_right,rgba(6,182,212,0.08),transparent_50%)]"></div>

        <!-- Top Navigation (Desktop/Tablet) -->
        <TopNav />

        <!-- Main Content Area -->
        <main
            class="relative pb-20 md:pb-0"
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
 * Global styles for supervisor layout.
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
