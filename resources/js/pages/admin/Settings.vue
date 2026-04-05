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
 * - Client + server form validation
 * - Real-time preview cards showing current values
 * - Lucide SVG icons
 * - Minimal motion-v entry animations
 * - Success/error feedback with flash messages
 * - Full light/dark mode (extra dark base)
 *
 * UX Principles:
 * - Error Prevention: Warning banner about global impact
 * - Feedback: Flash messages and real-time preview cards
 * - Recognition over Recall: Clear labels with descriptions
 * - Fitts's Law: Large touch targets (>=44px)
 * - Hick's Law: Two clear form actions (save/cancel)
 *
 * @example Route: /admin/settings
 */

import { router, useForm, usePage } from '@inertiajs/vue3';
import {
    Activity,
    CircleCheck,
    Gauge,
    Save,
    Timer,
    TriangleAlert,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';

import { update } from '@/actions/App/Http/Controllers/Admin/SettingsController';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import { dashboard } from '@/routes/supervisor';
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

/** Flash message from server (success notification). */
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
    form.put(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
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
    router.visit(dashboard.url());
}
</script>

<template>
    <SupervisorLayout title="Pengaturan Aplikasi">
        <div class="p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Header Section -->
                <motion.div
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25 }"
                >
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white md:text-3xl">
                        Pengaturan Aplikasi
                    </h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Konfigurasi parameter sistem tracking kecepatan
                    </p>
                </motion.div>

                <!-- Success Message -->
                <motion.div
                    v-if="flashMessage"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.2 }"
                    class="rounded-lg border border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-4"
                >
                    <div class="flex items-start gap-3">
                        <CircleCheck
                            :size="20"
                            class="shrink-0 text-emerald-600 dark:text-emerald-400"
                        />
                        <p class="flex-1 text-sm text-emerald-800 dark:text-emerald-300">
                            {{ flashMessage }}
                        </p>
                    </div>
                </motion.div>

                <!-- Settings Form Card -->
                <motion.form
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.05, duration: 0.25 }"
                    class="rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-6 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 md:p-8"
                    @submit.prevent="handleSubmit"
                >
                    <div class="space-y-8">
                        <!-- Speed Limit Setting -->
                        <div class="space-y-2">
                            <label
                                for="speed_limit"
                                class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white"
                            >
                                <Gauge
                                    :size="16"
                                    class="text-cyan-600 dark:text-cyan-400"
                                />
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
                                        class="w-full rounded-lg border px-4 py-3 pr-16 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                                        :class="{
                                            'border-red-400 bg-red-50 dark:bg-red-950/20 text-red-900 dark:text-red-100 focus:ring-red-500 dark:focus:ring-red-400/50': form.errors.speed_limit,
                                            'border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-900/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-cyan-500 dark:focus:ring-cyan-400/50': !form.errors.speed_limit,
                                        }"
                                    />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        km/h
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Kecepatan maksimal sebelum terdeteksi pelanggaran (1-200 km/h)
                                </p>
                                <p
                                    v-if="form.errors.speed_limit"
                                    class="text-xs text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.speed_limit }}
                                </p>
                            </div>
                        </div>

                        <!-- Auto-Stop Duration Setting -->
                        <div class="space-y-2">
                            <label
                                for="auto_stop_duration"
                                class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white"
                            >
                                <Timer
                                    :size="16"
                                    class="text-blue-600 dark:text-blue-400"
                                />
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
                                        class="w-full rounded-lg border px-4 py-3 pr-20 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                                        :class="{
                                            'border-red-400 bg-red-50 dark:bg-red-950/20 text-red-900 dark:text-red-100 focus:ring-red-500 dark:focus:ring-red-400/50': form.errors.auto_stop_duration,
                                            'border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-900/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-cyan-500 dark:focus:ring-cyan-400/50': !form.errors.auto_stop_duration,
                                        }"
                                    />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        menit
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Waktu inaktivitas sebelum trip otomatis dihentikan (1-120 menit)
                                </p>
                                <p
                                    v-if="form.errors.auto_stop_duration"
                                    class="text-xs text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.auto_stop_duration }}
                                </p>
                            </div>
                        </div>

                        <!-- Speed Log Interval Setting -->
                        <div class="space-y-2">
                            <label
                                for="speed_log_interval"
                                class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white"
                            >
                                <Activity
                                    :size="16"
                                    class="text-emerald-600 dark:text-emerald-400"
                                />
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
                                        class="w-full rounded-lg border px-4 py-3 pr-20 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                                        :class="{
                                            'border-red-400 bg-red-50 dark:bg-red-950/20 text-red-900 dark:text-red-100 focus:ring-red-500 dark:focus:ring-red-400/50': form.errors.speed_log_interval,
                                            'border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-900/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-cyan-500 dark:focus:ring-cyan-400/50': !form.errors.speed_log_interval,
                                        }"
                                    />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        detik
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Frekuensi pencatatan kecepatan selama trip (1-60 detik)
                                </p>
                                <p
                                    v-if="form.errors.speed_log_interval"
                                    class="text-xs text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.speed_log_interval }}
                                </p>
                            </div>
                        </div>

                        <!-- Warning Box -->
                        <div class="rounded-lg border border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-4">
                            <div class="flex gap-3">
                                <TriangleAlert
                                    :size="20"
                                    class="mt-0.5 shrink-0 text-amber-600 dark:text-amber-400"
                                />
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                                        Perhatian
                                    </p>
                                    <p class="text-xs text-amber-700 dark:text-amber-300/80">
                                        Perubahan pengaturan akan langsung berlaku untuk semua karyawan.
                                        Pastikan nilai yang dimasukkan sudah sesuai dengan kebijakan perusahaan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                type="button"
                                class="min-h-[44px] rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-6 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-cyan-400/50 dark:focus:ring-offset-zinc-900"
                                @click="handleCancel"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex min-h-[44px] items-center gap-2 rounded-lg bg-linear-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 transition-all duration-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-cyan-400/50"
                            >
                                <Save
                                    v-if="!form.processing"
                                    :size="18"
                                />
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                            </button>
                        </div>
                    </div>
                </motion.form>

                <!-- Info Cards (Real-time Preview) -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Speed Limit Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.1, duration: 0.25 }"
                        class="rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg border border-cyan-200 dark:border-cyan-500/20 bg-cyan-100 dark:bg-cyan-500/15"
                            >
                                <Gauge
                                    :size="18"
                                    class="text-cyan-600 dark:text-cyan-400"
                                />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Batas Kecepatan
                                </h3>
                                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                    Nilai saat ini:
                                    <span class="font-mono text-cyan-600 dark:text-cyan-400">
                                        {{ form.speed_limit }} km/h
                                    </span>
                                </p>
                            </div>
                        </div>
                    </motion.div>

                    <!-- Auto-Stop Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.15, duration: 0.25 }"
                        class="rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg border border-blue-200 dark:border-blue-500/20 bg-blue-100 dark:bg-blue-500/15"
                            >
                                <Timer
                                    :size="18"
                                    class="text-blue-600 dark:text-blue-400"
                                />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Auto-Stop
                                </h3>
                                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                    Nilai saat ini:
                                    <span class="font-mono text-blue-600 dark:text-blue-400">
                                        {{ autoStopMinutes }} menit
                                    </span>
                                </p>
                            </div>
                        </div>
                    </motion.div>

                    <!-- Logging Interval Card -->
                    <motion.div
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.2, duration: 0.25 }"
                        class="rounded-lg bg-white/95 dark:bg-zinc-800/95 border border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 p-4 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg border border-emerald-200 dark:border-emerald-500/20 bg-emerald-100 dark:bg-emerald-500/15"
                            >
                                <Activity
                                    :size="18"
                                    class="text-emerald-600 dark:text-emerald-400"
                                />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Logging
                                </h3>
                                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                    Nilai saat ini:
                                    <span class="font-mono text-emerald-600 dark:text-emerald-400">
                                        {{ form.speed_log_interval }} detik
                                    </span>
                                </p>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
