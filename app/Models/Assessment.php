<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'assessor_id',
        'job_id',
        'employee_id',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function assessor()
    {
        return $this->belongsTo(GroupAssessor::class, 'assessor_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
