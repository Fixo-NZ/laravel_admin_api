<?php

namespace App\Http\Controllers;

use App\Models\JobRequest;
use App\Models\Tradie;
use Illuminate\Http\Request;

class TradieRecommendationController extends Controller
{
    /**
     * Recommend tradies for a given job request.
     *
     * @param  int  $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommend($jobId)
    {
        // 1) Load job request; may be null if ID is invalid
        $job = JobRequest::with('category')
            ->where('status', '!=', 'cancelled')
            ->find($jobId);

        // If no matching job, return empty list instead of throwing
        if (!$job) {
            return response()->json([
                'success' => true,
                'message' => 'No available tradie found for this job request.',
                'data'    => [],
            ], 200);
        }

        // Extract job details
        $latitude   = $job->latitude;
        $longitude  = $job->longitude;
        $budget     = $job->budget;
        $categoryId = $job->job_category_id;

        // 2️⃣  Build query to find matching tradies
        $query = Tradie::query()
            ->active()
            ->available()
            ->verified()
            // Match by service category
            ->whereHas('services', function ($q) use ($categoryId) {
                $q->where('category', function ($sub) use ($categoryId) {
                    // If job_categories and services are mapped by name/category
                    // adapt this to join directly if you have a pivot
                });
            })
            // Within service radius of the job location
            ->withinServiceRadius($latitude, $longitude);

        // Filter by budget if provided
        if (!is_null($budget)) {
            $query->where(function ($q) use ($budget) {
                $q->whereNull('hourly_rate')
                    ->orWhere('hourly_rate', '<=', $budget);
            });
        }

        // 3️⃣  Fetch and rank
        $tradies = $query
            ->with(['services:id,name,category'])
            ->get()
            ->sortByDesc(function ($t) {
                // Ranking formula: rating first, then experience
                return [$t->average_rating, $t->years_experience];
            })
            ->take(5) // return max 5
            ->values();

        // 4️⃣  Return response
        if ($tradies->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No available tradie found for this job request.',
                'data'    => [],
            ], 200);
        }

        // Map for frontend (key details)
        $data = $tradies->map(function ($t) {
            return [
                'id'                => $t->id,
                'name'              => trim("{$t->first_name} {$t->last_name}"),
                'business_name'     => $t->business_name,
                'distance_km'       => round($t->distance ?? 0, 2),
                'average_rating'    => round($t->average_rating, 2),
                'total_reviews'     => $t->total_reviews,
                'hourly_rate'       => $t->hourly_rate,
                'availability'      => $t->availability_status,
                'service_radius_km' => $t->service_radius,
                'city'              => $t->city,
                'region'            => $t->region,
                'services'          => $t->services->pluck('name'),
                'avatar'            => $t->avatar,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $data->count(),
            'data'    => $data,
        ]);
    }
}
