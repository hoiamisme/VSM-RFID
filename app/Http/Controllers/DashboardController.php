<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use App\Models\TrackingLog;
use App\Models\RfidCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * DashboardController
 * 
 * Controller untuk dashboard monitoring sistem VMS
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class DashboardController extends Controller
{
    /**
     * Display main dashboard
     * Route: GET /dashboard
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get filter period (default: today)
        $period = $request->get('period', 'today');

        // Get statistics
        $stats = $this->getStatistics($period);

        // Get face recognition statistics
        $faceStats = $this->getFaceStatistics();

        // Get active visitors (currently inside any location)
        $activeVisitors = $this->getActiveVisitors();

        // Get recent activity
        $recentActivity = $this->getRecentActivity(20);

        // Get location occupancy
        $locationOccupancy = $this->getLocationOccupancy();

        // Get chart data
        $chartData = $this->getChartData($period);

        return view('dashboard.index', compact(
            'stats',
            'faceStats',
            'activeVisitors',
            'recentActivity',
            'locationOccupancy',
            'chartData',
            'period'
        ));
    }

    /**
     * Get statistics for dashboard
     * 
     * @param string $period (today|week|month|all)
     * @return array
     */
    protected function getStatistics(string $period = 'today'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $query = TrackingLog::query();
        if ($startDate) {
            $query->where('scanned_at', '>=', $startDate);
        }

        // Total scans
        $totalScans = $query->count();

        // Accepted vs Denied
        $acceptedScans = (clone $query)->accepted()->count();
        $deniedScans = (clone $query)->denied()->count();

        // Unique visitors
        $uniqueVisitors = (clone $query)->distinct('user_id')->count('user_id');

        // Total users
        $totalUsers = User::active()->count();
        $totalGuests = User::active()->guests()->count();
        $totalEmployees = User::active()->employees()->count();
        $totalKadets = User::active()->kadets()->count();

        // Total RFID cards
        $totalRfidCards = RfidCard::active()->count();

        // Total locations
        $totalLocations = Location::active()->count();

        // Currently inside
        $currentlyInside = $this->getActiveVisitors()->count();

        // Today's entries
        $todayEntries = TrackingLog::today()
            ->accepted()
            ->whereIn('action_type', [TrackingLog::ACTION_ENTRY, TrackingLog::ACTION_MOVE])
            ->count();

        // Today's exits
        $todayExits = TrackingLog::today()
            ->accepted()
            ->where('action_type', TrackingLog::ACTION_EXIT)
            ->count();

        return [
            'total_scans' => $totalScans,
            'accepted_scans' => $acceptedScans,
            'denied_scans' => $deniedScans,
            'acceptance_rate' => $totalScans > 0 ? round(($acceptedScans / $totalScans) * 100, 2) : 0,
            'unique_visitors' => $uniqueVisitors,
            'total_users' => $totalUsers,
            'total_guests' => $totalGuests,
            'total_employees' => $totalEmployees,
            'total_kadets' => $totalKadets,
            'total_rfid_cards' => $totalRfidCards,
            'total_locations' => $totalLocations,
            'currently_inside' => $currentlyInside,
            'today_entries' => $todayEntries,
            'today_exits' => $todayExits,
        ];
    }

    /**
     * Get face recognition statistics
     * 
     * @return array
     */
    protected function getFaceStatistics(): array
    {
        // Total users
        $totalUsers = User::active()->count();

        // Users with face enrolled
        $totalEnrolled = User::active()
            ->whereNotNull('face_descriptor')
            ->whereNotNull('face_registered_at')
            ->count();

        // Enrollment rate
        $enrollmentRate = $totalUsers > 0 
            ? round(($totalEnrolled / $totalUsers) * 100, 2) 
            : 0;

        // Users requiring face verification
        $requireVerification = User::active()
            ->where('require_face_verification', true)
            ->count();

        // Face verified today
        $verifiedToday = TrackingLog::today()
            ->where('face_verified', true)
            ->where('verification_method', 'rfid+face')
            ->count();

        // Face verification failed today
        $failedToday = TrackingLog::today()
            ->where('face_verified', false)
            ->whereNotNull('face_similarity')
            ->count();

        // Total face verifications today (success + failed)
        $totalVerificationsToday = $verifiedToday + $failedToday;

        // Verification success rate today
        $verificationSuccessRate = $totalVerificationsToday > 0 
            ? round(($verifiedToday / $totalVerificationsToday) * 100, 2) 
            : 0;

        // Average similarity for successful verifications
        $avgSimilarity = TrackingLog::today()
            ->where('face_verified', true)
            ->whereNotNull('face_similarity')
            ->avg('face_similarity');

        $avgSimilarity = $avgSimilarity ? round($avgSimilarity * 100, 2) : 0;

        return [
            'total_enrolled' => $totalEnrolled,
            'enrollment_rate' => $enrollmentRate,
            'require_verification' => $requireVerification,
            'verified_today' => $verifiedToday,
            'failed_today' => $failedToday,
            'verification_success_rate' => $verificationSuccessRate,
            'avg_similarity' => $avgSimilarity,
        ];
    }

    /**
     * Get list of active visitors (currently inside)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getActiveVisitors()
    {
        // Get all users who have entry/move log without subsequent exit
        $activeUserIds = DB::table('tracking_logs as t1')
            ->select('t1.user_id', 't1.location_id', 't1.scanned_at as entry_time')
            ->whereIn('t1.action_type', [TrackingLog::ACTION_ENTRY, TrackingLog::ACTION_MOVE])
            ->where('t1.status', TrackingLog::STATUS_ACCEPTED)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tracking_logs as t2')
                    ->whereColumn('t2.user_id', 't1.user_id')
                    ->whereColumn('t2.location_id', 't1.location_id')
                    ->where('t2.action_type', TrackingLog::ACTION_EXIT)
                    ->whereColumn('t2.scanned_at', '>', 't1.scanned_at');
            })
            ->groupBy('t1.user_id', 't1.location_id', 't1.scanned_at')
            ->get()
            ->unique('user_id')
            ->pluck('user_id');

        return User::with(['trackingLogs' => function ($query) {
                $query->latest('scanned_at')->limit(1);
            }])
            ->whereIn('id', $activeUserIds)
            ->get()
            ->map(function ($user) {
                $lastLog = $user->trackingLogs->first();
                $user->current_location = $lastLog ? $lastLog->location : null;
                $user->entry_time = $lastLog ? $lastLog->scanned_at : null;
                return $user;
            });
    }

    /**
     * Get recent activity logs
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecentActivity(int $limit = 20)
    {
        return TrackingLog::with(['user', 'location', 'rfidCard'])
            ->whereHas('user')
            ->whereHas('location')
            ->latest('scanned_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get location occupancy
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getLocationOccupancy()
    {
        $locations = Location::active()->get();

        return $locations->map(function ($location) {
            $currentCount = $location->getCurrentVisitorCount();
            $percentage = $location->capacity 
                ? ($currentCount / $location->capacity) * 100 
                : 0;

            return [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'current_count' => $currentCount,
                'capacity' => $location->capacity,
                'percentage' => round($percentage, 1),
                'status' => $this->getOccupancyStatus($percentage),
            ];
        });
    }

    /**
     * Get occupancy status color
     * 
     * @param float $percentage
     * @return string
     */
    protected function getOccupancyStatus(float $percentage): string
    {
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 70) return 'warning';
        if ($percentage >= 50) return 'info';
        return 'success';
    }

    /**
     * Get chart data for dashboard
     * 
     * @param string $period
     * @return array
     */
    protected function getChartData(string $period = 'today'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        // Hourly activity (for today/week)
        $hourlyActivity = $this->getHourlyActivity($startDate);

        // Daily activity (for week/month)
        $dailyActivity = $this->getDailyActivity($startDate);

        // Activity by location
        $activityByLocation = $this->getActivityByLocation($startDate);

        // Activity by user type
        $activityByUserType = $this->getActivityByUserType($startDate);

        return [
            'hourly' => $hourlyActivity,
            'daily' => $dailyActivity,
            'by_location' => $activityByLocation,
            'by_user_type' => $activityByUserType,
        ];
    }

    /**
     * Get hourly activity data
     * 
     * @param Carbon|null $startDate
     * @return array
     */
    protected function getHourlyActivity(?Carbon $startDate): array
    {
        $query = TrackingLog::query();
        if ($startDate) {
            $query->where('scanned_at', '>=', $startDate);
        }

        $data = $query->select(
                DB::raw('HOUR(scanned_at) as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted'),
                DB::raw('SUM(CASE WHEN status = "denied" THEN 1 ELSE 0 END) as denied')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return [
            'labels' => $data->pluck('hour')->map(fn($h) => sprintf('%02d:00', $h))->toArray(),
            'total' => $data->pluck('count')->toArray(),
            'accepted' => $data->pluck('accepted')->toArray(),
            'denied' => $data->pluck('denied')->toArray(),
        ];
    }

    /**
     * Get daily activity data
     * 
     * @param Carbon|null $startDate
     * @return array
     */
    protected function getDailyActivity(?Carbon $startDate): array
    {
        $query = TrackingLog::query();
        if ($startDate) {
            $query->where('scanned_at', '>=', $startDate);
        }

        $data = $query->select(
                DB::raw('DATE(scanned_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted'),
                DB::raw('SUM(CASE WHEN status = "denied" THEN 1 ELSE 0 END) as denied')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray(),
            'total' => $data->pluck('count')->toArray(),
            'accepted' => $data->pluck('accepted')->toArray(),
            'denied' => $data->pluck('denied')->toArray(),
        ];
    }

    /**
     * Get activity by location
     * 
     * @param Carbon|null $startDate
     * @return array
     */
    protected function getActivityByLocation(?Carbon $startDate): array
    {
        $query = TrackingLog::with('location')
            ->whereHas('location');
        if ($startDate) {
            $query->where('scanned_at', '>=', $startDate);
        }

        $data = $query->select('location_id', DB::raw('COUNT(*) as count'))
            ->groupBy('location_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('location.name')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    /**
     * Get activity by user type
     * 
     * @param Carbon|null $startDate
     * @return array
     */
    protected function getActivityByUserType(?Carbon $startDate): array
    {
        $query = TrackingLog::with('user')
            ->whereHas('user');
        if ($startDate) {
            $query->where('scanned_at', '>=', $startDate);
        }

        $guestCount = (clone $query)->whereHas('user', function ($q) {
            $q->where('user_type', User::TYPE_GUEST);
        })->count();

        $employeeCount = (clone $query)->whereHas('user', function ($q) {
            $q->where('user_type', User::TYPE_EMPLOYEE);
        })->count();

        return [
            'labels' => ['Tamu', 'Pegawai'],
            'data' => [$guestCount, $employeeCount],
        ];
    }

    /**
     * Get start date for period
     * 
     * @param string $period
     * @return Carbon|null
     */
    protected function getPeriodStartDate(string $period): ?Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            'all' => null,
            default => now()->startOfDay(),
        };
    }

    /**
     * API endpoint untuk real-time dashboard data
     * Route: GET /api/dashboard/realtime
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function realtimeData()
    {
        return response()->json([
            'currently_inside' => $this->getActiveVisitors()->count(),
            'recent_activity' => $this->getRecentActivity(5),
            'location_occupancy' => $this->getLocationOccupancy(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
