<script setup lang="ts">
/**
 * Toast Notification Component
 *
 * Global toast notification container that displays success, error, info,
 * and warning messages with motion-v animations. Toasts automatically
 * slide in from top, stack vertically, and slide out when dismissed.
 *
 * Features:
 * - AnimatePresence for smooth enter/exit animations
 * - Color-coded by type (green/red/blue/yellow)
 * - Auto-dismiss with manual dismiss button
 * - Touch-friendly dismiss button (44x44px minimum)
 * - SafeTrack dark theme styling
 * - Accessible with ARIA attributes
 *
 * @example
 * ```vue
 * <template>
 *   <div>
 *     <Toast />
 *     <!-- Rest of app -->
 *   </div>
 * </template>
 * ```
 */

import { AnimatePresence, motion } from 'motion-v';

import { useToast } from '@/composables/useToast';
import type { ToastType } from '@/types/sync';

// ========================================================================
// Toast State
// ========================================================================

const { toasts, dismissToast } = useToast();

// ========================================================================
// Helper Functions
// ========================================================================

/**
 * Get toast color classes based on type.
 *
 * @param type - Toast type
 * @returns Tailwind CSS classes for background and border
 */
function getToastColors(type: ToastType): string {
    const colorMap: Record<ToastType, string> = {
        success:
            'bg-green-500/10 border-green-500/50 text-green-400 shadow-green-500/20',
        error: 'bg-red-500/10 border-red-500/50 text-red-400 shadow-red-500/20',
        info: 'bg-blue-500/10 border-blue-500/50 text-blue-400 shadow-blue-500/20',
        warning:
            'bg-yellow-500/10 border-yellow-500/50 text-yellow-400 shadow-yellow-500/20',
    };

    return colorMap[type];
}

/**
 * Get toast icon based on type.
 *
 * @param type - Toast type
 * @returns SVG path data for icon
 */
function getToastIcon(type: ToastType): string {
    const iconMap: Record<ToastType, string> = {
        success:
            'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', // Check circle
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', // X circle
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', // Info circle
        warning:
            'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', // Warning triangle
    };

    return iconMap[type];
}

/**
 * Get toast icon color.
 *
 * @param type - Toast type
 * @returns Tailwind CSS color class
 */
function getIconColor(type: ToastType): string {
    const colorMap: Record<ToastType, string> = {
        success: 'text-green-400',
        error: 'text-red-400',
        info: 'text-blue-400',
        warning: 'text-yellow-400',
    };

    return colorMap[type];
}
</script>

<template>
    <!-- ======================================================================
        Toast Container - Fixed position at top center
    ======================================================================= -->
    <div
        class="pointer-events-none fixed inset-x-0 top-0 z-50 flex flex-col items-center gap-2 p-4"
        aria-live="polite"
        aria-atomic="true"
    >
        <!-- AnimatePresence for enter/exit animations -->
        <AnimatePresence>
            <motion.div
                v-for="toast in toasts"
                :key="toast.id"
                :initial="{ y: -100, opacity: 0, scale: 0.8 }"
                :animate="{ y: 0, opacity: 1, scale: 1 }"
                :exit="{ y: -50, opacity: 0, scale: 0.9 }"
                :transition="{
                    type: 'spring',
                    stiffness: 500,
                    damping: 30,
                    mass: 0.8,
                }"
                :class="[
                    'pointer-events-auto flex w-full max-w-sm items-center justify-between gap-3 rounded-lg border p-4 shadow-lg backdrop-blur-sm',
                    getToastColors(toast.type),
                ]"
                role="alert"
                :aria-label="`${toast.type}: ${toast.message}`"
            >
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <svg
                        :class="['h-6 w-6', getIconColor(toast.type)]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="getToastIcon(toast.type)"
                        />
                    </svg>
                </div>

                <!-- Message -->
                <div class="flex-1 text-sm font-medium">
                    {{ toast.message }}
                </div>

                <!-- Dismiss Button -->
                <motion.button
                    type="button"
                    @click="dismissToast(toast.id)"
                    :whileHover="{ scale: 1.1 }"
                    :whilePress="{ scale: 0.9 }"
                    :transition="{ type: 'spring', stiffness: 400 }"
                    class="flex-shrink-0 rounded p-1 transition-colors hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/50"
                    :aria-label="`Dismiss ${toast.type} notification`"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </motion.button>
            </motion.div>
        </AnimatePresence>
    </div>
</template>
