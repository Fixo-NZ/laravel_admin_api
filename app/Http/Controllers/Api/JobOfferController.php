<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\HomeownerJobOffer;
use Illuminate\Support\Facades\Storage;
use App\Events\ScheduleDisplayed;

class JobOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeownerJobOffer::with(['category', 'services', 'photos', 'homeowner:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Job offers fetched successfully.',
            'data' => $query->latest()->get(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $job = HomeownerJobOffer::with(['category', 'services', 'photos'])->findOrFail($id);

        if ($job->homeowner_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json(['success' => true, 'data' => $job]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_category_id' => 'required|exists:service_categories,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:300',
                'job_type' => 'required|in:standard,urgent,recurrent',
                'frequency' => 'nullable|required_if:job_type,recurrent|in:daily,weekly,monthly,quarterly,yearly,custom',
                'start_date' => 'nullable|required_if:job_type,recurrent|date',
                'end_date' => 'nullable|required_if:job_type,recurrent|date|after_or_equal:start_date',
                'preferred_date' => 'nullable|required_if:job_type,standard|date',
                'job_size' => 'required|in:small,medium,large',
                'address' => 'required|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'services' => 'required|array|min:1',
                'services.*' => 'exists:services,id',
                'photos' => 'nullable|array|max:8',
                'photos.*' => 'string',

                'start_time' => 'nullable|required_if:job_type,recurrent|date',
                'end_time' => 'nullable|required_if:job_type,recurrent|date|after_or_equal:start_time',
                'tradie_id' => 'nullable|exists:tradies,id'
            ]);

            $homeowner = $request->user();

            $jobOffer = $homeowner->jobOffers()->create($validated);

            $jobOffer->services()->sync($validated['services']);

            // Handle Base64 photos
            if (!empty($validated['photos'])) {
                foreach ($validated['photos'] as $base64Image) {
                    $this->storeBase64Photo($jobOffer, $base64Image);
                }
            }

            // Load the job offer with relationships
            $jobOffer->load(['category', 'services', 'photos', 'homeowner:id,first_name,last_name,middle_name,email,address,phone']);

            // Add debug logging before the broadcast
            \Log::info('JobOffer created', [
                'job_id' => $jobOffer->id,
                'tradie_id' => $jobOffer->tradie_id,
                'has_tradie_id' => !is_null($jobOffer->tradie_id)
            ]);
            
            // Debug output
            error_log("DEBUG: JobOffer created - ID: {$jobOffer->id}, Tradie ID: {$jobOffer->tradie_id}");

            // 🔥 BROADCAST NOTIFICATION (Flutter app will fetch fresh data via API)
            if ($jobOffer->tradie_id) {
                \Log::info('Broadcasting schedule update notification', [
                    'tradie_id' => $jobOffer->tradie_id,
                    'job_id' => $jobOffer->id,
                    'action' => 'created'
                ]);

                broadcast(new ScheduleDisplayed([
                    'tradie_id' => $jobOffer->tradie_id,
                    'job_id' => $jobOffer->id,
                    'action' => 'created',
                    'message' => 'New job offer created',
                ]));
            } else {
                \Log::info('No tradie_id, skipping broadcast');
            }

            return response()->json([
                'success' => true,
                'message' => 'Job offer created successfully.',
                'data' => $jobOffer,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jobOffer = HomeownerJobOffer::findOrFail($id);

            if ($jobOffer->homeowner_id !== $request->user()->id) {
                abort(403, 'You do not have permission to update this job offer.');
            }

            $validated = $request->validate([
                'service_category_id' => 'sometimes|exists:service_categories,id',
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:300',
                'job_type' => 'sometimes|in:standard,urgent,recurrent',
                'frequency' => 'nullable|required_if:job_type,recurrent|in:daily,weekly,monthly,quarterly,yearly,custom',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'preferred_date' => 'nullable|required_if:job_type,standard|date',
                'job_size' => 'sometimes|in:small,medium,large',
                'address' => 'sometimes|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'services' => 'sometimes|array',
                'services.*' => 'exists:services,id',
                'photos' => 'nullable|array|max:8',
                'photos.*' => 'string',
            ]);

            $jobOffer->update($validated);

            if (isset($validated['services'])) {
                $jobOffer->services()->sync($validated['services']);
            }

            if (!empty($validated['photos'])) {
                foreach ($validated['photos'] as $base64Image) {
                    $this->storeBase64Photo($jobOffer, $base64Image);
                }
            }

            // Load the job offer with relationships
            $jobOffer->load(['category', 'services', 'photos', 'homeowner:id,first_name,last_name,middle_name,email,address,phone']);

            // 🔥 BROADCAST NOTIFICATION (Flutter app will fetch fresh data via API)
            if ($jobOffer->tradie_id) {
                broadcast(new ScheduleDisplayed([
                    'tradie_id' => $jobOffer->tradie_id,
                    'job_id' => $jobOffer->id,
                    'action' => 'updated',
                    'message' => 'Job offer updated',
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Job offer updated successfully.',
                'data' => $jobOffer,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $jobOffer = HomeownerJobOffer::findOrFail($id);

        if ($jobOffer->homeowner_id !== $request->user()->id) {
            abort(403, 'You do not have permission to delete this job offer.');
        }

        // Store tradie_id before deletion
        $tradieId = $jobOffer->tradie_id;

        foreach ($jobOffer->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }

        $jobOffer->delete();

        // 🔥 BROADCAST NOTIFICATION (Flutter app will fetch fresh data via API)
        if ($tradieId) {
            broadcast(new ScheduleDisplayed([
                'tradie_id' => $tradieId,
                'job_id' => $id,
                'action' => 'deleted',
                'message' => 'Job offer deleted',
            ]));
        }

        return response()->json(['success' => true, 'message' => 'Job offer deleted successfully.']);
    }

    /**
     * Helper: store a base64 image for a job offer
     */
    private function storeBase64Photo($jobOffer, $base64Image)
    {
        // Match base64 header
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = strtolower($matches[1]);
            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $imageData = base64_decode($imageData);
        } else {
            throw new \Exception('Invalid base64 image format.');
        }

        $fileName = uniqid('job_', true) . '.' . $extension;
        $filePath = 'uploads/job_photos/' . $fileName;

        Storage::disk('public')->put($filePath, $imageData);

        $jobOffer->photos()->create([
            'file_path' => $filePath,
            'original_name' => $fileName,
            'file_size' => strlen($imageData),
        ]);
    }
}
