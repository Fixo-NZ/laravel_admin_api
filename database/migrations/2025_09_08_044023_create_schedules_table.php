<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homeowner_id')->constrained('homeowners')->onDelete('cascade'); // ✅ Foreign key
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('job_title')->nullable();
            $table->string('duration')->nullable();
            $table->date('date')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('color')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('rescheduled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
