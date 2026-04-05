<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Employees Controller (Supervisor)
|--------------------------------------------------------------------------
|
| Handles employee management page with CRUD operations for user accounts.
| Supervisors can create, update, deactivate users and manage roles.
| Supports search, filtering, and pagination for effective user management.
|
*/
class EmployeesController extends Controller
{
    /**
     * User service instance for business logic.
     */
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display paginated list of all users for supervisors.
     *
     * Provides comprehensive user management with search by name/email,
     * filtering by role and status, and pagination for performance.
     *
     * @param  ListUsersRequest  $request  Validated query parameters
     * @return Response Inertia response with users data and metadata
     */
    public function index(ListUsersRequest $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => $request->input('search', ''),
            'role' => $request->input('role', ''),
            'status' => $request->input('status', ''),
            'per_page' => $request->input('per_page', 20),
        ];

        $users = $this->userService->getAllUsers($filters);

        return Inertia::render('supervisor/Employees', [
            'users' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            'filters' => [
                'search' => $filters['search'],
                'role' => $filters['role'],
                'status' => $filters['status'],
            ],
        ]);
    }

    /**
     * Store a newly created user in the database.
     *
     * Creates new user account with validated data including
     * credentials, role assignment, and account status.
     *
     * @param  StoreUserRequest  $request  Validated user creation data
     * @return RedirectResponse Redirect back with success message
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->userService->createUser($request->validated());

        return redirect()->back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /**
     * Update the specified user in the database.
     *
     * Updates user information including profile, role, status,
     * and optionally password. Prevents supervisor from updating themselves.
     *
     * @param  UpdateUserRequest  $request  Validated update data
     * @param  User  $user  User instance to update
     * @return RedirectResponse Redirect back with success message
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->updateUser($user, $request->validated());

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Deactivate the specified user account.
     *
     * Soft deactivation by setting is_active to false.
     * Prevents supervisor from deactivating themselves.
     *
     * @param  User  $user  User instance to deactivate
     * @return RedirectResponse Redirect back with success message
     */
    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $this->userService->deactivateUser($user);

        return redirect()->back()->with('success', 'Karyawan berhasil dinonaktifkan.');
    }
}
