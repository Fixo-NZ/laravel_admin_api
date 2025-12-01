<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urgent_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('homeowner_id')->constrained('homeowners')->onDelete('cascade');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedBigInteger('tradie_id')->nullable();

            $table->string('status')->default('pending');
            $table->string('priority_level')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->text('notes')->nullable();
            $table->string('service_name')->nullable();
            $table->string('preferred_date')->nullable();
            $table->string('preferred_time_window')->nullable();

            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('address')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urgent_bookings');
    }
};


