<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Review;
use App\Models\User;
use App\Models\Job;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $feedbacks = [
            "Excellent service! Very professional and completed the work on time.",
            "Good work overall, but took a bit longer than expected.",
            "Outstanding quality! Highly recommend this service provider.",
            "Average service. Got the job done but nothing special.",
            "Not satisfied with the quality. Had to request fixes.",
            "Amazing work! Exceeded my expectations in every way.",
            "Professional and courteous. Will definitely hire again.",
            "The service was okay, but communication could be better.",
            "Fantastic results! Very skilled and detail-oriented.",
            "Decent work but overpriced for what was delivered.",
        ];

        $bestFeatures = [
            "Punctuality and professionalism",
            "Attention to detail",
            "Quick response time",
            "Quality of work",
            "Fair pricing",
            "Excellent communication",
            "Clean and organized",
            "Problem-solving skills",
        ];

        // Get completed jobs
        $completedJobs = Job::where('status', 'completed')
            ->with(['user', 'provider'])
            ->get();

        if ($completedJobs->isEmpty()) {
            $this->command->warn('No completed jobs found. Please create some jobs first.');
            return;
        }

        foreach ($completedJobs as $job) {
            // Skip if review already exists
            if (Review::where('job_id', $job->id)->exists()) {
                continue;
            }

            $rating = rand(1, 5);
            
            Review::create([
                'job_id' => $job->id,
                'user_id' => $job->user_id,
                'provider_id' => $job->provider_id,
                'rating' => $rating,
                'feedback' => $feedbacks[array_rand($feedbacks)],
                'service_quality_rating' => rand(1, 5),
                'service_quality_comment' => 'The service quality was ' . ($rating >= 4 ? 'excellent' : 'acceptable'),
                'performance_rating' => rand(1, 5),
                'performance_comment' => 'Performance was ' . ($rating >= 4 ? 'outstanding' : 'adequate'),
                'contractor_service_rating' => rand(1, 5),
                'response_time_rating' => rand(1, 5),
                'best_feature' => $bestFeatures[array_rand($bestFeatures)],
                'show_username' => rand(0, 1) ? true : false,
                'helpful_count' => rand(0, 20),
                'status' => 'approved',
                'created_at' => now()->subDays(rand(1, 90)),
            ]);
        }

        $this->command->info('Reviews seeded successfully!');
    }
}
