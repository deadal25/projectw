<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'employee_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
