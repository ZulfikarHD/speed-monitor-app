<script setup lang="ts">
/**
 * Employee Management Page (Supervisor)
 *
 * Comprehensive user management page for supervisors with CRUD operations,
 * search functionality, role/status filtering, and pagination.
 *
 * Features:
 * - User listing with search by name/email
 * - Filter by role (employee/supervisor/admin) and status (active/inactive)
 * - Add new user with modal form
 * - Edit user details with modal form
 * - Deactivate user with confirmation modal
 * - Responsive design (table on desktop, cards on mobile)
 * - motion-v animations following Law of UX
 * - Pagination (20 per page)
 * - Empty states
 * - Loading states
 * - Success/error flash messages
 *
 * @example Route: /supervisor/employees
 */

import { router, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

import { index } from '@/actions/App/Http/Controllers/Supervisor/EmployeesController';
import IconBan from '@/components/icons/IconBan.vue';
import IconCheck from '@/components/icons/IconCheck.vue';
import IconEdit from '@/components/icons/IconEdit.vue';
import IconPlus from '@/components/icons/IconPlus.vue';
import ConfirmDeactivateModal from '@/components/supervisor/ConfirmDeactivateModal.vue';
import UserFormModal from '@/components/supervisor/UserFormModal.vue';
import Pagination from '@/components/trips/Pagination.vue';
import SupervisorLayout from '@/layouts/SupervisorLayout.vue';
import { useAuthStore } from '@/stores/auth';
import type { User, UserListFilters } from '@/types/auth';

// ========================================================================
// Props (Server-Side Data)
// ========================================================================

interface Props {
    /** Users for current page */
    users: User[];

    /** Pagination metadata */
    meta: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };

    /** Current filter values */
    filters: UserListFilters;
}

const props = defineProps<Props>();

// ========================================================================
// Stores & Page Data
// ========================================================================

const authStore = useAuthStore();
const page = usePage();

// ========================================================================
// Local State
// ========================================================================

/** Show add user modal */
const showAddModal = ref(false);

/** Show edit user modal */
const showEditModal = ref(false);

/** Show deactivate confirmation modal */
const showDeactivateModal = ref(false);

/** User being edited */
const editingUser = ref<User | null>(null);

/** User being deactivated */
const deactivatingUser = ref<User | null>(null);

/** Local filter state (synced with props) */
const localFilters = ref<UserListFilters>({
    search: props.filters.search,
    role: props.filters.role,
    status: props.filters.status,
});

/** Debounce timer for search input */
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

// ========================================================================
// Computed
// ========================================================================

/**
 * Check if there are any active filters.
 */
const hasActiveFilters = computed(() => {
    return !!(localFilters.value.search || localFilters.value.role || localFilters.value.status);
});

/**
 * Flash message from server.
 */
const flashMessage = computed(() => {
    return page.props.flash?.success as string | undefined;
});

// ========================================================================
// Watchers
// ========================================================================

/**
 * Watch for search input changes and debounce.
 */
watch(
    () => localFilters.value.search,
    () => {
        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        searchDebounceTimer = setTimeout(() => {
            applyFilters();
        }, 500);
    }
);

/**
 * Watch for role filter changes.
 */
watch(
    () => localFilters.value.role,
    () => {
        applyFilters();
    }
);

/**
 * Watch for status filter changes.
 */
watch(
    () => localFilters.value.status,
    () => {
        applyFilters();
    }
);

// ========================================================================
// Methods
// ========================================================================

/**
 * Apply filters by navigating with query parameters.
 *
 * WHY: Uses Wayfinder for type-safe routing with query parameter support.
 * Query parameters are properly encoded and preserve filter state across navigation.
 */
