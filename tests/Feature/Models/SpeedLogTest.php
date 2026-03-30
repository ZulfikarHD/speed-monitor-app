<?php

namespace Tests\Feature\Models;

use App\Models\SpeedLog;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeedLogTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertEquals('2026-03-30 10:30:00', $speedLog->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_speed_log_factory_creates_valid_speed_log(): void
    {
        $speedLog = SpeedLog::factory()->create();

        $this->assertInstanceOf(SpeedLog::class, $speedLog);
        $this->assertNotNull($speedLog->trip_id);
        $this->assertNotNull($speedLog->speed);
        $this->assertNotNull($speedLog->recorded_at);
        $this->assertIsBool($speedLog->is_violation);
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

    public function test_speed_log_created_at_timestamp_is_set(): void
    {
        $speedLog = SpeedLog::factory()->create();

        $this->assertNotNull($speedLog->created_at);
        $this->assertInstanceOf(\DateTimeInterface::class, $speedLog->created_at);
    }

    public function test_violations_scope_filters_only_violations(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->safe()->count(5)->create(['trip_id' => $trip->id]);
        SpeedLog::factory()->violation()->count(3)->create(['trip_id' => $trip->id]);

        $violations = SpeedLog::violations()->get();

        $this->assertCount(3, $violations);
        $violations->each(function ($log) {
            $this->assertTrue($log->is_violation);
        });
    }

    public function test_safe_scope_filters_only_safe_speeds(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->safe()->count(4)->create(['trip_id' => $trip->id]);
        SpeedLog::factory()->violation()->count(2)->create(['trip_id' => $trip->id]);

        $safeLogs = SpeedLog::safe()->get();

        $this->assertCount(4, $safeLogs);
        $safeLogs->each(function ($log) {
            $this->assertFalse($log->is_violation);
        });
    }

    public function test_bulk_create_facade_method(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => 45.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 55.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 65.0, 'recorded_at' => '2026-03-30 10:00:10'],
        ];

        $result = SpeedLog::bulkCreate($trip, $speedLogData);

        $this->assertCount(3, $result);
        $this->assertDatabaseCount('speed_logs', 3);
    }

    public function test_speed_log_timestamps_behavior(): void
    {
        $speedLog = SpeedLog::factory()->create();

        $this->assertNotNull($speedLog->created_at);
    }

    public function test_speed_logs_can_be_queried_by_trip(): void
    {
        $trip1 = Trip::factory()->create();
        $trip2 = Trip::factory()->create();

        SpeedLog::factory()->count(3)->create(['trip_id' => $trip1->id]);
        SpeedLog::factory()->count(2)->create(['trip_id' => $trip2->id]);

        $trip1Logs = SpeedLog::where('trip_id', $trip1->id)->get();
        $trip2Logs = SpeedLog::where('trip_id', $trip2->id)->get();

        $this->assertCount(3, $trip1Logs);
        $this->assertCount(2, $trip2Logs);
    }

    public function test_speed_logs_can_be_ordered_by_recorded_at(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->create([
            'trip_id' => $trip->id,
            'recorded_at' => '2026-03-30 10:00:10',
        ]);
        SpeedLog::factory()->create([
            'trip_id' => $trip->id,
            'recorded_at' => '2026-03-30 10:00:00',
        ]);
        SpeedLog::factory()->create([
            'trip_id' => $trip->id,
            'recorded_at' => '2026-03-30 10:00:05',
        ]);

        $orderedLogs = SpeedLog::where('trip_id', $trip->id)
            ->orderBy('recorded_at')
            ->get();

        $this->assertEquals('2026-03-30 10:00:00', $orderedLogs[0]->recorded_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-30 10:00:05', $orderedLogs[1]->recorded_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-30 10:00:10', $orderedLogs[2]->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_speed_log_can_query_violations_for_specific_trip(): void
    {
        $trip = Trip::factory()->create();

        SpeedLog::factory()->safe()->count(5)->create(['trip_id' => $trip->id]);
        SpeedLog::factory()->violation()->count(2)->create(['trip_id' => $trip->id]);

        $tripViolations = SpeedLog::where('trip_id', $trip->id)
            ->violations()
            ->get();

        $this->assertCount(2, $tripViolations);
    }

    public function test_speed_log_decimal_precision(): void
    {
        $speedLog = SpeedLog::factory()->create(['speed' => 123.456]);

        $speedLog->refresh();

        $this->assertEquals('123.46', $speedLog->speed);
    }

    public function test_multiple_speed_logs_with_same_timestamp(): void
    {
        $trip = Trip::factory()->create();
        $timestamp = '2026-03-30 10:00:00';

        $log1 = SpeedLog::factory()->create([
            'trip_id' => $trip->id,
            'recorded_at' => $timestamp,
            'speed' => 50.0,
        ]);
        $log2 = SpeedLog::factory()->create([
            'trip_id' => $trip->id,
            'recorded_at' => $timestamp,
            'speed' => 55.0,
        ]);

        $this->assertDatabaseHas('speed_logs', ['id' => $log1->id, 'speed' => '50.00']);
        $this->assertDatabaseHas('speed_logs', ['id' => $log2->id, 'speed' => '55.00']);
    }
}
