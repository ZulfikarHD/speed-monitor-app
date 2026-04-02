<?php

use App\Http\Controllers\Auth\AuthController;
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
