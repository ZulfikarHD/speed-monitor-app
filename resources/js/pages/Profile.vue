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
    Briefcase,
    Building2,
    CheckCircle,
    ChevronRight,
    Hash,
    KeyRound,
    Layers,
    Loader2,
    LogOut,
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
import { useAuth } from '@/composables/useAuth';
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
const { handleLogout, isLoading: isLoggingOut } = useAuth();

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
        <Head title="Pengaturan Profil" />

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
                            Pengaturan Profil
                        </h1>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Kelola informasi akun Anda
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
                        Informasi Profil
                    </h2>
                </div>

                <form @submit.prevent="submitProfile" class="space-y-5">
                    <!-- Name Field -->
                    <div>
                        <Label for="profile-name" required>Nama</Label>
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
                                placeholder="Masukkan nama lengkap"
                                :disabled="profileForm.processing"
                                :error="profileForm.errors.name"
                                autocomplete="name"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <Label for="profile-email" required>Alamat Email</Label>
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
                                placeholder="Masukkan alamat email"
                                :disabled="profileForm.processing"
                                :error="profileForm.errors.email"
                                autocomplete="email"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- Role Field -->
                    <div>
                        <Label>Peran</Label>
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
                                Hanya-baca
                            </span>
                        </div>
                        <p
                            class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400"
                        >
                            Hubungi administrator untuk mengubah peran Anda
                        </p>
                    </div>

                    <!-- NPK Field -->
                    <div>
                        <Label>NPK</Label>
                        <div
                            class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 px-4 py-3"
                        >
                            <Hash
                                :size="18"
                                class="text-zinc-500 dark:text-zinc-400"
                            />
                            <span
                                v-if="user.npk"
                                class="flex-1 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ user.npk }}
                            </span>
                            <span
                                v-else
                                class="flex-1 text-sm text-zinc-400 dark:text-zinc-500 italic"
                            >
                                Belum diisi
                            </span>
                            <span
                                class="rounded-full bg-cyan-100 dark:bg-cyan-500/20 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-300"
                            >
                                Hanya-baca
                            </span>
                        </div>
                    </div>

                    <!-- Divisi Field -->
                    <div>
                        <Label>Divisi</Label>
                        <div
                            class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 px-4 py-3"
                        >
                            <Building2
                                :size="18"
                                class="text-zinc-500 dark:text-zinc-400"
                            />
                            <span
                                v-if="user.divisi"
                                class="flex-1 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ user.divisi }}
                            </span>
                            <span
                                v-else
                                class="flex-1 text-sm text-zinc-400 dark:text-zinc-500 italic"
                            >
                                Belum diisi
                            </span>
                            <span
                                class="rounded-full bg-cyan-100 dark:bg-cyan-500/20 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-300"
                            >
                                Hanya-baca
                            </span>
                        </div>
                    </div>

                    <!-- Departement Field -->
                    <div>
                        <Label>Departement</Label>
                        <div
                            class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 px-4 py-3"
                        >
                            <Briefcase
                                :size="18"
                                class="text-zinc-500 dark:text-zinc-400"
                            />
                            <span
                                v-if="user.departement"
                                class="flex-1 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ user.departement }}
                            </span>
                            <span
                                v-else
                                class="flex-1 text-sm text-zinc-400 dark:text-zinc-500 italic"
                            >
                                Belum diisi
                            </span>
                            <span
                                class="rounded-full bg-cyan-100 dark:bg-cyan-500/20 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-300"
                            >
                                Hanya-baca
                            </span>
                        </div>
                    </div>

                    <!-- Section Field -->
                    <div>
                        <Label>Section</Label>
                        <div
                            class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 px-4 py-3"
                        >
                            <Layers
                                :size="18"
                                class="text-zinc-500 dark:text-zinc-400"
                            />
                            <span
                                v-if="user.section"
                                class="flex-1 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ user.section }}
                            </span>
                            <span
                                v-else
                                class="flex-1 text-sm text-zinc-400 dark:text-zinc-500 italic"
                            >
                                Belum diisi
                            </span>
                            <span
                                class="rounded-full bg-cyan-100 dark:bg-cyan-500/20 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-300"
                            >
                                Hanya-baca
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        variant="primary"
                        :loading="profileForm.processing"
                        :disabled="profileForm.processing"
                        full-width
                    >
                        Simpan Perubahan
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
                                Ubah Kata Sandi
                            </p>
                            <p
                                class="text-sm text-zinc-600 dark:text-zinc-400"
                            >
                                Perbarui kata sandi untuk menjaga keamanan akun
                            </p>
                        </div>
                    </div>
                    <ChevronRight
                        :size="20"
                        class="text-zinc-400 dark:text-zinc-500 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors"
                    />
                </Link>
            </motion.section>

            <!-- Logout Section -->
            <motion.section
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.2 }"
                class="mt-6"
            >
                <button
                    @click="handleLogout"
                    type="button"
                    :disabled="isLoggingOut"
                    class="group w-full flex items-center justify-between rounded-lg border border-zinc-200/80 dark:border-white/10 bg-white/95 dark:bg-zinc-800/95 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 p-5 transition-all duration-200 hover:border-red-500/50 dark:hover:border-red-400/50 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-500/15 group-hover:bg-red-200 dark:group-hover:bg-red-500/25 transition-colors"
                        >
                            <Loader2
                                v-if="isLoggingOut"
                                :size="20"
                                class="animate-spin text-red-600 dark:text-red-400"
                            />
                            <LogOut
                                v-else
                                :size="20"
                                class="text-red-600 dark:text-red-400"
                            />
                        </div>
                        <div class="text-left">
                            <p
                                class="font-medium text-zinc-900 dark:text-white"
                            >
                                {{ isLoggingOut ? 'Keluar...' : 'Keluar' }}
                            </p>
                            <p
                                class="text-sm text-zinc-600 dark:text-zinc-400"
                            >
                                Keluar dari akun Anda
                            </p>
                        </div>
                    </div>
                    <ChevronRight
                        :size="20"
                        class="text-zinc-400 dark:text-zinc-500 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors"
                    />
                </button>
            </motion.section>
        </div>
    </EmployeeLayout>
</template>
