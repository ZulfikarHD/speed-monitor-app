<script setup lang="ts">
/**
 * Settings Page (Admin Only)
 *
 * Configure application-wide parameters for speed tracking.
 * Changes affect all employees' trip tracking behavior immediately.
 *
 * Settings:
 * - Speed Limit: Maximum allowed speed before violation (km/h)
 * - Auto-Stop Duration: Inactivity duration before auto-ending trip (minutes)
 * - Speed Log Interval: Frequency of speed logging (seconds)
 *
 * Features:
 * - Admin-only access (policy enforced)
 * - Form validation (client + server)
 * - Real-time preview cards
 * - Motion-v animations following Law of UX
 * - Success/error feedback
 * - Responsive design
 *
 * @example Route: /admin/settings
 */

import { router, useForm, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';

import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import { useSettingsStore } from '@/stores/settings';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Current settings from database */
    settings: {
        speed_limit?: string;
        auto_stop_duration?: string;
        speed_log_interval?: string;
    };
}

const props = defineProps<Props>();

// ========================================================================
// Dependencies
// ========================================================================

const page = usePage();
const settingsStore = useSettingsStore();

// ========================================================================
// Form State
// ========================================================================

/**
 * Settings form with Inertia useForm.
 *
 * WHY: useForm provides built-in loading states, error handling,
 * and optimistic updates for better UX.
 */
const form = useForm({
    speed_limit: Number(props.settings.speed_limit) || 60,
    auto_stop_duration: Number(props.settings.auto_stop_duration) || 1800,
    speed_log_interval: Number(props.settings.speed_log_interval) || 5,
});

// ========================================================================
// Computed
// ========================================================================

/**
 * Convert auto_stop_duration from seconds to minutes for better UX.
 *
 * WHY: Backend stores seconds (1800 = 30 minutes) but users understand
 * minutes better. Conversion happens transparently.
 */
const autoStopMinutes = computed({
    get: () => Math.floor(form.auto_stop_duration / 60),
    set: (value: number) => {
        form.auto_stop_duration = value * 60;
    },
});

/**
 * Flash message from server (success notification).
 */
const flashMessage = computed(() => page.props.flash?.success as string | undefined);

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle form submission.
 *
 * Submits settings update to backend web route and updates global settings store
 * on success so components like Speedometer immediately use new limits.
 */
function handleSubmit(): void {
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Update global settings store with new values
            // WHY: Ensures all components use new settings without page reload
            settingsStore.setSettings({
                speed_limit: form.speed_limit,
                auto_stop_duration: form.auto_stop_duration,
                speed_log_interval: form.speed_log_interval,
            });
        },
    });
}

/**
 * Handle cancel button click.
 *
 * Returns to supervisor dashboard without saving changes.
 */
function handleCancel(): void {
    router.visit(route('supervisor.dashboard'));
}
</script>

