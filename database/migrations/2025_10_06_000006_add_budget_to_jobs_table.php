<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
<<<<<<< HEAD
<<<<<<< HEAD
            $table->decimal('budget_min', 8, 2)->nullable();
=======
            $table->decimal('budget_min', 8, 2)->nullable()->after('status');
>>>>>>> 8c80aa9 (Ready for QA testing!)
=======
            $table->decimal('budget_min', 8, 2)->nullable();
>>>>>>> bf01661 (Refracted jobs table to service table to be able to accompany with other groups. Adjusted unit testing and passed all.)
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
