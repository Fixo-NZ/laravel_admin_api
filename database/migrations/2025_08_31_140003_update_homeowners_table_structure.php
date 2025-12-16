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
        Schema::table('homeowners', function (Blueprint $table) {
            // Add new columns
            $table->string('first_name')->after('firebase_uid');
            $table->string('last_name')->after('first_name');
            $table->string('middle_name')->nullable()->after('last_name');
        });

        // Migrate existing data from 'name' to 'first_name' and 'last_name'
        DB::statement("
            UPDATE homeowners 
            SET 
                first_name = SUBSTRING_INDEX(name, ' ', 1),
                last_name = CASE 
                    WHEN LOCATE(' ', name) > 0 THEN SUBSTRING(name, LOCATE(' ', name) + 1)
                    ELSE ''
                END
            WHERE name IS NOT NULL
        ");

        Schema::table('homeowners', function (Blueprint $table) {
            // Drop the old 'name' column
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homeowners', function (Blueprint $table) {
            // Add back the 'name' column
            $table->string('name')->after('firebase_uid');
        });

        // Migrate data back from first_name and last_name to name
        DB::statement("
            UPDATE homeowners 
            SET name = CONCAT(first_name, ' ', last_name)
            WHERE first_name IS NOT NULL
        ");

        Schema::table('homeowners', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn(['first_name', 'last_name', 'middle_name']);
        });
    }
};