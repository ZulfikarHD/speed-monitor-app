<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/login', 'auth/Login')->name('login');

// Employee routes (auth + employee role required)
Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::inertia('/employee/dashboard', 'employee/Dashboard')->name('employee.dashboard');
    Route::inertia('/employee/speedometer', 'employee/Speedometer')->name('employee.speedometer');
});

// Supervisor routes (auth + supervisor role required)
Route::middleware(['auth', 'role:supervisor'])->group(function () {
    Route::inertia('/supervisor/dashboard', 'supervisor/Dashboard')->name('supervisor.dashboard');
});

// Admin routes (auth + admin role required)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');
});

// Test pages (development only)
Route::inertia('/test', 'test/TestIndex')->name('test.index');
Route::inertia('/test/geolocation', 'test/GeolocationTest')->name('test.geolocation');
Route::inertia('/test/speed-gauge-demo', 'test/SpeedGaugeDemo')->name('test.speed-gauge-demo');
Route::inertia('/test/trip-controls-demo', 'test/TripControlsDemo')->name('test.trip-controls-demo');
Route::inertia('/test/trip-stats-demo', 'test/TripStatsDemo')->name('test.trip-stats-demo');
