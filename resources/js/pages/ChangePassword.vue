<script setup lang="ts">
/**
 * Change Password Page - User password management (Server-Side Rendered).
 *
 * Allows authenticated users to change their password with current
 * password verification for security.
 *
 * Features:
 * - Current password verification
 * - New password with confirmation
 * - Server-side validation with error messages
 * - Success flash messages
 * - Form reset on success
 * - Wayfinder type-safe routes
 * - VeloTrack dark theme styling
 * - Responsive design (mobile-first)
 * - Proper container with padding and max-width
 *
 * @example Route: /profile/change-password
 */

import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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

/** Password change form (current, new, confirm) */
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

// ========================================================================
// State
// ========================================================================

/** Visibility flag for success message (for auto-dismiss) */
const showSuccessMessage = ref(false);

/** Visibility flag for error message */
const showErrorMessage = ref(false);

// ========================================================================
// Computed
// ========================================================================

/** Success message from flash data */
const successMessage = computed(() => page.props.flash?.success as string);

/** Error message from flash data */
const errorMessage = computed(() => page.props.flash?.error as string);

/** Check if form has validation errors */
const hasValidationErrors = computed(() => {
    return Object.keys(passwordForm.errors).length > 0;
});

// ========================================================================
// Watchers
// ========================================================================

/**
 * Auto-show and auto-dismiss success message.
 */
watch(successMessage, (newVal) => {
    if (newVal) {
        showSuccessMessage.value = true;
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
});

/**
 * Auto-show and auto-dismiss error message.
 */
watch(errorMessage, (newVal) => {
    if (newVal) {
        showErrorMessage.value = true;
        // Auto-dismiss after 7 seconds (longer for errors)
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 7000);
    }
});

/**
 * Show validation error summary when form has errors.
 */
watch(hasValidationErrors, (newVal) => {
    if (newVal) {
        showErrorMessage.value = true;
    }
});

// ========================================================================
// Lifecycle
// ========================================================================

onMounted(() => {
    // Show messages on mount if they exist
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
            // Clear password fields on success
            passwordForm.reset();
        },
    });
}
</script>

<template>
    <EmployeeLayout>
        <Head title="Change Password" />

        <!-- ======================================================================
            Container with proper padding and max-width
        ======================================================================= -->
        <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- ======================================================================
                Page Header with Back Link
            ======================================================================= -->
            <motion.header
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.4, duration: 0.6 }"
                class="mb-8"
            >
                <!-- Back to Profile Link -->
                <motion.div
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                >
                    <Link
                        href="/profile"
                        class="mb-4 inline-flex items-center gap-2 text-sm text-[#9ca3af] transition-colors hover:text-cyan-400"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        <span>Back to Profile</span>
                    </Link>
                </motion.div>

                <h1
                    class="text-3xl font-bold tracking-tight text-[#e5e7eb] sm:text-4xl"
                >
                    Change Password
                </h1>
                <p class="mt-2 text-[#9ca3af]">
                    Update your password to keep your account secure
                </p>
            </motion.header>

            <!-- ======================================================================
                Success Message Toast
                Displays flash success messages from server with auto-dismiss
            ======================================================================= -->
            <AnimatePresence>
                <motion.div
                    v-if="successMessage && showSuccessMessage"
                    :initial="{ opacity: 0, y: -20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :exit="{ opacity: 0, y: -10, scale: 0.95 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="mb-6 rounded-lg border border-green-500/30 bg-green-950/30 px-4 py-3 text-green-400 shadow-lg"
                    role="alert"
                    aria-live="polite"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span class="font-medium">{{ successMessage }}</span>
                        </div>
                        <button
                            type="button"
                            @click="dismissSuccess"
                            class="flex-shrink-0 rounded p-1 hover:bg-green-500/20 focus:outline-none focus:ring-2 focus:ring-green-500"
                            aria-label="Dismiss success message"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- ======================================================================
                Error Message Toast
                Displays validation errors or server errors
            ======================================================================= -->
            <AnimatePresence>
                <motion.div
                    v-if="(errorMessage || hasValidationErrors) && showErrorMessage"
                    :initial="{ opacity: 0, y: -20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :exit="{ opacity: 0, y: -10, scale: 0.95 }"
                    :transition="{ type: 'spring', bounce: 0.4, duration: 0.5 }"
                    class="mb-6 rounded-lg border border-red-500/30 bg-red-950/30 px-4 py-3 text-red-400 shadow-lg"
                    role="alert"
                    aria-live="assertive"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-2">
                            <svg
                                class="h-5 w-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <div>
                                <p class="font-medium">
                                    {{
                                        errorMessage ||
                                        'Please fix the errors below'
                                    }}
                                </p>
                                <ul
                                    v-if="hasValidationErrors"
                                    class="mt-2 list-inside list-disc space-y-1 text-sm text-red-300"
                                >
                                    <li
                                        v-for="(error, field) in passwordForm.errors"
                                        :key="field"
                                    >
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="dismissError"
                            class="flex-shrink-0 rounded p-2.5 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500"
                            aria-label="Dismiss error message"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- ======================================================================
                Password Change Form
                Verify current password and set new password
            ======================================================================= -->
            <motion.section
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.1 }"
                class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-6 shadow-lg"
            >
                <form @submit.prevent="submitPassword" class="space-y-6">
                    <!-- Current Password Field -->
                    <div>
                        <Label for="current-password" required
                            >Current Password</Label
                        >
                        <Input
                            id="current-password"
                            v-model="passwordForm.current_password"
                            type="password"
                            placeholder="Enter your current password"
                            :disabled="passwordForm.processing"
                            :error="passwordForm.errors.current_password"
                            autocomplete="current-password"
                        />
                    </div>

                    <!-- New Password Field -->
                    <div>
                        <Label for="new-password" required>New Password</Label>
                        <Input
                            id="new-password"
                            v-model="passwordForm.new_password"
                            type="password"
                            placeholder="Enter your new password"
                            :disabled="passwordForm.processing"
                            :error="passwordForm.errors.new_password"
                            autocomplete="new-password"
                        />
                        <p class="mt-1.5 text-xs text-[#6b7280]">
                            Minimum 8 characters
                        </p>
                    </div>

                    <!-- Confirm New Password Field -->
                    <div>
                        <Label for="confirm-password" required
                            >Confirm New Password</Label
                        >
                        <Input
                            id="confirm-password"
                            v-model="passwordForm.new_password_confirmation"
                            type="password"
                            placeholder="Confirm your new password"
                            :disabled="passwordForm.processing"
                            autocomplete="new-password"
                        />
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        variant="primary"
                        :loading="passwordForm.processing"
                        :disabled="passwordForm.processing"
                        full-width
                    >
                        Change Password
                    </Button>
                </form>
            </motion.section>

            <!-- ======================================================================
                Security Note
            ======================================================================= -->
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.2 }"
                class="mt-6 rounded-lg border border-blue-500/30 bg-blue-950/20 px-4 py-3 text-sm text-blue-400"
            >
                <div class="flex gap-3">
                    <svg
                        class="h-5 w-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <div>
                        <p class="font-medium">Security Tip</p>
                        <p class="mt-1 text-blue-300">
                            Use a strong password with at least 8 characters,
                            including letters, numbers, and special characters.
                        </p>
                    </div>
                </div>
            </motion.div>
        </div>
    </EmployeeLayout>
</template>
