<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Dashboard API Tests
 *
 * Tests the superuser/admin dashboard overview endpoint including
 * authorization, data structure, accuracy, and caching behavior.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // ========================================================================
    // Authorization Tests
    // ========================================================================

    public function test_superuser_can_access_dashboard_overview(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_dashboard_overview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_dashboard_overview(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->getJson('/api/dashboard/overview');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard/overview');

        $response->assertStatus(401);
    }

    // ========================================================================
    // Data Structure Tests
    // ========================================================================

    public function test_dashboard_returns_correct_data_structure(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'today_summary' => [
                    'total_trips',
                    'violations_count',
                ],
                'active_trips',
                'top_violators',
                'average_speed',
            ]);
    }

    // ========================================================================
    // Today's Summary Tests
    // ========================================================================

    public function test_today_summary_counts_trips_from_today(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Create trips today
        Trip::factory()->count(3)->create([
            'user_id' => $employee->id,
            'started_at' => now(),
        ]);

        // Create trip from yesterday (should not be counted)
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200)
            ->assertJson([
                'today_summary' => [
                    'total_trips' => 3,
                ],
            ]);
    }

    public function test_today_summary_counts_violations_correctly(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Create trips with violations today
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
            'violation_count' => 5,
        ]);

        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
            'violation_count' => 3,
        ]);

        // Create trip from yesterday with violations (should not be counted)
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now()->subDay(),
            'violation_count' => 10,
        ]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200)
            ->assertJson([
                'today_summary' => [
                    'violations_count' => 8, // 5 + 3
                ],
            ]);
    }

    // ========================================================================
    // Active Trips Tests
    // ========================================================================

    public function test_active_trips_returns_only_in_progress_trips(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Create active trips
        Trip::factory()->count(2)->create([
            'user_id' => $employee->id,
        ]);

        // Create completed trip (should not appear in active trips)
        Trip::factory()->completed()->create([
            'user_id' => $employee->id,
        ]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);

        $activeTrips = $response->json('active_trips');
        $this->assertCount(2, $activeTrips);
    }

    public function test_active_trips_includes_user_information(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create([
            'role' => 'employee',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Trip::factory()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200)
            ->assertJsonPath('active_trips.0.user.name', 'John Doe')
            ->assertJsonPath('active_trips.0.user.email', 'john@example.com');
    }

    public function test_active_trips_includes_duration(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now()->subMinutes(30),
        ]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);

        $activeTrip = $response->json('active_trips.0');
        $this->assertArrayHasKey('duration_seconds', $activeTrip);
        $this->assertGreaterThanOrEqual(1800, $activeTrip['duration_seconds']); // At least 30 minutes
    }

    // ========================================================================
    // Top Violators Tests
    // ========================================================================

    public function test_top_violators_returns_only_trips_with_violations_from_today(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee1 = User::factory()->create(['role' => 'employee', 'name' => 'High Violator']);
        $employee2 = User::factory()->create(['role' => 'employee', 'name' => 'Low Violator']);
        $employee3 = User::factory()->create(['role' => 'employee', 'name' => 'No Violations']);

        // High violator today
        Trip::factory()->create([
            'user_id' => $employee1->id,
            'started_at' => now(),
            'violation_count' => 15,
        ]);

        // Low violator today
        Trip::factory()->create([
            'user_id' => $employee2->id,
            'started_at' => now(),
            'violation_count' => 5,
        ]);

        // No violations today
        Trip::factory()->create([
            'user_id' => $employee3->id,
            'started_at' => now(),
            'violation_count' => 0,
        ]);

        // High violations yesterday (should not be included)
        Trip::factory()->create([
            'user_id' => $employee3->id,
            'started_at' => now()->subDay(),
            'violation_count' => 20,
        ]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);

        $topViolators = $response->json('top_violators');
        $this->assertCount(2, $topViolators);
        $this->assertEquals('High Violator', $topViolators[0]['user']['name']);
        $this->assertEquals(15, $topViolators[0]['violation_count']);
    }

    public function test_top_violators_orders_by_violation_count_descending(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Create trips with different violation counts
        $employees = [
            ['name' => 'Employee A', 'violations' => 3],
            ['name' => 'Employee B', 'violations' => 10],
            ['name' => 'Employee C', 'violations' => 7],
            ['name' => 'Employee D', 'violations' => 1],
            ['name' => 'Employee E', 'violations' => 5],
        ];

        foreach ($employees as $emp) {
            $user = User::factory()->create(['role' => 'employee', 'name' => $emp['name']]);
            Trip::factory()->create([
                'user_id' => $user->id,
                'started_at' => now(),
                'violation_count' => $emp['violations'],
            ]);
        }

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);

        $topViolators = $response->json('top_violators');
        $this->assertEquals('Employee B', $topViolators[0]['user']['name']); // 10 violations
        $this->assertEquals('Employee C', $topViolators[1]['user']['name']); // 7 violations
        $this->assertEquals('Employee E', $topViolators[2]['user']['name']); // 5 violations
    }

    public function test_top_violators_limits_to_five_results(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Create 10 trips with violations
        for ($i = 1; $i <= 10; $i++) {
            $user = User::factory()->create(['role' => 'employee']);
            Trip::factory()->create([
                'user_id' => $user->id,
                'started_at' => now(),
                'violation_count' => $i,
            ]);
        }

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200);

        $topViolators = $response->json('top_violators');
        $this->assertCount(5, $topViolators);
    }

    // ========================================================================
    // Average Speed Tests
    // ========================================================================

    public function test_average_speed_calculates_from_completed_trips_today(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Create completed trips today
        Trip::factory()->completed()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
            'average_speed' => 50.00,
        ]);

        Trip::factory()->completed()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
            'average_speed' => 60.00,
        ]);

        // In-progress trip (should not be counted)
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
            'average_speed' => null,
        ]);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200)
            ->assertJson([
                'average_speed' => 55.00, // (50 + 60) / 2
            ]);
    }

    public function test_average_speed_returns_zero_when_no_completed_trips(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $response->assertStatus(200)
            ->assertJson([
                'average_speed' => 0,
            ]);
    }

    // ========================================================================
    // Cache Tests
    // ========================================================================

    public function test_dashboard_data_is_cached(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Clear cache before test
        Cache::forget('dashboard:overview');

        // First request - should cache data
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Verify cache exists
        $this->assertTrue(Cache::has('dashboard:overview'));
    }

    public function test_cached_data_is_returned_on_subsequent_requests(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Clear cache
        Cache::forget('dashboard:overview');

        // Create initial trip
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
        ]);

        // First request - caches data with 1 trip
        $firstResponse = $this->actingAs($superuser)->getJson('/api/dashboard/overview');
        $firstResponse->assertJson(['today_summary' => ['total_trips' => 1]]);

        // Create another trip after cache is set
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
        ]);

        // Second request - should still return cached data (1 trip)
        $secondResponse = $this->actingAs($superuser)->getJson('/api/dashboard/overview');
        $secondResponse->assertJson(['today_summary' => ['total_trips' => 1]]);
    }

    public function test_cache_can_be_cleared_to_get_fresh_data(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Clear cache
        Cache::forget('dashboard:overview');

        // Create initial trip
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
        ]);

        // First request
        $this->actingAs($superuser)->getJson('/api/dashboard/overview')
            ->assertJson(['today_summary' => ['total_trips' => 1]]);

        // Create another trip
        Trip::factory()->create([
            'user_id' => $employee->id,
            'started_at' => now(),
        ]);

        // Clear cache manually
        Cache::forget('dashboard:overview');

        // Request after cache clear - should show updated data
        $this->actingAs($superuser)->getJson('/api/dashboard/overview')
            ->assertJson(['today_summary' => ['total_trips' => 2]]);
    }
}
