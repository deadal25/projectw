<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'threshold_lagging',
        'custom_target',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'threshold_lagging' => 'integer',
            'custom_target' => 'integer',
        ];
    }

    /**
     * Akun Ketua kelompok ini
     */
    public function leader()
    {
        return $this->hasOne(User::class, 'group_id');
    }

    /**
     * Daftar Penilai di kelompok ini (termasuk Ketua & anggota)
     */
    public function assessors()
    {
        return $this->hasMany(GroupAssessor::class);
    }

    /**
     * Pivot karyawan yang dinilai
     */
    public function groupEmployees()
    {
        return $this->hasMany(GroupEmployee::class);
    }

    /**
     * Karyawan yang dinilai dalam kelompok ini
     */
    public function targetEmployees()
    {
        return $this->belongsToMany(Employee::class, 'group_employees', 'group_id', 'employee_id')
            ->withTimestamps();
    }

    /**
     * Daftar Jabatan di kelompok ini
     */
    public function jobs()
    {
        return $this->hasMany(Job::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Daftar Jabatan yang AKTIF dinilai pada periode ini
     */
    public function activeJobs()
    {
        return $this->hasMany(Job::class)->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Seluruh data penilaian di kelompok ini
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function jobComments()
    {
        return $this->hasMany(JobComment::class);
    }

    /**
     * Hitung progres kelompok secara dinamis & zero-safe
     */
    public function getProgressData(): array
    {
        $assessorCount = $this->assessors()->count();
        // Hanya hitung jabatan yang aktif dinilai pada kelompok ini
        $activeJobs = $this->jobs()->where('is_active', true)->get();
        $activeJobCount = $activeJobs->count();
        $totalJobCount = $this->jobs()->count();
        $employeeCount = $this->groupEmployees()->count();

        // Cek apakah ada jabatan aktif yang telah disetel target orangnya
        $hasSpecificTargets = $activeJobs->contains(fn($j) => $j->target_employees !== null);

        if ($hasSpecificTargets) {
            // Jumlahkan target orang dari masing-masing jabatan aktif
            $totalTargetHeadcount = $activeJobs->sum(function($j) use ($employeeCount) {
                return $j->target_employees !== null ? (int)$j->target_employees : $employeeCount;
            });
            $formulaTarget = $totalTargetHeadcount;
        } else {
            $totalTargetHeadcount = $activeJobCount * $employeeCount;
            $formulaTarget = $totalTargetHeadcount;
        }

        // Jika ada target kustom manual, gunakan custom_target; jika tidak, gunakan formulaTarget
        $target = $this->custom_target !== null ? $this->custom_target : $formulaTarget;
        $checked = $this->assessments()->count();
        $checklistPercentage = $target > 0 ? min(round(($checked / $target) * 100, 1), 100) : 0;

        // Hitung karyawan unik yang sudah dinilai (minimal dinilai 1 penilai dihitung 1 orang)
        $assessedEmployeesCount = $this->assessments()->pluck('employee_id')->unique()->count();
        $unassessedEmployeesCount = max(0, $employeeCount - $assessedEmployeesCount);

        // Capaian Progres Berbasis Karyawan Target (sama persis dengan Dashboard Ketua, misal 17/28 = 60.7%)
        $employeePercentage = $employeeCount > 0 
            ? min(round(($assessedEmployeesCount / $employeeCount) * 100, 1), 100) 
            : 0;

        // Persentase utama kelompok berbasis karyawan target (Permintaan user: 60.7% selesai)
        $percentage = $employeePercentage;

        // Tentukan Status: Belum Mulai (0), Tertinggal (<5), Berjalan (5..27), Selesai (full)
        if ($assessedEmployeesCount === 0 && $checked === 0) {
            $status = 'Belum Mulai';
            $statusColor = 'slate';
        } elseif ($percentage >= 100 || ($employeeCount > 0 && $assessedEmployeesCount >= $employeeCount)) {
            $status = 'Selesai';
            $statusColor = 'green';
        } elseif ($assessedEmployeesCount < 5) {
            $status = 'Tertinggal';
            $statusColor = 'red';
        } else {
            $status = 'Berjalan';
            $statusColor = 'blue';
        }

        return [
            'assessor_count' => $assessorCount,
            'job_count' => $activeJobCount,
            'total_jobs_count' => $totalJobCount,
            'employee_count' => $employeeCount,
            'total_target_headcount' => $totalTargetHeadcount,
            'has_specific_targets' => $hasSpecificTargets,
            'custom_target' => $this->custom_target,
            'is_custom_target' => ($this->custom_target !== null),
            'target' => $target,
            'checked' => $checked,
            'checklist_percentage' => $checklistPercentage,
            'assessed_employees_count' => $assessedEmployeesCount,
            'unassessed_employees_count' => $unassessedEmployeesCount,
            'employee_percentage' => $employeePercentage,
            'percentage' => $percentage,
            'status' => $status,
            'status_color' => $statusColor,
        ];
    }

    /**
     * Pemetaan resmi NRP master karyawan untuk para Ketua Kelompok 1 s.d. 12
     */
    public static array $officialLeaderNrps = [
        'Nanang Siswanto' => '622',
        'Agung Wahyu Wibowo' => '545',
        'Heri Eko Purnomo' => '548',
        'Joko Pramono' => '740',
        'Muhamad Setiyawan' => '683',
        'Joko Hartono' => '276',
        'Arif Dwi Saputro' => '620',
        'Zulianto' => '318',
        'Eko Prasetyo' => '671',
        'Ricky Apriliyanto' => '861',
        'Ricky Aprilianto' => '861',
        'Yayat Sutrisno' => '1390',
        'Sariyanto' => '624',
    ];

    public static array $groupLeaderNrps = [
        1 => '622',
        2 => '545',
        3 => '548',
        4 => '740',
        5 => '683',
        6 => '276',
        7 => '620',
        8 => '318',
        9 => '671',
        10 => '861',
        11 => '1390',
        12 => '624',
    ];

    /**
     * Pastikan akun Ketua (dari Manajemen Akun / User) terhubung otomatis sebagai Ketua Tim Penilai
     */
    public function syncLeaderAssessor(): ?GroupAssessor
    {
        $leaderUser = $this->leader;
        if (!$leaderUser) {
            return null;
        }

        $employee = null;

        // 1. Cek jika user sudah memiliki employee_id yang valid
        if ($leaderUser->employee_id) {
            $existing = Employee::find($leaderUser->employee_id);
            if ($existing) {
                $employee = $existing;
            }
        }

        // 2. Cek jika username akun adalah NRP karyawan yang valid
        if (!$employee && !empty($leaderUser->username) && ctype_digit($leaderUser->username)) {
            $employee = Employee::where('nrp', trim($leaderUser->username))->first();
        }

        // 3. Cek jika nama cocok di master data karyawan
        if (!$employee && !empty($leaderUser->name)) {
            $employee = Employee::whereRaw('LOWER(fullname) = LOWER(?)', [trim($leaderUser->name)])
                ->where('nrp', 'not like', 'K-%')
                ->first();
        }

        // 4. Fallback ke pemetaan resmi lama HANYA jika belum terdaftar
        if (!$employee) {
            $targetNrp = self::$officialLeaderNrps[$leaderUser->name] 
                ?? (self::$groupLeaderNrps[$this->number] ?? null);
            if ($targetNrp) {
                $employee = Employee::where('nrp', $targetNrp)->first();
            }
        }

        // 5. Fallback jika benar-benar belum terdaftar di database master karyawan
        if (!$employee) {
            $targetNrp = self::$officialLeaderNrps[$leaderUser->name] 
                ?? (self::$groupLeaderNrps[$this->number] ?? null);
            $nrp = (!empty($leaderUser->username) && ctype_digit($leaderUser->username)) 
                ? trim($leaderUser->username) 
                : ($targetNrp ?: ('K-' . str_pad((string)$this->number, 3, '0', STR_PAD_LEFT)));
            
            // Cek apakah employee dengan NRP ini sudah ada di database agar tidak terjadi UNIQUE constraint error
            $existingByNrp = Employee::where('nrp', $nrp)->first();
            if ($existingByNrp) {
                $employee = $existingByNrp;
                $employee->update(['fullname' => $leaderUser->name]);
            } else {
                $employee = Employee::create([
                    'area_wla' => $this->name,
                    'nrp' => $nrp,
                    'fullname' => $leaderUser->name,
                    'division' => $this->name,
                    'dept' => $this->name,
                    'section' => 'Ketua Kelompok',
                    'plant' => 'CIKARANG',
                ]);
            }
        }

        // Pastikan nama dan NRP employee selalu sinkron dengan User saat ini
        if ($employee) {
            $empUpdates = [];
            if ($employee->fullname !== $leaderUser->name) {
                $empUpdates['fullname'] = $leaderUser->name;
            }
            if (!empty($leaderUser->username) && ctype_digit($leaderUser->username) && $employee->nrp !== $leaderUser->username) {
                $conflict = Employee::where('nrp', $leaderUser->username)->where('id', '!=', $employee->id)->exists();
                if (!$conflict) {
                    $empUpdates['nrp'] = $leaderUser->username;
                }
            }
            if (!empty($empUpdates)) {
                $employee->update($empUpdates);
            }
        }

        // Pastikan User selalu terhubung ke Employee master asli ini
        if ($leaderUser->employee_id !== $employee->id) {
            $leaderUser->update(['employee_id' => $employee->id]);
        }

        // Hubungkan ke GroupAssessor
        $assessor = GroupAssessor::where('group_id', $this->id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$assessor) {
            GroupAssessor::where('group_id', $this->id)->update(['is_leader' => false]);
            $assessor = GroupAssessor::create([
                'group_id' => $this->id,
                'employee_id' => $employee->id,
                'is_leader' => true,
            ]);
        } else {
            if (!$assessor->is_leader) {
                GroupAssessor::where('group_id', $this->id)->update(['is_leader' => false]);
                $assessor->update(['is_leader' => true]);
            }
        }

        // Bersihkan assessor duplikat yang menunjuk ke dummy lama K- jika ketua saat ini sudah memakai karyawan master asli
        if ($employee && !str_starts_with($employee->nrp, 'K-')) {
            GroupAssessor::where('group_id', $this->id)
                ->where('id', '!=', $assessor->id)
                ->whereHas('employee', function ($q) {
                    $q->where('nrp', 'like', 'K-%');
                })
                ->delete();
        }

        return $assessor;
    }
}

