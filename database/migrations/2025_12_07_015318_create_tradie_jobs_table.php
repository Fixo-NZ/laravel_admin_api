<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tradie_jobs', function (Blueprint $table) {
            $table->id();

            // Link to the original homeowner job request (if applicable)
            $table->foreignId('job_request_id')
                  ->nullable()
                  ->constrained('job_requests')
                  ->nullOnDelete();

            // Assigned tradie
            $table->foreignId('tradie_id')
                  ->nullable()
                  ->constrained('tradies')
                  ->nullOnDelete();

            // Optional service if you want direct link
            $table->foreignId('service_id')
                  ->nullable()
                  ->constrained('services')
                  ->nullOnDelete();

            // Status of the job
            $table->enum('status', ['pending','accepted','in_progress','completed','cancelled'])
                  ->default('pending');

            // Financials
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('agreed_rate', 10, 2)->nullable();

            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Location (if job_requests doesn’t provide)
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();

            // Useful indexes
            $table->index(['tradie_id', 'status']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tradie_jobs');
    }
};