<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Duplicate columns, already present in jobs table migration. No action needed.
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->string('title')->nullable()->after('category_id');
        //     $table->text('description')->nullable()->after('title');
        //     $table->string('location')->nullable()->after('description');
        //     $table->decimal('latitude', 10, 6)->nullable()->after('location');
        //     $table->decimal('longitude', 10, 6)->nullable()->after('latitude');
        //     $table->string('status')->default('open')->after('longitude');
        // });
    }

    public function down()
    {
        // Duplicate columns, already present in jobs table migration. No action needed.
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->dropColumn(['title', 'description', 'location', 'latitude', 'longitude', 'status']);
        // });
    }
};