<template>
    <SupervisorLayout title="Pengaturan Aplikasi">
        <div class="min-h-screen bg-[#0a0c0f] p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- ======================================================================
                    Header Section
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6 }"
                >
                    <h1 class="text-3xl font-bold text-[#EDEDEC] md:text-4xl">
                        Pengaturan Aplikasi
                    </h1>
                    <p class="mt-1 text-sm text-[#A1A09A]">
                        Konfigurasi parameter sistem tracking kecepatan
                    </p>
                </motion.div>

                <!-- ======================================================================
                    Success Message
                ======================================================================= -->
                <motion.div
                    v-if="flashMessage"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :exit="{ opacity: 0, scale: 0.95 }"
                    :transition="{ duration: 0.3 }"
                    class="rounded-lg border border-green-500/30 bg-green-500/10 p-4"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="text-xl"
                            aria-hidden="true"
                        >✅</span>
                        <p class="flex-1 text-sm text-green-300">
                            {{ flashMessage }}
                        </p>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Settings Form Card
                ======================================================================= -->
                <motion.form
                    @submit.prevent="handleSubmit"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1, duration: 0.4 }"
                    class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-6 md:p-8"
                >
                    <div class="space-y-8">
                        <!-- Speed Limit Setting -->
                        <div class="space-y-2">
                            <label
                                for="speed_limit"
                                class="block text-sm font-semibold text-[#e5e7eb]"
                            >
                                Batas Kecepatan
                            </label>
                            <div class="space-y-1">
                                <div class="relative">
                                    <input
                                        id="speed_limit"
                                        v-model.number="form.speed_limit"
                                        type="number"
                                        min="1"
                                        max="200"
                                        step="1"
                                        class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-3 pr-16 text-[#e5e7eb] transition-all focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                                        :class="{ 'border-red-500': form.errors.speed_limit }"
                                    />
                                    <span
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-[#A1A09A]"
                                    >
                                        km/h
                                    </span>
                                </div>
                                <p class="text-xs text-[#6b7280]">
                                    Kecepatan maksimal sebelum terdeteksi pelanggaran (1-200 km/h)
                                </p>
                                <p
                                    v-if="form.errors.speed_limit"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.speed_limit }}
                                </p>
                            </div>
                        </div>

                        <!-- Auto-Stop Duration Setting -->
                        <div class="space-y-2">
                            <label
                                for="auto_stop_duration"
                                class="block text-sm font-semibold text-[#e5e7eb]"
                            >
                                Durasi Auto-Stop
                            </label>
                            <div class="space-y-1">
                                <div class="relative">
                                    <input
                                        id="auto_stop_duration"
                                        v-model.number="autoStopMinutes"
                                        type="number"
                                        min="1"
                                        max="120"
                                        step="1"
                                        class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-3 pr-20 text-[#e5e7eb] transition-all focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                                        :class="{ 'border-red-500': form.errors.auto_stop_duration }"
                                    />
                                    <span
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-[#A1A09A]"
                                    >
                                        menit
                                    </span>
                                </div>
                                <p class="text-xs text-[#6b7280]">
                                    Waktu inaktivitas sebelum trip otomatis dihentikan (1-120 menit)
                                </p>
                                <p
                                    v-if="form.errors.auto_stop_duration"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.auto_stop_duration }}
                                </p>
                            </div>
                        </div>

                        <!-- Speed Log Interval Setting -->
                        <div class="space-y-2">
                            <label
                                for="speed_log_interval"
                                class="block text-sm font-semibold text-[#e5e7eb]"
                            >
                                Interval Logging
                            </label>
                            <div class="space-y-1">
                                <div class="relative">
                                    <input
                                        id="speed_log_interval"
                                        v-model.number="form.speed_log_interval"
                                        type="number"
                                        min="1"
                                        max="60"
                                        step="1"
                                        class="w-full rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-4 py-3 pr-20 text-[#e5e7eb] transition-all focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-[#1a1d23]"
                                        :class="{ 'border-red-500': form.errors.speed_log_interval }"
                                    />
                                    <span
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-[#A1A09A]"
                                    >
                                        detik
                                    </span>
                                </div>
                                <p class="text-xs text-[#6b7280]">
                                    Frekuensi pencatatan kecepatan selama trip (1-60 detik)
                                </p>
                                <p
                                    v-if="form.errors.speed_log_interval"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.speed_log_interval }}
                                </p>
                            </div>
                        </div>

                        <!-- Warning Box -->
                        <div class="rounded-lg border border-yellow-500/30 bg-yellow-500/10 p-4">
                            <div class="flex gap-3">
                                <span
                                    class="text-xl"
                                    aria-hidden="true"
                                >⚠️</span>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium text-yellow-300">
                                        Perhatian
                                    </p>
                                    <p class="text-xs text-yellow-200/80">
                                        Perubahan pengaturan akan langsung berlaku untuk semua karyawan.
                                        Pastikan nilai yang dimasukkan sudah sesuai dengan kebijakan perusahaan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3 pt-4">
                            <motion.button
                                type="button"
                                @click="handleCancel"
                                :whileHover="{ scale: 1.02 }"
                                :whilePress="{ scale: 0.98 }"
                                :transition="{ type: 'spring', stiffness: 400 }"
                                class="rounded-lg border border-[#3E3E3A] bg-[#0a0c0f] px-6 py-3 text-sm font-semibold text-[#e5e7eb] transition-colors hover:bg-[#1a1d23]"
                            >
                                Batal
                            </motion.button>
                            <motion.button
                                type="submit"
                                :disabled="form.processing"
                                :whileHover="{ scale: 1.02 }"
                                :whilePress="{ scale: 0.98 }"
                                :transition="{ type: 'spring', stiffness: 400 }"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-3 text-sm font-semibold text-white transition-all hover:from-cyan-600 hover:to-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span
                                    v-if="form.processing"
                                    class="text-lg"
                                    aria-hidden="true"
                                >⏳</span>
                                <span
                                    v-else
                                    class="text-lg"
                                    aria-hidden="true"
                                >💾</span>
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                            </motion.button>
                        </div>
                    </div>
                </motion.form>

                <!-- ======================================================================
                    Info Cards (Real-time Preview)
                ======================================================================= -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Speed Limit Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.2, duration: 0.4 }"
                        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="text-2xl"
                                aria-hidden="true"
                            >🚦</span>
                            <div>
                                <h3 class="text-sm font-semibold text-[#e5e7eb]">
                                    Batas Kecepatan
                                </h3>
                                <p class="mt-1 text-xs text-[#6b7280]">
                                    Nilai saat ini: <span class="font-mono text-cyan-400">{{ form.speed_limit }} km/h</span>
                                </p>
                            </div>
                        </div>
                    </motion.div>

                    <!-- Auto-Stop Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.25, duration: 0.4 }"
                        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="text-2xl"
                                aria-hidden="true"
                            >⏱️</span>
                            <div>
                                <h3 class="text-sm font-semibold text-[#e5e7eb]">
                                    Auto-Stop
                                </h3>
                                <p class="mt-1 text-xs text-[#6b7280]">
                                    Nilai saat ini: <span class="font-mono text-cyan-400">{{ autoStopMinutes }} menit</span>
                                </p>
                            </div>
                        </div>
                    </motion.div>

                    <!-- Logging Interval Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.3, duration: 0.4 }"
                        class="rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-4"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="text-2xl"
                                aria-hidden="true"
                            >📝</span>
                            <div>
                                <h3 class="text-sm font-semibold text-[#e5e7eb]">
                                    Logging
                                </h3>
                                <p class="mt-1 text-xs text-[#6b7280]">
                                    Nilai saat ini: <span class="font-mono text-cyan-400">{{ form.speed_log_interval }} detik</span>
                                </p>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
