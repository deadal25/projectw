<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'job_id',
        'assessor_id',
        'user_id',
        'employee_id',
        'comment',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function assessor()
    {
        return $this->belongsTo(GroupAssessor::class, 'assessor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
