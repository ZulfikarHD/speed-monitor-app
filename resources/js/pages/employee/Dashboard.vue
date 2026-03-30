<script setup lang="ts">
/**
 * Employee Dashboard - Landing page for employee role.
 *
 * Placeholder dashboard showing user profile and logout functionality.
 * Will be expanded with speedometer and trip tracking in Sprint 3.
 */

import { Head } from '@inertiajs/vue3';

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
                        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                            Employee Dashboard
                        </h1>
                        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
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

                <div
                    class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] p-6 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
                >
                    <div class="mb-4">
                        <h2 class="mb-2 text-lg font-medium">Your Profile</h2>
                        <div class="space-y-2 text-sm">
                            <p>
                                <span class="font-medium">Name:</span>
                                {{ authStore.user?.name }}
                            </p>
                            <p>
                                <span class="font-medium">Email:</span>
                                {{ authStore.user?.email }}
                            </p>
                            <p>
                                <span class="font-medium">Role:</span>
                                <span
                                    class="ml-1 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-200"
                                >
                                    {{ authStore.user?.role }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-900/10"
                    >
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            This is a placeholder dashboard. The speedometer and trip tracking
                            features will be implemented in Sprint 3.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
