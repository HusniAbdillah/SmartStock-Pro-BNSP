<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Produk — custom routes MUST come before resource() to avoid route conflict
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('products/import/status/{jobId}', [ProductController::class, 'importStatus'])->name('products.import.status');
    Route::resource('products', ProductController::class);

    // Kategori
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Gudang
    Route::resource('warehouses', WarehouseController::class);

    // Supplier
    Route::resource('suppliers', SupplierController::class);

    // Transaksi
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'show']);

    // Transfer antar gudang
    Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');

    // Laporan — generate routes before any catch-all
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::post('reports/generate-large', [ReportController::class, 'generateLarge'])->name('reports.generate-large');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('reports/export-pdf/status/{filename}', [ReportController::class, 'checkPdfStatus'])->name('reports.pdf-status');

    // Error log — Admin & Manajer Gudang only
    Route::middleware('role:Admin,Manajer Gudang')->group(function () {
        Route::get('error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
        Route::patch('error-logs/{errorLog}/resolve', [ErrorLogController::class, 'resolve'])->name('error-logs.resolve');
    });

    // Audit log & User management — Admin only
    Route::middleware('role:Admin')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::resource('users', UserController::class);
    });
});
