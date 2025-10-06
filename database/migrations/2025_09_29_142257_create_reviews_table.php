<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('homeowner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tradie_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1–5 fits tinyint
            $table->text('feedback_text')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['tradie_id', 'job_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
