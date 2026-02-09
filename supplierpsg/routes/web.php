<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});


// Global Search & Diagnostic
Route::get('/global-search', [App\Http\Controllers\Dashboard\GlobalSearchController::class, 'search'])->name('global.search');
Route::get('/system/diagnostic', [App\Http\Controllers\Dashboard\DiagnosticController::class, 'run'])->name('system.diagnostic');

// Profile Management (for Kabag and Management users)
Route::prefix('profile')->name('profile.')->middleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    Route::get('/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
    Route::put('/username', [App\Http\Controllers\ProfileController::class, 'updateUsername'])->name('update.username');
    Route::put('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('update.password');
});


// Routes for bahan baku proses (receiving/supply)
Route::prefix('bahanbaku')->name('bahanbaku.')->group(function () {
    // Dashboard Bahan Baku
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\BahanBakuDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('receiving')->name('receiving.')->group(function () {
        Route::get('/', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'store'])->name('store');
        
        // Receiving berbasis PO (konsep baru)
        Route::get('/create-by-po', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'createByPO'])->name('createByPO');
        Route::post('/store-by-po', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'storeByPO'])->name('storeByPO');
        Route::get('/{receiving}/labels-by-po', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'labelsByPO'])->name('labelsByPO');
        
        Route::get('/{receiving}/detail', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'detail'])->name('detail');
        Route::get('/{receiving}/labels', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'labels'])->name('labels');
        Route::get('/{receiving}/edit', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'edit'])->name('edit');
        Route::get('/{receiving}/delete', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'delete'])->name('delete');
        Route::put('/{receiving}', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'update'])->name('update');
        Route::delete('/{receiving}', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'destroy'])->name('destroy');
        
        // API untuk mencari manpower berdasarkan QR code
        Route::get('/api/manpower/qr', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'findManpowerByQr'])->name('api.manpower.qr');
        
        // API untuk mendapatkan bahan baku berdasarkan supplier
        Route::get('/api/bahanbaku/supplier', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'getBahanBakuBySupplier'])->name('api.bahanbaku.supplier');
        
        // API untuk fetch schedule by PO
        Route::get('/api/schedule-by-po', [App\Http\Controllers\BahanBaku\ReceivingController::class, 'fetchScheduleByPO'])->name('api.scheduleByPO');
    });
});


// Routes for Control Supplier (Monitoring per Item)
Route::prefix('controlsupplier')->name('controlsupplier.')->group(function () {
    // Dashboard Control Supplier
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\ControlSupplierDashboardController::class, 'index'])->name('dashboard');


    
    Route::get('/monitoring', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'monitoring'])->name('monitoring');
    Route::get('/import', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'importProcess'])->name('import.process');
    Route::post('/reset', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'resetData'])->name('reset');
    
    // API routes for editable cells
    Route::post('/update-ponumb', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'updatePONumb'])->name('updatePONumb');
    Route::post('/detach-ponumb', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'detachPONumb'])->name('detachPONumb');
    Route::post('/update-plan', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'updatePlan'])->name('updatePlan');
    
    // Auto-sync from receiving
    Route::post('/sync-receiving/{receivingId}', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'syncFromReceiving'])->name('syncReceiving');
    
    // Import SAP Excel
    Route::post('/import-sap', [App\Http\Controllers\BahanBaku\ControlSupplierController::class, 'importSAPExcel'])->name('importSAP');
});


// Authentication Routes - Redirect to S2S MWT
Route::get('/login', function () {
    return redirect()->away('http://mwtpsg.test/login');
})->name('login');

// Logout Route
Route::post('/logout', function () {
    \Auth::logout();
    session()->flush();
    return redirect()->away('http://mwtpsg.test/login');
})->name('logout');

