<?php

namespace Database\Seeders;

use App\Models\SpeedLog;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Trip Seeder
 *
 * Seeds extensive trip data for all employees with realistic patterns.
 * Generates trips over the last 30 days with varied shift types, vehicle types,
 * and violation patterns to provide comprehensive test data for analytics,
 * dashboards, and reporting features.
 */
class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic trip data for each employee:
     * - 10-20 trips per employee over the last 30 days
     * - Mix of shift types (non_shift, shift_pagi, shift_malam)
     * - Mix of vehicle types (mobil, motor)
     * - 70% safe trips, 25% with minor violations, 5% with major violations
     * - Some auto-stopped trips (5%)
     * - Realistic speed logs for each trip
     */
    public function run(): void
    {
        $employees = User::where('role', 'employee')->get();

        if ($employees->isEmpty()) {
            return;
        }

        $this->command->info('Creating trips for '.$employees->count().' employees...');

        foreach ($employees as $employee) {
            $tripsCount = fake()->numberBetween(10, 20);
            $this->command->info("Generating {$tripsCount} trips for {$employee->name}...");

            for ($i = 0; $i < $tripsCount; $i++) {
                $this->createTripForEmployee($employee);
            }
        }

        $totalTrips = Trip::count();
        $this->command->info("Created {$totalTrips} total trips with speed logs!");
    }

    /**
     * Create a single trip for an employee with realistic data.
     */
    private function createTripForEmployee(User $employee): void
    {
        // Random date in the last 30 days
        $daysAgo = fake()->numberBetween(0, 30);
        $startedAt = now()->subDays($daysAgo)->setTime(
            fake()->numberBetween(6, 20),
            fake()->numberBetween(0, 59)
        );

        // Determine trip type with realistic distribution
        $rand = fake()->numberBetween(1, 100);

        if ($rand <= 5) {
            // 5% auto-stopped trips
            $this->createAutoStoppedTrip($employee, $startedAt);
        } elseif ($rand <= 30) {
            // 25% trips with violations
            $this->createTripWithViolations($employee, $startedAt);
        } else {
            // 70% safe trips
            $this->createSafeTrip($employee, $startedAt);
        }
    }

    /**
     * Create a safe trip without violations.
     */
    private function createSafeTrip(User $employee, $startedAt): void
    {
        $endedAt = (clone $startedAt)->addMinutes(fake()->numberBetween(20, 120));

        $trip = Trip::factory()
            ->completed()
            ->for($employee)
            ->create([
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $startedAt->diffInSeconds($endedAt),
            ]);

        // Add 20-40 safe speed logs
        $logsCount = fake()->numberBetween(20, 40);
        SpeedLog::factory()
            ->for($trip)
            ->safe()
            ->count($logsCount)
            ->create([
                'recorded_at' => function () use ($trip) {
                    return fake()->dateTimeBetween(
                        $trip->started_at,
                        $trip->ended_at
                    );
                },
            ]);
    }

    /**
     * Create a trip with speed violations.
     */
    private function createTripWithViolations(User $employee, $startedAt): void
    {
        $endedAt = (clone $startedAt)->addMinutes(fake()->numberBetween(20, 120));

        // Determine violation severity
        $isMajorViolation = fake()->numberBetween(1, 100) <= 20; // 20% are major
        $violationCount = $isMajorViolation
            ? fake()->numberBetween(10, 20)
            : fake()->numberBetween(3, 8);

        $trip = Trip::factory()
            ->completed()
            ->withViolations()
            ->for($employee)
            ->create([
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $startedAt->diffInSeconds($endedAt),
                'violation_count' => $violationCount,
            ]);

        // Add mix of safe and violation speed logs
        $safeLogsCount = fake()->numberBetween(20, 35);

        // Create safe speed logs
        SpeedLog::factory()
            ->for($trip)
            ->safe()
            ->count($safeLogsCount)
            ->create([
                'recorded_at' => function () use ($trip) {
                    return fake()->dateTimeBetween(
                        $trip->started_at,
                        $trip->ended_at
                    );
                },
            ]);

        // Create violation speed logs
        SpeedLog::factory()
            ->for($trip)
            ->violation()
            ->count($violationCount)
            ->create([
                'recorded_at' => function () use ($trip) {
                    return fake()->dateTimeBetween(
                        $trip->started_at,
                        $trip->ended_at
                    );
                },
            ]);
    }

    /**
     * Create an auto-stopped trip.
     */
    private function createAutoStoppedTrip(User $employee, $startedAt): void
    {
        $endedAt = (clone $startedAt)->addMinutes(fake()->numberBetween(10, 40));

        $trip = Trip::factory()
            ->autoStopped()
            ->for($employee)
            ->create([
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $startedAt->diffInSeconds($endedAt),
            ]);

        // Add fewer speed logs (trip was cut short)
        $logsCount = fake()->numberBetween(5, 15);
        SpeedLog::factory()
            ->for($trip)
            ->count($logsCount)
            ->create([
                'recorded_at' => function () use ($trip) {
                    return fake()->dateTimeBetween(
                        $trip->started_at,
                        $trip->ended_at
                    );
                },
            ]);
    }
}
