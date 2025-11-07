<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('homeowner_id')->constrained('homeowners')->onDelete('cascade'); // Customer who left review
            $table->foreignId('tradie_id')->constrained('tradies')->onDelete('cascade'); // Service provider
            
            // Rating (1-5 stars)
            $table->tinyInteger('rating')->unsigned();
            
            // Feedback text
            $table->text('feedback')->nullable();
            
            // Additional rating categories
            $table->tinyInteger('service_quality_rating')->unsigned()->nullable();
            $table->text('service_quality_comment')->nullable();
            
            $table->tinyInteger('performance_rating')->unsigned()->nullable();
            $table->text('performance_comment')->nullable();
            
            $table->tinyInteger('contractor_service_rating')->unsigned()->nullable();
            $table->tinyInteger('response_time_rating')->unsigned()->nullable();
            
            $table->string('best_feature')->nullable();
            
            // Images (JSON array)
            $table->json('images')->nullable();
            
            // Show username on review
            $table->boolean('show_username')->default(true);
            
            // Helpful counter
            $table->integer('helpful_count')->default(0);
            
            // Status
            $table->enum('status', ['pending', 'approved', 'reported', 'hidden'])->default('approved');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('tradie_id');
            $table->index('homeowner_id');
            $table->index('job_id');
            $table->index('rating');
            $table->index('status');
            
            // Unique constraint: one review per job per homeowner
            $table->unique(['job_id', 'homeowner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};