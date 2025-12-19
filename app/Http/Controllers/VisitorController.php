<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RfidCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * VisitorController
 * 
 * Controller untuk mengelola data tamu dan pegawai
 * 
 * @author VMS Development Team
 * @version 1.0
 */
class VisitorController extends Controller
{
    /**
     * Display list of visitors
     * Route: GET /visitors
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::with(['rfidCards', 'trackingLogs'])
            ->orderBy('created_at', 'desc');

        // Filter by user type
        if ($request->has('type') && in_array($request->type, ['guest', 'employee'])) {
            $query->ofType($request->type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $visitors = $query->paginate(20);

        return view('visitors.index', compact('visitors'));
    }

    /**
     * Show form to create new visitor
     * Route: GET /visitors/create
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('visitors.create');
    }

    /**
     * Store new visitor
     * Route: POST /visitors
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'required|in:guest,employee,kadet',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // Max 2MB
            'rfid_uid' => 'nullable|string|max:255|unique:rfid_cards,uid',
            'rfid_card_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_type' => $request->user_type,
                'address' => $request->address,
                'institution' => $request->institution,
                'employee_id' => $request->employee_id,
                'photo' => $photoPath,
                'is_active' => true,
            ]);

            // Create RFID card if UID provided
            if ($request->filled('rfid_uid')) {
                RfidCard::create([
                    'uid' => $request->rfid_uid,
                    'user_id' => $user->id,
                    'card_number' => $request->rfid_card_number,
                    'status' => RfidCard::STATUS_ACTIVE,
                    'registered_at' => now(),
                ]);
            }

            // Create access rights for guests based on selected locations
            // Employees get default access via checkAccess logic in RfidScanController
            if ($request->user_type === 'guest') {
                $selectedLocations = $request->input('access_locations', []);
                
                // If no location selected, give access to main entrance by default
                if (empty($selectedLocations)) {
                    $mainLocation = \App\Models\Location::where('code', 'MAIN')->first();
                    if ($mainLocation) {
                        $selectedLocations = [$mainLocation->id];
                    }
                }
                
                // Create access rights for selected locations
                foreach ($selectedLocations as $locationId) {
                    \App\Models\AccessRight::create([
                        'user_id' => $user->id,
                        'location_id' => $locationId,
                        'can_access' => true,
                        'access_type' => 'temporary',
                        'valid_from' => now(),
                        'valid_until' => now()->addDays(30), // Valid 30 hari
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('visitors.show', $user->id)
                ->with('success', 'Data ' . ($request->user_type == 'guest' ? 'tamu' : 'pegawai') . ' berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded photo if error
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show visitor detail
     * Route: GET /visitors/{id}
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        $visitor = User::with([
            'rfidCards',
            'accessRights.location',
            'trackingLogs' => function ($query) {
                $query->latest('scanned_at')->limit(50);
            }
        ])->findOrFail($id);

        // Get statistics
        $stats = [
            'total_scans' => $visitor->trackingLogs()->count(),
            'accepted_scans' => $visitor->trackingLogs()->accepted()->count(),
            'denied_scans' => $visitor->trackingLogs()->denied()->count(),
            'unique_locations' => $visitor->trackingLogs()
                ->accepted()
                ->distinct('location_id')
                ->count('location_id'),
            'current_location' => $visitor->getCurrentLocation(),
            'is_inside' => $visitor->isCurrentlyInside(),
            'last_entry' => $visitor->trackingLogs()
                ->accepted()
                ->whereIn('action_type', ['entry', 'move'])
                ->orderBy('scanned_at', 'desc')
                ->first(),
        ];

        // Get recent activity (last 20 logs)
        $recentActivity = $visitor->trackingLogs()
            ->with('location')
            ->latest('scanned_at')
            ->limit(20)
            ->get();

        return view('visitors.show', compact('visitor', 'stats', 'recentActivity'));
    }

    /**
     * Show form to edit visitor
     * Route: GET /visitors/{id}/edit
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $visitor = User::with('rfidCards')->findOrFail($id);
        return view('visitors.edit', compact('visitor'));
    }

    /**
     * Update visitor data
     * Route: PUT /visitors/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $visitor = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($visitor->photo) {
                    Storage::disk('public')->delete($visitor->photo);
                }

                $visitor->photo = $request->file('photo')->store('photos', 'public');
            }

            $visitor->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'institution' => $request->institution,
                'employee_id' => $request->employee_id,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('visitors.show', $visitor->id)
                ->with('success', 'Data berhasil diupdate!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete visitor (soft delete)
     * Route: DELETE /visitors/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        try {
            $visitor = User::findOrFail($id);
            $visitor->delete(); // Soft delete

            return redirect()->route('visitors.index')
                ->with('success', 'Data berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Register RFID card for visitor
     * Route: POST /visitors/{id}/register-rfid
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerRfid(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required|string|max:255|unique:rfid_cards,uid',
            'card_number' => 'nullable|string|max:50',
            'expired_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $visitor = User::findOrFail($id);

            $rfidCard = RfidCard::create([
                'uid' => $request->uid,
                'user_id' => $visitor->id,
                'card_number' => $request->card_number,
                'status' => RfidCard::STATUS_ACTIVE,
                'registered_at' => now(),
                'expired_at' => $request->expired_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kartu RFID berhasil didaftarkan!',
                'data' => [
                    'id' => $rfidCard->id,
                    'uid' => $rfidCard->uid,
                    'card_number' => $rfidCard->card_number,
                    'status' => $rfidCard->status_name,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
