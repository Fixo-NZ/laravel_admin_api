<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tradie;
use App\Services\OtpService;
use App\Notifications\SendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class TradieAuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/api/tradie/request-otp",
     *     summary="Request OTP for Tradie",
     *     tags={"Tradie Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                 @OA\Property(property="details", type="object", example="{ phone: ['The phone field is required.'] }"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully"),
     *        )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="OTP Generation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="OTP_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Failed to generate OTP. Please try again."),
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/tradie/verify-otp",
     *     summary="Verify OTP for Tradie",
     *     tags={"Tradie Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="example@email.com"),
     *             @OA\Property(property="otp_code", type="string", example="123456"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Schema(
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="error", type="object",
     *                             @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                             @OA\Property(property="details", type="object", example="{ email: ['The email field is required.'] }"),
     *                         )
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="error", type="object",
     *                             @OA\Property(property="code", type="string", example="TRADIE_NOT_FOUND"),
     *                             @OA\Property(property="message", type="string", example="The given email does not exist as a tradie."),
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP successfully verified."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="password_reset_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="expires_at", type="string", example="2024-12-31 23:59:59"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP Verification Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="OTP_VERIFICATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Failed to verify OTP. Please try again."),
     *             )
     *         )
     *    )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
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

        $tradie = Tradie::where('email', $request->email)->first();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TRADIE_NOT_FOUND',
                    'message' => 'The given email does not exist as a tradie.',
                ]
            ], 422);
        }

        if ($this->otpService->verifyOtp($tradie->phone, $request->otp_code)) {

            $tradie->tokens()->where('name', 'password-reset-token')->delete();

            $token = $tradie->createToken(
                'password-reset-token',
                ['reset-password'],
                now()->addMinutes(60)
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP successfully verified.',
                'data' => [
                    'password_reset_token' => $token->plainTextToken,
                    'expires_at' => now()->addMinutes(60)->toDateTimeString(),
                ]
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

    /**
     * @OA\Post(
     *     path="/api/tradie/register",
     *     summary="Register a new user",
     *     tags={"Tradie Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="middle_name", type="string", example="M"),
     *             @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="business_name", type="string", example="John's Plumbing"),
     *             @OA\Property(property="license_number", type="string", example="LIC123456"),
     *             @OA\Property(property="years_experience", type="integer", example=5),
     *             @OA\Property(property="hourly_rate", type="number", format="float", example=50.00),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Metropolis"),
     *             @OA\Property(property="region", type="string", example="State"),
     *             @OA\Property(property="postal_code", type="string", example="12345"),
     *             @OA\Property(property="service_radius", type="integer", example=50),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *            @OA\Property(property="success", type="boolean", example=false),
     *            @OA\Property(property="error", type="object",
     *                @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                @OA\Property(property="details", type="object", example="{ email: ['The email has already been taken.'] }"),
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="middle_name", type="string", example="M"),
     *                     @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
     *                     @OA\Property(property="phone", type="string", example="09123456789"),
     *                     @OA\Property(property="business_name", type="string", example="John's Plumbing"),
     *                     @OA\Property(property="license_number", type="string", example="LIC123456"),
     *                     @OA\Property(property="years_experience", type="integer", example=5),
     *                     @OA\Property(property="hourly_rate", type="number", format="float", example=50.00),
     *                     @OA\Property(property="address", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="Metropolis"),
     *                     @OA\Property(property="region", type="string", example="State"),
     *                     @OA\Property(property="postal_code", type="string", example="12345"),
     *                     @OA\Property(property="service_radius", type="integer", example=50),
     *                     @OA\Property(property="availability_status", type="string", example="available"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="user_type", type="string", example="tradie"),
     *                 ),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Registration Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="REGISTRATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Failed to register user. Please try again."),
     *             )
     *         )
     *     )
     *  )
     */
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

            event(new Registered($tradie));

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

    /**
     * @OA\Get(
     *     path="/api/tradie/auth/verify-email/{id}/{hash}",
     *     summary="Verify tradie email",
     *     tags={"Tradie Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tradie ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Email verification hash",
     *         required=true,
     *         @OA\Schema(type="string", example="sha1-hash-of-email")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tradie Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="TRADIE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Tradie does not exist."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid or Signature or Hash Mismatch",
     *         @OA\JsonContent(
     *             @OA\Schema(
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="error", type="object",
     *                             @OA\Property(property="code", type="string", example="INVALID_SIGNATURE"),
     *                             @OA\Property(property="message", type="string", example="Invalid or expired verification link."),
     *                         )
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="error", type="object",
     *                             @OA\Property(property="code", type="string", example="INVALID_VERIFICATION"),
     *                             @OA\Property(property="message", type="string", example="Verification details do not match."),
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Verification Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VERIFICATION_FAILED"),
     *                 @OA\Property(property="message", type="string", example="Failed to verify email. Please try again."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email Verified Successfully / is Already Verified",
     *         @OA\JsonContent(
     *             @OA\Schema(
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=true),
     *                         @OA\Property(property="message", type="string", example="Email verified successfully."),
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=true),
     *                         @OA\Property(property="message", type="string", example="Email already verified."),
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     * )
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $tradie = Tradie::find($id);

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TRADIE_NOT_FOUND',
                    'message' => 'Tradie does not exist.',
                ]
            ], 404);
        }

        if (!URL::hasValidSignature($request)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_SIGNATURE',
                    'message' => 'Invalid or expired verification link.',
                ]
            ], 403);
        }

        if (!hash_equals((string) $hash, sha1($tradie->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_VERIFICATION',
                    'message' => 'Verification details do not match.',
                ]
            ], 403);
        }

        if ($tradie->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ], 200);
        }

        if (!$tradie->markEmailAsVerified()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VERIFICATION_FAILED',
                    'message' => 'Failed to verify email. Please try again.',
                ]
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tradie/auth/resend-verification",
     *     summary="Resend email verification for tradie",
     *     tags={"Tradie Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="example@email.com"),
     *        )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Schema(
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="error", type="object",
     *                             @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                             @OA\Property(property="details", type="object", example="{ email: ['The email field is required.'] }"),
     *                         )
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="error", type="object",
     *                             @OA\Property(property="code", type="string", example="TRADIE_NOT_FOUND"),
     *                             @OA\Property(property="message", type="string", example="Tradie does not exist."),
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Resend Failed",
     *        @OA\JsonContent(
     *            @OA\Property(property="success", type="boolean", example=false),
     *            @OA\Property(property="error", type="object",
     *                @OA\Property(property="code", type="string", example="RESEND_FAILED"),
     *                @OA\Property(property="message", type="string", example="Failed to send verification email. Please try again."),
     *            )
     *        )
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Verification Email Resent Successfully / Email Already Verified",
     *        @OA\JsonContent(
     *            @OA\Schema(
     *                oneOf={
     *                    @OA\Schema(
     *                        @OA\Property(property="success", type="boolean", example=true),
     *                        @OA\Property(property="message", type="string", example="Verification email resent successfully."),
     *                    ),
     *                    @OA\Schema(
     *                        @OA\Property(property="success", type="boolean", example=true),
     *                        @OA\Property(property="message", type="string", example="Email already verified."),
     *                    )
     *                }
     *            )
     *        )
     *    )
     * )
     */
    public function resendEmailVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
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

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TRADIE_NOT_FOUND',
                    'message' => 'Tradie does not exist.',
                ]
            ], 422);
        }

        if ($tradie->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ], 200);
        }

        try {
            $tradie->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESEND_FAILED',
                    'message' => 'Failed to send verification email. Please try again.',
                ]
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification email resent successfully.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tradie/login",
     *     summary="Login tradie",
     *     tags={"Tradie Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                 @OA\Property(property="details", type="object", example="{ email: ['The email field is required.'] }"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid Credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_CREDENTIALS"),
     *                 @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account Inactive",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="ACCOUNT_INACTIVE"),
     *                 @OA\Property(property="message", type="string", example="Your account is not active. Please contact support."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="middle_name", type="string", example="M"),
     *                     @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
     *                     @OA\Property(property="phone", type="string", example="09123456789"),
     *                     @OA\Property(property="business_name", type="string", example="John's Plumbing"),
     *                     @OA\Property(property="license_number", type="string", example="LIC123456"),
     *                     @OA\Property(property="years_experience", type="integer", example=5),
     *                     @OA\Property(property="hourly_rate", type="number", format="float", example=50.00),
     *                     @OA\Property(property="address", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="Metropolis"),
     *                     @OA\Property(property="region", type="string", example="State"),
     *                     @OA\Property(property="postal_code", type="string", example="12345"),
     *                     @OA\Property(property="service_radius", type="integer", example=50),
     *                     @OA\Property(property="availability_status", type="string", example="available"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="user_type", type="string", example="tradie"),
     *                 ),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             )
     *         )
     *     )
     * )
     */
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
                    'name' => $tradie->name,
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
                    'user_type' => 'tradie',
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tradie/reset-password-request",
     *     summary="Request password reset OTP for tradie",
     *     description="Request password reset OTP for tradie",
     *     tags={"Tradie Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="The given email does not exist as a user."),
     *                 @OA\Property(property="details", type="object", example="{ email: ['The email field is required.'] }"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OTP generated and sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="OTP Generation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="OTP_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Failed to generate OTP. Please try again."),
     *             )
     *         )
     *     )
     * )
     */
    public function resetPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given email is invalid.',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $tradie = Tradie::where('email', $request->email)->first();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'The given email does not exist as a user.',
                ]
            ], 422);
        }

        $otp = $this->otpService->generateOtp($tradie->phone);

        $tradie->notify(new SendOtp($otp));

        if ($otp) {
            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OTP_ERROR',
                    'message' => 'Failed to generate OTP. Please try again.',
                ]
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tradie/reset-password",
     *     summary="Reset tradie password",
     *     description="Reset tradie password",
     *     tags={"Tradie Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="new_password", type="string", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                 @OA\Property(property="details", type="object", example="{ email: ['The email field is required.'] }"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Reset Password Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="RESET_PASSWORD_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Failed to reset password. Please try again."),
     *             )
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            $tradie = $request->user();

            $tradie->password = Hash::make($request->new_password);
            $tradie->save();

            // Revoke token used for resetting password
            $tradie->currentAccessToken()->delete();
            // Revoke all other tokens forcing re-login on all devices
            $tradie->tokens()->where('name', '!=', 'password-reset-token')->delete();

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

    /**
     * @OA\Post(
     *     path="/api/tradie/logout",
     *     summary="Logout tradie",
     *     tags={"Tradie Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully"),
     *         )
     *     )
     * )    
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tradie/me",
     *     summary="Get authenticated tradie details",
     *     tags={"Tradie Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated tradie details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="middle_name", type="string", example="M"),
     *                     @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
     *                     @OA\Property(property="phone", type="string", example="09123456789"),
     *                     @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg"),
     *                     @OA\Property(property="bio", type="string", example="Experienced plumber specializing in residential repairs."),
     *                     @OA\Property(property="business_name", type="string", example="John's Plumbing"),
     *                     @OA\Property(property="license_number", type="string", example="LIC123456"),
     *                     @OA\Property(property="insurance_details", type="string", example="Insured with ABC Insurance, Policy #123456"),
     *                     @OA\Property(property="years_experience", type="integer", example=5),
     *                     @OA\Property(property="hourly_rate", type="number", format="float", example=50.00),
     *                     @OA\Property(property="address", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="Metropolis"),
     *                     @OA\Property(property="region", type="string", example="State"),
     *                     @OA\Property(property="postal_code", type="string", example="12345"),
     *                     @OA\Property(property="latitude", type="number", format="float", example=40.7128),
     *                     @OA\Property(property="longitude", type="number", format="float", example=-74.0060),
     *                     @OA\Property(property="service_radius", type="integer", example=50),
     *                     @OA\Property(property="availability_status", type="string", example="available"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="user_type", type="string", example="tradie"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        $tradie = $request->user();

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
                    'user_type' => 'tradie',
                ]
            ]
        ]);
    }
}