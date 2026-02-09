<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagementDashboardController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::withoutMiddleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Main Management Routes
Route::middleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    Route::get('/', [ManagementDashboardController::class, 'index'])->name('dashboard');
    
    // API Routes for Real-time Updates (Polling fallback)
    Route::get('/api/stats', [ManagementDashboardController::class, 'getStats'])->name('api.stats');

    // Vendor Dashboard
    Route::prefix('vendor')->name('dashboard.vendor.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\VendorController::class, 'index'])->name('index');
    });

    // Delivery Dashboard
    Route::prefix('delivery')->name('dashboard.delivery.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'index'])->name('index');
    });

    // Stock Dashboard
    Route::prefix('stock')->name('dashboard.stock.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\StockController::class, 'index'])->name('index');
    });

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
        Route::put('/username', [\App\Http\Controllers\ProfileController::class, 'updateUsername'])->name('update.username');
        Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('update.password');
    });
});
