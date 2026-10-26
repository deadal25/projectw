<?php

namespace App\Exports\Sheets;

use App\Models\Group;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WolaSummarySheet implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $groupId;

    public function __construct($groupId = null)
    {
        $this->groupId = $groupId;
    }

    public function title(): string
    {
        return 'Ringkasan Progres Kelompok';
    }

    public function collection()
    {
        $query = Group::with(['leader', 'assessors', 'jobs', 'groupEmployees'])->orderBy('number');

        if ($this->groupId && $this->groupId !== 'all' && is_numeric($this->groupId)) {
            $query->where('id', (int)$this->groupId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Grup',
            'Nama Area Kelompok',
            'Ketua Kelompok',
            'Jumlah Penilai',
            'Jumlah Jabatan',
            'Jumlah Target Karyawan',
            'Target Total (Unit)',
            'Realisasi Checklist (Unit)',
            'Capaian Progres (%)',
            'Status Penilaian',
        ];
    }

    public function map($group): array
    {
        $prog = $group->getProgressData();
        return [
            'G' . $group->number,
            $group->name,
            $group->leader ? $group->leader->name : 'Belum ditetapkan',
            $prog['assessor_count'],
            $prog['job_count'],
            $prog['employee_count'],
            $prog['target'],
            $prog['checked'],
            $prog['percentage'] . '%',
            $prog['status'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'], // Musashi Corporate Navy
                ],
            ],
        ];
    }
}
