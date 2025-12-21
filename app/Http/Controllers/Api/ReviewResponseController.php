<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\Tradie;
use App\Notifications\ReviewResponseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewResponseController extends Controller
{
    public function show(Review $review)
    {
        $response = $review->response()->with('tradie')->first();

        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'No response found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function store(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!($user instanceof Tradie)) {
            return response()->json([
                'success' => false,
                'message' => 'Only tradies can respond to reviews',
            ], 403);
        }

        if ($user->id !== $review->tradie_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only respond to reviews about your own jobs',
            ], 403);
        }

        if ($review->response()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already responded to this review',
            ], 409);
        }

        $response = ReviewResponse::create([
            'review_id' => $review->id,
            'tradie_id' => $user->id,
            'content' => $request->input('content'),
        ]);

        if ($review->homeowner) {
            $review->homeowner->notify(new ReviewResponseNotification($response));
        }

        return response()->json([
            'success' => true,
            'message' => 'Response saved',
            'data' => $response->load('tradie'),
        ], 201);
    }

    public function update(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!($user instanceof Tradie)) {
            return response()->json([
                'success' => false,
                'message' => 'Only tradies can edit responses',
            ], 403);
        }

        if ($user->id !== $review->tradie_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit responses to your own reviews',
            ], 403);
        }

        $response = $review->response;
        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'No response found to update',
            ], 404);
        }

        $response->content = $request->input('content');
        $response->edited_at = now();
        $response->save();

        return response()->json([
            'success' => true,
            'message' => 'Response updated',
            'data' => $response->fresh()->load('tradie'),
        ]);
    }
}
