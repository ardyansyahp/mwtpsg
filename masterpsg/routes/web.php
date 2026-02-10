<?php

use Illuminate\Support\Facades\Route;

// Debug routes (remove in production)
require __DIR__.'/debug.php';

Route::get('/', function () {
    $headers = [
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    // 1. Check Login
    $userId = session('user_id');
    if (!$userId) return redirect()->route('login')->withHeaders($headers);
    
    // 2. Role Fast Track
    $role = (int) session('role');

    // 2a. Superadmin (1) -> Stay on Master Portal
    if ($role === 1) {
        return response(view('home'))->withHeaders($headers);
    }

    // 2b. Management (2) -> Redirect to Management Portal
    if ($role === 2) {
        return redirect()->away(env('URL_MANAGEMENT'))->withHeaders($headers);
    }
    
    // 3. Explicit Master Homepage Priority
    if (userCan('homepage.master.view')) {
        return response(view('home'))->withHeaders($headers);
    }

    // 4. Regular User Redirects
    if (userCan('homepage.supplier.view')) {
        return redirect()->away(env('URL_SUPPLIER'))->withHeaders($headers);
    }
    
    if (userCan('homepage.shipping.view')) {
        return redirect()->away(env('URL_SHIPPING'))->withHeaders($headers);
    }

    // 3. Default Master Homepage
    return response(view('home'))->withHeaders($headers);
});

// Global Search
Route::get('/global-search', [App\Http\Controllers\Dashboard\GlobalSearchController::class, 'search'])->name('global.search');

// System Diagnostic
Route::get('/system/diagnostic', [App\Http\Controllers\Dashboard\DiagnosticController::class, 'run'])->name('system.diagnostic');

// Profile Management (for Kabag and Management users)
Route::prefix('profile')->name('profile.')->middleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    Route::get('/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
    Route::put('/username', [App\Http\Controllers\ProfileController::class, 'updateUsername'])->name('update.username');
    Route::put('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('update.password');
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
        Route::get('/bulk-permissions', [App\Http\Controllers\Superadmin\UserManagementController::class, 'bulkPermissions'])->name('bulk_permissions');
        Route::get('/{user}', [App\Http\Controllers\Superadmin\UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/permissions', [App\Http\Controllers\Superadmin\UserManagementController::class, 'editPermissions'])->name('permissions.edit');
        Route::put('/{user}/permissions', [App\Http\Controllers\Superadmin\UserManagementController::class, 'updatePermissions'])->name('permissions.update');
        Route::post('/bulk-update-permissions', [App\Http\Controllers\Superadmin\UserManagementController::class, 'bulkUpdatePermissions'])->name('bulk_update_permissions');
        Route::delete('/{user}', [App\Http\Controllers\Superadmin\UserManagementController::class, 'destroy'])->name('destroy');
    });
});