function applyFilters(): void {
    const query: Record<string, string> = {};

    if (localFilters.value.search) {
        query.search = localFilters.value.search;
    }

    if (localFilters.value.role) {
        query.role = localFilters.value.role;
    }

    if (localFilters.value.status) {
        query.status = localFilters.value.status;
    }

    router.get(index.url({ query }), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

/**
 * Clear all filters.
 */
function clearFilters(): void {
    localFilters.value = {
        search: '',
        role: '',
        status: '',
    };

    router.get(index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

/**
 * Open add user modal.
 */
function openAddModal(): void {
    showAddModal.value = true;
}

/**
 * Close add user modal.
 */
function closeAddModal(): void {
    showAddModal.value = false;
}

/**
 * Open edit user modal.
 */
function openEditModal(user: User): void {
    editingUser.value = user;
    showEditModal.value = true;
}

/**
 * Close edit user modal.
 */
function closeEditModal(): void {
    showEditModal.value = false;
    editingUser.value = null;
}

/**
 * Open deactivate confirmation modal.
 */
function openDeactivateModal(user: User): void {
    deactivatingUser.value = user;
    showDeactivateModal.value = true;
}

/**
 * Close deactivate confirmation modal.
 */
function closeDeactivateModal(): void {
    showDeactivateModal.value = false;
    deactivatingUser.value = null;
}

/**
 * Handle page change from pagination.
 */
function handlePageChange(page: number): void {
    const query: Record<string, string | number> = { page };

    if (localFilters.value.search) {
        query.search = localFilters.value.search;
    }

    if (localFilters.value.role) {
        query.role = localFilters.value.role;
    }

    if (localFilters.value.status) {
        query.status = localFilters.value.status;
    }

    router.get(index.url({ query }), {}, {
        preserveScroll: true,
    });
}

/**
 * Get role badge color classes (theme-aware).
 */
function getRoleBadgeColor(role: string): string {
    switch (role) {
        case 'admin':
            return 'bg-purple-500/20 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30';
        case 'supervisor':
            return 'bg-blue-500/20 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 border-blue-500/30';
        case 'employee':
            return 'bg-emerald-500/20 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30';
        default:
            return 'bg-zinc-500/20 dark:bg-zinc-500/15 text-zinc-700 dark:text-zinc-300 border-zinc-500/30';
    }
}

/**
 * Get role display label.
 */
function getRoleLabel(role: string): string {
    switch (role) {
        case 'admin':
            return 'Admin';
        case 'supervisor':
            return 'Supervisor';
        case 'employee':
            return 'Karyawan';
        default:
            return role;
    }
}

/**
 * Get status badge color classes (theme-aware).
 */
function getStatusBadgeColor(isActive: boolean): string {
    return isActive
        ? 'bg-emerald-500/20 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30'
        : 'bg-red-500/20 dark:bg-red-500/15 text-red-700 dark:text-red-300 border-red-500/30';
}

/**
 * Get status display label.
 */
function getStatusLabel(isActive: boolean): string {
    return isActive ? 'Aktif' : 'Nonaktif';
}
</script>

<template>
    <SupervisorLayout title="Kelola Karyawan">
        <div class="min-h-screen p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header Section -->
                <motion.div
                    :initial="{ opacity: 0, y: -20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3 }"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- Title -->
                    <div>
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white md:text-4xl">
                            Kelola Karyawan
                        </h1>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Manajemen pengguna dan hak akses sistem
                        </p>
                    </div>

                    <!-- Add Button -->
                    <motion.button
                        @click="openAddModal"
                        :whileHover="{ scale: 1.05 }"
                        :whilePress="{ scale: 0.95 }"
                        :transition="{ duration: 0.2 }"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 dark:from-cyan-500 dark:to-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-200 dark:shadow-cyan-500/25 transition-all duration-200 hover:shadow-xl active:scale-95 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                    >
                        <IconPlus :size="18" />
                        <span>Tambah Karyawan</span>
                    </motion.button>
                </motion.div>

                <!-- Success Message -->
                <motion.div
                    v-if="flashMessage"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :exit="{ opacity: 0, scale: 0.95 }"
                    :transition="{ duration: 0.2 }"
                    class="rounded-lg border border-emerald-500/30 dark:border-emerald-500/30 bg-emerald-100 dark:bg-emerald-500/10 p-4"
                >
                    <div class="flex items-start gap-3">
                        <IconCheck
                            :size="20"
                            class="text-emerald-600 dark:text-emerald-400"
                        />
                        <p class="flex-1 text-sm text-emerald-800 dark:text-emerald-300">
                            {{ flashMessage }}
                        </p>
                    </div>
                </motion.div>

                <!-- Filters Section -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1, duration: 0.3 }"
                    class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white/90 dark:bg-zinc-800/50 backdrop-blur-sm p-4 md:p-6 shadow-lg shadow-zinc-200 dark:shadow-none"
                >
                    <div class="grid gap-4 md:grid-cols-3">
                        <!-- Search Input -->
                        <div>
                            <label
                                for="search"
                                class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                Cari Karyawan
                            </label>
                            <input
                                id="search"
                                v-model="localFilters.search"
                                type="text"
                                placeholder="Nama atau email..."
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800"
                            />
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <label
                                for="role-filter"
                                class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                Filter Role
                            </label>
                            <select
                                id="role-filter"
                                v-model="localFilters.role"
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800"
                            >
                                <option value="">Semua Role</option>
                                <option value="employee">Karyawan</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label
                                for="status-filter"
                                class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                Filter Status
                            </label>
                            <select
                                id="status-filter"
                                v-model="localFilters.status"
                                class="w-full rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-800/50 px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100 transition-all duration-200 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Clear Filters Button -->
                    <div
                        v-if="hasActiveFilters"
                        class="mt-4 flex justify-end"
                    >
                        <button
                            @click="clearFilters"
                            class="text-sm text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 transition-colors duration-200"
                        >
                            Hapus Filter
                        </button>
                    </div>
                </motion.div>

                <!-- Users Table (Desktop) -->
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.2, duration: 0.3 }"
                    class="hidden md:block overflow-hidden rounded-lg border border-zinc-200 dark:border-white/5 bg-white/90 dark:bg-zinc-800/50 backdrop-blur-sm shadow-lg shadow-zinc-200 dark:shadow-none"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-900 dark:text-white">
                                        Nama
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-900 dark:text-white">
                                        Email
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-900 dark:text-white">
                                        Role
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-900 dark:text-white">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <motion.tr
                                    v-for="(user, index) in users"
                                    :key="user.id"
                                    :initial="{ opacity: 0, x: -20 }"
                                    :animate="{ opacity: 1, x: 0 }"
                                    :transition="{ delay: index * 0.05, duration: 0.2 }"
                                    class="border-b border-zinc-200 dark:border-white/5 last:border-b-0 hover:bg-zinc-50 dark:hover:bg-white/5 transition-all duration-200"
                                >
                                    <td class="px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getRoleBadgeColor(user.role)"
                                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                        >
                                            {{ getRoleLabel(user.role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getStatusBadgeColor(user.is_active)"
                                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                        >
                                            {{ getStatusLabel(user.is_active) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Edit Button -->
                                            <motion.button
                                                v-if="user.id !== authStore.user?.id"
                                                @click="openEditModal(user)"
                                                :whileHover="{ scale: 1.1 }"
                                                :whilePress="{ scale: 0.9 }"
                                                :transition="{ duration: 0.2 }"
                                                class="rounded-lg bg-blue-500/20 dark:bg-blue-500/15 p-2 text-blue-600 dark:text-blue-400 transition-all duration-200 hover:bg-blue-500/30 dark:hover:bg-blue-500/25"
                                                title="Edit"
                                            >
                                                <IconEdit :size="18" />
                                            </motion.button>

                                            <!-- Deactivate Button -->
                                            <motion.button
                                                v-if="user.id !== authStore.user?.id && user.is_active"
                                                @click="openDeactivateModal(user)"
                                                :whileHover="{ scale: 1.1 }"
                                                :whilePress="{ scale: 0.9 }"
                                                :transition="{ duration: 0.2 }"
                                                class="rounded-lg bg-red-500/20 dark:bg-red-500/15 p-2 text-red-600 dark:text-red-400 transition-all duration-200 hover:bg-red-500/30 dark:hover:bg-red-500/25"
                                                title="Nonaktifkan"
                                            >
                                                <IconBan :size="18" />
                                            </motion.button>

                                            <!-- Self Indicator -->
                                            <span
                                                v-if="user.id === authStore.user?.id"
                                                class="text-xs text-zinc-500 dark:text-zinc-400"
                                            >
                                                (Anda)
                                            </span>
                                        </div>
                                    </td>
                                </motion.tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="users.length === 0"
                        class="py-12 text-center"
                    >
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Tidak ada karyawan ditemukan.
                        </p>
                    </div>
                </motion.div>

                <!-- Users Cards (Mobile) -->
                <div class="md:hidden space-y-4">
                    <motion.div
                        v-for="(user, index) in users"
                        :key="user.id"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: index * 0.05, duration: 0.2 }"
                        class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white/90 dark:bg-zinc-800/50 backdrop-blur-sm p-4 shadow-lg shadow-zinc-200 dark:shadow-none"
                    >
                        <div class="space-y-3">
                            <!-- Name & Email -->
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ user.email }}
                                </p>
                            </div>

                            <!-- Badges -->
                            <div class="flex items-center gap-2">
                                <span
                                    :class="getRoleBadgeColor(user.role)"
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                >
                                    {{ getRoleLabel(user.role) }}
                                </span>
                                <span
                                    :class="getStatusBadgeColor(user.is_active)"
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                >
                                    {{ getStatusLabel(user.is_active) }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div
                                v-if="user.id !== authStore.user?.id"
                                class="flex items-center gap-2 pt-2"
                            >
                                <button
                                    @click="openEditModal(user)"
                                    class="flex-1 rounded-lg bg-blue-500/20 dark:bg-blue-500/15 px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 transition-all duration-200 hover:bg-blue-500/30 dark:hover:bg-blue-500/25"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="user.is_active"
                                    @click="openDeactivateModal(user)"
                                    class="flex-1 rounded-lg bg-red-500/20 dark:bg-red-500/15 px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 transition-all duration-200 hover:bg-red-500/30 dark:hover:bg-red-500/25"
                                >
                                    Nonaktifkan
                                </button>
                            </div>

                            <!-- Self Indicator -->
                            <p
                                v-else
                                class="text-center text-xs text-zinc-500 dark:text-zinc-400 pt-2"
                            >
                                (Akun Anda)
                            </p>
                        </div>
                    </motion.div>

                    <!-- Empty State -->
                    <div
                        v-if="users.length === 0"
                        class="rounded-lg border border-zinc-200 dark:border-white/5 bg-white/90 dark:bg-zinc-800/50 backdrop-blur-sm py-12 text-center shadow-lg shadow-zinc-200 dark:shadow-none"
                    >
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Tidak ada karyawan ditemukan.
                        </p>
                    </div>
                </div>

                <!-- Pagination -->
                <motion.div
                    v-if="meta.last_page > 1"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.3, duration: 0.3 }"
                >
                    <Pagination
                        :current-page="meta.current_page"
                        :last-page="meta.last_page"
                        :total="meta.total"
                        @page-change="handlePageChange"
                    />
                </motion.div>
            </div>
        </div>

        <!-- ======================================================================
            Modals
        ======================================================================= -->
        <UserFormModal
            :show="showAddModal"
            @close="closeAddModal"
        />

        <UserFormModal
            v-if="editingUser"
            :show="showEditModal"
            :user="editingUser"
            @close="closeEditModal"
        />

        <ConfirmDeactivateModal
            v-if="deactivatingUser"
            :show="showDeactivateModal"
            :user="deactivatingUser"
            @close="closeDeactivateModal"
        />
    </SupervisorLayout>
</template>
