<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Models\Homeowner;
use App\Models\Tradie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FirebaseController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Sync homeowner to Firebase
     */
    public function syncHomeowner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'homeowner_id' => 'required|exists:homeowners,id',
            'firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $homeowner = Homeowner::findOrFail($request->homeowner_id);
            $success = $this->firebaseService->syncHomeownerToFirebase($homeowner, $request->firebase_uid);

            if ($success) {
                // Update homeowner with Firebase UID
                $homeowner->update(['firebase_uid' => $request->firebase_uid]);

                return response()->json([
                    'success' => true,
                    'message' => 'Homeowner synced to Firebase successfully',
                    'data' => [
                        'homeowner_id' => $homeowner->id,
                        'firebase_uid' => $request->firebase_uid
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync homeowner to Firebase'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Homeowner sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Sync tradie to Firebase
     */
    public function syncTradie(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tradie_id' => 'required|exists:tradies,id',
            'firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tradie = Tradie::findOrFail($request->tradie_id);
            $success = $this->firebaseService->syncTradieToFirebase($tradie, $request->firebase_uid);

            if ($success) {
                // Update tradie with Firebase UID
                $tradie->update(['firebase_uid' => $request->firebase_uid]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tradie synced to Firebase successfully',
                    'data' => [
                        'tradie_id' => $tradie->id,
                        'firebase_uid' => $request->firebase_uid
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync tradie to Firebase'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Tradie sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Verify Firebase token and get user data
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $firebaseUid = $this->firebaseService->verifyIdToken($request->id_token);

            if (!$firebaseUid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Firebase token'
                ], 401);
            }

            // Try to find user in Laravel database
            $homeowner = Homeowner::where('firebase_uid', $firebaseUid)->first();
            $tradie = Tradie::where('firebase_uid', $firebaseUid)->first();

            $userData = null;
            $userType = null;

            if ($homeowner) {
                $userData = $homeowner;
                $userType = 'homeowner';
            } elseif ($tradie) {
                $userData = $tradie;
                $userType = 'tradie';
            }

            return response()->json([
                'success' => true,
                'message' => 'Token verified successfully',
                'data' => [
                    'firebase_uid' => $firebaseUid,
                    'user_type' => $userType,
                    'user_data' => $userData
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Token verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user data by Firebase UID
     */
    public function getUserByFirebaseUid(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $firebaseUid = $request->firebase_uid;

            // Try to find user in Laravel database
            $homeowner = Homeowner::where('firebase_uid', $firebaseUid)->first();
            $tradie = Tradie::where('firebase_uid', $firebaseUid)->first();

            if ($homeowner) {
                return response()->json([
                    'success' => true,
                    'message' => 'User found',
                    'data' => [
                        'user_type' => 'homeowner',
                        'user_data' => $homeowner
                    ]
                ]);
            }

            if ($tradie) {
                return response()->json([
                    'success' => true,
                    'message' => 'User found',
                    'data' => [
                        'user_type' => 'tradie',
                        'user_data' => $tradie
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}