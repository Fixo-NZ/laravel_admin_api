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
            $table->foreignId('homeowner_id')->constrained('homeowners')->onDelete('cascade');
            // job_categoryid will be added in a later migration after categories table exists
            $table->unsignedBigInteger('job_categoryid')->nullable();
            $table->text('job_description');
            $table->string('location');
            $table->enum('status', ['Pending', 'InProgress', 'Completed', 'Cancelled'])->default('Pending');
            $table->integer('rating')->nullable();
            $table->timestamps();
            
            $table->index(['homeowner_id', 'status']);
            $table->index(['job_categoryid', 'status']);
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
