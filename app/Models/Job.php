<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit untuk jabatan WOLA
     */
    protected $table = 'jobs';

    protected $fillable = [
        'group_id',
        'name',
        'sort_order',
        'is_active',
        'target_employees',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'target_employees' => 'integer',
        ];
    }

    /**
     * Kode posisi (J01 - J10)
     */
    public function getCodeAttribute(): string
    {
        return 'J' . str_pad((string)$this->sort_order, 2, '0', STR_PAD_LEFT);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function comments()
    {
        return $this->hasMany(JobComment::class);
    }

    /**
     * Hitung progres untuk jabatan ini
     */
    public function getProgressData(): array
    {
        $checked = $this->assessments()->count();

        if (!$this->is_active) {
            return [
                'target' => 0,
                'target_headcount' => 0,
                'checked' => $checked,
                'percentage' => 0,
                'is_completed' => false,
                'is_active' => false,
            ];
        }

        $group = $this->group;
        $assessorCount = $group ? $group->assessors()->count() : 0;
        $employeeCount = $group ? $group->groupEmployees()->count() : 0;

        // Gunakan target_employees spesifik jika telah diatur, atau default ke seluruh karyawan kelompok
        $headcount = ($this->target_employees !== null) ? (int)$this->target_employees : $employeeCount;

        $target = $assessorCount * $headcount;
        $percentage = $target > 0 ? round(($checked / $target) * 100, 1) : 0;

        return [
            'target' => $target,
            'target_headcount' => $headcount,
            'checked' => $checked,
            'percentage' => $percentage,
            'is_completed' => ($target > 0 && $checked >= $target),
            'is_active' => true,
        ];
    }
}
