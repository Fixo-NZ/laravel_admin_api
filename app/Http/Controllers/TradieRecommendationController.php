<?php
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
        // 3. Rank tradies by best fit (rating, experience, distance)
        $sorted = $tradies->sortByDesc('rating')
                          ->sortByDesc('years_experience')
                          ->sortBy('distance_km')
                          ->sortBy('distance_km')
                          ->values();

        // 4. Limit to 3-5 recommendations
        $recommendations = $sorted->take(5);

        if ($recommendations->isEmpty()) {
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
        // 5. Return response
        return response()->json([
            'success' => true,
            'serviceId'   => $serviceId,
            'recommendations' => $recommendations
        ]);
    }
}
