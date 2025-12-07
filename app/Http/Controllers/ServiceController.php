<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with(['homeowner', 'category'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'homeowner_id' => 'required|exists:homeowners,id',
            'job_categoryid' => 'required|exists:categories,id',
            'job_description' => 'required|string',
            'location' => 'required|string|max:255',
            'status' => 'required|in:Pending,InProgress,Completed,Cancelled',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);
        $service = Service::create($validated);
        return response()->json($service->load(['homeowner', 'category']), 201);
    }

    public function show($id)
    {
        return Service::with(['homeowner', 'category'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            'homeowner_id' => 'sometimes|exists:homeowners,id',
            'job_categoryid' => 'sometimes|exists:categories,id',
            'job_description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:Pending,InProgress,Completed,Cancelled',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);
        $service->update($validated);
        return response()->json($service->load(['homeowner', 'category']), 200);
    }

    public function destroy($id)
    {
        Service::destroy($id);
        return response()->json(null, 204);
    }
}