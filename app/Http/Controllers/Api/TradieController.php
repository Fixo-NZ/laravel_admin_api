<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tradie;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TradieController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Get all tradies
     */
    public function index(): JsonResponse
    {
        try {
            $tradies = Tradie::all();
            return response()->json([
                'success' => true,
                'data' => $tradies
            ]);
        } catch (\Exception $e) {
            Log::error('Get tradies error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new tradie
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:tradies,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'business_name' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'insurance_details' => 'nullable|string|max:500',
            'years_experience' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'availability_status' => 'nullable|in:available,busy,unavailable',
            'service_radius' => 'nullable|integer|min:0',
            'firebase_uid' => 'nullable|string|unique:tradies,firebase_uid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tradie = Tradie::create($request->all());

            // Auto-sync to Firebase if firebase_uid is provided
            if ($request->firebase_uid && config('firebase.auto_sync.on_create')) {
                $this->firebaseService->syncTradieToFirebase($tradie, $request->firebase_uid);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tradie created successfully',
                'data' => $tradie
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create tradie error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a specific tradie
     */
    public function show($id): JsonResponse
    {
        try {
            $tradie = Tradie::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $tradie
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tradie not found'
            ], 404);
        }
    }

    /**
     * Update a tradie
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:tradies,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'business_name' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'insurance_details' => 'nullable|string|max:500',
            'years_experience' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'availability_status' => 'nullable|in:available,busy,unavailable',
            'service_radius' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tradie = Tradie::findOrFail($id);
            $tradie->update($request->all());

            // Auto-sync to Firebase if enabled and firebase_uid exists
            if ($tradie->firebase_uid && config('firebase.auto_sync.on_update')) {
                $this->firebaseService->syncTradieToFirebase($tradie, $tradie->firebase_uid);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tradie updated successfully',
                'data' => $tradie
            ]);

        } catch (\Exception $e) {
            Log::error('Update tradie error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a tradie
     */
    public function destroy($id): JsonResponse
    {
        try {
            $tradie = Tradie::findOrFail($id);
            
            // Delete from Firebase if enabled and firebase_uid exists
            if ($tradie->firebase_uid && config('firebase.auto_sync.on_delete')) {
                $this->firebaseService->deleteFirebaseUser($tradie->firebase_uid);
            }

            $tradie->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tradie deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete tradie error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get tradie by Firebase UID
     */
    public function getByFirebaseUid($firebaseUid): JsonResponse
    {
        try {
            $tradie = Tradie::where('firebase_uid', $firebaseUid)->first();
            
            if (!$tradie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tradie not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $tradie
            ]);

        } catch (\Exception $e) {
            Log::error('Get tradie by Firebase UID error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Search tradies by location and service
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius' => 'nullable|integer|min:1',
            'service_type' => 'nullable|string',
            'availability_status' => 'nullable|in:available,busy,unavailable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Tradie::query();

            // Filter by availability status
            if ($request->availability_status) {
                $query->where('availability_status', $request->availability_status);
            }

            // Add location-based search if coordinates provided
            if ($request->latitude && $request->longitude) {
                $radius = $request->radius ?? 50; // Default 50km radius
                
                // Using Haversine formula for distance calculation
                $query->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
                ", [$request->latitude, $request->longitude, $request->latitude])
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
            }

            $tradies = $query->get();

            return response()->json([
                'success' => true,
                'data' => $tradies
            ]);

        } catch (\Exception $e) {
            Log::error('Search tradies error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}