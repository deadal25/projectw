<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAssessor;
use App\Models\GroupEmployee;
use App\Models\Job;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\JobComment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\GoogleSheetService;

class AssessmentController extends Controller
{
    protected GoogleSheetService $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }
    /**
    /**
     * Otorisasi: Pastikan Ketua hanya dapat mengakses dan menilai kelompoknya sendiri.
     */
    private function authorizeGroupAccess(Group $group, bool $isJson = false)
    {
        $user = Auth::user();
        if ($user && $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin'])) {
            if ((int)$user->group_id !== (int)$group->id) {
                if ($isJson) {
                    abort(response()->json(['error' => 'Akses ditolak: Anda hanya berwenang menilai kelompok Anda sendiri.'], 403));
                }
                abort(403, 'Anda hanya dapat mengisi penilaian untuk kelompok Anda sendiri.');
            }
        }
    }

    /**
     * Redirect cerdas rute root penilaian (/wola/assessments)
     */
    public function rootIndex()
    {
        $user = Auth::user();
        if ($user && $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin'])) {
            $groupId = $user->group_id;
            $group = $groupId ? Group::find($groupId) : Group::orderBy('number')->first();
        } else {
            $group = Group::orderBy('number')->first();
        }

        if (!$group) {
            abort(404, 'Belum ada kelompok yang terdaftar.');
        }

        return redirect()->route('wola.groups.assessments.index', $group);
    }

    /**
     * Halaman Utama Penilaian WOLA:
     * - Jika belum pilih penilai: Tampilkan grid kartu penilai
     * - Jika sudah pilih penilai: Tampilkan workspace checklist (Mode Per Jabatan / Per Karyawan)
     */
    public function index(Group $group, Request $request)
    {
        $user = Auth::user();
        $isKetuaOnly = $user && $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin']);

        // Jika Ketua kelompok lain mencoba membuka kelompok ini, alihkan secara ramah ke kelompoknya sendiri
        if ($isKetuaOnly && (int)$user->group_id !== (int)$group->id) {
            $myGroup = Group::find($user->group_id);
            if ($myGroup) {
                return redirect()->route('wola.groups.assessments.index', $myGroup)
                    ->with('warning', "Anda dialihkan ke kelompok Anda ({$myGroup->name}). Anda hanya berwenang menilai kelompok sendiri.");
            }
            abort(403, 'Anda hanya dapat mengisi penilaian untuk kelompok Anda sendiri.');
        }

        $allGroups = collect();
        if (!$isKetuaOnly) {
            $allGroups = Group::orderBy('number')->get();
        }

        $group->load([
            'assessors.employee',
            'jobs' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'targetEmployees' => function ($q) {
                $q->orderBy('nrp');
            }
        ]);

        $assessorId = $request->input('assessor_id');
        $mode = $request->input('mode', 'by_job'); // 'by_job' atau 'by_employee'
        $jobId = $request->input('job_id');
        $employeeId = $request->input('employee_id');

        // Jika belum ada penilai yang dipilih, tampilkan layar pemilihan kartu penilai
        if (!$assessorId) {
            $assessors = $group->assessors;
            $groupProgress = $group->getProgressData();
            return view('wola.assessments.select_assessor', compact('group', 'assessors', 'groupProgress', 'allGroups', 'isKetuaOnly'));
        }

        // Cari penilai yang dipilih, jika tidak valid alihkan kembali ke halaman pemilihan penilai
        $currentAssessor = GroupAssessor::with('employee')->where('group_id', $group->id)->find($assessorId);
        if (!$currentAssessor) {
            return redirect()->route('wola.groups.assessments.index', $group)
                ->with('warning', 'Penilai yang dipilih tidak ditemukan pada kelompok ini. Silakan pilih penilai.');
        }
        $jobs = $group->jobs;
        $employees = $group->targetEmployees;

        // Ambil SELURUH penilaian di kelompok ini untuk mendeteksi siapa yang sudah menilai setiap unit
        $allGroupAssessments = Assessment::with('assessor.employee')
            ->where('group_id', $group->id)
            ->get();

        // 1. Penilaian milik penilai yang sedang dipilih saat ini
        $myAssessments = [];
        // 2. Penilaian yang sudah diisi oleh penilai lain dalam kelompok ini (mencegah duplikasi/dobel)
        $otherAssessments = [];
        // 3. Seluruh penilaian per unit (semua penilai yang menilai unit tersebut, baik 1, 2, 3, 4 atau lebih penilai)
        $allAssessmentsByUnit = [];

        foreach ($allGroupAssessments as $item) {
            $key = $item->job_id . '_' . $item->employee_id;
            $assessorName = $item->assessor && $item->assessor->employee 
                ? $item->assessor->employee->fullname 
                : 'Penilai Lain';
            $isLeader = $item->assessor ? (bool)$item->assessor->is_leader : false;
            $checkedAt = $item->checked_at ? $item->checked_at->format('d/m/Y H:i') : null;
            $formattedName = $assessorName . ($isLeader ? ' (Ketua)' : '');

            // Kumpulkan untuk allAssessmentsByUnit
            if (!isset($allAssessmentsByUnit[$key])) {
                $allAssessmentsByUnit[$key] = [
                    'assessors' => [],
                    'names' => [],
                    'names_string' => '',
                    'count' => 0,
                    'has_my' => false,
                    'has_other' => false,
                ];
            }
            $allAssessmentsByUnit[$key]['assessors'][] = [
                'id' => $item->id,
                'assessor_id' => $item->assessor_id,
                'assessor_name' => $assessorName,
                'formatted_name' => $formattedName,
                'is_leader' => $isLeader,
                'checked_at' => $checkedAt,
            ];
            $allAssessmentsByUnit[$key]['names'][] = $formattedName;

            if ($item->assessor_id == $assessorId) {
                $myAssessments[$key] = [
                    'id' => $item->id,
                    'checked_at' => $checkedAt,
                ];
                $allAssessmentsByUnit[$key]['has_my'] = true;
            } else {
                $allAssessmentsByUnit[$key]['has_other'] = true;
                if (!isset($otherAssessments[$key])) {
                    $otherAssessments[$key] = [
                        'assessors' => [],
                        'names_string' => '',
                        'assessor_name' => '',
                        'count' => 0,
                    ];
                }
                $otherAssessments[$key]['assessors'][] = [
                    'id' => $item->id,
                    'assessor_id' => $item->assessor_id,
                    'assessor_name' => $assessorName,
                    'formatted_name' => $formattedName,
                    'is_leader' => $isLeader,
                    'checked_at' => $checkedAt,
                ];
            }
        }

        foreach ($otherAssessments as $key => $data) {
            $names = array_map(function($a) {
                return $a['formatted_name'];
            }, $data['assessors']);
            $names = array_values(array_unique($names));
            $formattedString = implode(', ', $names);

            $otherAssessments[$key]['names_string'] = $formattedString;
            $otherAssessments[$key]['assessor_name'] = $formattedString;
            $otherAssessments[$key]['count'] = count($names);
        }

        foreach ($allAssessmentsByUnit as $key => $data) {
            $names = array_values(array_unique($data['names']));
            $formattedString = implode(', ', $names);

            $allAssessmentsByUnit[$key]['names'] = $names;
            $allAssessmentsByUnit[$key]['names_string'] = $formattedString;
            $allAssessmentsByUnit[$key]['count'] = count($names);
        }

        // Untuk kompatibilitas backward view
        $existingAssessments = $allGroupAssessments->where('assessor_id', $assessorId)->keyBy(function ($item) {
            return $item->job_id . '_' . $item->employee_id;
        });

        // Hitung statistik per jabatan untuk kelompok ini (total progres unik per jabatan)
        $jobStats = [];
        $totalEmployeesCount = $employees->count();
        foreach ($jobs as $job) {
            $jobAssessments = $allGroupAssessments->where('job_id', $job->id);
            $totalCheckedForJob = $jobAssessments->unique('employee_id')->count();
            $myCheckedForJob = $jobAssessments->where('assessor_id', $assessorId)->count();
            $otherCheckedForJob = $totalCheckedForJob - $myCheckedForJob;

            $jobStats[$job->id] = [
                'checked' => $totalCheckedForJob,
                'my_checked' => $myCheckedForJob,
                'other_checked' => $otherCheckedForJob,
                'total' => $totalEmployeesCount,
                'is_complete' => ($totalEmployeesCount > 0 && $totalCheckedForJob >= $totalEmployeesCount),
            ];
        }

        // Hitung statistik per karyawan untuk kelompok ini (total jabatan yang sudah dinilai)
        $employeeStats = [];
        $totalJobsCount = $jobs->count();
        foreach ($employees as $emp) {
            $empAssessments = $allGroupAssessments->where('employee_id', $emp->id);
            $totalCheckedForEmp = $empAssessments->unique('job_id')->count();
            $myCheckedForEmp = $empAssessments->where('assessor_id', $assessorId)->count();
            $otherCheckedForEmp = $totalCheckedForEmp - $myCheckedForEmp;

            $employeeStats[$emp->id] = [
                'checked' => $totalCheckedForEmp,
                'my_checked' => $myCheckedForEmp,
                'other_checked' => $otherCheckedForEmp,
                'total' => $totalJobsCount,
                'is_complete' => ($totalJobsCount > 0 && $totalCheckedForEmp >= $totalJobsCount),
            ];
        }

        // Tentukan pilihan aktif saat ini
        if ($mode === 'by_job') {
            $currentJob = $jobId ? $jobs->firstWhere('id', $jobId) : $jobs->first();
            $currentEmployee = null;
        } else {
            $currentEmployee = $employeeId ? $employees->firstWhere('id', $employeeId) : $employees->first();
            $currentJob = null;
        }

        $assessorProgress = $currentAssessor->getProgressData();

        // Ambil komentar untuk jabatan aktif dan hitung total komentar per jabatan
        $jobComments = collect();
        if ($currentJob) {
            $jobComments = JobComment::with(['assessor.employee', 'user', 'employee'])
                ->where('group_id', $group->id)
                ->where('job_id', $currentJob->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $jobCommentsCount = JobComment::where('group_id', $group->id)
            ->selectRaw('job_id, count(*) as total')
            ->groupBy('job_id')
            ->pluck('total', 'job_id')
            ->toArray();

        return view('wola.assessments.index', compact(
            'group',
            'currentAssessor',
            'jobs',
            'employees',
            'mode',
            'currentJob',
            'currentEmployee',
            'existingAssessments',
            'myAssessments',
            'otherAssessments',
            'allAssessmentsByUnit',
            'jobStats',
            'employeeStats',
            'assessorProgress',
            'allGroups',
            'isKetuaOnly',
            'jobComments',
            'jobCommentsCount'
        ));
    }

    /**
     * Endpoint AJAX Auto-Save: Toggle centang per unit (assessor_id, job_id, employee_id)
     */
    public function toggle(Group $group, Request $request)
    {
        $this->authorizeGroupAccess($group, true);

        $validated = $request->validate([
            'assessor_id' => 'required|exists:group_assessors,id',
            'job_id' => 'required|exists:jobs,id',
            'employee_id' => 'required|exists:employees,id',
            'checked' => 'required|boolean',
        ]);

        $assessorId = $validated['assessor_id'];
        $jobId = $validated['job_id'];
        $employeeId = $validated['employee_id'];
        $isChecked = (bool)$validated['checked'];

        $assessor = GroupAssessor::with('employee')->where('group_id', $group->id)->findOrFail($assessorId);
        $job = Job::where('group_id', $group->id)->findOrFail($jobId);
        $employee = Employee::findOrFail($employeeId);

        if ($isChecked) {
            // Wajib ada catatan / komentar evaluasi sebelum dapat disimpan sebagai Dinilai
            $commentText = trim($request->input('comment', ''));
            $hasComment = JobComment::where('job_id', $jobId)
                ->where('assessor_id', $assessorId)
                ->where(function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                })
                ->exists();

            if (!$hasComment && empty($commentText)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian tidak dapat disimpan tanpa catatan komentar. Harap pilih karyawan dan tuliskan catatan evaluasi terlebih dahulu.',
                ], 422);
            }

            // Jika ada komentar yang disertakan, simpan sebagai JobComment
            if (!empty($commentText)) {
                JobComment::create([
                    'group_id' => $group->id,
                    'job_id' => $jobId,
                    'assessor_id' => $assessorId,
                    'user_id' => Auth::id(),
                    'employee_id' => $employeeId,
                    'comment' => $commentText,
                ]);
            }

            // Simpan penilaian penilai ini (unique pada assessor_id, job_id, employee_id)
            $assessment = Assessment::firstOrCreate(
                [
                    'assessor_id' => $assessorId,
                    'job_id' => $jobId,
                    'employee_id' => $employeeId,
                ],
                [
                    'group_id' => $group->id,
                    'checked_by' => Auth::id(),
                    'checked_at' => now(),
                ]
            );

            // Sinkronkan ke tab Hasil_Penilaian di Google Spreadsheet
            $this->googleSheetService->appendAssessment($assessment);

            ActivityLog::log(
                'CHECK_ASSESSMENT',
                "{$assessor->employee->fullname} menilai {$employee->fullname} untuk jabatan {$job->name}",
                [
                    'group_id' => $group->id,
                    'assessor_id' => $assessorId,
                    'job_id' => $jobId,
                    'employee_id' => $employeeId,
                ],
                $group->id
            );
        } else {
            // Hapus penilaian milik penilai ini saja
            Assessment::where('assessor_id', $assessorId)
                ->where('job_id', $jobId)
                ->where('employee_id', $employeeId)
                ->delete();

            // Hapus dari tab Hasil_Penilaian di Google Spreadsheet
            $this->googleSheetService->deleteAssessment(
                $employee->fullname,
                $job->name,
                $assessor->employee?->fullname,
                $group->number
            );

            ActivityLog::log(
                'UNCHECK_ASSESSMENT',
                "Membatalkan penilaian {$assessor->employee->fullname} untuk {$employee->fullname} pada jabatan {$job->name}",
                [
                    'group_id' => $group->id,
                    'assessor_id' => $assessorId,
                    'job_id' => $jobId,
                    'employee_id' => $employeeId,
                ],
                $group->id
            );
        }

        // Ambil data terkini seluruh penilai pada unit ini (untuk sinkronisasi nama-nama penilai secara real-time)
        $currentUnitAssessments = Assessment::with('assessor.employee')
            ->where('group_id', $group->id)
            ->where('job_id', $jobId)
            ->where('employee_id', $employeeId)
            ->get();

        $allAssessorNames = [];
        $otherAssessorNames = [];
        foreach ($currentUnitAssessments as $ua) {
            $name = $ua->assessor && $ua->assessor->employee 
                ? $ua->assessor->employee->fullname 
                : 'Penilai Lain';
            if ($ua->assessor && $ua->assessor->is_leader) {
                $name .= ' (Ketua)';
            }
            $allAssessorNames[] = $name;
            if ($ua->assessor_id != $assessorId) {
                $otherAssessorNames[] = $name;
            }
        }
        $allAssessorNames = array_values(array_unique($allAssessorNames));
        $otherAssessorNames = array_values(array_unique($otherAssessorNames));

        $allAssessorString = implode(', ', $allAssessorNames);
        $otherAssessorString = implode(', ', $otherAssessorNames);

        // Hitung ulang progres penilai & kelompok saat ini
        $assessorProgress = $assessor->getProgressData();
        $groupProgress = $group->getProgressData();

        return response()->json([
            'success' => true,
            'checked' => $isChecked,
            'all_names' => $allAssessorNames,
            'all_names_string' => $allAssessorString,
            'all_count' => count($allAssessorNames),
            'other_assessor' => $otherAssessorString ?: null,
            'other_assessors_count' => count($otherAssessorNames),
            'assessor_progress' => $assessorProgress,
            'group_progress' => $groupProgress,
        ]);
    }

    /**
     * Endpoint AJAX Batch: "Ceklis Semua" atau "Hapus Semua Ceklis"
     */
    public function batch(Group $group, Request $request)
    {
        $this->authorizeGroupAccess($group, true);

        $validated = $request->validate([
            'assessor_id' => 'required|exists:group_assessors,id',
            'action' => 'required|in:check_all,uncheck_all',
            'job_id' => 'nullable|exists:jobs,id',
            'employee_id' => 'nullable|exists:employees,id',
            'target_ids' => 'required|array',
        ]);

        $assessorId = $validated['assessor_id'];
        $action = $validated['action'];
        $jobId = $validated['job_id'] ?? null;
        $employeeId = $validated['employee_id'] ?? null;
        $targetIds = $validated['target_ids'];

        $assessor = GroupAssessor::with('employee')->where('group_id', $group->id)->findOrFail($assessorId);
        $user = Auth::user();

        if ($action === 'check_all') {
            return response()->json([
                'success' => false,
                'message' => 'Penilaian massal tanpa catatan tidak diizinkan. Setiap penilaian wajib disertai catatan evaluasi tersendiri.',
            ], 422);
        }

        DB::transaction(function () use ($group, $assessorId, $action, $jobId, $employeeId, $targetIds, $user, $assessor) {
            if ($jobId) {
                $job = Job::where('group_id', $group->id)->find($jobId);
                // Mode Per Jabatan: targetIds adalah employee_ids
                if ($action === 'check_all') {
                    $insertData = [];
                    $now = now();
                    foreach ($targetIds as $empId) {
                        $insertData[] = [
                            'group_id' => $group->id,
                            'assessor_id' => $assessorId,
                            'job_id' => $jobId,
                            'employee_id' => $empId,
                            'checked_by' => $user->id,
                            'checked_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    if (!empty($insertData)) {
                        Assessment::upsert($insertData, ['assessor_id', 'job_id', 'employee_id'], ['checked_by', 'checked_at', 'updated_at']);
                        $assessments = Assessment::where('group_id', $group->id)
                            ->where('assessor_id', $assessorId)
                            ->where('job_id', $jobId)
                            ->whereIn('employee_id', $targetIds)
                            ->get();
                        foreach ($assessments as $ass) {
                            $this->googleSheetService->appendAssessment($ass);
                        }
                    }
                } else {
                    $targetEmps = Employee::whereIn('id', $targetIds)->get();
                    Assessment::where('assessor_id', $assessorId)
                        ->where('job_id', $jobId)
                        ->whereIn('employee_id', $targetIds)
                        ->delete();
                    foreach ($targetEmps as $emp) {
                        $this->googleSheetService->deleteAssessment($emp->fullname, $job?->name ?? '', $assessor->employee?->fullname, $group->number);
                    }
                }
            } elseif ($employeeId) {
                $employee = Employee::find($employeeId);
                // Mode Per Karyawan: targetIds adalah job_ids
                if ($action === 'check_all') {
                    $insertData = [];
                    $now = now();
                    foreach ($targetIds as $jId) {
                        $insertData[] = [
                            'group_id' => $group->id,
                            'assessor_id' => $assessorId,
                            'job_id' => $jId,
                            'employee_id' => $employeeId,
                            'checked_by' => $user->id,
                            'checked_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    if (!empty($insertData)) {
                        Assessment::upsert($insertData, ['assessor_id', 'job_id', 'employee_id'], ['checked_by', 'checked_at', 'updated_at']);
                        $assessments = Assessment::where('group_id', $group->id)
                            ->where('assessor_id', $assessorId)
                            ->where('employee_id', $employeeId)
                            ->whereIn('job_id', $targetIds)
                            ->get();
                        foreach ($assessments as $ass) {
                            $this->googleSheetService->appendAssessment($ass);
                        }
                    }
                } else {
                    $targetJobs = Job::whereIn('id', $targetIds)->get();
                    Assessment::where('assessor_id', $assessorId)
                        ->where('employee_id', $employeeId)
                        ->whereIn('job_id', $targetIds)
                        ->delete();
                    foreach ($targetJobs as $j) {
                        $this->googleSheetService->deleteAssessment($employee?->fullname ?? '', $j->name, $assessor->employee?->fullname, $group->number);
                    }
                }
            }
        });

        ActivityLog::log(
            'BATCH_ASSESSMENT',
            "Eksekusi massal " . ($action === 'check_all' ? 'Ceklis Semua' : 'Hapus Semua Ceklis') . " oleh {$assessor->employee->fullname}",
            ['group_id' => $group->id, 'action' => $action, 'count' => count($targetIds)],
            $group->id
        );

        $assessorProgress = $assessor->getProgressData();
        $groupProgress = $group->getProgressData();

        return response()->json([
            'success' => true,
            'action' => $action,
            'count' => count($targetIds),
            'assessor_progress' => $assessorProgress,
            'group_progress' => $groupProgress,
        ]);
    }

    /**
     * Simpan / Tambah Komentar untuk Posisi Jabatan Tertentu oleh Penilai
     */
    public function saveJobComment(Group $group, Request $request)
    {
        $this->authorizeGroupAccess($group, true);

        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'assessor_id' => 'required|exists:group_assessors,id',
            'employee_id' => 'nullable|exists:employees,id',
            'comment' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $job = Job::where('group_id', $group->id)->findOrFail($validated['job_id']);
        $assessor = GroupAssessor::with('employee')->where('group_id', $group->id)->findOrFail($validated['assessor_id']);

        $isKetuaOnly = $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin']);
        if ($isKetuaOnly && (int)$group->id !== (int)$user->group_id) {
            abort(403, 'Akses ditolak.');
        }

        $employeeId = $validated['employee_id'] ?? null;
        $comment = JobComment::create([
            'group_id' => $group->id,
            'job_id' => $job->id,
            'assessor_id' => $assessor->id,
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'comment' => trim($validated['comment']),
        ]);

        $comment->load(['assessor.employee', 'user', 'employee']);

        // Jika catatan ditujukan khusus untuk karyawan tertentu, otomatis tandai karyawan tersebut sebagai Dinilai oleh penilai ini
        $autoAssessed = false;
        if ($employeeId) {
            Assessment::firstOrCreate(
                [
                    'assessor_id' => $assessor->id,
                    'job_id' => $job->id,
                    'employee_id' => $employeeId,
                ],
                [
                    'group_id' => $group->id,
                    'checked_by' => $user->id,
                    'checked_at' => now(),
                ]
            );
            $autoAssessed = true;
        }

        $targetDesc = $comment->employee ? " untuk karyawan {$comment->employee->fullname}" : "";
        ActivityLog::log(
            'ADD_JOB_COMMENT',
            "{$user->name} menambahkan catatan komentar untuk jabatan {$job->name}{$targetDesc} atas nama {$assessor->employee->fullname}",
            [
                'group_id' => $group->id,
                'job_id' => $job->id,
                'assessor_id' => $assessor->id,
                'employee_id' => $comment->employee_id,
                'comment_id' => $comment->id,
            ],
            $group->id
        );

        // Sinkronisasi otomatis ke Google Spreadsheet tab Hasil_Penilaian (kolom Catatan & Status Dinilai)
        $this->googleSheetService->syncJobComment($comment, $group, $job, $assessor, $comment->employee);

        if ($request->ajax() || $request->wantsJson()) {
            $allNames = [];
            $otherAssessorNames = null;
            $otherAssessorsCount = 0;

            if ($employeeId) {
                $unitAssessments = Assessment::with('assessor.employee')
                    ->where('group_id', $group->id)
                    ->where('job_id', $job->id)
                    ->where('employee_id', $employeeId)
                    ->get();

                $allNames = $unitAssessments->map(function ($a) {
                    return $a->assessor?->employee?->fullname ?? 'Penilai';
                })->values()->toArray();

                $others = $unitAssessments->filter(fn($a) => $a->assessor_id !== $assessor->id);
                $otherNames = $others->map(fn($a) => $a->assessor?->employee?->fullname ?? 'Penilai')->values()->toArray();
                $otherAssessorNames = !empty($otherNames) ? implode(', ', $otherNames) : null;
                $otherAssessorsCount = count($otherNames);
            }

            return response()->json([
                'success' => true,
                'message' => $autoAssessed 
                    ? "Catatan berhasil disimpan dan {$comment->employee->fullname} otomatis ditandai Dinilai!"
                    : 'Catatan / komentar berhasil disimpan!',
                'comment' => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'assessor_id' => $comment->assessor_id,
                    'assessor_name' => $comment->assessor && $comment->assessor->employee ? $comment->assessor->employee->fullname : 'Penilai',
                    'is_leader' => $comment->assessor ? (bool)$comment->assessor->is_leader : false,
                    'user_name' => $comment->user ? $comment->user->name : '',
                    'employee_id' => $comment->employee_id,
                    'target_employee_name' => $comment->employee ? $comment->employee->fullname : null,
                    'target_employee_nrp' => $comment->employee ? $comment->employee->nrp : null,
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'created_at_formatted' => $comment->created_at->format('d M Y, H:i'),
                ],
                'auto_assessed' => $autoAssessed,
                'employee_id' => $employeeId,
                'job_id' => $job->id,
                'all_names' => $allNames,
                'all_names_string' => !empty($allNames) ? implode(', ', $allNames) : '',
                'all_count' => count($allNames),
                'other_assessor' => $otherAssessorNames,
                'other_assessors_count' => $otherAssessorsCount,
            ]);
        }

        return back()->with('success', 'Catatan / komentar berhasil disimpan!');
    }

    /**
     * Hapus Komentar Jabatan
     */
    public function deleteJobComment(Group $group, JobComment $comment, Request $request)
    {
        $this->authorizeGroupAccess($group, true);

        if ($comment->group_id != $group->id) {
            abort(404);
        }

        $user = Auth::user();
        $isKetuaOnly = $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin']);
        $isGroupLeader = $user->hasRole('ketua') && (int)$user->group_id === (int)$group->id;

        // Hanya pemilik komentar, ketua kelompok dari kelompok ini, atau admin/superadmin yang bisa menghapus
        if ($isKetuaOnly && $comment->user_id != $user->id && !$isGroupLeader) {
            abort(403, 'Anda tidak berhak menghapus komentar ini.');
        }

        // Hapus catatan komentar dari Google Spreadsheet tab Hasil_Penilaian
        $this->googleSheetService->deleteJobComment($comment, $group);

        $comment->delete();

        ActivityLog::log(
            'DELETE_JOB_COMMENT',
            "{$user->name} menghapus komentar pada jabatan ID {$comment->job_id}",
            ['group_id' => $group->id, 'comment_id' => $comment->id],
            $group->id
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus!',
            ]);
        }

        return back()->with('success', 'Komentar berhasil dihapus!');
    }
}
