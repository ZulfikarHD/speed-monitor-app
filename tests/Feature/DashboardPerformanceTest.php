<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Dashboard Performance Tests
 *
 * Verifies cache functionality and query optimization (N+1 prevention)
 * for the dashboard overview endpoint.
 */
class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    // ========================================================================
    // Query Optimization Tests (N+1 Prevention)
    // ========================================================================

    public function test_active_trips_uses_eager_loading_to_prevent_n_plus_1(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Create 10 active trips with different users
        for ($i = 0; $i < 10; $i++) {
            $employee = User::factory()->create(['role' => 'employee']);
            Trip::factory()->create(['user_id' => $employee->id]);
        }

        // Clear cache to ensure fresh query
        Cache::forget('dashboard:overview');

        // Enable query logging
        DB::enableQueryLog();

        // Make request
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Get queries executed
        $queries = DB::getQueryLog();

        // Filter out queries for authentication and other setup
        $tripRelatedQueries = collect($queries)->filter(function ($query) {
            return str_contains(strtolower($query['query']), 'trips')
                || str_contains(strtolower($query['query']), 'users');
        });

        // With eager loading (with('user')), we should have:
        // 1. One query for active trips
        // 2. One query for users (eager loaded)
        // 3. Additional queries for today's summary and other stats
        // Total trip-related queries should be manageable (< 10 regardless of trip count)

        // The key test: query count should NOT scale with number of trips
        $this->assertLessThan(10, $tripRelatedQueries->count(),
            'Too many queries detected. Possible N+1 issue. Queries: '.json_encode($queries));

        DB::disableQueryLog();
    }

    public function test_top_violators_uses_eager_loading(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Create 5 trips with violations from different users
        for ($i = 1; $i <= 5; $i++) {
            $employee = User::factory()->create(['role' => 'employee']);
            Trip::factory()->create([
                'user_id' => $employee->id,
                'started_at' => now(),
                'violation_count' => $i,
            ]);
        }

        // Clear cache
        Cache::forget('dashboard:overview');

        // Enable query logging
        DB::enableQueryLog();

        // Make request
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Get queries
        $queries = DB::getQueryLog();

        // Count should not include separate query for each user
        // With proper eager loading: 1 query for trips + 1 query for users
        $this->assertLessThan(15, count($queries),
            'Too many queries. Check eager loading for top_violators.');

        DB::disableQueryLog();
    }

    // ========================================================================
    // Cache Functionality Tests
    // ========================================================================

    public function test_cache_reduces_database_queries_on_subsequent_requests(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);
        $employee = User::factory()->create(['role' => 'employee']);

        // Create test data
        Trip::factory()->count(3)->create(['user_id' => $employee->id]);

        // Clear cache
        Cache::forget('dashboard:overview');

        // Verify cache is empty
        $this->assertFalse(Cache::has('dashboard:overview'));

        // First request - should populate cache
        $firstResponse = $this->actingAs($superuser)->getJson('/api/dashboard/overview');
        $firstResponse->assertStatus(200);

        // Verify cache is now populated
        $this->assertTrue(Cache::has('dashboard:overview'),
            'Cache should be populated after first request');

        // Get cached data
        $cachedData = Cache::get('dashboard:overview');
        $this->assertIsArray($cachedData);
        $this->assertArrayHasKey('today_summary', $cachedData);
        $this->assertArrayHasKey('active_trips', $cachedData);

        // Make a second request - verify it returns same cached data
        $secondResponse = $this->actingAs($superuser)->getJson('/api/dashboard/overview');
        $secondResponse->assertStatus(200);

        // The response should match the cached data
        $secondResponse->assertJson($cachedData);
    }

    public function test_cache_key_is_consistent(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Clear cache
        Cache::forget('dashboard:overview');

        // Make first request
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Verify cache key exists
        $this->assertTrue(Cache::has('dashboard:overview'),
            'Cache key "dashboard:overview" should exist after first request');

        // Make second request
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Cache key should still exist
        $this->assertTrue(Cache::has('dashboard:overview'),
            'Cache key should persist across multiple requests within TTL');
    }

    public function test_cache_expires_after_5_minutes(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Clear cache
        Cache::forget('dashboard:overview');

        // Make request to populate cache
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Verify cache exists
        $this->assertTrue(Cache::has('dashboard:overview'));

        // Travel forward in time by 6 minutes
        $this->travel(6)->minutes();

        // Cache should be expired
        $this->assertFalse(Cache::has('dashboard:overview'),
            'Cache should expire after 5 minutes');
    }

    // ========================================================================
    // Performance Benchmarks
    // ========================================================================

    public function test_dashboard_response_time_is_acceptable_with_many_trips(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Create 50 trips with various states
        for ($i = 0; $i < 50; $i++) {
            $employee = User::factory()->create(['role' => 'employee']);

            // Mix of active and completed trips
            if ($i % 2 === 0) {
                Trip::factory()->create(['user_id' => $employee->id]);
            } else {
                Trip::factory()->completed()->withViolations()->create([
                    'user_id' => $employee->id,
                    'started_at' => now(),
                ]);
            }
        }

        // Clear cache to measure fresh query performance
        Cache::forget('dashboard:overview');

        // Measure response time
        $startTime = microtime(true);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Response should be successful
        $response->assertStatus(200);

        // Response time should be under 500ms even with 50 trips
        $this->assertLessThan(500, $duration,
            "Dashboard response took {$duration}ms, which exceeds 500ms threshold");
    }

    public function test_cached_response_is_very_fast(): void
    {
        $superuser = User::factory()->create(['role' => 'superuser']);

        // Populate cache with first request
        Cache::forget('dashboard:overview');
        $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        // Measure cached response time
        $startTime = microtime(true);

        $response = $this->actingAs($superuser)->getJson('/api/dashboard/overview');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Response should be successful
        $response->assertStatus(200);

        // Cached response should be very fast (< 100ms)
        $this->assertLessThan(100, $duration,
            "Cached dashboard response took {$duration}ms, should be under 100ms");
    }
}
