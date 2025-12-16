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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_thread_id')->nullable()->unique(); // Firebase Realtime DB thread ID
            $table->unsignedBigInteger('participant_1_id'); // Laravel ID (tradie or homeowner)
            $table->unsignedBigInteger('participant_2_id'); // Laravel ID (tradie or homeowner)
            $table->enum('participant_1_type', ['homeowner', 'tradie']);
            $table->enum('participant_2_type', ['homeowner', 'tradie']);
            $table->text('last_message')->nullable();
            $table->unsignedBigInteger('last_sender_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['participant_1_id', 'participant_1_type']);
            $table->index(['participant_2_id', 'participant_2_type']);
            $table->index('firebase_thread_id');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};