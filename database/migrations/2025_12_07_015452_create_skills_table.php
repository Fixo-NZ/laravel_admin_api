<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tradie_id');
            $table->string('skill_name');
            $table->timestamps();
            $table->foreign('tradie_id')->references('id')->on('tradies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('skills');
    }
};