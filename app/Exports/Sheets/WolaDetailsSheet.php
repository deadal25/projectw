<?php

namespace App\Exports\Sheets;

use App\Models\Assessment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WolaDetailsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $groupId;
    protected $jobId;
    protected $assessorId;
    protected $employeeId;

    public function __construct($groupId = null, $jobId = null, $assessorId = null, $employeeId = null)
    {
        $this->groupId = $groupId;
        $this->jobId = $jobId;
        $this->assessorId = $assessorId;
        $this->employeeId = $employeeId;
    }

    public function title(): string
    {
        return 'Detail Siapa Menilai Siapa';
    }

    public function collection()
    {
        $query = Assessment::with([
            'group',
            'assessor.employee',
            'job',
            'employee',
            'creator'
        ])->orderBy('group_id')->orderBy('job_id')->orderBy('employee_id');

        if ($this->groupId && $this->groupId !== 'all' && is_numeric($this->groupId)) {
            $query->where('group_id', (int)$this->groupId);
        }
        if ($this->jobId && $this->jobId !== 'all') {
            $query->where('job_id', $this->jobId);
        }
        if ($this->assessorId) {
            $query->where('assessor_id', $this->assessorId);
        }
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Waktu Ceklis (WIB)',
            'Area Kelompok',
            'NRP Penilai',
            'Nama Penilai',
            'NRP Karyawan Target',
            'Nama Karyawan Target',
            'Departemen Karyawan',
            'Kode Jabatan',
            'Nama Jabatan Yang Dinilai',
            'Dicatat Oleh Akun',
        ];
    }

    public function map($assessment): array
    {
        $penilaiName = $assessment->assessor && $assessment->assessor->employee 
            ? $assessment->assessor->employee->fullname 
            : 'Penilai';
        $penilaiNrp = $assessment->assessor && $assessment->assessor->employee 
            ? $assessment->assessor->employee->nrp 
            : '-';

        return [
            $assessment->checked_at ? $assessment->checked_at->format('d/m/Y H:i:s') : '-',
            $assessment->group ? 'G' . $assessment->group->number . ': ' . $assessment->group->name : '-',
            $penilaiNrp,
            $penilaiName,
            $assessment->employee ? $assessment->employee->nrp : '-',
            $assessment->employee ? $assessment->employee->fullname : '-',
            $assessment->employee ? $assessment->employee->dept : '-',
            $assessment->job ? $assessment->job->code : '-',
            $assessment->job ? $assessment->job->name : '-',
            $assessment->creator ? $assessment->creator->name : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF047857'], // Musashi Emerald Header
                ],
            ],
        ];
    }
}
