<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $divisi = fake()->randomElement(['Operasional', 'Manajemen', 'IT', 'HR', 'Finance']);
        $departements = [
            'Operasional' => ['Transport & Logistik', 'Fleet Management', 'Maintenance'],
            'Manajemen' => ['Operations Management', 'Safety & Compliance', 'Strategic Planning'],
            'IT' => ['Information Technology', 'System Development', 'Infrastructure'],
            'HR' => ['Human Resources', 'Talent Management', 'Training & Development'],
            'Finance' => ['Accounting', 'Budget Planning', 'Financial Analysis'],
        ];
        $sections = [
            'Transport & Logistik' => ['Driver Pool', 'Driver Khusus', 'Driver Ekspedisi', 'Admin Transport', 'Koordinator Driver'],
            'Fleet Management' => ['Maintenance', 'Vehicle Inventory', 'Fleet Coordinator'],
            'Maintenance' => ['Workshop', 'Spare Parts', 'Quality Control'],
            'Operations Management' => ['Supervisor Operasional', 'Koordinator Armada', 'Operations Analyst'],
            'Safety & Compliance' => ['Safety Officer', 'Compliance Officer', 'Risk Management'],
            'Strategic Planning' => ['Business Planning', 'Project Management'],
            'Information Technology' => ['System Administration', 'Development Team', 'Support'],
            'System Development' => ['Frontend', 'Backend', 'Database'],
            'Infrastructure' => ['Network', 'Server', 'Security'],
            'Human Resources' => ['Recruitment', 'Employee Relations', 'Payroll'],
            'Talent Management' => ['Career Development', 'Performance Management'],
            'Training & Development' => ['Technical Training', 'Soft Skills', 'Onboarding'],
            'Accounting' => ['General Ledger', 'Accounts Payable', 'Accounts Receivable'],
            'Budget Planning' => ['Cost Control', 'Budget Analysis'],
            'Financial Analysis' => ['Reporting', 'Forecasting'],
        ];

        $departement = fake()->randomElement($departements[$divisi]);
        $section = fake()->randomElement($sections[$departement] ?? ['General']);

        return [
            'name' => fake()->name(),
            'npk' => fake()->unique()->numerify('#####'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'employee',
            'is_active' => true,
            'divisi' => $divisi,
            'departement' => $departement,
            'section' => $section,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create user with admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Create user with superuser role.
     */
    public function superuser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'superuser',
        ]);
    }

    /**
     * Create user with employee role.
     */
    public function employee(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'employee',
        ]);
    }

    /**
     * Create inactive user account.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
