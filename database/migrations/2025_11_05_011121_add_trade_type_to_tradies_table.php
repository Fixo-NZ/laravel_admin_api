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
            $table->enum('trade_type', [
                'Electrical',
                'Painting',
                'Plumbing',
                'Carpentry',
                'Appliance Repair',
                'Fencing & Decking',
                'Pest Control',
                'Drywall & Plastering',
                'Window & Door',
                'HVAC',
                'Masonry',
                'Flooring',
                'Gardening',
                'Roofing',
            ])->nullable()->after('license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tradies', function (Blueprint $table) {
            $table->dropColumn('trade_type');
        });
    }
};
