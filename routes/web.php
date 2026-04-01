<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/login', 'auth/Login')->name('login');

Route::inertia('/employee/dashboard', 'employee/Dashboard')->name('employee.dashboard');
Route::inertia('/supervisor/dashboard', 'supervisor/Dashboard')->name('supervisor.dashboard');
Route::inertia('/admin/dashboard', 'admin/Dashboard')->name('admin.dashboard');

// Test pages (development only)
Route::inertia('/test/geolocation', 'test/GeolocationTest')->name('test.geolocation');
