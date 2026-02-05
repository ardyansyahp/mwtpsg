<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});


// Routes for master perusahaan CRUD
Route::prefix('master/perusahaan')->name('master.perusahaan.')->group(function () {
    // Import & Export routes
    Route::get('/import', [App\Http\Controllers\Master\PerusahaanController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\PerusahaanController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\PerusahaanController::class, 'export'])->name('export');
    
    // Recycle Bin routes
    Route::get('/trash', [App\Http\Controllers\Master\PerusahaanController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\PerusahaanController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\PerusahaanController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\PerusahaanController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\PerusahaanController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\PerusahaanController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\PerusahaanController::class, 'forceDeleteAll'])->name('empty.trash');
    
    Route::post('/destroy-all', [App\Http\Controllers\Master\PerusahaanController::class, 'destroyAll'])->name('destroy.all');
    
    Route::get('/', [App\Http\Controllers\Master\PerusahaanController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\PerusahaanController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Master\PerusahaanController::class, 'store'])->name('store');
    Route::get('/{perusahaan}/edit', [App\Http\Controllers\Master\PerusahaanController::class, 'edit'])->name('edit');
    Route::get('/{perusahaan}/delete', [App\Http\Controllers\Master\PerusahaanController::class, 'delete'])->name('delete');
    Route::put('/{perusahaan}', [App\Http\Controllers\Master\PerusahaanController::class, 'update'])->name('update');
    Route::delete('/{perusahaan}', [App\Http\Controllers\Master\PerusahaanController::class, 'destroy'])->name('destroy');
    Route::get('/{perusahaan}', [App\Http\Controllers\Master\PerusahaanController::class, 'show'])->name('show'); // Keep this last
});

// Routes for submaster bahan baku CRUD (gabungan material/masterbatch/subpart)
Route::prefix('submaster/bahanbaku')->name('master.bahanbaku.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Master\BahanBakuController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\BahanBakuController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\BahanBakuController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Master\BahanBakuController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\BahanBakuController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\BahanBakuController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\BahanBakuController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\BahanBakuController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\BahanBakuController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\BahanBakuController::class, 'forceDeleteAll'])->name('empty.trash');
    Route::post('/destroy-all', [App\Http\Controllers\Master\BahanBakuController::class, 'destroyAll'])->name('destroy.all');

    Route::get('/', [App\Http\Controllers\Master\BahanBakuController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\BahanBakuController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Master\BahanBakuController::class, 'store'])->name('store');
    Route::get('/{bahanbaku}/edit', [App\Http\Controllers\Master\BahanBakuController::class, 'edit'])->name('edit');
    Route::get('/{bahanbaku}/delete', [App\Http\Controllers\Master\BahanBakuController::class, 'delete'])->name('delete');
    Route::put('/{bahanbaku}', [App\Http\Controllers\Master\BahanBakuController::class, 'update'])->name('update');
    Route::delete('/{bahanbaku}', [App\Http\Controllers\Master\BahanBakuController::class, 'destroy'])->name('destroy');
    Route::get('/{bahanbaku}', [App\Http\Controllers\Master\BahanBakuController::class, 'show'])->name('show');
});

// Routes for master mesin CRUD
Route::prefix('master/mesin')->name('master.mesin.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Master\MesinController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\MesinController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\MesinController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Master\MesinController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\MesinController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\MesinController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\MesinController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\MesinController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\MesinController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\MesinController::class, 'forceDeleteAll'])->name('empty.trash');
    Route::post('/destroy-all', [App\Http\Controllers\Master\MesinController::class, 'destroyAll'])->name('destroy.all');

    Route::get('/', [App\Http\Controllers\Master\MesinController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\MesinController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Master\MesinController::class, 'store'])->name('store');
    Route::get('/{mesin}/edit', [App\Http\Controllers\Master\MesinController::class, 'edit'])->name('edit');
    Route::get('/{mesin}/delete', [App\Http\Controllers\Master\MesinController::class, 'delete'])->name('delete');
    Route::post('/{mesin}/destroy', [App\Http\Controllers\Master\MesinController::class, 'destroy'])->name('destroy');
    Route::post('/{mesin}/update', [App\Http\Controllers\Master\MesinController::class, 'update'])->name('update');
    Route:: get('/{id}/detail', [App\Http\Controllers\Master\MesinController::class, 'show'])->name('show');
    
    // ID Card
    Route::get('/{mesin}/idcard', [App\Http\Controllers\Master\MesinController::class, 'idcard'])->name('idcard');
});

