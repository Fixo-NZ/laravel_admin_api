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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homeowner_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('payment_method_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('usd');
            $table->string('status')->default('pending');
            $table->text('card_brand')->nullable();
            $table->text('card_last4number', 4)->nullable();
            $table->text('exp_month')->nullable();
            $table->text('exp_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};


