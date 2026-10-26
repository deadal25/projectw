<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAssessor;
use App\Models\Job;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\JobComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard Administrator & Superadmin
     */
    public function adminDashboard(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'all');
        $sortBy = $request->input('sort', 'number_asc');

        $allGroups = Group::with([
            'leader',
            'assessors.employee',
            'jobs',
            'groupEmployees',
            'targetEmployees'
        ])->orderBy('number')->get();

        // 1. Kalkulasi Metrik Global
        $totalTarget = 0;
        $totalChecked = 0;
        $completedCount = 0;
        $laggingCount = 0;
        $inProgressCount = 0;
        $notStartedCount = 0;

        $groupsWithProgress = $allGroups->map(function ($group) use (
            &$totalTarget,
            &$totalChecked,
            &$completedCount,
            &$laggingCount,
            &$inProgressCount,
            &$notStartedCount
        ) {
            $prog = $group->getProgressData();
            $totalTarget += $prog['target'];
            $totalChecked += $prog['checked'];

            if ($prog['status'] === 'Selesai') {
                $completedCount++;
            } elseif ($prog['status'] === 'Tertinggal') {
                $laggingCount++;
            } elseif ($prog['status'] === 'Berjalan') {
                $inProgressCount++;
            } else {
                $notStartedCount++;
            }

            $group->calculated_progress = $prog;
            return $group;
        });

        $overallPercentage = $totalTarget > 0 ? round(($totalChecked / $totalTarget) * 100, 1) : 0;

        // Ambil seluruh rekaman penilaian di database (semua kelompok) dengan relasi lengkap
        $allAssessments = Assessment::with(['assessor.employee', 'job', 'employee'])->get();

        // 2. Data untuk Chart.js (Grafik Karyawan Target Dinilai Antar Kelompok)
        $chartLabels = [];
        $chartLabelsShort = [];
        $chartData = [];
        $chartColors = [];
        $chartEmpAssessed = [];
        $chartEmpTotal = [];
        $chartEmpDataLabels = [];

        // Data untuk Chart.js (Grafik Posisi Job Target Dinilai Antar Kelompok)
        $chartJobData = [];
        $chartJobColors = [];
        $chartJobAssessed = [];
        $chartJobTotal = [];
        $chartJobDataLabels = [];

        foreach ($groupsWithProgress as $g) {
            $chartLabels[] = 'K' . $g->number . ': ' . $g->name;
            $chartLabelsShort[] = 'K' . $g->number;
            $percentage = $g->calculated_progress['percentage'];
            $chartData[] = $percentage;

            $empAssessed = $g->calculated_progress['assessed_employees_count'] ?? 0;
            $empTotal = $g->calculated_progress['employee_count'] ?? 0;
            $chartEmpAssessed[] = $empAssessed;
            $chartEmpTotal[] = $empTotal;
            $chartEmpDataLabels[] = $percentage . '% (' . $empAssessed . '/' . $empTotal . ' Org)';

            $statusColor = $g->calculated_progress['status_color'] ?? 'slate';
            if ($statusColor === 'green') {
                $chartColors[] = '#10B981'; // Hijau Selesai
            } elseif ($statusColor === 'blue') {
                $chartColors[] = '#3B82F6'; // Biru Berjalan
            } elseif ($statusColor === 'red') {
                $chartColors[] = '#F43F5E'; // Merah Tertinggal
            } else {
                $chartColors[] = '#94A3B8'; // Abu Belum Mulai
            }

            // Metrik Posisi Job untuk Kelompok ini
            $gTotalJobs = $g->calculated_progress['job_count'] ?? $g->jobs()->count();
            $gAssessedJobs = $allAssessments->where('group_id', $g->id)->pluck('job_id')->unique()->count();
            $gJobPct = $gTotalJobs > 0 ? round(($gAssessedJobs / $gTotalJobs) * 100, 1) : 0;

            $chartJobData[] = $gJobPct;
            $chartJobAssessed[] = $gAssessedJobs;
            $chartJobTotal[] = $gTotalJobs;
            $chartJobDataLabels[] = $gJobPct . '% (' . $gAssessedJobs . '/' . $gTotalJobs . ' Job)';

            if ($gJobPct >= 100) {
                $chartJobColors[] = '#10B981'; // Hijau 100% Selesai
            } elseif ($gJobPct > 0) {
                $chartJobColors[] = '#8B5CF6'; // Ungu Aktif Dinilai
            } else {
                $chartJobColors[] = '#CBD5E1'; // Abu Belum Mulai
            }
        }

        // 3. Filter & Sortir Kelompok untuk Tampilan Grid
        $filteredGroups = $groupsWithProgress;

        if ($search) {
            $filteredGroups = $filteredGroups->filter(function ($g) use ($search) {
                $leaderName = $g->leader ? $g->leader->name : '';
                return str_contains(strtolower($g->name), strtolower($search)) ||
                       str_contains(strtolower($leaderName), strtolower($search)) ||
                       $g->number == $search;
            });
        }

        if ($statusFilter !== 'all') {
            $filteredGroups = $filteredGroups->filter(function ($g) use ($statusFilter) {
                if ($statusFilter === 'completed') return $g->calculated_progress['status'] === 'Selesai';
                if ($statusFilter === 'lagging') return $g->calculated_progress['status'] === 'Tertinggal';
                if ($statusFilter === 'in_progress') return $g->calculated_progress['status'] === 'Berjalan';
                if ($statusFilter === 'not_started') return in_array($g->calculated_progress['status'], ['Belum Mulai', 'Belum mulai']);
                return true;
            });
        }

        if ($sortBy === 'progress_desc') {
            $filteredGroups = $filteredGroups->sortByDesc(fn($g) => $g->calculated_progress['percentage']);
        } elseif ($sortBy === 'progress_asc') {
            $filteredGroups = $filteredGroups->sortBy(fn($g) => $g->calculated_progress['percentage']);
        } elseif ($sortBy === 'name_asc') {
            $filteredGroups = $filteredGroups->sortBy('name');
        } else {
            $filteredGroups = $filteredGroups->sortBy('number');
        }

        // 4. Kalkulasi Metrik Agregat Riil Seluruh Kelompok (Monitoring Admin)
        $adminTotalAssessors = $groupsWithProgress->sum(fn($g) => $g->calculated_progress['assessor_count'] ?? 0);
        $adminTotalJobs = $groupsWithProgress->sum(fn($g) => $g->calculated_progress['job_count'] ?? 0);
        $adminTotalTargetEmployees = $groupsWithProgress->sum(fn($g) => $g->calculated_progress['employee_count'] ?? 0);

        // 1. Karyawan yang telah dinilai di seluruh kelompok (unik per kelompok)
        $adminAssessedEmployeesCount = $groupsWithProgress->sum(function($g) use ($allAssessments) {
            return $allAssessments->where('group_id', $g->id)->pluck('employee_id')->unique()->count();
        });
        $adminEmployeeProgressPercentage = $adminTotalTargetEmployees > 0 
            ? round(($adminAssessedEmployeesCount / $adminTotalTargetEmployees) * 100, 1) 
            : 0;

        // 2. Total posisi jabatan unik yang telah dinilai di seluruh kelompok
        $adminAssessedJobsCount = $groupsWithProgress->sum(function($g) use ($allAssessments) {
            return $allAssessments->where('group_id', $g->id)->pluck('job_id')->unique()->count();
        });
        $adminJobPct = $adminTotalJobs > 0 
            ? round(($adminAssessedJobsCount / $adminTotalJobs) * 100, 1) 
            : 0;

        // 3. Total verifikasi checklist riil dari seluruh kelompok
        $adminVerifiedChecklists = $allAssessments->count();
        $adminTargetChecklists = $totalTarget;
        $adminChecklistPercentage = $adminTargetChecklists > 0 
            ? round(($adminVerifiedChecklists / $adminTargetChecklists) * 100, 1) 
            : 0;

        // 4. Karyawan yang lengkap dinilai oleh seluruh penilai pada kelompok masing-masing
        $adminFullAssessedEmployeesCount = 0;
        foreach ($groupsWithProgress as $g) {
            $numAssessors = $g->calculated_progress['assessor_count'] ?? 0;
            if ($numAssessors <= 0) continue;

            $groupAssessments = $allAssessments->where('group_id', $g->id);
            $empMap = [];
            foreach ($g->groupEmployees as $ge) {
                $empMap[$ge->employee_id] = [];
            }
            foreach ($groupAssessments as $a) {
                if (isset($empMap[$a->employee_id])) {
                    $empMap[$a->employee_id][$a->assessor_id] = true;
                }
            }
            foreach ($empMap as $eId => $assList) {
                if (count($assList) >= $numAssessors) {
                    $adminFullAssessedEmployeesCount++;
                }
            }
        }
        $adminFullAssessedPercentage = $adminTotalTargetEmployees > 0 
            ? round(($adminFullAssessedEmployeesCount / $adminTotalTargetEmployees) * 100, 1) 
            : 0;

        // Status rule monitoring admin (Belum Mulai, Tertinggal, Berjalan, Selesai)
        if ($adminAssessedEmployeesCount === 0) {
            $adminEmployeeProgressStatus = 'Belum Mulai';
            $adminEmployeeStatusColor = 'slate';
        } elseif ($adminAssessedEmployeesCount >= $adminTotalTargetEmployees || $adminEmployeeProgressPercentage >= 100) {
            $adminEmployeeProgressStatus = 'Selesai';
            $adminEmployeeStatusColor = 'green';
        } elseif ($adminAssessedEmployeesCount < 5) {
            $adminEmployeeProgressStatus = 'Tertinggal';
            $adminEmployeeStatusColor = 'red';
        } else {
            $adminEmployeeProgressStatus = 'Berjalan';
            $adminEmployeeStatusColor = 'blue';
        }

        // 5. Daftar Karyawan & Posisi Job Sudah Dinilai Lintas Seluruh Kelompok (Untuk Tabel Admin & Super Admin)
        $adminAssessedEmployeesList = collect();
        $adminAssessedJobsList = collect();

        foreach ($allGroups as $g) {
            $groupAssessments = $allAssessments->where('group_id', $g->id);
            if ($groupAssessments->isEmpty()) {
                continue;
            }

            $gJobs = $g->jobs;
            $gTotalAssessors = $g->assessors->count();
            $gTargetEmployees = $g->targetEmployees;

            // Karyawan dinilai dalam kelompok ini
            $empGrouped = $groupAssessments->groupBy('employee_id');
            foreach ($empGrouped as $empId => $empAssessments) {
                $employee = $empAssessments->first()->employee ?? $gTargetEmployees->firstWhere('id', $empId);
                if (!$employee) continue;

                $assessedJobsCount = $empAssessments->pluck('job_id')->unique()->count();
                $distinctAssessors = $empAssessments->map(function ($a) {
                    return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
                })->unique()->values();

                $distinctJobs = $empAssessments->groupBy('job_id')->map(function ($jobAss) {
                    $first = $jobAss->first();
                    $job = $first->job;
                    if (!$job) return null;

                    $assessors = $jobAss->map(function ($a) {
                        return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
                    })->unique()->values();

                    return (object)[
                        'id' => $job->id,
                        'name' => $job->name,
                        'checklists_count' => $jobAss->count(),
                        'assessors' => $assessors,
                    ];
                })->filter()->values();

                $allJobsFullyAssessed = $distinctJobs->isNotEmpty() && $distinctJobs->every(function ($j) use ($gTotalAssessors) {
                    return count($j->assessors) >= $gTotalAssessors;
                });
                $isFull = ($gTotalAssessors > 0 && ($allJobsFullyAssessed || $distinctAssessors->count() >= $gTotalAssessors));

                $adminAssessedEmployeesList->push((object)[
                    'group' => $g,
                    'group_id' => $g->id,
                    'group_number' => $g->number,
                    'group_name' => $g->name,
                    'employee' => $employee,
                    'assessed_jobs_count' => $assessedJobsCount,
                    'group_jobs_count' => $gJobs->count(),
                    'group_assessors_count' => $gTotalAssessors,
                    'distinct_assessors' => $distinctAssessors,
                    'distinct_jobs' => $distinctJobs,
                    'total_checklists' => $empAssessments->count(),
                    'is_full' => $isFull,
                ]);
            }

            // Job dinilai dalam kelompok ini
            $jobGrouped = $groupAssessments->groupBy('job_id');
            foreach ($jobGrouped as $jobId => $jobAssessments) {
                $job = $jobAssessments->first()->job ?? $gJobs->firstWhere('id', $jobId);
                if (!$job) continue;

                $assessedEmpsCount = $jobAssessments->pluck('employee_id')->unique()->count();
                $distinctAssessors = $jobAssessments->map(function ($a) {
                    return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
                })->unique()->values();

                $distinctEmployees = $jobAssessments->groupBy('employee_id')->map(function ($empAss) {
                    $first = $empAss->first();
                    $emp = $first->employee;
                    if (!$emp) return null;

                    $assessors = $empAss->map(function ($a) {
                        return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
                    })->unique()->values();

                    return (object)[
                        'id' => $emp->id,
                        'nrp' => $emp->nrp,
                        'fullname' => $emp->fullname,
                        'checklists_count' => $empAss->count(),
                        'assessors' => $assessors,
                    ];
                })->filter()->values();

                $targetCount = $job->target_employees ?? $gTargetEmployees->count();
                $pct = $targetCount > 0 ? round(($assessedEmpsCount / $targetCount) * 100, 1) : 0;

                $adminAssessedJobsList->push((object)[
                    'group' => $g,
                    'group_id' => $g->id,
                    'group_number' => $g->number,
                    'group_name' => $g->name,
                    'job' => $job,
                    'assessed_employees_count' => $assessedEmpsCount,
                    'target_count' => $targetCount,
                    'percentage' => $pct,
                    'distinct_assessors' => $distinctAssessors,
                    'distinct_employees' => $distinctEmployees,
                    'total_checklists' => $jobAssessments->count(),
                ]);
            }
        }

        return view('wola.admin.dashboard', compact(
            'filteredGroups',
            'allGroups',
            'totalTarget',
            'totalChecked',
            'overallPercentage',
            'completedCount',
            'laggingCount',
            'inProgressCount',
            'notStartedCount',
            'chartLabels',
            'chartLabelsShort',
            'chartData',
            'chartColors',
            'chartEmpAssessed',
            'chartEmpTotal',
            'chartEmpDataLabels',
            'chartJobData',
            'chartJobColors',
            'chartJobAssessed',
            'chartJobTotal',
            'chartJobDataLabels',
            'search',
            'statusFilter',
            'sortBy',
            'adminTotalAssessors',
            'adminTotalJobs',
            'adminTotalTargetEmployees',
            'adminAssessedEmployeesCount',
            'adminEmployeeProgressPercentage',
            'adminEmployeeProgressStatus',
            'adminEmployeeStatusColor',
            'adminAssessedJobsCount',
            'adminJobPct',
            'adminVerifiedChecklists',
            'adminTargetChecklists',
            'adminChecklistPercentage',
            'adminFullAssessedEmployeesCount',
            'adminFullAssessedPercentage',
            'adminAssessedEmployeesList',
            'adminAssessedJobsList'
        ));
    }

    /**
     * Dashboard Ketua Kelompok (dengan Heatmap Karyawan × Jabatan)
     */
    public function ketuaDashboard(Request $request)
    {
        $user = Auth::user();
        $isKetuaOnly = $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin']);

        // Tentukan kelompok:
        // Jika user adalah KETUA: Terkunci HANYA pada kelompok kerjanya sendiri!
        // Dilarang keras melihat atau beralih ke kelompok ketua lain.
        if ($isKetuaOnly) {
            $group = $user->group ?? ($user->group_id ? Group::find($user->group_id) : null);
            if (!$group) {
                abort(403, 'Akun Ketua Anda belum ditautkan ke kelompok kerja manapun.');
            }
            $allGroups = collect([$group]);
        } else {
            // Jika Administrator (Superadmin / Admin) mengakses dashboard ketua:
            $groupId = $request->input('group_id');
            $group = null;
            if ($groupId) {
                $group = Group::find($groupId);
            }
            if (!$group) {
                $group = $user->group ?? Group::orderBy('number')->first();
            }
            if (!$group) {
                abort(404, 'Data kelompok tidak ditemukan.');
            }
            $allGroups = Group::orderBy('number')->get();
        }

        $group->load([
            'leader',
            'assessors.employee',
            'jobs' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'targetEmployees' => function ($q) {
                $q->orderBy('employees.nrp');
            }
        ]);

        $groupProgress = $group->getProgressData();
        $assessors = $group->assessors;
        $totalAssessors = $assessors->count();
        $jobs = $group->jobs;
        $targetEmployees = $group->targetEmployees;

        // Ambil seluruh data penilaian di kelompok ini beserta nama penilainya, jabatan, dan karyawannya
        $assessments = Assessment::with(['assessor.employee', 'job', 'employee'])
            ->where('group_id', $group->id)
            ->get();

        // Bangun matriks Heatmap: [emp_id][job_id] => [ 'count' => X, 'names' => [...] ]
        $heatmapMatrix = [];
        foreach ($assessments as $a) {
            $empId = $a->employee_id;
            $jobId = $a->job_id;
            $assessorName = $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';

            if (!isset($heatmapMatrix[$empId])) {
                $heatmapMatrix[$empId] = [];
            }
            if (!isset($heatmapMatrix[$empId][$jobId])) {
                $heatmapMatrix[$empId][$jobId] = [
                    'count' => 0,
                    'assessors' => [],
                ];
            }

            $heatmapMatrix[$empId][$jobId]['count']++;
            $heatmapMatrix[$empId][$jobId]['assessors'][] = $assessorName;
        }

        // Ringkasan heatmap sel (hanya hitung jabatan yang aktif dinilai)
        $activeJobs = $jobs->where('is_active', true);
        $totalCells = $activeJobs->count() * $targetEmployees->count();
        $completedCells = 0; // Semua penilai sudah menilai
        $partialCells = 0;   // Sebagian penilai sudah menilai
        $unassessedCells = 0; // Belum dinilai sama sekali

        foreach ($targetEmployees as $emp) {
            foreach ($activeJobs as $job) {
                $count = $heatmapMatrix[$emp->id][$job->id]['count'] ?? 0;
                if ($count >= $totalAssessors && $totalAssessors > 0) {
                    $completedCells++;
                } elseif ($count > 0) {
                    $partialCells++;
                } else {
                    $unassessedCells++;
                }
            }
        }

        // 1. Perhitungan Capaian Progres Berbasis Karyawan Target (Permintaan User)
        // Dihitung dari karyawan target: jika penilai A sudah menilai salah satu karyawan maka dihitung 1,
        // jika penilai B menilai karyawan yang sama tetap dihitung 1.
        $totalTargetEmployees = $targetEmployees->count();
        $assessedEmployeeIds = $assessments->pluck('employee_id')->unique();
        $assessedEmployeesCount = $assessedEmployeeIds->count();
        $unassessedEmployeesCount = max(0, $totalTargetEmployees - $assessedEmployeesCount);
        $employeeProgressPercentage = $totalTargetEmployees > 0 
            ? round(($assessedEmployeesCount / $totalTargetEmployees) * 100, 1) 
            : 0;

        // Tentukan Status: Belum Mulai (0), Tertinggal (<5), Berjalan (5..27), Selesai (full)
        if ($assessedEmployeesCount === 0) {
            $employeeProgressStatus = 'Belum Mulai';
            $employeeStatusColor = 'slate';
        } elseif ($employeeProgressPercentage >= 100 || ($totalTargetEmployees > 0 && $assessedEmployeesCount >= $totalTargetEmployees)) {
            $employeeProgressStatus = 'Selesai';
            $employeeStatusColor = 'green';
        } elseif ($assessedEmployeesCount < 5) {
            $employeeProgressStatus = 'Tertinggal';
            $employeeStatusColor = 'red';
        } else {
            $employeeProgressStatus = 'Berjalan';
            $employeeStatusColor = 'blue';
        }

        // 2. Metrik Hasil Evaluasi Tambahan (4 Card Baru Hasil Evaluasi)
        $totalVerifiedAssessments = $assessments->count();
        $uniqueUnitsCount = $assessments->map(fn($a) => $a->job_id . '_' . $a->employee_id)->unique()->count();
        $assessedJobsCount = $assessments->pluck('job_id')->unique()->count();
        $totalCommentsCount = JobComment::where('group_id', $group->id)->count();

        // Rincian capaian masing-masing penilai (contoh: Nanang: 9, Dodi: 5, Kasimin: 14)
        $assessorsBreakdown = $assessors->map(function($a) use ($assessments) {
            $firstName = $a->employee ? explode(' ', trim($a->employee->fullname))[0] : 'Penilai';
            $count = $assessments->where('assessor_id', $a->id)->count();
            return "$firstName: $count";
        })->join(', ');

        // Hitung karyawan yang sudah lengkap diceklis oleh seluruh penilai (misal 3 penilai)
        $empAssessorsMap = [];
        foreach ($targetEmployees as $emp) {
            $empAssessorsMap[$emp->id] = [];
        }
        foreach ($assessments as $a) {
            if (isset($empAssessorsMap[$a->employee_id])) {
                $empAssessorsMap[$a->employee_id][$a->assessor_id] = true;
            }
        }
        $fullAssessedEmployeesCount = 0;
        foreach ($empAssessorsMap as $eId => $assList) {
            if ($totalAssessors > 0 && count($assList) >= $totalAssessors) {
                $fullAssessedEmployeesCount++;
            }
        }
        $fullAssessedPercentage = $totalTargetEmployees > 0 
            ? round(($fullAssessedEmployeesCount / $totalTargetEmployees) * 100, 1) 
            : 0;

        // 3. Daftar Detail untuk Dua Tabel Monitoring Baru (Karyawan & Job yang Sudah Dinilai)
        $assessedEmployeesList = $targetEmployees->filter(function ($emp) use ($assessments) {
            return $assessments->where('employee_id', $emp->id)->count() > 0;
        })->map(function ($emp) use ($assessments, $jobs, $totalAssessors) {
            $empAssessments = $assessments->where('employee_id', $emp->id);
            $assessedJobsCount = $empAssessments->pluck('job_id')->unique()->count();
            $distinctAssessors = $empAssessments->map(function ($a) {
                return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
            })->unique()->values();

            $distinctJobs = $empAssessments->groupBy('job_id')->map(function ($jobAssessments) {
                $first = $jobAssessments->first();
                $job = $first->job;
                if (!$job) return null;

                $assessors = $jobAssessments->map(function ($a) {
                    return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
                })->unique()->values();

                return (object)[
                    'id' => $job->id,
                    'code' => $job->code ?? ('J' . str_pad((string)$job->sort_order, 2, '0', STR_PAD_LEFT)),
                    'name' => $job->name,
                    'checklists_count' => $jobAssessments->count(),
                    'assessors' => $assessors,
                ];
            })->filter()->values();

            $allJobsFullyAssessed = $distinctJobs->isNotEmpty() && $distinctJobs->every(function ($j) use ($totalAssessors) {
                return count($j->assessors) >= $totalAssessors;
            });
            $isFull = ($totalAssessors > 0 && ($allJobsFullyAssessed || $distinctAssessors->count() >= $totalAssessors));

            return (object) [
                'employee' => $emp,
                'assessed_jobs_count' => $assessedJobsCount,
                'group_assessors_count' => $totalAssessors,
                'distinct_assessors' => $distinctAssessors,
                'distinct_jobs' => $distinctJobs,
                'total_checklists' => $empAssessments->count(),
                'is_full' => $isFull,
            ];
        })->values();

        $assessedJobsList = $jobs->filter(function ($job) use ($assessments) {
            return $assessments->where('job_id', $job->id)->count() > 0;
        })->map(function ($job) use ($assessments, $targetEmployees) {
            $jobAssessments = $assessments->where('job_id', $job->id);
            $assessedEmpsCount = $jobAssessments->pluck('employee_id')->unique()->count();
            $distinctAssessors = $jobAssessments->map(function ($a) {
                return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
            })->unique()->values();

            $distinctEmployees = $jobAssessments->groupBy('employee_id')->map(function ($empAssessments) {
                $first = $empAssessments->first();
                $employee = $first->employee;
                if (!$employee) return null;

                $assessors = $empAssessments->map(function ($a) {
                    return $a->assessor && $a->assessor->employee ? $a->assessor->employee->fullname : 'Penilai';
                })->unique()->values();

                return (object)[
                    'id' => $employee->id,
                    'nrp' => $employee->nrp,
                    'fullname' => $employee->fullname,
                    'checklists_count' => $empAssessments->count(),
                    'assessors' => $assessors,
                ];
            })->filter()->values();

            $targetCount = $job->target_employees ?? $targetEmployees->count();
            $pct = $targetCount > 0 ? round(($assessedEmpsCount / $targetCount) * 100, 1) : 0;

            return (object) [
                'job' => $job,
                'assessed_employees_count' => $assessedEmpsCount,
                'target_count' => $targetCount,
                'percentage' => $pct,
                'distinct_assessors' => $distinctAssessors,
                'distinct_employees' => $distinctEmployees,
                'total_checklists' => $jobAssessments->count(),
            ];
        })->values();

        return view('wola.ketua.dashboard', compact(
            'group',
            'groupProgress',
            'assessors',
            'totalAssessors',
            'assessorsBreakdown',
            'fullAssessedEmployeesCount',
            'fullAssessedPercentage',
            'jobs',
            'activeJobs',
            'targetEmployees',
            'totalTargetEmployees',
            'assessedEmployeesCount',
            'unassessedEmployeesCount',
            'employeeProgressPercentage',
            'employeeProgressStatus',
            'employeeStatusColor',
            'totalVerifiedAssessments',
            'uniqueUnitsCount',
            'assessedJobsCount',
            'totalCommentsCount',
            'heatmapMatrix',
            'totalCells',
            'completedCells',
            'partialCells',
            'unassessedCells',
            'allGroups',
            'isKetuaOnly',
            'assessedEmployeesList',
            'assessedJobsList'
        ));
    }
}
