/**
 * PWA Install Prompt Composable
 *
 * Manages the beforeinstallprompt event for custom PWA installation UI.
 * Works on Chrome/Edge Android - iOS Safari requires manual "Add to Home Screen".
 *
 * Features:
 * - Detects beforeinstallprompt event
 * - Provides install() method to trigger native prompt
 * - Tracks user dismissal in localStorage
 * - Auto-hides after successful installation
 *
 * @example
 * ```vue
 * <script setup>
 * import { useInstallPrompt } from '@/composables/useInstallPrompt';
 *
 * const { showPrompt, install, dismiss } = useInstallPrompt();
 * </script>
 *
 * <template>
 *   <InstallPrompt
 *     v-if="showPrompt"
 *     @install="install"
 *     @dismiss="dismiss"
 *   />
 * </template>
 * ```
 */

import { onMounted, onUnmounted, ref } from 'vue';

// ========================================================================
// Types
// ========================================================================

/**
 * BeforeInstallPromptEvent interface.
 *
 * Chrome/Edge-specific event for PWA installation.
 */
interface BeforeInstallPromptEvent extends Event {
    /**
     * Show native install prompt.
     */
    prompt: () => Promise<void>;

    /**
     * User choice result.
     */
    userChoice: Promise<{
        outcome: 'accepted' | 'dismissed';
        platform: string;
    }>;
}

// ========================================================================
// Composable
// ========================================================================

/**
 * Use PWA install prompt composable.
 *
 * Manages beforeinstallprompt event and provides install/dismiss handlers.
 *
 * @returns Install prompt state and methods
 */
export function useInstallPrompt() {
    // Deferred prompt event
    const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null);

    // Show prompt flag
    const showPrompt = ref<boolean>(false);

    // Is installation in progress
    const isInstalling = ref<boolean>(false);

    /**
     * Handle beforeinstallprompt event.
     *
     * Prevents default browser prompt and stores event for later use.
     *
     * @param event - BeforeInstallPromptEvent
     */
    const handleBeforeInstallPrompt = (event: Event): void => {
        // Prevent default browser prompt
        event.preventDefault();

        // Store event for later use
        deferredPrompt.value = event as BeforeInstallPromptEvent;

        // Check if user previously dismissed prompt
        const dismissed = localStorage.getItem('pwa-install-dismissed');
        const dismissedAt = localStorage.getItem('pwa-install-dismissed-at');

        // If dismissed within last 7 days, don't show again
        if (dismissed && dismissedAt) {
            const dismissedDate = new Date(dismissedAt);
            const daysSinceDismissed =
                (Date.now() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24);

            if (daysSinceDismissed < 7) {
                console.log(
                    '[useInstallPrompt] User dismissed prompt recently, not showing again',
                );

                return;
            }
        }

        // Show custom install prompt
        showPrompt.value = true;

        console.log('[useInstallPrompt] Install prompt ready');
    };

    /**
     * Handle app installed event.
     *
     * Hides prompt when app is successfully installed.
     */
    const handleAppInstalled = (): void => {
        console.log('[useInstallPrompt] App installed successfully');

        // Hide prompt
        showPrompt.value = false;
        deferredPrompt.value = null;

        // Clear dismissal flag (user installed, show prompt again if they uninstall)
        localStorage.removeItem('pwa-install-dismissed');
        localStorage.removeItem('pwa-install-dismissed-at');
    };

    /**
     * Trigger native install prompt.
     *
     * Shows browser's native "Add to Home Screen" dialog.
     */
    const install = async (): Promise<void> => {
        if (!deferredPrompt.value) {
            console.warn('[useInstallPrompt] No deferred prompt available');

            return;
        }

        try {
            isInstalling.value = true;

            // Show native prompt
            await deferredPrompt.value.prompt();

            // Wait for user choice
            const { outcome } = await deferredPrompt.value.userChoice;

            console.log('[useInstallPrompt] User choice:', outcome);

            if (outcome === 'accepted') {
                // User accepted installation
                showPrompt.value = false;
            } else {
                // User dismissed native prompt (but keep custom prompt visible)
                console.log('[useInstallPrompt] User dismissed native prompt');
            }
        } catch (error) {
            console.error('[useInstallPrompt] Installation failed:', error);
        } finally {
            isInstalling.value = false;
            deferredPrompt.value = null;
        }
    };

    /**
     * Dismiss install prompt.
     *
     * Hides prompt and stores dismissal in localStorage.
     */
    const dismiss = (): void => {
        showPrompt.value = false;

        // Store dismissal with timestamp
        localStorage.setItem('pwa-install-dismissed', 'true');
        localStorage.setItem('pwa-install-dismissed-at', new Date().toISOString());

        console.log('[useInstallPrompt] Install prompt dismissed by user');
    };

    // ========================================================================
    // Lifecycle
    // ========================================================================

    onMounted(() => {
        // Listen for beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

        // Listen for appinstalled event
        window.addEventListener('appinstalled', handleAppInstalled);

        console.log('[useInstallPrompt] Event listeners registered');
    });

    onUnmounted(() => {
        // Clean up event listeners
        window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.removeEventListener('appinstalled', handleAppInstalled);

        console.log('[useInstallPrompt] Event listeners removed');
    });

    // ========================================================================
    // Return
    // ========================================================================

    return {
        /**
         * Show install prompt flag.
         */
        showPrompt,

        /**
         * Is installation in progress.
         */
        isInstalling,

        /**
         * Trigger native install prompt.
         */
        install,

        /**
         * Dismiss install prompt.
         */
        dismiss,
    };
}
