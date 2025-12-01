<?php
<<<<<<< HEAD

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
        // 1️⃣  Load job request with required details
        $job = JobRequest::with('category')
            ->where('status', '!=', 'cancelled')
            ->findOrFail($jobId);

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
=======
// New file created for tradie recommendation feature (G4 - #52)
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Tradie;

class TradieRecommendationController extends Controller
{
    public function recommend($serviceId)
    {
        // 1. Get the service (job) request
        $service = Service::with('category')->find($serviceId);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service (Job) not found'
            ], 404);
        }

        $jobLat = $service->location_lat ?? null;
        $jobLng = $service->location_lng ?? null;

        // 2. Find tradies that match skills, location, availability, and budget
        $isSqlite = 
            config('database.default') === 'sqlite' ||
            (config('database.connections.sqlite.driver') ?? null) === 'sqlite';

        $queryBuilder = Tradie::where('availability_status', 'available')
            ->where('status', 'active')
            ->whereHas('skills', function($query) use ($service) {
                $query->where('skill_name', 'LIKE', '%' . $service->category->category_name . '%');
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
            'serviceId'   => $serviceId,
            'recommendations' => $recommendations
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
        ]);
    }
}
