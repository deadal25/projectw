<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Group;
use App\Models\Job;
use App\Models\User;
use App\Models\Assessment;
use App\Models\GroupAssessor;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FullDatabaseExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new class implements FromArray, WithTitle, WithHeadings, ShouldAutoSize {
                public function title(): string { return 'Karyawan'; }
                public function headings(): array {
                    return ['NRP', 'Nama_Lengkap', 'Departemen', 'Divisi', 'Plant', 'Area_WLA', 'ID_Kelompok'];
                }
                public function array(): array {
                    $groups = Group::all()->keyBy('name');
                    return Employee::orderBy('nrp')->get()->map(function($e) use ($groups) {
                        $grpId = '';
                        if (!empty($e->area_wla) && isset($groups[$e->area_wla])) {
                            $grpId = $groups[$e->area_wla]->number;
                        }
                        return [
                            'nrp' => $e->nrp,
                            'fullname' => $e->fullname,
                            'dept' => $e->dept,
                            'division' => $e->division,
                            'plant' => $e->plant,
                            'area_wla' => $e->area_wla,
                            'id_kelompok' => $grpId,
                        ];
                    })->toArray();
                }
            },
            new class implements FromArray, WithTitle, WithHeadings, ShouldAutoSize {
                public function title(): string { return 'Kelompok'; }
                public function headings(): array {
                    return ['ID_Kelompok', 'Nama_Kelompok', 'Plant'];
                }
                public function array(): array {
                    return Group::orderBy('number')->get()->map(function($g) {
                        return [
                            'number' => $g->number,
                            'name' => $g->name,
                            'plant' => $g->plant,
                        ];
                    })->toArray();
                }
            },
            new class implements FromArray, WithTitle, WithHeadings, ShouldAutoSize {
                public function title(): string { return 'tim_penilai'; }
                public function headings(): array {
                    return ['ID_Kelompok', 'Nama_Kelompok', 'NRP_Penilai', 'Nama_Penilai', 'Peran', 'Departemen', 'Plant', 'Total_Penilaian_Dibuat'];
                }
                public function array(): array {
                    return GroupAssessor::with(['group', 'employee', 'assessments'])->get()
                        ->sortBy(function($a) {
                            return ($a->group ? $a->group->number : 999) * 100 + ($a->is_leader ? 0 : 1);
                        })
                        ->map(function($a) {
                            return [
                                'group_number' => $a->group ? $a->group->number : '-',
                                'group_name' => $a->group ? $a->group->name : '-',
                                'nrp' => $a->employee ? $a->employee->nrp : '-',
                                'fullname' => $a->employee ? $a->employee->fullname : 'Penilai',
                                'role' => $a->is_leader ? 'Ketua Kelompok' : 'Anggota Penilai',
                                'dept' => $a->employee ? $a->employee->dept : '-',
                                'plant' => $a->group ? $a->group->plant : ($a->employee ? $a->employee->plant : '-'),
                                'total_assessments' => $a->assessments->count(),
                            ];
                        })->values()->toArray();
                }
            },
            new class implements FromArray, WithTitle, WithHeadings, ShouldAutoSize {
                public function title(): string { return 'Posisi_Job'; }
                public function headings(): array {
                    return ['ID_Kelompok', 'Kode_Job', 'Nama_Job', 'Target_Kuota'];
                }
                public function array(): array {
                    return Job::with('group')->orderBy('group_id')->orderBy('code')->get()->map(function($j) {
                        return [
                            'group_number' => $j->group ? $j->group->number : 1,
                            'code' => $j->code,
                            'name' => $j->name,
                            'target_kuota' => $j->target_employees,
                        ];
                    })->toArray();
                }
            },
            new class implements FromArray, WithTitle, WithHeadings, ShouldAutoSize {
                public function title(): string { return 'Pengguna'; }
                public function headings(): array {
                    return ['Nama', 'Username', 'Email', 'Role', 'Kelompok', 'ID_Kelompok'];
                }
                public function array(): array {
                    return User::with(['roles', 'group'])->get()->map(function($u) {
                        return [
                            'name' => $u->name,
                            'username' => $u->username,
                            'email' => $u->email,
                            'role' => $u->roles->pluck('name')->implode(', '),
                            'group' => $u->group ? "Kelompok {$u->group->number} ({$u->group->name})" : 'Semua Area',
                            'id_kelompok' => $u->group ? $u->group->number : '',
                        ];
                    })->toArray();
                }
            },
            new class implements FromArray, WithTitle, WithHeadings, ShouldAutoSize {
                public function title(): string { return 'Hasil_Penilaian'; }
                public function headings(): array {
                    return ['Tanggal_Waktu', 'ID_Kelompok', 'NRP_Penilai', 'Penilai', 'NRP_Dinilai', 'Karyawan_Dinilai', 'Posisi_Job', 'Status', 'Catatan'];
                }
                public function array(): array {
                    return Assessment::with(['group', 'assessor.employee', 'employee', 'job'])
                        ->orderBy('assessed_at', 'desc')
                        ->get()
                        ->map(function($a) {
                            return [
                                'assessed_at' => $a->assessed_at ? $a->assessed_at->format('Y-m-d H:i') : '',
                                'group_number' => $a->group ? $a->group->number : '-',
                                'nrp_penilai' => $a->assessor?->employee?->nrp ?? '-',
                                'assessor' => $a->assessor?->employee?->fullname ?? 'Ketua/Penilai',
                                'nrp_dinilai' => $a->employee?->nrp ?? '-',
                                'employee' => $a->employee?->fullname ?? '-',
                                'job' => $a->job?->name ?? '-',
                                'status' => $a->is_checked ? 'Dinilai' : 'Belum',
                                'notes' => $a->notes ?? '-',
                            ];
                        })->toArray();
                }
            },
        ];
    }
}
