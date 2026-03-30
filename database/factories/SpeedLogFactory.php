<?php

namespace Database\Factories;

use App\Models\SpeedLog;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpeedLog>
 */
class SpeedLogFactory extends Factory
{
    /**
     * Default speed limit in km/h.
     *
     * Used to determine violation status. Will be replaced by
     * settings table value in future sprints.
     */
    private const DEFAULT_SPEED_LIMIT = 60;

    /**
     * Define the model's default state.
     *
     * Creates a speed log with random speed between 0-80 km/h.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $speed = fake()->randomFloat(2, 0, 80);

        return [
            'trip_id' => Trip::factory(),
            'speed' => $speed,
            'recorded_at' => now(),
            'is_violation' => $speed > self::DEFAULT_SPEED_LIMIT,
        ];
    }

    /**
     * Create a speed log that violates the speed limit.
     *
     * Generates speed above the configured limit.
     */
    public function violation(): static
    {
        return $this->state(function (array $attributes) {
            $speed = fake()->randomFloat(2, self::DEFAULT_SPEED_LIMIT + 1, 100);

            return [
                'speed' => $speed,
                'is_violation' => true,
            ];
        });
    }

    /**
     * Create a safe speed log below the speed limit.
     *
     * Generates speed below the configured limit.
     */
    public function safe(): static
    {
        return $this->state(function (array $attributes) {
            $speed = fake()->randomFloat(2, 0, self::DEFAULT_SPEED_LIMIT - 1);

            return [
                'speed' => $speed,
                'is_violation' => false,
            ];
        });
    }
}
