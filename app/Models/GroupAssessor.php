<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAssessor extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'employee_id',
        'is_leader',
    ];

    protected function casts(): array
    {
        return [
            'is_leader' => 'boolean',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'assessor_id');
    }

    public function comments()
    {
        return $this->hasMany(JobComment::class, 'assessor_id');
    }

    /**
     * Hitung progres penilaian untuk penilai spesifik ini
     */
    public function getProgressData(): array
    {
        $group = $this->group;
        $activeJobs = $group ? $group->jobs()->where('is_active', true)->get() : collect();
        $employeeCount = $group ? $group->groupEmployees()->count() : 0;

        $hasSpecificTargets = $activeJobs->contains(fn($j) => $j->target_employees !== null);
        if ($hasSpecificTargets) {
            $target = $activeJobs->sum(function($j) use ($employeeCount) {
                return $j->target_employees !== null ? (int)$j->target_employees : $employeeCount;
            });
        } else {
            $target = $activeJobs->count() * $employeeCount;
        }

        if ($group && $group->custom_target !== null) {
            $assessorCount = max(1, $group->assessors()->count());
            $target = (int) round($group->custom_target / $assessorCount);
        }

        $checked = $this->assessments()->count();

        $percentage = $target > 0 ? min(round(($checked / $target) * 100, 1), 100) : 0;

        return [
            'target' => $target,
            'checked' => $checked,
            'percentage' => $percentage,
        ];
    }
}
