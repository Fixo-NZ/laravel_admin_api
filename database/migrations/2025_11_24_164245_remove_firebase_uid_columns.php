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
        // Remove firebase_uid from homeowners table
        if (Schema::hasColumn('homeowners', 'firebase_uid')) {
            Schema::table('homeowners', function (Blueprint $table) {
                $table->dropIndex(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            });
        }

        // Remove firebase_uid from tradies table
        if (Schema::hasColumn('tradies', 'firebase_uid')) {
            Schema::table('tradies', function (Blueprint $table) {
                $table->dropIndex(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            });
        }

        // Remove firebase_uid from users table
        if (Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add firebase_uid back to homeowners table
        Schema::table('homeowners', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->index('firebase_uid');
        });

        // Add firebase_uid back to tradies table
        Schema::table('tradies', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->index('firebase_uid');
        });

        // Add firebase_uid back to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->index('firebase_uid');
        });
    }
};
