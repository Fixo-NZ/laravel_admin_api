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
        // Add foreign key constraint after categories table exists
        Schema::table('services', function (Blueprint $table) {
            // Make job_categoryid required (not nullable)
            $table->unsignedBigInteger('job_categoryid')->nullable(false)->change();
            
            // Add foreign key constraint
            $table->foreign('job_categoryid')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['job_categoryid']);
        });
    }
};

