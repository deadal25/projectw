<?php

namespace App\Exports;

use App\Services\DashboardMonitoringService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Legend;

class AdminDashboardExport implements WithMultipleSheets
{
    protected array $data;

    public function __construct(?array $data = null)
    {
        $this->data = $data ?? DashboardMonitoringService::getAdminMonitoringData();
    }

    public function sheets(): array
    {
        return [
            // =================================================================
            // SHEET 1: DASHBOARD (PIVOT TABLE & 3 CHARTS PERSIS DASHBOARD ADMIN)
            // =================================================================
            new class($this->data) implements FromArray, WithTitle, WithCharts, WithStyles, ShouldAutoSize, WithColumnFormatting {
                protected array $d;

                public function __construct(array $d) {
                    $this->d = $d;
                }

                public function title(): string {
                    return 'Dashboard';
                }

                public function columnFormats(): array {
                    return [
                        'D' => '#,##0',
                        'E' => '#,##0',
                        'F' => '0.0"%"',
                    ];
                }

                public function array(): array {
                    $rows = [];

                    // Baris 1 - 3: Header Judul Resmi
                    $rows[] = ['PT MUSASHI AUTO PARTS INDONESIA - WOLA ASSESSMENT SYSTEM', '', '', '', '', ''];
                    $rows[] = ['DASHBOARD & PIVOT REKAPITULASI PROGRES EVALUASI BEBAN KERJA', '', '', '', '', ''];
                    $rows[] = [
                        'Tanggal Unduh: ' . date('d F Y, H:i') . ' WIB  |  Target Karyawan: ' . number_format($this->d['adminTotalTargetEmployees']) . ' Org (' . $this->d['adminAssessedEmployeesCount'] . ' Dinilai - ' . $this->d['adminEmployeeProgressPercentage'] . '%)  |  Target Job: ' . $this->d['adminTotalJobs'] . ' Posisi (' . $this->d['adminAssessedJobsCount'] . ' Dinilai - ' . $this->d['adminJobPct'] . '%)  |  Status: ' . $this->d['adminEmployeeProgressStatus'],
                        '', '', '', '', ''
                    ];
                    $rows[] = ['', '', '', '', '', '']; // Baris 4 spacer

                    // =========================================================
                    // 1. PIVOT TABEL 1: PERBANDINGAN CAPAIAN KARYAWAN DINILAI
                    // =========================================================
                    $rows[] = [
                        'No',
                        'Nomor Kelompok',
                        'Nama Area Kelompok',
                        'Target Karyawan (Org)',
                        'Karyawan Dinilai (Org)',
                        'Capaian Karyawan (%)',
                    ];

                    $counter = 1;
                    $totalTargetEmp = 0;
                    $totalAssessedEmp = 0;

                    foreach ($this->d['groupsWithProgress'] as $g) {
                        $p = $g->calculated_progress;
                        $empTotal = (int)($p['employee_count'] ?? 0);
                        $empAssessed = (int)($p['assessed_employees_count'] ?? 0);
                        $empPct = (float)($p['percentage'] ?? 0);

                        $totalTargetEmp += $empTotal;
                        $totalAssessedEmp += $empAssessed;

                        $rows[] = [
                            $counter++,
                            'Kelompok ' . $g->number,
                            $g->name,
                            $empTotal,
                            $empAssessed,
                            $empPct,
                        ];
                    }

                    $numGroups = count($this->d['groupsWithProgress']);
                    $safeDivisor = max(1, $numGroups);

                    $totalEmpPct = $totalTargetEmp > 0 ? (float)round(($totalAssessedEmp / $totalTargetEmp) * 100, 1) : 0.0;
                    $rows[] = [
                        'TOTAL',
                        '-',
                        'TOTAL KARYAWAN (' . $numGroups . ' KELOMPOK)',
                        $totalTargetEmp,
                        $totalAssessedEmp,
                        $totalEmpPct,
                    ];

                    // Spacer Baris
                    $rows[] = ['', '', '', '', '', ''];
                    $rows[] = ['', '', '', '', '', ''];

                    // =========================================================
                    // 2. PIVOT TABEL 2: PERBANDINGAN CAPAIAN POSISI JOB DINILAI
                    // =========================================================
                    $rows[] = [
                        'No',
                        'Nomor Kelompok',
                        'Nama Area Kelompok',
                        'Total Posisi (Job)',
                        'Posisi Job Dinilai',
                        'Capaian Job (%)',
                    ];

                    $counter = 1;
                    $totalTargetJob = 0;
                    $totalAssessedJob = 0;

                    foreach ($this->d['groupsWithProgress'] as $idx => $g) {
                        $jobTotal = (int)($this->d['chartJobTotal'][$idx] ?? ($g->jobs ? $g->jobs->count() : 0));
                        $jobAssessed = (int)($this->d['chartJobAssessed'][$idx] ?? 0);
                        $jobPct = (float)($this->d['chartJobData'][$idx] ?? ($jobTotal > 0 ? round(($jobAssessed / $jobTotal) * 100, 1) : 0));

                        $totalTargetJob += $jobTotal;
                        $totalAssessedJob += $jobAssessed;

                        $rows[] = [
                            $counter++,
                            'Kelompok ' . $g->number,
                            $g->name,
                            $jobTotal,
                            $jobAssessed,
                            $jobPct,
                        ];
                    }

                    $totalJobPct = $totalTargetJob > 0 ? (float)round(($totalAssessedJob / $totalTargetJob) * 100, 1) : 0.0;
                    $rows[] = [
                        'TOTAL',
                        '-',
                        'TOTAL POSISI JOB (' . $numGroups . ' KELOMPOK)',
                        $totalTargetJob,
                        $totalAssessedJob,
                        $totalJobPct,
                    ];

                    // Spacer Baris
                    $rows[] = ['', '', '', '', '', ''];
                    $rows[] = ['', '', '', '', '', ''];

                    // =========================================================
                    // 3. PIVOT TABEL 3: DISTRIBUSI STATUS KELOMPOK
                    // =========================================================
                    $rows[] = [
                        'No',
                        'Status Penyelesaian Kelompok',
                        'Jumlah Kelompok',
                        'Persentase (%)',
                        'Keterangan Status',
                        '',
                    ];

                    $kpiStatus = [
                        [1, 'Selesai', (int)$this->d['completedCount'], (float)round(($this->d['completedCount'] / $safeDivisor) * 100, 1), 'Progres 100% Selesai', ''],
                        [2, 'Berjalan', (int)$this->d['inProgressCount'], (float)round(($this->d['inProgressCount'] / $safeDivisor) * 100, 1), 'Aktif Pengisian Penilaian', ''],
                        [3, 'Tertinggal', (int)$this->d['laggingCount'], (float)round(($this->d['laggingCount'] / $safeDivisor) * 100, 1), 'Perlu Akselerasi & Tindak Lanjut', ''],
                        [4, 'Belum Mulai', (int)$this->d['notStartedCount'], (float)round(($this->d['notStartedCount'] / $safeDivisor) * 100, 1), 'Menunggu Pelaksanaan Pengisian', ''],
                    ];

                    foreach ($kpiStatus as $ks) {
                        $rows[] = $ks;
                    }

                    $rows[] = [
                        'TOTAL',
                        'TOTAL KESELURUHAN (' . $numGroups . ' KELOMPOK)',
                        $numGroups,
                        100.0,
                        'Semua Kelompok Terdata',
                        '',
                    ];

                    return $rows;
                }

                public function charts() {
                    $numGroups = count($this->d['groupsWithProgress']);
                    if ($numGroups === 0) return [];

                    $t1Start = 6;
                    $t1End = 5 + $numGroups;
                    $t1Total = 6 + $numGroups;

                    $t2Header = 9 + $numGroups;
                    $t2Start = 10 + $numGroups;
                    $t2End = 9 + (2 * $numGroups);
                    $t2Total = 10 + (2 * $numGroups);

                    $t3Header = 13 + (2 * $numGroups);
                    $t3Start = 14 + (2 * $numGroups);
                    $t3End = 17 + (2 * $numGroups);
                    $t3Total = 18 + (2 * $numGroups);

                    // -------------------------------------------------------------
                    // 1. Chart 1: Perbandingan Capaian Karyawan Dinilai Antar Kelompok
                    // -------------------------------------------------------------
                    $cat1 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$B\${$t1Start}:\$B\${$t1End}", null, $numGroups)
                    ];
                    $val1 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$F\${$t1Start}:\$F\${$t1End}", null, $numGroups)
                    ];
                    $lab1 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Dashboard!$F$5', null, 1)
                    ];

                    $series1 = new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        DataSeries::GROUPING_CLUSTERED,
                        [0],
                        $lab1,
                        $cat1,
                        $val1,
                        DataSeries::DIRECTION_COL
                    );

