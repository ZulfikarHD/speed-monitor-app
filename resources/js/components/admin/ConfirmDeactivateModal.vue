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
    <!-- ======================================================================
        Modal Overlay
    ======================================================================= -->
    <Teleport to="body">
        <AnimatePresence>
            <motion.div
                v-if="show"
                @click="handleBackdropClick"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.2 }"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="'confirm-deactivate-modal-title'"
            >
                <!-- ======================================================================
                    Modal Content
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, y: 20 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.4 }"
                    class="w-full max-w-md rounded-lg border border-[#3E3E3A] bg-[#1a1d23] shadow-xl"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-[#3E3E3A] p-6">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl" aria-hidden="true">⚠️</span>
                            <h2
                                id="confirm-deactivate-modal-title"
                                class="text-xl font-semibold text-[#EDEDEC]"
                            >
                                Konfirmasi Nonaktifkan
                            </h2>
                        </div>
                        <button
                            @click="emit('close')"
                            :disabled="isSubmitting"
                            class="rounded-lg p-2 text-[#A1A09A] transition-colors hover:bg-[#3E3E3A] hover:text-[#EDEDEC] disabled:cursor-not-allowed disabled:opacity-50"
                            aria-label="Tutup"
                        >
                            <span class="text-xl" aria-hidden="true">✕</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <!-- Warning Message -->
                        <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-4">
                            <p class="text-sm text-red-300">
                                Anda yakin ingin menonaktifkan akun karyawan berikut?
                            </p>
                        </div>

                        <!-- User Details -->
                        <div class="rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] p-4">
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-xs font-medium text-[#6b7280]">
                                        Nama
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-[#e5e7eb]">
                                        {{ user.name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-[#6b7280]">
                                        Email
                                    </dt>
                                    <dd class="mt-1 text-sm text-[#A1A09A]">
                                        {{ user.email }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-[#6b7280]">
                                        Role
                                    </dt>
                                    <dd class="mt-1">
                                        <span
                                            class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                                            :class="
                                                user.role === 'admin'
                                                    ? 'border-purple-500/30 bg-purple-500/20 text-purple-400'
                                                    : user.role === 'supervisor'
                                                        ? 'border-blue-500/30 bg-blue-500/20 text-blue-400'
                                                        : 'border-green-500/30 bg-green-500/20 text-green-400'
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
                        <p class="text-sm text-[#A1A09A]">
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
