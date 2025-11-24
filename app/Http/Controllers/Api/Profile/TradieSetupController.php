<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tradie;
use Illuminate\Support\Facades\Storage;

class TradieSetupController extends Controller
{
    public function updateBasicInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tradies,email,' . auth()->id(),
            'phone' => 'required|string|max:20',
            'business_name' => 'required|string|max:255',
            'professional_bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid basic information',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        try {
            $tradie = auth()->user();
            $data = $validator->validated();

            $tradie->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'business_name' => $data['business_name'],
                'bio' => $data['professional_bio'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Basic information updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_ERROR',
                    'message' => 'Failed to update basic information'
                ]
            ], 500);
        }
    }

    public function updateSkillsAndService(Request $request)
    {

        if ($request->has('skills') && is_string($request->skills)) {
            $decodedSkills = json_decode($request->skills, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $request->merge(['skills' => $decodedSkills]);
            }   
        }

        if ($request->has('service_location') && is_string($request->service_location)) {
            $decodedLocation = json_decode($request->service_location, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['service_location' => $decodedLocation]);
            }
        }
        $validator = Validator::make($request->all(), [
            'skills' => 'nullable|array',
            'skills.*' => 'integer',
            'service_radius' => 'nullable|integer|min:1|max:200',
            'service_location' => 'nullable|array',
            'service_location.address' => 'nullable|string|max:500',
            'service_location.city' => 'nullable|string|max:100',
            'service_location.region' => 'nullable|string|max:100',
            'service_location.postal_code' => 'nullable|string|max:10',
            'service_location.latitude' => 'nullable|numeric',
            'service_location.longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid skills or service area data',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        try {
            $tradie = auth()->user();

            if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Tradie profile not found for this user.'
                ]
            ], 404);
        }
            $data = $validator->validated();

            $updateData = [];

            if (isset($data['service_location'])) {
                $updateData['address'] = $data['service_location']['address'] ?? null;
                $updateData['city'] = $data['service_location']['city'] ?? null;
                $updateData['region'] = $data['service_location']['region'] ?? null;
                $updateData['postal_code'] = $data['service_location']['postal_code'] ?? null;
                $updateData['latitude'] = $data['service_location']['latitude'] ?? null;
                $updateData['longitude'] = $data['service_location']['longitude'] ?? null;
            }

            if (isset($data['service_radius'])) {
                $updateData['service_radius'] = $data['service_radius'];
            }

            if (isset($data['skills'])) {
                $updateData['skills'] = json_encode($data['skills']); 
            }

            \Log::info('Skills update data:', $updateData);

            $tradie->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Skills and service area updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_ERROR',
                    'message' => 'Failed to update skills and service area',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }


    public function updateAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'working_hours' => 'nullable|array',
            'working_hours.*.day' => 'nullable|integer|min:0|max:6',
            'working_hours.*.start' => 'nullable|date_format:H:i',
            'working_hours.*.end' => 'nullable|date_format:H:i',
            'emergency_available' => 'nullable|boolean',
            'availability_calendar' => 'nullable|array',
            'availability_calendar.*' => 'nullable|date_format:Y-m-d'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid availability data',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        try {
            $tradie = auth()->user();
            $data = $validator->validated();

            $tradie->update([
                'working_hours' => $data['working_hours'] ?? null,
                'emergency_available' => $data['emergency_available'] ?? null,
                // 'availability_calendar' => $data['availability_calendar'] ?? null,
                'availability_status' => 'available'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully'
            ]);
                    } catch (\Exception $e) {
                \Log::error('❌ Update availability failed', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UPDATE_ERROR',
                        'message' => $e->getMessage(), // show actual message instead of generic
                    ]
                ], 500);
            }
    }

   public function updatePortfolio(Request $request)
{
    $validator = Validator::make($request->all(), [
        'portfolio_images' => 'sometimes|nullable|array',
        'portfolio_images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:10240',
        'rate_type' => 'nullable|in:hourly,fixed_price,both',
        'standard_rate' => 'nullable|numeric|min:0',
        // 'minimum_hours' => 'nullable|integer|min:1',
        'standard_rate_description' => 'nullable|string|max:1000',
        'after_hours_enabled' => 'nullable|boolean',
        'after_hours_rate' => 'nullable|numeric|min:0',
        'call_out_fee_enabled' => 'nullable|boolean',
        'call_out_fee' => 'nullable|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid portfolio data',
                'details' => $validator->errors()
            ]
        ], 422);
    }

    try {
        $tradie = auth()->user();
        $data = $validator->validated();

        $portfolioImages = [];
        if ($request->hasFile('portfolio_images')) {
            foreach ($request->file('portfolio_images') as $image) {
                $path = $image->store('portfolio', 'public');
                $portfolioImages[] = $path;
            }
        }

        // $updateData = [
        //     'rate_type' => $data['rate_type'],
        //     'standard_rate' => $data['standard_rate'],
        //     'minimum_hours' => $data['minimum_hours'],
        //     'standard_rate_description' => $data['standard_rate_description'] ?? null,
        //     'after_hours_enabled' => $data['after_hours_enabled'] ?? false,
        //     'after_hours_rate' => $data['after_hours_rate'] ?? null,
        //     'call_out_fee_enabled' => $data['call_out_fee_enabled'] ?? false,
        //     'call_out_fee' => $data['call_out_fee'] ?? null
        // ];
        $updateData = [
            'hourly_rate' => $data['standard_rate'] ?? null,
            // 'minimum_hours' => $data['minimum_hours'] ?? null,
            'description' => $data['standard_rate_description'] ?? null,
            'after_hours' => $data['after_hours_enabled'] ?? false,
            'call_out_fee' => $data['call_out_fee_enabled'] ?? false,
        ];


        if (!empty($portfolioImages)) {
            $updateData['portfolio_images'] = $portfolioImages;
        }

        $tradie->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Portfolio updated successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UPDATE_ERROR',
                'message' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(), 
            ]
        ], 500);
    }
}

