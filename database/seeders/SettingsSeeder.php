<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Settings Seeder
 *
 * Seeds default application settings for speed tracking system including
 * speed limits, auto-stop duration, logging intervals, and violation alerts.
 * These values can be updated dynamically via the settings API.
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates default settings for:
     * - Speed limit enforcement (60 km/h)
     * - Trip auto-stop after inactivity (30 minutes)
     * - Speed logging interval (5 seconds)
     * - Violation alerts (enabled by default)
     */
    public function run(): void
    {
        Setting::set(
            'speed_limit',
            '60',
            'Global speed limit in km/h'
        );

        Setting::set(
            'auto_stop_duration',
            '1800',
            'Auto-stop trip after inactivity (seconds)'
        );

        Setting::set(
            'speed_log_interval',
            '5',
            'Speed logging interval (seconds)'
        );

        Setting::set(
            'violation_alerts_enabled',
            'true',
            'Enable/disable speed violation alerts (browser notification, audio, visual)'
        );

        Setting::set(
            'auto_sync_enabled',
            'true',
            'Enable automatic background synchronization when online'
        );
    }
}
