<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RfidScanController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\Api\CheckinController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * API ROUTES VISITOR MANAGEMENT SYSTEM
 * 
 * Semua API routes dimulai dengan prefix: /api/
 * Contoh: /api/dashboard/realtime
 * 
 * Response format: JSON
 */

// ============================================
// DASHBOARD API
// ============================================
Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
    // Real-time dashboard data untuk AJAX polling
    Route::get('/realtime', [DashboardController::class, 'realtimeData'])
        ->name('realtime');
    
    // Get statistics
    Route::get('/statistics', function (Request $request) {
        $period = $request->get('period', 'today');
        $controller = new DashboardController();
        $method = new ReflectionMethod($controller, 'getStatistics');
        $method->setAccessible(true);
        return response()->json($method->invoke($controller, $period));
    })->name('statistics');
});

// ============================================
// RFID SCAN API
// ============================================
Route::prefix('rfid')->name('api.rfid.')->group(function () {
    // Process RFID scan (alternative endpoint)
    Route::post('/scan', [RfidScanController::class, 'processRfid'])
        ->name('scan');
    
    // Get location visitors
    Route::get('/locations/{locationCode}/visitors', [RfidScanController::class, 'getLocationVisitors'])
        ->name('location.visitors');
});

// ============================================
// VISITORS API
// ============================================
Route::prefix('visitors')->name('api.visitors.')->group(function () {
    // Get all visitors (JSON)
    Route::get('/', function (Request $request) {
        $query = \App\Models\User::query();
        
        if ($request->has('type')) {
            $query->ofType($request->type);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $visitors = $query->with('rfidCards')->paginate(20);
        return response()->json($visitors);
    })->name('index');
    
    // Get visitor detail
    Route::get('/{id}', function ($id) {
        $visitor = \App\Models\User::with(['rfidCards', 'accessRights.location', 'trackingLogs'])
            ->findOrFail($id);
        return response()->json($visitor);
    })->name('show');
    
    // Register RFID card
    Route::post('/{id}/register-rfid', [VisitorController::class, 'registerRfid'])
        ->name('register-rfid');
});

// ============================================
// LOCATIONS API
// ============================================
Route::prefix('locations')->name('api.locations.')->group(function () {
    // Get all locations
    Route::get('/', function () {
        $locations = \App\Models\Location::active()->get();
        return response()->json($locations);
    })->name('index');
    
    // Get location detail
    Route::get('/{code}', function ($code) {
        $location = \App\Models\Location::byCode($code)->firstOrFail();
        return response()->json([
            'location' => $location,
            'current_visitors' => $location->getCurrentVisitorCount(),
            'capacity' => $location->capacity,
            'statistics' => $location->getStatistics('today'),
        ]);
    })->name('show');
    
    // Get location visitors
    Route::get('/{code}/visitors', function ($code) {
        $location = \App\Models\Location::byCode($code)->firstOrFail();
        return response()->json([
            'location' => $location,
            'visitors' => $location->getCurrentVisitors(),
        ]);
    })->name('visitors');
});

// ============================================
// TRACKING LOGS API
// ============================================
Route::prefix('tracking')->name('api.tracking.')->group(function () {
    // Get recent logs
    Route::get('/recent', function (Request $request) {
        $limit = $request->get('limit', 20);
        $logs = \App\Models\TrackingLog::with(['user', 'location', 'rfidCard'])
            ->latest('scanned_at')
            ->limit($limit)
            ->get();
        return response()->json($logs);
    })->name('recent');
    
    // Get active visitors (currently inside)
    Route::get('/active', function () {
        $controller = new DashboardController();
        $method = new ReflectionMethod($controller, 'getActiveVisitors');
        $method->setAccessible(true);
        $activeVisitors = $method->invoke($controller);
        return response()->json($activeVisitors);
    })->name('active');
    
    // Get logs by user
    Route::get('/user/{userId}', function ($userId) {
        $logs = \App\Models\TrackingLog::with(['location', 'rfidCard'])
            ->where('user_id', $userId)
            ->latest('scanned_at')
            ->paginate(50);
        return response()->json($logs);
    })->name('user');
    
    // Get logs by location
    Route::get('/location/{locationId}', function ($locationId) {
        $logs = \App\Models\TrackingLog::with(['user', 'rfidCard'])
            ->where('location_id', $locationId)
            ->latest('scanned_at')
            ->paginate(50);
        return response()->json($logs);
    })->name('location');
});

// ============================================
// CHECK-IN API (POS UTAMA)
// ============================================
Route::prefix('checkin')->name('api.checkin.')->group(function () {
    // Check visitor by RFID UID
    Route::post('/check-visitor', [CheckinController::class, 'checkVisitor'])
        ->name('check-visitor');
    
    // Grant access and create check-in log
    Route::post('/grant-access', [CheckinController::class, 'grantAccess'])
        ->name('grant-access');
    
    // Get today's check-ins
    Route::get('/today', [CheckinController::class, 'todayCheckins'])
        ->name('today');
    
    // Checkout visitor
    Route::post('/checkout', [CheckinController::class, 'checkout'])
        ->name('checkout');
});

// ============================================
// STATISTICS API
// ============================================
Route::prefix('statistics')->name('api.statistics.')->group(function () {
    // Get overall statistics
    Route::get('/overall', function (Request $request) {
        $period = $request->get('period', 'today');
        
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfDay(),
        };
        
        return response()->json([
            'total_users' => \App\Models\User::active()->count(),
            'total_scans' => \App\Models\TrackingLog::where('scanned_at', '>=', $startDate)->count(),
            'total_locations' => \App\Models\Location::active()->count(),
            'currently_inside' => \App\Models\TrackingLog::whereIn('action_type', ['entry', 'move'])
                ->where('status', 'accepted')
                ->distinct('user_id')
                ->count(),
        ]);
    })->name('overall');
});
