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
            $table->bigIncrements('job_id');
            $table->unsignedBigInteger('homeowner_id');
            $table->unsignedBigInteger('job_categoryid');
            $table->text('job_description');
            $table->string('location', 255);
            $table->enum('status', ['Pending', 'InProgress', 'Completed', 'Cancelled']);
            $table->dateTime('createdAt');
            $table->dateTime('updatedAt');
            $table->tinyInteger('rating')->nullable();

            $table->foreign('homeowner_id')->references('id')->on('homeowners')->onDelete('cascade');
            $table->foreign('job_categoryid')->references('id')->on('categories')->onDelete('cascade');
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
