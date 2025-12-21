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
        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            
            // Reporter can be either homeowner or tradie
            $table->string('reporter_type'); // 'homeowner' or 'tradie'
            $table->unsignedBigInteger('reporter_id');
            
            $table->enum('reason', [
                'spam',
                'offensive',
                'inappropriate',
                'fake',
                'other'
            ]);
            $table->text('description')->nullable();
            
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('review_id');
            $table->index(['reporter_type', 'reporter_id']);
            $table->index('status');
            
            // Unique constraint: one report per review per user
            $table->unique(['review_id', 'reporter_type', 'reporter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_reports');
    }
};
