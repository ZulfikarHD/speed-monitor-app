<script setup lang="ts">
/**
 * Top Navigation Component
 *
 * Horizontal navigation bar for desktop/tablet with fake glass effect.
 * Follows SpeedMonitor design system with lucide icons and theme-aware styling.
 *
 * Features:
 * - SpeedMonitor logo/branding on left
 * - Horizontal navigation links with lucide icons
 * - Sync status badge on My Trips link
 * - User profile dropdown on right
 * - Active state indicators with gradient accent
 * - Responsive: hidden on mobile (BottomNav takes over)
 * - ARIA accessibility landmarks
 *
 * UX Principles:
 * - Fitts's Law: Adequate click targets for nav items
 * - Jakob's Law: Familiar horizontal top navigation pattern
 * - Law of Proximity: Icon + label grouped per nav item
 */

import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardList,
    Gauge,
    Home,
    Moon,
    Route,
    Settings,
    Sun,
    Trophy,
    Users,
} from '@lucide/vue';
import type { Component } from 'vue';
import { computed } from 'vue';

import UserProfileDropdown from '@/components/navigation/UserProfileDropdown.vue';
import SyncBadge from '@/components/sync/SyncBadge.vue';
import { useActiveRoute } from '@/composables/useActiveRoute';
import { useSyncQueue } from '@/composables/useSyncQueue';
import { useTheme } from '@/composables/useTheme';
import { useAuthStore } from '@/stores/auth';

// ========================================================================
// Navigation Configuration
// ========================================================================

interface NavItem {
    id: string;
    label: string;
    icon: Component;
    href: string;
}

/** Employee navigation items */
const employeeNavItems: NavItem[] = [
    { id: 'dashboard', label: 'Dasbor', icon: Home, href: '/employee/dashboard' },
    { id: 'speedometer', label: 'Speedometer', icon: Gauge, href: '/employee/speedometer' },
    { id: 'trips', label: 'Perjalanan', icon: ClipboardList, href: '/employee/my-trips' },
    { id: 'statistics', label: 'Statistik', icon: BarChart3, href: '/employee/statistics' },
];

/** Superuser/Admin navigation items */
const superuserNavItems: NavItem[] = [
    { id: 'dashboard', label: 'Dasbor', icon: BarChart3, href: '/superuser/dashboard' },
    { id: 'trips', label: 'Semua Trip', icon: Route, href: '/superuser/trips' },
    { id: 'leaderboard', label: 'Peringkat', icon: Trophy, href: '/superuser/leaderboard' },
    { id: 'employees', label: 'Karyawan', icon: Users, href: '/superuser/employees' },
    { id: 'settings', label: 'Pengaturan', icon: Settings, href: '/admin/settings' },
];

// ========================================================================
// Dependencies
// ========================================================================

const authStore = useAuthStore();
const { isActive } = useActiveRoute();
const { openModal } = useSyncQueue();
const { isDark, toggleTheme } = useTheme();

// ========================================================================
// Computed
// ========================================================================

/**
 * Get navigation items based on user role.
 *
 * WHY: Superusers/admins see monitoring tools while employees see trip tracking.
 */
const navItems = computed((): NavItem[] => {
    const role = authStore.role;

    if (role === 'superuser' || role === 'admin') {
        return superuserNavItems;
    }

    return employeeNavItems;
});

/**
 * WHY: Only employees need sync badge — they track trips offline.
 */
const isEmployee = computed(() => authStore.role === 'employee');

// ========================================================================
// Methods
// ========================================================================

/**
 * Handle sync badge click — opens sync queue modal without navigating.
 */
function handleSyncBadgeClick(event: MouseEvent): void {
    event.preventDefault();
    event.stopPropagation();
    openModal();
}
</script>

<template>
    <!-- ======================================================================
        Top Navigation Bar (Desktop/Tablet)
        Fake glass effect — no backdrop-blur, uses semi-transparent bg + borders
    ======================================================================= -->
    <nav
        class="fixed top-0 left-0 right-0 z-50 hidden md:block bg-white/95 dark:bg-zinc-900/98 border-b border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5"
        role="navigation"
        aria-label="Navigasi utama"
    >
        <!-- Grid pattern overlay (32px, theme-aware) -->
        <div
            class="pointer-events-none absolute inset-0 z-0 bg-[linear-gradient(rgba(6,182,212,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(255,255,255,.01)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.01)_1px,transparent_1px)] bg-[size:32px_32px]"
        ></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Left: Logo + Nav Links -->
                <div class="flex items-center gap-8">
                    <!-- SpeedMonitor Logo -->
                    <Link
                        href="/employee/dashboard"
                        class="group flex items-center gap-3 transition-colors duration-200"
                    >
                        <div
                            class="relative flex h-10 w-10 items-center justify-center rounded-lg border border-cyan-200 dark:border-cyan-500/20 bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-500/10 dark:to-blue-600/10 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/5 transition-colors duration-200 group-hover:border-cyan-300 dark:group-hover:border-cyan-400/40"
                        >
                            <Route
                                :size="20"
                                class="text-cyan-600 dark:text-cyan-400"
                            />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-mono text-base font-semibold tracking-wider text-zinc-900 dark:text-zinc-50">
                                SpeedMonitor
                            </span>
                            <span class="text-[10px] font-medium tracking-widest text-zinc-500 dark:text-zinc-500">
                                TRACKING
                            </span>
                        </div>
                    </Link>

                    <!-- Navigation Links -->
                    <div class="flex items-center gap-2">
                        <Link
                            v-for="item in navItems"
                            :key="item.id"
                            :href="item.href"
                            class="group relative flex items-center gap-2.5 rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                            :class="
                                isActive(item.href)
                                    ? 'bg-cyan-100 dark:bg-gradient-to-br dark:from-cyan-500 dark:to-blue-600 text-cyan-700 dark:text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25'
                                    : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-200'
                            "
                            :aria-current="isActive(item.href) ? 'page' : undefined"
                        >
                            <!-- Sync Badge (My Trips only, employees only) -->
                            <SyncBadge
                                v-if="item.id === 'trips' && isEmployee"
                                class="absolute -right-1 -top-1 z-10"
                                size="sm"
                                @click="handleSyncBadgeClick"
                            />

                            <component
                                :is="item.icon"
                                :size="18"
                                class="transition-colors duration-200"
                                :class="
                                    isActive(item.href)
                                        ? 'text-cyan-600 dark:text-white'
                                        : 'text-zinc-500 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                                "
                            />

                            <span class="font-medium">{{ item.label }}</span>

                            <!-- Active indicator line -->
                            <div
                                v-if="isActive(item.href)"
                                class="absolute -bottom-2 left-1/2 h-0.5 w-8 -translate-x-1/2 rounded-full bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-400 dark:to-blue-500"
                            ></div>
                        </Link>
                    </div>
                </div>

                <!-- Right: Theme Toggle + User Profile -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200/80 dark:border-white/10 bg-white/80 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 transition-all duration-200 hover:bg-zinc-100 dark:hover:bg-white/10 hover:text-zinc-900 dark:hover:text-zinc-200"
                        :aria-label="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
                        @click="toggleTheme"
                    >
                        <Moon v-if="!isDark" :size="18" />
                        <Sun v-else :size="18" />
                    </button>
                    <UserProfileDropdown />
                </div>
            </div>
        </div>
    </nav>
</template>
