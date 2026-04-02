<script setup lang="ts">
/**
 * Profile Page - User profile information management (Server-Side Rendered).
 *
 * Allows authenticated users to view and update their profile information
 * (name, email). Password change is handled in a separate page.
 *
 * Features:
 * - Profile information form (name, email)
 * - Role display (read-only)
 * - Server-side validation with error messages
 * - Success flash messages
 * - Wayfinder type-safe routes
 * - VeloTrack dark theme styling
 * - Responsive design (mobile-first)
 * - Proper container with padding and max-width
 *
 * @example Route: /profile
 */

import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

import { updateProfile } from '@/actions/App/Http/Controllers/ProfileController';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useAuthStore } from '@/stores/auth';
import type { User } from '@/stores/auth';

// Import Wayfinder routes

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Current user data */
    user: User;
}

const props = defineProps<Props>();

// ========================================================================
// Dependencies
// ========================================================================

const authStore = useAuthStore();
const page = usePage();

// ========================================================================
// Forms
// ========================================================================

/** Profile information form (name, email) */
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
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
    return Object.keys(profileForm.errors).length > 0;
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
 * Submit profile information update.
 */
function submitProfile(): void {
    profileForm.put(updateProfile.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Update auth store with fresh user data
            const authUser = page.props.auth?.user as User;

            if (authUser) {
                authStore.setUser(authUser);
            }
        },
    });
}
</script>

<template>
    <EmployeeLayout>
        <Head title="Profile Settings" />

        <!-- ======================================================================
            Container with proper padding and max-width
        ======================================================================= -->
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- ======================================================================
                Page Header
                Title and description for profile settings page
            ======================================================================= -->
            <header class="mb-8">
                <h1
                    class="text-3xl font-bold tracking-tight text-[#e5e7eb] sm:text-4xl"
                >
                    Profile Settings
                </h1>
                <p class="mt-2 text-[#9ca3af]">
                    Manage your account information
                </p>
            </header>

            <!-- ======================================================================
                Success Message Toast
                Displays flash success messages from server with auto-dismiss
            ======================================================================= -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-2 opacity-0"
            >
                <div
                    v-if="successMessage && showSuccessMessage"
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
                </div>
            </transition>

            <!-- ======================================================================
                Error Message Toast
                Displays validation errors or server errors
            ======================================================================= -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-2 opacity-0"
            >
                <div
                    v-if="(errorMessage || hasValidationErrors) && showErrorMessage"
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
                                        v-for="(error, field) in profileForm.errors"
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
                            class="flex-shrink-0 rounded p-1 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500"
                            aria-label="Dismiss error message"
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
                </div>
            </transition>

            <!-- ======================================================================
                Profile Information Form
                Edit name and email (role is read-only)
            ======================================================================= -->
            <section
                class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-6 shadow-lg"
            >
                <h2 class="mb-6 text-xl font-semibold text-[#e5e7eb]">
                    Profile Information
                </h2>

                <form @submit.prevent="submitProfile" class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <Label for="profile-name" required>Name</Label>
                        <Input
                            id="profile-name"
                            v-model="profileForm.name"
                            type="text"
                            placeholder="Enter your full name"
                            :disabled="profileForm.processing"
                            :error="profileForm.errors.name"
                            autocomplete="name"
                        />
                    </div>

                    <!-- Email Field -->
                    <div>
                        <Label for="profile-email" required>Email</Label>
                        <Input
                            id="profile-email"
                            v-model="profileForm.email"
                            type="email"
                            placeholder="Enter your email address"
                            :disabled="profileForm.processing"
                            :error="profileForm.errors.email"
                            autocomplete="email"
                        />
                    </div>

                    <!-- Role Field (Read-Only) -->
                    <div>
                        <Label>Role</Label>
                        <div
                            class="rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-3 text-sm text-[#9ca3af]"
                        >
                            {{ user.role }}
                        </div>
                        <p class="mt-1.5 text-xs text-[#6b7280]">
                            Contact administrator to change your role
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        variant="primary"
                        :loading="profileForm.processing"
                        :disabled="profileForm.processing"
                        full-width
                    >
                        Save Changes
                    </Button>
                </form>
            </section>

            <!-- ======================================================================
                Security Settings Link
            ======================================================================= -->
            <section class="mt-8">
                <h3 class="mb-4 text-lg font-semibold text-[#e5e7eb]">
                    Security Settings
                </h3>
                <Link
                    href="/profile/change-password"
                    class="flex items-center justify-between rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4 transition-colors hover:border-cyan-500/50 hover:bg-[#1f2329]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-500/10"
                        >
                            <svg
                                class="h-5 w-5 text-cyan-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-[#e5e7eb]">
                                Change Password
                            </p>
                            <p class="text-sm text-[#9ca3af]">
                                Update your password to keep your account secure
                            </p>
                        </div>
                    </div>
                    <svg
                        class="h-5 w-5 text-[#6b7280]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </Link>
            </section>
        </div>
    </EmployeeLayout>
</template>
