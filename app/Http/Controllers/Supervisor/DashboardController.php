<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Supervisor Dashboard Controller
|--------------------------------------------------------------------------
|
| Handles supervisor/admin dashboard data retrieval with caching
| and authorization for monitoring employee trip compliance.
|
*/
class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display supervisor dashboard page.
     *
     * Returns Inertia view for dashboard with optional initial data.
     * Client-side will fetch fresh data via overview() API endpoint
     * and auto-refresh every 30 seconds for real-time monitoring.
     *
     * @return Response Inertia response rendering supervisor dashboard
     */
    public function index(): Response
    {
        $this->authorize('viewDashboard', User::class);

        return Inertia::render('supervisor/Dashboard');
    }

    /**
     * Get dashboard overview statistics.
     *
     * Returns comprehensive dashboard data including today's summary,
     * active trips, top violators, and average speed. Data is cached
     * for 5 minutes to optimize performance under high load.
     *
     * @return JsonResponse Dashboard overview with all metrics
     */
    public function overview(): JsonResponse
    {
        $this->authorize('viewDashboard', User::class);

        $data = Cache::remember(
            'dashboard:overview',
            now()->addMinutes(5),
            fn () => $this->dashboardService->getOverview()
        );

        return response()->json($data);
    }

    /**
     * Display violation leaderboard page.
     *
     * Shows employees ranked by violation count within specified date range.
     * Allows supervisors and admins to identify problematic drivers and monitor
     * speed compliance trends across the organization.
     *
     * @param  Request  $request  HTTP request with optional date_from and date_to query params
     * @return Response Inertia response rendering violation leaderboard page
     */
    public function violations(Request $request): Response
    {
        $this->authorize('viewDashboard', User::class);

        $dateFrom = $request->query('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $leaderboard = $this->dashboardService->getViolationLeaderboard($dateFrom, $dateTo);

        return Inertia::render('supervisor/ViolationLeaderboard', [
            'leaderboard' => $leaderboard,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
