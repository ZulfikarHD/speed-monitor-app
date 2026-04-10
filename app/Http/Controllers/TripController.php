<?php

namespace App\Http\Controllers;

use App\Enums\TripStatus;
use App\Http\Requests\Trip\BulkCreateSpeedLogsRequest;
use App\Http\Requests\Trip\EndTripRequest;
use App\Http\Requests\Trip\ListTripsRequest;
use App\Http\Requests\Trip\StartTripRequest;
use App\Models\Setting;
use App\Models\Trip;
use App\Services\SpeedLogService;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Trip Controller
|--------------------------------------------------------------------------
|
| Handles trip management endpoints for starting, ending, listing, and
| viewing trips. Implements role-based authorization ensuring employees
| can only manage their own trips while superusers and admins have
| broader access for monitoring purposes.
|
*/
class TripController extends Controller
{
    public function __construct(
        private TripService $tripService,
        private SpeedLogService $speedLogService
    ) {}

    /**
     * List trips with filtering and pagination.
     *
     * Employees can only view their own trips. Superusers and admins
     * can view all trips with optional user_id filtering for monitoring.
     * Supports date range, status filtering, and pagination.
     *
     * @param  ListTripsRequest  $request  Validated query parameters
     * @return JsonResponse Paginated trip list with user relationships (200)
     */
    public function index(ListTripsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Trip::class);

        $query = Trip::query()->with('user:id,name,email')->withCount('speedLogs');

        // Employees can only see their own trips
        $user = auth()->user();
        if ($user->isEmployee() && ! $user->isSuperuser() && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        } else {
            // Superusers and admins can filter by user_id
            if ($request->has('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }
        }

        // Apply status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply date range filters
        if ($request->has('date_from')) {
            $query->whereDate('started_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('started_at', '<=', $request->input('date_to'));
        }

        // Order by most recent first
        $query->orderBy('started_at', 'desc');

        $perPage = $request->input('per_page', 20);
        $trips = $query->paginate($perPage);

        return response()->json([
            'data' => $trips->items(),
            'meta' => [
                'current_page' => $trips->currentPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
                'last_page' => $trips->lastPage(),
            ],
        ], 200);
    }

    /**
     * Start a new trip for the authenticated user.
     *
     * Creates a new trip in InProgress status. Prevents multiple active
     * trips by checking for existing in-progress trips before creation.
     *
     * @param  StartTripRequest  $request  Validated trip data (optional notes)
     * @return JsonResponse Created trip data (201) or validation error (422)
     */
    public function store(StartTripRequest $request): JsonResponse
    {
        $this->authorize('create', Trip::class);

        // Check if user already has an active trip
        $activeTrip = Trip::where('user_id', auth()->id())
            ->where('status', TripStatus::InProgress)
            ->first();

        if ($activeTrip) {
            return response()->json([
                'message' => 'You already have an active trip. Please stop it before starting a new one.',
                'active_trip' => $activeTrip,
            ], 422);
        }

        $trip = $this->tripService->startTrip(
            auth()->user(),
            $request->input('notes'),
            $request->only(['shift_type', 'vehicle_type'])
        );

        return response()->json([
            'trip' => $trip,
        ], 201);
    }

    /**
     * Get detailed trip information including speed logs.
     *
     * Returns complete trip data with associated user and speed log records.
     * Trip owner, superusers, and admins can view trip details.
     *
     * @param  Trip  $trip  The trip to view (route model binding)
     * @return JsonResponse Trip details with user and speed logs (200)
     */
    public function show(Trip $trip): JsonResponse
    {
        $this->authorize('view', $trip);

        $trip->load(['user:id,name,email', 'speedLogs']);

        return response()->json([
            'trip' => $trip,
        ], 200);
    }

    /**
     * Display trip detail page with speed chart visualization (Web view).
     *
     * Shows comprehensive trip information including speed logs, statistics,
     * and violation markers in an Inertia-rendered Vue page. Uses route model
     * binding for automatic Trip lookup and eager loads speed_logs relationship
     * to avoid N+1 queries.
     *
     * @param  Trip  $trip  The trip to display (route model binding)
     * @return Response Inertia response with trip and speedLimit data
     */
    public function showWeb(Trip $trip): Response
    {
        $this->authorize('view', $trip);

        // Eager load relationships to prevent N+1 queries
        $trip->load(['user:id,name,email', 'speedLogs']);

        // Fetch speed limit from settings
        $speedLimit = Setting::where('key', 'speed_limit')->value('value') ?? 60;

        return Inertia::render('employee/TripDetail', [
            'trip' => $trip,
            'speedLimit' => (int) $speedLimit,
        ]);
    }

    /**
     * End an active trip and calculate final statistics.
     *
     * Updates trip status to Completed, calculates duration and statistics
     * from speed logs. Only trip owner can end their own trip. Trip must
     * be in InProgress status.
     *
     * @param  EndTripRequest  $request  Validated request (optional notes)
     * @param  Trip  $trip  The trip to end (route model binding)
     * @return JsonResponse Updated trip with statistics (200) or validation error (422)
     */
    public function update(EndTripRequest $request, Trip $trip): JsonResponse
    {
        $this->authorize('update', $trip);

        // Validate trip is in progress
        if ($trip->status !== TripStatus::InProgress) {
            return response()->json([
                'message' => 'Only trips in progress can be ended',
            ], 422);
        }

        $trip = $this->tripService->endTrip($trip);

        // Update notes if provided
        if ($request->has('notes')) {
            $trip->update(['notes' => $request->input('notes')]);
            $trip->refresh();
        }

        return response()->json([
            'trip' => $trip,
        ], 200);
    }

    /**
     * Bulk insert speed logs for a trip.
     *
     * Accepts multiple speed log entries and performs efficient bulk insert
     * to database. Primarily used for offline sync when devices reconnect.
     * Updates trip statistics and synced_at timestamp after insertion.
     *
     * @param  BulkCreateSpeedLogsRequest  $request  Validated speed logs array
     * @param  Trip  $trip  The trip to add speed logs to (route model binding)
     * @return JsonResponse Created speed logs count and updated trip (200) or validation error (422)
     */
    public function storeSpeedLogs(BulkCreateSpeedLogsRequest $request, Trip $trip): JsonResponse
    {
        $this->authorize('addSpeedLogs', $trip);

        // Validate trip is in progress
        if ($trip->status !== TripStatus::InProgress) {
            return response()->json([
                'message' => 'Only trips in progress can accept speed logs',
            ], 422);
        }

        $speedLogs = $this->speedLogService->bulkInsert(
            $trip,
            $request->input('speed_logs')
        );

        // Update synced_at timestamp for offline sync tracking
        $trip->update(['synced_at' => now()]);
        $trip->refresh();

        return response()->json([
            'message' => 'Speed logs created successfully',
            'created_count' => $speedLogs->count(),
            'trip' => [
                'id' => $trip->id,
                'max_speed' => $trip->max_speed,
                'average_speed' => $trip->average_speed,
                'total_distance' => $trip->total_distance,
                'violation_count' => $trip->violation_count,
                'synced_at' => $trip->synced_at,
                'speed_logs_count' => $trip->speedLogs()->count(),
            ],
        ], 200);
    }
}
