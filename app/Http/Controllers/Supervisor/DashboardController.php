<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

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
}
