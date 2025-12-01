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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('homeowner_id');
            $table->unsignedBigInteger('job_category_id'); // matches new naming
            $table->text('job_description');
            $table->string('location', 255);
            $table->enum('status', ['Pending', 'InProgress', 'Completed', 'Cancelled']);
            $table->tinyInteger('rating')->nullable();
            $table->timestamps();

            $table->foreign('homeowner_id')->references('id')->on('homeowners')->onDelete('cascade');
            $table->foreign('job_category_id')->references('id')->on('job_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
