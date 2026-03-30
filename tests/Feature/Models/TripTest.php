<?php

namespace Tests\Feature\Models;

use App\Enums\TripStatus;
use App\Models\SpeedLog;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $trip->user);
        $this->assertEquals($user->id, $trip->user->id);
    }

    public function test_trip_has_many_speed_logs(): void
    {
        $trip = Trip::factory()->create();
        $speedLogs = SpeedLog::factory()->count(3)->create(['trip_id' => $trip->id]);

        $this->assertCount(3, $trip->speedLogs);
        $this->assertInstanceOf(SpeedLog::class, $trip->speedLogs->first());
    }

    public function test_user_has_many_trips(): void
    {
        $user = User::factory()->create();
        $trips = Trip::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->trips);
        $this->assertInstanceOf(Trip::class, $user->trips->first());
    }

    public function test_trip_started_at_is_cast_to_datetime(): void
    {
        $trip = Trip::factory()->create(['started_at' => '2026-03-30 10:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $trip->started_at);
        $this->assertEquals('2026-03-30 10:00:00', $trip->started_at->format('Y-m-d H:i:s'));
    }

    public function test_trip_ended_at_is_cast_to_datetime(): void
    {
        $trip = Trip::factory()->create(['ended_at' => '2026-03-30 12:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $trip->ended_at);
        $this->assertEquals('2026-03-30 12:00:00', $trip->ended_at->format('Y-m-d H:i:s'));
    }

    public function test_trip_synced_at_is_cast_to_datetime(): void
    {
        $trip = Trip::factory()->create(['synced_at' => '2026-03-30 13:00:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $trip->synced_at);
    }

    public function test_trip_status_is_cast_to_enum(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);

        $this->assertInstanceOf(TripStatus::class, $trip->status);
        $this->assertEquals(TripStatus::InProgress, $trip->status);
    }

    public function test_trip_decimal_attributes_have_two_decimal_places(): void
    {
        $trip = Trip::factory()->create([
            'total_distance' => 12.345,
            'max_speed' => 67.891,
            'average_speed' => 54.321,
        ]);

        $trip->refresh();

        $this->assertEquals('12.35', $trip->total_distance);
        $this->assertEquals('67.89', $trip->max_speed);
        $this->assertEquals('54.32', $trip->average_speed);
    }

    public function test_trip_integer_attributes_are_cast_correctly(): void
    {
        $trip = Trip::factory()->create([
            'violation_count' => 5,
            'duration_seconds' => 3600,
        ]);

        $this->assertIsInt($trip->violation_count);
        $this->assertIsInt($trip->duration_seconds);
        $this->assertEquals(5, $trip->violation_count);
        $this->assertEquals(3600, $trip->duration_seconds);
    }

    public function test_deleting_trip_cascades_to_speed_logs(): void
    {
        $trip = Trip::factory()->create();
        $speedLog1 = SpeedLog::factory()->create(['trip_id' => $trip->id]);
        $speedLog2 = SpeedLog::factory()->create(['trip_id' => $trip->id]);

        $this->assertDatabaseHas('speed_logs', ['id' => $speedLog1->id]);
        $this->assertDatabaseHas('speed_logs', ['id' => $speedLog2->id]);

        $trip->delete();

        $this->assertDatabaseMissing('speed_logs', ['id' => $speedLog1->id]);
        $this->assertDatabaseMissing('speed_logs', ['id' => $speedLog2->id]);
    }

    public function test_trip_factory_creates_valid_trip(): void
    {
        $trip = Trip::factory()->create();

        $this->assertInstanceOf(Trip::class, $trip);
        $this->assertNotNull($trip->user_id);
        $this->assertNotNull($trip->started_at);
        $this->assertEquals(TripStatus::InProgress, $trip->status);
    }

    public function test_trip_factory_completed_state(): void
    {
        $trip = Trip::factory()->completed()->create();

        $this->assertEquals(TripStatus::Completed, $trip->status);
        $this->assertNotNull($trip->ended_at);
        $this->assertNotNull($trip->duration_seconds);
        $this->assertNotNull($trip->total_distance);
    }

    public function test_trip_factory_auto_stopped_state(): void
    {
        $trip = Trip::factory()->autoStopped()->create();

        $this->assertEquals(TripStatus::AutoStopped, $trip->status);
        $this->assertNotNull($trip->ended_at);
        $this->assertNotNull($trip->duration_seconds);
    }

    public function test_trip_factory_with_violations_state(): void
    {
        $trip = Trip::factory()->withViolations()->create();

        $this->assertGreaterThan(0, $trip->violation_count);
    }

    public function test_trip_factory_synced_state(): void
    {
        $trip = Trip::factory()->synced()->create();

        $this->assertNotNull($trip->synced_at);
    }

    public function test_speed_log_belongs_to_trip(): void
    {
        $trip = Trip::factory()->create();
        $speedLog = SpeedLog::factory()->create(['trip_id' => $trip->id]);

        $this->assertInstanceOf(Trip::class, $speedLog->trip);
        $this->assertEquals($trip->id, $speedLog->trip->id);
    }

    public function test_speed_log_speed_is_cast_to_decimal(): void
    {
        $speedLog = SpeedLog::factory()->create(['speed' => 65.789]);

        $speedLog->refresh();

        $this->assertEquals('65.79', $speedLog->speed);
    }

    public function test_speed_log_is_violation_is_cast_to_boolean(): void
    {
        $speedLog = SpeedLog::factory()->create(['is_violation' => true]);

        $this->assertIsBool($speedLog->is_violation);
        $this->assertTrue($speedLog->is_violation);
    }

    public function test_speed_log_recorded_at_is_cast_to_datetime(): void
    {
        $speedLog = SpeedLog::factory()->create(['recorded_at' => '2026-03-30 10:30:00']);

        $this->assertInstanceOf(\DateTimeInterface::class, $speedLog->recorded_at);
    }

    public function test_speed_log_factory_violation_state(): void
    {
        $speedLog = SpeedLog::factory()->violation()->create();

        $this->assertTrue($speedLog->is_violation);
        $this->assertGreaterThan(60, $speedLog->speed);
    }

    public function test_speed_log_factory_safe_state(): void
    {
        $speedLog = SpeedLog::factory()->safe()->create();

        $this->assertFalse($speedLog->is_violation);
        $this->assertLessThan(60, $speedLog->speed);
    }

    public function test_trip_can_be_mass_assigned_with_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => TripStatus::InProgress,
            'notes' => 'Test trip',
        ];

        $trip = Trip::create($data);

        $this->assertEquals($user->id, $trip->user_id);
        $this->assertEquals('Test trip', $trip->notes);
    }

    public function test_speed_log_can_be_mass_assigned_with_fillable_attributes(): void
    {
        $trip = Trip::factory()->create();
        $data = [
            'trip_id' => $trip->id,
            'speed' => 55.5,
            'recorded_at' => now(),
            'is_violation' => false,
        ];

        $speedLog = SpeedLog::create($data);

        $this->assertEquals($trip->id, $speedLog->trip_id);
        $this->assertEquals('55.50', $speedLog->speed);
        $this->assertFalse($speedLog->is_violation);
    }

    public function test_trip_status_enum_values_match_database(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::InProgress]);
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'status' => 'in_progress']);

        $trip->status = TripStatus::Completed;
        $trip->save();
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'status' => 'completed']);

        $trip->status = TripStatus::AutoStopped;
        $trip->save();
        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'status' => 'auto_stopped']);
    }
}
