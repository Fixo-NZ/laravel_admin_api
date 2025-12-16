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
        // Fix homeowners table - make last_name nullable
        Schema::table('homeowners', function (Blueprint $table) {
            $table->string('last_name')->nullable()->change();
        });

        // Fix tradies table - make last_name and middle_name nullable
        Schema::table('tradies', function (Blueprint $table) {
            $table->string('last_name')->nullable()->change();
            $table->string('middle_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homeowners', function (Blueprint $table) {
            $table->string('last_name')->nullable(false)->change();
        });

        Schema::table('tradies', function (Blueprint $table) {
            $table->string('last_name')->nullable(false)->change();
            $table->string('middle_name')->nullable(false)->change();
        });
    }
};