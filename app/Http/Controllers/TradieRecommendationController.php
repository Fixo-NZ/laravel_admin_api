<?php
// New file created for tradie recommendation feature (G4 - #52)
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;
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

        // Assume job has budget_min and budget_max fields
        $budgetMin = $job->budget_min ?? null;
        $budgetMax = $job->budget_max ?? null;
        $jobLat = $job->latitude;
        $jobLng = $job->longitude;

        // 2. Find tradies that match skills, location, availability, and budget
        $isSqlite = 
            config('database.default') === 'sqlite' ||
            (config('database.connections.sqlite.driver') ?? null) === 'sqlite';

        $queryBuilder = Tradie::where('availability_status', 'available')
            ->where('status', 'active')
            ->whereHas('skills', function($query) use ($job) {
                $query->where('skill_name', 'LIKE', '%' . $job->category->category_name . '%');
            })
            ->when($budgetMin, function($q) use ($budgetMin) {
                $q->where('hourly_rate', '>=', $budgetMin);
            })
            ->when($budgetMax, function($q) use ($budgetMax) {
                $q->where('hourly_rate', '<=', $budgetMax);
            });

        $tradies = $queryBuilder->get()->filter(function($tradie) use ($jobLat, $jobLng, $isSqlite) {
            if ($jobLat && $jobLng && $tradie->latitude && $tradie->longitude) {
                $jobLatF = (float)$jobLat;
                $jobLngF = (float)$jobLng;
                $tradieLatF = (float)$tradie->latitude;
                $tradieLngF = (float)$tradie->longitude;
                $theta = $jobLngF - $tradieLngF;
                $dist = sin(deg2rad($jobLatF)) * sin(deg2rad($tradieLatF)) + cos(deg2rad($jobLatF)) * cos(deg2rad($tradieLatF)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = round($miles * 1.609344, 2); // km
                // Only include tradies within their service radius
                if ($isSqlite && $distance > $tradie->service_radius) {
                    return false;
                }
            }
            return true;
        })->map(function($tradie) use ($jobLat, $jobLng) {
            $distance = null;
            if ($jobLat && $jobLng && $tradie->latitude && $tradie->longitude) {
                $jobLatF = (float)$jobLat;
                $jobLngF = (float)$jobLng;
                $tradieLatF = (float)$tradie->latitude;
                $tradieLngF = (float)$tradie->longitude;
                $theta = $jobLngF - $tradieLngF;
                $dist = sin(deg2rad($jobLatF)) * sin(deg2rad($tradieLatF)) + cos(deg2rad($jobLatF)) * cos(deg2rad($tradieLatF)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = round($miles * 1.609344, 2); // km
            }
            return [
                'id'         => $tradie->id,
                'name'       => $tradie->first_name . ' ' . $tradie->last_name,
                'occupation' => $tradie->business_name ?? $tradie->occupation,
                'rating'     => $tradie->rating ?? null,
                'service_area' => $tradie->city . ', ' . $tradie->region,
                'years_experience' => $tradie->years_experience,
                'distance_km' => $distance,
                'hourly_rate' => $tradie->hourly_rate,
                'availability' => $tradie->availability_status,
            ];
        });

        // 3. Rank tradies by best fit (rating, experience, distance)
        $sorted = $tradies->sortByDesc('rating')
                          ->sortByDesc('years_experience')
                          ->sortBy('distance_km')
                          ->values();

        // 4. Limit to 3-5 recommendations
        $recommendations = $sorted->take(5);

        if ($recommendations->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No suitable tradies found',
                'data'    => []
            ]);
        }

        // 5. Return response
        return response()->json([
            'success' => true,
            'jobId'   => $jobId,
            'recommendations' => $recommendations
        ]);
    }
}
