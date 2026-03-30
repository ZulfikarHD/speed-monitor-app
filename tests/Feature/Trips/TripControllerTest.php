<?php

namespace Tests\Feature\Trips;

use App\Enums\TripStatus;
use App\Models\SpeedLog;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsEmployee(): User
    {
        return User::factory()->employee()->create();
    }

    private function actingAsSupervisor(): User
    {
        return User::factory()->supervisor()->create();
    }

    private function actingAsAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    // ========== START TRIP TESTS (POST /api/trips) ==========

    public function test_employee_can_start_trip(): void
    {
        $user = $this->actingAsEmployee();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/trips', []);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'trip' => [
                    'id',
                    'user_id',
                    'started_at',
                    'status',
                ],
            ])
            ->assertJson([
                'trip' => [
                    'user_id' => $user->id,
                    'status' => TripStatus::InProgress->value,
                ],
            ]);

        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'status' => TripStatus::InProgress->value,
        ]);
    }

    public function test_employee_cannot_start_trip_when_one_is_already_active(): void
    {
        $user = $this->actingAsEmployee();
        Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/trips', []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'You already have an active trip',
            ]);
    }

    public function test_unauthenticated_user_cannot_start_trip(): void
    {
        $response = $this->postJson('/api/trips', []);

        $response->assertStatus(401);
    }

    public function test_start_trip_with_notes(): void
    {
        $user = $this->actingAsEmployee();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/trips', [
                'notes' => 'Morning commute',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'trip' => [
                    'notes' => 'Morning commute',
                ],
            ]);

        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'notes' => 'Morning commute',
        ]);
    }

    public function test_start_trip_validates_notes_max_length(): void
    {
        $user = $this->actingAsEmployee();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/trips', [
                'notes' => str_repeat('a', 1001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    public function test_supervisor_can_start_trip(): void
    {
        $user = $this->actingAsSupervisor();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/trips', []);

        $response->assertStatus(201)
            ->assertJson([
                'trip' => [
                    'user_id' => $user->id,
                    'status' => TripStatus::InProgress->value,
                ],
            ]);
    }

    // ========== END TRIP TESTS (PUT /api/trips/{id}) ==========

    public function test_employee_can_end_own_trip(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/trips/{$trip->id}", []);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trip' => [
                    'id',
                    'user_id',
                    'started_at',
                    'ended_at',
                    'status',
                    'duration_seconds',
                ],
            ])
            ->assertJson([
                'trip' => [
                    'status' => TripStatus::Completed->value,
                ],
            ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => TripStatus::Completed->value,
        ]);
    }

    public function test_employee_cannot_end_another_users_trip(): void
    {
        $user = $this->actingAsEmployee();
        $otherUser = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $otherUser->id,
            'status' => TripStatus::InProgress,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/trips/{$trip->id}", []);

        $response->assertStatus(403);
    }

    public function test_cannot_end_trip_that_is_not_in_progress(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->completed()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/trips/{$trip->id}", []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only trips in progress can be ended',
            ]);
    }

    public function test_ending_trip_calculates_statistics(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        SpeedLog::factory()->count(5)->create([
            'trip_id' => $trip->id,
            'speed' => 50,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/trips/{$trip->id}", []);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trip' => [
                    'max_speed',
                    'average_speed',
                    'total_distance',
                    'violation_count',
                ],
            ]);
    }

    public function test_end_trip_with_notes_updates_notes_field(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/trips/{$trip->id}", [
                'notes' => 'Trip completed safely',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'trip' => [
                    'notes' => 'Trip completed safely',
                ],
            ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'notes' => 'Trip completed safely',
        ]);
    }

    public function test_unauthenticated_user_cannot_end_trip(): void
    {
        $trip = Trip::factory()->create([
            'status' => TripStatus::InProgress,
        ]);

        $response = $this->putJson("/api/trips/{$trip->id}", []);

        $response->assertStatus(401);
    }

    // ========== LIST TRIPS TESTS (GET /api/trips) ==========

    public function test_employee_can_list_own_trips(): void
    {
        $user = $this->actingAsEmployee();
        Trip::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'user' => ['id', 'name', 'email'],
                        'started_at',
                        'status',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_employee_cannot_see_other_users_trips(): void
    {
        $user = $this->actingAsEmployee();
        $otherUser = User::factory()->create();

        Trip::factory()->count(2)->create(['user_id' => $user->id]);
        Trip::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $trip) {
            $this->assertEquals($user->id, $trip['user_id']);
        }
    }

    public function test_supervisor_can_list_all_trips(): void
    {
        $supervisor = $this->actingAsSupervisor();
        $employee1 = User::factory()->employee()->create();
        $employee2 = User::factory()->employee()->create();

        Trip::factory()->create(['user_id' => $employee1->id]);
        Trip::factory()->create(['user_id' => $employee2->id]);

        $response = $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_supervisor_can_filter_trips_by_user_id(): void
    {
        $supervisor = $this->actingAsSupervisor();
        $employee1 = User::factory()->employee()->create();
        $employee2 = User::factory()->employee()->create();

        Trip::factory()->count(2)->create(['user_id' => $employee1->id]);
        Trip::factory()->count(3)->create(['user_id' => $employee2->id]);

        $response = $this->actingAs($supervisor, 'sanctum')
            ->getJson("/api/trips?user_id={$employee1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $trip) {
            $this->assertEquals($employee1->id, $trip['user_id']);
        }
    }

    public function test_list_trips_with_pagination(): void
    {
        $user = $this->actingAsEmployee();
        Trip::factory()->count(25)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJson([
                'meta' => [
                    'per_page' => 10,
                    'total' => 25,
                    'last_page' => 3,
                ],
            ]);
    }

    public function test_list_trips_filtered_by_status(): void
    {
        $user = $this->actingAsEmployee();
        Trip::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);
        Trip::factory()->count(3)->completed()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $trip) {
            $this->assertEquals(TripStatus::Completed->value, $trip['status']);
        }
    }

    public function test_list_trips_filtered_by_date_range(): void
    {
        $user = $this->actingAsEmployee();

        Trip::factory()->create([
            'user_id' => $user->id,
            'started_at' => now()->subDays(5),
        ]);

        Trip::factory()->create([
            'user_id' => $user->id,
            'started_at' => now()->subDays(2),
        ]);

        Trip::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips?date_from='.now()->subDays(3)->format('Y-m-d').'&date_to='.now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_list_trips_validates_per_page_max_100(): void
    {
        $user = $this->actingAsEmployee();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_list_trips_eager_loads_user_relationship(): void
    {
        $user = $this->actingAsEmployee();
        Trip::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'user' => ['id', 'name', 'email'],
                    ],
                ],
            ]);

        $this->assertNotNull($response->json('data.0.user'));
    }

    // ========== SHOW TRIP TESTS (GET /api/trips/{id}) ==========

    public function test_employee_can_view_own_trip_details(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trip' => [
                    'id',
                    'user_id',
                    'user' => ['id', 'name', 'email'],
                    'started_at',
                    'status',
                ],
            ])
            ->assertJson([
                'trip' => [
                    'id' => $trip->id,
                ],
            ]);
    }

    public function test_employee_cannot_view_another_users_trip(): void
    {
        $user = $this->actingAsEmployee();
        $otherUser = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(403);
    }

    public function test_supervisor_can_view_any_trip(): void
    {
        $supervisor = $this->actingAsSupervisor();
        $employee = User::factory()->employee()->create();
        $trip = Trip::factory()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($supervisor, 'sanctum')
            ->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJson([
                'trip' => [
                    'id' => $trip->id,
                ],
            ]);
    }

    public function test_trip_details_includes_speed_logs(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create(['user_id' => $user->id]);
        SpeedLog::factory()->count(3)->create(['trip_id' => $trip->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trip' => [
                    'speed_logs' => [
                        '*' => [
                            'id',
                            'speed',
                            'recorded_at',
                            'is_violation',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(3, 'trip.speed_logs');
    }

    public function test_trip_details_includes_user_info(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trip' => [
                    'user' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJson([
                'trip' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_trip(): void
    {
        $trip = Trip::factory()->create();

        $response = $this->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(401);
    }

    // ========== BULK INSERT SPEED LOGS TESTS (POST /api/trips/{id}/speed-logs) ==========

    public function test_employee_can_bulk_insert_speed_logs_to_own_active_trip(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 45.5, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 62.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 58.3, 'recorded_at' => '2026-03-30 10:00:10'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'created_count',
                'trip' => [
                    'id',
                    'max_speed',
                    'average_speed',
                    'total_distance',
                    'violation_count',
                    'synced_at',
                ],
            ])
            ->assertJson([
                'message' => 'Speed logs created successfully',
                'created_count' => 3,
            ]);

        $this->assertDatabaseCount('speed_logs', 3);
    }

    public function test_bulk_insert_successfully_creates_speed_logs_in_database(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 60.0, 'recorded_at' => '2026-03-30 10:00:05'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('speed_logs', [
            'trip_id' => $trip->id,
            'speed' => 50.0,
        ]);

        $this->assertDatabaseHas('speed_logs', [
            'trip_id' => $trip->id,
            'speed' => 60.0,
        ]);
    }

    public function test_bulk_insert_updates_trip_statistics(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
            'max_speed' => null,
            'average_speed' => null,
            'total_distance' => null,
            'violation_count' => 0,
        ]);

        $speedLogs = [
            ['speed' => 40.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 60.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 80.0, 'recorded_at' => '2026-03-30 10:00:10'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'trip' => [
                    'max_speed' => 80.0,
                    'average_speed' => 60.0,
                ],
            ]);

        $trip->refresh();
        $this->assertEquals(80.0, (float) $trip->max_speed);
        $this->assertEquals(60.0, (float) $trip->average_speed);
        $this->assertGreaterThan(0, $trip->total_distance);
    }

    public function test_bulk_insert_calculates_violation_flags_correctly(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 45.0, 'recorded_at' => '2026-03-30 10:00:00'],
            ['speed' => 70.0, 'recorded_at' => '2026-03-30 10:00:05'],
            ['speed' => 80.0, 'recorded_at' => '2026-03-30 10:00:10'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(200);

        $trip->refresh();
        $this->assertEquals(2, $trip->violation_count);

        $createdLogs = SpeedLog::where('trip_id', $trip->id)->orderBy('recorded_at')->get();
        $this->assertFalse($createdLogs[0]->is_violation);
        $this->assertTrue($createdLogs[1]->is_violation);
        $this->assertTrue($createdLogs[2]->is_violation);
    }

    public function test_bulk_insert_handles_large_batch_efficiently(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [];
        for ($i = 0; $i < 100; $i++) {
            $speedLogs[] = [
                'speed' => 40.0 + ($i % 40),
                'recorded_at' => now()->addSeconds($i * 5)->format('Y-m-d H:i:s'),
            ];
        }

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'created_count' => 100,
            ]);

        $this->assertDatabaseCount('speed_logs', 100);
    }

    public function test_bulk_insert_updates_synced_at_timestamp(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
            'synced_at' => null,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trip' => ['synced_at'],
            ]);

        $trip->refresh();
        $this->assertNotNull($trip->synced_at);
    }

    public function test_employee_cannot_add_speed_logs_to_another_users_trip(): void
    {
        $user = $this->actingAsEmployee();
        $otherUser = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $otherUser->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_add_speed_logs_to_employee_trip(): void
    {
        $supervisor = $this->actingAsSupervisor();
        $employee = User::factory()->employee()->create();
        $trip = Trip::factory()->create([
            'user_id' => $employee->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_add_speed_logs(): void
    {
        $trip = Trip::factory()->create([
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->postJson("/api/trips/{$trip->id}/speed-logs", [
            'speed_logs' => $speedLogs,
        ]);

        $response->assertStatus(401);
    }

    public function test_bulk_insert_rejects_empty_speed_logs_array(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs']);
    }

    public function test_bulk_insert_rejects_missing_speed_field(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs.0.speed']);
    }

    public function test_bulk_insert_rejects_missing_recorded_at_field(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 50.0],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs.0.recorded_at']);
    }

    public function test_bulk_insert_rejects_negative_speed_values(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => -10.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs.0.speed']);
    }

    public function test_bulk_insert_rejects_non_numeric_speed_values(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 'fast', 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs.0.speed']);
    }

    public function test_bulk_insert_rejects_invalid_datetime_format(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => 'invalid-date'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs.0.recorded_at']);
    }

    public function test_bulk_insert_rejects_array_exceeding_max_size(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => TripStatus::InProgress,
        ]);

        $speedLogs = [];
        for ($i = 0; $i < 1001; $i++) {
            $speedLogs[] = [
                'speed' => 50.0,
                'recorded_at' => now()->addSeconds($i * 5)->format('Y-m-d H:i:s'),
            ];
        }

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_logs']);
    }

    public function test_cannot_add_speed_logs_to_completed_trip(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->completed()->create([
            'user_id' => $user->id,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only trips in progress can accept speed logs',
            ]);
    }

    public function test_cannot_add_speed_logs_to_auto_stopped_trip(): void
    {
        $user = $this->actingAsEmployee();
        $trip = Trip::factory()->autoStopped()->create([
            'user_id' => $user->id,
        ]);

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/trips/{$trip->id}/speed-logs", [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only trips in progress can accept speed logs',
            ]);
    }

    public function test_bulk_insert_returns_404_when_trip_not_found(): void
    {
        $user = $this->actingAsEmployee();

        $speedLogs = [
            ['speed' => 50.0, 'recorded_at' => '2026-03-30 10:00:00'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/trips/99999/speed-logs', [
                'speed_logs' => $speedLogs,
            ]);

        $response->assertStatus(404);
    }
}
