<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\HomeownerJobOffer;
use Illuminate\Support\Facades\Storage;

class JobOfferController extends Controller
{
    /**
     * GET /api/job-offers
     * Public job listings (OPEN only)
     */
    public function index(Request $request)
    {
        try {
            $query = HomeownerJobOffer::with([
                'category',
                'services',
                'photos',
                'homeowner:id,first_name,last_name',
            ])
            ->where('status', 'open');

            // Validate optional filters
            if ($request->filled('category_id')) {
                if (!is_numeric($request->category_id)) {
                    return response()->json([
                        
                        'success' => false,
                        'message' => 'Invalid category_id provided.',
                    ], 422);
                }

                $query->where('service_category_id', $request->category_id);
            }

            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . trim($request->search) . '%');
            }

            $jobs = $query->latest()->paginate(10);

            if ($jobs->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No job offers found.',
                    'data' => $jobs,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Job offers fetched successfully.',
                'data' => $jobs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch job offers.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/job-offers/browse
     * Public job browsing
     */
    public function browse(Request $request)
    {
        try {
            $jobs = HomeownerJobOffer::with([
                'category',
                'services',
                'photos',
                'homeowner:id,first_name,last_name',
            ])
            // ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->latest()
            ->paginate(10);

            if ($jobs->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No available job offers.',
                    'data' => $jobs,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Job offers fetched successfully.',
                'data' => $jobs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to browse job offers.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/job-offers/{id}
     * Public job details
     */
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid job ID.',
            ], 422);
        }

        $job = HomeownerJobOffer::with([
            'category',
            'services',
            'photos',
            'homeowner:id,first_name,last_name',
        ])
        ->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job offer not found or not publicly available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Job offer fetched successfully.',
            'data' => $job,
        ]);
    }
}
