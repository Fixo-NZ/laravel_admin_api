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
            // Drop old columns
            $table->dropIndex(['category', 'is_active']);

            $table->dropColumn('category');
            $table->dropColumn('is_active');

            // Add new ones
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
            // Drop the new columns
            $table->dropForeign(['category_id']); // drop FK constraint first
            $table->dropColumn('category_id');
            $table->dropColumn('status');

            // Recreate the original columns
            $table->string('category')->after('description');
            $table->boolean('is_active')->default(true)->after('category');

            // Optional: re-add index if you need it for rollback consistency
            $table->index(['category', 'is_active']);
        });
    }
};
