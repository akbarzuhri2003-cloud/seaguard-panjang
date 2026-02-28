<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\MapController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
    Route::get('/export-knn-data', [MapController::class, 'exportKnnData'])->name('export.knn');
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Fallback
Route::fallback(function () {
    return redirect()->route('login');
});

// EMERGENCY SETUP ROUTE
Route::get('/setup-admin', function () {
    try {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@seaguard.id'],
            [
                'name' => 'Admin SeaGuard',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]
        );
        return "✅ User admin berhasil dipastikan ada di database Railway! Silakan <a href='/'>Login ke sini</a> dengan email: admin@seaguard.id dan password: password123";
    } catch (\Exception $e) {
        return "❌ Gagal membuat user: " . $e->getMessage();
    }
});