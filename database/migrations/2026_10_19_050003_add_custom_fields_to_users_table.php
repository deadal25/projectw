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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id')->constrained('employees')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->after('employee_id')->constrained('groups')->nullOnDelete();
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->string('avatar')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn(['employee_id', 'group_id', 'username', 'avatar', 'is_active']);
        });
    }
};
