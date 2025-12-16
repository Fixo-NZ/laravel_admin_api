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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_thread_id')->nullable(); // Firebase Realtime DB thread ID
            $table->string('firebase_message_id')->nullable(); // Firebase message ID (msg_1, msg_2, etc)
            $table->unsignedBigInteger('chat_id')->nullable(); // Laravel chat ID
            $table->unsignedBigInteger('sender_id'); // Laravel sender ID
            $table->unsignedBigInteger('receiver_id'); // Laravel receiver ID
            $table->enum('sender_type', ['homeowner', 'tradie']);
            $table->enum('receiver_type', ['homeowner', 'tradie']);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable(); // For future extensions (attachments, etc.)
            $table->timestamps();

            // Foreign keys (nullable for flexibility)
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('set null');

            // Indexes
            $table->index(['firebase_thread_id', 'sent_at']);
            $table->index(['sender_id', 'sender_type']);
            $table->index(['receiver_id', 'receiver_type']);
            $table->index('firebase_message_id');
            $table->index('sent_at');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};