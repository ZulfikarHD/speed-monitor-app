<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Settings Controller (Admin)
|--------------------------------------------------------------------------
|
| Handles settings page view for admins to configure application-wide
| parameters including speed limits, auto-stop duration, and logging intervals.
|
*/
class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    /**
     * Show settings page.
     *
     * Returns Inertia view with current settings pre-loaded for editing.
     * Admin-only access enforced via policy.
     *
     * @return Response Inertia response with settings data
     */
    public function index(): Response
    {
        $this->authorize('update', Setting::class);

        $settings = $this->settingsService->getAllSettings();

        return Inertia::render('admin/Settings', [
            'settings' => $settings,
        ]);
    }
}
