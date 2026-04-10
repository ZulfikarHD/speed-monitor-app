<script setup lang="ts">
/**
 * PWA Install Prompt Component
 *
 * Custom install prompt banner for SafeTrack PWA.
 * Slide-up prompt encouraging users to install the app.
 *
 * Features:
 * - Slide-up entrance animation
 * - SafeTrack branding with gradient border
 * - Large touch targets per Fitts's Law
 * - Two actions: Install / Dismiss per Hick's Law
 * - Loading state during installation
 * - 7-day dismissal memory
 *
 * Platform Support:
 * - Chrome/Edge Android: Full support with native prompt
 * - iOS Safari: Requires manual "Add to Home Screen"
 * - Desktop Chrome: Creates desktop shortcut
 */

import { Loader2, Motorbike } from '@lucide/vue';
import { motion } from 'motion-v';

// ========================================================================
// Props & Emits
// ========================================================================

interface Emits {
    /** User clicks "Install" button */
    (event: 'install'): void;
    /** User clicks "Dismiss" button */
    (event: 'dismiss'): void;
}

interface Props {
    /** Is installation in progress (loading state) */
    isInstalling?: boolean;
}

const emit = defineEmits<Emits>();

const props = withDefaults(defineProps<Props>(), {
    isInstalling: false,
});

// ========================================================================
// Event Handlers
// ========================================================================

/** Emit install event to parent. */
const handleInstall = (): void => {
    emit('install');
};

/** Emit dismiss event to parent. */
const handleDismiss = (): void => {
    emit('dismiss');
};
</script>

<template>
    <!-- ======================================================================
        PWA Install Prompt Banner
        Slide-up with gradient border accent
    ======================================================================= -->
    <motion.div
        :initial="{ y: 100, opacity: 0 }"
        :animate="{ y: 0, opacity: 1 }"
        :exit="{ y: 100, opacity: 0 }"
        :transition="{ duration: 0.3 }"
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
            <div
                class="overflow-hidden rounded-2xl border-2 border-transparent shadow-2xl bg-white/95 dark:bg-zinc-900/98"
                style="
                    background: linear-gradient(var(--card-bg), var(--card-bg)) padding-box,
                        linear-gradient(135deg, #06b6d4, #2563eb) border-box;
                    --card-bg: rgb(24 24 27 / 0.98);
                "
            >
                <div class="p-6">
                    <!-- Icon & Text -->
                    <div class="mb-6 flex items-start gap-4">
                        <!-- SafeTrack Icon -->
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/30"
                        >
                            <Motorbike
                                :size="28"
                                class="text-white"
                                aria-hidden="true"
                            />
                        </div>

                        <!-- Text Content -->
                        <div class="flex-1">
                            <h3
                                id="install-prompt-title"
                                class="mb-1 text-lg font-semibold text-zinc-900 dark:text-white"
                            >
                                Install SafeTrack
                            </h3>
                            <p
                                id="install-prompt-description"
                                class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400"
                            >
                                Install SafeTrack ke layar utama untuk akses cepat dan
                                pengalaman seperti aplikasi native.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <!-- Dismiss Button (Secondary) -->
                        <button
                            type="button"
                            class="flex-1 rounded-lg px-6 py-3 text-sm font-medium transition-colors duration-200 border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                            @click="handleDismiss"
                        >
                            Nanti Saja
                        </button>

                        <!-- Install Button (Primary) -->
                        <button
                            type="button"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold transition-all duration-200 bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/30 hover:shadow-xl active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="props.isInstalling"
                            @click="handleInstall"
                        >
                            <Loader2
                                v-if="props.isInstalling"
                                :size="18"
                                class="animate-spin"
                                aria-hidden="true"
                            />
                            <span>
                                {{ props.isInstalling ? 'Menginstall...' : 'Install Sekarang' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </motion.div>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}

/* Light mode card bg override */
:root:not(.dark) .overflow-hidden {
    --card-bg: rgb(255 255 255 / 0.95);
}
</style>
