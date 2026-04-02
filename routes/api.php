<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Employee\StatisticsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
    Route::post('/trips/{trip}/speed-logs', [TripController::class, 'storeSpeedLogs'])->name('trips.speed-logs.store');

    Route::get('/statistics/my-stats', [StatisticsController::class, 'api'])->name('statistics.my-stats');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
