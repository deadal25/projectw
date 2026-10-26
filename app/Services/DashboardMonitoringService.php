<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Assessment;

class DashboardMonitoringService
{
    /**
     * Mengambil dan mengkalkulasi seluruh data monitoring dashboard admin & superadmin
     *
     * @return array
     */
    public static function getAdminMonitoringData(): array
    {
        $allGroups = Group::with([
            'leader',
            'assessors.employee',
            'jobs',
            'groupEmployees',
            'targetEmployees'
        ])->orderBy('number')->get();

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
        $allAssessments = Assessment::with(['assessor.employee', 'job', 'employee'])->get();

        // Data untuk visualisasi grafik evaluasi & capaian (Chart.js & Excel)
        $chartLabels = [];
        $chartLabelsShort = [];
        $chartData = [];
        $chartColors = [];
        $chartEmpAssessed = [];
        $chartEmpTotal = [];
        $chartEmpDataLabels = [];

        $chartJobData = [];
        $chartJobColors = [];
        $chartJobAssessed = [];
        $chartJobTotal = [];
        $chartJobDataLabels = [];

        foreach ($groupsWithProgress as $g) {
            $chartLabels[] = 'K' . $g->number . ': ' . $g->name;
            $chartLabelsShort[] = 'K' . $g->number;
            $percentage = $g->calculated_progress['percentage'] ?? 0;
            $chartData[] = $percentage;

            $empAssessed = $g->calculated_progress['assessed_employees_count'] ?? 0;
            $empTotal = $g->calculated_progress['employee_count'] ?? 0;
            $chartEmpAssessed[] = $empAssessed;
            $chartEmpTotal[] = $empTotal;
            $chartEmpDataLabels[] = $percentage . '% (' . $empAssessed . '/' . $empTotal . ' Org)';

            $statusColor = $g->calculated_progress['status_color'] ?? 'slate';
            if ($statusColor === 'green') {
                $chartColors[] = '#10B981';
            } elseif ($statusColor === 'blue') {
                $chartColors[] = '#3B82F6';
            } elseif ($statusColor === 'red') {
                $chartColors[] = '#F43F5E';
            } else {
                $chartColors[] = '#94A3B8';
            }

            $gTotalJobs = $g->calculated_progress['job_count'] ?? $g->jobs()->count();
            $gAssessedJobs = $allAssessments->where('group_id', $g->id)->pluck('job_id')->unique()->count();
            $gJobPct = $gTotalJobs > 0 ? round(($gAssessedJobs / $gTotalJobs) * 100, 1) : 0;

            $chartJobData[] = $gJobPct;
            $chartJobAssessed[] = $gAssessedJobs;
            $chartJobTotal[] = $gTotalJobs;
            $chartJobDataLabels[] = $gJobPct . '% (' . $gAssessedJobs . '/' . $gTotalJobs . ' Job)';

            if ($gJobPct >= 100) {
                $chartJobColors[] = '#10B981';
            } elseif ($gJobPct > 0) {
                $chartJobColors[] = '#8B5CF6';
            } else {
                $chartJobColors[] = '#CBD5E1';
            }
        }

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

        // Status rule monitoring admin
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

        // 5. Daftar Karyawan & Posisi Job Sudah Dinilai Lintas Seluruh Kelompok
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

        return [
            'allGroups' => $allGroups,
            'groupsWithProgress' => $groupsWithProgress,
            'totalTarget' => $totalTarget,
            'totalChecked' => $totalChecked,
            'overallPercentage' => $overallPercentage,
            'completedCount' => $completedCount,
            'laggingCount' => $laggingCount,
            'inProgressCount' => $inProgressCount,
            'notStartedCount' => $notStartedCount,
            'adminTotalAssessors' => $adminTotalAssessors,
            'adminTotalJobs' => $adminTotalJobs,
            'adminTotalTargetEmployees' => $adminTotalTargetEmployees,
            'adminAssessedEmployeesCount' => $adminAssessedEmployeesCount,
            'adminEmployeeProgressPercentage' => $adminEmployeeProgressPercentage,
            'adminEmployeeProgressStatus' => $adminEmployeeProgressStatus,
            'adminEmployeeStatusColor' => $adminEmployeeStatusColor,
            'adminAssessedJobsCount' => $adminAssessedJobsCount,
            'adminJobPct' => $adminJobPct,
            'adminVerifiedChecklists' => $adminVerifiedChecklists,
            'adminTargetChecklists' => $adminTargetChecklists,
            'adminChecklistPercentage' => $adminChecklistPercentage,
            'adminFullAssessedEmployeesCount' => $adminFullAssessedEmployeesCount,
            'adminFullAssessedPercentage' => $adminFullAssessedPercentage,
            'adminAssessedEmployeesList' => $adminAssessedEmployeesList,
            'adminAssessedJobsList' => $adminAssessedJobsList,
            'chartLabels' => $chartLabels,
            'chartLabelsShort' => $chartLabelsShort,
            'chartData' => $chartData,
            'chartColors' => $chartColors,
            'chartEmpAssessed' => $chartEmpAssessed,
            'chartEmpTotal' => $chartEmpTotal,
            'chartEmpDataLabels' => $chartEmpDataLabels,
            'chartJobData' => $chartJobData,
            'chartJobColors' => $chartJobColors,
            'chartJobAssessed' => $chartJobAssessed,
            'chartJobTotal' => $chartJobTotal,
            'chartJobDataLabels' => $chartJobDataLabels,
        ];
    }
}