                    $chart1 = new Chart(
                        'chart_emp_capaian',
                        new Title('Perbandingan Capaian Karyawan Dinilai Antar Kelompok (%)'),
                        null,
                        new PlotArea(null, [$series1]),
                        true,
                        DataSeries::EMPTY_AS_GAP,
                        new Title('Kelompok Kerja'),
                        new Title('Capaian (%)')
                    );
                    $chart1->setTopLeftPosition('H5');
                    $chart1Height = max(15, $numGroups + 4);
                    $chart1->setBottomRightPosition('R' . (5 + $chart1Height));

                    // -------------------------------------------------------------
                    // 2. Chart 2: Perbandingan Capaian Posisi Job Dinilai Antar Kelompok
                    // -------------------------------------------------------------
                    $cat2 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$B\${$t2Start}:\$B\${$t2End}", null, $numGroups)
                    ];
                    $val2 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$F\${$t2Start}:\$F\${$t2End}", null, $numGroups)
                    ];
                    $lab2 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$F\${$t2Header}", null, 1)
                    ];

                    $series2 = new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        DataSeries::GROUPING_CLUSTERED,
                        [0],
                        $lab2,
                        $cat2,
                        $val2,
                        DataSeries::DIRECTION_COL
                    );

                    $chart2 = new Chart(
                        'chart_job_capaian',
                        new Title('Perbandingan Capaian Posisi Job Dinilai Antar Kelompok (%)'),
                        null,
                        new PlotArea(null, [$series2]),
                        true,
                        DataSeries::EMPTY_AS_GAP,
                        new Title('Kelompok Kerja'),
                        new Title('Capaian (%)')
                    );
                    $chart2Top = max(5 + $chart1Height + 2, $t2Header);
                    $chart2->setTopLeftPosition('H' . $chart2Top);
                    $chart2->setBottomRightPosition('R' . ($chart2Top + $chart1Height));

                    // -------------------------------------------------------------
                    // 3. Chart 3: Donut Chart Distribusi Status Kelompok
                    // -------------------------------------------------------------
                    $cat3 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$B\${$t3Start}:\$B\${$t3End}", null, 4)
                    ];
                    $val3 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$C\${$t3Start}:\$C\${$t3End}", null, 4)
                    ];
                    $lab3 = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$C\${$t3Header}", null, 1)
                    ];

                    $series3 = new DataSeries(
                        DataSeries::TYPE_DOUGHNUTCHART,
                        null,
                        [0],
                        $lab3,
                        $cat3,
                        $val3
                    );

                    $chart3 = new Chart(
                        'chart_status_donut',
                        new Title('Distribusi Status Kelompok (' . $numGroups . ' Kelompok)'),
                        new Legend(Legend::POSITION_BOTTOM, null, false),
                        new PlotArea(null, [$series3]),
                        true,
                        DataSeries::EMPTY_AS_GAP
                    );
                    $chart3Top = max($chart2Top + $chart1Height + 2, $t3Header);
                    $chart3->setTopLeftPosition('H' . $chart3Top);
                    $chart3->setBottomRightPosition('R' . ($chart3Top + 14));

                    return [$chart1, $chart2, $chart3];
                }

                public function styles(Worksheet $sheet) {
                    $numGroups = count($this->d['groupsWithProgress']);
                    if ($numGroups === 0) return [];

                    $t1Start = 6;
                    $t1End = 5 + $numGroups;
                    $t1Total = 6 + $numGroups;

                    $t2Header = 9 + $numGroups;
                    $t2Start = 10 + $numGroups;
                    $t2End = 9 + (2 * $numGroups);
                    $t2Total = 10 + (2 * $numGroups);

                    $t3Header = 13 + (2 * $numGroups);
                    $t3Start = 14 + (2 * $numGroups);
                    $t3End = 17 + (2 * $numGroups);
                    $t3Total = 18 + (2 * $numGroups);

                    // Header title styles
                    $sheet->mergeCells('A1:F1');
                    $sheet->mergeCells('A2:F2');
                    $sheet->mergeCells('A3:F3');

                    $sheet->getStyle('A1')->getFont()->setSize(13)->setBold(true)->getColor()->setARGB('FF1E3A8A');
                    $sheet->getStyle('A2')->getFont()->setSize(10)->setBold(true)->getColor()->setARGB('FF334155');
                    $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setARGB('FF64748B');

                    // Table 1 Header (Row 5)
                    $sheet->getStyle("A5:F5")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1E40AF'], // Blue-800
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                    $sheet->getRowDimension(5)->setRowHeight(26);

                    // Table 1 Data formatting
                    $sheet->getStyle("A{$t1Start}:A{$t1Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$t1Start}:B{$t1Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$t1Start}:F{$t1Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A5:F{$t1Total}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');

                    // Table 1 Total Row
                    $sheet->getStyle("A{$t1Total}:F{$t1Total}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF'], 'size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFEFF6FF'],
                        ],
                    ]);
                    $sheet->getStyle("A{$t1Total}:F{$t1Total}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF1E40AF');
                    $sheet->getStyle("A{$t1Total}:F{$t1Total}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setARGB('FF1E40AF');

                    // Table 2 Header
                    $sheet->getStyle("A{$t2Header}:F{$t2Header}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF6B21A8'], // Purple-800
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                    $sheet->getRowDimension($t2Header)->setRowHeight(26);

                    // Table 2 Data formatting
                    $sheet->getStyle("A{$t2Start}:A{$t2Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$t2Start}:B{$t2Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$t2Start}:F{$t2Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A{$t2Header}:F{$t2Total}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');

                    // Table 2 Total Row
                    $sheet->getStyle("A{$t2Total}:F{$t2Total}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF6B21A8'], 'size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF5F3FF'],
                        ],
                    ]);
                    $sheet->getStyle("A{$t2Total}:F{$t2Total}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF6B21A8');
                    $sheet->getStyle("A{$t2Total}:F{$t2Total}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setARGB('FF6B21A8');

                    // Table 3 Header
                    $sheet->getStyle("A{$t3Header}:E{$t3Header}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF047857'], // Emerald-700
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension($t3Header)->setRowHeight(24);

                    // Table 3 Data formatting
                    $sheet->getStyle("A{$t3Start}:A{$t3Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$t3Start}:D{$t3Total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$t3Start}:D{$t3Total}")->getNumberFormat()->setFormatCode('0.0"%"');
                    $sheet->getStyle("A{$t3Header}:E{$t3Total}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');

                    // Table 3 Total Row
                    $sheet->getStyle("A{$t3Total}:E{$t3Total}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF047857'], 'size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFECFDF5'],
                        ],
                    ]);
                    $sheet->getStyle("A{$t3Total}:E{$t3Total}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF047857');
                    $sheet->getStyle("A{$t3Total}:E{$t3Total}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setARGB('FF047857');

                    return [];
                }
            },

            // =================================================================
            // SHEET 2: SUMMARY 12 KELOMPOK KERJA (DETAIL KETUA & CHECKLIST)
            // =================================================================
            new class($this->data) implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithStyles {
                protected array $d;

                public function __construct(array $d) {
                    $this->d = $d;
                }

                public function title(): string {
                    $totalGroups = count($this->d['groupsWithProgress']);
                    return "Summary {$totalGroups} Kelompok";
                }

                public function headings(): array {
                    return [
                        'No',
                        'Nomor Kelompok',
                        'Nama Area Kelompok',
                        'Ketua Kelompok',
                        'Jumlah Tim Penilai',
                        'Jumlah Posisi Jabatan',
                        'Target Karyawan (Orang)',
                        'Karyawan Dinilai (Orang)',
                        'Target Checklist (Unit)',
                        'Realisasi Checklist (Unit)',
                        'Capaian Karyawan (%)',
                        'Capaian Checklist (%)',
                        'Status Monitoring',
                    ];
                }

                public function array(): array {
                    $rows = [];
                    $counter = 1;
                    $totalTargetEmp = 0;
                    $totalAssessedEmp = 0;
                    $totalTargetUnit = 0;
                    $totalRealizedUnit = 0;

                    foreach ($this->d['groupsWithProgress'] as $g) {
                        $p = $g->calculated_progress;
                        $leaderName = $g->leader ? $g->leader->name : 'Belum ditetapkan';
                        $empTotal = $p['employee_count'] ?? 0;
                        $empAssessed = $p['assessed_employees_count'] ?? 0;
                        $chkTarget = $p['target'] ?? 0;
                        $chkRealized = $p['checked'] ?? 0;
                        $empPct = $p['percentage'] ?? 0;
                        $chkPct = $chkTarget > 0 ? round(($chkRealized / $chkTarget) * 100, 1) : 0;

                        $totalTargetEmp += $empTotal;
                        $totalAssessedEmp += $empAssessed;
                        $totalTargetUnit += $chkTarget;
                        $totalRealizedUnit += $chkRealized;

                        $rows[] = [
                            $counter++,
                            'Kelompok ' . $g->number,
                            $g->name,
                            $leaderName,
                            $p['assessor_count'] ?? 0,
                            $p['job_count'] ?? 0,
                            $empTotal,
                            $empAssessed,
                            $chkTarget,
                            $chkRealized,
                            $empPct . '%',
                            $chkPct . '%',
                            $p['status'] ?? '-',
                        ];
                    }

                    // Baris Total Akumulasi
                    $totalEmpPct = $totalTargetEmp > 0 ? round(($totalAssessedEmp / $totalTargetEmp) * 100, 1) : 0;
                    $totalChkPct = $totalTargetUnit > 0 ? round(($totalRealizedUnit / $totalTargetUnit) * 100, 1) : 0;

                    $totalGroupsCount = count($this->d['groupsWithProgress']);
                    $rows[] = [
                        'TOTAL',
                        '-',
                        "TOTAL KESELURUHAN ({$totalGroupsCount} KELOMPOK)",
                        '-',
                        $this->d['adminTotalAssessors'],
                        $this->d['adminTotalJobs'],
                        $totalTargetEmp,
                        $totalAssessedEmp,
                        $totalTargetUnit,
                        $totalRealizedUnit,
                        $totalEmpPct . '%',
                        $totalChkPct . '%',
                        $this->d['adminEmployeeProgressStatus'],
                    ];

                    return $rows;
                }

                public function styles(Worksheet $sheet) {
                    $lastRow = count($this->d['groupsWithProgress']) + 2;
                    return [
                        1 => [
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF1E3A8A'],
                            ],
                        ],
                        $lastRow => [
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E3A8A']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFEFF6FF'],
                            ],
                        ],
                    ];
                }
            },

            // =================================================================
            // SHEET 3: DAFTAR KARYAWAN SUDAH DINILAI (LINTAS SELURUH KELOMPOK)
            // =================================================================
            new class($this->data) implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithStyles {
                protected array $d;

                public function __construct(array $d) {
                    $this->d = $d;
                }

                public function title(): string {
                    return 'Daftar Karyawan Sudah Dinilai';
                }

                public function headings(): array {
                    return [
                        'No',
                        'Nomor Kelompok',
                        'Nama Area Kelompok',
                        'NRP Karyawan',
                        'Nama Lengkap Karyawan',
                        'Departemen',
                        'Total Job Dinilai',
                        'Daftar Posisi Job Dinilai',
                        'Tim Penilai',
                        'Total Ceklis Diverifikasi',
                        'Status Lengkap Seluruh Penilai',
                    ];
                }

                public function array(): array {
                    $rows = [];
                    $counter = 1;

                    foreach ($this->d['adminAssessedEmployeesList'] as $item) {
                        $jobNames = $item->distinct_jobs->pluck('name')->implode(', ');
                        $assessors = $item->distinct_assessors->implode(', ');

                        $rows[] = [
                            $counter++,
                            'Kelompok ' . $item->group_number,
                            $item->group_name,
                            $item->employee->nrp,
                            $item->employee->fullname,
                            $item->employee->dept ?? '-',
                            $item->assessed_jobs_count . ' Job',
                            $jobNames,
                            $assessors,
                            $item->total_checklists . ' Ceklis',
                            $item->is_full ? 'Lengkap Semua Penilai' : 'Sebagian Penilai',
                        ];
                    }

                    return $rows;
                }

                public function styles(Worksheet $sheet) {
                    $styles = [
                        1 => [
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF047857'], // Emerald
                            ],
                        ],
                    ];

                    $highestRow = $sheet->getHighestRow();
                    for ($r = 2; $r <= $highestRow; $r++) {
                        $val = (string)$sheet->getCell('K' . $r)->getValue();
                        if (str_contains($val, 'Lengkap')) {
                            $sheet->getStyle('K' . $r)->getFont()->setBold(true)->getColor()->setARGB('FF15803D');
                            $sheet->getStyle('K' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCFCE7');
                        } else {
                            $sheet->getStyle('K' . $r)->getFont()->getColor()->setARGB('FFB45309');
                            $sheet->getStyle('K' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF3C7');
                        }
                    }

                    return $styles;
                }
            },

            // =================================================================
            // SHEET 4: DAFTAR POSISI JOB SUDAH DINILAI (LINTAS SELURUH KELOMPOK)
            // =================================================================
            new class($this->data) implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithStyles {
                protected array $d;

                public function __construct(array $d) {
                    $this->d = $d;
                }

                public function title(): string {
                    return 'Daftar Posisi Job Sudah Dinilai';
                }

                public function headings(): array {
                    return [
                        'No',
                        'Nomor Kelompok',
                        'Nama Area Kelompok',
                        'Kode Job',
                        'Nama Posisi Jabatan',
                        'Karyawan Dinilai (Orang)',
                        'Target Karyawan (Orang)',
                        'Capaian Job (%)',
                        'Penilai Aktif',
                        'Total Ceklis Diverifikasi',
                    ];
                }

                public function array(): array {
                    $rows = [];
                    $counter = 1;

                    foreach ($this->d['adminAssessedJobsList'] as $item) {
                        $assessors = $item->distinct_assessors->implode(', ');

                        $rows[] = [
                            $counter++,
                            'Kelompok ' . $item->group_number,
                            $item->group_name,
                            $item->job->code ?? '-',
                            $item->job->name,
                            $item->assessed_employees_count,
                            $item->target_count,
                            $item->percentage . '%',
                            $assessors,
                            $item->total_checklists . ' Ceklis',
                        ];
                    }

                    return $rows;
                }

                public function styles(Worksheet $sheet) {
                    return [
                        1 => [
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF6B21A8'], // Purple
                            ],
                        ],
                    ];
                }
            },

            // =================================================================
            // SHEET 5: RINGKASAN KPI GLOBAL DASHBOARD
            // =================================================================
            new class($this->data) implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithStyles {
                protected array $d;

                public function __construct(array $d) {
                    $this->d = $d;
                }

                public function title(): string {
                    return 'Ringkasan KPI Dashboard';
                }

                public function headings(): array {
                    return [
                        'No',
                        'Indikator Metrik Evaluasi WOLA',
                        'Capaian / Nilai Riil',
                        'Target / Total Referensi',
                        'Persentase Capaian (%)',
                        'Keterangan Status',
                    ];
                }

                public function array(): array {
                    $totalGroups = count($this->d['groupsWithProgress']);
                    $safeDivisor = max(1, $totalGroups);

                    return [
                        [
                            1,
                            'Karyawan Target Sudah Dinilai',
                            $this->d['adminAssessedEmployeesCount'] . ' Orang',
                            $this->d['adminTotalTargetEmployees'] . ' Orang',
                            $this->d['adminEmployeeProgressPercentage'] . '%',
                            $this->d['adminEmployeeProgressStatus'],
                        ],
                        [
                            2,
                            'Posisi Jabatan yang Telah Dinilai',
                            $this->d['adminAssessedJobsCount'] . ' Posisi',
                            $this->d['adminTotalJobs'] . ' Posisi',
                            $this->d['adminJobPct'] . '%',
                            'Aktif Dinilai Penilai',
                        ],
                        [
                            3,
                            'Total Verifikasi Checklist Penilaian',
                            $this->d['adminVerifiedChecklists'] . ' Checklist',
                            $this->d['adminTargetChecklists'] . ' Checklist Target',
                            $this->d['adminChecklistPercentage'] . '%',
                            'Akumulasi Seluruh Kelompok',
                        ],
                        [
                            4,
                            'Karyawan Lengkap Dinilai Seluruh Penilai',
                            $this->d['adminFullAssessedEmployeesCount'] . ' Orang',
                            $this->d['adminTotalTargetEmployees'] . ' Orang',
                            $this->d['adminFullAssessedPercentage'] . '%',
                            'Telah dinilai oleh seluruh tim penilai',
                        ],
                        [
                            5,
                            'Kelompok Kerja Berstatus Selesai',
                            $this->d['completedCount'] . ' Kelompok',
                            $totalGroups . ' Kelompok',
                            round(($this->d['completedCount'] / $safeDivisor) * 100, 1) . '%',
                            'Capaian 100%',
                        ],
                        [
                            6,
                            'Kelompok Kerja Berstatus Berjalan',
                            $this->d['inProgressCount'] . ' Kelompok',
                            $totalGroups . ' Kelompok',
                            round(($this->d['inProgressCount'] / $safeDivisor) * 100, 1) . '%',
                            'Sedang dalam proses penilaian',
                        ],
                        [
                            7,
                            'Kelompok Kerja Berstatus Tertinggal',
                            $this->d['laggingCount'] . ' Kelompok',
                            $totalGroups . ' Kelompok',
                            round(($this->d['laggingCount'] / $safeDivisor) * 100, 1) . '%',
                            'Perlu akselerasi penilaian',
                        ],
                        [
                            8,
                            'Kelompok Kerja Belum Mulai',
                            $this->d['notStartedCount'] . ' Kelompok',
                            $totalGroups . ' Kelompok',
                            round(($this->d['notStartedCount'] / $safeDivisor) * 100, 1) . '%',
                            'Belum ada penilaian tercatat',
                        ],
                        [
                            9,
                            'Total Tim Penilai Terdaftar',
                            $this->d['adminTotalAssessors'] . ' Penilai',
                            $totalGroups . ' Kelompok',
                            '100%',
                            'Tersebar di seluruh kelompok',
                        ],
                    ];
                }

                public function styles(Worksheet $sheet) {
                    return [
                        1 => [
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF1E293B'], // Slate-800
                            ],
                        ],
                    ];
                }
            },
        ];
    }
}
