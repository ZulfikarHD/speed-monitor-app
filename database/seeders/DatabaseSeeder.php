<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder
 *
 * Main seeder that orchestrates all database seeding. Seeds are executed
 * in order: settings first (required by other features), then users
 * (required by trips), and finally trips with speed logs.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            UserSeeder::class,
            TripSeeder::class,
        ]);
    }
}
