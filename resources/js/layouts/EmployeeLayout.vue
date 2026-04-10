<script setup lang="ts">
/**
 * Employee Layout Component
 *
 * Reusable layout wrapper for all employee pages with responsive navigation.
 * Provides consistent navigation structure and global overlays.
 *
 * Navigation Structure:
 * - Mobile (<=768px): Bottom navigation bar with 5 items
 * - Desktop (>768px): Top navigation bar with horizontal menu
 *
 * Features:
 * - Responsive navigation (bottom mobile, top desktop)
 * - Content slot for page content
 * - Proper spacing for fixed navigation
 * - SafeTrack design system styling (extra dark mode)
 * - Global PWA install prompt
 * - Global service worker update notification
 * - Global sync queue modal
 * - Accessible navigation landmarks
 */

import { Head } from '@inertiajs/vue3';

import UpdateNotification from '@/components/common/UpdateNotification.vue';
import BottomNav from '@/components/navigation/BottomNav.vue';
import TopNav from '@/components/navigation/TopNav.vue';
import InstallPrompt from '@/components/pwa/InstallPrompt.vue';
import SyncQueueModal from '@/components/sync/SyncQueueModal.vue';
import { useInstallPrompt } from '@/composables/useInstallPrompt';
import { useServiceWorker } from '@/composables/useServiceWorker';
import { useSyncQueue } from '@/composables/useSyncQueue';

// ========================================================================
// Component Props
// ========================================================================

interface Props {
    /** Page title for document head */
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'SafeTrack',
});

// ========================================================================
// Service Worker Update Management
// ========================================================================

const { hasUpdate, applyUpdate } = useServiceWorker();

/** Apply pending Service Worker update. */
const handleUpdate = async (): Promise<void> => {
    try {
        await applyUpdate();
    } catch (error) {
        console.error('[EmployeeLayout] Failed to apply update:', error);
    }
};

/** Dismiss update notification — update applies on next page load. */
const handleDismiss = (): void => {};

// ========================================================================
// PWA Install Prompt Management
// ========================================================================

const { showPrompt: showInstallPrompt, isInstalling, install, dismiss } = useInstallPrompt();

/** Trigger native browser install prompt. */
const handleInstall = async (): Promise<void> => {
    try {
        await install();
    } catch (error) {
        console.error('[EmployeeLayout] PWA installation failed:', error);
    }
};

/** Dismiss install prompt and store preference. */
const handleInstallDismiss = (): void => {
    dismiss();
};

// ========================================================================
// Sync Queue Management
// ========================================================================

const { isModalOpen, closeModal } = useSyncQueue();
</script>

<template>
    <Head :title="props.title" />

    <!-- ======================================================================
        Employee Layout
        Theme-aware with extra dark mode (black/zinc-950 base)
    ======================================================================= -->
    <div class="relative min-h-screen bg-gradient-to-br from-zinc-50 via-white to-zinc-50 dark:from-black dark:via-zinc-950 dark:to-black">
        <!-- Tech Grid Background (64px, theme-aware) -->
        <div
            class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(6,182,212,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.08)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(6,182,212,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.02)_1px,transparent_1px)] bg-[size:64px_64px]"
        ></div>

        <!-- Radial Gradient Overlay -->
        <div
            class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(6,182,212,0.05),transparent_40%)] dark:bg-[radial-gradient(ellipse_at_top_right,rgba(6,182,212,0.08),transparent_50%)]"
        ></div>

        <!-- Top Navigation (Desktop/Tablet) -->
        <TopNav />

        <!-- Main Content Area -->
        <main
            class="relative z-0 pt-0 pb-20 md:pt-16 md:pb-0"
            role="main"
        >
            <slot />
        </main>

        <!-- Bottom Navigation (Mobile) -->
        <BottomNav />

        <!-- PWA Install Prompt -->
        <InstallPrompt
            v-if="showInstallPrompt"
            :is-installing="isInstalling"
            @install="handleInstall"
            @dismiss="handleInstallDismiss"
        />

        <!-- Service Worker Update Notification -->
        <UpdateNotification
            :show="hasUpdate"
            @update="handleUpdate"
            @dismiss="handleDismiss"
        />

        <!-- Sync Queue Modal -->
        <SyncQueueModal
            :show="isModalOpen"
            @close="closeModal"
        />
    </div>
</template>

<style>
/**
 * Mobile: bottom padding for fixed bottom nav.
 *
 * WHY: Prevents content from being hidden behind the fixed bottom navigation.
 */
@media (max-width: 767px) {
    main {
        padding-bottom: calc(80px + env(safe-area-inset-bottom));
    }
}
</style>