// Routes for master manpower CRUD
Route::prefix('master/manpower')->name('master.manpower.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Master\ManpowerController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\ManpowerController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\ManpowerController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Master\ManpowerController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\ManpowerController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\ManpowerController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\ManpowerController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\ManpowerController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\ManpowerController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\ManpowerController::class, 'forceDeleteAll'])->name('empty.trash');
    Route::post('/destroy-all', [App\Http\Controllers\Master\ManpowerController::class, 'destroyAll'])->name('destroy.all');

    Route::get('/', [App\Http\Controllers\Master\ManpowerController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\ManpowerController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Master\ManpowerController::class, 'store'])->name('store');
    Route::get('/{manpower}/edit', [App\Http\Controllers\Master\ManpowerController::class, 'edit'])->name('edit');
    Route::get('/{manpower}/delete', [App\Http\Controllers\Master\ManpowerController::class, 'delete'])->name('delete');
    Route::post('/{manpower}/destroy', [App\Http\Controllers\Master\ManpowerController::class, 'destroy'])->name('destroy');
    Route::post('/{manpower}/update', [App\Http\Controllers\Master\ManpowerController::class, 'update'])->name('update');
    Route::get('/{id}/detail', [App\Http\Controllers\Master\ManpowerController::class, 'show'])->name('show');
    
    // ID Card
    Route::get('/{manpower}/idcard', [App\Http\Controllers\Master\ManpowerController::class, 'idcard'])->name('idcard');
});

// Routes for submaster mold CRUD
Route::prefix('submaster/mold')->name('master.mold.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Master\MoldController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\MoldController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\MoldController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Master\MoldController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\MoldController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\MoldController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\MoldController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\MoldController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\MoldController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\MoldController::class, 'forceDeleteAll'])->name('empty.trash');
    Route::post('/destroy-all', [App\Http\Controllers\Master\MoldController::class, 'destroyAll'])->name('destroy.all');

    Route::get('/', [App\Http\Controllers\Master\MoldController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\MoldController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Master\MoldController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\Master\MoldController::class, 'edit'])->name('edit');
    Route::get('/{id}/delete', [App\Http\Controllers\Master\MoldController::class, 'delete'])->name('delete');
    Route::get('/{id}/detail', [App\Http\Controllers\Master\MoldController::class, 'detail'])->name('detail');
    Route::put('/{id}', [App\Http\Controllers\Master\MoldController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\Master\MoldController::class, 'destroy'])->name('destroy');
    Route::get('/{id}', [App\Http\Controllers\Master\MoldController::class, 'show'])->name('show'); // Keep this last
});

// Routes for master kendaraan CRUD
// Routes for master kendaraan CRUD
Route::prefix('master/kendaraan')->name('master.kendaraan.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Master\KendaraanController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\KendaraanController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\KendaraanController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Master\KendaraanController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\KendaraanController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\KendaraanController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\KendaraanController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\KendaraanController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\KendaraanController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\KendaraanController::class, 'forceDeleteAll'])->name('empty.trash');
    Route::post('/destroy-all', [App\Http\Controllers\Master\KendaraanController::class, 'destroyAll'])->name('destroy.all');

    Route::get('/', [App\Http\Controllers\Master\KendaraanController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\KendaraanController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\Master\KendaraanController::class, 'store'])->name('store');
    Route::get('/{kendaraan}/edit', [App\Http\Controllers\Master\KendaraanController::class, 'edit'])->name('edit');
    Route::get('/{kendaraan}/delete', [App\Http\Controllers\Master\KendaraanController::class, 'delete'])->name('delete');
    Route::post('/{kendaraan}/update', [App\Http\Controllers\Master\KendaraanController::class, 'update'])->name('update');
    Route::post('/{kendaraan}/destroy', [App\Http\Controllers\Master\KendaraanController::class, 'destroy'])->name('destroy');
    Route::get('/{kendaraan}/label', [App\Http\Controllers\Master\KendaraanController::class, 'label'])->name('label');
    Route::get('/{kendaraan}', [App\Http\Controllers\Master\KendaraanController::class, 'show'])->name('show'); // Keep this last
});

