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
        Schema::create('business_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('homeowners')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('tradies')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('pending');
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_jobs');
    }
};
