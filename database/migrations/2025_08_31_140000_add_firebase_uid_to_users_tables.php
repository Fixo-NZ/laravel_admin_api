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
        // Add firebase_uid to homeowners table
        Schema::table('homeowners', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->index('firebase_uid');
        });

        // Add firebase_uid to tradies table
        Schema::table('tradies', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->index('firebase_uid');
        });

        // Add firebase_uid to users table (if needed for admin users)
        Schema::table('users', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->index('firebase_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homeowners', function (Blueprint $table) {
            $table->dropIndex(['firebase_uid']);
            $table->dropColumn('firebase_uid');
        });

        Schema::table('tradies', function (Blueprint $table) {
            $table->dropIndex(['firebase_uid']);
            $table->dropColumn('firebase_uid');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['firebase_uid']);
            $table->dropColumn('firebase_uid');
        });
    }
};