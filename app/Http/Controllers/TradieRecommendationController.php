<?php
// New file created for tradie recommendation feature (G4 - #52)
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service as Job;
use App\Models\Tradie;

class TradieRecommendationController extends Controller
{
    public function recommend($jobId)
    {
        // 1. Get the job request
    $job = Job::with('category')->find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        // 2. Find tradies that match service type (category), location, availability, and skills
        $tradies = Tradie::where('availability_status', 'available')
            ->where('status', 'active')
            // Filter by job category (assuming relation tradie_skills / categories)
            ->whereHas('skills', function($query) use ($job) {
                $query->where('skill_name', 'LIKE', '%' . $job->category->category_name . '%');
            })
            // Filter by location radius (if lat/long is stored for tradie & job)
            ->get()
            ->map(function($tradie) use ($job) {
                return [
                    'id'         => $tradie->id,
                    'name'       => $tradie->first_name . ' ' . $tradie->last_name,
                    'occupation' => $tradie->business_name ?? $tradie->occupation,
                    'rating'     => $tradie->rating ?? null,
                    'service_area' => $tradie->city . ', ' . $tradie->region,
                    'years_experience' => $tradie->years_experience,
                ];
            });

        // 3. Rank tradies (simple: by rating + experience, but you can improve scoring logic)
        $sorted = $tradies->sortByDesc('rating')
                          ->sortByDesc('years_experience')
                          ->values();

        if ($sorted->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No suitable tradies found',
                'recommendations'    => []
            ]);
        }

        // 4. Return response
        return response()->json([
            'success' => true,
            'jobId'   => $jobId,
            'recommendations' => $sorted
        ]);
    }
}
