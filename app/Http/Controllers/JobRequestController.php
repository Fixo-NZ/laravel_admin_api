<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobRequestController extends Controller
{
    // 🧾 Store a new Job Request
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'homeowner_id' => 'required|exists:homeowners,id',
            'service_type' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job = JobRequest::create($validator->validated());

        return response()->json([
            'message' => 'Job request created successfully!',
            'data' => $job
        ], 201);
    }

    // 👀 List all Job Requests (for Tradies)
    public function index()
    {
        $jobs = JobRequest::with('homeowner')->latest()->get();
        return response()->json($jobs);
    }

    // 📄 View a specific job request
    public function show($id)
    {
        $job = JobRequest::with('homeowner')->findOrFail($id);
        return response()->json($job);
    }
}
