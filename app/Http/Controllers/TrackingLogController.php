<?php

namespace App\Http\Controllers;

use App\Models\TrackingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingLogController extends Controller
{
    /**
     * Update face verification status for a tracking log
     */
    public function updateFaceStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'face_verified' => 'required|boolean',
            'face_similarity' => 'required|numeric|min:0|max:1',
            'verification_method' => 'required|string|in:rfid_only,rfid+face'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $trackingLog = TrackingLog::find($id);

        if (!$trackingLog) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking log tidak ditemukan'
            ], 404);
        }

        $trackingLog->update([
            'face_verified' => $request->face_verified,
            'face_similarity' => $request->face_similarity,
            'verification_method' => $request->verification_method
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status verifikasi wajah berhasil diperbarui',
            'data' => $trackingLog
        ]);
    }
}
