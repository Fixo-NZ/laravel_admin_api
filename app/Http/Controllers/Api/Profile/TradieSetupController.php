<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tradie;
use Illuminate\Support\Facades\Storage;

class TradieSetupController extends Controller
{
    //Update basic information of the Tradie profile
    public function updateBasicInfo(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make(
            $request->all(), 
            [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tradies,email,' . auth()->id(),
            'phone' => [
                'required',
                'string',
                'regex:/^\+64\s?\d{1,2}\s?\d{3,4}\s?\d{3}$/'
            ],
            'business_name' => 'required|string|max:255',
            'professional_bio' => 'nullable|string|max:1000',
        ]);

        // Return validation errors if any
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
            // Get authenticated user
            $tradie = auth()->user();
            $data = $validator->validated();

            // Update user's basic details
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

    //Upload license or ID files
    public function uploadLicenseFiles(Request $request)
    {
        try {
            $request->validate([
                'file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
                'file_type' => 'nullable|in:license,id',
            ]);

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => true,
                    'message' => 'No file uploaded (optional step)',
                ], 200);
            }

            $tradie = $request->user();

            $folder = $request->file_type === 'license' ? 'licenses' : 'ids';
            $path = $request->file('file')->store($folder, 'public');

            if ($request->file_type === 'license') {
                $current = $tradie->license_files ?? [];
                $current[] = $path;
                $tradie->license_files = $current;
            } else {
                $current = $tradie->id_files ?? [];
                $current[] = $path;
                $tradie->id_files = $current;
            }

            $tradie->save();

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->file_type) . ' file uploaded successfully!',
                'file_url' => asset('storage/' . $path),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    // Update tradie skills and service location
    public function updateSkillsAndService(Request $request)
    {
        // Convert JSON string inputs to actual arrays
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

        // Validate input
        $validator = Validator::make($request->all(), [
            'skills' => 'nullable|array',
            'skills.*' => 'integer',
            'service_radius' => 'nullable|integer|min:1|max:200',

            'service_location' => 'nullable|array',
            'service_location.address' => 'nullable|string|max:500',
            'service_location.city' => 'nullable|string|max:100',
            'service_location.region' => 'nullable|string|max:100',
            'service_location.postal_code' => 'nullable|string|max:20',

            'service_location.latitude' => 'nullable|numeric|between:-90,90',
            'service_location.longitude' => 'nullable|numeric|between:-180,180',
            //'service_location.latitude' => 'nullable|numeric',
            //'service_location.longitude' => 'nullable|numeric',
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

            // Map service location values to DB columns
            //NOTE: This will be updated. Implement backend support for user-pinned map locations coming from the mobile app.
            //----- Since the mobile requirement states that the location should not rely on the user’s hardware GPS, 
            //----- the backend must accept and store manually pinned coordinates provided by the mobile app.
           /*  if (isset($data['service_location'])) {
                $updateData['address'] = $data['service_location']['address'] ?? null;
                $updateData['city'] = $data['service_location']['city'] ?? null;
                $updateData['region'] = $data['service_location']['region'] ?? null;
                $updateData['postal_code'] = $data['service_location']['postal_code'] ?? null;
                $updateData['latitude'] = $data['service_location']['latitude'] ?? null;
                $updateData['longitude'] = $data['service_location']['longitude'] ?? null;
            } */

            if (!empty($data['service_location'])) {
                $location = $data['service_location'];

                $updateData['address'] = $location['address'] ?? null;
                $updateData['city'] = $location['city'] ?? null;
                $updateData['region'] = $location['region'] ?? null;
                $updateData['postal_code'] = $location['postal_code'] ?? null;

                // Store exactly what the user pinned or geocoded
                $updateData['latitude'] = isset($location['latitude'])
                    ? (float) $location['latitude']
                    : null;

                $updateData['longitude'] = isset($location['longitude'])
                    ? (float) $location['longitude']
                    : null;
            }

            // Save service radius
            if (isset($data['service_radius'])) {
                $updateData['service_radius'] = $data['service_radius'];
            }

            // Save skills as JSON
            if (isset($data['skills'])) {
                $updateData['skills'] = json_encode($data['skills']); 
            }

            // Log update for debugging
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

    // Update tradie's availability information
    public function updateAvailability(Request $request)
    {
        // Validate working hours, emergency availability, and optional calendar
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

            // Update availability-related fields
            $tradie->update([
                'working_hours' => $data['working_hours'] ?? null,
                'emergency_available' => $data['emergency_available'] ?? null,
                // 'availability_calendar' => $data['availability_calendar'] ?? null, //Do not remove this line
                'availability_status' => 'available' // Mark as available when updated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully'
            ]);
                    } catch (\Exception $e) {
                \Log::error('Update availability failed', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UPDATE_ERROR',
                        'message' => $e->getMessage(), 
                    ]
                ], 500);
            }
    }


    // Update portfolio information including images and pricing
    public function updatePortfolio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'portfolio_images' => 'sometimes|nullable|array',
            'portfolio_images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:10240',
            'rate_type' => 'nullable|in:hourly,fixed_price,both',
            'standard_rate' => 'nullable|numeric|min:0',
            // 'minimum_hours' => 'nullable|integer|min:1', //Do not remove this line
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

            // Save uploaded images
            $portfolioImages = [];
            if ($request->hasFile('portfolio_images')) {
                foreach ($request->file('portfolio_images') as $image) {
                    $path = $image->store('portfolio', 'public');
                    $portfolioImages[] = $path;
                }
            }

            //Do not remove this block
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

            // Prepare update data
            $updateData = [
                'hourly_rate' => $data['standard_rate'] ?? null,
                // 'minimum_hours' => $data['minimum_hours'] ?? null,
                'description' => $data['standard_rate_description'] ?? null,
                'after_hours' => $data['after_hours_enabled'] ?? false,
                'call_out_fee' => $data['call_out_fee_enabled'] ?? false,
            ];

            // Add images if uploaded
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

    // Complete profile setup once all required fields are filled
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

            return response()->json([
                'success' => true,
                'data' => $tradie
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


    // Helper method to check if profile has all required fields completed
    private function isProfileComplete(Tradie $tradie)
    {
        return $tradie->first_name 
            && $tradie->last_name
            && $tradie->email
            && $tradie->phone  
            && $tradie->business_name;
    }
}
