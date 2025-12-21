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
        Schema::create('review_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->foreignId('tradie_id')->constrained('tradies')->onDelete('cascade');
            $table->text('content');
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'tradie_id']); 
            $table->index('review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_responses');
    }
};
