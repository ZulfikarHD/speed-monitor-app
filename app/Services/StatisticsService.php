<?php

namespace App\Services;

use App\Enums\TripStatus;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Statistics Service
 *
 * Handles calculation and aggregation of user statistics for different time periods.
 * Provides data for employee statistics dashboard with charts and summary metrics.
 */
class StatisticsService
{
    /**
     * Get user statistics for a given period.
     *
     * Calculates summary metrics and chart data for trips within the specified period.
     * Period can be 'week', 'month', 'year', or 'custom' with explicit dates.
     *
     * @param  User  $user  The user to calculate statistics for
     * @param  string  $period  Period selector ('week', 'month', 'year', 'custom')
     * @param  string|null  $dateFrom  Custom start date (YYYY-MM-DD), required when period is 'custom'
     * @param  string|null  $dateTo  Custom end date (YYYY-MM-DD), required when period is 'custom'
     * @return array Statistics data including summary, charts, and period info
     */
    public function getUserStatistics(User $user, string $period, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        if ($period === 'custom' && $dateFrom && $dateTo) {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
        } else {
            [$startDate, $endDate] = $this->getPeriodDates($period);
        }

        // Determine chart grouping based on date span
        $chartPeriod = $this->resolveChartPeriod($period, $startDate, $endDate);

        // Fetch completed trips within period
        $trips = $user->trips()
            ->whereBetween('started_at', [$startDate, $endDate])
            ->where('status', TripStatus::Completed)
            ->orderBy('started_at', 'asc')
            ->get();

        return [
            'summary' => $this->calculateSummary($trips),
            'charts' => [
                'trips_over_time' => $this->getTripsOverTime($trips, $chartPeriod),
                'violations_over_time' => $this->getViolationsOverTime($trips, $chartPeriod),
            ],
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate instanceof Carbon ? $endDate->toDateString() : Carbon::parse($endDate)->toDateString(),
                'label' => $this->getPeriodLabel($period, $startDate, $endDate),
            ],
        ];
    }

    /**
     * Resolve chart grouping period based on date span.
     *
     * For custom ranges, uses daily grouping for <=31 days and monthly for >31 days.
     */
    private function resolveChartPeriod(string $period, CarbonInterface $startDate, CarbonInterface $endDate): string
    {
        if ($period !== 'custom') {
            return $period;
        }

        $daySpan = $startDate->diffInDays($endDate);

        return $daySpan > 31 ? 'year' : 'month';
    }

    /**
     * Calculate summary statistics from trips.
     *
     * Aggregates total trips, distance, average speed, and violation count.
     *
     * @param  Collection  $trips  Collection of Trip models
     * @return array Summary statistics
     */
    private function calculateSummary(Collection $trips): array
    {
        return [
            'total_trips' => $trips->count(),
            'total_distance' => round($trips->sum('total_distance'), 2),
            'average_speed' => $trips->count() > 0
                ? round($trips->avg('average_speed'), 2)
                : 0,
            'violation_count' => $trips->sum('violation_count'),
        ];
    }

    /**
     * Get trips count over time for chart visualization.
     *
     * Groups trips by date based on period granularity:
     * - Week: Daily breakdown (7 days)
     * - Month: Daily breakdown (up to 31 days)
     * - Year: Monthly breakdown (12 months)
     *
     * @param  Collection  $trips  Collection of Trip models
     * @param  string  $period  Period selector
     * @return array Chart data points with date and count
     */
    private function getTripsOverTime(Collection $trips, string $period): array
    {
        // Determine grouping format based on period
        $groupByFormat = match ($period) {
            'week' => 'Y-m-d',    // Daily for week
            'month' => 'Y-m-d',   // Daily for month
            'year' => 'Y-m',      // Monthly for year
        };

        // Group trips by date
        $grouped = $trips->groupBy(function ($trip) use ($groupByFormat) {
            return $trip->started_at->format($groupByFormat);
        });

        // Transform to chart data format
        return $grouped->map(function ($group, $date) {
            return [
                'date' => $date,
                'count' => $group->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Get violations count over time for chart visualization.
     *
     * Groups violation counts by date based on period granularity.
     * Uses same grouping logic as trips over time.
     *
     * @param  Collection  $trips  Collection of Trip models
     * @param  string  $period  Period selector
     * @return array Chart data points with date and violation count
     */
    private function getViolationsOverTime(Collection $trips, string $period): array
    {
        // Determine grouping format based on period
        $groupByFormat = match ($period) {
            'week' => 'Y-m-d',    // Daily for week
            'month' => 'Y-m-d',   // Daily for month
            'year' => 'Y-m',      // Monthly for year
        };

        // Group trips by date and sum violations
        $grouped = $trips->groupBy(function ($trip) use ($groupByFormat) {
            return $trip->started_at->format($groupByFormat);
        });

        // Transform to chart data format
        return $grouped->map(function ($group, $date) {
            return [
                'date' => $date,
                'count' => $group->sum('violation_count'),
            ];
        })->values()->toArray();
    }

    /**
     * Get start and end dates for the selected period.
     *
     * Periods are calculated relative to today with maximum 12-month range:
     * - Week: Monday to Sunday of current week
     * - Month: First to last day of current month
     * - Year: First to last day of last 12 months
     *
     * @param  string  $period  Period selector ('week', 'month', 'year')
     * @return array Array containing [Carbon $startDate, Carbon $endDate]
     */
    private function getPeriodDates(string $period): array
    {
        $now = now();

        return match ($period) {
            'week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'year' => [
                // Last 12 months from today
                $now->copy()->subYear()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get human-readable period label for display.
     *
     * Generates Indonesian language labels for the selected period.
     *
     * @param  string  $period  Period selector
     * @param  CarbonInterface  $startDate  Period start date
     * @param  CarbonInterface|null  $endDate  Period end date (used for custom range)
     * @return string Formatted period label
     */
    private function getPeriodLabel(string $period, CarbonInterface $startDate, ?CarbonInterface $endDate = null): string
    {
        return match ($period) {
            'week' => 'Minggu '.$startDate->format('W, Y'),
            'month' => $startDate->translatedFormat('F Y'),
            'year' => 'Last 12 Months',
            'custom' => $startDate->translatedFormat('d M Y').' - '.($endDate ? $endDate->translatedFormat('d M Y') : ''),
            default => $startDate->translatedFormat('F Y'),
        };
    }
}
