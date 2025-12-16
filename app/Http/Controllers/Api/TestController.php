<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Homeowner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    /**
     * Simple test endpoint without Firebase
     */
    public function test(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Laravel API is working!',
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test database connection
     */
    public function testDatabase(): JsonResponse
    {
        try {
            $count = Homeowner::count();
            return response()->json([
                'success' => true,
                'message' => 'Database connection working!',
                'homeowner_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test homeowner creation without Firebase
     */
    public function testCreateHomeowner(Request $request): JsonResponse
    {
        try {
            $homeowner = Homeowner::create([
                'firebase_uid' => 'test_simple_' . time(),
                'first_name' => 'Simple',
                'last_name' => 'Test',
                'email' => 'simple.test.' . time() . '@example.com',
                'password' => bcrypt('password123'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Homeowner created successfully!',
                'data' => $homeowner,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Creation error: ' . $e->getMessage()
            ], 500);
        }
    }
}