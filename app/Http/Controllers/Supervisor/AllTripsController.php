<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\ListTripsRequest;
use App\Models\Trip;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| All Trips Controller (Supervisor)
|--------------------------------------------------------------------------
|
| Handles supervisor trip monitoring page with advanced filtering, sorting,
| and pagination. Displays trips from all employees with comprehensive
| filtering options for effective monitoring.
|
*/
class AllTripsController extends Controller
{
    /**
     * Display paginated list of all employee trips for supervisors.
     *
     * Provides comprehensive trip monitoring with filtering by employee,
     * date range, status, and violations. Supports sorting by various
     * metrics and pagination for performance.
     *
     * @param  ListTripsRequest  $request  Validated query parameters
     * @return Response Inertia response with trips data and metadata
     */
    public function index(ListTripsRequest $request): Response
    {
        $this->authorize('viewAny', Trip::class);

        $query = Trip::query()->with('user:id,name,email');

        // Apply employee filter
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Apply date range filters
        if ($request->has('date_from')) {
            $query->whereDate('started_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('started_at', '<=', $request->input('date_to'));
        }

        // Apply status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply violations filter
        if ($request->has('violations_only') && $request->boolean('violations_only')) {
            $query->where('violation_count', '>', 0);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'started_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->input('per_page', 20);
        $trips = $query->paginate($perPage);

        // Get employees list for filter dropdown
        $employees = User::where('role', 'employee')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('supervisor/AllTrips', [
            'trips' => $trips->items(),
            'employees' => $employees,
            'meta' => [
                'current_page' => $trips->currentPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
                'last_page' => $trips->lastPage(),
            ],
            'filters' => [
                'user_id' => $request->input('user_id', null),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
                'status' => $request->input('status', ''),
                'violations_only' => $request->boolean('violations_only', false),
            ],
            'sort' => [
                'by' => $sortBy,
                'order' => $sortOrder,
            ],
        ]);
    }
}
