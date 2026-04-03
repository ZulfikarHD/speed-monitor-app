<script setup lang="ts">
/**
 * PWA Install Prompt Component
 *
 * Custom install prompt for VeloTrack PWA with motion-v animations.
 * Displays a slide-up banner encouraging users to install the app.
 *
 * Features:
 * - Spring animation slide-up entrance/exit
 * - VeloTrack dark theme styling with gradient border
 * - Large touch targets (Fitts's Law)
 * - Only 2 actions: Install / Dismiss (Hick's Law)
 * - Auto-dismiss on successful installation
 * - 7-day dismissal memory
 *
 * Platform Support:
 * - Chrome/Edge Android: Full support with native prompt
 * - iOS Safari: Requires manual "Add to Home Screen"
 * - Desktop Chrome: Creates desktop shortcut
 *
 * @example
 * ```vue
 * <template>
 *   <InstallPrompt @install="handleInstall" @dismiss="handleDismiss" />
 * </template>
 * ```
 */

import { motion } from 'motion-v';

// ========================================================================
// Emits
// ========================================================================

interface Emits {
    /**
     * Emitted when user clicks "Install" button.
     */
    (event: 'install'): void;

    /**
     * Emitted when user clicks "Dismiss" button.
     */
    (event: 'dismiss'): void;
}

const emit = defineEmits<Emits>();

// ========================================================================
// Props
// ========================================================================

interface Props {
    /**
     * Is installation in progress (loading state).
     */
    isInstalling?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isInstalling: false,
});

// ========================================================================
// Event Handlers
// ========================================================================

/**
 * Handle install button click.
 *
 * Emits 'install' event to parent component.
 */
const handleInstall = (): void => {
    emit('install');
};

/**
 * Handle dismiss button click.
 *
 * Emits 'dismiss' event to parent component.
 */
const handleDismiss = (): void => {
    emit('dismiss');
};
</script>

<template>
    <!-- ======================================================================
        PWA Install Prompt Banner
        Slide-up animated prompt with VeloTrack branding
    ======================================================================= -->
    <motion.div
        :initial="{ y: 100, opacity: 0 }"
        :animate="{ y: 0, opacity: 1 }"
        :exit="{ y: 100, opacity: 0 }"
        :transition="{ type: 'spring', bounce: 0.3, duration: 0.6 }"
        class="fixed inset-x-0 bottom-0 z-50 pb-safe"
        role="dialog"
        aria-labelledby="install-prompt-title"
        aria-describedby="install-prompt-description"
    >
        <!-- Background Overlay (dismissible on click) -->
        <div
            class="absolute inset-0 bg-black/50"
            @click="handleDismiss"
            aria-hidden="true"
        ></div>

        <!-- Install Prompt Card -->
        <div class="relative mx-4 mb-4 md:mx-auto md:mb-8 md:max-w-md">
            <motion.div
                :whileHover="{ scale: 1.01 }"
                :transition="{ type: 'spring', stiffness: 400, damping: 20 }"
                class="overflow-hidden rounded-2xl border-2 border-transparent bg-[#1a1d23] shadow-2xl"
                style="
                    background: linear-gradient(#1a1d23, #1a1d23) padding-box,
                        linear-gradient(135deg, #06b6d4, #2563eb) border-box;
                "
            >
                <!-- Content -->
                <div class="p-6">
                    <!-- Icon & Text -->
                    <div class="mb-6 flex items-start gap-4">
                        <!-- VeloTrack Icon -->
                        <motion.div
                            :whileHover="{ scale: 1.1, rotate: 5 }"
                            :whilePress="{ scale: 0.95 }"
                            :transition="{ type: 'spring', bounce: 0.6, duration: 0.5 }"
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/30"
                        >
                            <span
                                class="text-3xl"
                                aria-hidden="true"
                            >
                                🚗
                            </span>
                        </motion.div>

                        <!-- Text Content -->
                        <div class="flex-1">
                            <h3
                                id="install-prompt-title"
                                class="mb-1 text-lg font-semibold text-[#e5e7eb]"
                            >
                                Install VeloTrack
                            </h3>
                            <p
                                id="install-prompt-description"
                                class="text-sm leading-relaxed text-[#9ca3af]"
                            >
                                Install VeloTrack ke layar utama untuk akses cepat dan
                                pengalaman seperti aplikasi native.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <!-- Dismiss Button (Secondary) -->
                        <motion.button
                            :whileHover="{ scale: 1.02 }"
                            :whilePress="{ scale: 0.98 }"
                            :transition="{ type: 'spring', stiffness: 400, damping: 17 }"
                            type="button"
                            class="touch-target flex-1 rounded-lg border-2 border-[#3e3e3a] bg-transparent px-6 py-3 text-sm font-medium text-[#e5e7eb] transition-colors hover:bg-[#2a2d35] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                            @click="handleDismiss"
                        >
                            Nanti Saja
                        </motion.button>

                        <!-- Install Button (Primary) -->
                        <motion.button
                            :whileHover="{ scale: 1.02 }"
                            :whilePress="{ scale: 0.98 }"
                            :transition="{ type: 'spring', stiffness: 400, damping: 17 }"
                            type="button"
                            class="touch-target flex flex-1 items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/30 transition-all hover:from-cyan-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23] disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="props.isInstalling"
                            @click="handleInstall"
                        >
                            <!-- Loading Spinner -->
                            <svg
                                v-if="props.isInstalling"
                                class="h-5 w-5 animate-spin"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>

                            <!-- Button Text -->
                            <span>
                                {{ props.isInstalling ? 'Menginstall...' : 'Install Sekarang' }}
                            </span>
                        </motion.button>
                    </div>
                </div>
            </motion.div>
        </div>
    </motion.div>
</template>

<style scoped>
/**
 * Safe area padding for iOS notch/home indicator.
 */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
