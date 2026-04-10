<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * User Seeder
 *
 * Seeds test users for development: 1 admin, 2 superusers, and 10 employees.
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
        User::factory()->superuser()->create([
            'name' => 'Superuser User',
            'email' => 'superuser@example.com',
        ]);

        User::factory()->employee()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
        ]);

        // Create additional superuser users
        User::factory()->superuser()->create([
            'name' => 'Superuser Two',
            'email' => 'superuser2@example.com',
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
