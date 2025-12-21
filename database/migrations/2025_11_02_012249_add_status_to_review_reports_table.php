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
        Schema::table('review_reports', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('review_reports', 'status')) {
                $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])
                    ->default('pending')
                    ->after('description');
            }
            
            // Add admin_notes column if it doesn't exist
            if (!Schema::hasColumn('review_reports', 'admin_notes')) {
                $table->text('admin_notes')
                    ->nullable()
                    ->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_reports', function (Blueprint $table) {
            $table->dropColumn(['status', 'admin_notes']);
        });
    }
};