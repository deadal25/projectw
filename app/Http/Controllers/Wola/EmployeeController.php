<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Group;
use App\Models\GroupEmployee;
use App\Models\GroupAssessor;
use App\Models\Assessment;
use App\Models\JobComment;
use App\Models\User;
use App\Models\ActivityLog;
use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\GoogleSheetService;

class EmployeeController extends Controller
{
    protected GoogleSheetService $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }
    /**
     * Temukan kelompok yang cocok dengan nama AREA WLA, atau buat otomatis jika belum ada.
     */
    protected function findOrCreateGroup(?string $areaWla): ?Group
    {
        $cleanName = trim((string)$areaWla);
        if (empty($cleanName)) {
            return null;
        }

        // Cari berdasarkan nama (case-insensitive)
        $group = Group::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])->first();
        if (!$group) {
            $maxNumber = (int) Group::max('number');
            $group = Group::create([
                'name' => $cleanName,
                'number' => $maxNumber + 1,
                'description' => "Kelompok kerja {$cleanName}",
                'target_checklist' => 60,
            ]);

            // Buat 10 jabatan standar WOLA untuk kelompok baru
            $defaultJobs = [
                'Section Head',
                'Sub Leader Production Control',
                'Sub Leader Delivery Control',
                'Sub Leader Warehousing & Logistic',
                'Staff Delivery Control',
                'Staff Warehousing Material',
                'Staff Part In-House',
                'Operator Delivery Control',
                'Operator Warehousing Material',
                'Operator Part In-House',
            ];

            foreach ($defaultJobs as $idx => $jobName) {
                $group->jobs()->create([
                    'name' => $jobName,
                    'sort_order' => $idx + 1,
                    'target_employees' => null,
                ]);
            }

            // Sinkronkan juga kelompok baru ke Google Spreadsheet
            $this->googleSheetService->appendGroup($group);
        }

        return $group;
    }
    /**
     * Tampilkan tabel master karyawan dengan filter dan live search
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $areaWla = $request->input('area_wla');
        $division = $request->input('division');
        $dept = $request->input('dept');
        $plant = $request->input('plant');

        $query = Employee::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nrp', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%");
            });
        }

        if ($areaWla) {
            $cleanArea = trim((string)$areaWla);
            $query->where(function ($q) use ($cleanArea) {
                $q->whereRaw('LOWER(TRIM(area_wla)) = ?', [strtolower($cleanArea)])
                  ->orWhereHas('groupEmployees.group', function ($gq) use ($cleanArea) {
                      $gq->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanArea)]);
                  });
            });
        }

        if ($division) {
            $query->where('division', $division);
        }

        if ($dept) {
            $query->where('dept', $dept);
        }

        if ($plant) {
            $query->where('plant', $plant);
        }

        $employees = $query->orderBy('nrp')->paginate(15)->withQueryString();

        // Data untuk dropdown filter - selalu urutkan kelompok mulai dari Kelompok 1..12
        $groups = Group::orderBy('number')->get();
        $groupAreaNames = $groups->pluck('name')->filter()->values()->toArray();
        $groupAreaNamesLower = array_map('strtolower', array_map('trim', $groupAreaNames));

        $allEmployeeAreas = Employee::distinct()
            ->whereNotNull('area_wla')
            ->where('area_wla', '!=', '')
            ->orderBy('area_wla')
            ->pluck('area_wla')
            ->toArray();

        // Hanya sertakan area lain jika benar-benar tidak terdaftar pada kelompok mana pun (case-insensitive)
        $otherAreas = [];
        foreach ($allEmployeeAreas as $empArea) {
            $lowerEmpArea = strtolower(trim($empArea));
            if (!in_array($lowerEmpArea, $groupAreaNamesLower)) {
                $otherAreas[] = $empArea;
            }
        }
        $areas = collect(array_merge($groupAreaNames, $otherAreas));

        $divisions = Employee::distinct()->whereNotNull('division')->where('division', '!=', '')->orderBy('division')->pluck('division');
        $depts = Employee::distinct()->whereNotNull('dept')->where('dept', '!=', '')->orderBy('dept')->pluck('dept');
        $plants = Employee::distinct()->whereNotNull('plant')->where('plant', '!=', '')->orderBy('plant')->pluck('plant');

        // Hitungan per kategori untuk preview real-time saat menghapus
        $areaCounts = Employee::whereNotNull('area_wla')
            ->where('area_wla', '!=', '')
            ->select('area_wla', DB::raw('count(*) as total'))
            ->groupBy('area_wla')
            ->pluck('total', 'area_wla')
            ->toArray();

        $divisionCounts = Employee::whereNotNull('division')
            ->where('division', '!=', '')
            ->select('division', DB::raw('count(*) as total'))
            ->groupBy('division')
            ->pluck('total', 'division')
            ->toArray();

        $deptCounts = Employee::whereNotNull('dept')
            ->where('dept', '!=', '')
            ->select('dept', DB::raw('count(*) as total'))
            ->groupBy('dept')
            ->pluck('total', 'dept')
            ->toArray();

        // Statistik ringkas
        $totalEmployees = Employee::count();
        $totalDepts = $depts->count();
        $totalPlants = $plants->count();
        $totalAreas = $areas->count();

        return view('wola.employees.index', compact(
            'employees',
            'groups',
            'areas',
            'divisions',
            'depts',
            'plants',
            'search',
            'areaWla',
            'division',
            'dept',
            'plant',
            'totalEmployees',
            'totalDepts',
            'totalPlants',
            'totalAreas',
            'areaCounts',
            'divisionCounts',
            'deptCounts'
        ));
    }

    /**
     * Form tambah karyawan baru
     */
    public function create()
    {
        $groups = Group::orderBy('number')->get();
        $areas = $groups->pluck('name');
        $depts = Employee::distinct()->whereNotNull('dept')->where('dept', '!=', '')->orderBy('dept')->pluck('dept');
        $divisions = Employee::distinct()->whereNotNull('division')->where('division', '!=', '')->orderBy('division')->pluck('division');
        $plants = Employee::distinct()->whereNotNull('plant')->where('plant', '!=', '')->orderBy('plant')->pluck('plant');

        $sheetSettings = $this->googleSheetService->getSettings();
        $isWebhookConfigured = !empty($sheetSettings['apps_script_url']);

        return view('wola.employees.create', compact('groups', 'areas', 'depts', 'divisions', 'plants', 'isWebhookConfigured', 'sheetSettings'));
    }

    /**
     * Simpan data karyawan baru
     */
    public function store(Request $request)
    {
        // Tangani jika pengguna memilih opsi tambah kelompok baru via input custom
        $rawArea = $request->input('area_wla');
        if ($rawArea === '__NEW__' || $request->filled('area_wla_custom')) {
            $rawArea = $request->input('area_wla_custom');
        }

        $validated = $request->validate([
            'area_wla' => 'nullable|string|max:150',
            'area_wla_custom' => 'nullable|string|max:150',
            'nrp' => 'required|string|max:30|unique:employees,nrp',
            'fullname' => 'required|string|max:150',
            'division' => 'nullable|string|max:100',
            'dept' => 'required|string|max:100',
            'section' => 'nullable|string|max:100',
            'plant' => 'required|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nrp.required' => 'EMPLOYEENO (NRP) wajib diisi.',
            'nrp.unique' => 'EMPLOYEENO ini sudah terdaftar di sistem.',
            'fullname.required' => 'FULLNAME wajib diisi.',
            'dept.required' => 'DEPT wajib diisi.',
            'plant.required' => 'PLANT wajib diisi.',
            'avatar.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $group = null;
        if (!empty($rawArea)) {
            $group = $this->findOrCreateGroup($rawArea);
            $validated['area_wla'] = $group ? $group->name : trim($rawArea);
        } else {
            $validated['area_wla'] = null;
        }

        unset($validated['area_wla_custom']);

        $validated['division'] = !empty($validated['division']) ? $validated['division'] : (!empty($validated['section']) ? $validated['section'] : $validated['dept']);
        $validated['section'] = !empty($validated['section']) ? $validated['section'] : $validated['division'];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $employee = Employee::create($validated);

        // Jika AREA WLA terkait dengan kelompok, masukkan otomatis sebagai sasaran penilaian kelompok
        if ($group) {
            GroupEmployee::firstOrCreate([
                'group_id' => $group->id,
                'employee_id' => $employee->id,
            ]);
        }

        ActivityLog::log(
            'CREATE_EMPLOYEE',
            "Menambahkan karyawan baru: {$employee->fullname} (EMPLOYEENO: {$employee->nrp}) - Area WLA: " . ($group ? $group->name : '-'),
            ['nrp' => $employee->nrp, 'dept' => $employee->dept, 'group_id' => $group?->id]
        );

        // Sinkronisasi otomatis ke Google Spreadsheet via Webhook Apps Script
        $this->googleSheetService->appendEmployee($employee);

        return redirect()->route('wola.employees.index')->with('success', "Data karyawan {$employee->fullname} berhasil ditambahkan.");
    }

    /**
     * Form edit karyawan
     */
    public function edit(Employee $employee)
    {
        $groups = Group::orderBy('number')->get();
        $areas = $groups->pluck('name');
        $depts = Employee::distinct()->whereNotNull('dept')->where('dept', '!=', '')->orderBy('dept')->pluck('dept');
        $divisions = Employee::distinct()->whereNotNull('division')->where('division', '!=', '')->orderBy('division')->pluck('division');
        $plants = Employee::distinct()->whereNotNull('plant')->where('plant', '!=', '')->orderBy('plant')->pluck('plant');

        return view('wola.employees.edit', compact('employee', 'groups', 'areas', 'depts', 'divisions', 'plants'));
    }

    /**
     * Update data karyawan
     */
    public function update(Request $request, Employee $employee)
    {
        $rawArea = $request->input('area_wla');
        if ($rawArea === '__NEW__' || $request->filled('area_wla_custom')) {
            $rawArea = $request->input('area_wla_custom');
        }

        $validated = $request->validate([
            'area_wla' => 'nullable|string|max:150',
            'area_wla_custom' => 'nullable|string|max:150',
            'nrp' => 'required|string|max:30|unique:employees,nrp,' . $employee->id,
            'fullname' => 'required|string|max:150',
            'division' => 'nullable|string|max:100',
            'dept' => 'required|string|max:100',
            'section' => 'nullable|string|max:100',
            'plant' => 'required|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nrp.required' => 'EMPLOYEENO (NRP) wajib diisi.',
            'nrp.unique' => 'EMPLOYEENO ini sudah digunakan oleh karyawan lain.',
            'fullname.required' => 'FULLNAME wajib diisi.',
            'dept.required' => 'DEPT wajib diisi.',
            'plant.required' => 'PLANT wajib diisi.',
            'avatar.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $group = null;
        if (!empty($rawArea)) {
            $group = $this->findOrCreateGroup($rawArea);
            $validated['area_wla'] = $group ? $group->name : trim($rawArea);
        } else {
            $validated['area_wla'] = null;
        }

        unset($validated['area_wla_custom']);

        $validated['division'] = !empty($validated['division']) ? $validated['division'] : (!empty($validated['section']) ? $validated['section'] : $validated['dept']);
        $validated['section'] = !empty($validated['section']) ? $validated['section'] : $validated['division'];

        if ($request->hasFile('avatar')) {
            if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
                Storage::disk('public')->delete($employee->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $oldNrp = $employee->nrp;
        $oldName = $employee->fullname;

        $employee->update($validated);

        if ($group) {
            GroupEmployee::firstOrCreate([
                'group_id' => $group->id,
                'employee_id' => $employee->id,
            ]);
        }

        // Cari dan perbarui User terkait jika ada (sinkronisasi nama & username NRP)
        $linkedUser = User::where('username', $oldNrp)
            ->orWhere('name', $oldName)
            ->orWhere('employee_id', $employee->id)
            ->first();

        if ($linkedUser) {
            $linkedUser->update([
                'name' => $employee->fullname,
                'username' => $employee->nrp,
            ]);
        }

        ActivityLog::log(
            'UPDATE_EMPLOYEE',
            "Mengubah data karyawan: {$employee->fullname} (EMPLOYEENO: {$employee->nrp})",
            ['nrp' => $employee->nrp, 'dept' => $employee->dept, 'group_id' => $group?->id]
        );

        // Sinkronisasi perubahan relasional ke seluruh tab Google Spreadsheet berdasarkan NRP (Karyawan, tim_penilai, Hasil_Penilaian, Pengguna)
        $this->googleSheetService->cascadeUpdatePerson([
            'nrp' => $employee->nrp,
            'old_nrp' => $oldNrp,
            'name' => $employee->fullname,
            'old_name' => $oldName,
            'email' => $linkedUser?->email ?? '',
            'role' => $linkedUser?->roles->pluck('name')->first() ?? 'ketua',
            'group_number' => $group?->number ?? '',
            'group_name' => $group?->name ?? $employee->area_wla ?? '',
        ]);

        $this->googleSheetService->updateEmployee($employee);

        return redirect()->route('wola.employees.index')->with('success', "Data karyawan {$employee->fullname} berhasil diperbarui.");
    }

    /**
     * Hapus satu data karyawan (dengan pembersihan relasi aman)
     */
    public function destroy(Employee $employee)
    {
        $name = $employee->fullname;
        $nrp = $employee->nrp;

        $this->performDeleteEmployees(
            [$employee->id],
            'DELETE_EMPLOYEE',
            "Menghapus data karyawan: {$name} (EMPLOYEENO {$nrp})",
            ['nrp' => $nrp]
        );

        return redirect()->route('wola.employees.index')->with('success', "Data karyawan {$name} ({$nrp}) berhasil dihapus.");
    }

    /**
     * Hapus karyawan terpilih (Checkbox bulk delete)
     */
    public function destroyBulk(Request $request)
    {
        $raw = $request->input('selected_ids');
        if (empty($raw) && $request->filled('selected_ids_str')) {
            $raw = explode(',', $request->input('selected_ids_str'));
        } elseif (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        $ids = array_values(array_filter(array_map('intval', (array) $raw)));

        if (empty($ids)) {
            return redirect()->route('wola.employees.index')
                ->with('error', 'Pilih minimal satu karyawan untuk dihapus.');
        }

        $count = count($ids);

        $deleted = $this->performDeleteEmployees(
            $ids,
            'DELETE_EMPLOYEES_BULK',
            "Menghapus {$count} data karyawan terpilih melalui checkbox",
            ['ids' => $ids]
        );

        return redirect()->route('wola.employees.index')
            ->with('success', "Sebanyak {$deleted} data karyawan terpilih berhasil dihapus.");
    }

    /**
     * Hapus seluruh karyawan dalam satu kategori (Area WLA, Divisi, atau Dept)
     */
    public function destroyByCategory(Request $request)
    {
        $validated = $request->validate([
            'category_type' => 'required|in:area_wla,division,dept',
            'category_value' => 'required|string',
        ], [
            'category_type.required' => 'Pilih tipe kategori (Area WLA, Divisi, atau Dept).',
            'category_value.required' => 'Pilih nilai kategori yang ingin dihapus.',
        ]);

        $type = $validated['category_type'];
        $rawVal = trim($validated['category_value']);
        $value = urldecode($rawVal);

        $typeLabels = [
            'area_wla' => 'Area WLA (Kelompok Kerja)',
            'division' => 'Divisi',
            'dept' => 'Departemen',
        ];
        $typeLabel = $typeLabels[$type] ?? $type;

        $query = Employee::where(function ($q) use ($type, $value, $rawVal) {
            $q->where($type, $value)
              ->orWhere($type, $rawVal)
              ->orWhereRaw("LOWER({$type}) = LOWER(?)", [$value]);
        });

        $ids = $query->pluck('id')->toArray();
        $count = count($ids);

        if ($count === 0) {
            return redirect()->route('wola.employees.index')
                ->with('error', "Tidak ditemukan data karyawan pada {$typeLabel}: \"{$value}\".");
        }

        $deleted = $this->performDeleteEmployees(
            $ids,
            'DELETE_EMPLOYEES_BY_CATEGORY',
            "Menghapus seluruh karyawan ({$count} orang) pada {$typeLabel}: {$value}",
            ['type' => $type, 'value' => $value]
        );

        return redirect()->route('wola.employees.index')
            ->with('success', "Seluruh karyawan ({$deleted} orang) pada {$typeLabel} \"{$value}\" berhasil dihapus.");
    }

    /**
     * Helper privat: Hapus sekumpulan ID karyawan beserta pembersihan relasi yang aman
     */
    protected function performDeleteEmployees(array $ids, string $logAction, string $logDescription, array $logMeta = []): int
    {
        if (empty($ids)) {
            return 0;
        }

        return DB::transaction(function () use ($ids, $logAction, $logDescription, $logMeta) {
            // 1. Lepaskan relasi foreign key User -> Employee agar tidak melanggar SQLite FK constraint
            User::whereIn('employee_id', $ids)->update(['employee_id' => null]);

            // 2. Lepaskan komentar jabatan yang menautkan employee
            JobComment::whereIn('employee_id', $ids)->update(['employee_id' => null]);

            // 3. Ambil daftar ID GroupAssessor yang bertaut pada karyawan yang akan dihapus
            $assessorIds = GroupAssessor::whereIn('employee_id', $ids)->pluck('id')->toArray();

            // 4. Hapus seluruh rekaman penilaian WOLA yang bertaut pada karyawan ini (baik sebagai karyawan dinilai maupun penilai)
            Assessment::whereIn('employee_id', $ids)
                ->when(!empty($assessorIds), function ($q) use ($assessorIds) {
                    $q->orWhereIn('assessor_id', $assessorIds);
                })
                ->delete();

            // 5. Hapus relasi penilai dan relasi sasaran kelompok
            if (!empty($assessorIds)) {
                GroupAssessor::whereIn('id', $assessorIds)->delete();
            }
            GroupEmployee::whereIn('employee_id', $ids)->delete();

            // 6. Hapus file avatar fisik dari storage publik jika ada
            $avatars = Employee::whereIn('id', $ids)->whereNotNull('avatar')->pluck('avatar');
            foreach ($avatars as $avatarPath) {
                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
            }

            // 7. Ambil data karyawan sebelum dihapus & hapus dari database
            $deletedRecords = Employee::whereIn('id', $ids)->get(['id', 'nrp', 'fullname']);
            $deletedCount = Employee::whereIn('id', $ids)->delete();

            // Sinkronkan penghapusan ke Google Spreadsheet
            foreach ($deletedRecords as $emp) {
                $this->googleSheetService->deleteEmployee($emp->nrp, $emp->fullname);
            }

            // 8. Catat audit trail / log aktivitas sistem
            ActivityLog::log(
                $logAction,
                $logDescription,
                array_merge(['count' => $deletedCount], $logMeta)
            );

            // 9. Re-sync Ketua sebagai Penilai untuk kelompok yang terdampak
            $groupsWithLeader = Group::has('leader')->with('leader')->get();
            foreach ($groupsWithLeader as $grp) {
                $grp->syncLeaderAssessor();
            }

            return $deletedCount;
        });
    }

    /**
     * Hapus SEMUA data karyawan (Truncate/Bulk Delete Aman)
     */
    public function destroyAll()
    {
        $total = Employee::count();
        if ($total === 0) {
            return redirect()->route('wola.employees.index')->with('error', 'Tidak ada data karyawan untuk dihapus.');
        }

        DB::transaction(function () use ($total) {
            // 1. Lepaskan relasi foreign key User -> Employee agar tidak melanggar SQLite FK constraint
            User::whereNotNull('employee_id')->update(['employee_id' => null]);

            // 2. Lepaskan komentar jabatan yang menautkan employee
            JobComment::whereNotNull('employee_id')->update(['employee_id' => null]);

            // 3. Hapus seluruh data penilaian WOLA
            Assessment::query()->delete();

            // 4. Hapus seluruh relasi karyawan sasaran & penilai kelompok
            GroupEmployee::query()->delete();
            GroupAssessor::query()->delete();

            // 5. Hapus file avatar fisik dari storage publik jika ada
            $avatars = Employee::whereNotNull('avatar')->pluck('avatar');
            foreach ($avatars as $avatarPath) {
                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
            }

            // 6. Hapus seluruh data record karyawan
            Employee::query()->delete();

            ActivityLog::log(
                'DELETE_ALL_EMPLOYEES',
                "Menghapus SELURUH ({$total}) data master karyawan dari sistem WOLA.",
                ['total_deleted' => $total]
            );
        });

        // Hapus juga seluruh baris di Google Spreadsheet jika webhook aktif
        $sheetSettings = $this->googleSheetService->getSettings();
        if (!empty($sheetSettings['apps_script_url'])) {
            $this->googleSheetService->clearEmployeesSheet();
        }

        return redirect()->route('wola.employees.index')->with('success', "Seluruh data karyawan berhasil dibersihkan.");
    }

    /**
     * Download template resmi Excel/CSV (Format sesuai Data Sales & Marketing (2W & 4W.xlsx)
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_karyawan_wola.csv"',
        ];

        $columns = ['AREA WLA', 'EMPLOYEENO', 'FULLNAME', 'DIVISION', 'DEPT', 'PLANT'];
        $sampleData = [
            ['Sales & Marketing (2W & 4W)', '653', 'ISNANTO', 'SALES & MARKETING DIV', 'SALES & MARKETING 4W', 'CIKARANG'],
            ['Sales & Marketing (2W & 4W)', '950', 'ANDIANAMARA', 'SALES & MARKETING DIV', 'SALES & MARKETING 4W', 'CIKARANG'],
            ['Capacity & Prod Plan Control', '1101', 'Ahmad Fauzi', 'PRODUCTION CONTROL DIV', 'CAPACITY PLAN', 'CIKARANG'],
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview data Excel/CSV sebelum disimpan ke database
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file = $request->file('file');
        $rawSheets = Excel::toArray(new EmployeesImport, $file);

        if (empty($rawSheets) || empty($rawSheets[0])) {
            return back()->with('error', 'File Excel/CSV kosong atau format kolom tidak sesuai.');
        }

        $rows = $rawSheets[0];
        $previewData = [];
        $validCount = 0;
        $duplicateCount = 0;
        $invalidCount = 0;

        $existingNrps = Employee::pluck('nrp')->flip()->toArray();

        foreach ($rows as $index => $row) {
            // Normalisasi key (case-insensitive tanpa spasi/simbol)
            $normalizedRow = [];
            foreach ($row as $k => $v) {
                $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$k));
                $normalizedRow[$cleanKey] = $v;
            }

            $areaWla = trim($normalizedRow['areawla'] ?? $normalizedRow['areawola'] ?? $normalizedRow['area'] ?? $normalizedRow['kelompok'] ?? '');
            $nrp = trim($normalizedRow['employeeno'] ?? $normalizedRow['employeenumber'] ?? $normalizedRow['nopegawai'] ?? $normalizedRow['nrp'] ?? '');
            $fullname = trim($normalizedRow['fullname'] ?? $normalizedRow['namalengkap'] ?? $normalizedRow['nama'] ?? '');
            $division = trim($normalizedRow['division'] ?? $normalizedRow['divisi'] ?? $normalizedRow['section'] ?? $normalizedRow['seksi'] ?? '');
            $dept = trim($normalizedRow['dept'] ?? $normalizedRow['departemen'] ?? '');
            $plant = trim($normalizedRow['plant'] ?? '');

            // Abaikan baris yang benar-benar kosong
            if (empty($nrp) && empty($fullname)) {
                continue;
            }

            $errors = [];
            if (empty($nrp)) $errors[] = 'EMPLOYEENO kosong';
            if (empty($fullname)) $errors[] = 'FULLNAME kosong';
            if (empty($dept)) $errors[] = 'DEPT kosong';
            if (empty($plant)) $errors[] = 'PLANT kosong';

            $status = 'VALID';
            if (!empty($errors)) {
                $status = 'INVALID';
                $invalidCount++;
            } elseif (isset($existingNrps[$nrp])) {
                $status = 'DUPLICATE';
                $duplicateCount++;
            } else {
                $validCount++;
            }

            $previewData[] = [
                'row_number' => $index + 2, // Baris 1 header
                'area_wla' => $areaWla,
                'nrp' => $nrp,
                'fullname' => $fullname,
                'division' => $division,
                'dept' => $dept,
                'plant' => $plant,
                'status' => $status,
                'errors' => implode(', ', $errors),
            ];
        }

        session([
            'import_preview_data' => $previewData,
            'import_filename' => $file->getClientOriginalName(),
        ]);

        return view('wola.employees.preview', compact(
            'previewData',
            'validCount',
            'duplicateCount',
            'invalidCount'
        ));
    }

    /**
     * Konfirmasi eksekusi penyimpanan hasil preview import
     */
    public function importConfirm(Request $request)
    {
        $previewData = session('import_preview_data');
        if (!$previewData || empty($previewData)) {
            return redirect()->route('wola.employees.index')->with('error', 'Sesi data preview telah kedaluwarsa. Silakan upload ulang.');
        }

        $action = $request->input('action_type', 'insert_only'); // 'insert_only' atau 'upsert'
        $insertedCount = 0;
        $updatedCount = 0;
        $groupCache = [];

        foreach ($previewData as $item) {
            if ($item['status'] === 'INVALID') {
                continue;
            }

            // Temukan atau buat kelompok berdasarkan AREA WLA
            $group = null;
            if (!empty($item['area_wla'])) {
                $rawArea = trim($item['area_wla']);
                if (!isset($groupCache[$rawArea])) {
                    $groupCache[$rawArea] = $this->findOrCreateGroup($rawArea);
                }
                $group = $groupCache[$rawArea];
            }

            $data = [
                'area_wla' => $group ? $group->name : ($item['area_wla'] ?: null),
                'nrp' => $item['nrp'],
                'fullname' => $item['fullname'],
                'division' => $item['division'] ?: null,
                'dept' => $item['dept'],
                'section' => $item['division'] ?: $item['dept'],
                'plant' => $item['plant'],
            ];

            if ($item['status'] === 'VALID') {
                $emp = Employee::create($data);
                $insertedCount++;

                // Hubungkan otomatis ke Kelompok WOLA
                if ($group) {
                    GroupEmployee::firstOrCreate([
                        'group_id' => $group->id,
                        'employee_id' => $emp->id,
                    ]);
                }
            } elseif ($item['status'] === 'DUPLICATE') {
                $existingEmp = Employee::where('nrp', $item['nrp'])->first();

                if ($action === 'upsert' && $existingEmp) {
                    $existingEmp->update($data);
                    $updatedCount++;

                    if ($group) {
                        GroupEmployee::firstOrCreate([
                            'group_id' => $group->id,
                            'employee_id' => $existingEmp->id,
                        ]);
                    }
                } elseif ($action === 'insert_only' && $existingEmp && $group) {
                    // Data tidak dimasukkan ganda, namun tetap dimasukkan ke dalam sasaran kelompok jika belum ada
                    if (empty($existingEmp->area_wla)) {
                        $existingEmp->update(['area_wla' => $group->name]);
                    }
                    GroupEmployee::firstOrCreate([
                        'group_id' => $group->id,
                        'employee_id' => $existingEmp->id,
                    ]);
                }
            }
        }

        ActivityLog::log(
            'IMPORT_EMPLOYEES',
            "Mengimpor data karyawan via Excel/CSV: {$insertedCount} data baru ditambahkan, {$updatedCount} diperbarui.",
            ['inserted' => $insertedCount, 'updated' => $updatedCount]
        );

        session()->forget(['import_preview_data', 'import_filename']);

        return redirect()->route('wola.employees.index')->with(
            'success',
            "Import berhasil! {$insertedCount} data karyawan baru tersimpan" . ($updatedCount > 0 ? " dan {$updatedCount} data diperbarui." : ".")
        );
    }

    /**
     * Export data karyawan ke format Excel (.xlsx)
     */
    public function export(Request $request)
    {
        $search = $request->input('search');
        $areaWla = $request->input('area_wla');
        $division = $request->input('division');
        $dept = $request->input('dept');
        $section = $request->input('section');
        $plant = $request->input('plant');

        $filename = 'Master_Karyawan_PT_Musashi_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new EmployeesExport($search, $dept, $section, $plant, $areaWla, $division), $filename);
    }
}
