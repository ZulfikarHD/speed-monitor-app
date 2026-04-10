<script setup lang="ts">
/**
 * Bottom Navigation Component
 *
 * Fixed bottom navigation bar for mobile devices with fake glass effect.
 * Follows SafeTrack design system with lucide icons and theme-aware styling.
 *
 * Features:
 * - Employee: 5 slots (Dashboard, Speedometer, Trips, Statistics, More)
 *   - More menu: Profil, Theme Toggle, Logout
 * - Superuser/Admin: 5 slots (Dashboard, Speedometer, Semua Trip, Peringkat, More)
 *   - More menu: Karyawan, Profil, Pengaturan Aplikasi, Theme Toggle, Logout
 * - Dynamic theme toggle (Moon/Sun icon changes with mode)
 * - Logout with destructive styling
 * - Sync status badge on My Trips icon (employee only)
 * - Touch-friendly targets (>=44x44px) per Fitts's Law
 * - Safe area inset aware for notched devices
 * - Active state with gradient accent
 * - ARIA accessibility
 */

import { Link, router } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardList,
    Gauge,
    Home,
    EllipsisVertical,
    LogOut,
    Moon,
    Motorbike,
    Settings,
    Sun,
    Trophy,
    User,
    Users,
    X,
} from '@lucide/vue';
import type { Component } from 'vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';

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
    href?: string;
    action?: () => void;
    isDynamic?: boolean; // For theme toggle that changes icon
}

/** Employee navigation items (mobile) */
const employeeNavItems: NavItem[] = [
    { id: 'dashboard', label: 'Dashboard', icon: Home, href: '/employee/dashboard' },
    { id: 'speedometer', label: 'Speedometer', icon: Gauge, href: '/employee/speedometer' },
    { id: 'trips', label: 'Perjalanan', icon: ClipboardList, href: '/employee/my-trips' },
    { id: 'statistics', label: 'Statistik', icon: BarChart3, href: '/employee/statistics' },
];

/** Employee overflow navigation items (mobile — shown in "More" menu) */
const employeeOverflowNavItems: NavItem[] = [
    { id: 'profile', label: 'Profil', icon: User, href: '/profile' },
    { id: 'theme', label: 'Mode Gelap', icon: Moon, isDynamic: true },
    { id: 'logout', label: 'Keluar', icon: LogOut },
];

/** Superuser/Admin primary navigation items (mobile — shown in bottom bar) */
const superuserPrimaryNavItems: NavItem[] = [
    { id: 'dashboard', label: 'Dashboard', icon: BarChart3, href: '/superuser/dashboard' },
    { id: 'speedometer', label: 'Speedometer', icon: Gauge, href: '/superuser/speedometer' },
    { id: 'trips', label: 'Semua Trip', icon: Motorbike, href: '/superuser/trips' },
    { id: 'leaderboard', label: 'Peringkat', icon: Trophy, href: '/superuser/leaderboard' },
];

/** Superuser/Admin overflow navigation items (mobile — shown in "More" menu) */
const superuserOverflowNavItems: NavItem[] = [
    { id: 'employees', label: 'Karyawan', icon: Users, href: '/superuser/employees' },
    { id: 'profile', label: 'Profil', icon: User, href: '/profile' },
    { id: 'settings', label: 'Pengaturan Aplikasi', icon: Settings, href: '/admin/settings' },
    { id: 'theme', label: 'Mode Gelap', icon: Moon, isDynamic: true },
    { id: 'logout', label: 'Keluar', icon: LogOut },
];

// ========================================================================
// Dependencies
// ========================================================================

const authStore = useAuthStore();
const { isActive } = useActiveRoute();
const { openModal } = useSyncQueue();
const { isDark, toggleTheme } = useTheme();

// ========================================================================
// State
// ========================================================================

const showMoreMenu = ref(false);
const moreMenuRef = ref<HTMLElement | null>(null);

// ========================================================================
// Computed
// ========================================================================

/**
 * Get primary navigation items based on user role.
 */
const navItems = computed((): NavItem[] => {
    const role = authStore.role;

    if (role === 'superuser' || role === 'admin') {
        return superuserPrimaryNavItems;
    }

    return employeeNavItems;
});

/**
 * Get overflow navigation items based on user role.
 * Both employees and superuser/admin have overflow items now (theme, logout, etc).
 */
const overflowItems = computed((): NavItem[] => {
    const role = authStore.role;

    if (role === 'superuser' || role === 'admin') {
        return superuserOverflowNavItems;
    }

    return employeeOverflowNavItems;
});

/**
 * Whether the "More" button should be shown.
 * Always shown now since all roles have overflow items.
 */
