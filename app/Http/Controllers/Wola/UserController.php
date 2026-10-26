<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Models\Employee;
use App\Models\GroupAssessor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Services\GoogleSheetService;

class UserController extends Controller
{
    protected GoogleSheetService $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }
    /**
     * Tampilkan daftar akun pengguna dengan filter role
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $roleFilter = $request->input('role');
        $search = $request->input('search');

        // Pastikan seluruh akun Ketua kelompok otomatis terdaftar sebagai 1 penilai di kelompoknya
        $groupsWithLeader = Group::has('leader')->with('leader')->get();
        foreach ($groupsWithLeader as $grp) {
            $grp->syncLeaderAssessor();
        }

        $query = User::with(['roles', 'group.assessors.employee', 'employee'])
            ->leftJoin('groups', 'users.group_id', '=', 'groups.id')
            ->select('users.*');

        // Jika Admin biasa: HANYA boleh melihat akun Ketua
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('superadmin')) {
            $query->role('ketua');
        } elseif ($roleFilter) {
            $query->role($roleFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.username', 'like', "%{$search}%")
                  ->orWhere('groups.name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT('kelompok ', groups.number) LIKE ?", ["%{$search}%"])
                  ->orWhere('groups.number', $search);
            });
        }

        // Selalu urutkan mulai dari Kelompok 1, 2, ..., 12, lalu disusul akun tanpa kelompok (admin/superadmin)
        $users = $query->orderByRaw('CASE WHEN groups.number IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('groups.number', 'asc')
            ->orderBy('users.name', 'asc')
            ->paginate(25)
            ->withQueryString();
        $groups = Group::with(['assessors.employee', 'leader.employee'])->orderBy('number')->get();
        $allEmployees = Employee::orderBy('fullname')->get();

        // Hitung total akun sesuai kewenangan
        $totalSuperadmin = $currentUser->hasRole('superadmin') ? User::role('superadmin')->count() : 0;
        $totalAdmin = $currentUser->hasRole('superadmin') ? User::role('admin')->count() : 0;
        $totalKetua = User::role('ketua')->count();
        $totalAssessors = GroupAssessor::count();

        return view('wola.users.index', compact(
            'users',
            'groups',
            'allEmployees',
            'roleFilter',
            'search',
            'totalSuperadmin',
            'totalAdmin',
            'totalKetua',
            'totalAssessors'
        ));
    }

    /**
     * Form tambah akun baru
     */
    public function create()
    {
        $currentUser = Auth::user();
        $groups = Group::orderBy('number')->get();
        $employees = Employee::orderBy('fullname')->get();

        // Admin biasa hanya bisa membuat akun Ketua
        $roles = $currentUser->hasRole('superadmin')
            ? Role::all()
            : Role::where('name', 'ketua')->get();

        return view('wola.users.create', compact('groups', 'employees', 'roles'));
    }

    /**
     * Simpan akun baru
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Tentukan batas role yang diizinkan dibuat
        $allowedRoles = $currentUser->hasRole('superadmin')
            ? ['superadmin', 'admin', 'ketua']
            : ['ketua'];

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'username' => ['nullable', 'string', 'regex:/^[0-9]+$/', 'max:30', 'unique:users,username'],
            'password' => 'required|string|min:6',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'group_id' => 'nullable|exists:groups,id',
            'employee_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama lengkap akun wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'username.regex' => 'NRP / Username harus berupa angka saja (contoh: 1183).',
            'username.unique' => 'NRP / Username ini sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.in' => 'Anda tidak memiliki hak untuk membuat akun dengan role tersebut.',
        ]);

        $employeeId = $validated['employee_id'] ?? null;
        if (!empty($validated['username']) && empty($employeeId)) {
            $matched = Employee::where('nrp', trim($validated['username']))->first();
            if ($matched) {
                $employeeId = $matched->id;
            }
        }
        if ($validated['role'] === 'ketua' && empty($employeeId)) {
            $group = !empty($validated['group_id']) ? Group::find($validated['group_id']) : null;
            $targetNrp = Group::$officialLeaderNrps[$validated['name']] 
                ?? (Group::$groupLeaderNrps[$group?->number ?? 0] ?? null);
            if ($targetNrp) {
                $matched = Employee::where('nrp', $targetNrp)->first();
                if ($matched) {
                    $employeeId = $matched->id;
                }
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'password' => Hash::make($validated['password']),
            'group_id' => $validated['role'] === 'ketua' ? $validated['group_id'] : null,
            'employee_id' => $employeeId,
            'is_active' => $request->has('is_active'),
        ]);

        $user->assignRole($validated['role']);

        if ($user->hasRole('ketua') && $user->group_id) {
            $group = Group::find($user->group_id);
            if ($group) {
                $leaderAssessor = $group->syncLeaderAssessor();
                if ($leaderAssessor) {
                    $this->googleSheetService->appendAssessor($leaderAssessor, $group);
                }
            }
        }

        ActivityLog::log(
            'CREATE_USER',
            "Membuat akun pengguna baru: {$user->name} ({$user->email}) dengan role {$validated['role']}",
            ['role' => $validated['role'], 'user_id' => $user->id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet via Webhook Apps Script
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->appendUser($user, $validated['role'], $validated['password']);
        if ($sheetRes['success']) {
            $tabNote = $user->hasRole('ketua') ? "Pengguna & tim_penilai" : "Pengguna";
            $sheetSyncNote = " 🟢 Otomatis ditambahkan ke Google Spreadsheet tab {$tabNote}!";
        } elseif (!$sheetRes['configured']) {
            $sheetSyncNote = " (Catatan: Hubungkan Webhook Google Sheets agar otomatis tertambah ke Google Spreadsheet)";
        }

        return redirect()->route('wola.users.index')->with('success', "Akun {$user->name} berhasil dibuat.{$sheetSyncNote}");
    }

    /**
     * Form edit akun
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user();

        // Proteksi: Admin biasa tidak boleh mengedit sesama Admin atau Superadmin
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('superadmin') && !$user->hasRole('ketua')) {
            abort(403, 'Anda tidak memiliki hak untuk mengedit akun Administrator ini.');
        }

        $groups = Group::orderBy('number')->get();
        $employees = Employee::orderBy('fullname')->get();

        $roles = $currentUser->hasRole('superadmin')
            ? Role::all()
            : Role::where('name', 'ketua')->get();

        return view('wola.users.edit', compact('user', 'groups', 'employees', 'roles'));
    }

    /**
     * Perbarui data akun
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // Proteksi kewenangan
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('superadmin') && !$user->hasRole('ketua')) {
            abort(403, 'Anda tidak memiliki hak untuk memperbarui akun Administrator ini.');
        }

        $allowedRoles = $currentUser->hasRole('superadmin')
            ? ['superadmin', 'admin', 'ketua']
            : ['ketua'];

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', 'regex:/^[0-9]+$/', 'max:30', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'group_id' => 'nullable|exists:groups,id',
            'employee_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama lengkap akun wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'username.regex' => 'NRP / Username harus berupa angka saja (contoh: 1183).',
            'username.unique' => 'NRP / Username ini sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.in' => 'Anda tidak memiliki hak untuk memperbarui akun dengan role tersebut.',
        ]);

        $employeeId = $validated['employee_id'] ?? null;
        if (!empty($validated['username']) && empty($employeeId)) {
            $matched = Employee::where('nrp', trim($validated['username']))->first();
            if ($matched) {
                $employeeId = $matched->id;
            }
        }
        if ($validated['role'] === 'ketua' && empty($employeeId)) {
            if ($user->employee_id && ($prevEmp = Employee::find($user->employee_id)) && !str_starts_with($prevEmp->nrp, 'K-')) {
                $employeeId = $user->employee_id;
            } else {
                $group = !empty($validated['group_id']) ? Group::find($validated['group_id']) : null;
                $targetNrp = Group::$officialLeaderNrps[$validated['name']] 
                    ?? (Group::$groupLeaderNrps[$group?->number ?? 0] ?? null);
                if ($targetNrp) {
                    $matched = Employee::where('nrp', $targetNrp)->first();
                    if ($matched) {
                        $employeeId = $matched->id;
                    }
                }
            }
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'group_id' => $validated['role'] === 'ketua' ? $validated['group_id'] : null,
            'employee_id' => $employeeId,
            'is_active' => $request->has('is_active'),
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldUsername = $user->username;
        $oldNrp = $user->username ?: ($user->employee?->nrp ?? '');

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        // Hubungkan & perbarui master data karyawan (Employee) agar nama & NRP konsisten
        if ($user->employee_id) {
            $emp = Employee::find($user->employee_id);
            if ($emp) {
                $empData = ['fullname' => $user->name];
                if (!empty($validated['username']) && ctype_digit($validated['username'])) {
                    $conflict = Employee::where('nrp', $validated['username'])->where('id', '!=', $emp->id)->exists();
                    if (!$conflict) {
                        $empData['nrp'] = $validated['username'];
                    }
                }
                if ($user->group) {
                    $empData['area_wla'] = $user->group->name;
                }
                $emp->update($empData);
            }
        }

        if ($user->hasRole('ketua') && $user->group_id) {
            $group = Group::find($user->group_id);
            $group?->syncLeaderAssessor();
        }

        ActivityLog::log(
            'UPDATE_USER',
            "Memperbarui data akun: {$user->name} ({$user->email})",
            ['user_id' => $user->id, 'role' => $validated['role']]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet secara relasional (Pengguna, tim_penilai, Karyawan, Hasil_Penilaian)
        $sheetSyncNote = '';
        $user->loadMissing(['group', 'employee']);
        $newNrp = $user->username ?: ($user->employee?->nrp ?? '');
        $sheetRes = $this->googleSheetService->cascadeUpdatePerson([
            'nrp' => $newNrp,
            'old_nrp' => $oldNrp ?: $newNrp,
            'name' => $user->name,
            'old_name' => $oldName,
            'email' => $user->email,
            'old_email' => $oldEmail,
            'role' => $validated['role'],
            'group_number' => $user->group ? $user->group->number : null,
            'group_name' => $user->group ? "Kelompok {$user->group->number} ({$user->group->name})" : 'Semua Area',
            'password' => !empty($validated['password']) ? $validated['password'] : null,
        ]);

        if ($sheetRes['success']) {
            $sheetSyncNote = " 🟢 Otomatis diperbarui juga di database Google Spreadsheet (Pengguna, tim_penilai, Karyawan)!";
        }

        return redirect()->route('wola.users.index')->with('success', "Akun {$user->name} berhasil diperbarui.{$sheetSyncNote}");
    }

    /**
     * Hapus akun pengguna
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // Tidak boleh menghapus diri sendiri
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Admin biasa hanya bisa menghapus akun Ketua
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('superadmin') && !$user->hasRole('ketua')) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus akun Administrator ini.');
        }

        $name = $user->name;
        $email = $user->email;
        $username = $user->username;
        $isKetua = $user->hasRole('ketua');
        $groupNumber = $user->group ? $user->group->number : null;
        $groupId = $user->group_id;
        $employeeId = $user->employee_id;
        $employeeNrp = $user->employee ? $user->employee->nrp : null;

        // 1. Jika akun adalah Ketua atau memiliki relasi kelompok, hapus seluruh tim penilai kelompok ini dari DB dan tim_penilai Spreadsheet
        if ($isKetua || $groupId || $employeeId) {
            $assessors = GroupAssessor::where(function ($q) use ($groupId, $employeeId) {
                if ($groupId) {
                    $q->where('group_id', $groupId);
                }
                if ($employeeId) {
                    $q->orWhere('employee_id', $employeeId);
                }
            })->with(['employee', 'group'])->get();

            $deletedNrps = [];
            foreach ($assessors as $assessor) {
                // Hapus data penilaian dan komentar yang dibuat oleh penilai ini
                $assessor->assessments()->delete();
                $assessor->comments()->delete();

                $grpNum = $assessor->group ? $assessor->group->number : $groupNumber;
                $aNrp = $assessor->employee ? $assessor->employee->nrp : ($employeeNrp ?: $username);
                $aName = $assessor->employee ? $assessor->employee->fullname : $name;
                if ($aNrp) $deletedNrps[] = $aNrp;

                // Hapus dari Google Spreadsheet tab tim_penilai
                $this->googleSheetService->deleteAssessor($grpNum ?: '', $aNrp ?: '', $aName);

                // Hapus record penilai dari database lokal
                $assessor->delete();
            }

            // Jika role ketua, pastikan baris ketua dan seluruh penilai kelompok ini di spreadsheet juga bersih
            if ($isKetua) {
                $targetNrp = $employeeNrp ?: ($username ?: '');
                if ($targetNrp) $deletedNrps[] = $targetNrp;
                if (!empty($groupNumber)) {
                    $this->googleSheetService->deleteAssessor($groupNumber, $targetNrp, $name, $deletedNrps);
                }
                $this->googleSheetService->deleteAssessor('', $targetNrp, $name, $deletedNrps);
            }
        }

        $user->delete();

        ActivityLog::log(
            'DELETE_USER',
            "Menghapus akun: {$name} ({$email})",
            ['deleted_user_email' => $email]
        );

        // Sinkronisasi hapus akun pengguna di Google Spreadsheet tab Pengguna & tim_penilai
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->deleteUser($name, $email, $username, $groupNumber);
        if ($sheetRes['success']) {
            $tabNote = $isKetua ? "Pengguna & tim_penilai" : "Pengguna";
            $sheetSyncNote = " 🟢 Otomatis dihapus juga dari Google Spreadsheet tab {$tabNote}!";
        }

        return redirect()->route('wola.users.index')->with('success', "Akun {$name} berhasil dihapus.{$sheetSyncNote}");
    }
}
