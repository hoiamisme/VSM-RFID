<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\User;
use App\Models\Location;
use App\Models\AccessRight;
use App\Models\TrackingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * RfidScanController
 * 
 * Controller untuk menangani proses scanning RFID
 * 
 * Logika Tracking:
 * 1. Scan pertama di lokasi → ENTRY (masuk)
 * 2. Scan kedua di lokasi yang sama → EXIT (keluar)
 * 3. Scan di lokasi berbeda → MOVE (pindah lokasi tanpa exit)
 * 4. Tidak punya akses → DENIED (ditolak)
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class RfidScanController extends Controller
{
    /**
     * Tampilkan halaman scan RFID
     * Route: GET /scan/{location?}
     * 
     * @param string|null $locationCode
     * @return \Illuminate\View\View
     */
    public function showScanPage(?string $locationCode = null)
    {
        // Get all active locations untuk dropdown
        $locations = Location::active()->orderBy('name')->get();
        
        // Get selected location jika ada parameter
        $selectedLocation = null;
        if ($locationCode) {
            $selectedLocation = Location::byCode($locationCode)->first();
        }

        return view('rfid.scan', compact('locations', 'selectedLocation'));
    }

    /**
     * Process RFID scan
     * Route: POST /rfid/scan
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processRfid(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'uid' => 'required|string|max:255',
                'location' => 'required|string|exists:locations,code',
            ]);

            $uid = trim($validated['uid']);
            $locationCode = $validated['location'];

            // Cari lokasi berdasarkan code
            $location = Location::byCode($locationCode)->first();

            if (!$location) {
                return $this->errorResponse('Lokasi tidak ditemukan', 404);
            }

            if (!$location->is_active) {
                return $this->errorResponse('Lokasi tidak aktif', 403);
            }

            // Cari RFID Card berdasarkan UID
            $rfidCard = RfidCard::with('user')
                ->byUid($uid)
                ->first();

            if (!$rfidCard) {
                return $this->errorResponse('Kartu RFID tidak terdaftar. UID: ' . $uid, 404);
            }

            // Validasi kartu RFID
            if (!$rfidCard->isValid()) {
                $reason = $rfidCard->isExpired() 
                    ? 'Kartu sudah kadaluarsa' 
                    : 'Kartu tidak aktif (Status: ' . $rfidCard->status_name . ')';

                $this->logDeniedAccess($rfidCard, $location, $reason, $request);

                return $this->errorResponse($reason, 403);
            }

            $user = $rfidCard->user;

            // VALIDASI: Untuk gedung (bukan MAIN), user HARUS sudah check-in di MAIN dulu
            if ($locationCode !== 'MAIN') {
                $isInsideMain = $this->checkUserInsideMain($user->id);
                
                if (!$isInsideMain) {
                    $reason = 'Anda harus check-in di Gerbang Utama terlebih dahulu sebelum dapat mengakses gedung';
                    $this->logDeniedAccess($rfidCard, $location, $reason, $request);

                    return $this->errorResponse($reason, 403);
                }
            }

            // Check hak akses user ke lokasi
            $hasAccess = $this->checkAccess($user->id, $location->id);

            if (!$hasAccess) {
                $reason = 'Anda tidak memiliki hak akses ke lokasi ini';
                $this->logDeniedAccess($rfidCard, $location, $reason, $request);

                return $this->errorResponse($reason, 403);
            }

            // Determine action type (entry/exit/move)
            $actionType = $this->determineActionType($user->id, $location->id);

            // Save tracking log
            $trackingLog = $this->saveTrackingLog([
                'rfid_card_id' => $rfidCard->id,
                'user_id' => $user->id,
                'location_id' => $location->id,
                'action_type' => $actionType,
                'status' => TrackingLog::STATUS_ACCEPTED,
                'scanned_at' => now(),
                'rfid_uid' => $uid,
                'location_code' => $locationCode,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Update last used timestamp pada RFID card
            $rfidCard->updateLastUsed();

            // Prepare response data
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->user_type_name,
                    'photo' => $user->photo_url,
                ],
                'location' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'full_name' => $location->full_name,
                ],
                'tracking' => [
                    'id' => $trackingLog->id,
                    'action_type' => $trackingLog->action_type,
                    'action_name' => $trackingLog->action_type_name,
                    'status' => $trackingLog->status,
                    'status_name' => $trackingLog->status_name,
                    'scanned_at' => $trackingLog->scanned_at->format('Y-m-d H:i:s'),
                    'time_ago' => $trackingLog->time_ago,
                ],
                'message' => $this->getSuccessMessage($actionType, $user->name, $location->name),
            ];

            return $this->successResponse($responseData, 'Scan berhasil');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Data tidak valid: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('RFID Scan Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return $this->errorResponse('Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check if user has access to location
     * 
     * @param int $userId
     * @param int $locationId
     * @return bool
     */
    protected function checkAccess(int $userId, int $locationId): bool
    {
        // Get user info
        $user = \App\Models\User::find($userId);
        
        // Employees and Kadets have default access to all locations
        if ($user && in_array($user->user_type, ['employee', 'kadet'])) {
            return true;
        }

        $accessRight = AccessRight::where('user_id', $userId)
            ->where('location_id', $locationId)
            ->first();

        if (!$accessRight) {
            return false; // Tidak ada access right
        }

        // Check if access is allowed
        if (!$accessRight->can_access) {
            return false;
        }

        // Check if access right is valid
        return $accessRight->isValidAt(now());
    }

    /**
     * Determine action type based on previous logs
     * 
     * Logika:
     * - Jika belum ada log ENTRY yang aktif → ENTRY
     * - Jika ada log ENTRY di lokasi yang sama → EXIT
     * - Jika ada log ENTRY di lokasi berbeda → MOVE
     * 
     * @param int $userId
     * @param int $locationId
     * @return string
     */
    protected function determineActionType(int $userId, int $locationId): string
    {
        // Get last active entry (entry or move) for this user
        $lastEntry = TrackingLog::where('user_id', $userId)
            ->where('status', TrackingLog::STATUS_ACCEPTED)
            ->whereIn('action_type', [TrackingLog::ACTION_ENTRY, TrackingLog::ACTION_MOVE])
            ->latest('scanned_at')
            ->first();

        // Jika tidak ada entry sebelumnya → ENTRY
        if (!$lastEntry) {
            return TrackingLog::ACTION_ENTRY;
        }

        // Check if user has exited since last entry
        $hasExited = TrackingLog::where('user_id', $userId)
            ->where('location_id', $lastEntry->location_id)
            ->where('action_type', TrackingLog::ACTION_EXIT)
            ->where('scanned_at', '>', $lastEntry->scanned_at)
            ->exists();

        // Jika sudah exit → ENTRY (masuk lagi)
        if ($hasExited) {
            return TrackingLog::ACTION_ENTRY;
        }

        // Jika scan di lokasi yang sama → EXIT
        if ($lastEntry->location_id == $locationId) {
            return TrackingLog::ACTION_EXIT;
        }

        // Jika scan di lokasi berbeda → MOVE
        return TrackingLog::ACTION_MOVE;
    }

    /**
     * Save tracking log to database
     * 
     * @param array $data
     * @return TrackingLog
     */
    protected function saveTrackingLog(array $data): TrackingLog
    {
        return TrackingLog::create($data);
    }

    /**
     * Log denied access attempt
     * 
     * @param RfidCard $rfidCard
     * @param Location $location
     * @param string $reason
     * @param Request $request
     * @return TrackingLog
     */
    protected function logDeniedAccess(
        RfidCard $rfidCard, 
        Location $location, 
        string $reason, 
        Request $request
    ): TrackingLog {
        return TrackingLog::create([
            'rfid_card_id' => $rfidCard->id,
            'user_id' => $rfidCard->user_id,
            'location_id' => $location->id,
            'action_type' => TrackingLog::ACTION_DENIED,
            'status' => TrackingLog::STATUS_DENIED,
            'scanned_at' => now(),
            'rfid_uid' => $rfidCard->uid,
            'location_code' => $location->code,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'denial_reason' => $reason,
        ]);
    }

    /**
     * Get success message based on action type
     * 
     * @param string $actionType
     * @param string $userName
     * @param string $locationName
     * @return string
     */
    protected function getSuccessMessage(
        string $actionType, 
        string $userName, 
        string $locationName
    ): string {
        return match($actionType) {
            TrackingLog::ACTION_ENTRY => "Selamat datang, {$userName}! Anda telah masuk ke {$locationName}.",
            TrackingLog::ACTION_EXIT => "Sampai jumpa, {$userName}! Anda telah keluar dari {$locationName}.",
            TrackingLog::ACTION_MOVE => "{$userName} telah berpindah ke {$locationName}.",
            default => "Scan berhasil.",
        };
    }

    /**
     * Check if user is already checked-in at MAIN location
     * User must check-in at MAIN (Gerbang Utama) before accessing other buildings
     * 
     * @param int $userId
     * @return bool
     */
    protected function checkUserInsideMain(int $userId): bool
    {
        // Get MAIN location
        $mainLocation = Location::where('code', 'MAIN')->first();
        
        if (!$mainLocation) {
            return false;
        }

        // Check last log at MAIN location
        $lastMainEntry = TrackingLog::where('user_id', $userId)
            ->where('location_code', 'MAIN')
            ->where('action_type', 'entry')
            ->where('status', 'accepted')
            ->latest('created_at')
            ->first();

        // If no entry at MAIN, user is not inside
        if (!$lastMainEntry) {
            return false;
        }

        // Check if user has exited from MAIN after the last entry
        $hasExitedMain = TrackingLog::where('user_id', $userId)
            ->where('location_code', 'MAIN')
            ->where('action_type', 'exit')
            ->where('status', 'accepted')
            ->where('created_at', '>', $lastMainEntry->created_at)
            ->exists();

        // User is inside MAIN if entry exists and no exit after that
        return !$hasExitedMain;
    }

    /**
     * Success response helper
     * 
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, string $message = 'Success'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    /**
     * Error response helper
     * 
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }

    /**
     * Get active visitors in a location
     * Route: GET /rfid/location/{locationCode}/visitors
     * 
     * @param string $locationCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocationVisitors(string $locationCode)
    {
        $location = Location::byCode($locationCode)->first();

        if (!$location) {
            return $this->errorResponse('Lokasi tidak ditemukan', 404);
        }

        $visitors = $location->getCurrentVisitors();

        $visitorsData = $visitors->map(function ($user) use ($location) {
            $lastLog = TrackingLog::where('user_id', $user->id)
                ->where('location_id', $location->id)
                ->whereIn('action_type', [TrackingLog::ACTION_ENTRY, TrackingLog::ACTION_MOVE])
                ->latest('scanned_at')
                ->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->user_type_name,
                'photo' => $user->photo_url,
                'entry_time' => $lastLog ? $lastLog->scanned_at->format('Y-m-d H:i:s') : null,
                'duration' => $lastLog ? $lastLog->scanned_at->diffForHumans() : null,
            ];
        });

        return $this->successResponse([
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
            ],
            'count' => $visitorsData->count(),
            'capacity' => $location->capacity,
            'visitors' => $visitorsData,
        ]);
    }
}
