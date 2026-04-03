<script setup lang="ts">
/**
 * Supervisor Dashboard - Landing page for supervisor role.
 *
 * Placeholder dashboard showing user profile and logout functionality.
 * Will be expanded with monitoring and reporting features in Sprint 6.
 */

import { Head } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import UpdateNotification from '@/components/common/UpdateNotification.vue';
import { useAuth } from '@/composables/useAuth';
import { useServiceWorker } from '@/composables/useServiceWorker';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const { handleLogout, isLoading } = useAuth();

// Service Worker update management
const { hasUpdate, applyUpdate } = useServiceWorker();

const handleUpdate = async (): Promise<void> => {
    try {
        await applyUpdate();
    } catch (error) {
        console.error('[Supervisor Dashboard] Failed to apply update:', error);
    }
};

const handleDismiss = (): void => {
    console.log('[Supervisor Dashboard] Update notification dismissed');
};
</script>

<template>
    <Head title="Supervisor Dashboard" />
    <div class="min-h-screen bg-[#FDFDFC] p-6 dark:bg-[#0a0a0a]">
        <div class="mx-auto max-w-6xl lg:max-w-7xl">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', bounce: 0.3, duration: 0.7 }"
                class="overflow-hidden rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] sm:p-8 lg:p-10 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <motion.div
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1, duration: 0.5 }"
                    class="mb-6 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-3xl font-semibold text-[#1b1b18] sm:text-4xl dark:text-[#EDEDEC]"
                        >
                            Supervisor Dashboard
                        </h1>
                        <p
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Welcome back, {{ authStore.user?.name }}!
                        </p>
                    </div>
                    <motion.button
                        @click="handleLogout"
                        :disabled="isLoading"
                        :whileHover="{ scale: 1.02, y: -1 }"
                        :whilePress="{ scale: 0.98 }"
                        :transition="{ type: 'spring', bounce: 0.4, duration: 0.3 }"
                        class="min-h-[44px] rounded-lg border border-[#e3e3e0] bg-white px-6 py-3 text-sm font-medium text-[#1b1b18] transition-colors hover:bg-[#FDFDFC] disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] dark:hover:bg-[#161615]"
                    >
                        {{ isLoading ? 'Logging out...' : 'Logout' }}
                    </motion.button>
                </motion.div>

                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6, delay: 0.2 }"
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
                                    class="ml-1 inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/20 dark:text-purple-200"
                                >
                                    {{ authStore.user?.role }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.3, duration: 0.5 }"
                        class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-900/50 dark:bg-purple-900/10"
                    >
                        <p class="text-sm text-purple-800 dark:text-purple-200">
                            This is a placeholder dashboard. The monitoring and
                            reporting features will be implemented in Sprint 6.
                        </p>
                    </motion.div>
                </motion.div>
            </motion.div>
        </div>

        <!-- Service Worker Update Notification -->
        <UpdateNotification
            :show="hasUpdate"
            @update="handleUpdate"
            @dismiss="handleDismiss"
        />
    </div>
</template>
