<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

<<<<<<< HEAD
return new class extends Migration
{
=======
return new class extends Migration {
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->string('email')->unique();
            $table->string('role')->default('admin');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
<<<<<<< HEAD
            $table->rememberToken();
            $table->timestamps();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
=======
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->rememberToken();
            $table->timestamps();
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
