<script setup lang="ts">
/**
 * Employee Dashboard - Landing page for employee role.
 *
 * Main dashboard showing quick actions and profile information.
 * Uses EmployeeLayout for consistent navigation across all employee pages.
 */

import { Link } from '@inertiajs/vue3';

import EmployeeLayout from '@/layouts/EmployeeLayout.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
    <EmployeeLayout title="Dashboard">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <h1
                    class="text-3xl font-bold text-[#e5e7eb]"
                    style="font-family: 'Bebas Neue', sans-serif"
                >
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-[#9ca3af]">
                    Welcome back, {{ authStore.user?.name }}!
                </p>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Speedometer Card -->
                <Link
                    href="/employee/speedometer"
                    class="group block rounded-lg border border-[#3E3E3A] bg-gradient-to-br from-blue-500/10 to-indigo-500/10 p-6 transition-all hover:scale-[1.02] hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-500/20"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-2xl shadow-lg"
                        >
                            🚗
                        </div>
                        <div class="flex-1">
                            <h3
                                class="mb-1 text-lg font-semibold text-[#e5e7eb] group-hover:text-blue-400"
                            >
                                Start Speedometer
                            </h3>
                            <p class="text-sm text-[#9ca3af]">
                                Track your trip speed and distance in real-time
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Trip History Card -->
                <Link
                    href="/employee/my-trips"
                    class="group block rounded-lg border border-[#3E3E3A] bg-gradient-to-br from-cyan-500/10 to-teal-500/10 p-6 transition-all hover:scale-[1.02] hover:border-cyan-500/50 hover:shadow-lg hover:shadow-cyan-500/20"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 text-2xl shadow-lg"
                        >
                            📋
                        </div>
                        <div class="flex-1">
                            <h3
                                class="mb-1 text-lg font-semibold text-[#e5e7eb] group-hover:text-cyan-400"
                            >
                                Trip History
                            </h3>
                            <p class="text-sm text-[#9ca3af]">
                                View your past trips and detailed statistics
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Statistics Card -->
                <Link
                    href="/employee/statistics"
                    class="group block rounded-lg border border-[#3E3E3A] bg-gradient-to-br from-purple-500/10 to-pink-500/10 p-6 transition-all hover:scale-[1.02] hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/20"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-2xl shadow-lg"
                        >
                            📊
                        </div>
                        <div class="flex-1">
                            <h3
                                class="mb-1 text-lg font-semibold text-[#e5e7eb] group-hover:text-purple-400"
                            >
                                My Statistics
                            </h3>
                            <p class="text-sm text-[#9ca3af]">
                                Track your performance and driving metrics
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Profile Info Card -->
            <div class="mt-8 rounded-lg border border-[#3E3E3A] bg-[#1a1d23] p-6">
                <h2 class="mb-4 text-lg font-semibold text-[#e5e7eb]">
                    Your Profile
                </h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-[#0a0c0f] p-4">
                        <p class="text-xs text-[#9ca3af]">Name</p>
                        <p class="mt-1 text-sm font-medium text-[#e5e7eb]">
                            {{ authStore.user?.name }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-[#0a0c0f] p-4">
                        <p class="text-xs text-[#9ca3af]">Email</p>
                        <p class="mt-1 text-sm font-medium text-[#e5e7eb]">
                            {{ authStore.user?.email }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-[#0a0c0f] p-4">
                        <p class="text-xs text-[#9ca3af]">Role</p>
                        <p class="mt-1">
                            <span
                                class="inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-xs font-medium text-blue-400"
                            >
                                {{ authStore.user?.role }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>
