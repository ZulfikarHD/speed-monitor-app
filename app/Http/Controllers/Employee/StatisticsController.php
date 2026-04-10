<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Statistics Controller (Employee)
 *
 * Handles employee statistics dashboard endpoints including summary metrics
 * and chart data for trips and violations over different time periods.
 */
class StatisticsController extends Controller
{
    /**
     * Statistics service instance.
     */
    private StatisticsService $statisticsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Display employee statistics page (Inertia view).
     *
     * Renders the MyStatistics Vue page with statistics data for the selected period.
     * Supports preset periods (week/month/year) and custom date ranges.
     *
     * @param  Request  $request  HTTP request with optional 'period', 'date_from', 'date_to' query parameters
     * @return Response Inertia response with statistics data
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        [$period, $dateFrom, $dateTo] = $this->resolveFilters($request);

        $statistics = $this->statisticsService->getUserStatistics($user, $period, $dateFrom, $dateTo);

        return Inertia::render('employee/MyStatistics', [
            'statistics' => $statistics,
            'currentPeriod' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Get employee statistics data (API endpoint).
     *
     * Returns JSON statistics data for the selected period.
     * Useful for client-side refetching without full page reload.
     *
     * @param  Request  $request  HTTP request with optional 'period', 'date_from', 'date_to' query parameters
     * @return JsonResponse Statistics data in JSON format
     */
    public function api(Request $request): JsonResponse
    {
        $user = $request->user();
        [$period, $dateFrom, $dateTo] = $this->resolveFilters($request);

        $statistics = $this->statisticsService->getUserStatistics($user, $period, $dateFrom, $dateTo);

        return response()->json([
            'statistics' => $statistics,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * Resolve and validate period/date filters from request.
     *
     * @return array{0: string, 1: string|null, 2: string|null}
     */
    private function resolveFilters(Request $request): array
    {
        $period = $request->input('period', 'month');

        if (! in_array($period, ['week', 'month', 'year', 'custom'])) {
            $period = 'month';
        }

        $dateFrom = null;
        $dateTo = null;

        if ($period === 'custom') {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            // Fall back to month if custom dates are missing or invalid
            if (! $dateFrom || ! $dateTo || ! strtotime($dateFrom) || ! strtotime($dateTo)) {
                $period = 'month';
                $dateFrom = null;
                $dateTo = null;
            }
        }

        return [$period, $dateFrom, $dateTo];
    }
}