// Routes for master plantgate CRUD
Route::prefix('master/plantgate')->name('master.plantgate.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Master\PlantGateController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Master\PlantGateController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Master\PlantGateController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Master\PlantGateController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Master\PlantGateController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Master\PlantGateController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Master\PlantGateController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Master\PlantGateController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Master\PlantGateController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Master\PlantGateController::class, 'forceDeleteAll'])->name('empty.trash');
    Route::post('/destroy-all', [App\Http\Controllers\Master\PlantGateController::class, 'destroyAll'])->name('destroy.all');

    Route::get('/', [App\Http\Controllers\Master\PlantGateController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Master\PlantGateController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\Master\PlantGateController::class, 'store'])->name('store');
    Route::get('/{plantgate}/edit', [App\Http\Controllers\Master\PlantGateController::class, 'edit'])->name('edit');
    Route::get('/{plantgate}/delete', [App\Http\Controllers\Master\PlantGateController::class, 'delete'])->name('delete');
    Route::post('/{plantgate}/update', [App\Http\Controllers\Master\PlantGateController::class, 'update'])->name('update');
    Route::post('/{plantgate}/destroy', [App\Http\Controllers\Master\PlantGateController::class, 'destroy'])->name('destroy');
    Route::get('/{plantgate}', [App\Http\Controllers\Master\PlantGateController::class, 'show'])->name('show'); // Keep this last
});

// Routes for submaster plantgate part CRUD
Route::prefix('submaster/plantgatepart')->name('submaster.plantgatepart.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'importPage'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'export'])->name('export');

    // Trash & Restore
    Route::get('/trash', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'emptyTrash'])->name('empty.trash');

    // Toggle Status
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'toggleStatus'])->name('toggle.status');

    // CRUD
    Route::get('/', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'store'])->name('store');
    Route::get('/{id}/detail', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'detail'])->name('detail');
    Route::get('/{id}/edit', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'update'])->name('update');
    Route::get('/{id}/delete', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'delete'])->name('delete');
    Route::delete('/{id}', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'destroy'])->name('destroy');
    Route::get('/{id}', [App\Http\Controllers\Submaster\PlantGatePartController::class, 'show'])->name('show');
});

