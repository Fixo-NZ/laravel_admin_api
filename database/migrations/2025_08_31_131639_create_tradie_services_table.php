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
<<<<<<< HEAD
            $table->unsignedBigInteger('service_id'); // foreign key
            $table->unsignedBigInteger('tradie_id');  // foreign key
=======
            $table->foreignId('tradie_id')->constrained('tradies')->onDelete('cascade');
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->references('job_id')->on('services')->onDelete('cascade');
            $table->decimal('base_rate', 8, 2)->nullable();
>>>>>>> 24172d873ef38a8fa72e08a82046ccf88c100ee2
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
