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
        Schema::create('tradies', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('business_name')->nullable();
            $table->string('license_number')->nullable();
            $table->text('insurance_details')->nullable();
            $table->integer('years_experience')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->enum('availability_status', ['available', 'busy', 'unavailable'])->default('available');
            $table->integer('service_radius')->default(50);
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['latitude', 'longitude']);
            $table->index(['city', 'region']);
            $table->index(['availability_status', 'verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tradies');
    }
};
