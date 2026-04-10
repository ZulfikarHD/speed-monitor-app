<script setup lang="ts">
/**
 * Change Password Page - User password management.
 *
 * Allows authenticated users to change their password with current
 * password verification for security.
 *
 * Features:
 * - Current password verification
 * - New password with confirmation
 * - Server-side validation with error messages
 * - Success flash messages with auto-dismiss
 * - Form reset on success
 * - Wayfinder type-safe routes
 * - Design system styling (zinc colors with dark mode)
 * - Lucide icons for visual hierarchy
 * - Responsive design (mobile-first)
 * - Security tips with info icon
 *
 * @example Route: /profile/change-password
 */

import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    Info,
    KeyRound,
    Lock,
    X,
    XCircle,
} from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onMounted, ref, watch } from 'vue';

import { updatePassword } from '@/actions/App/Http/Controllers/ProfileController';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';

// ========================================================================
// Dependencies
// ========================================================================

const page = usePage();

// ========================================================================
// Forms
// ========================================================================

const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

// ========================================================================
// State
// ========================================================================

const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);

// ========================================================================
// Computed
// ========================================================================

const successMessage = computed(() => page.props.flash?.success as string);
const errorMessage = computed(() => page.props.flash?.error as string);

const hasValidationErrors = computed(() => {
    return Object.keys(passwordForm.errors).length > 0;
});

// ========================================================================
// Watchers
// ========================================================================

watch(successMessage, (newVal) => {
    if (newVal) {
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
});

watch(errorMessage, (newVal) => {
    if (newVal) {
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 7000);
    }
});

watch(hasValidationErrors, (newVal) => {
    if (newVal) {
        showErrorMessage.value = true;
    }
});

// ========================================================================
// Lifecycle
// ========================================================================

