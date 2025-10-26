<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->string('professional_title')->nullable()->after('email');
            $table->text('professional_bio')->nullable()->after('professional_title');
        });
    }

    public function down()
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->dropColumn(['professional_title', 'professional_bio']);
        });
    }
};