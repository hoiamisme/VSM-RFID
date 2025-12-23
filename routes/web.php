<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RfidScanController;
use App\Http\Controllers\VisitorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * ROUTE VISITOR MANAGEMENT SYSTEM
 * 
 * Struktur routing:
 * - Dashboard: monitoring dan statistik
 * - RFID Scan: halaman scan dan proses scan
 * - Visitors: CRUD tamu dan pegawai
 * - Locations: management lokasi
 * - Access Rights: management hak akses
 * - Reports: laporan aktivitas
 */

// ============================================
// HOME / LANDING PAGE
// ============================================
Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

// ============================================
// FACE RECOGNITION TEST PAGE (Development Only)
// ============================================
Route::get('/face-test', function () {
    return view('face-recognition-test');
})->name('face.test');

// ============================================
// DASHBOARD
// ============================================
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    // Main dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('index');
});

// ============================================
// RFID SCAN ROUTES
// ============================================
Route::prefix('rfid')->name('rfid.')->group(function () {
    // Halaman scan dengan location code (Mode A)
    // Contoh: /scan/dekanat, /scan/aula, /scan/lab-informatika
    Route::get('/scan/{location?}', [RfidScanController::class, 'showScanPage'])
        ->name('scan.page');
    
    // Process RFID scan (AJAX endpoint)
    Route::post('/scan', [RfidScanController::class, 'processRfid'])
        ->name('scan.process');
    
    // Get active visitors in a location
    Route::get('/location/{locationCode}/visitors', [RfidScanController::class, 'getLocationVisitors'])
        ->name('location.visitors');
});

// Alias route untuk backward compatibility
Route::get('/scan/{location?}', [RfidScanController::class, 'showScanPage'])
    ->name('scan');

// ============================================
// CHECK-IN POS UTAMA
// ============================================
Route::get('/checkin', function () {
    return view('checkin.index');
})->name('checkin.index');

// ============================================
// VISITOR MANAGEMENT ROUTES
// ============================================
Route::resource('visitors', VisitorController::class);

// Additional visitor routes
Route::prefix('visitors')->name('visitors.')->group(function () {
    // Register RFID card untuk visitor
    Route::post('/{id}/register-rfid', [VisitorController::class, 'registerRfid'])
        ->name('register-rfid');
    
    // Bulk import visitors (optional)
    Route::get('/import', [VisitorController::class, 'showImportForm'])
        ->name('import.form');
    Route::post('/import', [VisitorController::class, 'import'])
        ->name('import.process');
});

// ============================================
// LOCATION MANAGEMENT ROUTES
// ============================================
use App\Http\Controllers\LocationController;
Route::resource('locations', LocationController::class);

// ============================================
// ACCESS RIGHTS MANAGEMENT ROUTES
// ============================================
use App\Http\Controllers\AccessRightController;
Route::resource('access-rights', AccessRightController::class)->only(['index', 'create', 'store', 'destroy']);
Route::post('access-rights/revoke-user', [AccessRightController::class, 'revokeUser'])->name('access-rights.revoke-user');

// ============================================
// REPORTS & ANALYTICS ROUTES
// ============================================
use App\Http\Controllers\ReportController;
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/export', [ReportController::class, 'export'])->name('export');
});

// ============================================
// ADMIN ROUTES (Optional - untuk konfigurasi sistem)
// ============================================
// Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
//     Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
//     Route::get('/users', [AdminController::class, 'users'])->name('users');
//     Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
// });
