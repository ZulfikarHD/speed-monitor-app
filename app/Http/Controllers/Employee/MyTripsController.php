<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\ListTripsRequest;
use App\Models\Setting;
use App\Models\Trip;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| My Trips Controller
|--------------------------------------------------------------------------
|
| Handles employee trip history page with pagination and filtering.
| Displays past trips with date range and status filters for easy
| trip review and driving history access.
|
*/
class MyTripsController extends Controller
{
    /**
     * Display paginated trip history for authenticated employee.
     *
     * Shows list of user's trips with filtering capabilities (date range,
     * status). Employees can only view their own trips. Implements server-
     * side pagination for performance with large trip histories.
     *
     * @param  ListTripsRequest  $request  Validated query parameters
     * @return Response Inertia response with trip data and pagination
     */
    public function index(ListTripsRequest $request): Response
    {
        $this->authorize('viewAny', Trip::class);

        $query = Trip::query()->with('user:id,name,email');

        // Employees can only see their own trips
        $user = auth()->user();
        $query->where('user_id', $user->id);

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

        // Apply vehicle type filter
        if ($request->has('vehicle_type')) {
            $query->where('vehicle_type', $request->input('vehicle_type'));
        }

        // Apply shift type filter
        if ($request->has('shift_type')) {
            $query->where('shift_type', $request->input('shift_type'));
        }

        // Order by most recent first
        $query->orderBy('started_at', 'desc');

        $perPage = $request->input('per_page', 20);
        $trips = $query->paginate($perPage);

        $speedLimit = Setting::where('key', 'speed_limit')->value('value') ?? 60;

        return Inertia::render('employee/MyTrips', [
            'speedLimit' => (int) $speedLimit,
            'trips' => $trips->items(),
            'meta' => [
                'current_page' => $trips->currentPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
                'last_page' => $trips->lastPage(),
            ],
            'filters' => [
                'status' => $request->input('status', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
                'vehicle_type' => $request->input('vehicle_type', ''),
                'shift_type' => $request->input('shift_type', ''),
            ],
        ]);
    }
}
