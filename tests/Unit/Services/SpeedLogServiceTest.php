<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Models\Trip;
use App\Services\SpeedLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SpeedLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private SpeedLogService $speedLogService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->speedLogService = new SpeedLogService;
    }

    public function test_bulk_insert_creates_multiple_speed_logs(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => 45.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 55.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 65.0, 'recorded_at' => '2026-03-30 10:00:10'],
        ];

        $result = $this->speedLogService->bulkInsert($trip, $speedLogData);

        $this->assertCount(3, $result);
        $this->assertDatabaseCount('speed_logs', 3);
        $this->assertEquals($trip->id, $result->first()->trip_id);
    }

    public function test_bulk_insert_calculates_violation_flags(): void
    {
        $trip = Trip::factory()->create();

        Setting::set('speed_limit', '60');

        $speedLogData = [
            ['speed' => 45.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 55.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 70.0, 'recorded_at' => '2026-03-30 10:00:10'],
            ['speed' => 80.0, 'recorded_at' => '2026-03-30 10:00:15'],
        ];

        $result = $this->speedLogService->bulkInsert($trip, $speedLogData);

        $this->assertFalse($result[0]->is_violation);
        $this->assertFalse($result[1]->is_violation);
        $this->assertTrue($result[2]->is_violation);
        $this->assertTrue($result[3]->is_violation);
    }

    public function test_bulk_insert_updates_trip_statistics(): void
    {
        $trip = Trip::factory()->create([
            'max_speed' => null,
            'average_speed' => null,
            'total_distance' => null,
            'violation_count' => 0,
        ]);

        $speedLogData = [
            ['speed' => 40.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 60.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 80.0, 'recorded_at' => '2026-03-30 10:00:10'],
        ];

        $this->speedLogService->bulkInsert($trip, $speedLogData);

        $trip->refresh();

        $this->assertEquals(80, $trip->max_speed);
        $this->assertEquals(60, $trip->average_speed);
        $this->assertGreaterThan(0, $trip->total_distance);
        $this->assertEquals(1, $trip->violation_count);
    }

    public function test_bulk_insert_handles_empty_array(): void
    {
        $trip = Trip::factory()->create();

        $result = $this->speedLogService->bulkInsert($trip, []);

        $this->assertEmpty($result);
        $this->assertDatabaseCount('speed_logs', 0);
    }

    public function test_bulk_insert_validates_missing_speed_field(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['recorded_at' => '2026-03-30 10:00:00'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Speed log at index 0 is missing 'speed' field");

        $this->speedLogService->bulkInsert($trip, $speedLogData);
    }

    public function test_bulk_insert_validates_missing_recorded_at_field(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => 50.0],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Speed log at index 0 is missing 'recorded_at' field");

        $this->speedLogService->bulkInsert($trip, $speedLogData);
    }

    public function test_bulk_insert_validates_non_array_entry(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            'not an array',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Speed log at index 0 must be an array');

        $this->speedLogService->bulkInsert($trip, $speedLogData);
    }

    public function test_bulk_insert_validates_invalid_speed_value(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => 'invalid', 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Speed log at index 0 has invalid 'speed' value");

        $this->speedLogService->bulkInsert($trip, $speedLogData);
    }

    public function test_bulk_insert_validates_negative_speed(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => -10.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Speed log at index 0 has negative speed');

        $this->speedLogService->bulkInsert($trip, $speedLogData);
    }

    public function test_calculate_violation_flag_returns_true_when_speed_exceeds_limit(): void
    {
        $result = $this->speedLogService->calculateViolationFlag(70.0, 60.0);

        $this->assertTrue($result);
    }

    public function test_calculate_violation_flag_returns_false_when_speed_within_limit(): void
    {
        $result = $this->speedLogService->calculateViolationFlag(50.0, 60.0);

        $this->assertFalse($result);
    }

    public function test_calculate_violation_flag_returns_false_when_speed_equals_limit(): void
    {
        $result = $this->speedLogService->calculateViolationFlag(60.0, 60.0);

        $this->assertFalse($result);
    }

    public function test_get_speed_limit_returns_default_when_setting_not_exists(): void
    {
        $speedLimit = $this->speedLogService->getSpeedLimit();

        $this->assertEquals(60.0, $speedLimit);
    }

    public function test_get_speed_limit_returns_configured_value(): void
    {
        Setting::set('speed_limit', '80');

        $speedLimit = $this->speedLogService->getSpeedLimit();

        $this->assertEquals(80.0, $speedLimit);
    }

    public function test_bulk_insert_sets_created_at_timestamp(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $result = $this->speedLogService->bulkInsert($trip, $speedLogData);

        $this->assertNotNull($result->first()->created_at);
    }

    public function test_bulk_insert_preserves_recorded_at_timestamp(): void
    {
        $trip = Trip::factory()->create();

        $recordedAt = '2026-03-30 10:00:00';
        $speedLogData = [
            ['speed' => 50.0, 'recorded_at' => $recordedAt],
        ];

        $result = $this->speedLogService->bulkInsert($trip, $speedLogData);

        $this->assertEquals($recordedAt, $result->first()->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_bulk_insert_orders_results_by_recorded_at(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:10'],
            ['speed' => 45.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 55.0, 'recorded_at' => '2026-03-30 10:00:05'],
        ];

        $result = $this->speedLogService->bulkInsert($trip, $speedLogData);

        $this->assertEquals('2026-03-30 10:00:00', $result[0]->recorded_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-30 10:00:05', $result[1]->recorded_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-30 10:00:10', $result[2]->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_bulk_insert_with_large_dataset(): void
    {
        $trip = Trip::factory()->create();

        $speedLogData = [];
        for ($i = 0; $i < 100; $i++) {
            $speedLogData[] = [
                'speed' => 40.0 + ($i % 40),
                'recorded_at' => now()->addSeconds($i * 5)->format('Y-m-d H:i:s'),
            ];
        }

        $result = $this->speedLogService->bulkInsert($trip, $speedLogData);

        $this->assertCount(100, $result);
        $this->assertDatabaseCount('speed_logs', 100);
    }
}
