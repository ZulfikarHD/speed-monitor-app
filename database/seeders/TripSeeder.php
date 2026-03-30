<?php

namespace Database\Seeders;

use App\Models\SpeedLog;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Trip Seeder
 *
 * Seeds sample trips with speed logs for development and testing.
 * Creates a variety of trips including completed trips with and without
 * violations to provide realistic test data for the dashboard and reporting features.
 */
class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get employee users to assign trips to
        $employees = User::where('role', 'employee')->get();

        if ($employees->isEmpty()) {
            // No employees to create trips for
            return;
        }

        // Create 5 completed trips without violations
        foreach ($employees->random(min(5, $employees->count())) as $employee) {
            $trip = Trip::factory()
                ->completed()
                ->for($employee)
                ->create();

            // Add 15-20 safe speed logs to this trip
            SpeedLog::factory()
                ->for($trip)
                ->safe()
                ->count(fake()->numberBetween(15, 20))
                ->create([
                    'recorded_at' => function () use ($trip) {
                        return fake()->dateTimeBetween(
                            $trip->started_at,
                            $trip->ended_at
                        );
                    },
                ]);
        }

        // Create 3 completed trips with violations
        foreach ($employees->random(min(3, $employees->count())) as $employee) {
            $trip = Trip::factory()
                ->completed()
                ->withViolations()
                ->for($employee)
                ->create();

            // Add mix of safe and violation speed logs
            $safeLogsCount = fake()->numberBetween(10, 15);
            $violationLogsCount = fake()->numberBetween(3, 8);

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
                ->count($violationLogsCount)
                ->create([
                    'recorded_at' => function () use ($trip) {
                        return fake()->dateTimeBetween(
                            $trip->started_at,
                            $trip->ended_at
                        );
                    },
                ]);

            // Update trip violation count to match actual violations
            $trip->update([
                'violation_count' => $violationLogsCount,
            ]);
        }

        // Create 2 auto-stopped trips
        foreach ($employees->random(min(2, $employees->count())) as $employee) {
            $trip = Trip::factory()
                ->autoStopped()
                ->for($employee)
                ->create();

            // Add speed logs
            SpeedLog::factory()
                ->for($trip)
                ->count(fake()->numberBetween(10, 15))
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
}
