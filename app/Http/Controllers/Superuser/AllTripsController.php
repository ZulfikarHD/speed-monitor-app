<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\ExportTripsRequest;
use App\Http\Requests\Trip\ListTripsRequest;
use App\Models\Trip;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| All Trips Controller (Superuser)
|--------------------------------------------------------------------------
|
| Handles superuser trip monitoring page with advanced filtering, sorting,
| and pagination. Displays trips from all employees with comprehensive
| filtering options for effective monitoring.
|
*/
class AllTripsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  ExportService  $exportService  Service for CSV generation
     */
    public function __construct(
        private ExportService $exportService
    ) {}

    /**
     * Display paginated list of all employee trips for superusers.
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

        return Inertia::render('superuser/AllTrips', [
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

    /**
     * Export trips to CSV file.
     *
     * Generates CSV file with filtered trips data following same filtering
     * logic as index() method. Applies filters for employee, date range,
     * status, and violations before generating export.
     *
     * @param  ExportTripsRequest  $request  Validated export parameters
     * @return HttpResponse CSV file download response
     */
    public function export(ExportTripsRequest $request): HttpResponse
    {
        $this->authorize('viewAny', Trip::class);

        $query = Trip::query()->with('user:id,name,email');

        // Apply same filters as index() method
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('started_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('started_at', '<=', $request->input('date_to'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('violations_only') && $request->boolean('violations_only')) {
            $query->where('violation_count', '>', 0);
        }

        // Sort by date (oldest first for exports)
        $query->orderBy('started_at', 'asc');

        // Get all matching trips (no pagination for exports)
        $trips = $query->get();

        // Generate CSV
        $csv = $this->exportService->generateTripsCsv($trips);

        // Generate filename with current date
        $filename = 'trips_export_'.now()->format('Y-m-d').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
