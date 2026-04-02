<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Profile Controller
|--------------------------------------------------------------------------
|
| Handles user profile management endpoints for viewing and updating
| profile information and password changes. Accessible by all authenticated
| users for managing their own account settings.
|
*/
class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    /**
     * Display user profile page.
     *
     * Renders profile settings page with current user data
     * for authenticated user to view and edit their information.
     *
     * @param  Request  $request  Authenticated request
     * @return Response Inertia profile page with user data
     */
    public function show(Request $request): Response
    {
        return Inertia::render('Profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user profile information.
     *
     * Updates authenticated user's name and email with validation.
     * Returns success message and refreshes auth data in frontend.
     *
     * @param  UpdateProfileRequest  $request  Validated profile data
     * @return RedirectResponse Redirects back with success message
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $updatedUser = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Update user password.
     *
     * Changes authenticated user's password after current password verification.
     * Returns success message on successful password change.
     *
     * @param  ChangePasswordRequest  $request  Validated password data
     * @return RedirectResponse Redirects back with success message
     */
    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $this->profileService->updatePassword(
            $request->user(),
            $request->validated()
        );

        return back()->with('success', 'Password changed successfully');
    }
}
