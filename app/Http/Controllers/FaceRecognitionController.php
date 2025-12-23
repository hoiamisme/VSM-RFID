<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RfidCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * FaceRecognitionController
 * 
 * Controller untuk menangani face recognition verification dan enrollment
 * 
 * @author VMS Development Team
 * @version 2.0
 */
class FaceRecognitionController extends Controller
{
    /**
     * Verify face against stored descriptor
     * 
     * Route: POST /api/face-verify
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'live_descriptor' => 'required|array',
                'live_descriptor.*' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user_id;
            $liveDescriptor = $request->live_descriptor;

            // Get user with face descriptor
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            if (empty($user->face_descriptor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User belum mendaftarkan wajah'
                ], 400);
            }

            // Decode stored descriptor
            $storedDescriptor = json_decode($user->face_descriptor, true);

            if (!is_array($storedDescriptor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face descriptor tidak valid'
                ], 500);
            }

            // Calculate Euclidean distance
            $distance = $this->calculateEuclideanDistance($liveDescriptor, $storedDescriptor);
            
            // Convert to similarity (0-1 scale)
            $similarity = 1 - $distance;
            
            // Threshold untuk match (default: 0.6 = 60%)
            $threshold = config('face_recognition.threshold', 0.6);
            $isMatch = $similarity >= $threshold;

            // Log verification attempt
            Log::info('Face verification attempt', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'similarity' => round($similarity, 4),
                'threshold' => $threshold,
                'match' => $isMatch,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'match' => $isMatch,
                'similarity' => round($similarity, 4),
                'confidence' => round($similarity * 100, 2),
                'threshold' => $threshold,
                'message' => $isMatch 
                    ? 'Wajah cocok! Verifikasi berhasil.' 
                    : 'Wajah tidak cocok. Verifikasi gagal.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'photo' => $user->photo_url ?? asset('images/default-avatar.png')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Face verification error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat verifikasi wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enroll face descriptor for user
     * 
     * Route: POST /api/face-enroll
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enroll(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'face_descriptor' => 'required|array',
                'face_descriptor.*' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user_id;
            $faceDescriptor = $request->face_descriptor;

            // Validate descriptor length (should be 128D)
            if (count($faceDescriptor) !== 128) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face descriptor harus 128 dimensi'
                ], 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Save face descriptor
            $user->face_descriptor = json_encode($faceDescriptor);
            $user->face_registered_at = now();
            $user->require_face_verification = $request->require_verification ?? false;
            $user->save();

            Log::info('Face enrolled successfully', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'require_verification' => $user->require_face_verification,
                'registered_at' => $user->face_registered_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil didaftarkan!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'face_registered_at' => $user->face_registered_at->format('Y-m-d H:i:s'),
                    'require_face_verification' => $user->require_face_verification
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Face enrollment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mendaftarkan wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete face descriptor for user
     * 
     * Route: DELETE /api/face-delete/{userId}
     * 
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $user->face_descriptor = null;
            $user->face_registered_at = null;
            $user->require_face_verification = false;
            $user->save();

            Log::info('Face descriptor deleted', [
                'user_id' => $userId,
                'user_name' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data wajah berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Face deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data wajah'
            ], 500);
        }
    }

    /**
     * Calculate Euclidean distance between two descriptors
     * 
     * @param array $descriptor1
     * @param array $descriptor2
     * @return float
     */
    protected function calculateEuclideanDistance(array $descriptor1, array $descriptor2): float
    {
        if (count($descriptor1) !== count($descriptor2)) {
            throw new \Exception('Descriptor dimensions do not match');
        }

        $sumSquares = 0;
        for ($i = 0; $i < count($descriptor1); $i++) {
            $diff = $descriptor1[$i] - $descriptor2[$i];
            $sumSquares += $diff * $diff;
        }

        return sqrt($sumSquares);
    }

    /**
     * Get face recognition stats for dashboard
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $totalUsers = User::count();
        $usersWithFace = User::whereNotNull('face_descriptor')->count();
        $requireVerification = User::where('require_face_verification', true)->count();

        return response()->json([
            'total_users' => $totalUsers,
            'users_with_face' => $usersWithFace,
            'require_verification' => $requireVerification,
            'enrollment_rate' => $totalUsers > 0 ? round(($usersWithFace / $totalUsers) * 100, 2) : 0
        ]);
    }
}