const hasOverflow = computed(() => overflowItems.value.length > 0);

/**
 * Whether any overflow item is currently active — highlights the "More" button.
 */
const isOverflowActive = computed(() =>
    overflowItems.value.some((item) => isActive(item.href)),
);

/**
 * Grid columns based on navigation items count.
 * Both employee and superuser/admin have 5 items (4 primary + More button).
 */
const gridCols = computed(() => 'grid-cols-5');

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

/**
 * Toggle the "More" overflow menu.
 */
function toggleMoreMenu(): void {
    showMoreMenu.value = !showMoreMenu.value;
}

/**
 * Close the "More" overflow menu.
 */
function closeMoreMenu(): void {
    showMoreMenu.value = false;
}

/**
 * Close menu when tapping outside.
 *
 * WHY: Using pointerdown instead of click avoids the race condition where
 * the toggle button's click event bubbles up to the document listener
 * on the same tick, immediately closing the menu after opening.
 */
function handleClickOutside(event: PointerEvent): void {
    if (moreMenuRef.value && !moreMenuRef.value.contains(event.target as Node)) {
        closeMoreMenu();
    }
}

/**
 * Handle overflow item click - either navigate or execute action.
 */
function handleOverflowItemClick(item: NavItem): void {
    if (item.id === 'theme') {
        toggleTheme();
        return;
    }

    if (item.id === 'logout') {
        router.post('/logout');
        closeMoreMenu();
        return;
    }

    // For regular navigation items, close menu (Link will handle navigation)
    closeMoreMenu();
}

/**
 * Get the appropriate icon for dynamic items (e.g., theme toggle).
 */
function getItemIcon(item: NavItem): Component {
    if (item.id === 'theme') {
        return isDark.value ? Sun : Moon;
    }
    return item.icon;
}

/**
 * Get the appropriate label for dynamic items (e.g., theme toggle).
 */
function getItemLabel(item: NavItem): string {
    if (item.id === 'theme') {
        return isDark.value ? 'Mode Terang' : 'Mode Gelap';
    }
    return item.label;
}