// Routes for submaster part CRUD
Route::prefix('submaster/part')->name('submaster.part.')->group(function () {
    // Import & Export
    Route::get('/import', [App\Http\Controllers\Submaster\PartController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\Submaster\PartController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\Submaster\PartController::class, 'export'])->name('export');

    // Trash & Maintenance
    Route::get('/trash', [App\Http\Controllers\Submaster\PartController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [App\Http\Controllers\Submaster\PartController::class, 'restore'])->name('restore');
    Route::post('/restore-all', [App\Http\Controllers\Submaster\PartController::class, 'restoreAll'])->name('restore.all');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\Submaster\PartController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/bulk-delete', [App\Http\Controllers\Submaster\PartController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/destroy-all', [App\Http\Controllers\Submaster\PartController::class, 'destroyAll'])->name('destroy.all');
    Route::post('/{id}/force-delete', [App\Http\Controllers\Submaster\PartController::class, 'forceDelete'])->name('force.delete');
    Route::post('/empty-trash', [App\Http\Controllers\Submaster\PartController::class, 'forceDeleteAll'])->name('empty.trash');

    Route::get('/', [App\Http\Controllers\Submaster\PartController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Submaster\PartController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Submaster\PartController::class, 'store'])->name('store');
    Route::get('/{part}/edit', [App\Http\Controllers\Submaster\PartController::class, 'edit'])->name('edit');
    Route::put('/{part}', [App\Http\Controllers\Submaster\PartController::class, 'update'])->name('update');
    Route::get('/{part}/detail', [App\Http\Controllers\Submaster\PartController::class, 'detail'])->name('detail');
    Route::get('/{part}/delete', [App\Http\Controllers\Submaster\PartController::class, 'delete'])->name('delete');
    Route::delete('/{part}', [App\Http\Controllers\Submaster\PartController::class, 'destroy'])->name('destroy');
    Route::get('/{part}', [App\Http\Controllers\Submaster\PartController::class, 'show'])->name('show'); // Keep this last
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

    Route::prefix('supply')->name('supply.')->group(function () {
        Route::get('/', [App\Http\Controllers\BahanBaku\SupplyController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BahanBaku\SupplyController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BahanBaku\SupplyController::class, 'store'])->name('store');
        Route::get('/{supply}/edit', [App\Http\Controllers\BahanBaku\SupplyController::class, 'edit'])->name('edit');
        Route::get('/{supply}/delete', [App\Http\Controllers\BahanBaku\SupplyController::class, 'delete'])->name('delete');
        Route::get('/{supply}/labels', [App\Http\Controllers\BahanBaku\SupplyController::class, 'labels'])->name('labels');
        Route::put('/{supply}', [App\Http\Controllers\BahanBaku\SupplyController::class, 'update'])->name('update');
        Route::delete('/{supply}', [App\Http\Controllers\BahanBaku\SupplyController::class, 'destroy'])->name('destroy');
        
        // API untuk mendapatkan kebutuhan material dari planning run
        Route::get('/api/planning-requirements', [App\Http\Controllers\BahanBaku\SupplyController::class, 'getPlanningRequirements'])->name('api.planningRequirements');
        Route::get('/api/planning-runs', [App\Http\Controllers\BahanBaku\SupplyController::class, 'getPlanningRuns'])->name('api.planningRuns');
        Route::get('/api/parts-by-tipe', [App\Http\Controllers\BahanBaku\SupplyController::class, 'getPartsByTipe'])->name('api.partsByTipe');
        Route::get('/api/part-subparts', [App\Http\Controllers\BahanBaku\SupplyController::class, 'getPartSubparts'])->name('api.partSubparts');
    });
});

// Routes for submaster subpart CRUD
// NOTE: Subpart sekarang jadi kategori di master/bahanbaku (tidak ada route submaster/subpart lagi)

// Routes for planning editor (1 halaman)
Route::prefix('planning')->name('planning.')->group(function () {
    Route::get('/', [App\Http\Controllers\Planning\PlanningController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\Planning\PlanningController::class, 'store'])->name('store');
    Route::get('/{planningDay}/edit', [App\Http\Controllers\Planning\PlanningController::class, 'edit'])->name('edit');
    Route::get('/{planningDay}/detail', [App\Http\Controllers\Planning\PlanningController::class, 'detail'])->name('detail');
    Route::put('/{planningDay}', [App\Http\Controllers\Planning\PlanningController::class, 'update'])->name('update');
    Route::delete('/{planningDay}', [App\Http\Controllers\Planning\PlanningController::class, 'destroy'])->name('destroy');

    // API kecil untuk bantu UI editor
    Route::get('/api/mold/{mold}/subparts', [App\Http\Controllers\Planning\PlanningController::class, 'moldSubparts'])->name('api.moldSubparts');
    Route::get('/api/mold/{mold}/part-data', [App\Http\Controllers\Planning\PlanningController::class, 'moldPartData'])->name('api.moldPartData');
    Route::get('/api/parts-by-tipe', [App\Http\Controllers\Planning\PlanningController::class, 'getPartsByTipe'])->name('api.partsByTipe');
    Route::get('/api/part-subparts', [App\Http\Controllers\Planning\PlanningController::class, 'getPartSubparts'])->name('api.partSubparts');
    
    // Matriks planning
    Route::get('/matriks', [App\Http\Controllers\Planning\PlanningController::class, 'matriks'])->name('matriks');
});

