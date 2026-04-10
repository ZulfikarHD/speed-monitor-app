<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\ExportTripsRequest;
use App\Http\Requests\Trip\ListTripsRequest;
use App\Models\Setting;
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

        // Apply vehicle type filter
        if ($request->has('vehicle_type')) {
            $query->where('vehicle_type', $request->input('vehicle_type'));
        }

        // Apply shift type filter
        if ($request->has('shift_type')) {
            $query->where('shift_type', $request->input('shift_type'));
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

        $speedLimit = (int) (Setting::where('key', 'speed_limit')->value('value') ?? 60);

        return Inertia::render('superuser/AllTrips', [
            'speedLimit' => $speedLimit,
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
                'vehicle_type' => $request->input('vehicle_type', ''),
                'shift_type' => $request->input('shift_type', ''),
            ],
            'sort' => [
                'by' => $sortBy,
                'order' => $sortOrder,
            ],
            'chartData' => Inertia::defer(fn () => $this->getChartData($request, $speedLimit)),
        ]);
    }

    /**
     * Build chart data from the same filtered query.
     *
     * @param  ListTripsRequest  $request  Validated query parameters
     * @param  int  $speedLimit  Speed limit from settings
     * @return array{avgSpeedVsStandard: array, maxSpeedVsStandard: array, violationsByEmployee: array, vehicleDistribution: array}
     */
    private function getChartData(ListTripsRequest $request, int $speedLimit): array
    {
        $chartQuery = Trip::query()->with('user:id,name');

        if ($request->has('user_id')) {
            $chartQuery->where('user_id', $request->input('user_id'));
        }

        if ($request->has('date_from')) {
            $chartQuery->whereDate('started_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $chartQuery->whereDate('started_at', '<=', $request->input('date_to'));
        }

        if ($request->has('status')) {
            $chartQuery->where('status', $request->input('status'));
        }

        if ($request->has('violations_only') && $request->boolean('violations_only')) {
            $chartQuery->where('violation_count', '>', 0);
        }

        if ($request->has('vehicle_type')) {
            $chartQuery->where('vehicle_type', $request->input('vehicle_type'));
        }

        if ($request->has('shift_type')) {
            $chartQuery->where('shift_type', $request->input('shift_type'));
        }

        $allTrips = $chartQuery->orderBy('started_at', 'desc')->limit(50)->get();

        $avgSpeedVsStandard = $allTrips->map(fn ($trip) => [
            'label' => $trip->user?->name ?? 'Unknown',
            'date' => $trip->started_at->format('d/m'),
            'avg_speed' => round((float) ($trip->average_speed ?? 0), 1),
            'speed_limit' => $speedLimit,
        ])->reverse()->values();

        $maxSpeedVsStandard = $allTrips->map(fn ($trip) => [
            'label' => $trip->user?->name ?? 'Unknown',
            'date' => $trip->started_at->format('d/m'),
            'max_speed' => round((float) ($trip->max_speed ?? 0), 1),
            'speed_limit' => $speedLimit,
        ])->reverse()->values();

        $violationsByEmployee = Trip::query()
            ->selectRaw('user_id, SUM(violation_count) as total_violations')
            ->with('user:id,name')
            ->when($request->has('date_from'), fn ($q) => $q->whereDate('started_at', '>=', $request->input('date_from')))
            ->when($request->has('date_to'), fn ($q) => $q->whereDate('started_at', '<=', $request->input('date_to')))
            ->when($request->has('vehicle_type'), fn ($q) => $q->where('vehicle_type', $request->input('vehicle_type')))
            ->when($request->has('shift_type'), fn ($q) => $q->where('shift_type', $request->input('shift_type')))
            ->groupBy('user_id')
            ->havingRaw('SUM(violation_count) > 0')
            ->orderByDesc('total_violations')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->user?->name ?? 'Unknown',
                'violations' => (int) $row->total_violations,
            ]);

        $vehicleBaseQuery = Trip::query()
            ->when($request->has('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->has('date_from'), fn ($q) => $q->whereDate('started_at', '>=', $request->input('date_from')))
            ->when($request->has('date_to'), fn ($q) => $q->whereDate('started_at', '<=', $request->input('date_to')))
            ->when($request->has('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->has('violations_only') && $request->boolean('violations_only'), fn ($q) => $q->where('violation_count', '>', 0))
            ->when($request->has('shift_type'), fn ($q) => $q->where('shift_type', $request->input('shift_type')));

        $vehicleDistribution = [
            'mobil' => (int) (clone $vehicleBaseQuery)->where('vehicle_type', 'mobil')->count(),
            'motor' => (int) (clone $vehicleBaseQuery)->where('vehicle_type', 'motor')->count(),
        ];

        return [
            'avgSpeedVsStandard' => $avgSpeedVsStandard,
            'maxSpeedVsStandard' => $maxSpeedVsStandard,
            'violationsByEmployee' => $violationsByEmployee,
            'vehicleDistribution' => $vehicleDistribution,
        ];
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

        if ($request->has('shift_type')) {
            $query->where('shift_type', $request->input('shift_type'));
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
