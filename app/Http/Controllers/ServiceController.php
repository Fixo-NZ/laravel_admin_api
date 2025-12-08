<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        // Get authenticated homeowner - filter services by logged in user
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        // Return only services for the authenticated homeowner
        $services = Service::where('homeowner_id', $homeowner->id)
                     ->with(['homeowner', 'category'])
                     ->orderBy('created_at', 'desc')
                     ->get();
        
        // Debug logging
        //\Log::info("Service index called by homeowner: {$homeowner->id}, found {$services->count()} services");
        
        // Return as array (Laravel will auto-convert to JSON)
        return response()->json($services, 200);
    }

    public function store(Request $request)
    {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        $validated = $request->validate([
            'job_categoryid' => 'required|exists:categories,id',
            'job_description' => 'required|string',
            'location' => 'required|string|max:255',
            'status' => 'required|in:Pending,InProgress,Completed,Cancelled',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);
        
        // Use authenticated homeowner ID
        $validated['homeowner_id'] = $homeowner->id;
        
        $service = Service::create($validated);
        return response()->json($service->load(['homeowner', 'category']), 201);
    }

    public function show(Request $request, $id)
    {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        // Ensure homeowner can only view their own services
        $service = Service::where('id', $id)
                         ->where('homeowner_id', $homeowner->id)
                         ->with(['homeowner', 'category'])
                         ->firstOrFail();
        
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        // Ensure homeowner can only update their own services
        $service = Service::where('id', $id)
                         ->where('homeowner_id', $homeowner->id)
                         ->firstOrFail();
        
        $validated = $request->validate([
            'job_categoryid' => 'sometimes|exists:categories,id',
            'job_description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:Pending,InProgress,Completed,Cancelled',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);
        
        // Remove homeowner_id from validation - user can't change ownership
        unset($validated['homeowner_id']);
        
        $service->update($validated);
        return response()->json($service->load(['homeowner', 'category']), 200);
    }

    public function destroy(Request $request, $id)
    {
        // Get authenticated homeowner
        $homeowner = $request->user();
        
        if (!$homeowner) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'No authenticated user found'
            ], 401);
        }
        
        // Ensure homeowner can only delete their own services
        $service = Service::where('id', $id)
                         ->where('homeowner_id', $homeowner->id)
                         ->firstOrFail();
        
        $service->delete();
        return response()->json(null, 204);
    }
}