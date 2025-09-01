<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Homeowner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HomeownerAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:homeowners',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
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
            $homeowner = Homeowner::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'region' => $request->region,
                'postal_code' => $request->postal_code,
                'status' => 'active',
            ]);

            $token = $homeowner->createToken('homeowner-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $homeowner->id,
                        'name' => $homeowner->name,
                        'email' => $homeowner->email,
                        'phone' => $homeowner->phone,
                        'address' => $homeowner->address,
                        'city' => $homeowner->city,
                        'region' => $homeowner->region,
                        'postal_code' => $homeowner->postal_code,
                        'status' => $homeowner->status,
                        'user_type' => 'homeowner',
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

        $homeowner = Homeowner::where('email', $request->email)->first();

        if (!$homeowner || !Hash::check($request->password, $homeowner->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are incorrect.',
                ]
            ], 401);
        }

        if ($homeowner->status !== 'active') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => 'Your account is not active. Please contact support.',
                ]
            ], 403);
        }

        // Revoke existing tokens
        $homeowner->tokens()->delete();

        $token = $homeowner->createToken('homeowner-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $homeowner->id,
                    'name' => $homeowner->name,
                    'email' => $homeowner->email,
                    'phone' => $homeowner->phone,
                    'address' => $homeowner->address,
                    'city' => $homeowner->city,
                    'region' => $homeowner->region,
                    'postal_code' => $homeowner->postal_code,
                    'status' => $homeowner->status,
                    'user_type' => 'homeowner',
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
        $homeowner = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $homeowner->id,
                    'name' => $homeowner->name,
                    'email' => $homeowner->email,
                    'phone' => $homeowner->phone,
                    'avatar' => $homeowner->avatar,
                    'bio' => $homeowner->bio,
                    'address' => $homeowner->address,
                    'city' => $homeowner->city,
                    'region' => $homeowner->region,
                    'postal_code' => $homeowner->postal_code,
                    'latitude' => $homeowner->latitude,
                    'longitude' => $homeowner->longitude,
                    'status' => $homeowner->status,
                    'user_type' => 'homeowner',
                    'created_at' => $homeowner->created_at,
                ]
            ]
        ]);
    }
}
