<?php

namespace App\Services;

use App\Enums\TripStatus;
use App\Models\Trip;
use Carbon\Carbon;

/**
 * Dashboard Service
 *
 * Handles supervisor/admin dashboard statistics aggregation including
 * real-time trip monitoring, violation tracking, and performance metrics.
 */
class DashboardService
{
    /**
     * Get complete dashboard overview for supervisors/admins.
     *
     * Aggregates today's summary statistics, active trips, top violators,
     * and overall performance metrics for employee monitoring.
     *
     * @return array Dashboard overview data with all metrics
     */
    public function getOverview(): array
    {
        return [
            'today_summary' => $this->getTodaySummary(),
            'active_trips' => $this->getActiveTrips(),
            'top_violators' => $this->getTopViolators(5),
            'average_speed' => $this->getAverageSpeedAllEmployees(),
        ];
    }

    /**
     * Get today's summary statistics.
     *
     * Calculates total trips and violations that occurred today across
     * all employees for high-level monitoring dashboard.
     *
     * @return array Summary with total_trips and violations_count for today
     */
    private function getTodaySummary(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $totalTrips = Trip::whereBetween('started_at', [$todayStart, $todayEnd])->count();

        $violationsCount = Trip::whereBetween('started_at', [$todayStart, $todayEnd])
            ->sum('violation_count');

        return [
            'total_trips' => $totalTrips,
            'violations_count' => $violationsCount,
        ];
    }

    /**
     * Get currently active trips with user information.
     *
     * Retrieves all in-progress trips with eager-loaded user data to avoid N+1
     * queries. Used for real-time monitoring of ongoing employee trips.
     *
     * @return array Array of active trips with user relationship
     */
    private function getActiveTrips(): array
    {
        return Trip::with('user:id,name,email')
            ->where('status', TripStatus::InProgress)
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'user' => [
                        'name' => $trip->user->name,
                        'email' => $trip->user->email,
                    ],
                    'started_at' => $trip->started_at->toIso8601String(),
                    'duration_seconds' => $trip->started_at->diffInSeconds(now()),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get top violators for today.
     *
     * Returns employees with highest violation counts from today's trips,
     * ordered by violation count descending. Used for leaderboard display.
     *
     * @param  int  $limit  Number of top violators to return
     * @return array Array of users with violation counts
     */
    private function getTopViolators(int $limit): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return Trip::with('user:id,name,email')
            ->whereBetween('started_at', [$todayStart, $todayEnd])
            ->where('violation_count', '>', 0)
            ->orderByDesc('violation_count')
            ->take($limit)
            ->get()
            ->map(function ($trip) {
                return [
                    'user' => [
                        'name' => $trip->user->name,
                        'email' => $trip->user->email,
                    ],
                    'violation_count' => $trip->violation_count,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Calculate average speed across all employees for today.
     *
     * Computes the average speed from all completed trips today to provide
     * overall performance metric for supervisor monitoring.
     *
     * @return float Average speed in km/h (rounded to 2 decimals)
     */
    private function getAverageSpeedAllEmployees(): float
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $averageSpeed = Trip::whereBetween('started_at', [$todayStart, $todayEnd])
            ->where('status', TripStatus::Completed)
            ->avg('average_speed');

        return round($averageSpeed ?? 0, 2);
    }

    /**
     * Get violation leaderboard with employee rankings.
     *
     * Aggregates violation statistics per employee within specified date range.
     * Returns employees ranked by total violations descending, including trip
     * counts and violation rates for comprehensive driver compliance monitoring.
     *
     * Uses efficient single query with GROUP BY to aggregate violations per user,
     * avoiding N+1 queries. Only includes employees with violations > 0.
     *
     * @param  string|null  $dateFrom  Start date filter (Y-m-d format), defaults to 30 days ago
     * @param  string|null  $dateTo  End date filter (Y-m-d format), defaults to today
     * @return array Array of leaderboard entries with rank, user info, violations, trips, and rate
     */
    public function getViolationLeaderboard(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? now()->subDays(30)->format('Y-m-d');
        $dateTo = $dateTo ?? now()->format('Y-m-d');

        $dateFromStart = Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = Carbon::parse($dateTo)->endOfDay();

        $results = Trip::selectRaw('
                user_id,
                SUM(violation_count) as total_violations,
                COUNT(*) as total_trips,
                ROUND(SUM(violation_count) / COUNT(*), 2) as violation_rate
            ')
            ->with('user:id,name,email')
            ->whereBetween('started_at', [$dateFromStart, $dateToEnd])
            ->groupBy('user_id')
            ->havingRaw('SUM(violation_count) > 0')
            ->orderByDesc('total_violations')
            ->get();

        return $results->map(function ($result, $index) {
            return [
                'rank' => $index + 1,
                'user' => [
                    'id' => $result->user->id,
                    'name' => $result->user->name,
                    'email' => $result->user->email,
                ],
                'violation_count' => (int) $result->total_violations,
                'total_trips' => (int) $result->total_trips,
                'violation_rate' => (float) $result->violation_rate,
            ];
        })->values()->toArray();
    }
}
