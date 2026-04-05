<script setup lang="ts">
/**
 * Update Notification Component
 *
 * Service Worker update notification with slide-in animation.
 * Displays when a new app version is available with update/dismiss actions.
 *
 * Features:
 * - Slide-in from bottom with motion-v
 * - Two actions: "Nanti Saja" (dismiss) and "Perbarui Sekarang" (update)
 * - Loading state on update button
 * - Auto-dismiss after configurable seconds
 * - Touch-friendly buttons (>=44px)
 * - Safe area padding for iOS
 * - Theme-aware design with fake glass effect
 *
 * UX Principles:
 * - Jakob's Law: Familiar PWA update pattern (Chrome/Edge style)
 * - Fitts's Law: Large touch targets (>=44px buttons)
 * - Hick's Law: Two clear choices (Update / Later)
 */

import { RefreshCw, Rocket } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Whether to show the notification */
    show?: boolean;
    /** Auto-reload after update (default: true) */
    autoReload?: boolean;
    /** Allow dismissing notification (default: true) */
    dismissible?: boolean;
    /** Auto-dismiss after N seconds (0 = never, default: 30) */
    autoDismissSeconds?: number;
}

interface Emits {
    /** User clicks "Perbarui Sekarang" */
    (e: 'update'): void;
    /** User clicks "Nanti Saja" or auto-dismiss triggers */
    (e: 'dismiss'): void;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    autoReload: true,
    dismissible: true,
    autoDismissSeconds: 30,
});

const emit = defineEmits<Emits>();

// ========================================================================
// State
// ========================================================================

const isUpdating = ref(false);
let autoDismissTimer: ReturnType<typeof setTimeout> | null = null;

// ========================================================================
// Computed
// ========================================================================

const updateButtonText = computed(() => {
    return isUpdating.value ? 'Memperbarui...' : 'Perbarui Sekarang';
});

// ========================================================================
// Methods
// ========================================================================

/** Trigger update and show loading state. */
const handleUpdate = (): void => {
    if (isUpdating.value) {
return;
}

    isUpdating.value = true;
    emit('update');
};

/** Dismiss notification. */
const handleDismiss = (): void => {
    if (!props.dismissible) {
return;
}

    clearAutoDismissTimer();
    emit('dismiss');
};

const startAutoDismissTimer = (): void => {
    if (!props.autoDismissSeconds || props.autoDismissSeconds <= 0) {
return;
}

    clearAutoDismissTimer();
    autoDismissTimer = setTimeout(() => {
        handleDismiss();
    }, props.autoDismissSeconds * 1000);
};

const clearAutoDismissTimer = (): void => {
    if (autoDismissTimer) {
        clearTimeout(autoDismissTimer);
        autoDismissTimer = null;
    }
};

// ========================================================================
// Lifecycle
// ========================================================================

onMounted(() => {
    if (props.show) {
startAutoDismissTimer();
}
});

onUnmounted(() => {
    clearAutoDismissTimer();
});
</script>

<template>
    <!-- Update Notification Container -->
    <AnimatePresence>
        <motion.div
            v-if="show"
            :initial="{ y: 100, opacity: 0 }"
            :animate="{ y: 0, opacity: 1 }"
            :exit="{ y: 100, opacity: 0 }"
            :transition="{ duration: 0.3 }"
            class="fixed bottom-0 left-0 right-0 z-50 pb-safe md:bottom-6 md:left-auto md:right-6"
        >
            <!-- Notification Card (fake glass) -->
            <div
                class="mx-4 rounded-2xl p-4 shadow-2xl md:mx-0 md:w-96 bg-white/95 dark:bg-zinc-900/98 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 shadow-zinc-900/5 dark:shadow-cyan-500/5"
            >
                <!-- Header Section -->
                <div class="mb-3 flex items-start gap-3">
                    <!-- Icon -->
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25"
                    >
                        <RefreshCw
                            :size="20"
                            class="text-white"
                        />
                    </div>

                    <!-- Title and Description -->
                    <div class="flex-1">
                        <h3 class="mb-1 text-base font-semibold text-zinc-900 dark:text-white">
                            Pembaruan Tersedia
                        </h3>
                        <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            Versi baru aplikasi siap diinstal. Perbarui sekarang untuk mendapatkan fitur terbaru.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <!-- Dismiss Button -->
                    <button
                        v-if="dismissible"
                        type="button"
                        class="flex-1 rounded-xl px-4 py-3 text-sm font-medium transition-colors duration-200 border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isUpdating"
                        @click="handleDismiss"
                    >
                        Nanti Saja
                    </button>

                    <!-- Update Button -->
                    <button
                        type="button"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200 bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 hover:shadow-xl active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="isUpdating"
                        @click="handleUpdate"
                    >
                        <RefreshCw
                            v-if="isUpdating"
                            :size="16"
                            class="animate-spin"
                        />
                        <span>{{ updateButtonText }}</span>
                        <Rocket
                            v-if="!isUpdating"
                            :size="16"
                        />
                    </button>
                </div>

                <!-- Auto-dismiss indicator -->
                <div
                    v-if="autoDismissSeconds > 0 && dismissible"
                    class="mt-3 text-center text-xs text-zinc-500 dark:text-zinc-500"
                >
                    Otomatis tertutup dalam {{ autoDismissSeconds }} detik
                </div>
            </div>
        </motion.div>
    </AnimatePresence>
</template>

<style scoped>
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .pb-safe {
        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    }
}
</style>
