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
        Schema::create('tradie_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tradie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('homeowner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'approved', 'dismissed'])->default('pending');
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tradie_complaints');
    }
};
