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
        Schema::create('job_offer_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained('homeowner_job_offers')->cascadeOnDelete();

            $table->string('file_path');
            $table->string('original_name')->nullable(); 
            $table->integer('file_size')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offer_photos');
    }
};
