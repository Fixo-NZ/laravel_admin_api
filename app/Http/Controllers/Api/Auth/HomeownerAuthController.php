<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Homeowner;
use App\Services\OtpService;
use App\Notifications\SendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class HomeownerAuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/api/homeowner/request-otp",
     *     summary="Request OTP for Homeowner",
     *     tags={"Homeowner Authentication"},
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
     *     path="/api/homeowner/verify-otp",
     *     summary="Verify OTP for Homeowner",
     *     tags={"Homeowner Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="example@email.com"),
     *             @OA\Property(property="otp_code", type="string", example="123456"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error or User Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR", description="Can be VALIDATION_ERROR or USER_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="The given data was invalid.", description="Error message varies based on error type"),
     *                 @OA\Property(property="details", type="object", example={"email": "The email field is required."}, description="Present only for validation errors")
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

        $homeowner = Homeowner::where('email', $request->email)->first();

        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'The given email does not exist as a user.',
                ]
            ], 422);
        }

        if ($this->otpService->verifyOtp($homeowner->phone, $request->otp_code)) {

            $homeowner->tokens()->where('name', 'password-reset-token')->delete();

            $token = $homeowner->createToken(
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
     *     path="/api/homeowner/register",
     *     summary="Register a new homeowner user",
     *     description="Register a new homeowner account. A verification email will be sent to the provided email address. The user must verify their email before they can log in.",
     *     tags={"Homeowner Authentication"},
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
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Metropolis"),
     *             @OA\Property(property="region", type="string", example="State"),
     *             @OA\Property(property="postal_code", type="string", example="12345"),
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
     *                     @OA\Property(property="address", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="Metropolis"),
     *                     @OA\Property(property="region", type="string", example="State"),
     *                     @OA\Property(property="postal_code", type="string", example="12345"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="user_type", type="string", example="homeowner"),
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
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:homeowners',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        // Return errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            // Create the homeowner record
            $homeowner = Homeowner::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'region' => $request->region,
                'postal_code' => $request->postal_code,
                'status' => 'active',
            ]);

            event(new Registered($homeowner));

            $token = $homeowner->createToken('homeowner-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $homeowner->id,
                        'first_name' => $homeowner->first_name,
                        'last_name' => $homeowner->last_name,
                        'middle_name' => $homeowner->middle_name,
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
                ],
            ], 201);

        } catch (\Exception $e) {
            // Handle any unexpected errors during registration
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'REGISTRATION_ERROR',
                    'message' => 'Failed to register user. Please try again.',
                ],
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * @OA\Get(
     *     path="/api/homeowner/auth/verify-email/{id}/{hash}",
     *     summary="Verify homeowner email",
     *     tags={"Homeowner Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Homeowner ID",
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
     *         description="Homeowner Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="USER_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="This user does not exist."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid Signature or Hash Mismatch",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_SIGNATURE", description="Can be INVALID_SIGNATURE or INVALID_VERIFICATION"),
     *                 @OA\Property(property="message", type="string", example="Invalid or expired verification link.", description="Error message varies based on error type")
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
     *         description="Email Verified Successfully or Already Verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email verified successfully.", description="Can be 'Email verified successfully.' or 'Email already verified.'")
     *         )
     *     ),
     * )
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $homeowner = Homeowner::find($id);

        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'This user does not exist.',
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

        if (!hash_equals((string) $hash, sha1($homeowner->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_VERIFICATION',
                    'message' => 'Verification details do not match.',
                ]
            ], 403);
        }

        if ($homeowner->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ], 200);
        }

        if (!$homeowner->markEmailAsVerified()) {
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
     *     path="/api/homeowner/auth/resend-verification",
     *     summary="Resend email verification for homeowner",
     *     tags={"Homeowner Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="example@email.com"),
     *        )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error or User Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR", description="Can be VALIDATION_ERROR or USER_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="The given data was invalid.", description="Error message varies based on error type"),
     *                 @OA\Property(property="details", type="object", example={"email": "The email field is required."}, description="Present only for validation errors")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Resend Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="RESEND_FAILED"),
     *                 @OA\Property(property="message", type="string", example="Failed to send verification email. Please try again."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification Email Resent Successfully or Email Already Verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Verification email resent successfully.", description="Can be 'Verification email resent successfully.' or 'Email already verified.'")
     *         )
     *     )
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

        $homeowner = Homeowner::where('email', $request->email)->first();

        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'This user does not exist.',
                ]
            ], 422);
        }

        if ($homeowner->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ], 200);
        }

        try {
            $homeowner->sendEmailVerificationNotification();
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
     *     path="/api/homeowner/login",
     *     summary="Login homeowner",
     *     description="Authenticate a homeowner user. The user must have verified their email address before they can log in.",
     *     tags={"Homeowner Authentication"},
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
     *         description="Account Inactive or Email Not Verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="ACCOUNT_INACTIVE", description="Can be ACCOUNT_INACTIVE or EMAIL_NOT_VERIFIED"),
     *                 @OA\Property(property="message", type="string", example="Your account is not active. Please contact support.", description="Error message varies based on error type")
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
     *                     @OA\Property(property="address", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="Metropolis"),
     *                     @OA\Property(property="region", type="string", example="State"),
     *                     @OA\Property(property="postal_code", type="string", example="12345"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="user_type", type="string", example="homeowner"),
     *                 ),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        // Validate login input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Return errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        // Find the homeowner by email
        $homeowner = Homeowner::where('email', $request->email)->first();

        // Verify password
        if (!$homeowner || !Hash::check($request->password, $homeowner->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are incorrect.',
                ],
            ], 401); // 401 Unauthorized
        }

        // Check account status
        if ($homeowner->status !== 'active') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => 'Your account is not active. Please contact support.',
                ],
            ], 403); // 403 Forbidden
        }

        // Check if email is verified
        if (!$homeowner->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EMAIL_NOT_VERIFIED',
                    'message' => 'Please verify your email before logging in.',
                ],
            ], 403); // 403 Forbidden
        }

        // Revoke any previous tokens to prevent session hijacking
        $homeowner->tokens()->delete();

        // Issue a new API token
        $token = $homeowner->createToken('homeowner-token')->plainTextToken;

        // Return success response with token
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $homeowner->id,
                    'first_name' => $homeowner->first_name,
                    'last_name' => $homeowner->last_name,
                    'middle_name' => $homeowner->middle_name,
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
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/homeowner/reset-password-request",
     *     summary="Request password reset OTP for homeowner",
     *     description="Request password reset OTP for homeowner",
     *     tags={"Homeowner Authentication"},
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
        // Validate incoming request email data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // Return errors if validation fails
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

        // Find homeowner by email
        $homeowner = Homeowner::where('email', $request->email)->first();

        if (!$homeowner) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'The given email does not exist as a user.',
                ]
            ], 422);
        }

        // Generate OTP
        $otp = $this->otpService->generateOtp($homeowner->phone);

        // Send OTP notification (e.g., via SMS or email)
        $homeowner->notify(new SendOtp($otp));

        // Check if otp is generated
        if ($otp) {
            // OTP generated successfully
            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ], 201);
        } else {
            // OTP generation failed
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
     *     path="/api/homeowner/reset-password",
     *     summary="Reset homeowner password",
     *     description="Reset homeowner password",
     *     tags={"Homeowner Authentication"},
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
     *                 @OA\Property(property="details", type="object", example="{ new_password: ['The new password must be at least 8 characters.'] }"),
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
            // Find the homeowner by the ID from the URL
            $homeowner = $request->user();

            $homeowner->password = Hash::make($request->new_password);
            $homeowner->save();

            // Revoke token used for resetting password
            $homeowner->currentAccessToken()->delete();
            // Revoke all other tokens forcing re-login on all devices
            $homeowner->tokens()->where('name', '!=', 'password-reset-token')->delete();

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
     *     path="/api/homeowner/logout",
     *     summary="Logout homeowner",
     *     tags={"Homeowner Authentication"},
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
        // Delete the token used for this request only
        $request->user()->currentAccessToken()->delete();

        // Return success message
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/homeowner/me",
     *     summary="Get authenticated homeowner details",
     *     tags={"Homeowner Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated homeowner details",
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
     *                     @OA\Property(property="address", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="Metropolis"),
     *                     @OA\Property(property="region", type="string", example="State"),
     *                     @OA\Property(property="postal_code", type="string", example="12345"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="user_type", type="string", example="homeowner"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        $homeowner = $request->user(); // Fetched via Sanctum authentication

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $homeowner->id,
                    'first_name' => $homeowner->first_name,
                    'last_name' => $homeowner->last_name,
                    'middle_name' => $homeowner->middle_name,
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
                ],
            ],
        ], 200);
    }

    /**
     * Show homeowner profile page (for admin panel)
     *
     * Only authorized admins should access this route.
     */
    public function show(Homeowner $homeowner)
    {
        // Pass the homeowner data to the Blade view (renders without Filament page wrapper)
        return view('filament.admin.pages.homeowner-profile-page', compact('homeowner'));
    }
}