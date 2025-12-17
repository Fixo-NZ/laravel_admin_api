<?php

// In database/migrations/2025_11_26_143025_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('homeowner_id')->constrained('homeowners')->onDelete('cascade');
            
            // This ensures the FK matches the BIGINT type of the bookings table ID
            $table->foreignId('booking_id')
                  ->nullable()
                  ->constrained('bookings')
                  ->onDelete('set null'); // Uses foreignId helper

            $table->string('customer_id')->nullable();
            $table->string('payment_method_id')->nullable();

            // Transaction Details
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('AUD');
            $table->string('status')->index(); // e.g., succeeded, requires_action
            
            // Card details (The columns your seeder is looking for)
            $table->string('card_brand')->nullable();
            $table->string('card_last4number', 4)->nullable();
            
            // 🛑 CRITICAL FIX: Add these two columns
            $table->string('exp_month', 2)->nullable();
            $table->string('exp_year', 4)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