onMounted(() => {
    if (successMessage.value) {
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }

    if (errorMessage.value) {
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 7000);
    }
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Manually dismiss success message.
 */
function dismissSuccess(): void {
    showSuccessMessage.value = false;
}

/**
 * Manually dismiss error message.
 */
function dismissError(): void {
    showErrorMessage.value = false;
}

/**
 * Submit password change.
 */
function submitPassword(): void {
    passwordForm.put(updatePassword.url(), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
}
</script>

<template>
    <EmployeeLayout>
        <Head title="Ubah Kata Sandi" />

        <!-- Container -->
        <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <motion.header
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3 }"
                class="mb-8"
            >
                <!-- Back Link -->
                <Link
                    href="/profile"
                    class="mb-4 inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors"
                >
                    <ArrowLeft :size="16" />
                    <span>Kembali ke Profil</span>
                </Link>

                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-500/10 dark:bg-cyan-500/15"
                    >
                        <KeyRound
                            :size="24"
                            class="text-cyan-600 dark:text-cyan-400"
                        />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-semibold text-zinc-900 dark:text-white"
                        >
                            Ubah Kata Sandi
                        </h1>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Perbarui kata sandi untuk menjaga keamanan akun
                        </p>
                    </div>
                </div>
            </motion.header>

            <!-- Success Message -->
            <AnimatePresence>
                <motion.div
                    v-if="successMessage && showSuccessMessage"
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -10 }"
                    :transition="{ duration: 0.2 }"
                    class="mb-6 rounded-lg border border-emerald-500/30 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 shadow-sm"
                    role="alert"
                    aria-live="polite"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <CheckCircle
                                :size="20"
                                class="flex-shrink-0 text-emerald-600 dark:text-emerald-400"
                            />
                            <span
                                class="text-sm font-medium text-emerald-800 dark:text-emerald-200"
                            >
                                {{ successMessage }}
                            </span>
                        </div>
                        <button
                            type="button"
                            @click="dismissSuccess"
                            class="flex-shrink-0 rounded p-1 hover:bg-emerald-200 dark:hover:bg-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400/50"
                            aria-label="Tutup pesan berhasil"
                        >
                            <X
                                :size="18"
                                class="text-emerald-600 dark:text-emerald-400"
                            />
                        </button>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- Error Message -->
            <AnimatePresence>
                <motion.div
                    v-if="(errorMessage || hasValidationErrors) && showErrorMessage"
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -10 }"
                    :transition="{ duration: 0.2 }"
                    class="mb-6 rounded-lg border border-red-500/30 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 shadow-sm"
                    role="alert"
                    aria-live="assertive"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <XCircle
                                :size="20"
                                class="mt-0.5 flex-shrink-0 text-red-600 dark:text-red-400"
                            />
                            <div>
                                <p
                                    class="text-sm font-medium text-red-800 dark:text-red-200"
                                >
                                    {{
                                        errorMessage ||
                                        'Mohon perbaiki kesalahan berikut'
                                    }}
                                </p>
                                <ul
                                    v-if="hasValidationErrors"
                                    class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-300"
                                >
                                    <li
                                        v-for="(error, field) in passwordForm.errors"
                                        :key="field"
                                    >
                                        • {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="dismissError"
                            class="flex-shrink-0 rounded p-1 hover:bg-red-200 dark:hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400/50"
                            aria-label="Tutup pesan kesalahan"
                        >
                            <X
                                :size="18"
                                class="text-red-600 dark:text-red-400"
                            />
                        </button>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- Password Change Form Card -->
            <motion.section
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.1 }"
                class="rounded-lg border border-zinc-200/80 dark:border-white/10 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 p-6"
            >
                <div class="mb-6 flex items-center gap-2">
                    <Lock :size="20" class="text-zinc-700 dark:text-zinc-300" />
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        Perbarui Kata Sandi
                    </h2>
                </div>

                <form @submit.prevent="submitPassword" class="space-y-5">
                    <!-- Current Password -->
                    <div>
                        <Label for="current-password" required>
                            Kata Sandi Saat Ini
                        </Label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <Lock
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <Input
                                id="current-password"
                                v-model="passwordForm.current_password"
                                type="password"
                                placeholder="Masukkan kata sandi saat ini"
                                :disabled="passwordForm.processing"
                                :error="passwordForm.errors.current_password"
                                autocomplete="current-password"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <Label for="new-password" required>Kata Sandi Baru</Label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <KeyRound
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <Input
                                id="new-password"
                                v-model="passwordForm.new_password"
                                type="password"
                                placeholder="Masukkan kata sandi baru"
                                :disabled="passwordForm.processing"
                                :error="passwordForm.errors.new_password"
                                autocomplete="new-password"
                                class="pl-10"
                            />
                        </div>
                        <p
                            class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400"
                        >
                            Minimal 8 karakter
                        </p>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <Label for="confirm-password" required>
                            Konfirmasi Kata Sandi Baru
                        </Label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <KeyRound
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <Input
                                id="confirm-password"
                                v-model="passwordForm.new_password_confirmation"
                                type="password"
                                placeholder="Konfirmasi kata sandi baru Anda"
                                :disabled="passwordForm.processing"
                                autocomplete="new-password"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        variant="primary"
                        :loading="passwordForm.processing"
                        :disabled="passwordForm.processing"
                        full-width
                    >
                        Perbarui Kata Sandi
                    </Button>
                </form>
            </motion.section>

            <!-- Security Tip -->
            <motion.div
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.15 }"
                class="mt-6 rounded-lg border border-cyan-500/30 dark:border-cyan-500/30 bg-cyan-50 dark:bg-cyan-500/10 px-4 py-3"
            >
                <div class="flex gap-3">
                    <Info
                        :size="20"
                        class="flex-shrink-0 text-cyan-600 dark:text-cyan-400"
                    />
                    <div class="flex-1">
                        <p
                            class="text-sm font-medium text-cyan-900 dark:text-cyan-200"
                        >
                            Tips Keamanan
                        </p>
                        <p
                            class="mt-1 text-sm text-cyan-800 dark:text-cyan-300"
                        >
                            Gunakan kata sandi yang kuat setidaknya 8 karakter,
                            termasuk huruf, angka, dan karakter khusus.
                        </p>
                    </div>
                </div>
            </motion.div>
        </div>
    </EmployeeLayout>
</template>
