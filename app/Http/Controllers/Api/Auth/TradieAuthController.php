<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tradie;
use App\Services\OtpService;
use App\Notifications\SendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TradieAuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function requestOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits_between:8,15',
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

        $otp = $this->otpService->generateOtp($request->phone);

        if ($otp) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'otp_code' => $otp->otp_code
            ], 201);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'OTP_ERROR',
                'message' => 'Failed to generate OTP. Please try again.',
            ]
        ], 500);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits_between:8,15',
            'otp_code' => 'required|digits:6',
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

        if ($this->otpService->verifyOtp($request->phone, $request->otp_code)) {

            $tradie = Tradie::where('phone', $request->phone)->first();

            if (! $tradie) {
                return response()->json([
                    'success' => true,
                    'status' => 'new_user',
                    'message' => 'OTP verification successful. Please proceed to registration.',
                ], 200);
            }

            $tradie->tokens()->delete();
            $token = $tradie->createToken('tradie-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'status' => 'existing_user',
                'message' => 'OTP verification successful.',
                'data' => [
                    'user' => [
                        'first_name' => $tradie->first_name,
                        'last_name' => $tradie->last_name,
                        'email' => $tradie->email,
                        'phone' => $tradie->phone,
                        'status' => $tradie->status,
                        'user_type' => 'tradie',
                    ],
                ],
                'authorisation' => [
                    'access_token' => $token,
                    'type' => 'Bearer',
                ],
            ], 200);

        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'OTP_VERIFICATION_ERROR',
                'message' => 'Failed to verify OTP. Please try again.',
            ]
        ], 400);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
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
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
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

    public function resetPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:tradies,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given email does not exist as a user.',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $tradie = Tradie::where('email', $request->email)->first();
        
        $otp = $this->otpService->generateOtp($tradie->phone);

        $tradie->notify(new SendOtp($otp));

        if ($otp) {
            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ], 201);
        }
        else {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OTP_ERROR',
                    'message' => 'Failed to generate OTP. Please try again.',
                ]
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:tradies,email',
            'new_password' => 'required|string|min:8|confirmed',
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
            $tradie = Tradie::where('email', $request->email)->first();
            $tradie->password = Hash::make($request->new_password);
            $tradie->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESET_PASSWORD_ERROR',
                    'message' => 'Failed to reset password. Please try again.',
                ]
            ], 500);
        }
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
