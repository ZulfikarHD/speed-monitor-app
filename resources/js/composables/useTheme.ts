/**
 * Theme Composable - Light/Dark mode management.
 *
 * Manages the `dark` class on `<html>` and persists preference to localStorage.
 * Supports three states: light, dark, or system (follows OS preference).
 */

import { computed, onMounted, onUnmounted, ref } from 'vue';

type ThemeMode = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'theme';

const currentMode = ref<ThemeMode>((localStorage.getItem(STORAGE_KEY) as ThemeMode) || 'system');
const systemPrefersDark = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

function applyTheme(): void {
    const shouldBeDark =
        currentMode.value === 'dark' ||
        (currentMode.value === 'system' && systemPrefersDark.value);

    document.documentElement.classList.toggle('dark', shouldBeDark);
}

applyTheme();

export function useTheme() {
    let mediaQuery: MediaQueryList | null = null;

    function handleSystemChange(e: MediaQueryListEvent): void {
        systemPrefersDark.value = e.matches;
        applyTheme();
    }

    onMounted(() => {
        mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', handleSystemChange);
    });

    onUnmounted(() => {
        mediaQuery?.removeEventListener('change', handleSystemChange);
    });

    const isDark = computed(() =>
        currentMode.value === 'dark' ||
        (currentMode.value === 'system' && systemPrefersDark.value),
    );

    function setTheme(mode: ThemeMode): void {
        currentMode.value = mode;
        localStorage.setItem(STORAGE_KEY, mode);
        applyTheme();
    }

    function toggleTheme(): void {
        setTheme(isDark.value ? 'light' : 'dark');
    }

    return {
        isDark,
        currentMode,
        setTheme,
        toggleTheme,
    };
}
