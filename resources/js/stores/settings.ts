/**
 * Settings Store - Application-wide configuration management.
 *
 * Manages global settings for speed tracking including speed limits,
 * trip auto-stop duration, logging intervals, and violation alerts.
 * Settings can be updated dynamically and persist across sessions.
 */

import { defineStore } from 'pinia';
import { ref } from 'vue';

/**
 * Application settings interface.
 *
 * Defines all configurable parameters for the speed tracking system.
 */
export interface AppSettings {
    /** Maximum allowed speed in km/h before violation */
    speed_limit: number;
    /** Auto-stop trip after inactivity (seconds) */
    auto_stop_duration: number;
    /** Speed logging interval (seconds) */
    speed_log_interval: number;
    /** Speed threshold for violation detection (km/h) */
    violation_threshold: number;
    /** Enable/disable violation alerts (notification + audio + visual) */
    violation_alerts_enabled: boolean;
}

/**
 * Settings store for application configuration.
 *
 * Provides centralized management of speed tracking settings with
 * reactive updates and type-safe value assignment.
 *
 * @example
 * ```ts
 * import { useSettingsStore } from '@/stores/settings';
 *
 * const settingsStore = useSettingsStore();
 *
 * // Update single setting
 * settingsStore.updateSetting('speed_limit', 80);
 *
 * // Bulk update settings
 * settingsStore.setSettings({ speed_limit: 80, violation_alerts_enabled: false });
 *
 * // Reset to defaults
 * settingsStore.reset();
 * ```
 */
export const useSettingsStore = defineStore('settings', () => {
    /**
     * Application settings with default values.
     *
     * WHY: Defaults match Indonesia speed regulations and practical usage patterns.
     */
    const settings = ref<AppSettings>({
        speed_limit: 60,
        auto_stop_duration: 1800,
        speed_log_interval: 5,
        violation_threshold: 60,
        violation_alerts_enabled: true,
    });

    /**
     * Whether settings have been loaded from backend.
     *
     * WHY: Track initialization state to prevent using unloaded settings.
     */
    const isLoaded = ref(false);

    /**
     * Update multiple settings at once.
     *
     * Merges provided settings with existing values and marks settings as loaded.
     * Typically used when loading settings from backend API.
     *
     * @param newSettings - Partial settings object to merge
     *
     * @example
     * ```ts
     * settingsStore.setSettings({ speed_limit: 80, violation_alerts_enabled: false });
     * ```
     */
    function setSettings(newSettings: Partial<AppSettings>): void {
        settings.value = { ...settings.value, ...newSettings };
        isLoaded.value = true;
    }

    /**
     * Update a single setting value.
     *
     * Type-safe update of individual setting keys. Automatically
     * validates that the value type matches the setting type.
     *
     * @param key - Setting key to update
     * @param value - New value (must match setting type)
     *
     * @example
     * ```ts
     * settingsStore.updateSetting('speed_limit', 80);
     * settingsStore.updateSetting('violation_alerts_enabled', false);
     * ```
     */
    function updateSetting<K extends keyof AppSettings>(
        key: K,
        value: AppSettings[K],
    ): void {
        settings.value[key] = value;
    }

    /**
     * Reset all settings to default values.
     *
     * Restores factory defaults and marks settings as unloaded.
     * Use when clearing user preferences or during logout.
     *
     * @example
     * ```ts
     * settingsStore.reset();
     * ```
     */
    function reset(): void {
        settings.value = {
            speed_limit: 60,
            auto_stop_duration: 1800,
            speed_log_interval: 5,
            violation_threshold: 60,
            violation_alerts_enabled: true,
        };
        isLoaded.value = false;
    }

    return {
        settings,
        isLoaded,
        setSettings,
        updateSetting,
        reset,
    };
});
