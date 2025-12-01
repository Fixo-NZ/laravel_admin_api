<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of all services with related homeowner, category, and tradies.
     */
    public function index()
    {
        return Service::with(['homeowner', 'category', 'tradies'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Store a newly created service in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'homeowner_id'      => 'required|exists:homeowners,id',
            'job_category_id'   => 'required|exists:categories,id',
            'job_description'   => 'required|string',
            'location'          => 'required|string|max:255',
            'status'            => 'required|in:Pending,InProgress,Completed,Cancelled',
            'rating'            => 'nullable|integer|min:1|max:5',
        ]);

        // Let Laravel handle timestamps automatically
        $service = Service::create($validated);

        return response()->json($service, 201);
    }

    /**
     * Display a single service by ID.
     * If the ID is missing or invalid, return the first existing service.
     */
    public function show($id = null)
    {
        if (!$id || !Service::where('id', $id)->exists()) {
            $service = Service::with(['homeowner', 'category', 'tradies'])
                ->orderBy('id')
                ->first();
        } else {
            $service = Service::with(['homeowner', 'category', 'tradies'])->findOrFail($id);
        }

        return response()->json($service);
    }

    /**
     * Update a service by ID.
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'homeowner_id'      => 'sometimes|exists:homeowners,id',
            'job_category_id'   => 'sometimes|exists:categories,id',
            'job_description'   => 'sometimes|string',
            'location'          => 'sometimes|string|max:255',
            'status'            => 'sometimes|in:Pending,InProgress,Completed,Cancelled',
            'rating'            => 'nullable|integer|min:1|max:5',
        ]);

        $service->update($validated);

        return response()->json($service, 200);
    }

    /**
     * Delete a service by ID.
     */
    public function destroy($id)
    {
        Service::destroy($id);
        return response()->json(null, 204);
    }
}






// class ServiceController extends Controller
// {
//     public function index()
//     {
//         return Service::with(['homeowner', 'category'])->get();
//     }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'homeowner_id' => 'required|exists:homeowners,id',
//             'job_categoryid' => 'required|exists:categories,id',
//             'job_description' => 'required|string',
//             'location' => 'required|string|max:255',
//             'status' => 'required|in:Pending,InProgress,Completed,Cancelled',
//             'createdAt' => 'required|date',
//             'updatedAt' => 'required|date',
//             'rating' => 'nullable|integer|min:1|max:5',
//         ]);
//         $service = Service::create($validated);
//         return response()->json($service, 201);
//     }

//     public function show($id)
//     {
//         return Service::with(['homeowner', 'category'])->findOrFail($id);
//     }

//     public function update(Request $request, $id)
//     {
//         $service = Service::findOrFail($id);
//         $validated = $request->validate([
//             'homeowner_id' => 'sometimes|exists:homeowners,id',
//             'job_categoryid' => 'sometimes|exists:categories,id',
//             'job_description' => 'sometimes|string',
//             'location' => 'sometimes|string|max:255',
//             'status' => 'sometimes|in:Pending,InProgress,Completed,Cancelled',
//             'createdAt' => 'sometimes|date',
//             'updatedAt' => 'sometimes|date',
//             'rating' => 'nullable|integer|min:1|max:5',
//         ]);
//         $service->update($validated);
//         return response()->json($service, 200);
//     }

//     public function destroy($id)
//     {
//         Service::destroy($id);
//         return response()->json(null, 204);
//     }
// }
