<?php

namespace Database\Factories;

use App\Enums\TripStatus;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Creates a trip in progress with start time.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'started_at' => now(),
            'ended_at' => null,
            'status' => TripStatus::InProgress,
            'total_distance' => null,
            'max_speed' => null,
            'average_speed' => null,
            'violation_count' => 0,
            'duration_seconds' => null,
            'notes' => null,
            'synced_at' => null,
        ];
    }

    /**
     * Create a completed trip with calculated statistics.
     *
     * Generates realistic trip data with end time, duration, and basic stats.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = now()->subHours(2);
            $endedAt = now()->subHour();

            return [
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'status' => TripStatus::Completed,
                'duration_seconds' => $endedAt->diffInSeconds($startedAt),
                'total_distance' => fake()->randomFloat(2, 5, 50),
                'max_speed' => fake()->randomFloat(2, 40, 80),
                'average_speed' => fake()->randomFloat(2, 30, 60),
                'violation_count' => 0,
            ];
        });
    }

    /**
     * Create an auto-stopped trip.
     *
     * Represents a trip that was automatically terminated due to inactivity.
     */
    public function autoStopped(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = now()->subHours(3);
            $endedAt = now()->subHours(2);

            return [
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'status' => TripStatus::AutoStopped,
                'duration_seconds' => $endedAt->diffInSeconds($startedAt),
                'total_distance' => fake()->randomFloat(2, 5, 50),
                'max_speed' => fake()->randomFloat(2, 40, 80),
                'average_speed' => fake()->randomFloat(2, 30, 60),
                'violation_count' => 0,
            ];
        });
    }

    /**
     * Create a trip with speed violations.
     *
     * Sets violation count to indicate driver exceeded speed limit.
     */
    public function withViolations(): static
    {
        return $this->state(fn (array $attributes) => [
            'violation_count' => fake()->numberBetween(1, 20),
        ]);
    }

    /**
     * Create a synced trip from offline data.
     *
     * Marks trip as having been synced from local storage.
     */
    public function synced(): static
    {
        return $this->state(fn (array $attributes) => [
            'synced_at' => now(),
        ]);
    }
}