// Routes for produksi inject (scan in/out)
Route::prefix('produksi/inject')->name('produksi.inject.')->group(function () {
    // Dashboard Inject
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\InjectDashboardController::class, 'index'])->name('dashboard');

    Route::get('/', [App\Http\Controllers\Produksi\InjectController::class, 'index'])->name('index');
    Route::get('/createin', [App\Http\Controllers\Produksi\InjectController::class, 'create'])->name('createin');
    Route::post('/storein', [App\Http\Controllers\Produksi\InjectController::class, 'store'])->name('storein');
    Route::get('/{injectIn}/editin', [App\Http\Controllers\Produksi\InjectController::class, 'edit'])->name('editin');
    Route::get('/{injectIn}/deletein', [App\Http\Controllers\Produksi\InjectController::class, 'delete'])->name('deletein');
    Route::put('/{injectIn}/updatein', [App\Http\Controllers\Produksi\InjectController::class, 'update'])->name('updatein');
    Route::delete('/{injectIn}/destroyin', [App\Http\Controllers\Produksi\InjectController::class, 'destroy'])->name('destroyin');
    Route::get('/{injectIn}/label', [App\Http\Controllers\Produksi\InjectController::class, 'label'])->name('label');

    // API untuk cari supply detail berdasarkan lot number
    Route::get('/api/supply-detail/{lotNumber}', [App\Http\Controllers\Produksi\InjectController::class, 'getSupplyDetailByLotNumber'])->name('api.supplyDetailByLotNumber');
    // API untuk cari mesin berdasarkan QR code
    Route::get('/api/mesin/{qrCode}', [App\Http\Controllers\Produksi\InjectController::class, 'getMesinByQR'])->name('api.mesinByQR');
    // API untuk cari operator berdasarkan QR code
    Route::get('/api/operator/{qrCode}', [App\Http\Controllers\Produksi\InjectController::class, 'getOperatorByQR'])->name('api.operatorByQR');

    // Routes untuk inject out
    Route::get('/out', [App\Http\Controllers\Produksi\InjectController::class, 'indexOut'])->name('indexout');
    Route::get('/createout', [App\Http\Controllers\Produksi\InjectController::class, 'createOut'])->name('createout');
    Route::post('/storeout', [App\Http\Controllers\Produksi\InjectController::class, 'storeOut'])->name('storeout');
    Route::get('/{injectOut}/detailout', [App\Http\Controllers\Produksi\InjectController::class, 'detailOut'])->name('detailout');
    Route::get('/{injectOut}/editout', [App\Http\Controllers\Produksi\InjectController::class, 'editOut'])->name('editout');
    Route::get('/{injectOut}/deleteout', [App\Http\Controllers\Produksi\InjectController::class, 'deleteOut'])->name('deleteout');
    Route::put('/{injectOut}/updateout', [App\Http\Controllers\Produksi\InjectController::class, 'updateOut'])->name('updateout');
    Route::delete('/{injectOut}/destroyout', [App\Http\Controllers\Produksi\InjectController::class, 'destroyOut'])->name('destroyout');

    // API untuk cari inject in berdasarkan lot number
    Route::get('/api/inject-in/{lotNumber}', [App\Http\Controllers\Produksi\InjectController::class, 'getInjectInByLotNumber'])->name('api.injectInByLotNumber');
});

