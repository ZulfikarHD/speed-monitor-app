<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Settings Service
 *
 * Manages application settings including retrieval and bulk updates of
 * configuration values such as speed limits, auto-stop duration, and
 * speed log intervals.
 */
class SettingsService
{
    /**
     * Valid setting keys that can be managed.
     */
    private const VALID_KEYS = [
        'speed_limit',
        'auto_stop_duration',
        'speed_log_interval',
    ];

    /**
     * Get all application settings as key-value pairs.
     *
     * Retrieves all settings from the database and transforms them into
     * an associative array for easy API consumption.
     *
     * @return array<string, string> Settings as key-value pairs
     */
    public function getAllSettings(): array
    {
        return Setting::all()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Update multiple settings at once.
     *
     * Accepts an associative array of setting key-value pairs and updates
     * each one. Only valid setting keys are processed. Returns the updated
     * settings array.
     *
     * @param  array<string, mixed>  $settings  Settings to update as key-value pairs
     * @return array<string, string> Updated settings as key-value pairs
     */
    public function updateSettings(array $settings): array
    {
        foreach ($settings as $key => $value) {
            // Only update valid setting keys
            if (in_array($key, self::VALID_KEYS)) {
                Setting::set($key, $value);
            }
        }

        return $this->getAllSettings();
    }
}
