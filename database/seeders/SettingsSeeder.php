<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Settings Seeder
 *
 * Seeds default application settings for speed limit, auto-stop duration,
 * and speed log interval. These values can be updated via the settings API.
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
    }
}
