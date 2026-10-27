<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAssessor;
use App\Models\GroupEmployee;
use App\Models\Job;
use App\Models\Employee;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\GoogleSheetService;

class GroupController extends Controller
{
    protected GoogleSheetService $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }
    /**
     * Otorisasi: Pastikan akun Ketua hanya dapat mengakses dan mengelola kelompoknya sendiri.
     */
    private function authorizeGroupAccess(Group $group): void
    {
        $user = Auth::user();
        if ($user && $user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin'])) {
            if ((int)$user->group_id !== (int)$group->id) {
                abort(403, 'Akses ditolak: Anda hanya memiliki hak akses untuk kelompok Anda sendiri (Kelompok ' . ($user->group?->number ?? $user->group_id) . ').');
            }
        }
    }

    /**
     * Otorisasi: Khusus Administrator & Super Admin (Ketua tidak diperbolehkan mengganti ketua atau mengelola karyawan sasaran).
     */
    private function authorizeAdminOnly(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Akses ditolak: Operasi penugasan Ketua dan pengelolaan master karyawan hanya berhak dilakukan oleh Administrator atau Super Admin.');
        }
    }

    /**
     * Tampilkan daftar seluruh kelompok proyek
     */
    public function index()
    {
        $user = Auth::user();

        // Jika Ketua, redirect langsung ke detail kelompoknya
        if ($user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin'])) {
            $myGroup = $user->group ?? ($user->group_id ? Group::find($user->group_id) : null);
            if (!$myGroup) {
                abort(403, 'Akun Ketua Anda belum ditautkan ke kelompok kerja manapun.');
            }
            return redirect()->route('wola.groups.show', $myGroup);
        }

        // Pastikan seluruh akun Ketua kelompok otomatis terdaftar sebagai 1 penilai di kelompoknya
        $groupsWithLeader = Group::has('leader')->with('leader')->get();
        foreach ($groupsWithLeader as $grp) {
            $grp->syncLeaderAssessor();
        }

        $groups = Group::with(['leader', 'assessors.employee', 'jobs', 'groupEmployees'])
            ->orderBy('number')
            ->get();

        $nextNumber = (Group::max('number') ?? 0) + 1;
        $potentialLeaders = User::role('ketua')
            ->leftJoin('groups', 'users.group_id', '=', 'groups.id')
            ->select('users.*')
            ->orderByRaw('CASE WHEN groups.number IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('groups.number', 'asc')
            ->orderBy('users.name', 'asc')
            ->get();

        return view('wola.groups.index', compact('groups', 'nextNumber', 'potentialLeaders'));
    }

    /**
     * Form tambah kelompok baru
     */
    public function create()
    {
        $nextNumber = (Group::max('number') ?? 0) + 1;
        $potentialLeaders = User::role('ketua')
            ->leftJoin('groups', 'users.group_id', '=', 'groups.id')
            ->select('users.*')
            ->orderByRaw('CASE WHEN groups.number IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('groups.number', 'asc')
            ->orderBy('users.name', 'asc')
            ->get();
        return view('wola.groups.create', compact('nextNumber', 'potentialLeaders'));
    }

    /**
     * Simpan kelompok baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:groups,number',
            'name' => 'required|string|max:150',
            'threshold_lagging' => 'nullable|integer|min:10|max:90',
            'leader_user_id' => 'nullable|exists:users,id',
        ], [
            'number.unique' => 'Nomor kelompok ini sudah digunakan.',
            'name.required' => 'Nama area kelompok wajib diisi.',
        ]);

        $validated['threshold_lagging'] = $validated['threshold_lagging'] ?? 40;

        $group = Group::create([
            'number' => $validated['number'],
            'name' => $validated['name'],
            'threshold_lagging' => $validated['threshold_lagging'],
        ]);

        // Hubungkan akun Ketua jika dipilih
        if (!empty($validated['leader_user_id'])) {
            User::where('id', $validated['leader_user_id'])->update(['group_id' => $group->id]);
        }

        // Secara default berikan 10 jabatan standar awal (bisa diedit/ditambah nanti)
        $defaultJobs = [
            'Senior Staff Capacity Plan',
            'Staff Capacity Plan',
            'Foreman Delivery Control',
            'Foreman Production Control',
            'Leader Delivery Control',
            'Leader Prod. Control',
            'Staff Delivery Control',
            'Staff Prod. Control',
            'Operator Delivery Control',
            'Admin',
        ];

        foreach ($defaultJobs as $order => $jobName) {
            Job::create([
                'group_id' => $group->id,
                'name' => $jobName,
                'sort_order' => $order + 1,
            ]);
        }

        ActivityLog::log(
            'CREATE_GROUP',
            "Membuat kelompok proyek baru: Kelompok {$group->number} - {$group->name}",
            ['group_id' => $group->id, 'number' => $group->number]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet via Webhook Apps Script
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->appendGroup($group);
        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis ditambahkan ke Google Spreadsheet tab Kelompok!";
        } elseif (!$sheetRes['configured']) {
            $sheetSyncNote = " (Catatan: Hubungkan Webhook Google Sheets agar otomatis tertambah ke Google Spreadsheet)";
        }

        return redirect()->route('wola.groups.index')->with('success', "Kelompok {$group->number} ({$group->name}) berhasil dibuat beserta 10 jabatan awal.{$sheetSyncNote}");
    }

    /**
     * Tampilkan detail manajemen kelompok: Tab Jabatan, Penilai, dan Karyawan Sasaran
     */
    public function show(Group $group, Request $request)
    {
        $this->authorizeGroupAccess($group);

        // Pastikan akun Ketua (dari Manajemen Akun) otomatis terhubung ke Tim Penilai kelompok ini
        $group->syncLeaderAssessor();

        $activeTab = $request->input('tab', 'jobs');

        $group->load([
            'leader',
            'assessors.employee',
            'jobs' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'targetEmployees' => function ($q) {
                $q->orderBy('nrp');
            }
        ]);

        $progressData = $group->getProgressData();

        // Daftar karyawan master yang belum masuk ke penilai kelompok ini
        $existingAssessorEmployeeIds = $group->assessors->pluck('employee_id')->toArray();
        $availableAssessors = Employee::whereNotIn('id', $existingAssessorEmployeeIds)
            ->orderBy('fullname')
            ->get();

        // Target employees dengan pagination atau koleksi
        $targetEmployees = $group->targetEmployees;
        $totalTargetEmployees = $targetEmployees->count();
        $assessedEmployeeIds = $group->assessments()->pluck('employee_id')->unique();
        $assessedEmployeesCount = $assessedEmployeeIds->count();
        $employeeProgressPercentage = $totalTargetEmployees > 0 
            ? round(($assessedEmployeesCount / $totalTargetEmployees) * 100, 1) 
            : 0;

        return view('wola.groups.show', compact(
            'group',
            'progressData',
            'activeTab',
            'availableAssessors',
            'targetEmployees',
            'totalTargetEmployees',
            'assessedEmployeesCount',
            'employeeProgressPercentage'
        ));
    }

    /**
     * Form edit informasi kelompok
     */
    public function edit(Group $group)
    {
        $user = Auth::user();
        if ($user->hasRole('ketua') && !$user->hasRole(['superadmin', 'admin'])) {
            abort(403, 'Hanya Administrator yang dapat mengubah data dasar kelompok.');
        }

        $potentialLeaders = User::role('ketua')
            ->leftJoin('groups', 'users.group_id', '=', 'groups.id')
            ->select('users.*')
            ->orderByRaw('CASE WHEN groups.number IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('groups.number', 'asc')
            ->orderBy('users.name', 'asc')
            ->get();

        return view('wola.groups.edit', compact('group', 'potentialLeaders'));
    }

    /**
     * Perbarui informasi kelompok
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', 'min:1', Rule::unique('groups')->ignore($group->id)],
            'name' => 'required|string|max:150',
            'threshold_lagging' => 'nullable|integer|min:10|max:90',
            'leader_user_id' => 'nullable|exists:users,id',
        ]);

        $oldNumber = $group->number;
        $oldName = $group->name;
        $oldPlant = $group->plant;

        $group->update([
            'number' => $validated['number'],
            'name' => $validated['name'],
            'threshold_lagging' => $validated['threshold_lagging'] ?? $group->threshold_lagging ?? 40,
        ]);

        // Hubungkan akun Ketua jika dipilih atau kosongkan jika dilepas secara eksplisit
        if (!empty($validated['leader_user_id'])) {
            User::where('group_id', $group->id)->where('id', '!=', $validated['leader_user_id'])->update(['group_id' => null]);
            User::where('id', $validated['leader_user_id'])->update(['group_id' => $group->id]);
        } elseif ($request->has('leader_user_id') && $request->input('leader_user_id') === '') {
            $existingLeader = User::where('group_id', $group->id)->first();
            if ($existingLeader) {
                // Periksa apakah ada penilai ketua aktif di tim_penilai yang terhubung ke akun ini
                $hasActiveLeaderAssessor = GroupAssessor::where('group_id', $group->id)->where('is_leader', true)->exists();
                if (!$hasActiveLeaderAssessor) {
                    $existingLeader->update(['group_id' => null]);
                }
            }
        }

        // Sinkronkan penilai ketua jika ada
        $group->syncLeaderAssessor();

        ActivityLog::log(
            'UPDATE_GROUP',
            "Memperbarui informasi Kelompok {$group->number} - {$group->name}",
            ['group_id' => $group->id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet secara cascade (Kelompok, tim_penilai, Pengguna)
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->updateGroup($group, $oldNumber, $oldName, $oldPlant);
        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis diperbarui juga di database Google Spreadsheet (Kelompok, tim_penilai, Pengguna)!";
        }

        if ($request->input('redirect_to') === 'show') {
            return redirect()->route('wola.groups.show', $group)->with('success', "Informasi kelompok {$group->number} berhasil diperbarui.{$sheetSyncNote}");
        }

        return redirect()->route('wola.groups.index')->with('success', "Informasi kelompok {$group->number} ({$group->name}) berhasil diperbarui.{$sheetSyncNote}");
    }

    /**
     * Hapus kelompok (Superadmin & Admin)
     */
    public function destroy(Request $request, Group $group)
    {
        $this->authorizeAdminOnly();

        // Validasi kata konfirmasi "HAPUS"
        if ($request->has('confirmation_text') && strtoupper(trim((string)$request->input('confirmation_text'))) !== 'HAPUS') {
            return back()->with('error', 'Konfirmasi gagal: Anda wajib mengetik kata HAPUS untuk menghapus kelompok ini.');
        }

        $name = $group->name;
        $num = $group->number;
        $groupId = $group->id;

        // 1. Kumpulkan seluruh identitas Ketua dan Tim Penilai SEBELUM data dihapus dari database lokal
        $leaderUsers = User::where('group_id', $groupId)->get();
        $leaderNames = [];
        $leaderEmails = [];
        $leaderUsernames = [];

        foreach ($leaderUsers as $lUser) {
            if (!empty($lUser->name)) $leaderNames[] = trim($lUser->name);
            if (!empty($lUser->email)) $leaderEmails[] = trim($lUser->email);
            if (!empty($lUser->username)) $leaderUsernames[] = trim($lUser->username);
        }

        // Ambil data seluruh penilai yang terdaftar pada kelompok ini
        $assessors = $group->assessors()->with('employee')->get();
        $assessorNrps = [];
        $assessorNames = [];

        foreach ($assessors as $assessor) {
            if ($assessor->employee) {
                if (!empty($assessor->employee->nrp)) $assessorNrps[] = trim($assessor->employee->nrp);
                if (!empty($assessor->employee->fullname)) $assessorNames[] = trim($assessor->employee->fullname);
            }
        }

        // Pastikan akun ketua juga tercatat dalam daftar NRP & Nama penilai
        foreach ($leaderUsernames as $uName) {
            if (!in_array($uName, $assessorNrps)) $assessorNrps[] = $uName;
        }
        foreach ($leaderNames as $lName) {
            if (!in_array($lName, $assessorNames)) $assessorNames[] = $lName;
        }

        // Tambahkan fallback NRP ketua dari pemetaan resmi jika ada
        if (isset(Group::$groupLeaderNrps[$num])) {
            $officialNrp = Group::$groupLeaderNrps[$num];
            if (!in_array($officialNrp, $assessorNrps)) {
                $assessorNrps[] = $officialNrp;
            }
        }

        // Ambil data seluruh jabatan yang terdaftar pada kelompok ini
        $jobNames = $group->jobs()->pluck('name')->filter()->values()->toArray();

        // Ambil data hasil penilaian yang terdaftar pada kelompok ini
        $assessmentPairs = [];
        $groupAssessments = $group->assessments()->with(['employee', 'job'])->get();
        foreach ($groupAssessments as $ass) {
            $eNrp = $ass->employee?->nrp ? trim($ass->employee->nrp) : '';
            $eName = $ass->employee?->fullname ? trim($ass->employee->fullname) : '';
            $jName = $ass->job?->name ? trim($ass->job->name) : '';
            if (!empty($eNrp) || !empty($eName)) {
                $assessmentPairs[] = [
                    'employee_nrp' => $eNrp,
                    'employee_name' => $eName,
                    'job_name' => $jName,
                ];
            }
        }

        // Ambil daftar karyawan yang terdaftar dengan Area WLA kelompok ini
        $affectedEmployees = Employee::where('area_wla', $name)
            ->orWhere('area_wla', 'Kelompok ' . $num)
            ->orWhere('area_wla', 'like', "%Kelompok {$num}%")
            ->get();
        $affectedEmployeeNrps = $affectedEmployees->pluck('nrp')->filter()->values()->toArray();

        // 2. Hapus data di database lokal terlebih dahulu secara konsisten (sangat cepat, < 0.05 detik)
        DB::transaction(function () use ($group, $num, $name, $leaderUsers) {
            foreach ($leaderUsers as $leaderUser) {
                ActivityLog::log(
                    'DELETE_USER',
                    "Menghapus akun Ketua '{$leaderUser->name}' ({$leaderUser->email}) karena Kelompok {$num} ({$name}) dihapus",
                    ['deleted_user_email' => $leaderUser->email, 'group_id' => $group->id]
                );

                if ($leaderUser->id !== Auth::id()) {
                    $leaderUser->delete();
                }
            }

            // Lepaskan relasi log aktivitas agar tidak error foreign key
            ActivityLog::where('group_id', $group->id)->update(['group_id' => null]);

            // Reset Area WLA pada data Karyawan (karyawan tetap ada, hanya area kelompoknya dikosongkan)
            Employee::where('area_wla', $name)
                ->orWhere('area_wla', 'Kelompok ' . $num)
                ->orWhere('area_wla', 'like', "%Kelompok {$num}%")
                ->update(['area_wla' => null]);

            // Hapus data anak secara teratur
            $group->assessments()->delete();
            $group->jobComments()->delete();
            $group->groupEmployees()->delete();
            $group->assessors()->delete();
            $group->jobs()->delete();

            $group->delete();
        });

        ActivityLog::log(
            'DELETE_GROUP',
            "Menghapus Kelompok {$num} - {$name} beserta seluruh jabatan, penilaian, akun Ketua dan tim penilai terkait",
            ['number' => $num]
        );

        // 3. Lakukan sinkronisasi HAPUS ke Google Spreadsheet secara menyeluruh via Webhook tunggal (Kelompok, tim_penilai, Pengguna, Posisi_Job, Hasil_Penilaian, reset Area_WLA Karyawan)
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->cascadeDeleteGroup($num, $name, [
            'leader_names' => $leaderNames,
            'leader_emails' => $leaderEmails,
            'leader_usernames' => $leaderUsernames,
            'assessor_nrps' => $assessorNrps,
            'assessor_names' => $assessorNames,
            'job_names' => $jobNames,
            'assessment_pairs' => $assessmentPairs,
            'employee_nrps' => $affectedEmployeeNrps,
        ]);

        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis dihapus juga dari database Google Spreadsheet (tab Kelompok, tim_penilai, Pengguna, Posisi_Job, Hasil_Penilaian, dan reset Area WLA Karyawan)!";
        } elseif (!$sheetRes['configured']) {
            $sheetSyncNote = " (Catatan: Hubungkan Webhook Google Sheets agar otomatis terhapus dari Google Spreadsheet)";
        }

        return redirect()->route('wola.groups.index')->with('success', "Kelompok {$num} ({$name}) beserta nama Ketua, seluruh tim penilai, jabatan, dan hasil penilaian berhasil dihapus dari sistem.{$sheetSyncNote}");
    }

    // =========================================================================
    // SUB-ACTIONS: KELOLA JABATAN KELOMPOK
    // =========================================================================

    public function addJob(Request $request, Group $group)
    {
        $this->authorizeGroupAccess($group);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
            'target_employees' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Nama jabatan wajib diisi.',
        ]);

        $maxSort = $group->jobs()->max('sort_order') ?? 0;

        $job = Job::create([
            'group_id' => $group->id,
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? ($maxSort + 1),
            'target_employees' => $validated['target_employees'] ?? null,
        ]);

        ActivityLog::log(
            'ADD_JOB',
            "Menambahkan jabatan '{$job->name}' (Target: " . ($job->target_employees ?? 'Standar') . " orang) pada Kelompok {$group->number}",
            ['group_id' => $group->id, 'job_id' => $job->id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet tab Posisi_Job
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->appendJob($job, $group);
        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis ditambahkan ke Google Spreadsheet tab Posisi_Job!";
        }

        return redirect()->route('wola.groups.show', [$group, 'tab' => 'jobs'])
            ->with('success', "Jabatan '{$job->name}' berhasil ditambahkan ke Kelompok {$group->number}.{$sheetSyncNote}");
    }

    public function updateJob(Request $request, Group $group, Job $job)
    {
        $this->authorizeGroupAccess($group);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'sort_order' => 'required|integer',
            'target_employees' => 'nullable|integer|min:0',
        ]);

        // Jika target_employees diisi nilai kosong atau null
        if (!isset($validated['target_employees']) || $validated['target_employees'] === '') {
            $validated['target_employees'] = null;
        }

        $oldName = $job->name;
        $oldCode = $job->code;

        $job->update($validated);

        ActivityLog::log(
            'UPDATE_JOB',
            "Memperbarui jabatan '{$job->name}' (Target: " . ($job->target_employees ?? 'Standar') . " orang) pada Kelompok {$group->number}",
            ['group_id' => $group->id, 'job_id' => $job->id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet tab Posisi_Job
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->updateJob($group->number, $job, $oldName, $oldCode);
        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis diperbarui juga di Google Spreadsheet tab Posisi_Job!";
        }

        return redirect()->route('wola.groups.show', [$group, 'tab' => 'jobs'])
            ->with('success', "Jabatan '{$job->name}' berhasil diperbarui.{$sheetSyncNote}");
    }

    public function removeJob(Group $group, Job $job)
    {
        $this->authorizeGroupAccess($group);

        if ($job->assessments()->exists()) {
            return back()->with('error', "Jabatan '{$job->name}' tidak dapat dihapus karena sudah memiliki checklist penilaian tersimpan.");
        }

        $name = $job->name;
        $jobCode = $job->code;
        $job->delete();

        ActivityLog::log(
            'REMOVE_JOB',
            "Menghapus jabatan '{$name}' dari Kelompok {$group->number}",
            ['group_id' => $group->id]
        );

        // Sinkronisasi hapus dari Google Spreadsheet tab Posisi_Job
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->deleteJob($name, $group->number, $jobCode);
        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis dihapus juga dari Google Spreadsheet tab Posisi_Job!";
        }

        return redirect()->route('wola.groups.show', [$group, 'tab' => 'jobs'])
            ->with('success', "Jabatan '{$name}' berhasil dihapus.{$sheetSyncNote}");
    }

    /**
     * Aktifkan / Nonaktifkan jabatan untuk penilaian
     */
    public function toggleJobActive(Group $group, Job $job)
    {
        $this->authorizeGroupAccess($group);

        $job->update([
            'is_active' => !$job->is_active,
        ]);

        $statusStr = $job->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::log(
            'TOGGLE_JOB_ACTIVE',
            "Jabatan '{$job->name}' {$statusStr} untuk penilaian Kelompok {$group->number}",
            ['group_id' => $group->id, 'job_id' => $job->id, 'is_active' => $job->is_active]
        );

        return back()->with('success', "Jabatan '{$job->name}' berhasil {$statusStr}.");
    }

    /**
     * Setel Target Manual atau Reset ke Otomatis Rumus
     */
    public function updateTarget(Request $request, Group $group)
    {
        $this->authorizeGroupAccess($group);
        $customTarget = $request->input('custom_target');

        if ($customTarget === null || $customTarget === '') {
            $group->update(['custom_target' => null]);
            $msg = "Target Kelompok {$group->number} direset ke mode Otomatis Rumus.";
        } else {
            $val = max(0, (int)$customTarget);
            $group->update(['custom_target' => $val]);
            $msg = "Target Kelompok {$group->number} berhasil disetel menjadi {$val} unit.";
        }

        ActivityLog::log(
            'UPDATE_GROUP_TARGET',
            $msg,
            ['group_id' => $group->id, 'custom_target' => $group->custom_target]
        );

        return back()->with('success', $msg);
    }

    // =========================================================================
    // SUB-ACTIONS: KELOLA PENILAI KELOMPOK
    // =========================================================================

    public function addAssessor(Request $request, Group $group)
    {
        $this->authorizeGroupAccess($group);

        // Dukung input manual (diketik langsung) atau pilihan employee_id
        if ($request->filled('fullname') || $request->filled('name')) {
            $name = trim($request->input('fullname') ?: $request->input('name'));
            $nrp = trim((string)$request->input('nrp'));
            $dept = trim((string)($request->input('dept') ?: ($group->name ?? 'WOLA')));
            $plant = trim((string)($request->input('plant') ?: 'CIKARANG'));

            if (empty($nrp)) {
                $nextId = (Employee::max('id') ?? 0) + 1;
                $nrp = 'P-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                while (Employee::where('nrp', $nrp)->exists()) {
                    $nextId++;
                    $nrp = 'P-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                }
            }

            // Cari apakah karyawan dengan NRP atau Nama ini sudah ada
            $employee = Employee::where('nrp', $nrp)->first();
            if (!$employee) {
                $employee = Employee::where('fullname', $name)->first();
            }

            if (!$employee) {
                $employee = Employee::create([
                    'area_wla' => $group->name,
                    'nrp' => $nrp,
                    'fullname' => $name,
                    'division' => $dept,
                    'dept' => $dept,
                    'section' => '-',
                    'plant' => $plant,
                ]);
            } else {
                $employee->update([
                    'fullname' => $name,
                    'dept' => $dept ?: $employee->dept,
                    'plant' => $plant ?: $employee->plant,
                ]);
            }

            $employeeId = $employee->id;
        } else {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
            ], [
                'employee_id.required' => 'Nama atau data karyawan penilai wajib diisi.',
            ]);
            $employeeId = $validated['employee_id'];
        }

        // Cek duplikasi
        $exists = GroupAssessor::where('group_id', $group->id)
            ->where('employee_id', $employeeId)
            ->exists();

        if ($exists) {
            $emp = Employee::find($employeeId);
            return back()->with('error', "Karyawan '{$emp->fullname}' sudah tercatat sebagai Penilai di kelompok ini.");
        }

        // Ketua kelompok HANYA berasal dari Akun Manajemen Akun (User role=ketua)
        $assessor = GroupAssessor::create([
            'group_id' => $group->id,
            'employee_id' => $employeeId,
            'is_leader' => false,
        ]);

        ActivityLog::log(
            'ADD_ASSESSOR',
            "Menambahkan Penilai '{$assessor->employee->fullname}' ke Kelompok {$group->number}",
            ['group_id' => $group->id, 'employee_id' => $assessor->employee_id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet tab tim_penilai
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->appendAssessor($assessor, $group);
        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis ditambahkan ke Google Spreadsheet tab tim_penilai!";
        } elseif (!$sheetRes['configured']) {
            $sheetSyncNote = " (Catatan: Hubungkan Webhook Google Sheets agar otomatis tertambah ke Google Spreadsheet)";
        }

        return back()->with('success', "Penilai '{$assessor->employee->fullname}' berhasil ditambahkan ke Kelompok {$group->number}.{$sheetSyncNote}");
    }

    public function updateAssessor(Request $request, Group $group, GroupAssessor $assessor)
    {
        $this->authorizeGroupAccess($group);

        $oldName = $assessor->employee ? $assessor->employee->fullname : '';
        $oldNrp = $assessor->employee ? $assessor->employee->nrp : '';

        // Dukung input manual (diketik langsung) atau pilihan employee_id
        if ($request->filled('fullname') || $request->filled('name')) {
            $name = trim($request->input('fullname') ?: $request->input('name'));
            $nrp = trim((string)$request->input('nrp'));
            $dept = trim((string)($request->input('dept') ?: ($group->name ?? 'WOLA')));
            $plant = trim((string)($request->input('plant') ?: 'CIKARANG'));

            $employee = $assessor->employee;
            if ($employee) {
                $employeeData = ['fullname' => $name];
                if (!empty($nrp) && $nrp !== $employee->nrp) {
                    $conflict = Employee::where('nrp', $nrp)->where('id', '!=', $employee->id)->exists();
                    if ($conflict) {
                        return back()->with('error', "NRP '{$nrp}' sudah digunakan oleh karyawan lain.");
                    }
                    $employeeData['nrp'] = $nrp;
                }
                if (!empty($dept)) $employeeData['dept'] = $dept;
                if (!empty($plant)) $employeeData['plant'] = $plant;
                $employee->update($employeeData);
            }
        } elseif ($request->filled('employee_id')) {
            $newEmployeeId = (int)$request->input('employee_id');
            if ($newEmployeeId !== (int)$assessor->employee_id) {
                $exists = GroupAssessor::where('group_id', $group->id)
                    ->where('employee_id', $newEmployeeId)
                    ->where('id', '!=', $assessor->id)
                    ->exists();

                if ($exists) {
                    return back()->with('error', 'Karyawan ini sudah tercatat sebagai Penilai di kelompok ini.');
                }

                if ($assessor->assessments()->exists()) {
                    return back()->with('error', "Karyawan penilai tidak dapat diubah karena '{$assessor->employee->fullname}' sudah memiliki checklist penilaian tersimpan.");
                }

                $assessor->update(['employee_id' => $newEmployeeId]);
            }
        }

        $assessor->load('employee');
        $assessorName = $assessor->employee ? $assessor->employee->fullname : 'Penilai';
        $newNrp = $assessor->employee?->nrp ?? '';

        $leaderEmail = '';
        $oldLeaderName = $oldName;
        if ($assessor->is_leader) {
            $leaderUser = User::where('group_id', $group->id)->whereHas('roles', fn($q) => $q->where('name', 'ketua'))->first()
                ?? User::where('group_id', $group->id)->first()
                ?? ($assessor->employee_id ? User::where('employee_id', $assessor->employee_id)->first() : null);
            if ($leaderUser) {
                $oldLeaderName = $leaderUser->name;
                $userUpdates = ['name' => $assessorName];
                if (!empty($newNrp) && ctype_digit($newNrp)) {
                    $conflictUser = User::where('username', $newNrp)->where('id', '!=', $leaderUser->id)->exists();
                    if (!$conflictUser) {
                        $userUpdates['username'] = $newNrp;
                    }
                }
                $leaderUser->update($userUpdates);
                $leaderEmail = $leaderUser->email;
            }
        }

        ActivityLog::log(
            'UPDATE_ASSESSOR',
            "Memperbarui data Penilai '{$assessorName}' di Kelompok {$group->number}",
            ['group_id' => $group->id, 'assessor_id' => $assessor->id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet secara relasional
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->cascadeUpdatePerson([
            'nrp' => $newNrp,
            'old_nrp' => $oldNrp ?? '',
            'name' => $assessorName,
            'old_name' => $oldLeaderName ?: $oldName,
            'email' => $leaderEmail,
            'old_email' => $leaderEmail,
            'role' => $assessor->is_leader ? 'ketua' : 'anggota',
            'group_number' => $group->number,
            'group_name' => "Kelompok {$group->number} ({$group->name})",
        ]);

        if ($sheetRes['success']) {
            $tabName = $assessor->is_leader ? 'Pengguna, tim_penilai & Karyawan' : 'tim_penilai & Karyawan';
            $sheetSyncNote = " 🟢 Otomatis diperbarui juga di database Google Spreadsheet ({$tabName})!";
        }

        return back()->with('success', "Data Penilai '{$assessorName}' berhasil diperbarui.{$sheetSyncNote}");
    }

    public function setLeader(Group $group, GroupAssessor $assessor)
    {
        $this->authorizeGroupAccess($group);
        return back()->with('error', 'Ketua kelompok tidak dapat diganti secara langsung. Penugasan Ketua hanya dapat diatur melalui akun Ketua pada menu Manajemen Akun.');
    }

    public function removeAssessor(Group $group, GroupAssessor $assessor)
    {
        $this->authorizeGroupAccess($group);

        // KETUA TIDAK DAPAT DIHAPUS DARI DAFTAR PENILAI
        if ($assessor->is_leader) {
            return back()->with('error', 'Ketua kelompok tidak dapat dihapus dari daftar penilai. Akun Ketua dikelola khusus melalui menu Manajemen Akun.');
        }

        // Hapus data penilaian dan komentar terkait jika penilai dihapus
        $assessor->assessments()->delete();
        $assessor->comments()->delete();

        $name = $assessor->employee ? $assessor->employee->fullname : 'Penilai';
        $nrp = $assessor->employee ? $assessor->employee->nrp : '';
        $groupNum = $group->number;
        $assessor->delete();

        ActivityLog::log(
            'REMOVE_ASSESSOR',
            "Menghapus Penilai '{$name}' dari Kelompok {$group->number}",
            ['group_id' => $group->id]
        );

        // Sinkronisasi hapus penilai di Google Spreadsheet tab tim_penilai
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->deleteAssessor($groupNum, $nrp, $name);
        if ($sheetRes['success'] && ($sheetRes['deleted_count'] ?? 1) > 0) {
            $sheetSyncNote = " 🟢 Otomatis dihapus juga dari Google Spreadsheet tab tim_penilai!";
        } elseif ($sheetRes['success'] && ($sheetRes['deleted_count'] ?? 1) === 0) {
            $sheetSyncNote = " (Catatan: Baris tidak ditemukan di tab tim_penilai spreadsheet, atau pastikan kode Apps Script terbaru sudah diterapkan).";
        }

        return back()->with('success', "Penilai '{$name}' berhasil dihapus dari kelompok.{$sheetSyncNote}");
    }

    // =========================================================================
    // SUB-ACTIONS: KELOLA KARYAWAN DINILAI (TARGET EMPLOYEES)
    // =========================================================================

    /**
     * Endpoint API JSON pencarian master karyawan untuk modal multi-select
     */
    public function searchMasterEmployees(Request $request, Group $group)
    {
        $this->authorizeGroupAccess($group);
        $this->authorizeAdminOnly();

        $search = $request->input('q');
        $dept = $request->input('dept');

        $assignedIds = GroupEmployee::where('group_id', $group->id)->pluck('employee_id')->toArray();

        $query = Employee::whereNotIn('id', $assignedIds);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nrp', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%");
            });
        }

        if ($dept) {
            $query->where('dept', $dept);
        }

        $employees = $query->orderBy('nrp')->limit(50)->get(['id', 'nrp', 'fullname', 'area_wla', 'division', 'dept', 'section', 'plant']);

        return response()->json($employees);
    }

    /**
     * Simpan karyawan terpilih dari modal master ke sasaran penilaian kelompok
     */
    public function syncEmployees(Request $request, Group $group)
    {
        $this->authorizeGroupAccess($group);
        $this->authorizeAdminOnly();

        $employeeIds = $request->input('employee_ids', []);

        if (empty($employeeIds)) {
            return back()->with('error', 'Tidak ada karyawan yang dipilih.');
        }

        $insertedCount = 0;
        foreach ($employeeIds as $empId) {
            $exists = GroupEmployee::where('group_id', $group->id)->where('employee_id', $empId)->exists();
            if (!$exists) {
                GroupEmployee::create([
                    'group_id' => $group->id,
                    'employee_id' => $empId,
                ]);
                $insertedCount++;
            }
        }

        ActivityLog::log(
            'ASSIGN_TARGET_EMPLOYEES',
            "Menambahkan {$insertedCount} karyawan sasaran ke Kelompok {$group->number}",
            ['group_id' => $group->id, 'count' => $insertedCount]
        );

        return redirect()->route('wola.groups.show', [$group, 'tab' => 'employees'])
            ->with('success', "Berhasil menambahkan {$insertedCount} karyawan ke dalam sasaran penilaian Kelompok {$group->number}.");
    }

    /**
     * Hapus karyawan dari sasaran kelompok
     */
    public function removeEmployee(Group $group, Employee $employee)
    {
        $this->authorizeGroupAccess($group);
        $this->authorizeAdminOnly();

        // Cek jika sudah dinilai di kelompok ini
        $hasAssessments = $group->assessments()->where('employee_id', $employee->id)->exists();
        if ($hasAssessments) {
            return back()->with('error', "Karyawan '{$employee->fullname}' tidak dapat dikeluarkan karena sudah memiliki checklist penilaian yang tercatat di kelompok ini.");
        }

        GroupEmployee::where('group_id', $group->id)->where('employee_id', $employee->id)->delete();

        ActivityLog::log(
            'REMOVE_TARGET_EMPLOYEE',
            "Mengeluarkan karyawan '{$employee->fullname}' dari sasaran Kelompok {$group->number}",
            ['group_id' => $group->id, 'employee_id' => $employee->id]
        );

        return redirect()->route('wola.groups.show', [$group, 'tab' => 'employees'])
            ->with('success', "Karyawan '{$employee->fullname}' berhasil dikeluarkan dari sasaran penilaian kelompok.");
    }
}
