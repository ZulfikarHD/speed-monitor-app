<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Employee Speedometer Controller
|--------------------------------------------------------------------------
|
| Handles speedometer page rendering with GPS-based speed tracking.
| Provides real-time speed monitoring, trip controls, and violation
| detection for employee commute tracking.
|
*/
class SpeedometerController extends Controller
{
    /**
     * Display speedometer page with application settings.
     *
     * Renders GPS speedometer interface for real-time speed tracking. Passes
     * speed limit and auto-stop duration from settings to enable client-side
     * violation detection and auto-stop functionality.
     *
     * @return Response Inertia response with speedometer settings
     */
    public function index(): Response
    {
        // Fetch application settings for client-side functionality
        $speedLimit = Setting::where('key', 'speed_limit')->value('value') ?? 60;
        $autoStopDuration = Setting::where('key', 'auto_stop_duration')->value('value') ?? 1800;

        return Inertia::render('employee/Speedometer', [
            'speedLimit' => (int) $speedLimit,
            'autoStopDuration' => (int) $autoStopDuration,
        ]);
    }
}
