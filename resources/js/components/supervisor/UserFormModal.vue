<script setup lang="ts">
/**
 * User Form Modal Component (Supervisor)
 *
 * Modal form for creating new users or editing existing users.
 * Supports validation, role assignment, and account status management.
 *
 * Features:
 * - Create mode (password required) or edit mode (password optional)
 * - Reusable UI components (Input, Label, Button)
 * - Client-side validation with error display
 * - Inertia form submission with useForm()
 * - Teleport modal overlay
 * - Lightweight entrance/exit animations
 * - Indonesian labels and messages
 * - Wayfinder type-safe routing
 * - Full light/dark theme support
 */

import { useForm } from '@inertiajs/vue3';
import { X } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, watch } from 'vue';

import { store, update } from '@/actions/App/Http/Controllers/Supervisor/EmployeesController';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import type { User, UserFormData } from '@/types/auth';

// ========================================================================
// Props & Emits
// ========================================================================

interface Props {
    /** Modal visibility */
    show: boolean;
    /** User to edit (undefined for create mode) */
    user?: User;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

// ========================================================================
// Computed
// ========================================================================

/** Check if in edit mode. */
const isEditMode = computed(() => !!props.user);

/** Modal title based on mode. */
const modalTitle = computed(() => {
    return isEditMode.value ? 'Edit Karyawan' : 'Tambah Karyawan Baru';
});

/** Submit button text based on mode. */
const submitButtonText = computed(() => {
    return isEditMode.value ? 'Perbarui' : 'Tambah';
});

// ========================================================================
// Form Setup
// ========================================================================

/** Inertia form instance. */
const form = useForm<UserFormData>({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    role: props.user?.role ?? 'employee',
    is_active: props.user?.is_active ?? true,
});

/** Watch for user prop changes to reset form. */
watch(
    () => props.user,
    (newUser) => {
        if (newUser) {
            form.name = newUser.name;
            form.email = newUser.email;
            form.password = '';
            form.role = newUser.role;
            form.is_active = newUser.is_active;
        } else {
            form.reset();
        }
    }
);

/** Watch for show prop changes to reset form when closed. */
watch(
    () => props.show,
    (isShowing) => {
        if (!isShowing) {
            form.reset();
            form.clearErrors();
        }
    }
);

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle form submission.
 *
 * WHY: Uses Wayfinder for type-safe routing with automatic method spoofing.
 * PUT requests for updates, POST for creation.
 */
function handleSubmit(): void {
    if (isEditMode.value && props.user) {
        form.put(update.url({ user: props.user.id }), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        form.post(store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
            },
        });
    }
}

/**
 * Handle backdrop click to close modal.
 *
 * @param event - Mouse click event
 */
function handleBackdropClick(event: MouseEvent): void {
    if (event.target === event.currentTarget) {
        emit('close');
    }
}

/**
 * Handle escape key to close modal.
 *
 * @param event - Keyboard event
 */
function handleEscapeKey(event: KeyboardEvent): void {
    if (event.key === 'Escape' && props.show) {
        emit('close');
    }
}

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
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.2 }"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="'user-form-modal-title'"
                @click="handleBackdropClick"
            >
                <!-- Modal Content -->
                <motion.div
                    :initial="{ opacity: 0, scale: 0.95, y: 12 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, y: 12 }"
                    :transition="{ duration: 0.2 }"
                    class="w-full max-w-lg rounded-lg border border-zinc-200 dark:border-white/5 bg-white/95 dark:bg-zinc-900/98 ring-1 ring-white/20 dark:ring-white/5 shadow-xl shadow-zinc-900/10 dark:shadow-cyan-500/5"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-white/5 p-6">
                        <h2
                            id="user-form-modal-title"
                            class="text-xl font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ modalTitle }}
                        </h2>
                        <button
                            class="rounded-lg p-2 text-zinc-500 dark:text-zinc-400 transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-white"
                            aria-label="Tutup"
                            @click="emit('close')"
                        >
                            <X :size="20" :stroke-width="2" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form
                        class="space-y-4 p-6"
                        @submit.prevent="handleSubmit"
                    >
                        <!-- Name Field -->
                        <div>
                            <Label for="name" required>
                                Nama Lengkap
                            </Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan nama lengkap"
                                :error="form.errors.name"
                                autocomplete="name"
                            />
                        </div>

                        <!-- Email Field -->
                        <div>
                            <Label for="email" required>
                                Email
                            </Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="nama@email.com"
                                :error="form.errors.email"
                                autocomplete="email"
                            />
                        </div>

                        <!-- Password Field -->
                        <div>
                            <Label
                                for="password"
                                :required="!isEditMode"
                            >
                                Password
                                <span
                                    v-if="isEditMode"
                                    class="ml-1 text-xs text-zinc-500 dark:text-zinc-500"
                                >
                                    (Kosongkan jika tidak ingin mengubah)
                                </span>
                            </Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                :error="form.errors.password"
                                autocomplete="new-password"
                            />
                        </div>

                        <!-- Role Field -->
                        <div>
                            <Label for="role" required>
                                Role
                            </Label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="w-full rounded-lg border px-4 py-3 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="[
                                    form.errors.role
                                        ? 'border-red-400 bg-red-50 dark:bg-red-950/20 text-red-900 dark:text-red-100 focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400/50 focus:ring-offset-white dark:focus:ring-offset-zinc-900'
                                        : 'border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:border-cyan-500 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-white dark:focus:ring-offset-zinc-900',
                                ]"
                            >
                                <option value="employee">Karyawan</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Admin</option>
                            </select>
                            <p
                                v-if="form.errors.role"
                                class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                                role="alert"
                            >
                                {{ form.errors.role }}
                            </p>
                        </div>

                        <!-- Status Field -->
                        <div>
                            <Label for="is-active">
                                Status Akun
                            </Label>
                            <div class="mt-2 flex items-center gap-3">
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        id="is-active"
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="peer h-6 w-11 rounded-full bg-zinc-300 dark:bg-zinc-700 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-zinc-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-cyan-500 dark:peer-checked:bg-cyan-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-cyan-500 dark:peer-focus:ring-cyan-400/50 peer-focus:ring-offset-2 peer-focus:ring-offset-white dark:peer-focus:ring-offset-zinc-900"
                                    ></div>
                                </label>
                                <span class="text-sm text-zinc-900 dark:text-white">
                                    {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p
                                v-if="form.errors.is_active"
                                class="mt-1.5 text-sm text-red-600 dark:text-red-400"
                                role="alert"
                            >
                                {{ form.errors.is_active }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 pt-4">
                            <Button
                                type="button"
                                variant="secondary"
                                full-width
                                @click="emit('close')"
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                variant="primary"
                                full-width
                                :loading="form.processing"
                            >
                                {{ submitButtonText }}
                            </Button>
                        </div>
                    </form>
                </motion.div>
            </motion.div>
        </AnimatePresence>
    </Teleport>
</template>
