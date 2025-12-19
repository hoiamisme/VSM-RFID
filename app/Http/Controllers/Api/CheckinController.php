<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfidCard;
use App\Models\User;
use App\Models\AccessRight;
use App\Models\TrackingLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckinController extends Controller
{
    /**
     * Check visitor by RFID UID
     */
    public function checkVisitor(Request $request)
    {
        $request->validate([
            'uid' => 'required|string'
        ]);

        $rfidCard = RfidCard::where('uid', $request->uid)->first();

        if (!$rfidCard) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu RFID tidak terdaftar'
            ], 404);
        }

        $user = $rfidCard->user;

        // Check if already inside UNHAN - hanya cek log terakhir di Pos Utama (MAIN)
        // Scan di gedung tidak mengubah status "di dalam UNHAN"
        $mainLocation = \App\Models\Location::where('code', 'MAIN')->first();
        $lastLogAtMain = TrackingLog::where('user_id', $user->id)
                              ->where('location_id', $mainLocation->id)
                              ->where('status', 'accepted')
                              ->latest('scanned_at')
                              ->first();

        $isInside = $lastLogAtMain && $lastLogAtMain->action_type === 'entry';

        // Get existing access rights
        $existingAccess = AccessRight::where('user_id', $user->id)
                                     ->where('can_access', true)
                                     ->where(function($q) {
                                         $q->whereNull('valid_until')
                                           ->orWhere('valid_until', '>=', now());
                                     })
                                     ->pluck('location_id')
                                     ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->user_type === 'employee' ? 'Pegawai' : 'Tamu',
                    'institution' => $user->institution,
                    'photo' => $user->photo
                ],
                'is_inside' => $isInside,
                'existing_access' => $existingAccess,
                'last_location' => $lastLogAtMain ? $lastLogAtMain->location->name : null
            ]
        ]);
    }

    /**
     * Grant access to locations and create check-in log
     */
    public function grantAccess(Request $request)
    {
        $request->validate([
            'visitor_id' => 'required|exists:users,id',
            'locations' => 'required|array|min:1',
            'locations.*' => 'exists:locations,id'
        ]);

        $user = User::findOrFail($request->visitor_id);

        // If guest, update/create access rights
        if ($user->user_type === 'guest') {
            // Valid for 30 days
            $validUntil = Carbon::now()->addDays(30);
            
            // Soft delete access rights not in new list (restore if trashed)
            AccessRight::withTrashed()
                ->where('user_id', $user->id)
                ->whereNotIn('location_id', $request->locations)
                ->forceDelete(); // Force delete to avoid duplicates
            
            // Update or create access rights (restore if soft deleted)
            foreach ($request->locations as $locationId) {
                $accessData = [
                    'can_access' => true,
                    'access_type' => 'temporary',
                    'valid_from' => now(),
                    'valid_until' => $validUntil,
                    'reason' => 'Check-in di Pos Utama oleh petugas jaga',
                    'granted_by' => null,
                    'time_restrictions' => null,
                    'deleted_at' => null // Restore if soft deleted
                ];
                
                // Check if exists (including soft deleted)
                $existing = AccessRight::withTrashed()
                    ->where('user_id', $user->id)
                    ->where('location_id', $locationId)
                    ->first();
                
                if ($existing) {
                    // Update existing (will restore if soft deleted)
                    $existing->update($accessData);
                    if ($existing->trashed()) {
                        $existing->restore();
                    }
                } else {
                    // Create new
                    AccessRight::create(array_merge($accessData, [
                        'user_id' => $user->id,
                        'location_id' => $locationId
                    ]));
                }
            }
        }

        // Create check-in log at MAIN location
        $mainLocation = \App\Models\Location::where('code', 'MAIN')->first();
        
        if ($mainLocation) {
            // Get first RFID card of user
            $rfidCard = $user->rfidCards()->first();
            
            if ($rfidCard) {
                TrackingLog::create([
                    'user_id' => $user->id,
                    'rfid_card_id' => $rfidCard->id,
                    'rfid_uid' => $rfidCard->uid,
                    'location_id' => $mainLocation->id,
                    'location_code' => $mainLocation->code,
                    'action_type' => 'entry',
                    'status' => 'accepted',
                    'scanned_at' => now(),
                    'notes' => 'Check-in di Pos Utama oleh petugas jaga'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Akses diberikan dan check-in berhasil'
        ]);
    }

    /**
     * Get today's check-ins
     */
    public function todayCheckins()
    {
        $logs = TrackingLog::with(['user', 'location'])
                           ->whereHas('user')
                           ->whereHas('location', function($q) {
                               $q->where('code', 'MAIN');
                           })
                           ->where('action_type', 'entry')
                           ->where('status', 'accepted')
                           ->whereDate('scanned_at', today())
                           ->orderBy('scanned_at', 'desc')
                           ->limit(20)
                           ->get();

        $data = $logs->map(function($log) {
            $user = $log->user;
            
            // Get granted locations (exclude MAIN)
            $locations = AccessRight::where('user_id', $user->id)
                                   ->where('can_access', true)
                                   ->with('location')
                                   ->get()
                                   ->filter(function($ar) {
                                       return $ar->location && $ar->location->code !== 'MAIN';
                                   })
                                   ->pluck('location.name')
                                   ->join(', ');

            // Check status untuk entry ini SPESIFIK - apakah sudah ada exit di MAIN setelah entry ini
            // Exit di gedung (bukan MAIN) tidak mengubah status "di dalam UNHAN"
            // Hanya exit di Pos Utama (MAIN) yang menandakan keluar dari UNHAN
            $mainLocation = \App\Models\Location::where('code', 'MAIN')->first();
            $exitAtMainAfterThisEntry = TrackingLog::where('user_id', $user->id)
                                  ->where('location_id', $mainLocation->id)
                                  ->where('action_type', 'exit')
                                  ->where('status', 'accepted')
                                  ->where('scanned_at', '>', $log->scanned_at)
                                  ->exists();

            $status = $exitAtMainAfterThisEntry ? 'Sudah keluar' : 'Di dalam';
            $isInside = !$exitAtMainAfterThisEntry;

            return [
                'time' => $log->scanned_at->format('H:i'),
                'name' => $user->name,
                'institution' => $user->institution ?? '-',
                'locations' => $locations ?: 'Semua area',
                'status' => $status,
                'is_inside' => $isInside
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Checkout visitor - record exit from UNHAN
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);

        // Get main location
        $mainLocation = \App\Models\Location::where('code', 'MAIN')->first();
        
        if (!$mainLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi Pos Utama tidak ditemukan'
            ], 500);
        }

        // Check if user is actually inside UNHAN (cek log terakhir di MAIN)
        $lastLogAtMain = TrackingLog::where('user_id', $user->id)
                              ->where('location_id', $mainLocation->id)
                              ->where('status', 'accepted')
                              ->latest('scanned_at')
                              ->first();

        if (!$lastLogAtMain || $lastLogAtMain->action_type !== 'entry') {
            return response()->json([
                'success' => false,
                'message' => 'Tamu ini tidak sedang berada di dalam area UNHAN atau sudah check-out'
            ], 400);
        }

        // Get RFID card
        $rfidCard = RfidCard::where('user_id', $user->id)->first();

        // Create exit log at MAIN (keluar dari UNHAN)
        TrackingLog::create([
            'user_id' => $user->id,
            'rfid_card_id' => $rfidCard ? $rfidCard->id : null,
            'rfid_uid' => $rfidCard ? $rfidCard->uid : null,
            'location_id' => $mainLocation->id,
            'location_code' => $mainLocation->code,
            'action_type' => 'exit',
            'status' => 'accepted',
            'scanned_at' => now(),
            'notes' => 'Check-out di Pos Utama oleh petugas jaga'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'checkout_time' => now()->format('Y-m-d H:i:s')
            ]
        ]);
    }
}
