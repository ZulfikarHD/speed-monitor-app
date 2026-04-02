/**
 * Toast Notification Composable
 *
 * Provides global toast notification management for displaying success, error,
 * info, and warning messages to users. Supports auto-dismiss and manual dismissal
 * with motion-v animations.
 *
 * Features:
 * - Type-safe toast creation (success/error/info/warning)
 * - Auto-dismiss after configurable duration
 * - Manual dismissal with dismiss button
 * - Maximum toast limit (3 by default)
 * - Global state shared across components
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useToast } from '@/composables/useToast';
 *
 * const { showToast } = useToast();
 *
 * async function handleSync() {
 *   try {
 *     await syncService.syncAllPendingTrips();
 *     showToast('Sinkronisasi berhasil!', 'success');
 *   } catch (error) {
 *     showToast(`Gagal sinkronisasi: ${error.message}`, 'error');
 *   }
 * }
 * </script>
 * ```
 */

import { ref } from 'vue';

import type { Toast, ToastType } from '@/types/sync';

/**
 * Global toast state.
 *
 * WHY: Shared across all components for consistent toast display.
 * WHY: Using reactive ref allows toast container to observe changes.
 */
const toasts = ref<Toast[]>([]);

/**
 * Maximum number of toasts to display at once.
 *
 * WHY: Follows Miller's Law (7±2 items) - limit to 3 for clarity.
 * WHY: Prevents toast stack overflow and UI clutter.
 */
const MAX_TOASTS = 3;

/**
 * Default toast duration in milliseconds.
 *
 * WHY: 5 seconds provides enough time to read without being annoying.
 */
const DEFAULT_DURATION = 5000;

/**
 * Toast notification composable.
 *
 * Provides methods for creating and managing toast notifications.
 * Uses global state to ensure toasts are visible from any component.
 *
 * @returns Object with toast state and management methods
 *
 * @example
 * ```ts
 * const { toasts, showToast, dismissToast } = useToast();
 *
 * showToast('Operasi berhasil!', 'success');
 * showToast('Terjadi kesalahan', 'error', 7000); // Custom duration
 * ```
 */
export function useToast() {
    /**
     * Show a toast notification.
     *
     * Creates a new toast and adds it to the global toast stack.
     * Automatically dismisses after specified duration.
     * If max toasts reached, removes oldest toast first.
     *
     * WHY: Centralized toast creation ensures consistent behavior.
     * WHY: Auto-dismiss reduces user interaction burden.
     * WHY: Toast limit prevents UI overwhelming.
     *
     * @param message - Message text to display
     * @param type - Toast type (success/error/info/warning)
     * @param duration - Duration in ms before auto-dismiss (default: 5000)
     * @returns Toast ID for manual dismissal if needed
     *
     * @example
     * ```ts
     * showToast('Trip berhasil disimpan', 'success');
     * showToast('Koneksi gagal', 'error', 7000);
     * ```
     */
    function showToast(
        message: string,
        type: ToastType = 'info',
        duration: number = DEFAULT_DURATION,
    ): number {
        // Generate unique ID
        const id = Date.now() + Math.random();

        // Create toast object
        const toast: Toast = {
            id,
            message,
            type,
            duration,
        };

        // Remove oldest toast if at max capacity
        if (toasts.value.length >= MAX_TOASTS) {
            toasts.value.shift();
        }

        // Add toast to stack
        toasts.value.push(toast);

        // Auto-dismiss after duration
        setTimeout(() => {
            dismissToast(id);
        }, duration);

        return id;
    }

    /**
     * Dismiss a specific toast by ID.
     *
     * Removes toast from stack, triggering exit animation.
     *
     * WHY: Manual dismissal gives users control.
     * WHY: ID-based removal allows dismissing specific toasts.
     *
     * @param id - Toast ID to dismiss
     *
     * @example
     * ```ts
     * const toastId = showToast('Loading...', 'info');
     * // Later...
     * dismissToast(toastId);
     * ```
     */
    function dismissToast(id: number): void {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    }

    /**
     * Dismiss all toasts.
     *
     * Clears entire toast stack at once.
     *
     * WHY: Useful for clearing stale toasts on navigation or errors.
     *
     * @example
     * ```ts
     * dismissAll(); // Clear all toasts
     * ```
     */
    function dismissAll(): void {
        toasts.value = [];
    }

    /**
     * Show success toast.
     *
     * Convenience method for common success messages.
     *
     * @param message - Success message
     * @returns Toast ID
     */
    function showSuccess(message: string): number {
        return showToast(message, 'success');
    }

    /**
     * Show error toast.
     *
     * Convenience method for error messages with longer duration.
     *
     * @param message - Error message
     * @returns Toast ID
     */
    function showError(message: string): number {
        return showToast(message, 'error', 7000); // 7s for errors
    }

    /**
     * Show info toast.
     *
     * Convenience method for informational messages.
     *
     * @param message - Info message
     * @returns Toast ID
     */
    function showInfo(message: string): number {
        return showToast(message, 'info');
    }

    /**
     * Show warning toast.
     *
     * Convenience method for warning messages.
     *
     * @param message - Warning message
     * @returns Toast ID
     */
    function showWarning(message: string): number {
        return showToast(message, 'warning', 6000); // 6s for warnings
    }

    return {
        toasts,
        showToast,
        dismissToast,
        dismissAll,
        showSuccess,
        showError,
        showInfo,
        showWarning,
    };
}
