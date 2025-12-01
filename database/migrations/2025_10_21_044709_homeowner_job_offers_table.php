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
        // Job Posting Table
        Schema::create('homeowner_job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homeowner_id')->constrained('homeowners')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->enum('job_type', ['standard', 'urgent', 'recurrent'])->default('standard');
            $table->date('preferred_date')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('title');
            $table->enum('job_size', ['small', 'medium', 'large'])->default('small');
            $table->text('description')->nullable();
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['pending', 'open', 'in_progress', 'completed', 'cancelled', 'expired'])->default('open');
            $table->timestamps();
        });
        
        // Pivot table for selected services (multi-select)
        Schema::create('homeowner_job_offer_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained('homeowner_job_offers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
        });

        // Photos uploaded for the job offer
        Schema::create('job_offer_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained('homeowner_job_offers')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name')->nullable(); 
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        // // Tradies who applied to this job (Future pa to/handled by another)
        // Schema::create('job_offer_applications', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('job_offer_id')->constrained('homeowner_job_offers')->cascadeOnDelete();
        //     $table->foreignId('tradie_id')->constrained('tradies')->cascadeOnDelete();
        //     $table->enum('status', ['applied', 'accepted', 'rejected'])->default('applied');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('job_offer_applications');
        Schema::dropIfExists('job_offer_photos');
        Schema::dropIfExists('homeowner_job_offer_services');
        Schema::dropIfExists('homeowner_job_offers');
    }
};