public function updateAvatar(Request $request)
{
    \Log::info('📸 Avatar upload received', [
        'has_file' => $request->hasFile('avatar'),
        'file' => $request->file('avatar'),
        'all_inputs' => $request->all(),
        'auth_user' => auth()->user()?->id,
    ]);

    // ✅ Validate file (5MB limit)
    $validator = Validator::make($request->all(), [
        'avatar' => 'required|image|mimes:jpg,jpeg,png|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid avatar file',
                'details' => $validator->errors(),
            ]
        ], 422);
    }

    try {
        $tradie = auth()->user();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Tradie not found',
                ]
            ], 404);
        }

        // ✅ Delete old avatar if it exists
        if ($tradie->avatar && Storage::disk('public')->exists($tradie->avatar)) {
            Storage::disk('public')->delete($tradie->avatar);
        }

        // ✅ Store new avatar under "storage/app/public/avatars"
        $path = $request->file('avatar')->store('avatars', 'public');

        // ✅ Save new avatar path
        $tradie->update(['avatar' => $path]);

        // ✅ Return public URL using Storage::url()
        $avatarUrl = $tradie->avatar ? asset(Storage::url($tradie->avatar)) : null;

        return response()->json([
            'success' => true,
            'data' => [
                ...$tradie->toArray(),
                'avatar_url' => $avatarUrl,
            ],
        ]);

    } catch (\Exception $e) {
        \Log::error('❌ Avatar upload failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UPDATE_ERROR',
                'message' => $e->getMessage(),
            ]
        ], 500);
    }
}




    public function completeSetup(Request $request)
    {
        try {
            $tradie = auth()->user();

            if (!$this->isProfileComplete($tradie)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INCOMPLETE_PROFILE',
                        'message' => 'Please complete all required sections before finalizing'
                    ]
                ], 422);
            }

            $tradie->update([
                'profile_completed' => true,
                'profile_completed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile setup completed successfully',
                'data' => [
                    'user' => $tradie
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPLETION_ERROR',
                    'message' => 'Failed to complete profile setup'
                ]
            ], 500);
        }
    }


public function getProfile(Request $request)
{
    try {
        $tradie = auth()->user();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'No tradie profile found.'
                ]
            ], 404);
        }

        $data = $tradie->toArray();
        $data['avatar_url'] = $tradie->avatar
            ? asset('storage/' . $tradie->avatar)
            : null;

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FETCH_ERROR',
                'message' => 'Failed to retrieve profile.',
                'details' => $e->getMessage()
            ]
        ], 500);
    }
}


public function getSkills(Request $request)
{
    try {
         $tradie = auth()->user();

        if (!$tradie) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'No tradie record found.'
                ]
            ], 404);
        }

        $skills = json_decode($tradie->skills, true) ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'skills' => $skills
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FETCH_ERROR',
                'message' => 'Failed to retrieve skills.',
                'details' => $e->getMessage()
            ]
        ], 500);
    }
}



    private function isProfileComplete(Tradie $tradie)
    {
        return $tradie->first_name 
            && $tradie->last_name
            && $tradie->email
            && $tradie->phone  
            && $tradie->business_name;
    }
}