onMounted(() => {
    document.addEventListener('pointerdown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('pointerdown', handleClickOutside);
});
</script>

<template>
    <!-- ======================================================================
        Bottom Navigation Bar (Mobile)
        Fake glass effect — no backdrop-blur, uses semi-transparent bg + borders
    ======================================================================= -->
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white/95 dark:bg-zinc-900/98 border-t border-zinc-200/80 dark:border-white/10 ring-1 ring-white/20 dark:ring-white/5 shadow-lg shadow-zinc-900/5 dark:shadow-cyan-500/5 pb-safe"
        role="navigation"
        aria-label="Navigasi bawah seluler"
    >
        <!-- Grid pattern overlay (32px, theme-aware) -->
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(6,182,212,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(255,255,255,.01)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.01)_1px,transparent_1px)] bg-[size:32px_32px]"
        ></div>

        <!-- Navigation Items Grid -->
        <div class="relative grid gap-0" :class="gridCols">
            <Link
                v-for="item in navItems"
                :key="item.id"
                :href="item.href"
                class="group relative flex min-h-[60px] flex-col items-center justify-center gap-1.5 px-2 py-2.5 transition-colors duration-200"
                :class="
                    isActive(item.href)
                        ? 'text-cyan-700 dark:text-white'
                        : 'text-zinc-500 dark:text-zinc-500 active:bg-zinc-100 dark:active:bg-white/5'
                "
                :aria-label="item.label"
                :aria-current="isActive(item.href) ? 'page' : undefined"
            >
                <!-- Sync Badge (My Trips only, employees only) -->
                <SyncBadge
                    v-if="item.id === 'trips' && isEmployee"
                    class="absolute right-2 top-1.5 z-10"
                    size="sm"
                    @click="handleSyncBadgeClick"
                />

                <!-- Icon Container -->
                <div class="relative flex items-center justify-center">
                    <!-- Active background pill (dark mode only) -->
                    <div
                        v-if="isActive(item.href)"
                        class="absolute inset-0 -m-1 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 opacity-0 dark:opacity-100"
                    ></div>

                    <component
                        :is="item.icon"
                        :size="22"
                        class="relative transition-colors duration-200"
                        :class="
                            isActive(item.href)
                                ? 'text-cyan-600 dark:text-white'
                                : 'text-zinc-500 dark:text-zinc-500'
                        "
                    />
                </div>

                <!-- Label -->
                <span
                    class="relative text-[10px] font-medium transition-colors duration-200"
                    :class="
                        isActive(item.href)
                            ? 'font-semibold tracking-wide'
                            : 'font-normal tracking-normal'
                    "
                >
                    {{ item.label }}
                </span>

                <!-- Active Indicator -->
                <div
                    v-if="isActive(item.href)"
                    class="absolute top-0 left-1/2 h-0.5 w-10 -translate-x-1/2 rounded-b-full bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-400 dark:to-blue-500 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/50"
                ></div>
            </Link>

            <!-- "More" Button (superuser/admin only) -->
            <div v-if="hasOverflow" ref="moreMenuRef" class="relative">
                <button
                    type="button"
                    class="group relative flex min-h-[60px] w-full flex-col items-center justify-center gap-1.5 px-2 py-2.5 transition-colors duration-200"
                    :class="
                        isOverflowActive || showMoreMenu
                            ? 'text-cyan-700 dark:text-white'
                            : 'text-zinc-500 dark:text-zinc-500 active:bg-zinc-100 dark:active:bg-white/5'
                    "
                    aria-label="Menu lainnya"
                    :aria-expanded="showMoreMenu"
                    @click="toggleMoreMenu"
                >
                    <!-- Icon Container -->
                    <div class="relative flex items-center justify-center">
                        <div
                            v-if="isOverflowActive && !showMoreMenu"
                            class="absolute inset-0 -m-1 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 opacity-0 dark:opacity-100"
                        ></div>

                        <X
                            v-if="showMoreMenu"
                            :size="22"
                            class="relative text-cyan-600 dark:text-white transition-colors duration-200"
                        />
                        <EllipsisVertical
                            v-else
                            :size="22"
                            class="relative transition-colors duration-200"
                            :class="
                                isOverflowActive
                                    ? 'text-cyan-600 dark:text-white'
                                    : 'text-zinc-500 dark:text-zinc-500'
                            "
                        />
                    </div>

                    <!-- Label -->
                    <span
                        class="relative text-[10px] font-medium transition-colors duration-200"
                        :class="
                            isOverflowActive || showMoreMenu
                                ? 'font-semibold tracking-wide'
                                : 'font-normal tracking-normal'
                        "
                    >
                        Lainnya
                    </span>

                    <!-- Active Indicator -->
                    <div
                        v-if="isOverflowActive"
                        class="absolute top-0 left-1/2 h-0.5 w-10 -translate-x-1/2 rounded-b-full bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-400 dark:to-blue-500 shadow-lg shadow-cyan-200 dark:shadow-cyan-500/50"
                    ></div>
                </button>

                <!-- Overflow Menu Popup -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <div
                        v-if="showMoreMenu"
                        class="absolute bottom-full right-0 mb-2 w-48 rounded-xl border border-zinc-200/80 dark:border-white/10 bg-white/95 dark:bg-zinc-900/98 shadow-xl shadow-zinc-900/10 dark:shadow-cyan-500/10 ring-1 ring-white/20 dark:ring-white/5 overflow-hidden"
                    >
                        <div class="py-1.5">
                            <!-- Navigation Items -->
                            <Link
                                v-for="item in overflowItems.filter((i) => i.href)"
                                :key="item.id"
                                :href="item.href!"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-medium transition-colors duration-150"
                                :class="
                                    isActive(item.href!)
                                        ? 'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400'
                                        : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-200'
                                "
                                :aria-current="isActive(item.href!) ? 'page' : undefined"
                                @click="handleOverflowItemClick(item)"
                            >
                                <component
                                    :is="getItemIcon(item)"
                                    :size="18"
                                    class="transition-colors duration-200"
                                    :class="
                                        isActive(item.href!)
                                            ? 'text-cyan-600 dark:text-cyan-400'
                                            : 'text-zinc-500 dark:text-zinc-500'
                                    "
                                />
                                <span>{{ getItemLabel(item) }}</span>
                            </Link>

                            <!-- Action Items (Theme, Logout) -->
                            <button
                                v-for="item in overflowItems.filter((i) => !i.href)"
                                :key="item.id"
                                type="button"
                                class="flex w-full items-center gap-3 px-4 py-3 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-white/5 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors duration-150"
                                :class="{
                                    'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-700 dark:hover:text-red-300':
                                        item.id === 'logout',
                                }"
                                @click="handleOverflowItemClick(item)"
                            >
                                <component
                                    :is="getItemIcon(item)"
                                    :size="18"
                                    class="text-zinc-500 dark:text-zinc-500 transition-colors duration-200"
                                    :class="{
                                        'text-red-500 dark:text-red-400': item.id === 'logout',
                                    }"
                                />
                                <span>{{ getItemLabel(item) }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </nav>
</template>

<style scoped>
/**
 * Safe area inset for devices with notches/home indicators.
 */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
