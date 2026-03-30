<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->supervisor()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
        ]);

        User::factory()->employee()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ]);

        Setting::set('speed_limit', '60', 'Global speed limit in km/h');
        Setting::set('auto_stop_duration', '1800', 'Auto-stop trip after inactivity (seconds)');
        Setting::set('speed_log_interval', '5', 'Speed logging interval (seconds)');
    }
}
