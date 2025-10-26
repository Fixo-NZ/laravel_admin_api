<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->enum('rate_type', ['hourly', 'fixed_price', 'both'])->nullable();
            $table->decimal('standard_rate', 10, 2)->nullable();
            $table->integer('minimum_hours')->nullable();
            $table->text('standard_rate_description')->nullable();
            $table->boolean('after_hours_enabled')->default(false);
            $table->decimal('after_hours_rate', 10, 2)->nullable();
            $table->boolean('call_out_fee_enabled')->default(false);
            $table->decimal('call_out_fee', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->dropColumn([
                'rate_type',
                'standard_rate',
                'minimum_hours',
                'standard_rate_description',
                'after_hours_enabled',
                'after_hours_rate',
                'call_out_fee_enabled',
                'call_out_fee'
            ]);
        });
    }
};