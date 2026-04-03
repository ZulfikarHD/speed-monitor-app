<?php

use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\MyTripsController;
use App\Http\Controllers\Employee\SpeedometerController;
use App\Http\Controllers\Employee\StatisticsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Supervisor\AllTripsController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
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
    Route::get('/employee/trips/{trip}', [TripController::class, 'showWeb'])->name('employee.trips.show');
    Route::get('/employee/statistics', [StatisticsController::class, 'index'])->name('employee.statistics');
});

// Supervisor routes (auth + supervisor role required)
Route::middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/supervisor/trips', [AllTripsController::class, 'index'])->name('supervisor.trips');
    Route::get('/supervisor/leaderboard', [SupervisorDashboardController::class, 'violations'])->name('supervisor.leaderboard');
});

// Supervisor/Admin shared routes (auth + supervisor or admin role required)
Route::middleware(['auth', 'role:supervisor,admin'])->group(function () {
    Route::get('/admin/employees', [EmployeesController::class, 'index'])->name('admin.employees');
    Route::post('/admin/employees', [EmployeesController::class, 'store'])->name('admin.employees.store');
    Route::put('/admin/employees/{user}', [EmployeesController::class, 'update'])->name('admin.employees.update');
    Route::delete('/admin/employees/{user}', [EmployeesController::class, 'deactivate'])->name('admin.employees.deactivate');
    Route::get('/admin/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
});

// Admin-only routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');
});
