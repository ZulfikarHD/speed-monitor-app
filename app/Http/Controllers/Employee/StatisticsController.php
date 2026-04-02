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
     * Period is passed via query parameter and defaults to 'month'.
     *
     * @param  Request  $request  HTTP request with optional 'period' query parameter
     * @return Response Inertia response with statistics data
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $period = $request->input('period', 'month');

        // Validate period input
        if (! in_array($period, ['week', 'month', 'year'])) {
            $period = 'month';
        }

        $statistics = $this->statisticsService->getUserStatistics($user, $period);

        return Inertia::render('employee/MyStatistics', [
            'statistics' => $statistics,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * Get employee statistics data (API endpoint).
     *
     * Returns JSON statistics data for the selected period.
     * Useful for client-side refetching without full page reload.
     *
     * @param  Request  $request  HTTP request with optional 'period' query parameter
     * @return JsonResponse Statistics data in JSON format
     */
    public function api(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->input('period', 'month');

        // Validate period input
        if (! in_array($period, ['week', 'month', 'year'])) {
            $period = 'month';
        }

        $statistics = $this->statisticsService->getUserStatistics($user, $period);

        return response()->json([
            'statistics' => $statistics,
            'currentPeriod' => $period,
        ]);
    }
}
