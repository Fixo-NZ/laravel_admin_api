<?php

namespace App\Http\Controllers;

use App\Models\JobRequest;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * List all job requests (with filters).
     */
    public function index(Request $request)
    {
        $query = JobRequest::query()->with(['homeowner', 'category']);

        // 🔹 Optional filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->has('homeowner_id')) {
            $query->where('homeowner_id', $request->homeowner_id);
        }

        // 🔹 Sorting
        $query->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data' => $query->paginate(10)
        ]);
    }

    /**
     * Show details of a single job request.
     */
    public function show($id)
    {
        $job = JobRequest::with(['homeowner', 'category'])->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Create a new job request (by homeowner).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'homeowner_id' => 'required|exists:homeowners,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'job_type' => 'required|in:urgent,standard,recurring',
            'budget' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'scheduled_at' => 'nullable|date',
        ]);

        $job = JobRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job request created successfully',
            'data' => $job,
        ], 201);
    }

    /**
     * Update a job request (only homeowner can do this).
     */
    public function update(Request $request, $id)
    {
        $job = JobRequest::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'job_type' => 'sometimes|in:urgent,standard,recurring',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'scheduled_at' => 'nullable|date',
        ]);

        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job request updated successfully',
            'data' => $job,
        ]);
    }

    /**
     * Delete a job request.
     */
    public function destroy($id)
    {
        $job = JobRequest::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job request deleted successfully',
        ]);
    }
}
