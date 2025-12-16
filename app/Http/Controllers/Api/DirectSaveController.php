<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Homeowner;
use App\Models\Tradie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DirectSaveController extends Controller
{
    /**
     * Direct save homeowner without any Firebase dependency
     */
    public function saveHomeowner(Request $request): JsonResponse
    {
        try {
            Log::info('Direct save homeowner called', $request->all());
            
            $homeowner = Homeowner::create([
                'firebase_uid' => $request->input('firebase_uid', 'manual_' . time()),
                'first_name' => $request->input('first_name', 'Unknown'),
                'last_name' => $request->input('last_name'), // Allow null since column is nullable
                'email' => $request->input('email', 'unknown@example.com'),
                'password' => bcrypt('password123'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'city' => $request->input('city'),
                'region' => $request->input('region'),
                'postal_code' => $request->input('postal_code'),
                'latitude' => $request->input('latitude') ? (float)$request->input('latitude') : null,
                'longitude' => $request->input('longitude') ? (float)$request->input('longitude') : null,
            ]);

            Log::info('Homeowner created successfully', ['id' => $homeowner->id]);

            return response()->json([
                'success' => true,
                'message' => 'Homeowner saved directly to database!',
                'data' => $homeowner,
                'debug' => [
                    'request_data' => $request->all(),
                    'created_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Direct save homeowner failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save homeowner',
                'error' => $e->getMessage(),
                'debug' => [
                    'request_data' => $request->all(),
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile(),
                ]
            ], 500);
        }
    }

    /**
     * Direct save tradie without any Firebase dependency
     */
    public function saveTradie(Request $request): JsonResponse
    {
        try {
            Log::info('Direct save tradie called', $request->all());
            
            $tradie = Tradie::create([
                'firebase_uid' => $request->input('firebase_uid', 'manual_' . time()),
                'first_name' => $request->input('first_name', 'Unknown'),
                'last_name' => $request->input('last_name'), // Allow null since column is nullable
                'middle_name' => $request->input('middle_name'), // Allow null since column is nullable
                'email' => $request->input('email', 'unknown@example.com'),
                'password' => bcrypt('password123'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'city' => $request->input('city'),
                'region' => $request->input('region'),
                'postal_code' => $request->input('postal_code'),
                'latitude' => $request->input('latitude') ? (float)$request->input('latitude') : null,
                'longitude' => $request->input('longitude') ? (float)$request->input('longitude') : null,
                'business_name' => $request->input('business_name'),
                'license_number' => $request->input('license_number'),
                'years_experience' => $request->input('years_experience') ? (int)$request->input('years_experience') : null,
                'hourly_rate' => $request->input('hourly_rate') ? (float)$request->input('hourly_rate') : null,
                'availability_status' => $request->input('availability_status', 'available'),
            ]);

            Log::info('Tradie created successfully', ['id' => $tradie->id]);

            return response()->json([
                'success' => true,
                'message' => 'Tradie saved directly to database!',
                'data' => $tradie,
                'debug' => [
                    'request_data' => $request->all(),
                    'created_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Direct save tradie failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save tradie',
                'error' => $e->getMessage(),
                'debug' => [
                    'request_data' => $request->all(),
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile(),
                ]
            ], 500);
        }
    }

    /**
     * Get all homeowners for verification
     */
    public function getHomeowners(): JsonResponse
    {
        try {
            $homeowners = Homeowner::orderBy('created_at', 'desc')->take(10)->get();
            
            return response()->json([
                'success' => true,
                'count' => $homeowners->count(),
                'data' => $homeowners,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get homeowners',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}