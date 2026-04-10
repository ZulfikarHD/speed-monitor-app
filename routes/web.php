<?php

use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\MyTripsController;
use App\Http\Controllers\Employee\SpeedometerController;
use App\Http\Controllers\Employee\StatisticsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superuser\AllTripsController;
use App\Http\Controllers\Superuser\DashboardController as SuperuserDashboardController;
use App\Http\Controllers\Superuser\EmployeesController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Root route - redirect to login or dashboard based on auth state
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Guest routes (login page and submission)
Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Profile routes (all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::inertia('/profile/change-password', 'ChangePassword')->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Employee routes (auth + employee role required)
Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/employee/speedometer', [SpeedometerController::class, 'index'])->name('employee.speedometer');
    Route::get('/employee/my-trips', [MyTripsController::class, 'index'])->name('employee.my-trips');
    Route::get('/employee/statistics', [StatisticsController::class, 'index'])->name('employee.statistics');
});

// Shared trip detail route (accessible by employees, superusers, and admins)
// Authorization is handled by TripPolicy which allows:
// - Employees to view their own trips
// - Superusers and admins to view any trip
Route::middleware('auth')->group(function () {
    Route::get('/employee/trips/{trip}', [TripController::class, 'showWeb'])->name('employee.trips.show');
});

// Superuser routes (auth + superuser or admin role required)
Route::middleware(['auth', 'role:superuser,admin'])->group(function () {
    Route::get('/superuser/dashboard', [SuperuserDashboardController::class, 'index'])->name('superuser.dashboard');
    Route::get('/superuser/speedometer', [SpeedometerController::class, 'index'])->name('superuser.speedometer');
    Route::get('/superuser/trips', [AllTripsController::class, 'index'])->name('superuser.trips');
    Route::get('/superuser/trips/export', [AllTripsController::class, 'export'])->name('superuser.trips.export');
    Route::get('/superuser/leaderboard', [SuperuserDashboardController::class, 'violations'])->name('superuser.leaderboard');
    Route::get('/superuser/employees', [EmployeesController::class, 'index'])->name('superuser.employees');
    Route::post('/superuser/employees', [EmployeesController::class, 'store'])->name('superuser.employees.store');
    Route::put('/superuser/employees/{user}', [EmployeesController::class, 'update'])->name('superuser.employees.update');
    Route::delete('/superuser/employees/{user}', [EmployeesController::class, 'deactivate'])->name('superuser.employees.deactivate');
});

// Superuser/Admin shared routes (auth + superuser or admin role required)
Route::middleware(['auth', 'role:superuser,admin'])->group(function () {
    Route::get('/admin/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
    Route::put('/admin/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
});

// Admin-only routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');
});
