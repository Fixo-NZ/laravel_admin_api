<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_id' => 'required|exists:jobs,id',
            'provider_id' => 'required|exists:users,id',
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
        ];
    }

    public function messages(): array
    {
        return [
            'job_id.required' => 'Job ID is required',
            'job_id.exists' => 'Job does not exist',
            'provider_id.required' => 'Provider ID is required',
            'provider_id.exists' => 'Provider does not exist',
            'rating.required' => 'Rating is required',
            'rating.integer' => 'Rating must be a number',
            'rating.min' => 'Rating must be at least 1 star',
            'rating.max' => 'Rating cannot exceed 5 stars',
            'feedback.max' => 'Feedback cannot exceed 5000 characters',
        ];
    }
}
