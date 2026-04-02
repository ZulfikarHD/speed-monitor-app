<script setup lang="ts">
/**
 * Employee Dashboard - Landing page for employee role.
 *
 * Placeholder dashboard showing user profile and logout functionality.
 * Will be expanded with speedometer and trip tracking in Sprint 3.
 */

import { Head, Link } from '@inertiajs/vue3';

import { useAuth } from '@/composables/useAuth';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const { handleLogout, isLoading } = useAuth();
</script>

<template>
    <Head title="Employee Dashboard" />
    <div class="min-h-screen bg-[#FDFDFC] p-6 dark:bg-[#0a0a0a]">
        <div class="mx-auto max-w-4xl">
            <div
                class="overflow-hidden rounded-lg bg-white p-8 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1
                            class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Employee Dashboard
                        </h1>
                        <p
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Welcome back, {{ authStore.user?.name }}!
                        </p>
                    </div>
                    <button
                        @click="handleLogout"
                        :disabled="isLoading"
                        class="rounded-lg border border-[#e3e3e0] bg-white px-4 py-2 text-sm font-medium text-[#1b1b18] transition-colors hover:bg-[#FDFDFC] disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:hover:bg-[#161615]"
                    >
                        {{ isLoading ? 'Logging out...' : 'Logout' }}
                    </button>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6">
                    <h2
                        class="mb-4 text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Quick Actions
                    </h2>

                    <Link
                        href="/employee/speedometer"
                        class="block rounded-lg border border-[#e3e3e0] bg-gradient-to-br from-blue-50 to-indigo-50 p-6 transition-all hover:scale-[1.02] hover:shadow-md dark:border-[#3E3E3A] dark:from-blue-900/20 dark:to-indigo-900/20"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl dark:bg-blue-600"
                            >
                                🚗
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="mb-1 text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                                >
                                    Start Speedometer
                                </h3>
                                <p
                                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Track your trip speed and distance in
                                    real-time
                                </p>
                            </div>
                            <div>
                                <svg
                                    class="h-6 w-6 text-blue-500 dark:text-blue-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Profile Info -->
                <div
                    class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] p-6 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                >
                    <h2
                        class="mb-4 text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Your Profile
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div
                            class="flex items-center justify-between rounded-lg border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <span
                                class="font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Name
                            </span>
                            <span
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                {{ authStore.user?.name }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <span
                                class="font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Email
                            </span>
                            <span
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                {{ authStore.user?.email }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg border border-[#e3e3e0] bg-white p-3 dark:border-[#3E3E3A] dark:bg-[#161615]"
                        >
                            <span
                                class="font-medium text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Role
                            </span>
                            <span
                                class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-200"
                            >
                                {{ authStore.user?.role }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
