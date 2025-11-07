<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a specific provider
     */
    public function getProviderReviews($tradieId)
    {
    try {
        $reviews = Review::with(['homeowner', 'job'])
            ->where('tradie_id', $tradieId)
            ->where('status', 'approved')
            ->latest()
            ->paginate(20);

        $stats = [
            'average_rating' => Review::getTradieAverageRating($tradieId),
            'total_reviews' => Review::getTradieReviewCount($tradieId),
            'rating_breakdown' => Review::getTradieRatingBreakdown($tradieId),
        ];

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'stats' => $stats,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Get reviews for a specific job
     */
    public function getJobReview($jobId)
    {
        $review = Review::with(['user', 'provider'])
            ->where('job_id', $jobId)
            ->approved()
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'No review found for this job',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review,
        ]);
    }

    /**
     * Check if a job can be reviewed
     */
    public function canReview($jobId)
    {
        $job = Job::find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // Check if job is completed
        if ($job->status !== 'completed') {
            return response()->json([
                'success' => false,
                'can_review' => false,
                'message' => 'Job must be completed before reviewing',
            ]);
        }

        // Check if already reviewed
        $existingReview = Review::where('job_id', $jobId)
            ->where('user_id', auth()->id())
            ->exists();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'can_review' => false,
                'message' => 'You have already reviewed this job',
            ]);
        }

        return response()->json([
            'success' => true,
            'can_review' => true,
            'job' => $job,
        ]);
    }

    /**
     * Submit a new review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'tradie_id' => 'required|exists:tradies,id',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:5000',
            'service_quality_rating' => 'nullable|integer|min:1|max:5',
            'service_quality_comment' => 'nullable|string|max:1000',
            'performance_rating' => 'nullable|integer|min:1|max:5',
            'performance_comment' => 'nullable|string|max:1000',
            'contractor_service_rating' => 'nullable|integer|min:1|max:5',
            'response_time_rating' => 'nullable|integer|min:1|max:5',
            'best_feature' => 'nullable|string|max:255',
            'show_username' => 'boolean',
        ]);

        if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

        // Verify job belongs to user and is completed
        $job = Job::where('id', $request->job_id)
            ->where('homeowner_id', auth()->id()) // Assuming auth()->user() is Homeowner
            ->where('status', 'completed')
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or not eligible for review',
            ], 403);
        }

        // Check if already reviewed
        $existingReview = Review::where('job_id', $request->job_id)
            ->where('homeowner_id', auth()->id())
            ->exists();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this job',
            ], 403);
        }

        // Create review
        $review = Review::create([
            'job_id' => $request->job_id,
            'homeowner_id' => auth()->id(),
            'provider_id' => $request->provider_id,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'service_quality_rating' => $request->service_quality_rating,
            'service_quality_comment' => $request->service_quality_comment,
            'performance_rating' => $request->performance_rating,
            'performance_comment' => $request->performance_comment,
            'contractor_service_rating' => $request->contractor_service_rating,
            'response_time_rating' => $request->response_time_rating,
            'best_feature' => $request->best_feature,
            'show_username' => $request->show_username ?? true,
            'status' => 'approved', // Auto-approve or set to 'pending' if you want manual review
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load(['homeowner', 'tradie']),
        ], 201);
    }

    /**
     * Mark a review as helpful
     */
    public function markHelpful($reviewId)
    {
        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $review->increment('helpful_count');

        return response()->json([
            'success' => true,
            'message' => 'Review marked as helpful',
            'helpful_count' => $review->helpful_count,
        ]);
    }

    /**
     * Report a review
     */
    public function reportReview(Request $request, $reviewId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|in:spam,offensive,inappropriate,fake,other',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        // Check if user already reported this review
        $existingReport = ReviewReport::where('review_id', $reviewId)
            ->where('reported_by', auth()->id())
            ->exists();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported this review',
            ], 403);
        }

        $report = ReviewReport::create([
            'review_id' => $reviewId,
            'reported_by' => auth()->id(),
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        // Update review status to reported
        $review->update(['status' => 'reported']);

        return response()->json([
            'success' => true,
            'message' => 'Review reported successfully. Our team will review it shortly.',
            'data' => $report,
        ], 201);
    }

    /**
     * Get reviews given by the authenticated user
     */
    public function myReviews()
    {
        $reviews = Review::with(['provider', 'job'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get statistics for a provider
     */
    public function getProviderStats($providerId)
    {
        $totalReviews = Review::forProvider($providerId)->approved()->count();
        $averageRating = Review::getProviderAverageRating($providerId);
        $ratingBreakdown = Review::getProviderRatingBreakdown($providerId);

        // Calculate percentages
        $ratingPercentages = [];
        foreach (range(1, 5) as $star) {
            $count = $ratingBreakdown[$star] ?? 0;
            $ratingPercentages[$star] = [
                'count' => $count,
                'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0,
            ];
        }

        // Get detailed ratings averages
        $detailedRatings = Review::forProvider($providerId)
            ->approved()
            ->select([
                DB::raw('AVG(service_quality_rating) as avg_service_quality'),
                DB::raw('AVG(performance_rating) as avg_performance'),
                DB::raw('AVG(contractor_service_rating) as avg_contractor_service'),
                DB::raw('AVG(response_time_rating) as avg_response_time'),
            ])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 1),
                'rating_breakdown' => $ratingPercentages,
                'detailed_ratings' => [
                    'service_quality' => round($detailedRatings->avg_service_quality ?? 0, 1),
                    'performance' => round($detailedRatings->avg_performance ?? 0, 1),
                    'contractor_service' => round($detailedRatings->avg_contractor_service ?? 0, 1),
                    'response_time' => round($detailedRatings->avg_response_time ?? 0, 1),
                ],
            ],
        ]);
    }
}