<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\MyTripsController;
use App\Http\Controllers\Employee\SpeedometerController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Guest routes (login page and submission)
Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Employee routes (auth + employee role required)
Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/employee/speedometer', [SpeedometerController::class, 'index'])->name('employee.speedometer');
    Route::get('/employee/my-trips', [MyTripsController::class, 'index'])->name('employee.my-trips');
    Route::get('/employee/trips/{trip}', [TripController::class, 'showWeb'])->name('employee.trips.show');
});

// Supervisor routes (auth + supervisor role required)
Route::middleware(['auth', 'role:supervisor'])->group(function () {
    Route::inertia('/supervisor/dashboard', 'supervisor/Dashboard')->name('supervisor.dashboard');
});

// Admin routes (auth + admin role required)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');
});
