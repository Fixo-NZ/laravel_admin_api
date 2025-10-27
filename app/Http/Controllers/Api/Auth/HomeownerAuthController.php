<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Homeowner;
use App\Services\OtpService;
use App\Notifications\SendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeownerAuthController extends Controller
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

            $homeowner = Homeowner::where('phone', $request->phone)->first();

            if (! $homeowner) {
                return response()->json([
                    'success' => true,
                    'status' => 'new_user',
                    'message' => 'OTP verification successful. Please proceed to registration.',
                ], 200);
            }

            $homeowner->tokens()->delete();
            $token = $homeowner->createToken('homeowner-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'status' => 'existing_user',
                'message' => 'OTP verification successful.',
                'data' => [
                    'user' => [
                        'first_name' => $homeowner->first_name,
                        'last_name' => $homeowner->last_name,
                        'email' => $homeowner->email,
                        'phone' => $homeowner->phone,
                        'status' => $homeowner->status,
                        'user_type' => 'homeowner',
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

    /**
     * Register a new homeowner
     *
     * This method handles the creation of a new homeowner user.
     * It validates the input, hashes the password, creates the user, 
     * and returns an API token for authentication.
     */
    public function register(Request $request)
    {
        // Step 1: Validate incoming request data
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',   
            'email'       => 'required|string|email|max:255|unique:homeowners,email',
            'phone'       => 'nullable|string|max:20',
            'password'    => 'required|string|min:8|confirmed',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'region'      => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        // Step 2: Return errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $validator->errors(), // Detailed field errors
                ],
            ], 422); // 422 Unprocessable Entity
        }

        // Step 3: Create the homeowner record
        $homeowner = Homeowner::create([
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'middle_name' => $request->middle_name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
            'address'     => $request->address,
            'city'        => $request->city,
            'region'      => $request->region,
            'postal_code' => $request->postal_code,
            'status'      => 'active', 
        ]);

        // Step 4: Generate API token using Laravel Sanctum
        $token = $homeowner->createToken('homeowner-token')->plainTextToken;

        // Step 5: Return success response with user data and token
        return response()->json([
            'success' => true,
            'data'    => [
                'user'  => $homeowner,
                'token' => $token,
            ],
        ], 201); // 201 Created
    }

    /**
     * Login homeowner
     *
     * Validates credentials, checks account status, revokes old tokens,
     * and issues a new Sanctum token for API authentication.
     */
    public function login(Request $request)
    {
        // Step 1: Validate login input
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Step 2: Return errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        // Step 3: Find the homeowner by email
        $homeowner = Homeowner::where('email', $request->email)->first();

        // Step 4: Verify password
        if (!$homeowner || !Hash::check($request->password, $homeowner->password)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are incorrect.',
                ],
            ], 401); // 401 Unauthorized
        }

        // Step 5: Check account status
        if ($homeowner->status !== 'active') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ACCOUNT_INACTIVE',
                    'message' => 'Your account is not active. Please contact support.',
                ],
            ], 403); // 403 Forbidden
        }

        // Step 6: Revoke any previous tokens to prevent session hijacking
        $homeowner->tokens()->delete();

        // Step 7: Issue a new API token
        $token = $homeowner->createToken('homeowner-token')->plainTextToken;

        // Step 8: Return success response with token
        return response()->json([
            'success' => true,
            'data'    => [
                'user'  => $homeowner,
                'token' => $token,
            ],
        ]);
    }

    public function resetPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:homeowners,email',
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

        $homeowner = Homeowner::where('email', $request->email)->first();
        
        $otp = $this->otpService->generateOtp($homeowner->phone);

        $homeowner->notify(new SendOtp($otp));

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
            'email' => 'required|email|exists:homeowners,email',
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
            $homeowner = Homeowner::where('email', $request->email)->first();
            $homeowner->password = Hash::make($request->new_password);
            $homeowner->save();

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
     * Logout homeowner
     *
     * Deletes the current token to revoke API access.
     */
    public function logout(Request $request)
    {
        // Delete the token used for this request only
        $request->user()->currentAccessToken()->delete();

        // Return success message
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated homeowner
     *
     * Returns the currently logged-in homeowner info.
     */
    public function me(Request $request)
    {
        $homeowner = $request->user(); // Fetched via Sanctum authentication

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => $homeowner,
            ],
        ]);
    }

    /**
     * Show homeowner profile page (for admin panel)
     *
     * Only authorized admins should access this route.
     */
    public function show(Homeowner $homeowner)
    {
        // Pass the homeowner data to the Blade view
        return view('filament.admin.pages.homeowner-profile-page', compact('homeowner'));
    }
}
