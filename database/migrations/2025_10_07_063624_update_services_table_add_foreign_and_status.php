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
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropColumn('category');
            $table->dropColumn('is_active');

            $table->foreignId('category_id')
                ->after('description')
                ->constrained('service_categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active')
                ->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropColumn('status');
            
            $table->string('category')->after('description');
            $table->boolean('is_active')->default(true)->after('category');

            $table->index(['category', 'is_active']);
        });
    }
};
