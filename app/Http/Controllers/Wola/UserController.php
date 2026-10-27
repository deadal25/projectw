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

        // 1. Auto-heal: Pastikan akun Ketua yang belum memiliki group_id otomatis terhubung ke kelompoknya
        $unlinkedLeaders = User::role('ketua')->whereNull('group_id')->get();
        foreach ($unlinkedLeaders as $uLead) {
            $matchedGroup = null;
            if (!empty($uLead->username)) {
                $grpNum = array_search($uLead->username, Group::$groupLeaderNrps, true);
                if ($grpNum) {
                    $matchedGroup = Group::where('number', $grpNum)->first();
                }
            }
            if (!$matchedGroup) {
                $assLead = GroupAssessor::where('is_leader', true)
                    ->whereHas('employee', function ($eq) use ($uLead) {
                        $eq->where('nrp', $uLead->username)
                           ->orWhereRaw('LOWER(fullname) = LOWER(?)', [trim($uLead->name)]);
                    })->first();
                if ($assLead) {
                    $matchedGroup = Group::find($assLead->group_id);
                }
            }
            if ($matchedGroup) {
                $uLead->update(['group_id' => $matchedGroup->id]);
            }
        }

        // 2. Pastikan seluruh akun Ketua kelompok otomatis terdaftar sebagai penilai ketua di kelompoknya
        $groupsWithLeader = Group::has('leader')->with('leader')->get();
        foreach ($groupsWithLeader as $grp) {
            $grp->syncLeaderAssessor();
        }

        // 3. Muat seluruh daftar penilai untuk tab 'Daftar Seluruh Penilai'
        $allAssessors = GroupAssessor::with(['group', 'employee'])
            ->leftJoin('groups', 'group_assessors.group_id', '=', 'groups.id')
            ->leftJoin('employees', 'group_assessors.employee_id', '=', 'employees.id')
            ->select('group_assessors.*')
            ->orderByRaw('CASE WHEN groups.number IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('groups.number', 'asc')
            ->orderBy('group_assessors.is_leader', 'desc')
            ->orderBy('employees.fullname', 'asc')
            ->get();

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

        // Selalu urutkan mulai dari Kelompok 1, 2, ..., 13, lalu disusul akun tanpa kelompok (admin/superadmin)
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
            'allAssessors',
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
        $user->load(['group', 'employee']);
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
            $tabNote = $user->hasRole('ketua') ? "Pengguna & tim_penilai" : "Pengguna";
            $sheetSyncNote = " 🟢 Otomatis diperbarui juga di database Google Spreadsheet tab {$tabNote}!";
        }

        return redirect()->route('wola.users.index')->with('success', "Akun {$user->name} berhasil diperbarui.{$sheetSyncNote}");
    }

    /**
     * Hapus akun pengguna (Jika akun Ketua: seluruh anggota tim penilai kelompoknya juga ikut terhapus)
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
        $isKetua = $user->hasRole('ketua') || $user->group_id !== null;
        $group = $user->group ?? ($user->group_id ? Group::find($user->group_id) : null);
        $groupId = $group ? $group->id : $user->group_id;
        $groupNumber = $group ? $group->number : null;
        $employeeId = $user->employee_id;
        $employeeNrp = $user->employee ? $user->employee->nrp : null;

        // Jika akun adalah Ketua atau memiliki relasi kelompok:
        // Hapus KETUA dan SELURUH ANGGOTA tim penilai kelompok ini dari DB dan Google Spreadsheet tab tim_penilai!
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
            $deletedNames = [];

            foreach ($assessors as $assessor) {
                // Hapus data penilaian dan komentar yang dibuat oleh penilai ini
                $assessor->assessments()->delete();
                $assessor->comments()->delete();

                $aNrp = $assessor->employee ? $assessor->employee->nrp : null;
                $aName = $assessor->employee ? $assessor->employee->fullname : null;
                if ($aNrp) $deletedNrps[] = $aNrp;
                if ($aName) $deletedNames[] = $aName;

                // Hapus record penilai dari database lokal
                $assessor->delete();
            }

            // Tambahkan juga identitas ketua jika belum masuk daftar
            $targetNrp = $employeeNrp ?: ($username ?: '');
            if ($targetNrp && !in_array($targetNrp, $deletedNrps)) {
                $deletedNrps[] = $targetNrp;
            }
            if ($name && !in_array($name, $deletedNames)) {
                $deletedNames[] = $name;
            }

            // Baca tab tim_penilai dari spreadsheet secara live untuk mendeteksi seluruh anggota kelompok
            // yang tercatat di spreadsheet agar dipastikan terhapus bersih
            if (!empty($groupNumber)) {
                try {
                    $sheetAssessors = $this->googleSheetService->fetchSheetRows('tim_penilai');
                    $cleanTargetGrp = preg_replace('/[^0-9]/', '', (string)$groupNumber);
                    foreach ($sheetAssessors as $sRow) {
                        $sGrp = preg_replace('/[^0-9]/', '', (string)($sRow['nomor_kelompok'] ?? ''));
                        if ($sGrp === $cleanTargetGrp) {
                            $sNrp = trim((string)($sRow['nrp_penilai'] ?? ''));
                            $sName = trim((string)($sRow['nama_penilai'] ?? ''));
                            if (!empty($sNrp) && !in_array($sNrp, $deletedNrps)) {
                                $deletedNrps[] = $sNrp;
                            }
                            if (!empty($sName) && !in_array($sName, $deletedNames)) {
                                $deletedNames[] = $sName;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal fetch tab tim_penilai saat hapus ketua: " . $e->getMessage());
                }
            }

            // Hapus dari Google Spreadsheet tab tim_penilai secara menyeluruh (Ketua + seluruh anggota)
            $this->googleSheetService->deleteAssessor($groupNumber ?: '', $targetNrp, $name, $deletedNrps, true);

            // Pastikan per-NRP anggota juga dikirimkan agar kompatibel dengan seluruh versi Apps Script
            foreach ($deletedNrps as $nrpItem) {
                if (!empty($nrpItem) && $nrpItem !== $targetNrp) {
                    $this->googleSheetService->deleteAssessor($groupNumber ?: '', $nrpItem, '');
                }
            }
        }

        $user->delete();

        ActivityLog::log(
            'DELETE_USER',
            "Menghapus akun: {$name} ({$email})" . ($isKetua ? " beserta seluruh anggota tim penilai Kelompok " . ($groupNumber ?? '') : ''),
            ['deleted_user_email' => $email]
        );

        // Sinkronisasi hapus akun pengguna di Google Spreadsheet tab Pengguna
        $sheetSyncNote = '';
        $sheetRes = $this->googleSheetService->deleteUser($name, $email, $username, $groupNumber);
        if ($sheetRes['success']) {
            $tabNote = $isKetua ? "Pengguna & seluruh anggota tim penilai tab tim_penilai" : "Pengguna";
            $sheetSyncNote = " 🟢 Otomatis dihapus juga dari Google Spreadsheet tab {$tabNote}!";
        }

        $successMsg = $isKetua 
            ? "Akun Ketua {$name} beserta seluruh anggota tim penilai kelompok " . ($groupNumber ? "{$groupNumber}" : '') . " berhasil dihapus.{$sheetSyncNote}"
            : "Akun {$name} berhasil dihapus.{$sheetSyncNote}";

        return redirect()->route('wola.users.index')->with('success', $successMsg);
    }

    /**
     * Sinkronkan Pengguna & Tim Penilai langsung dari Google Spreadsheet
     */
    public function syncFromSheet(Request $request)
    {
        try {
            $rGrp = $this->googleSheetService->syncGroups();
            $rUsers = $this->googleSheetService->syncUsers();
            $rAss = $this->googleSheetService->syncAssessors();

            $totalAdded = ($rGrp['added'] ?? 0) + ($rUsers['added'] ?? 0) + ($rAss['added'] ?? 0);
            $totalUpdated = ($rGrp['updated'] ?? 0) + ($rUsers['updated'] ?? 0) + ($rAss['updated'] ?? 0);

            // Auto-heal relasi ketua
            $allGroups = Group::all();
            foreach ($allGroups as $grp) {
                $grp->syncLeaderAssessor();
            }

            $tab = $request->input('tab', 'assessors');

            return redirect()->route('wola.users.index', ['tab' => $tab])
                ->with('success', "Sinkronisasi berhasil! {$totalAdded} data ditambahkan, {$totalUpdated} diperbarui langsung dari Google Spreadsheet (Kelompok, Pengguna, dan tim_penilai).");
        } catch (\Exception $e) {
            return redirect()->route('wola.users.index')
                ->with('error', "Gagal menyinkronkan data dari Google Spreadsheet: " . $e->getMessage());
        }
    }
}
