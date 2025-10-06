<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_requests', function (Blueprint $table) {
            $table->id();

            // Category of work (can be mapped to services or a separate category table)
            $table->foreignId('job_category_id')
                  ->constrained('job_categories')
                  ->cascadeOnDelete();

            // Homeowner who created the job request
            $table->foreignId('homeowner_id')
                  ->constrained('homeowners')
                  ->cascadeOnDelete();

            $table->string('title');               // Job title
            $table->text('description')->nullable();

            // Job type
            $table->enum('job_type', ['urgent', 'standard', 'recurring'])
                  ->default('standard');

            // Status of the job request
            $table->enum('status', ['pending','active','assigned','completed','cancelled'])
                  ->default('pending');

            // Financial & scheduling details
            $table->decimal('budget', 10, 2)->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // Location details
            $table->string('location')->nullable(); // e.g., street address
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['status','job_type']);
            $table->index(['latitude','longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_requests');
    }
};
