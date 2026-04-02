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

        // Create primary test accounts with simple emails
        User::factory()->supervisor()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
        ]);

        User::factory()->employee()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ]);

        // Create additional supervisor users
        User::factory()->supervisor()->create([
            'name' => 'Supervisor Two',
            'email' => 'supervisor2@example.com',
        ]);

        // Create additional employee users
        for ($i = 2; $i <= 10; $i++) {
            User::factory()->employee()->create([
                'name' => "Employee {$i}",
                'email' => "employee{$i}@example.com",
            ]);
        }
    }
}
