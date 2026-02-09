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


// Routes for finishgood stock
Route::get('finishgood/stock', [App\Http\Controllers\FinishGood\FinishGoodStockController::class, 'index'])->name('finishgood.stock.index');
Route::get('finishgood/stock/import', [App\Http\Controllers\FinishGood\FinishGoodStockController::class, 'importForm'])->name('finishgood.stock.import.form');
Route::post('finishgood/stock/import', [App\Http\Controllers\FinishGood\FinishGoodStockController::class, 'import'])->name('finishgood.stock.import');
Route::get('finishgood/stock/export', [App\Http\Controllers\FinishGood\FinishGoodStockController::class, 'export'])->name('finishgood.stock.export');
Route::put('finishgood/stock/update-limits/{part}', [App\Http\Controllers\FinishGood\FinishGoodStockController::class, 'updateLimits'])->name('finishgood.stock.update-limits');

// Routes for Stock Management (Opname & PO)
Route::prefix('shipping/stock')->name('stock.')->group(function () {
    // Opname
    Route::get('/opname', [App\Http\Controllers\Stock\StockOpnameController::class, 'index'])->name('opname.index');
    Route::post('/opname', [App\Http\Controllers\Stock\StockOpnameController::class, 'store'])->name('opname.store');
    Route::get('/opname/import', [App\Http\Controllers\Stock\StockOpnameController::class, 'importForm'])->name('opname.import.form');
    Route::post('/opname/import', [App\Http\Controllers\Stock\StockOpnameController::class, 'import'])->name('opname.import');
    Route::get('/opname/export', [App\Http\Controllers\Stock\StockOpnameController::class, 'export'])->name('opname.export');

    // Purchase Order
    Route::get('/po/import', [App\Http\Controllers\Stock\PurchaseOrderController::class, 'importForm'])->name('po.import.form');
    Route::post('/po/import', [App\Http\Controllers\Stock\PurchaseOrderController::class, 'import'])->name('po.import');
    Route::get('/po/export', [App\Http\Controllers\Stock\PurchaseOrderController::class, 'export'])->name('po.export');
    Route::resource('po', App\Http\Controllers\Stock\PurchaseOrderController::class)->except(['show']);
});

// Routes for finishgood (scan in)
Route::prefix('finishgood/in')->name('finishgood.in.')->group(function () {
    // Dashboard Finish Good In
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\FinishGoodInDashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'index'])->name('index');
    Route::get('/export', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'export'])->name('export');
    Route::get('/create', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'store'])->name('store');
    Route::get('/{finishGoodIn}/detail', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'detail'])->name('detail');
    Route::get('/{finishGoodIn}/edit', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'edit'])->name('edit');
    Route::get('/{finishGoodIn}/delete', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'delete'])->name('delete');
    Route::put('/{finishGoodIn}/update', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'update'])->name('update');
    Route::delete('/{finishGoodIn}/destroy', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'destroy'])->name('destroy');
    Route::delete('/{finishGoodIn}/destroy-lot', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'destroyLot'])->name('destroyLot');
    
    // API untuk cari operator/manpower berdasarkan QR code
    Route::get('/api/operator/{qrCode}', [App\Http\Controllers\FinishGood\FinishGoodController::class, 'getManpowerByQR'])->name('api.manpowerByQR');
});

