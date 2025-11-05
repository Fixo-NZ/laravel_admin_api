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
        Schema::table('tradies', function (Blueprint $table) {
            $table->text('suspension_reason')->nullable();
            $table->timestamp('suspension_start')->nullable();
            $table->timestamp('suspension_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->dropColumn(['suspension_reason', 'suspension_start', 'suspension_end']);
        });
    }
};
