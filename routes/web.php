<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/login', 'auth/Login')->name('login');

Route::inertia('/employee/dashboard', 'employee/Dashboard')->name('employee.dashboard');
Route::inertia('/supervisor/dashboard', 'supervisor/Dashboard')->name('supervisor.dashboard');
Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');

// Test pages (development only)
Route::inertia('/test', 'test/TestIndex')->name('test.index');
Route::inertia('/test/geolocation', 'test/GeolocationTest')->name('test.geolocation');
Route::inertia('/test/speed-gauge-demo', 'test/SpeedGaugeDemo')->name('test.speed-gauge-demo');
Route::inertia('/test/trip-controls-demo', 'test/TripControlsDemo')->name('test.trip-controls-demo');
Route::inertia('/test/trip-stats-demo', 'test/TripStatsDemo')->name('test.trip-stats-demo');
