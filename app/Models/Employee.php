<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_wla',
        'nrp',
        'fullname',
        'division',
        'dept',
        'section',
        'plant',
        'avatar',
    ];

    /**
     * Alias EMPLOYEENO untuk kompatibilitas data WOLA Excel
     */
    public function getEmployeenoAttribute()
    {
        return $this->nrp;
    }

    /**
     * Akun user yang terhubung ke karyawan ini (jika ada)
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Posisi sebagai penilai di kelompok
     */
    public function groupAssessors()
    {
        return $this->hasMany(GroupAssessor::class);
    }

    /**
     * Posisi sebagai karyawan yang dinilai di kelompok
     */
    public function groupEmployees()
    {
        return $this->hasMany(GroupEmployee::class);
    }

    /**
     * Penilaian di mana karyawan ini dinilai
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'employee_id');
    }
}
