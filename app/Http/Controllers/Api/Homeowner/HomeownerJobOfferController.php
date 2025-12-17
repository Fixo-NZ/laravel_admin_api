<?php

namespace App\Http\Controllers\Api\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\HomeownerJobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class HomeownerJobOfferController extends Controller
{
    // ============================================================
    // API METHODS - /homeowner/job-offers
    // ============================================================

    /**
     * GET /homeowner/job-offers
     * Filters, search, sorting
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = HomeownerJobOffer::with([
            'category',
            'services',
            'photos',
        ])->where('homeowner_id', $user->id);

        /* --------------------
         | Filters
         |-------------------- */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        /* --------------------
         | Search (title + description)
         |-------------------- */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        /* --------------------
         | Sorting
         |-------------------- */
        $sortable = ['created_at', 'status', 'title'];
        $sortBy = in_array($request->get('sort_by'), $sortable)
            ? $request->get('sort_by')
            : 'created_at';

        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return response()->json([
            'success' => true,
            'data' => $query->paginate(10),
        ]);
    }

    
    public function myJobOffers(Request $request)
    {
        $homeowner = $request->user();

        $jobOffers = HomeownerJobOffer::with(['category', 'services', 'photos'])
            ->where('homeowner_id', $homeowner->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'My job offers fetched successfully.',
            'data' => $jobOffers,
        ]);
    }

    
    /**
     * GET /homeowner/job-offers/{id}
     */
    public function show(Request $request, $id)
    {
        $jobOffer = HomeownerJobOffer::with([
            'category',
            'services',
            'photos',
        ])
        ->where('homeowner_id', $request->user()->id)
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $jobOffer,
        ]);
    }

    /**
     * POST /homeowner/job-offers
     * Create a new job offer
     */
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
            ]);

            $homeowner = $request->user();

            $jobOffer = $homeowner->jobOffers()->create($validated);

            $jobOffer->services()->sync($validated['services']);

            if (!empty($validated['photos'])) {
                foreach ($validated['photos'] as $base64Image) {
                    $this->storeBase64Photo($jobOffer, $base64Image);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Job offer created successfully.',
                'data' => $jobOffer->load(['services', 'photos']),
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

    /**
     * Helper : store a base64 image for a job offer
     */
    private function storeBase64Photo($jobOffer, $base64Image)
    {
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

    public function update(Request $request, $id)
    {
        try {
            $jobOffer = HomeownerJobOffer::where('homeowner_id', $request->user()->id)
                ->findOrFail($id);

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

                'status' => 'sometimes|in:posted,assigned,completed,cancelled',

                'services' => 'sometimes|array|min:1',
                'services.*' => 'exists:services,id',

                'photos' => 'nullable|array|max:8',
                'photos.*' => 'string',
            ]);

            if (isset($validated['status']) && !$request->user()->hasRole('admin')) {
                unset($validated['status']);
            }

            $jobOffer->update($validated);

            // Sync services if provided
            if (isset($validated['services'])) {
                $jobOffer->services()->sync($validated['services']);
            }

            // Store photos if provided
            if (!empty($validated['photos'])) {
                foreach ($validated['photos'] as $base64Image) {
                    $this->storeBase64Photo($jobOffer, $base64Image);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Job offer updated successfully.',
                'data' => $jobOffer->load(['category', 'services', 'photos']),
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
        $user = $request->user();

        if (!$user instanceof \App\Models\Homeowner) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN_ROLE',
                    'message' => 'Only homeowners can delete job offers.',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        $jobOffer = HomeownerJobOffer::find($id);

        if (!$jobOffer) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Job offer not found.',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($jobOffer->homeowner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ACTION',
                    'message' => 'You do not own this job offer.',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        $jobOffer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job offer deleted successfully.',
        ]);
    }
}
