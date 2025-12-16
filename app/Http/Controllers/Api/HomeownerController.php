<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Homeowner;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class HomeownerController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Get all homeowners
     */
    public function index(): JsonResponse
    {
        try {
            $homeowners = Homeowner::all();
            return response()->json([
                'success' => true,
                'data' => $homeowners
            ]);
        } catch (\Exception $e) {
            Log::error('Get homeowners error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new homeowner
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:homeowners,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'firebase_uid' => 'nullable|string|unique:homeowners,firebase_uid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $homeowner = Homeowner::create($request->all());

            // Auto-sync to Firebase if firebase_uid is provided
            if ($request->firebase_uid && config('firebase.auto_sync.on_create')) {
                $this->firebaseService->syncHomeownerToFirebase($homeowner, $request->firebase_uid);
            }

            return response()->json([
                'success' => true,
                'message' => 'Homeowner created successfully',
                'data' => $homeowner
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create homeowner error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a specific homeowner
     */
    public function show($id): JsonResponse
    {
        try {
            $homeowner = Homeowner::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $homeowner
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Homeowner not found'
            ], 404);
        }
    }

    /**
     * Update a homeowner
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:homeowners,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $homeowner = Homeowner::findOrFail($id);
            $homeowner->update($request->all());

            // Auto-sync to Firebase if enabled and firebase_uid exists
            if ($homeowner->firebase_uid && config('firebase.auto_sync.on_update')) {
                $this->firebaseService->syncHomeownerToFirebase($homeowner, $homeowner->firebase_uid);
            }

            return response()->json([
                'success' => true,
                'message' => 'Homeowner updated successfully',
                'data' => $homeowner
            ]);

        } catch (\Exception $e) {
            Log::error('Update homeowner error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a homeowner
     */
    public function destroy($id): JsonResponse
    {
        try {
            $homeowner = Homeowner::findOrFail($id);
            
            // Delete from Firebase if enabled and firebase_uid exists
            if ($homeowner->firebase_uid && config('firebase.auto_sync.on_delete')) {
                $this->firebaseService->deleteFirebaseUser($homeowner->firebase_uid);
            }

            $homeowner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Homeowner deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete homeowner error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get homeowner by Firebase UID
     */
    public function getByFirebaseUid($firebaseUid): JsonResponse
    {
        try {
            $homeowner = Homeowner::where('firebase_uid', $firebaseUid)->first();
            
            if (!$homeowner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homeowner not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $homeowner
            ]);

        } catch (\Exception $e) {
            Log::error('Get homeowner by Firebase UID error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}