<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Job;
use App\Models\Employee;
use App\Models\GroupAssessor;
use App\Models\Assessment;
use App\Models\ActivityLog;
use App\Exports\WolaReportExport;
use App\Exports\AdminDashboardExport;
use App\Services\DashboardMonitoringService;
use App\Models\JobComment;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Tampilan utama pusat laporan WOLA (Matriks, Detail "Siapa Menilai Siapa", Ringkasan)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdminOrSuper = $user->hasRole(['superadmin', 'admin']);
        $isKetuaOnly = $user->hasRole('ketua') && !$isAdminOrSuper;

        $tab = $request->input('tab', 'matrix'); // matrix | details | summary | group_summary
        if ($tab === 'group_summary' && !$isAdminOrSuper) {
            $tab = 'matrix';
        }

        // Kelompok yang diizinkan diakses
        if ($isKetuaOnly) {
            $groupId = $user->group_id;
            $allGroups = Group::where('id', $groupId)->get();
            $isAllGroups = false;
        } else {
            $allGroups = Group::with([
                'leader',
                'assessors.employee',
                'jobs',
                'targetEmployees'
            ])->orderBy('number')->get();

            $rawGroupId = $request->input('group_id');
            // Jika memilih 'all' atau belum memilih di Tab matrix/details, support 'all'
            if ($rawGroupId === 'all') {
                $groupId = 'all';
                $isAllGroups = true;
            } elseif ($rawGroupId) {
                $groupId = $rawGroupId;
                $isAllGroups = false;
            } else {
                // Default di halaman admin & superadmin adalah 'all'
                $groupId = 'all';
                $isAllGroups = true;
            }
        }

        $selectedGroup = (!$isAllGroups && $groupId) 
            ? Group::with(['leader', 'assessors.employee', 'jobs', 'targetEmployees'])->find($groupId) 
            : null;

        // Data khusus tab Summary Kelompok (Hasil Dashboard Admin - Khusus Superadmin & Admin)
        $dashboardData = ($tab === 'group_summary' && $isAdminOrSuper) ? DashboardMonitoringService::getAdminMonitoringData() : null;

        // 1. DATA UNTUK TAB MATRIKS (Karyawan × Penilai per Jabatan)
        $selectedJob = null;
        $matrixData = [];
        $matrixAssessors = collect();
        $matrixEmployees = collect();
        $jobs = collect();
        $allGroupsMatrixData = [];
        $isAllJobs = ($request->input('job_id') === 'all' && Auth::user()->hasRole(['superadmin', 'admin']));
        $singleGroupAllJobsData = [];
        $globalMatrixStats = [
            'total_groups' => $allGroups->count(),
            'total_employees' => 0,
            'total_assessors' => 0,
            'total_checks' => 0,
            'full_count' => 0,
            'partial_count' => 0,
            'zero_count' => 0,
        ];

        // Ambil seluruh data penilaian untuk efisiensi
        $allAssessments = Assessment::all();

        if ($isAllGroups) {
            // Mode "Semua Kelompok" (Khusus Admin & Superadmin)
            foreach ($allGroups as $g) {
                $totalTarget = $g->targetEmployees->count();
                $totalAssessors = $g->assessors->count();
                $globalMatrixStats['total_employees'] += $totalTarget;
                $globalMatrixStats['total_assessors'] += $totalAssessors;

                $groupAssessments = $allAssessments->where('group_id', $g->id);
                $globalMatrixStats['total_checks'] += $groupAssessments->count();

                // Petakan tiap jabatan
                $jobsData = [];
                foreach ($g->jobs as $j) {
                    $jobChecks = $groupAssessments->where('job_id', $j->id);
                    $jobMatrix = [];
                    foreach ($jobChecks as $a) {
                        $jobMatrix[$a->employee_id][$a->assessor_id] = [
                            'checked' => true,
                            'checked_at' => $a->checked_at ? $a->checked_at->format('d/m/Y H:i') : '✓',
                        ];
                    }

                    $zeroCount = 0;
                    $oneCount = 0;
                    $twoOrPartial = 0;
                    $fullCount = 0;

                    foreach ($g->targetEmployees as $emp) {
                        $cnt = isset($jobMatrix[$emp->id]) ? count($jobMatrix[$emp->id]) : 0;
                        if ($cnt === 0) {
                            $zeroCount++;
                        } elseif ($cnt === 1) {
                            if ($totalAssessors === 1) $fullCount++;
                            else $oneCount++;
                        } elseif ($cnt >= $totalAssessors && $totalAssessors > 0) {
                            $fullCount++;
                        } else {
                            $twoOrPartial++;
                        }
                    }

                    $jobsData[$j->id] = [
                        'job' => $j,
                        'total_employees' => $totalTarget,
                        'total_assessors' => $totalAssessors,
                        'zero' => $zeroCount,
                        'zero_pct' => $totalTarget > 0 ? round(($zeroCount / $totalTarget) * 100, 1) : 0,
                        'one' => $oneCount,
                        'one_pct' => $totalTarget > 0 ? round(($oneCount / $totalTarget) * 100, 1) : 0,
                        'two' => $twoOrPartial,
                        'partial' => $twoOrPartial,
                        'partial_pct' => $totalTarget > 0 ? round(($twoOrPartial / $totalTarget) * 100, 1) : 0,
                        'full' => $fullCount,
                        'full_pct' => $totalTarget > 0 ? round(($fullCount / $totalTarget) * 100, 1) : 0,
                        'matrix' => $jobMatrix,
                    ];
                }

                $firstJob = $g->jobs->first();
                $firstJobStats = $firstJob ? ($jobsData[$firstJob->id] ?? null) : null;
                if ($firstJobStats) {
                    $globalMatrixStats['zero_count'] += $firstJobStats['zero'];
                    $globalMatrixStats['partial_count'] += ($firstJobStats['one'] + $firstJobStats['partial']);
                    $globalMatrixStats['full_count'] += $firstJobStats['full'];
                }

                $jobMatrixData = [];
                $jobStatsData = [];
                foreach ($jobsData as $jid => $jInfo) {
                    $jobMatrixData[$jid] = $jInfo['matrix'];
                    $jobStatsData[$jid] = $jInfo;
                }

                $firstJobId = $g->jobs->first()?->id ?? 0;
                $allGroupsMatrixData[$g->id] = [
                    'group' => $g,
                    'assessors' => $g->assessors,
                    'employees' => $g->targetEmployees,
                    'jobs' => $g->jobs,
                    'jobsData' => $jobsData,
                    'jobStats' => $jobStatsData,
                    'matrixData' => $jobMatrixData,
                    'activeJobId' => $firstJobId,
                    'firstJobId' => $firstJobId,
                ];
            }

            $jobMatrixStats = [
                'total_employees' => $globalMatrixStats['total_employees'],
                'total_assessors' => $globalMatrixStats['total_assessors'],
                'zero' => $globalMatrixStats['zero_count'],
                'zero_pct' => $globalMatrixStats['total_employees'] > 0 ? round(($globalMatrixStats['zero_count'] / $globalMatrixStats['total_employees']) * 100, 1) : 0,
                'one' => 0,
                'one_pct' => 0,
                'two' => $globalMatrixStats['partial_count'],
                'two_pct' => $globalMatrixStats['total_employees'] > 0 ? round(($globalMatrixStats['partial_count'] / $globalMatrixStats['total_employees']) * 100, 1) : 0,
                'full' => $globalMatrixStats['full_count'],
                'full_pct' => $globalMatrixStats['total_employees'] > 0 ? round(($globalMatrixStats['full_count'] / $globalMatrixStats['total_employees']) * 100, 1) : 0,
            ];
        } else {
            // Mode 1 Kelompok Spesifik
            $isAllJobs = ($request->input('job_id') === 'all' && Auth::user()->hasRole(['superadmin', 'admin']));
            $singleGroupAllJobsData = [];

            if ($selectedGroup) {
                $jobs = $selectedGroup->jobs;
                $matrixAssessors = $selectedGroup->assessors;
                $matrixEmployees = $selectedGroup->targetEmployees()->orderBy('employees.nrp')->get();

                if ($isAllJobs) {
                    $selectedJob = null;
                    $zeroCountTotal = 0;
                    $oneCountTotal = 0;
                    $twoOrPartialTotal = 0;
                    $fullCountTotal = 0;

                    foreach ($jobs as $j) {
                        $jobAssessments = Assessment::where('group_id', $selectedGroup->id)
                            ->where('job_id', $j->id)
                            ->get();

                        $jMatrix = [];
                        foreach ($jobAssessments as $a) {
                            $jMatrix[$a->employee_id][$a->assessor_id] = [
                                'checked' => true,
                                'checked_at' => $a->checked_at ? $a->checked_at->format('d/m/Y H:i') : '✓',
                            ];
                        }

                        $totalTargetEmp = $matrixEmployees->count();
                        $totalAssessorsCount = $matrixAssessors->count();

                        $zeroCount = 0;
                        $oneCount = 0;
                        $twoOrPartialCount = 0;
                        $fullCount = 0;

                        foreach ($matrixEmployees as $emp) {
                            $cnt = isset($jMatrix[$emp->id]) ? count($jMatrix[$emp->id]) : 0;
                            if ($cnt === 0) {
                                $zeroCount++;
                            } elseif ($cnt === 1) {
                                if ($totalAssessorsCount === 1) {
                                    $fullCount++;
                                } else {
                                    $oneCount++;
                                }
                            } elseif ($cnt >= $totalAssessorsCount && $totalAssessorsCount > 0) {
                                $fullCount++;
                            } else {
                                $twoOrPartialCount++;
                            }
                        }

                        $zeroCountTotal += $zeroCount;
                        $oneCountTotal += $oneCount;
                        $twoOrPartialTotal += $twoOrPartialCount;
                        $fullCountTotal += $fullCount;

                        $singleGroupAllJobsData[$j->id] = [
                            'job' => $j,
                            'matrix' => $jMatrix,
                            'stats' => [
                                'total_employees' => $totalTargetEmp,
                                'total_assessors' => $totalAssessorsCount,
                                'zero' => $zeroCount,
                                'zero_pct' => $totalTargetEmp > 0 ? round(($zeroCount / $totalTargetEmp) * 100, 1) : 0,
                                'one' => $oneCount,
                                'one_pct' => $totalTargetEmp > 0 ? round(($oneCount / $totalTargetEmp) * 100, 1) : 0,
                                'two' => $twoOrPartialCount,
                                'two_pct' => $totalTargetEmp > 0 ? round(($twoOrPartialCount / $totalTargetEmp) * 100, 1) : 0,
                                'full' => $fullCount,
                                'full_pct' => $totalTargetEmp > 0 ? round(($fullCount / $totalTargetEmp) * 100, 1) : 0,
                            ],
                        ];
                    }

                    $totalPossibleEvaluations = $jobs->count() * $matrixEmployees->count();
                    $jobMatrixStats = [
                        'total_employees' => $matrixEmployees->count(),
                        'total_assessors' => $matrixAssessors->count(),
                        'zero' => $zeroCountTotal,
                        'zero_pct' => $totalPossibleEvaluations > 0 ? round(($zeroCountTotal / $totalPossibleEvaluations) * 100, 1) : 0,
                        'one' => $oneCountTotal,
                        'one_pct' => $totalPossibleEvaluations > 0 ? round(($oneCountTotal / $totalPossibleEvaluations) * 100, 1) : 0,
                        'two' => $twoOrPartialTotal,
                        'two_pct' => $totalPossibleEvaluations > 0 ? round(($twoOrPartialTotal / $totalPossibleEvaluations) * 100, 1) : 0,
                        'full' => $fullCountTotal,
                        'full_pct' => $totalPossibleEvaluations > 0 ? round(($fullCountTotal / $totalPossibleEvaluations) * 100, 1) : 0,
                    ];
                } else {
                    $jobId = $request->input('job_id', $jobs->first()->id ?? null);
                    if ($jobId) {
                        $selectedJob = $jobs->firstWhere('id', $jobId) ?? $jobs->first();
                    }

                    if ($selectedJob) {
                        $assessments = Assessment::where('group_id', $selectedGroup->id)
                            ->where('job_id', $selectedJob->id)
                            ->get();

                        foreach ($assessments as $a) {
                            $matrixData[$a->employee_id][$a->assessor_id] = [
                                'checked' => true,
                                'checked_at' => $a->checked_at ? $a->checked_at->format('d/m/Y H:i') : '✓',
                            ];
                        }

                        $totalTargetEmp = $matrixEmployees->count();
                        $totalAssessorsCount = $matrixAssessors->count();

                        $zeroCount = 0;
                        $oneCount = 0;
                        $twoOrPartialCount = 0;
                        $fullCount = 0;

                        foreach ($matrixEmployees as $emp) {
                            $cnt = isset($matrixData[$emp->id]) ? count($matrixData[$emp->id]) : 0;
                            if ($cnt === 0) {
                                $zeroCount++;
                            } elseif ($cnt === 1) {
                                if ($totalAssessorsCount === 1) {
                                    $fullCount++;
                                } else {
                                    $oneCount++;
                                }
                            } elseif ($cnt >= $totalAssessorsCount && $totalAssessorsCount > 0) {
                                $fullCount++;
                            } else {
                                $twoOrPartialCount++;
                            }
                        }

                        $jobMatrixStats = [
                            'total_employees' => $totalTargetEmp,
                            'total_assessors' => $totalAssessorsCount,
                            'zero' => $zeroCount,
                            'zero_pct' => $totalTargetEmp > 0 ? round(($zeroCount / $totalTargetEmp) * 100, 1) : 0,
                            'one' => $oneCount,
                            'one_pct' => $totalTargetEmp > 0 ? round(($oneCount / $totalTargetEmp) * 100, 1) : 0,
                            'two' => $twoOrPartialCount,
                            'two_pct' => $totalTargetEmp > 0 ? round(($twoOrPartialCount / $totalTargetEmp) * 100, 1) : 0,
                            'full' => $fullCount,
                            'full_pct' => $totalTargetEmp > 0 ? round(($fullCount / $totalTargetEmp) * 100, 1) : 0,
                        ];
                    } else {
                        $jobMatrixStats = [
                            'total_employees' => 0,
                            'total_assessors' => 0,
                            'zero' => 0,
                            'zero_pct' => 0,
                            'one' => 0,
                            'one_pct' => 0,
                            'two' => 0,
                            'two_pct' => 0,
                            'full' => 0,
                            'full_pct' => 0,
                        ];
                    }
                }
            } else {
                $jobMatrixStats = [
                    'total_employees' => 0,
                    'total_assessors' => 0,
                    'zero' => 0,
                    'zero_pct' => 0,
                    'one' => 0,
                    'one_pct' => 0,
                    'two' => 0,
                    'two_pct' => 0,
                    'full' => 0,
                    'full_pct' => 0,
                ];
            }
        }

        // 2. DATA UNTUK TAB DETAIL ("Siapa Menilai Siapa")
        $detailQuery = Assessment::with([
            'group',
            'assessor.employee',
            'job',
            'employee',
            'creator'
        ])->orderByDesc('id');

        $detailGroupId = $request->input('detail_group_id', ($request->has('detail_group_id') ? null : $request->input('group_id', 'all')));

        if ($isKetuaOnly) {
            $detailQuery->where('group_id', $user->group_id);
            $detailGroupId = $user->group_id;
        } elseif (!empty($detailGroupId) && $detailGroupId !== 'all') {
            $detailQuery->where('group_id', $detailGroupId);
        } else {
            $detailGroupId = 'all';
        }

        if ($request->filled('job_id_filter')) {
            $detailQuery->where('job_id', $request->input('job_id_filter'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $detailQuery->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('fullname', 'like', "%{$search}%")
                       ->orWhere('nrp', 'like', "%{$search}%");
                })->orWhereHas('assessor.employee', function ($aq) use ($search) {
                    $aq->where('fullname', 'like', "%{$search}%")
                       ->orWhere('nrp', 'like', "%{$search}%");
                });
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100, 250, 500])) {
            $perPage = 25;
        }

        $detailAssessments = $detailQuery->paginate($perPage)->withQueryString();
        $totalAllAssessments = Assessment::count();

        $detailJobs = ($detailGroupId !== 'all' && $detailGroupId)
            ? Job::where('group_id', $detailGroupId)->orderBy('name')->get()
            : Job::orderBy('name')->get()->unique('name');

        // 3. DATA UNTUK TAB RINGKASAN CAPAIAN
        $groupsSummary = $allGroups->map(function ($g) {
            $g->calculated_progress = $g->getProgressData();
            return $g;
        });

        // Hitung Data Rincian Progres Area Terpilih (10 Jabatan & 28 Karyawan beserta keterangan Penilai)
        $selectedGroupProgress = $selectedGroup ? $selectedGroup->getProgressData() : null;
        $jobSummaries = collect();
        $employeeSummaries = collect();
        $assessorSummaries = collect();

        if ($selectedGroup) {
            $totalAssessors = $selectedGroup->assessors->count();
            $totalTargetEmployees = $selectedGroup->targetEmployees->count();
            $totalJobs = $selectedGroup->jobs->count();

            // Ambil semua penilaian untuk kelompok ini sekali saja ke memori
            $allGroupAssessments = Assessment::with('assessor.employee')
                ->where('group_id', $selectedGroup->id)
                ->get();

            // 3.A Rincian Progres per Posisi Jabatan
            $jobSummaries = $selectedGroup->jobs->map(function ($job) use ($selectedGroup, $allGroupAssessments, $totalAssessors, $totalTargetEmployees) {
                $targetEmps = $job->target_employees !== null ? $job->target_employees : $totalTargetEmployees;
                $targetUnits = $targetEmps;
                if ($selectedGroup->custom_target !== null) {
                    $targetUnits = $selectedGroup->custom_target;
                }

                $checkedCount = $allGroupAssessments->where('job_id', $job->id)->count();
                $pct = $targetUnits > 0 ? min(100, round(($checkedCount / $targetUnits) * 100, 1)) : 0;

                $status = 'Belum Dinilai';
                $color = 'slate';
                if ($pct >= 100) {
                    $status = 'Selesai 100%';
                    $color = 'emerald';
                } elseif ($pct > 0) {
                    $status = 'Sedang Berjalan';
                    $color = 'amber';
                }

                return [
                    'id' => $job->id,
                    'name' => $job->name,
                    'sort_order' => $job->sort_order,
                    'target_employees' => $targetEmps,
                    'checked_units' => $checkedCount,
                    'target_units' => $targetUnits,
                    'percentage' => $pct,
                    'status' => $status,
                    'color' => $color,
                ];
            });

            // 3.B Rincian Status Evaluasi per Karyawan Sasaran & Keterangan Siapa Saja yang Menilai
            $expectedPerEmp = $totalJobs;

            $employeeSummaries = $selectedGroup->targetEmployees->map(function ($emp) use ($allGroupAssessments, $selectedGroup, $totalJobs, $expectedPerEmp) {
                $empAssessments = $allGroupAssessments->where('employee_id', $emp->id);
                $checkedCount = $empAssessments->count();

                // Group penilaian berdasarkan penilai untuk mengetahui siapa saja yang menilai
                $assessorGrouped = $empAssessments->groupBy('assessor_id');
                $assessorDetails = [];

                foreach ($selectedGroup->assessors as $assessor) {
                    if ($assessorGrouped->has($assessor->id)) {
                        $cnt = $assessorGrouped->get($assessor->id)->count();
                        $assessorDetails[] = [
                            'assessor_id' => $assessor->id,
                            'name' => $assessor->employee ? $assessor->employee->fullname : 'Penilai',
                            'is_leader' => (bool)$assessor->is_leader,
                            'checked_count' => $cnt,
                            'total_jobs' => $totalJobs,
                            'is_full' => ($cnt >= $totalJobs && $totalJobs > 0),
                        ];
                    }
                }

                $pct = $expectedPerEmp > 0 ? min(100, round(($checkedCount / $expectedPerEmp) * 100, 1)) : 0;
                $status = 'Belum Dinilai';
                $color = 'slate';
                if ($checkedCount >= $expectedPerEmp && $expectedPerEmp > 0) {
                    $status = 'Lengkap (100%)';
                    $color = 'emerald';
                } elseif ($checkedCount > 0) {
                    $status = 'Sebagian';
                    $color = 'amber';
                }

                return [
                    'id' => $emp->id,
                    'nrp' => $emp->nrp,
                    'fullname' => $emp->fullname,
                    'dept' => $emp->dept,
                    'section' => $emp->section,
                    'plant' => $emp->plant,
                    'checked_count' => $checkedCount,
                    'expected_count' => $expectedPerEmp,
                    'percentage' => $pct,
                    'status' => $status,
                    'color' => $color,
                    'assessors' => $assessorDetails,
                    'assessor_count' => count($assessorDetails),
                ];
            });

            // 3.C Rincian Kontribusi Tiap Penilai
            $assessorSummaries = $selectedGroup->assessors->map(function ($assessor) {
                return [
                    'assessor' => $assessor,
                    'progress' => $assessor->getProgressData(),
                ];
            });
        }

        return view('wola.reports.index', compact(
            'tab',
            'allGroups',
            'selectedGroup',
            'groupId',
            'isAllGroups',
            'isKetuaOnly',
            'isAdminOrSuper',
            'jobs',
            'selectedJob',
            'isAllJobs',
            'singleGroupAllJobsData',
            'matrixAssessors',
            'matrixEmployees',
            'matrixData',
            'allGroupsMatrixData',
            'globalMatrixStats',
            'detailAssessments',
            'detailGroupId',
            'detailJobs',
            'groupsSummary',
            'selectedGroupProgress',
            'jobSummaries',
            'employeeSummaries',
            'assessorSummaries',
            'jobMatrixStats',
            'dashboardData',
            'perPage',
            'totalAllAssessments'
        ));
    }

    /**
     * Ekspor Laporan Hasil Monitoring Dashboard Admin ke Format Excel (.xlsx)
     * Khusus Superadmin dan Admin
     */
    public function exportDashboardExcel(Request $request)
    {
        if (!Auth::user()->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Hanya Superadmin dan Admin yang memiliki hak akses untuk mengunduh laporan Excel hasil dashboard admin.');
        }

        $filename = 'Laporan_Dashboard_WOLA_PT_Musashi_' . date('Ymd_His') . '.xlsx';

        ActivityLog::log(
            'export_dashboard_excel',
            "Mengunduh file laporan Excel Dashboard & Chart Progres WOLA ({$filename})"
        );

        return Excel::download(
            new AdminDashboardExport(),
            $filename
        );
    }

    /**
     * Ekspor Laporan ke Format Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $isKetuaOnly = $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin']);

        $groupId = $isKetuaOnly ? $user->group_id : $request->input('group_id');
        $jobId = $request->input('job_id');
        $assessorId = $request->input('assessor_id');
        $employeeId = $request->input('employee_id');

        $cleanGroupId = ($groupId && $groupId !== 'all' && is_numeric($groupId)) ? (int)$groupId : null;
        $groupLabel = $cleanGroupId ? 'Grup_' . $cleanGroupId : 'Seluruh_Kelompok';
        $filename = 'Laporan_WOLA_PT_Musashi_' . $groupLabel . '_' . date('Ymd_His') . '.xlsx';

        ActivityLog::log(
            'report_export_excel',
            "Mengunduh file laporan Excel WOLA ({$filename})",
            ['group_id' => $groupId, 'job_id' => $jobId],
            $cleanGroupId
        );

        return Excel::download(
            new WolaReportExport($cleanGroupId, $jobId, $assessorId, $employeeId),
            $filename
        );
    }

    /**
     * Tampilan Khusus Cetak Ramah Kertas / PDF (Print Preview)
     */
    public function print(Request $request)
    {
        $user = Auth::user();
        $isKetuaOnly = $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin']);

        if ($isKetuaOnly) {
            $groupId = $user->group_id;
            $group = $groupId ? Group::with(['leader', 'assessors.employee', 'jobs', 'targetEmployees'])->findOrFail($groupId) : null;
            $allGroups = $group ? collect([$group]) : collect();
        } else {
            $groupId = $request->input('group_id');
            $cleanGroupId = ($groupId && $groupId !== 'all' && is_numeric($groupId)) ? (int)$groupId : null;
            $group = $cleanGroupId ? Group::with(['leader', 'assessors.employee', 'jobs', 'targetEmployees'])->find($cleanGroupId) : null;
            $allGroups = Group::with(['leader', 'assessors', 'jobs', 'groupEmployees'])->orderBy('number')->get();
        }

        $jobId = $request->input('job_id');
        $selectedJob = ($group && $jobId && $jobId !== 'all') ? $group->jobs->firstWhere('id', $jobId) : null;

        // Hitung progres
        $summary = [];
        if ($group) {
            $summary = $group->getProgressData();
        }

        $cleanLogGroupId = ($groupId && $groupId !== 'all' && is_numeric($groupId)) ? (int)$groupId : null;
        ActivityLog::log(
            'report_print',
            "Mencetak / Print preview laporan WOLA untuk " . ($group ? $group->name : 'Seluruh Kelompok'),
            ['group_id' => $groupId],
            $cleanLogGroupId
        );

        return view('wola.reports.print', compact(
            'group',
            'allGroups',
            'selectedJob',
            'summary'
        ));
    }

    /**
     * Hapus Semua Data Checklist Penilaian WOLA (Khusus Superadmin)
     */
    public function destroyAllAssessments(Request $request)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Hanya Superadmin yang memiliki hak akses untuk menghapus data checklist penilaian WOLA.');
        }

        $scope = $request->input('scope', 'all'); // 'all' atau 'group'
        $groupId = $request->input('detail_group_id');
        $perPage = (int) $request->input('per_page', 25);

        $query = Assessment::query();
        $commentQuery = JobComment::query();
        $scopeLabel = 'Seluruh Kelompok (Semua Area 1-12)';

        if ($scope === 'group' && !empty($groupId) && $groupId !== 'all') {
            $group = Group::find($groupId);
            if ($group) {
                $query->where('group_id', $group->id);
                $commentQuery->where('group_id', $group->id);
                $scopeLabel = "Kelompok {$group->number}: {$group->name}";
            }
        }

        $totalDeleted = $query->count();
        $totalCommentsDeleted = $commentQuery->count();

        if ($totalDeleted === 0 && $totalCommentsDeleted === 0) {
            return redirect()->route('wola.reports.index', [
                'tab' => 'details',
                'detail_group_id' => ($scope === 'group' ? $groupId : 'all'),
                'per_page' => $perPage,
            ])->with('error', "Tidak ada data checklist penilaian yang ditemukan untuk {$scopeLabel}.");
        }

        // Jalankan penghapusan dalam transaksi database
        DB::transaction(function () use ($query, $commentQuery) {
            $query->delete();
            $commentQuery->delete();
        });

        // Audit log aktivitas
        ActivityLog::log(
            'PURGE_ASSESSMENTS',
            "Superadmin membersihkan checklist penilaian WOLA untuk {$scopeLabel} ({$totalDeleted} checklist & {$totalCommentsDeleted} catatan evaluasi dihapus).",
            [
                'scope' => $scope,
                'group_id' => $groupId,
                'deleted_assessments_count' => $totalDeleted,
                'deleted_comments_count' => $totalCommentsDeleted,
                'purged_by' => Auth::user()->name,
            ]
        );

        // Auto-sync pembersihan data ke Google Spreadsheet jika terkonfigurasi
        try {
            app(GoogleSheetService::class)->bulkSyncAssessmentsToSheet();
        } catch (\Throwable $e) {
            Log::warning("Auto-sync Google Sheets setelah pembersihan data penilaian: " . $e->getMessage());
        }

        return redirect()->route('wola.reports.index', [
            'tab' => 'details',
            'detail_group_id' => ($scope === 'group' ? $groupId : 'all'),
            'per_page' => $perPage,
        ])->with('success', "Berhasil menghapus {$totalDeleted} data checklist penilaian WOLA ({$scopeLabel}).");
    }
}
