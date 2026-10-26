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
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedInteger('custom_target')->nullable()->after('threshold_lagging');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('custom_target');
        });
    }
};
