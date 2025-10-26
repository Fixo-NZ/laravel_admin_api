<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->json('portfolio_images')->nullable()->after('avatar');
            $table->text('skills_bio')->nullable()->after('bio');
            $table->json('working_hours')->nullable();
            $table->boolean('emergency_available')->default(false);
            $table->json('availability_calendar')->nullable();
            $table->boolean('profile_completed')->default(false);
            $table->timestamp('profile_completed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->dropColumn([
                'portfolio_images',
                'skills_bio',
                'working_hours',
                'emergency_available',
                'availability_calendar',
                'profile_completed',
                'profile_completed_at'
            ]);
        });
    }
};