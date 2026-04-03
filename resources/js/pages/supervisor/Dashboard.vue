<script setup lang="ts">
/**
 * Supervisor Dashboard Page
 *
 * Real-time monitoring dashboard for supervisors and admins showing overview
 * statistics, active trips, and top violators with auto-refresh functionality.
 *
 * Features:
 * - Stats cards: active trips, total trips, violations, average speed
 * - Active trips table with live duration updates
 * - Recent violations leaderboard (top 5)
 * - Auto-refresh every 30 seconds
 * - Countdown timer for next refresh
 * - Loading states with skeleton UI
 * - Error handling with retry
 * - motion-v animations (Law of UX principles)
 * - Responsive design (mobile/tablet/desktop)
 */

import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import ActiveTripsTable from '@/components/dashboard/ActiveTripsTable.vue';
import RecentViolationsList from '@/components/dashboard/RecentViolationsList.vue';
import StatCard from '@/components/statistics/StatCard.vue';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import type { DashboardOverview } from '@/types/dashboard';

// ========================================================================
// Local State
// ========================================================================

/** Dashboard data from API */
const dashboardData = ref<DashboardOverview | null>(null);

/** Loading state */
const isLoading = ref(true);

/** Error state */
const error = ref<string | null>(null);

/** Countdown for next refresh (in seconds) */
const countdown = ref(30);

/** Last updated timestamp */
const lastUpdated = ref<Date | null>(null);

// ========================================================================
// Constants
// ========================================================================

/** Refresh interval in milliseconds (30 seconds) */
const REFRESH_INTERVAL = 30_000;

/** Interval IDs for cleanup */
let refreshIntervalId: number | null = null;
let countdownIntervalId: number | null = null;

// ========================================================================
// Lifecycle
// ========================================================================

onMounted(async () => {
    // Initial data fetch
    await fetchDashboardData();

    // Setup auto-refresh every 30 seconds
    refreshIntervalId = window.setInterval(async () => {
        await fetchDashboardData();
        countdown.value = 30; // Reset countdown
    }, REFRESH_INTERVAL);

    // Setup countdown timer (updates every second)
    countdownIntervalId = window.setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        }
    }, 1000);
});

onBeforeUnmount(() => {
    // Cleanup intervals to prevent memory leaks
    if (refreshIntervalId !== null) {
        clearInterval(refreshIntervalId);
    }

    if (countdownIntervalId !== null) {
        clearInterval(countdownIntervalId);
    }
});

// ========================================================================
// Computed
// ========================================================================

/**
 * Get active trips count.
 */
const activeTripsCount = computed(() => {
    return dashboardData.value?.active_trips.length ?? 0;
});

/**
 * Get total trips today.
 */
const totalTripsToday = computed(() => {
    return dashboardData.value?.today_summary.total_trips ?? 0;
});

/**
 * Get violations count today.
 */
const violationsCount = computed(() => {
    return dashboardData.value?.today_summary.violations_count ?? 0;
});

/**
 * Get average speed.
 */
const averageSpeed = computed(() => {
    return dashboardData.value?.average_speed ?? 0;
});

/**
 * Format last updated time.
 */
const lastUpdatedText = computed(() => {
    if (!lastUpdated.value) {
return 'Never';
}
    
    const now = new Date();
    const diff = Math.floor((now.getTime() - lastUpdated.value.getTime()) / 1000);
    
    if (diff < 60) {
return `${diff}s ago`;
}

    if (diff < 3600) {
return `${Math.floor(diff / 60)}m ago`;
}

    return lastUpdated.value.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
});

// ========================================================================
// Methods
// ========================================================================

/**
 * Fetch dashboard data from API.
 *
 * Uses Wayfinder for type-safe route access and handles loading/error states.
 * Data is cached on backend for 5 minutes for optimal performance.
 */
