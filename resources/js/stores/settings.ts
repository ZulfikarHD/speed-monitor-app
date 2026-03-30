import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface AppSettings {
    speed_limit: number;
    auto_stop_duration: number;
    speed_log_interval: number;
    violation_threshold: number;
}

export const useSettingsStore = defineStore('settings', () => {
    const settings = ref<AppSettings>({
        speed_limit: 60,
        auto_stop_duration: 1800,
        speed_log_interval: 5,
        violation_threshold: 60,
    });

    const isLoaded = ref(false);

    function setSettings(newSettings: Partial<AppSettings>) {
        settings.value = { ...settings.value, ...newSettings };
        isLoaded.value = true;
    }

    function updateSetting<K extends keyof AppSettings>(
        key: K,
        value: AppSettings[K],
    ) {
        settings.value[key] = value;
    }

    function reset() {
        settings.value = {
            speed_limit: 60,
            auto_stop_duration: 1800,
            speed_log_interval: 5,
            violation_threshold: 60,
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
