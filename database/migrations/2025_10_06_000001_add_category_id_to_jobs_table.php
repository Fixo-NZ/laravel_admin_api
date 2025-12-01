<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Duplicate column, already present in jobs table migration. No action needed.
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->unsignedBigInteger('category_id')->nullable()->after('id');
        //     $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        // });
    }

    public function down()
    {
        // Duplicate column, already present in jobs table migration. No action needed.
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->dropForeign(['category_id']);
        //     $table->dropColumn('category_id');
        // });
    }
};
