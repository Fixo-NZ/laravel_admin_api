<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TradieTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TradieTestAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tradie_tests',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors(),
            ], 422);
        }

        $tradie = TradieTest::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'confirm_password' => Hash::make($request->password_confirmation),
        ]);

        $token = $tradie->createToken('tradie-test-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // or whatever
            'user' => [
                'id' => $tradie->id,
                'first_name' => $tradie->first_name ?? '',
                'middle_name' => $tradie->middle_name ?? '',
                'last_name' => $tradie->last_name ?? '',
                'email' => $tradie->email ?? '',
                'phone' => $tradie->phone ?? '',
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors(),
            ], 422);
        }

        $tradie = TradieTest::where('email', $request->email)->first();

        if (!$tradie || !Hash::check($request->password, $tradie->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials.',
            ], 401);
        }

        $tradie->tokens()->delete();
        $token = $tradie->createToken('tradie-test-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // or whatever
            'user' => [
                'id' => $tradie->id,
                'first_name' => $tradie->first_name ?? '',
                'middle_name' => $tradie->middle_name ?? '',
                'last_name' => $tradie->last_name ?? '',
                'email' => $tradie->email ?? '',
                'phone' => $tradie->phone ?? '',
            ],
        ], 201);
    }
}
