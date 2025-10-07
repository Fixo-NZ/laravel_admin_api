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
            $table->string('title');                      // Event title
            $table->text('description')->nullable();      // Optional description
            $table->dateTime('start_time');               // Start datetime
            $table->dateTime('end_time');                 // End datetime
            $table->string('color')->nullable();          // For frontend calendar color
            $table->string('status')->default('scheduled'); // scheduled | rescheduled | cancelled
            $table->timestamp('rescheduled_at')->nullable(); // When the schedule was rescheduled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
