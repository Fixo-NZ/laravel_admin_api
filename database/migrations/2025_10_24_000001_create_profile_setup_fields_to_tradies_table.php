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
       Schema::table('tradies', function (Blueprint $table) {
           // Skills & availability
           $table->json('skills')->nullable()->after('years_experience'); // ["Painting", "Electrical", ...]
           $table->json('days')->nullable()->after('skills'); // ["Mon", "Tue", "Wed"]
           $table->time('start_time')->nullable()->after('days');
           $table->time('end_time')->nullable()->after('start_time');
           $table->boolean('emergency_available')->default(false)->after('end_time');


           // Portfolio & working hours
           $table->json('portfolio_images')->nullable()->after('avatar');
           $table->json('working_hours')->nullable()->after('portfolio_images');


           // Rates & charges
           $table->enum('charge_type', ['hourly', 'fixed', 'both'])->nullable()->after('working_hours');
           $table->text('description')->nullable()->after('charge_type');
           $table->boolean('after_hours')->default(false)->after('description');
           $table->boolean('call_out_fee')->default(false)->after('after_hours');


           // Profile completion status
           $table->boolean('profile_completed')->default(false)->after('call_out_fee');
           $table->timestamp('profile_completed_at')->nullable()->after('profile_completed');
       });
   }


   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
       Schema::table('tradies', function (Blueprint $table) {
           $table->dropColumn([
               'skills',
               'days',
               'start_time',
               'end_time',
               'emergency_available',
               'portfolio_images',
               'working_hours',
               'charge_type',
               'description',
               'after_hours',
               'call_out_fee',
               'profile_completed',
               'profile_completed_at',
           ]);
       });
   }
};


