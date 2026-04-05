<script setup lang="ts">
/**
 * Confirm Deactivate Modal Component
 *
 * Confirmation dialog for deactivating user accounts.
 * Prevents accidental deactivation with explicit user confirmation.
 *
 * Features:
 * - Clear warning message with user details
 * - Danger-styled primary action button
 * - Secondary cancel button
 * - Teleport modal overlay with backdrop click to close
 * - motion-v entrance/exit animations
 * - Inertia router DELETE request
 * - Loading state during submission
 *
 * Props:
 * - show: boolean - Modal visibility
 * - user: User - User to deactivate
 */

import { router } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { ref } from 'vue';

import IconAlert from '@/components/icons/IconAlert.vue';
import IconClose from '@/components/icons/IconClose.vue';
import Button from '@/components/ui/Button.vue';
import type { User } from '@/types/auth';

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Modal visibility */
    show: boolean;
    /** User to deactivate */
    user: User;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

// ========================================================================
// Local State
// ========================================================================

/** Loading state during deactivation */
const isSubmitting = ref(false);

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle deactivate confirmation.
 */
function handleConfirm(): void {
    isSubmitting.value = true;

    router.delete(route('admin.employees.deactivate', { user: props.user.id }), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
}

/**
 * Handle backdrop click to close modal.
 */
function handleBackdropClick(event: MouseEvent): void {
    if (event.target === event.currentTarget && !isSubmitting.value) {
        emit('close');
    }
}

/**
 * Handle escape key to close modal.
 */
function handleEscapeKey(event: KeyboardEvent): void {
    if (event.key === 'Escape' && props.show && !isSubmitting.value) {
        emit('close');
    }
}

// Setup escape key listener
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleEscapeKey);
}
</script>

<template>
    <!-- Modal Overlay -->
    <Teleport to="body">
        <AnimatePresence>
            <motion.div
                v-if="show"
                @click="handleBackdropClick"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.2 }"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="'confirm-deactivate-modal-title'"
            >
                <!-- Modal Content -->
                <motion.div
                    :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, y: 20 }"
                    :transition="{ duration: 0.3 }"
                    class="w-full max-w-md rounded-lg border border-zinc-200 dark:border-white/5 bg-white dark:bg-zinc-800/50 backdrop-blur-xl shadow-xl"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-white/5 p-6">
                        <div class="flex items-center gap-3">
                            <IconAlert
                                :size="24"
                                class="text-red-600 dark:text-red-400"
                            />
                            <h2
                                id="confirm-deactivate-modal-title"
                                class="text-xl font-semibold text-zinc-900 dark:text-white"
                            >
                                Konfirmasi Nonaktifkan
                            </h2>
                        </div>
                        <button
                            @click="emit('close')"
                            :disabled="isSubmitting"
                            class="rounded-lg p-2 text-zinc-500 dark:text-zinc-400 transition-all duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                            aria-label="Tutup"
                        >
                            <IconClose :size="20" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <!-- Warning Message -->
                        <div class="rounded-lg border border-red-500/30 dark:border-red-500/30 bg-red-100 dark:bg-red-500/10 p-4">
                            <p class="text-sm text-red-800 dark:text-red-300">
                                Anda yakin ingin menonaktifkan akun karyawan berikut?
                            </p>
                        </div>

                        <!-- User Details -->
                        <div class="rounded-lg border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50 p-4">
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Nama
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ user.name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Email
                                    </dt>
                                    <dd class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ user.email }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        Role
                                    </dt>
                                    <dd class="mt-1">
                                        <span
                                            class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                                            :class="
                                                user.role === 'admin'
                                                    ? 'border-purple-500/30 bg-purple-500/20 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300'
                                                    : user.role === 'supervisor'
                                                        ? 'border-blue-500/30 bg-blue-500/20 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300'
                                                        : 'border-emerald-500/30 bg-emerald-500/20 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'
                                            "
                                        >
                                            {{
                                                user.role === 'admin'
                                                    ? 'Admin'
                                                    : user.role === 'supervisor'
                                                        ? 'Supervisor'
                                                        : 'Karyawan'
                                            }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Info Message -->
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Akun yang dinonaktifkan tidak dapat login ke sistem.
                            Anda dapat mengaktifkan kembali akun ini nanti jika diperlukan.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 pt-2">
                            <Button
                                type="button"
                                variant="secondary"
                                full-width
                                :disabled="isSubmitting"
                                @click="emit('close')"
                            >
                                Batal
                            </Button>
                            <Button
                                type="button"
                                variant="danger"
                                full-width
                                :loading="isSubmitting"
                                @click="handleConfirm"
                            >
                                Nonaktifkan
                            </Button>
                        </div>
                    </div>
                </motion.div>
            </motion.div>
        </AnimatePresence>
    </Teleport>
</template>
