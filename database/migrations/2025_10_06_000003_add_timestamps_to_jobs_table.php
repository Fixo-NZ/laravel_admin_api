<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Duplicate columns, already present in jobs table migration. No action needed.
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->timestamps();
        // });
    }

    public function down()
    {
        // Duplicate columns, already present in jobs table migration. No action needed.
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->dropColumn(['created_at', 'updated_at']);
        // });
    }
};
