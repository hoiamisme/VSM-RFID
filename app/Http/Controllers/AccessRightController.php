<?php

namespace App\Http\Controllers;

use App\Models\AccessRight;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccessRightController extends Controller
{
    /**
     * Display a listing of access rights
     */
    public function index(Request $request)
    {
        $query = AccessRight::with(['user', 'location'])
            ->whereHas('user')
            ->whereHas('location');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by location
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Filter by access type
        if ($request->filled('access_type')) {
            $query->where('access_type', $request->access_type);
        }

        $accessRights = $query->latest()->paginate(20);
        $users = User::where('user_type', 'guest')->orderBy('name')->get();
        $locations = Location::where('code', '!=', 'MAIN')->orderBy('name')->get();

        return view('access-rights.index', compact('accessRights', 'users', 'locations'));
    }

    /**
     * Show the form for creating a new access right
     */
    public function create()
    {
        $users = User::where('user_type', 'guest')->orderBy('name')->get();
        $locations = Location::where('code', '!=', 'MAIN')->where('is_active', true)->orderBy('name')->get();

        return view('access-rights.create', compact('users', 'locations'));
    }

    /**
     * Store a newly created access right
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'location_ids' => 'required|array|min:1',
            'location_ids.*' => 'exists:locations,id',
            'access_type' => 'required|in:permanent,temporary,scheduled',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'reason' => 'nullable|string'
        ]);

        $user = User::find($validated['user_id']);
        
        foreach ($validated['location_ids'] as $locationId) {
            $data = [
                'user_id' => $validated['user_id'],
                'location_id' => $locationId,
                'can_access' => true,
                'access_type' => $validated['access_type'],
                'valid_from' => $validated['valid_from'] ?? now(),
                'valid_until' => $validated['valid_until'],
                'reason' => $validated['reason'],
                'granted_by' => auth()->id(),
            ];

            // Check if exists (including soft deleted)
            $existing = AccessRight::withTrashed()
                ->where('user_id', $validated['user_id'])
                ->where('location_id', $locationId)
                ->first();

            if ($existing) {
                $existing->update($data);
                if ($existing->trashed()) {
                    $existing->restore();
                }
            } else {
                AccessRight::create($data);
            }
        }

        return redirect()->route('access-rights.index')
            ->with('success', 'Hak akses berhasil diberikan');
    }

    /**
     * Remove the specified access right
     */
    public function destroy(AccessRight $accessRight)
    {
        $accessRight->delete();

        return redirect()->route('access-rights.index')
            ->with('success', 'Hak akses berhasil dicabut');
    }

    /**
     * Bulk revoke access for a user
     */
    public function revokeUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        AccessRight::where('user_id', $request->user_id)->delete();

        return redirect()->route('access-rights.index')
            ->with('success', 'Semua hak akses user berhasil dicabut');
    }
}
