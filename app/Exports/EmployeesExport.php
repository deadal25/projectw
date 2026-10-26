<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    protected $areaWla;
    protected $division;
    protected $dept;
    protected $section;
    protected $plant;

    public function __construct($search = null, $dept = null, $section = null, $plant = null, $areaWla = null, $division = null)
    {
        $this->search = $search;
        $this->dept = $dept;
        $this->section = $section;
        $this->plant = $plant;
        $this->areaWla = $areaWla;
        $this->division = $division;
    }

    public function collection()
    {
        $query = Employee::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nrp', 'like', "%{$this->search}%")
                  ->orWhere('fullname', 'like', "%{$this->search}%");
            });
        }

        if ($this->areaWla) {
            $query->where('area_wla', $this->areaWla);
        }

        if ($this->division) {
            $query->where('division', $this->division);
        }

        if ($this->dept) {
            $query->where('dept', $this->dept);
        }

        if ($this->section) {
            $query->where('section', $this->section);
        }

        if ($this->plant) {
            $query->where('plant', $this->plant);
        }

        return $query->orderBy('nrp')->get();
    }

    public function headings(): array
    {
        return [
            'AREA WLA',
            'EMPLOYEENO',
            'FULLNAME',
            'DIVISION',
            'DEPT',
            'PLANT',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->area_wla ?: ($employee->groupEmployees->first()?->group?->name ?? 'Belum Ditugaskan'),
            $employee->nrp,
            $employee->fullname,
            $employee->division ?: $employee->section,
            $employee->dept,
            $employee->plant,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'], // Corporate Blue Navy
                ],
            ],
        ];
    }
}