async function fetchDashboardData(): Promise<void> {
    try {
        error.value = null;
        
        // Fetch from API endpoint
        const response = await fetch('/api/dashboard/overview', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        dashboardData.value = data;
        lastUpdated.value = new Date();
        isLoading.value = false;
    } catch (err) {
        console.error('[Dashboard] Failed to fetch data:', err);
        error.value = err instanceof Error ? err.message : 'Failed to load dashboard data';
        isLoading.value = false;
    }
}

/**
 * Manual refresh handler.
 *
 * Allows user to force refresh instead of waiting for auto-refresh.
 * Resets countdown timer after successful refresh.
 */
async function handleManualRefresh(): Promise<void> {
    isLoading.value = true;
    await fetchDashboardData();
    countdown.value = 30; // Reset countdown
}
</script>

<template>
    <SupervisorLayout title="Dashboard Overview">
        <div class="min-h-screen bg-[#0a0c0f] p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- ======================================================================
                    Header Section
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', bounce: 0.3, duration: 0.6 }"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- Title -->
                    <div>
                        <h1 class="text-3xl font-bold text-[#EDEDEC] md:text-4xl">
                            Dashboard Overview
                        </h1>
                        <p class="mt-1 text-sm text-[#A1A09A]">
                            Real-time monitoring of employee trip compliance
                        </p>
                    </div>

                    <!-- Refresh Controls -->
                    <div class="flex items-center gap-3">
                        <!-- Last Updated -->
                        <div class="text-sm text-[#A1A09A]">
                            Updated: {{ lastUpdatedText }}
                        </div>

                        <!-- Auto-refresh Badge -->
                        <motion.div
                            :animate="{ scale: countdown <= 5 ? [1, 1.05, 1] : 1 }"
                            :transition="{ duration: 0.5, repeat: countdown <= 5 ? Infinity : 0 }"
                            class="rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-medium text-cyan-400"
                        >
                            Next in {{ countdown }}s
                        </motion.div>

                        <!-- Manual Refresh Button -->
                        <motion.button
                            @click="handleManualRefresh"
                            :disabled="isLoading"
                            :whileHover="{ scale: 1.05, rotate: 180 }"
                            :whilePress="{ scale: 0.95 }"
                            :transition="{ type: 'spring', bounce: 0.5, duration: 0.4 }"
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#3E3E3A] text-[#EDEDEC] transition-colors hover:bg-[#4a4a46] disabled:cursor-not-allowed disabled:opacity-50"
                            :aria-label="'Refresh dashboard'"
                        >
                            <span class="text-lg" aria-hidden="true">🔄</span>
                        </motion.button>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Error State
                ======================================================================= -->
                <motion.div
                    v-if="error && !isLoading"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.4 }"
                    class="rounded-lg border border-red-500/30 bg-red-500/10 p-6"
                >
                    <div class="flex items-start gap-3">
                        <span class="text-2xl" aria-hidden="true">⚠️</span>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-400">
                                Failed to Load Dashboard Data
                            </h3>
                            <p class="mt-1 text-sm text-red-300">
                                {{ error }}
                            </p>
                            <button
                                @click="handleManualRefresh"
                                class="mt-3 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-medium text-red-400 transition-colors hover:bg-red-500/30"
                            >
                                Try Again
                            </button>
                        </div>
                    </div>
                </motion.div>

                <!-- ======================================================================
                    Stats Cards Grid
                ======================================================================= -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Active Trips Card -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.1, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <StatCard
                            title="Active Trips"
                            :value="activeTripsCount"
                            unit="in progress"
                            icon="🚗"
                            color="blue"
                        />
                    </motion.div>

                    <!-- Total Trips Card -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.2, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <StatCard
                            title="Total Trips Today"
                            :value="totalTripsToday"
                            unit="trips"
                            icon="📊"
                            color="green"
                        />
                    </motion.div>

                    <!-- Violations Card -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.3, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <StatCard
                            title="Violations Today"
                            :value="violationsCount"
                            unit="violations"
                            icon="⚠️"
                            color="red"
                        />
                    </motion.div>

                    <!-- Average Speed Card -->
                    <motion.div
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ delay: 0.4, duration: 0.4, type: 'spring', bounce: 0.3 }"
                    >
                        <StatCard
                            title="Average Speed"
                            :value="averageSpeed"
                            unit="km/h"
                            icon="⚡"
                            color="purple"
                        />
                    </motion.div>
                </div>

                <!-- ======================================================================
                    Active Trips Table
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.5, duration: 0.5, type: 'spring', bounce: 0.3 }"
                >
                    <ActiveTripsTable
                        :trips="dashboardData?.active_trips ?? []"
                        :is-loading="isLoading"
                    />
                </motion.div>

                <!-- ======================================================================
                    Recent Violations List
                ======================================================================= -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.6, duration: 0.5, type: 'spring', bounce: 0.3 }"
                >
                    <RecentViolationsList
                        :violators="dashboardData?.top_violators ?? []"
                        :is-loading="isLoading"
                    />
                </motion.div>
            </div>
        </div>
    </SupervisorLayout>
</template>
