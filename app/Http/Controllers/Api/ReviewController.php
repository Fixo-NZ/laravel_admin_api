<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\Job;
use App\Models\Homeowner;
use App\Models\Tradie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a specific tradie
     */
    public function getTradieReviews($tradieId)
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
        $review = Review::with(['homeowner', 'tradie', 'job'])
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

        if ($job->status !== 'completed') {
            return response()->json([
                'success' => false,
                'can_review' => false,
                'message' => 'Job must be completed before reviewing',
            ]);
        }

        $existingReview = Review::where('job_id', $jobId)
            ->where('homeowner_id', auth()->id())
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
     * Submit a new job review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'tradie_id' => 'required|exists:tradies,id',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|min:3|max:5000',
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

        // Spam prevention checks
        $feedback = $request->feedback ?? '';
        
        // Check for duplicate/similar comments from same user in past 7 days
        if ($feedback) {
            $recentSimilarReview = Review::where('homeowner_id', auth()->id())
                ->where('created_at', '>=', now()->subDays(7))
                ->whereRaw('LOWER(feedback) = LOWER(?)', [trim($feedback)])
                ->exists();
            
            if ($recentSimilarReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You recently submitted a very similar review. Please provide original feedback.',
                ], 422);
            }
        }
        
        // Check rate limit: max 5 reviews per user per day
        $reviewsToday = Review::where('homeowner_id', auth()->id())
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
        
        if ($reviewsToday >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the daily review limit (5 reviews per day). Please try again tomorrow.',
            ], 429);
        }

        $job = Job::where('id', $request->job_id)
            ->where('homeowner_id', auth()->id())
            ->where('status', 'completed')
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or not eligible for review',
            ], 403);
        }

        $existingReview = Review::where('job_id', $request->job_id)
            ->where('homeowner_id', auth()->id())
            ->exists();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this job',
            ], 403);
        }

        $review = Review::create([
            'job_id' => $request->job_id,
            'homeowner_id' => auth()->id(),
            'tradie_id' => $request->tradie_id,
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
            'status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load(['homeowner', 'tradie']),
        ], 201);
    }

    /**
     * ⭐ NEW: Store simple feedback from Flutter (NO Dart server)
     * OPTIMIZED: Minimal logging, efficient database query, streamlined response
     */
public function storeFeedback(Request $request)
{
    // Fast validation with no unnecessary logging
    $validator = Validator::make($request->all(), [
        'name' => 'nullable|string|max:255',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|min:3|max:5000',
        'mediaPaths' => 'nullable|array',
        'contractorId' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        Log::warning('Feedback validation failed', ['errors' => $validator->errors()->all()]);
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'message' => 'Validation failed',
        ], 422);
    }

    try {
        // Extract and prepare data efficiently
        $validated = $validator->validated();
        $name = $validated['name'] ?? 'Anonymous';
        $rating = (int) $validated['rating'];
        $comment = trim($validated['comment'] ?? '');
        $mediaPaths = $validated['mediaPaths'] ?? [];
        $contractorId = $validated['contractorId'] ?? null;
        
        // Spam prevention: check for duplicate comments in past 7 days
        if ($comment) {
            $recentSimilarFeedback = Review::where('homeowner_id', auth()->id())
                ->where('created_at', '>=', now()->subDays(7))
                ->whereRaw('LOWER(feedback) = LOWER(?)', [$comment])
                ->exists();
            
            if ($recentSimilarFeedback) {
                Log::warning('Duplicate feedback attempt', [
                    'homeowner_id' => auth()->id(),
                    'comment_preview' => substr($comment, 0, 50),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Similar feedback already submitted recently. Please provide original feedback.',
                ], 422);
            }
        }
        
        // Rate limit: max 10 feedback submissions per user per day
        $feedbackToday = Review::where('homeowner_id', auth()->id())
            ->where('created_at', '>=', now()->startOfDay())
            ->where('job_id', null)
            ->count();
        
        if ($feedbackToday >= 10) {
            Log::warning('Feedback rate limit exceeded', ['homeowner_id' => auth()->id()]);
            return response()->json([
                'success' => false,
                'message' => 'You have reached the daily feedback limit (10 per day). Try again tomorrow.',
            ], 429);
        }

        Log::info('Attempting to store feedback', [
            'name' => $name,
            'rating' => $rating,
            'comment' => $comment,
            'contractorId' => $contractorId,
            'homeowner_id' => auth()->id(),
        ]);

        // Create review directly without intermediate variables
        $review = Review::create([
            'job_id' => null,
            'homeowner_id' => auth()->id(),
            'tradie_id' => $contractorId,
            'rating' => $rating,
            'feedback' => $comment,
            'images' => $mediaPaths,
            'helpful_count' => 0,
            'show_username' => $name !== 'Anonymous',
            'status' => 'approved',
        ]);

        Log::info('Feedback stored successfully', ['review_id' => $review->id]);

        // Invalidate relevant caches for this contractor
        if ($contractorId) {
            \Illuminate\Support\Facades\Cache::forget("tradie_avg_rating_{$contractorId}");
            \Illuminate\Support\Facades\Cache::forget("tradie_review_count_{$contractorId}");
            \Illuminate\Support\Facades\Cache::forget("tradie_rating_breakdown_{$contractorId}");
        }

        // Build response from local variables (no reload from DB)
        return response()->json([
            'data' => [
                'id' => (string) $review->id,
                'name' => $name,
                'rating' => $rating,
                'date' => $review->created_at->toIso8601String(),
                'comment' => $comment,
                'likes' => 0,
                'isLiked' => false,
                'mediaPaths' => $mediaPaths,
                'contractorId' => $contractorId ? (string) $contractorId : null,
            ]
        ], 201);
    } catch (\Exception $e) {
        Log::error('Failed to store feedback', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to store feedback',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Delete feedback by ID (OPTIMIZED)
     */
    public function deleteFeedback($id)
    {
        try {
            $review = Review::find($id);

            if (!$review) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $tradieId = $review->tradie_id;
            $review->delete();

            // Invalidate cache for affected tradie
            if ($tradieId) {
                \Illuminate\Support\Facades\Cache::forget("tradie_avg_rating_{$tradieId}");
                \Illuminate\Support\Facades\Cache::forget("tradie_review_count_{$tradieId}");
                \Illuminate\Support\Facades\Cache::forget("tradie_rating_breakdown_{$tradieId}");
            }

            return response('', 204);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete review',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle like status (OPTIMIZED)
     */
    public function likeFeedback($id)
    {
        try {
            $review = Review::find($id);

            if (!$review) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $newLikes = $review->helpful_count > 0 ? 0 : 1;
            $review->update(['helpful_count' => $newLikes]);

            return response()->json([
                'data' => [
                    'id' => (string) $review->id,
                    'name' => $review->homeowner->first_name ?? 'Anonymous',
                    'rating' => $review->rating,
                    'date' => $review->created_at->toIso8601String(),
                    'comment' => $review->feedback,
                    'likes' => $review->helpful_count,
                    'isLiked' => $review->helpful_count > 0,
                    'mediaPaths' => $review->images ?? [],
                    'contractorId' => $review->tradie_id ? (string) $review->tradie_id : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update like',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
