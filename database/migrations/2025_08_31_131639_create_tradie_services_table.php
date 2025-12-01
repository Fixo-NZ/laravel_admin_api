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
        Schema::create('tradie_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id'); // foreign key
            $table->unsignedBigInteger('tradie_id');  // foreign key
            $table->timestamps();

            $table->foreign('service_id')
                ->references('id') // match services.id
                ->on('services')
                ->onDelete('cascade');

            $table->foreign('tradie_id')
                ->references('id') // match tradies.id
                ->on('tradies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tradie_services');
    }
};
