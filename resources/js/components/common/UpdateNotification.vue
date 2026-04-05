<!--
==============================================================================
UPDATE NOTIFICATION COMPONENT
==============================================================================

Service Worker update notification with motion-v animations.
Displays a slide-in notification when a new app version is available.

Features:
- Slide-in from bottom with spring animation
- Two action buttons: "Nanti Saja" (dismiss) and "Perbarui Sekarang" (update)
- Loading state on update button
- Auto-dismiss after 30 seconds (optional)
- Touch-friendly buttons (≥48px)
- Safe area padding for iOS notch
- SpeedoMontor dark theme styling

UX Laws Applied:
- Jakob's Law: Familiar PWA update pattern (Chrome/Edge style)
- Fitts's Law: Large touch targets (≥48px buttons)
- Miller's Law: Two clear choices (Update / Later)
- Aesthetic-Usability: Smooth spring animations for polish

@example
```vue
<template>
  <UpdateNotification
    :show="hasUpdate"
    @update="handleUpdate"
    @dismiss="handleDismiss"
  />
</template>
```
==============================================================================
-->

<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';

/**
 * Component props.
 */
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

/**
 * Component emits.
 */
interface Emits {
    /** Emitted when user clicks "Perbarui Sekarang" */
    (e: 'update'): void;

    /** Emitted when user clicks "Nanti Saja" or auto-dismiss triggers */
    (e: 'dismiss'): void;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    autoReload: true,
    dismissible: true,
    autoDismissSeconds: 30,
});

const emit = defineEmits<Emits>();

// ==============================================================================
// State
// ==============================================================================

/**
 * Whether update is being applied.
 */
const isUpdating = ref(false);

/**
 * Auto-dismiss timer reference.
 */
let autoDismissTimer: ReturnType<typeof setTimeout> | null = null;

// ==============================================================================
// Computed
// ==============================================================================

/**
 * Button text for update action.
 */
const updateButtonText = computed(() => {
    return isUpdating.value ? 'Memperbarui...' : 'Perbarui Sekarang';
});

// ==============================================================================
// Methods
// ==============================================================================

/**
 * Handle update button click.
 * Triggers update and shows loading state.
 */
const handleUpdate = (): void => {
    if (isUpdating.value) {
        return;
    }

    isUpdating.value = true;
    emit('update');

    // Note: Page will reload automatically after SW activation
    // Loading state kept until reload happens
};

/**
 * Handle dismiss button click.
 * Closes notification without updating.
 */
const handleDismiss = (): void => {
    if (!props.dismissible) {
        return;
    }

    clearAutoDismissTimer();
    emit('dismiss');
};

/**
 * Start auto-dismiss timer.
 * Automatically dismisses notification after specified seconds.
 */
const startAutoDismissTimer = (): void => {
    if (!props.autoDismissSeconds || props.autoDismissSeconds <= 0) {
        return;
    }

    clearAutoDismissTimer();

    autoDismissTimer = setTimeout(() => {
        handleDismiss();
    }, props.autoDismissSeconds * 1000);
};

/**
 * Clear auto-dismiss timer.
 */
const clearAutoDismissTimer = (): void => {
    if (autoDismissTimer) {
        clearTimeout(autoDismissTimer);
        autoDismissTimer = null;
    }
};

// ==============================================================================
// Lifecycle
// ==============================================================================

/**
 * Start auto-dismiss timer when notification shown.
 */
onMounted(() => {
    if (props.show) {
        startAutoDismissTimer();
    }
});

/**
 * Clean up timer on unmount.
 */
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
            :transition="{
                type: 'spring',
                stiffness: 300,
                damping: 30,
                mass: 0.8,
            }"
            class="fixed bottom-0 left-0 right-0 z-50 pb-safe md:bottom-6 md:left-auto md:right-6"
        >
            <!-- Notification Card -->
            <div
                class="mx-4 rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-800 to-slate-900 p-4 shadow-2xl backdrop-blur-xl md:mx-0 md:w-96"
            >
                <!-- Header Section -->
                <div class="mb-3 flex items-start gap-3">
                    <!-- Icon -->
                    <motion.div
                        :initial="{ scale: 0, rotate: -180 }"
                        :animate="{ scale: 1, rotate: 0 }"
                        :transition="{ delay: 0.1, type: 'spring', stiffness: 200 }"
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600"
                    >
                        <svg
                            class="size-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </motion.div>

                    <!-- Title and Description -->
                    <div class="flex-1">
                        <h3 class="mb-1 text-base font-semibold text-slate-100">
                            Pembaruan Tersedia
                        </h3>
                        <p class="text-sm leading-relaxed text-slate-400">
                            Versi baru aplikasi siap diinstal. Perbarui sekarang untuk mendapatkan fitur terbaru.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <!-- Dismiss Button (if dismissible) -->
                    <button
                        v-if="dismissible"
                        type="button"
                        class="flex-1 rounded-xl border border-slate-600 bg-slate-800/50 px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:border-slate-500 hover:bg-slate-700/50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isUpdating"
                        @click="handleDismiss"
                    >
                        Nanti Saja
                    </button>

                    <!-- Update Button -->
                    <button
                        type="button"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/30 transition-all hover:from-blue-500 hover:to-blue-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="isUpdating"
                        @click="handleUpdate"
                    >
                        <!-- Loading Spinner -->
                        <motion.svg
                            v-if="isUpdating"
                            :animate="{ rotate: 360 }"
                            :transition="{ duration: 1, repeat: Infinity, ease: 'linear' }"
                            class="size-4"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </motion.svg>

                        <!-- Button Text -->
                        <span>{{ updateButtonText }}</span>

                        <!-- Rocket Icon (when not loading) -->
                        <span v-if="!isUpdating">🚀</span>
                    </button>
                </div>

                <!-- Auto-dismiss indicator (optional) -->
                <motion.div
                    v-if="autoDismissSeconds > 0 && dismissible"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :transition="{ delay: 0.3 }"
                    class="mt-3 text-center text-xs text-slate-500"
                >
                    Otomatis tertutup dalam {{ autoDismissSeconds }} detik
                </motion.div>
            </div>
        </motion.div>
    </AnimatePresence>
</template>

<style scoped>
/* Safe area padding for iOS notch */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .pb-safe {
        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    }
}
</style>