// Routes for produksi WIP (scan in/out)
Route::prefix('produksi/wip')->name('produksi.wip.')->group(function () {
    // Dashboard WIP
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\WipDashboardController::class, 'index'])->name('dashboard');

    // Routes untuk WIP In
    Route::get('/in', [App\Http\Controllers\Produksi\WipController::class, 'indexIn'])->name('indexin');
    Route::post('/sync-inject-out', [App\Http\Controllers\Produksi\WipController::class, 'syncFromInjectOut'])->name('syncInjectOut');
    Route::get('/createin', [App\Http\Controllers\Produksi\WipController::class, 'createIn'])->name('createin');
    Route::post('/storein', [App\Http\Controllers\Produksi\WipController::class, 'storeIn'])->name('storein');
    Route::post('/{wipIn}/confirmin', [App\Http\Controllers\Produksi\WipController::class, 'confirmIn'])->name('confirmin');
    Route::get('/{wipIn}/editin', [App\Http\Controllers\Produksi\WipController::class, 'editIn'])->name('editin');
    Route::get('/{wipIn}/deletein', [App\Http\Controllers\Produksi\WipController::class, 'deleteIn'])->name('deletein');
    Route::put('/{wipIn}/updatein', [App\Http\Controllers\Produksi\WipController::class, 'updateIn'])->name('updatein');
    Route::delete('/{wipIn}/destroyin', [App\Http\Controllers\Produksi\WipController::class, 'destroyIn'])->name('destroyin');

    // API untuk cari inject out berdasarkan lot number (untuk WIP In)
    Route::get('/api/inject-out/{lotNumber}', [App\Http\Controllers\Produksi\WipController::class, 'getInjectOutByLotNumber'])->name('api.injectOutByLotNumber');

    // Routes untuk WIP Out
    Route::get('/out', [App\Http\Controllers\Produksi\WipController::class, 'indexOut'])->name('indexout');
    Route::get('/createout', [App\Http\Controllers\Produksi\WipController::class, 'createOut'])->name('createout');
    Route::post('/storeout', [App\Http\Controllers\Produksi\WipController::class, 'storeOut'])->name('storeout');
    Route::get('/{wipOut}/editout', [App\Http\Controllers\Produksi\WipController::class, 'editOut'])->name('editout');
    Route::get('/{wipOut}/deleteout', [App\Http\Controllers\Produksi\WipController::class, 'deleteOut'])->name('deleteout');
    Route::put('/{wipOut}/updateout', [App\Http\Controllers\Produksi\WipController::class, 'updateOut'])->name('updateout');
    Route::delete('/{wipOut}/destroyout', [App\Http\Controllers\Produksi\WipController::class, 'destroyOut'])->name('destroyout');

    // API untuk cari wip in berdasarkan lot number (untuk WIP Out)
    Route::get('/api/wip-in/{lotNumber}', [App\Http\Controllers\Produksi\WipController::class, 'getWipInByLotNumber'])->name('api.wipInByLotNumber');
});

// Routes for produksi ASSY (scan in/out)
Route::prefix('produksi/assy')->name('produksi.assy.')->group(function () {
    // Dashboard Assy
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\AssyDashboardController::class, 'index'])->name('dashboard');

    // Routes untuk ASSY In
    Route::get('/in', [App\Http\Controllers\Produksi\AssyController::class, 'indexIn'])->name('indexin');
    Route::get('/detailin', [App\Http\Controllers\Produksi\AssyController::class, 'detailIn'])->name('detailin');
    Route::get('/createin', [App\Http\Controllers\Produksi\AssyController::class, 'createIn'])->name('createin');
    Route::post('/storein', [App\Http\Controllers\Produksi\AssyController::class, 'storeIn'])->name('storein');
    Route::get('/{assyIn}/editin', [App\Http\Controllers\Produksi\AssyController::class, 'editIn'])->name('editin');
    Route::put('/{assyIn}/updatein', [App\Http\Controllers\Produksi\AssyController::class, 'updateIn'])->name('updatein');
    Route::get('/{assyIn}/deletein', [App\Http\Controllers\Produksi\AssyController::class, 'deleteIn'])->name('deletein');
    Route::delete('/{assyIn}/destroyin', [App\Http\Controllers\Produksi\AssyController::class, 'destroyIn'])->name('destroyin');
    
    // Routes untuk ASSY Out
    Route::get('/out', [App\Http\Controllers\Produksi\AssyController::class, 'indexOut'])->name('indexout');
    Route::get('/createout', [App\Http\Controllers\Produksi\AssyController::class, 'createOut'])->name('createout');
    Route::post('/storeout', [App\Http\Controllers\Produksi\AssyController::class, 'storeOut'])->name('storeout');
    Route::get('/{assyOut}/detailout', [App\Http\Controllers\Produksi\AssyController::class, 'detailOut'])->name('detailout');
    Route::get('/{assyOut}/editout', [App\Http\Controllers\Produksi\AssyController::class, 'editOut'])->name('editout');
    Route::put('/{assyOut}/updateout', [App\Http\Controllers\Produksi\AssyController::class, 'updateOut'])->name('updateout');
    Route::get('/{assyOut}/deleteout', [App\Http\Controllers\Produksi\AssyController::class, 'deleteOut'])->name('deleteout');
    Route::delete('/{assyOut}/destroyout', [App\Http\Controllers\Produksi\AssyController::class, 'destroyOut'])->name('destroyout');
    
    // API untuk cari supply detail berdasarkan lot number (untuk ASSY In)
    Route::get('/api/supply-detail/{lotNumber}', [App\Http\Controllers\Produksi\AssyController::class, 'getSupplyDetailByLotNumber'])->name('api.supplyDetailByLotNumber');
    
    // API untuk cari wip out berdasarkan lot number (untuk ASSY In)
    Route::get('/api/wip-out/{lotNumber}', [App\Http\Controllers\Produksi\AssyController::class, 'getWipOutByLotNumber'])->name('api.wipOutByLotNumber');
    
    // API untuk cari operator/manpower berdasarkan QR code (untuk ASSY In)
    Route::get('/api/operator/{qrCode}', [App\Http\Controllers\Produksi\AssyController::class, 'getManpowerByQR'])->name('api.manpowerByQR');
    
    // API untuk cari assy in berdasarkan lot number (untuk ASSY Out)
    Route::get('/api/assy-in/{lotNumber}', [App\Http\Controllers\Produksi\AssyController::class, 'getAssyInByLotNumber'])->name('api.assyInByLotNumber');
    
    // API untuk cari assy out berdasarkan lot number (untuk Finish Good In)
    Route::get('/api/assy-out/{lotNumber}', [App\Http\Controllers\Produksi\AssyController::class, 'getAssyOutByLotNumber'])->name('api.assyOutByLotNumber');
});

