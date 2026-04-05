<script setup lang="ts">
/**
 * Profile Page - User profile information management.
 *
 * Allows authenticated users to view and update their profile information
 * (name, email). Password change is handled in a separate page.
 *
 * Features:
 * - Profile information form (name, email)
 * - Role display (read-only badge)
 * - Server-side validation with error messages
 * - Success/error flash messages with icons
 * - Wayfinder type-safe routes
 * - Design system styling (zinc colors with dark mode)
 * - Lucide icons for visual hierarchy
 * - Responsive design (mobile-first)
 * - Modern card layout with proper spacing
 *
 * @example Route: /profile
 */

import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    ChevronRight,
    KeyRound,
    Mail,
    Shield,
    User as UserIcon,
    X,
    XCircle,
} from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onMounted, ref, watch } from 'vue';

import { updateProfile } from '@/actions/App/Http/Controllers/ProfileController';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useAuthStore } from '@/stores/auth';
import type { User } from '@/stores/auth';

// ========================================================================
// Props
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

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
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
    return Object.keys(profileForm.errors).length > 0;
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
 * Submit profile information update.
 */
function submitProfile(): void {
    profileForm.put(updateProfile.url(), {
        preserveScroll: true,
        onSuccess: () => {
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

        <!-- Container -->
        <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <motion.header
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3 }"
                class="mb-8"
            >
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-500/10 dark:bg-cyan-500/15"
                    >
                        <UserIcon
                            :size="24"
                            class="text-cyan-600 dark:text-cyan-400"
                        />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-semibold text-zinc-900 dark:text-white"
                        >
                            Profile Settings
                        </h1>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Manage your account information
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
                            aria-label="Dismiss success message"
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
                                        'Please fix the errors below'
                                    }}
                                </p>
                                <ul
                                    v-if="hasValidationErrors"
                                    class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-300"
                                >
                                    <li
                                        v-for="(error, field) in profileForm.errors"
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
                            aria-label="Dismiss error message"
                        >
                            <X
                                :size="18"
                                class="text-red-600 dark:text-red-400"
                            />
                        </button>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- Profile Information Card -->
            <motion.section
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.1 }"
                class="rounded-lg border border-zinc-200/80 dark:border-white/10 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 p-6"
            >
                <div class="mb-6 flex items-center gap-2">
                    <UserIcon
                        :size="20"
                        class="text-zinc-700 dark:text-zinc-300"
                    />
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        Profile Information
                    </h2>
                </div>

                <form @submit.prevent="submitProfile" class="space-y-5">
                    <!-- Name Field -->
                    <div>
                        <Label for="profile-name" required>Name</Label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <UserIcon
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <Input
                                id="profile-name"
                                v-model="profileForm.name"
                                type="text"
                                placeholder="Enter your full name"
                                :disabled="profileForm.processing"
                                :error="profileForm.errors.name"
                                autocomplete="name"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <Label for="profile-email" required>Email</Label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <Mail
                                    :size="18"
                                    class="text-zinc-400 dark:text-zinc-500"
                                />
                            </div>
                            <Input
                                id="profile-email"
                                v-model="profileForm.email"
                                type="email"
                                placeholder="Enter your email address"
                                :disabled="profileForm.processing"
                                :error="profileForm.errors.email"
                                autocomplete="email"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- Role Field -->
                    <div>
                        <Label>Role</Label>
                        <div
                            class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 px-4 py-3"
                        >
                            <Shield
                                :size="18"
                                class="text-zinc-500 dark:text-zinc-400"
                            />
                            <span
                                class="flex-1 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ user.role }}
                            </span>
                            <span
                                class="rounded-full bg-cyan-100 dark:bg-cyan-500/20 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-300"
                            >
                                Read-only
                            </span>
                        </div>
                        <p
                            class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400"
                        >
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
            </motion.section>

            <!-- Security Settings -->
            <motion.section
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.15 }"
                class="mt-6"
            >
                <Link
                    href="/profile/change-password"
                    class="group flex items-center justify-between rounded-lg border border-zinc-200/80 dark:border-white/10 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 p-5 transition-all duration-200 hover:border-cyan-500/50 dark:hover:border-cyan-400/50 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-100 dark:bg-cyan-500/15 group-hover:bg-cyan-200 dark:group-hover:bg-cyan-500/25 transition-colors"
                        >
                            <KeyRound
                                :size="20"
                                class="text-cyan-600 dark:text-cyan-400"
                            />
                        </div>
                        <div>
                            <p
                                class="font-medium text-zinc-900 dark:text-white"
                            >
                                Change Password
                            </p>
                            <p
                                class="text-sm text-zinc-600 dark:text-zinc-400"
                            >
                                Update your password to keep your account secure
                            </p>
                        </div>
                    </div>
                    <ChevronRight
                        :size="20"
                        class="text-zinc-400 dark:text-zinc-500 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors"
                    />
                </Link>
            </motion.section>
        </div>
    </EmployeeLayout>
</template>
