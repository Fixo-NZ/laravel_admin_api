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
        Schema::create('job_offer_applications', function (Blueprint $table) {
            $table->id();

            $table->string('number', 32)->unique(); //Unique application number

            $table->foreignId('job_offer_id')->constrained('homeowner_job_offers')->cascadeOnDelete();
            $table->foreignId('tradie_id')->constrained('tradies')->cascadeOnDelete();
            $table->enum('status', ['applied', 'accepted', 'rejected'])->default('applied');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offer_applications');
    }
};
