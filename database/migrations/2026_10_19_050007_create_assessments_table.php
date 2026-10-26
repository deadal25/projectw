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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('assessor_id')->constrained('group_assessors')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();

            // Constraint unik sesuai spesifikasi: unique(assessor_id, job_id, employee_id)
            $table->unique(['assessor_id', 'job_id', 'employee_id'], 'unique_assessment_unit');

            // Indexing pendukung dashboard matriks & kalkulasi progres real-time
            $table->index(['group_id', 'assessor_id']);
            $table->index(['group_id', 'job_id']);
            $table->index(['group_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
