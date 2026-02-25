<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\MapController; // TAMBAHKAN INI

// Public routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// API routes (public)
Route::get('/api/predictions', [PredictionController::class, 'getPredictions']);
Route::post('/api/predictions/refresh', [PredictionController::class, 'refreshPredictions']);

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/import', [DashboardController::class, 'import'])->name('dashboard.import');
    Route::get('/api/realtime-data', [DashboardController::class, 'getRealTimeData'])->name('api.realtime-data');
    Route::get('/weekly-prediction', [PredictionController::class, 'index'])->name('weekly-prediction');
    
    Route::get('/maps', [MapController::class, 'index'])->name('maps');
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Fallback
Route::fallback(function () {
    return redirect()->route('login');
});