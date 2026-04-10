<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Settings Controller (Admin)
|--------------------------------------------------------------------------
|
| Handles settings page view and form submission for admins and superusers
| to configure application-wide parameters including speed limits, auto-stop
| duration, and logging intervals.
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
     * Superuser and admin access enforced via policy.
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

    /**
     * Update application settings.
     *
     * Handles form submission from Settings.vue, updates settings in database,
     * and redirects back with success message.
     *
     * @param  UpdateSettingsRequest  $request  Validated settings data
     * @return RedirectResponse Redirect back to settings page with flash
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validated();
        $this->settingsService->updateSettings($validated);

        return redirect()
            ->back()
            ->with('success', 'Pengaturan berhasil disimpan');
    }
}
