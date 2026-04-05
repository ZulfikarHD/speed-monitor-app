<?php

namespace Tests\Unit\Services;

use App\Enums\TripStatus;
use App\Models\SpeedLog;
use App\Models\Trip;
use App\Models\User;
use App\Services\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TripServiceTest extends TestCase
{
    use RefreshDatabase;

    private TripService $tripService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tripService = app(TripService::class);
    }

    public function test_start_trip_creates_trip_with_correct_user(): void
    {
        $user = User::factory()->create();

        $trip = $this->tripService->startTrip($user);

        $this->assertEquals($user->id, $trip->user_id);
        $this->assertInstanceOf(Trip::class, $trip);
    }

    public function test_start_trip_sets_status_to_in_progress(): void
    {
        $user = User::factory()->create();

        $trip = $this->tripService->startTrip($user);

        $this->assertEquals(TripStatus::InProgress, $trip->status);
    }

    public function test_start_trip_sets_started_at_correctly(): void
    {
        $user = User::factory()->create();

        $trip = $this->tripService->startTrip($user);

        $this->assertNotNull($trip->started_at);
        $this->assertEqualsWithDelta(now()->timestamp, $trip->started_at->timestamp, 2);
    }

    public function test_start_trip_with_notes(): void
    {
        $user = User::factory()->create();
        $notes = 'Morning commute to office';

        $trip = $this->tripService->startTrip($user, $notes);

        $this->assertEquals($notes, $trip->notes);
    }

    public function test_start_trip_initializes_violation_count_to_zero(): void
    {
        $user = User::factory()->create();

        $trip = $this->tripService->startTrip($user);

        $this->assertEquals(0, $trip->violation_count);
    }

    public function test_end_trip_sets_status_to_completed(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        $updatedTrip = $this->tripService->endTrip($trip);

        $this->assertEquals(TripStatus::Completed, $updatedTrip->status);
    }

    public function test_end_trip_sets_ended_at_timestamp(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        $updatedTrip = $this->tripService->endTrip($trip);

        $this->assertNotNull($updatedTrip->ended_at);
        $this->assertEqualsWithDelta(now()->timestamp, $updatedTrip->ended_at->timestamp, 2);
    }

    public function test_end_trip_calculates_duration_seconds(): void
    {
        $startedAt = now()->subMinutes(30);
        $trip = Trip::factory()->create([
            'status' => TripStatus::InProgress,
            'started_at' => $startedAt,
        ]);

        $updatedTrip = $this->tripService->endTrip($trip);

        $expectedDuration = (int) abs(now()->diffInSeconds($startedAt));
        $this->assertEqualsWithDelta($expectedDuration, $updatedTrip->duration_seconds, 2);
    }

    public function test_end_trip_throws_exception_for_non_in_progress_trip(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::Completed]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only trips with InProgress status can be ended');

        $this->tripService->endTrip($trip);
    }

    public function test_end_trip_calls_update_trip_stats(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        SpeedLog::factory()->count(3)->create([
            'trip_id' => $trip->id,
            'speed' => 50,
            'is_violation' => false,
        ]);

        $updatedTrip = $this->tripService->endTrip($trip);

        $this->assertNotNull($updatedTrip->max_speed);
        $this->assertNotNull($updatedTrip->average_speed);
        $this->assertNotNull($updatedTrip->total_distance);
    }

    public function test_auto_stop_trip_sets_status_to_auto_stopped(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        $updatedTrip = $this->tripService->autoStopTrip($trip);

        $this->assertEquals(TripStatus::AutoStopped, $updatedTrip->status);
    }

    public function test_auto_stop_trip_calculates_duration_and_stats(): void
    {
        $startedAt = now()->subMinutes(45);
        $trip = Trip::factory()->create([
            'status' => TripStatus::InProgress,
            'started_at' => $startedAt,
        ]);

        SpeedLog::factory()->count(2)->create([
            'trip_id' => $trip->id,
            'speed' => 40,
        ]);

        $updatedTrip = $this->tripService->autoStopTrip($trip);

        $this->assertNotNull($updatedTrip->duration_seconds);
        $this->assertNotNull($updatedTrip->ended_at);
        $this->assertEquals(TripStatus::AutoStopped, $updatedTrip->status);
    }

    public function test_auto_stop_trip_throws_exception_for_non_in_progress_trip(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::Completed]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only trips with InProgress status can be auto-stopped');

        $this->tripService->autoStopTrip($trip);
    }

    public function test_update_trip_stats_calculates_max_speed(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 40]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 80]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 55]);

        $this->tripService->updateTripStats($trip);

        $this->assertEquals(80, $trip->max_speed);
    }

    public function test_update_trip_stats_calculates_average_speed(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 40]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 60]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 50]);

        $this->tripService->updateTripStats($trip);

        $this->assertEquals(50, $trip->average_speed);
    }

    public function test_update_trip_stats_counts_violations(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->safe()->count(5)->create(['trip_id' => $trip->id]);
        SpeedLog::factory()->violation()->count(3)->create(['trip_id' => $trip->id]);

        $this->tripService->updateTripStats($trip);

        $this->assertEquals(3, $trip->violation_count);
    }

    public function test_update_trip_stats_calculates_total_distance(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 60]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 60]);

        $this->tripService->updateTripStats($trip);

        $expectedDistance = (60 * 5 / 3600) * 2;
        $this->assertEquals(round($expectedDistance, 2), $trip->total_distance);
    }

    public function test_update_trip_stats_handles_empty_speed_logs(): void
    {
        $trip = Trip::factory()->create();

        $this->tripService->updateTripStats($trip);

        $this->assertEquals(0, $trip->max_speed);
        $this->assertEquals(0, $trip->average_speed);
        $this->assertEquals(0, $trip->total_distance);
        $this->assertEquals(0, $trip->violation_count);
    }

    public function test_update_trip_stats_with_single_speed_log(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 45.5]);

        $this->tripService->updateTripStats($trip);

        $this->assertEquals(45.5, $trip->max_speed);
        $this->assertEquals(45.5, $trip->average_speed);
        $this->assertGreaterThan(0, $trip->total_distance);
    }

    public function test_distance_calculation_with_varying_speeds(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 30]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 60]);
        SpeedLog::factory()->create(['trip_id' => $trip->id, 'speed' => 90]);

        $this->tripService->updateTripStats($trip);

        $expectedDistance = ((30 + 60 + 90) * 5 / 3600);
        $this->assertEquals(round($expectedDistance, 2), $trip->total_distance);
    }

    public function test_end_trip_persists_changes_to_database(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        $this->tripService->endTrip($trip);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => TripStatus::Completed->value,
        ]);
    }

    public function test_auto_stop_trip_persists_changes_to_database(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        $this->tripService->autoStopTrip($trip);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => TripStatus::AutoStopped->value,
        ]);
    }
}
