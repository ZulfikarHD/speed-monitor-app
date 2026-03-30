---
name: US-1.5 Login Frontend
overview: Implement the login page frontend with form validation, API integration, token management, and role-based redirection for the speed tracking application using Inertia.js v3 + Vue 3.
todos:
  - id: create-api-types
    content: Create TypeScript types for API requests/responses in types/api.ts
    status: completed
  - id: create-api-service
    content: Create API service with login/logout methods using fetch in services/api.ts
    status: cancelled
  - id: create-auth-composable
    content: Create useAuth composable wrapping auth store and API calls
    status: completed
  - id: create-login-page
    content: Create Login.vue page with form, validation, and error handling
    status: completed
  - id: create-placeholder-dashboards
    content: Create placeholder dashboard pages for employee, supervisor, and admin roles
    status: completed
  - id: add-web-routes
    content: Add Inertia routes for login and role-based dashboards in routes/web.php
    status: completed
  - id: test-login-flow
    content: Test complete login flow with all three user roles
    status: completed
  - id: run-linter
    content: Run yarn lint to fix any code style issues
    status: completed
isProject: false
---

# Plan: US-1.5 Login Page Frontend

## Current State Analysis

**Completed (US-1.4):**
- Backend authentication API endpoints (`/api/auth/login`, `/api/auth/logout`, `/api/auth/me`)
- [`AuthService.php`](app/Services/AuthService.php) with login/logout business logic
- [`AuthController.php`](app/Http/Controllers/Auth/AuthController.php) handling API requests
- [`LoginRequest.php`](app/Http/Requests/Auth/LoginRequest.php) with validation rules
- Pinia auth store at [`resources/js/stores/auth.ts`](resources/js/stores/auth.ts) with token management

**Test Users Available (from DatabaseSeeder):**
- Admin: `admin@example.com` (password: `password`)
- Supervisor: `supervisor@example.com` (password: `password`)
- Employee: `employee@example.com` (password: `password`)

**Tech Stack:**
- Inertia.js v3 + Vue 3 (SPA with server-side routing)
- Tailwind CSS v4 for styling
- Pinia for state management
- Laravel Sanctum for API authentication

## Implementation Strategy

This is an **Inertia.js application**, but uses **token-based authentication** (not typical session-based). The login flow will:
1. Make direct API call to `/api/auth/login`
2. Store token in localStorage via Pinia store
3. Use Inertia router for navigation to role-based pages

## Files to Create

### 1. Login Page Component
**File:** `resources/js/pages/auth/Login.vue`

Create Vue component with:
- Email and password input fields using proper form elements
- Client-side validation (required fields, email format, min password length)
- Loading state during API call
- Error message display for invalid credentials or server errors
- Submit handler that calls login API
- Responsive Tailwind CSS styling following the Welcome page design pattern

### 2. API Service for Authentication
**File:** `resources/js/services/api.ts`

Create HTTP client service:
- Base Axios-like configuration using native `fetch`
- `login(credentials)` method that POSTs to `/api/auth/login`
- `logout()` method that POSTs to `/api/auth/logout`
- `getCurrentUser()` method that GETs from `/api/auth/me`
- Error handling with typed responses
- Add Authorization header with stored token for authenticated requests

### 3. Auth Composable
**File:** `resources/js/composables/useAuth.ts`

Create reusable auth logic:
- Wraps auth store actions
- Provides `handleLogin()` method
- Provides `handleLogout()` method
- Provides `fetchCurrentUser()` method
- Returns loading/error states
- Integrates with Inertia router for redirects

### 4. Role-Based Landing Pages (Placeholders)

Since US-1.5 requires redirecting based on role, create placeholder pages:

**Employee Dashboard:**
- File: `resources/js/pages/employee/Dashboard.vue`
- Simple welcome message showing user name and role
- Logout button

**Supervisor Dashboard:**
- File: `resources/js/pages/supervisor/Dashboard.vue`
- Simple welcome message showing user name and role
- Logout button

**Admin Dashboard:**
- File: `resources/js/pages/admin/Dashboard.vue`
- Simple welcome message showing user name and role
- Logout button

### 5. Web Routes Configuration
**File:** `routes/web.php`

Add Inertia routes:
```php
Route::inertia('/login', 'auth/Login')->name('login');

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'auth/Login')->name('login');
});

// Protected routes (middleware will be added in future US)
Route::inertia('/employee/dashboard', 'employee/Dashboard')->name('employee.dashboard');
Route::inertia('/supervisor/dashboard', 'supervisor/Dashboard')->name('supervisor.dashboard');
Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');
```

### 6. Type Definitions
**File:** `resources/js/types/api.ts`

Add TypeScript types:
- `LoginCredentials` interface
- `LoginResponse` interface
- `ApiError` interface

## Acceptance Criteria Mapping

✅ **Login.vue component created** - New Vue component in `pages/auth/`
✅ **Email and password inputs** - Form fields with proper types
✅ **Form validation (required fields)** - Client-side validation before submission
✅ **Submit button triggers API call** - Async handler with `useAuth` composable
✅ **Token stored in localStorage** - Via existing Pinia auth store
✅ **Redirects to appropriate page based on role** - Inertia navigation to role-specific dashboard
✅ **Error messages displayed** - Toast/alert for failed login attempts
✅ **Loading state during API call** - Disabled button + spinner during submission

## Technical Decisions

1. **No Axios**: Use native `fetch` API since Axios is not installed
2. **Inertia Router**: Use `router.visit()` for navigation instead of Vue Router
3. **API vs Inertia Forms**: Use direct API calls (not Inertia form submission) to maintain token-based auth pattern
4. **Validation**: Client-side validation mirrors backend `LoginRequest` rules
5. **Error Handling**: Display user-friendly messages for common error scenarios

## Testing Approach

Manual testing checklist:
1. Visit `/login` route
2. Test empty form submission (validation errors)
3. Test invalid email format (validation error)
4. Test wrong credentials (API error message)
5. Test successful login as employee → redirects to employee dashboard
6. Test successful login as supervisor → redirects to supervisor dashboard
7. Test successful login as admin → redirects to admin dashboard
8. Verify token persists in localStorage after refresh
9. Test logout functionality

## Dependencies

- Existing auth store (`stores/auth.ts`)
- Existing API routes (`/api/auth/login`, etc.)
- Existing user seeders with test accounts
- Tailwind CSS for styling
- @inertiajs/vue3 for navigation

## Future Considerations (Not in Scope)

- Protected route middleware (will be US-1.6 or later)
- Remember me functionality
- Password reset flow
- Email verification
- Rate limiting on frontend
- CSRF token handling (Sanctum handles this)
