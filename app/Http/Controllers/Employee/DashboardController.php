<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Employee Dashboard Controller
|--------------------------------------------------------------------------
|
| Handles employee dashboard page rendering. Displays welcome screen with
| quick navigation to speedometer and trip history features.
|
*/
class DashboardController extends Controller
{
    /**
     * Display employee dashboard.
     *
     * Shows welcome screen with quick action cards for speedometer and trip
     * history. User information is available via Pinia auth store on frontend.
     *
     * @return Response Inertia response rendering employee dashboard
     */
    public function index(): Response
    {
        return Inertia::render('employee/Dashboard');
    }
}
