<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

/*
|--------------------------------------------------------------------------
| Settings Controller
|--------------------------------------------------------------------------
|
| Manages application configuration settings including speed limits,
| auto-stop duration, and speed log intervals. All authenticated users
| can view settings, but only admins can modify them.
|
*/
class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    /**
     * Get all application settings.
     *
     * Returns all configuration settings as key-value pairs. Available to
     * all authenticated users because employees need to know the current
     * speed limit and other parameters for their trip tracking.
     *
     * @return JsonResponse Settings as key-value pairs (200)
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $settings = $this->settingsService->getAllSettings();

        return response()->json([
            'data' => $settings,
        ], 200);
    }

    /**
     * Update application settings in bulk.
     *
     * Allows admins to update multiple settings at once. All fields are
     * optional to support partial updates. Only valid setting keys are
     * processed, invalid keys are silently ignored.
     *
     * @param  UpdateSettingsRequest  $request  Validated settings data
     * @return JsonResponse Updated settings and success message (200)
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validated();
        $settings = $this->settingsService->updateSettings($validated);

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $settings,
        ], 200);
    }
}
