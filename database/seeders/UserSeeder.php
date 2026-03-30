<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * User Seeder
 *
 * Seeds test users for development: 1 admin, 2 supervisors, and 10 employees.
 * All users have default password 'password' for easy testing.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 1 admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create 2 supervisor users
        User::factory()->supervisor()->create([
            'name' => 'Supervisor One',
            'email' => 'supervisor1@example.com',
        ]);

        User::factory()->supervisor()->create([
            'name' => 'Supervisor Two',
            'email' => 'supervisor2@example.com',
        ]);

        // Create 10 employee users
        for ($i = 1; $i <= 10; $i++) {
            User::factory()->employee()->create([
                'name' => "Employee {$i}",
                'email' => "employee{$i}@example.com",
            ]);
        }
    }
}
