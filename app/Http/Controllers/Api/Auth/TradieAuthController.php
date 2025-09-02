<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tradie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TradieAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tradies',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'business_name' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'hourly_rate' => 'nullable|numeric|min:0|max:999.99',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'service_radius' => 'nullable|integer|min:1|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        try {
            $tradie = Tradie::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'business_name' => $request->business_name,
                'license_number' => $request->license_number,
                'years_experience' => $request->years_experience,
                'hourly_rate' => $request->hourly_rate,
                'address' => $request->address,
                'city' => $request->city,
                'region' => $request->region,
                'postal_code' => $request->postal_code,
                'service_radius' => $request->service_radius ?? 50,
                'availability_status' => 'available',
                'status' => 'active',
            ]);

            $token = $tradie->createToken('tradie-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $tradie->id,
                        'first_name' => $tradie->first_name,
                        'last_name' => $tradie->last_name,
                        'middle_name' => $tradie->middle_name,
                        'email' => $tradie->email,
                        'phone' => $tradie->phone,
                        'business_name' => $tradie->business_name,
                        'license_number' => $tradie->license_number,
                        'years_experience' => $tradie->years_experience,
                        'hourly_rate' => $tradie->hourly_rate,
                        'address' => $tradie->address,
                        'city' => $tradie->city,
                        'region' => $tradie->region,
                        'postal_code' => $tradie->postal_code,
                        'service_radius' => $tradie->service_radius,
                        'availability_status' => $tradie->availability_status,
                        'status' => $tradie->status,
                        'is_verified' => $tradie->is_verified,
                        'user_type' => 'tradie',
                    ],
                    'token' => $token,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'REGISTRATION_ERROR',
                    'message' => 'Registration failed. Please try again.',
                ]
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $tradie = Tradie::where('email', $request->email)->first();

        if (!$tradie || !Hash::check($request->password, $tradie->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are incorrect.',
                ]
            ], 401);
        }

        if ($tradie->status !== 'active') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => 'Your account is not active. Please contact support.',
                ]
            ], 403);
        }

        // Revoke existing tokens
        $tradie->tokens()->delete();

        $token = $tradie->createToken('tradie-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $tradie->id,
                    'first_name' => $tradie->first_name,
                    'last_name' => $tradie->last_name,
                    'middle_name' => $tradie->middle_name,
                    'email' => $tradie->email,
                    'phone' => $tradie->phone,
                    'business_name' => $tradie->business_name,
                    'license_number' => $tradie->license_number,
                    'years_experience' => $tradie->years_experience,
                    'hourly_rate' => $tradie->hourly_rate,
                    'address' => $tradie->address,
                    'city' => $tradie->city,
                    'region' => $tradie->region,
                    'postal_code' => $tradie->postal_code,
                    'service_radius' => $tradie->service_radius,
                    'availability_status' => $tradie->availability_status,
                    'status' => $tradie->status,
                    'is_verified' => $tradie->is_verified,
                    //'average_rating' => $tradie->average_rating,
                    //'total_reviews' => $tradie->total_reviews,
                    'user_type' => 'tradie',
                ],
                'token' => $token,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function me(Request $request)
    {
        $tradie = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $tradie->id,
                    'name' => $tradie->name,
                    'email' => $tradie->email,
                    'phone' => $tradie->phone,
                    'avatar' => $tradie->avatar,
                    'bio' => $tradie->bio,
                    'business_name' => $tradie->business_name,
                    'license_number' => $tradie->license_number,
                    'insurance_details' => $tradie->insurance_details,
                    'years_experience' => $tradie->years_experience,
                    'hourly_rate' => $tradie->hourly_rate,
                    'address' => $tradie->address,
                    'city' => $tradie->city,
                    'region' => $tradie->region,
                    'postal_code' => $tradie->postal_code,
                    'latitude' => $tradie->latitude,
                    'longitude' => $tradie->longitude,
                    'service_radius' => $tradie->service_radius,
                    'availability_status' => $tradie->availability_status,
                    'status' => $tradie->status,
                    'is_verified' => $tradie->is_verified,
                    'verified_at' => $tradie->verified_at,
                    //'average_rating' => $tradie->average_rating,
                    //'total_reviews' => $tradie->total_reviews,
                    'user_type' => 'tradie',
                    'created_at' => $tradie->created_at,
                ]
            ]
        ]);
    }
}
