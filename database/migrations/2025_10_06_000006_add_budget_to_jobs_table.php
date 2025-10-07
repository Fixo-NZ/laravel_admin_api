<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('budget_min', 8, 2)->nullable();
            $table->decimal('budget_max', 8, 2)->nullable()->after('budget_min');
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['budget_min', 'budget_max']);
        });
    }
};