// Routes for finishgood stock
Route::get('finishgood/stock', [App\Http\Controllers\FinishGood\FinishGoodStockController::class, 'index'])->name('finishgood.stock.index');

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

// Routes for tracer (tracking part journey)
Route::prefix('tracer')->name('tracer.')->group(function () {
    Route::get('/', [App\Http\Controllers\Tracer\TracerController::class, 'index'])->name('index');
    Route::get('/trace/{lotNumber}', [App\Http\Controllers\Tracer\TracerController::class, 'trace'])->name('trace');
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

// Routes for Control Supplier (Monitoring per Item)
Route::prefix('controlsupplier')->name('controlsupplier.')->group(function () {
    // Dashboard Control Supplier
    Route::get('/dashboard', [App\Http\Controllers\Dashboard\ControlSupplierDashboardController::class, 'index'])->name('dashboard');


    
    Route::get('/monitoring', [App\Http\Controllers\ControlSupplierController::class, 'monitoring'])->name('monitoring');
    Route::get('/import', [App\Http\Controllers\ControlSupplierController::class, 'showImportForm'])->name('import.form');
    Route::post('/import', [App\Http\Controllers\ControlSupplierController::class, 'importProcess'])->name('import.process');
    Route::post('/reset', [App\Http\Controllers\ControlSupplierController::class, 'resetData'])->name('reset');
    
    // API routes for editable cells
    Route::post('/update-ponumb', [App\Http\Controllers\ControlSupplierController::class, 'updatePONumb'])->name('updatePONumb');
    Route::post('/detach-ponumb', [App\Http\Controllers\ControlSupplierController::class, 'detachPONumb'])->name('detachPONumb');
    Route::post('/update-plan', [App\Http\Controllers\ControlSupplierController::class, 'updatePlan'])->name('updatePlan');
    
    // Auto-sync from receiving
    Route::post('/sync-receiving/{receivingId}', [App\Http\Controllers\ControlSupplierController::class, 'syncFromReceiving'])->name('syncReceiving');
    
    // Import SAP Excel
    Route::post('/import-sap', [App\Http\Controllers\ControlSupplierController::class, 'importSAPExcel'])->name('importSAP');
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

// Authentication Routes (exclude from CheckAuth middleware)
Route::withoutMiddleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});

// Logout Route (requires auth)
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Superadmin Routes (protected by superadmin middleware)
Route::prefix('superadmin')->name('superadmin.')->middleware('superadmin')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [App\Http\Controllers\Superadmin\UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Superadmin\UserManagementController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Superadmin\UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [App\Http\Controllers\Superadmin\UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/permissions', [App\Http\Controllers\Superadmin\UserManagementController::class, 'editPermissions'])->name('permissions.edit');
        Route::put('/{user}/permissions', [App\Http\Controllers\Superadmin\UserManagementController::class, 'updatePermissions'])->name('permissions.update');
        Route::delete('/{user}', [App\Http\Controllers\Superadmin\UserManagementController::class, 'destroy'])->name('destroy');
    });
});