// Routes for finishgood out (scan out)
Route::prefix('finishgood/out')->name('finishgood.out.')->group(function () {
    // Dashboard Finish Good Out
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\FinishGoodOutDashboardController::class, 'index'])->name('dashboard');
    
    // Modifying existing routes to include new scan features
    Route::get('/', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'index'])->name('index');
    Route::get('/export', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'export'])->name('export');
    Route::get('/scan/{spk}', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'scan'])->name('scan');
    Route::post('/scan/close-cycle/{spk}', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'closeCycle'])->name('close-cycle');
    Route::get('/print/{spk}', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'printDocument'])->name('print');
    
    // New Edit & Reset Routes (SPK based)
    Route::get('/{spk}/edit', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'edit'])->name('edit');
    Route::put('/{spk}/update', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'update'])->name('update');
    Route::post('/{spk}/reset', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'reset'])->name('reset');

    // CRUD ops (Legacy / Other)
    Route::get('/{spk}/create', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'store'])->name('store');
    
    Route::get('/{finishGoodOut}/detail', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'detail'])->name('detail');
    // REMOVED old edit/update/destroy routes to prevent conflict
    Route::get('/{finishGoodOut}/delete', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'delete'])->name('delete');
    Route::delete('/{finishGoodOut}/destroy', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'destroy'])->name('destroy');
    
    // API routes
    Route::get('/api/finish-good-in/{lotNumber}', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'getFinishGoodInByLotNumber'])->name('api.finishGoodInByLotNumber');
    Route::get('/api/spk/{spkId}/parts', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'getPartsBySpk'])->name('api.partsBySpk');
    Route::get('/api/parts/{spkId}', [App\Http\Controllers\FinishGood\FinishGoodOutController::class, 'getPartsBySpk'])->name('api.partsBySpk_alias'); // Alias for consistent access
});

