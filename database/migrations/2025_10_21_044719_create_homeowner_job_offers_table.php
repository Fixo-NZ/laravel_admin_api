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
        Schema::create('homeowner_job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homeowner_id')->constrained('homeowners')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();

            $table->string('number', 32)->unique(); //Unique job offer number

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('job_type', ['standard', 'urgent', 'recurrent'])->default('standard');
            $table->enum('job_size', ['small', 'medium', 'large'])->default('small');

            $table->decimal('budget', 10, 2)->nullable(); // Estimated budget posted by homeowner
            $table->decimal('final_budget', 10, 2)->nullable(); // Final agreed budget

            $table->date('preferred_date')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('status', ['open', 'assigned', 'in_progress', 'completed', 'cancelled', 'expired'])->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homeowner_job_offers');
    }
};
