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
        // 1️⃣ Load job request
        $job = JobRequest::with('category')
            ->where('status', '!=', 'cancelled')
            ->findOrFail($jobId);

        $latitude   = $job->latitude ?? -36.8485; // Default Auckland
        $longitude  = $job->longitude ?? 174.7633;
        $budget     = $job->budget;
        $jobCategoryId = $job->job_category_id;

        $categoryName = $job->category?->category_name ?? null;

        // 2️⃣ Build tradie query
        $query = Tradie::query()
            ->active()
            ->available()
            ->verified();

        // Match service category
        if ($categoryName) {
            $query->whereHas('services', function ($q) use ($categoryName) {
                $q->whereHas('category', fn($catQ) => $catQ->where('category_name', $categoryName));
            });
        } else {
            $query->whereHas('services', fn($q) => $q->where('job_categoryid', $jobCategoryId));
        }

        // Within service radius
        $query->withinServiceRadius($latitude, $longitude);

        // Filter by budget if provided
        if (!is_null($budget)) {
            $query->where(function ($q) use ($budget) {
                $q->whereNull('hourly_rate')
                    ->orWhere('hourly_rate', '<=', $budget);
            });
        }

        // 3️⃣ Fetch tradies
        $tradies = $query->with(['services:id,job_description,job_categoryid'])->get();

        // 4️⃣ Sort by: distance (asc), average_rating (desc), years_experience (desc)
        $tradies = $tradies->sort(function ($a, $b) {
            $distanceA = $a->distance ?? PHP_INT_MAX;
            $distanceB = $b->distance ?? PHP_INT_MAX;

            if ($distanceA != $distanceB) return $distanceA <=> $distanceB;

            $ratingA = $a->average_rating ?? 0;
            $ratingB = $b->average_rating ?? 0;

            if ($ratingA != $ratingB) return $ratingB <=> $ratingA;

            return ($b->years_experience ?? 0) <=> ($a->years_experience ?? 0);
        })->take(5)->values();

        // 5️⃣ Prepare response
        if ($tradies->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No available tradie found for this job request.',
                'data'    => [],
            ]);
        }

        $data = $tradies->map(fn($t) => [
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
            'services'          => $t->services->pluck('job_description'),
            'avatar'            => $t->avatar,
        ]);

        return response()->json([
            'success' => true,
            'count'   => $data->count(),
            'data'    => $data,
        ]);
    }
}