// Routes for SPK (Surat Perintah Pengiriman)
Route::prefix('spk')->name('spk.')->group(function () {
    Route::get('/', [App\Http\Controllers\SPK\SPKController::class, 'index'])->name('index');
    Route::get('/trash', [App\Http\Controllers\SPK\SPKController::class, 'trash'])->name('trash'); // Trash Index
    Route::get('/create', [App\Http\Controllers\SPK\SPKController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\SPK\SPKController::class, 'store'])->name('store');
    
    // Import & Export
    Route::get('/import', [App\Http\Controllers\SPK\SPKController::class, 'importForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\SPK\SPKController::class, 'import'])->name('import'); 
    Route::get('/export', [App\Http\Controllers\SPK\SPKController::class, 'export'])->name('export');

    // Bulk Actions
    Route::post('/bulk-delete', [App\Http\Controllers\SPK\SPKController::class, 'bulkDelete'])->name('bulk_delete');
    Route::delete('/destroy-all', [App\Http\Controllers\SPK\SPKController::class, 'destroyAll'])->name('destroy_all');

    Route::get('/{spk}/detail', [App\Http\Controllers\SPK\SPKController::class, 'detail'])->name('detail');
    Route::get('/{spk}/edit', [App\Http\Controllers\SPK\SPKController::class, 'edit'])->name('edit');
    Route::get('/{spk}/delete', [App\Http\Controllers\SPK\SPKController::class, 'delete'])->name('delete');
    Route::put('/{spk}/update', [App\Http\Controllers\SPK\SPKController::class, 'update'])->name('update');
    Route::delete('/{spk}/destroy', [App\Http\Controllers\SPK\SPKController::class, 'destroy'])->name('destroy');
    
    // Restore and Force Delete (using ID because model binding might fail for trashed items if not configured)
    Route::post('/{id}/restore', [App\Http\Controllers\SPK\SPKController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [App\Http\Controllers\SPK\SPKController::class, 'forceDelete'])->name('force_delete');
    
    // Bulk Trash Actions
    Route::post('/restore-all', [App\Http\Controllers\SPK\SPKController::class, 'restoreAll'])->name('restore_all');
    Route::delete('/force-delete-all', [App\Http\Controllers\SPK\SPKController::class, 'forceDeleteAll'])->name('force_delete_all');
    
    // API routes
    Route::get('/api/plantgates', [App\Http\Controllers\SPK\SPKController::class, 'getPlantgatesByCustomer'])->name('api.plantgates');
    Route::get('/api/parts', [App\Http\Controllers\SPK\SPKController::class, 'getPartsByPlantgate'])->name('api.parts');
});

// Routes for Shipping - Admin (Control Truck Board)
Route::prefix('shipping/controltruck')->name('shipping.controltruck.')->group(function () {
    Route::get('/monitoring', [App\Http\Controllers\Shipping\ControlTruckController::class, 'monitoring'])->name('monitoring');
    
    // API routes for updating time
    Route::post('/update-time', [App\Http\Controllers\Shipping\ControlTruckController::class, 'updateTime'])->name('updateTime');
    
    // API routes for updating status per jam
    Route::post('/update-status', [App\Http\Controllers\Shipping\ControlTruckController::class, 'updateStatus'])->name('updateStatus');

    // API search driver & update info
    Route::get('/search-driver', [App\Http\Controllers\Shipping\ControlTruckController::class, 'searchDriver'])->name('searchDriver');
    Route::get('/search-customer', [App\Http\Controllers\Shipping\ControlTruckController::class, 'searchCustomer'])->name('searchCustomer');
    Route::post('/update-truck-info', [App\Http\Controllers\Shipping\ControlTruckController::class, 'updateTruckInfo'])->name('updateTruckInfo');
    Route::post('/assign-spk', [App\Http\Controllers\Shipping\ControlTruckController::class, 'assignSpk'])->name('assignSpk');
});

// Routes for Shipping - Tracker (New Experimental)
Route::prefix('shipping/tracker')->name('shipping.tracker.')->group(function () {
    Route::get('/', [App\Http\Controllers\Shipping\TrackerController::class, 'index'])->name('index'); // Admin Map
    Route::get('/track/{delivery}', [App\Http\Controllers\Shipping\TrackerController::class, 'track'])->name('track'); // Driver View
    Route::post('/store', [App\Http\Controllers\Shipping\TrackerController::class, 'storeLocation'])->name('store'); // API Store
    Route::get('/locations', [App\Http\Controllers\Shipping\TrackerController::class, 'getDeliveryLocations'])->name('locations'); // API Fetch
});

// Routes for Shipping - Dispatch (Finalize Driver & Truck)
Route::prefix('shipping/dispatch')->name('shipping.dispatch.')->group(function () {
    Route::get('/', [App\Http\Controllers\Shipping\DispatchController::class, 'index'])->name('index');
    Route::post('/assign', [App\Http\Controllers\Shipping\DispatchController::class, 'assign'])->name('assign');
});

// Status Dashboard
Route::get('shipping/status', [\App\Http\Controllers\Shipping\StatusShippingController::class, 'index'])->name('shipping.status.index');

// Routes for Shipping - Driver (Delivery CRUD)
Route::prefix('shipping/delivery')->name('shipping.delivery.')->group(function () {
    // Dashboard Delivery
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\DeliveryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [App\Http\Controllers\Dashboard\DeliveryDashboardController::class, 'export'])->name('dashboard.export');
    
    Route::get('/', [App\Http\Controllers\Shipping\DeliveryController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Shipping\DeliveryController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Shipping\DeliveryController::class, 'store'])->name('store');
    
    // New Scanning Routes
    Route::get('/scan/{spk}', [App\Http\Controllers\Shipping\DeliveryController::class, 'scan'])->name('scan');
    Route::post('/store-from-scan', [App\Http\Controllers\Shipping\DeliveryController::class, 'storeFromScan'])->name('store-from-scan');
    
    // Arrival Proof Routes
    Route::get('/{delivery}/arrive', [App\Http\Controllers\Shipping\DeliveryController::class, 'showArrivalForm'])->name('arrive');
    Route::post('/{delivery}/report-arrival', [App\Http\Controllers\Shipping\DeliveryController::class, 'reportArrival'])->name('report-arrival');
    Route::post('/{delivery}/finish', [App\Http\Controllers\Shipping\DeliveryController::class, 'finishTrip'])->name('finish');

    Route::get('/{delivery}/edit', [App\Http\Controllers\Shipping\DeliveryController::class, 'edit'])->name('edit');
    Route::put('/{delivery}', [App\Http\Controllers\Shipping\DeliveryController::class, 'update'])->name('update');
    Route::get('/{delivery}/delete', [App\Http\Controllers\Shipping\DeliveryController::class, 'delete'])->name('delete');
    Route::delete('/{delivery}', [App\Http\Controllers\Shipping\DeliveryController::class, 'destroy'])->name('destroy');

    // Incident Reporting
    Route::post('/incident', [App\Http\Controllers\Shipping\IncidentController::class, 'store'])->name('incident.store');
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
