<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Group;
use App\Models\Job;
use App\Models\User;
use App\Models\Assessment;
use App\Models\JobComment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class GoogleSheetService
{
    protected string $settingsFile = 'google_sheet_settings.json';
    protected string $defaultUrl = 'https://docs.google.com/spreadsheets/d/15fKc1XUf5-647t-a5LC2j53FsGWE4JDl012ZVoZYgr0/edit?usp=sharing';
    protected string $defaultAppsScriptUrl = 'https://script.google.com/macros/s/AKfycbzR3VH4dKoG4i8mnT-7gv3ZBZuXcb4CzxD8GvB7_SPXiRsM4iTc2bDS7574darO_j2v/exec';

    /**
     * Dapatkan konfigurasi saat ini
     */
    public function getSettings(): array
    {
        $defaultUrl = env('GOOGLE_SHEET_URL', $this->defaultUrl);
        $defaultScriptUrl = env('GOOGLE_APPS_SCRIPT_URL', $this->defaultAppsScriptUrl);

        if (Storage::disk('local')->exists($this->settingsFile)) {
            $data = json_decode(Storage::disk('local')->get($this->settingsFile), true);
            if (is_array($data) && !empty($data['url'])) {
                if (empty($data['apps_script_url']) && !empty($defaultScriptUrl)) {
                    $data['apps_script_url'] = $defaultScriptUrl;
                }
                return $data;
            }
        }

        return [
            'url' => $defaultUrl,
            'spreadsheet_id' => $this->extractSpreadsheetId($defaultUrl),
            'apps_script_url' => $defaultScriptUrl,
            'last_synced_at' => null,
            'last_sync_status' => null,
            'last_sync_summary' => null,
        ];
    }

    /**
     * Simpan konfigurasi Google Sheet
     */
    public function saveSettings(string $url, ?string $appsScriptUrl = null, ?array $additional = []): array
    {
        $current = $this->getSettings();
        $spreadsheetId = $this->extractSpreadsheetId($url);

        $settings = array_merge($current, [
            'url' => trim($url),
            'spreadsheet_id' => $spreadsheetId,
            'apps_script_url' => $appsScriptUrl !== null ? (trim($appsScriptUrl) ?: null) : ($current['apps_script_url'] ?? null),
        ], $additional);

        try {
            Storage::disk('local')->put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Exception $e) {
            Log::warning("Penyimpanan lokal google_sheet_settings.json dilewati di environment read-only: " . $e->getMessage());
        }

        return $settings;
    }

    /**
     * Ekstrak Spreadsheet ID dari URL atau String mentah
     */
    public function extractSpreadsheetId(string $input): string
    {
        $input = trim($input);
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $input, $matches)) {
            return $matches[1];
        }
        return $input;
    }

    /**
     * Ambil data baris dari Google Sheet (CSV format)
     */
    public function fetchSheetRows(string $sheetName, ?string $spreadsheetId = null): array
    {
        $id = $spreadsheetId ?: ($this->getSettings()['spreadsheet_id'] ?? null);
        if (!$id) {
            throw new \Exception('Spreadsheet ID belum dikonfigurasi.');
        }

        $url = "https://docs.google.com/spreadsheets/d/{$id}/gviz/tq?tqx=out:csv&sheet=" . urlencode($sheetName);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WolaMusashi/1.0',
                    'Accept' => 'text/csv,text/plain,*/*',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception("Gagal menghubungi Google Sheet: HTTP {$response->status()}");
            }

            $csvContent = trim($response->body());
            if (empty($csvContent)) {
                return [];
            }

            return $this->parseCsv($csvContent);
        } catch (\Exception $e) {
            Log::error("GoogleSheetService fetch error on [{$sheetName}]: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse string CSV menjadi array asosiatif dengan key yang dinormalisasi
     */
    public function parseCsv(string $csvContent): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $csvContent);
        if (empty($lines)) {
            return [];
        }

        $headers = [];
        $headerRow = str_getcsv(array_shift($lines));
        foreach ($headerRow as $h) {
            // Normalisasi: huruf kecil, ganti spasi/karakter khusus dengan underscore
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($h)), '_'));
            $headers[] = $normalized;
        }

        $rows = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $colValues = str_getcsv($line);
            if (count($colValues) === 0 || (count($colValues) === 1 && trim($colValues[0]) === '')) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if (!empty($header)) {
                    $row[$header] = isset($colValues[$index]) ? trim($colValues[$index]) : '';
                }
            }

            // Validasi baris memiliki setidaknya satu kolom yang terisi
            $hasContent = false;
            foreach ($row as $val) {
                if ($val !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if ($hasContent) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Sinkronkan Data Karyawan dari Google Sheet
     */
    public function syncEmployees(): array
    {
        // Coba baca dari tab 'Karyawan', jika kosong coba tab 'Database'
        $rows = [];
        $sourceSheet = 'Karyawan';
        try {
            $rows = $this->fetchSheetRows('Karyawan');
        } catch (\Exception $e) {
            // Ignore & fallback to Database
        }

        if (empty($rows)) {
            try {
                $rows = $this->fetchSheetRows('Database');
                $sourceSheet = 'Database';
            } catch (\Exception $e) {
                // Continue with empty
            }
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => "Tab 'Karyawan' atau 'Database' di Google Sheet belum memiliki data baris.",
                'added' => 0,
                'updated' => 0,
                'total' => 0,
            ];
        }

        $added = 0;
        $updated = 0;

        foreach ($rows as $row) {
            // Deteksi kolom NRP
            $nrp = $this->findValue($row, ['nrp', 'nik', 'no_karyawan', 'employeeno', 'id_karyawan', 'id']);
            // Deteksi kolom Nama Lengkap
            $fullname = $this->findValue($row, ['nama_lengkap', 'nama', 'fullname', 'name', 'nama_karyawan']);

            if (!$nrp || !$fullname) {
                continue;
            }

            $dept = $this->findValue($row, ['departemen', 'dept', 'department']) ?: 'MARKETING';
            $division = $this->findValue($row, ['divisi', 'division']) ?: 'SALES & MARKETING';
            $section = $this->findValue($row, ['seksi', 'section']);
            $line = $this->findValue($row, ['line', 'posisi_line']);
            $plant = $this->findValue($row, ['plant', 'lokasi', 'factory']) ?: 'CIKARANG';
            $areaWla = $this->findValue($row, ['area_wla', 'area', 'wla', 'area_kerja']);

            $existing = Employee::where('nrp', $nrp)->first();

            $employeeData = [
                'fullname' => strtoupper(trim($fullname)),
                'dept' => strtoupper(trim($dept)),
                'division' => strtoupper(trim($division)),
                'section' => $section ? strtoupper(trim($section)) : ($existing && $existing->section ? $existing->section : '-'),
                'line' => $line ? strtoupper(trim($line)) : ($existing && $existing->line ? $existing->line : '-'),
                'plant' => strtoupper(trim($plant)),
                'area_wla' => $areaWla ? strtoupper(trim($areaWla)) : ($existing && $existing->area_wla ? $existing->area_wla : null),
                'is_active' => true,
            ];

            if ($existing) {
                $existing->update($employeeData);
                $updated++;
            } else {
                Employee::create(array_merge(['nrp' => trim($nrp)], $employeeData));
                $added++;
            }
        }

        return [
            'success' => true,
            'source' => $sourceSheet,
            'added' => $added,
            'updated' => $updated,
            'total' => $added + $updated,
            'message' => "Sinkronisasi karyawan berhasil dari tab '{$sourceSheet}': {$added} ditambah, {$updated} diperbarui.",
        ];
    }

    /**
     * Sinkronkan Data Kelompok Kerja
     */
    public function syncGroups(): array
    {
        $rows = [];
        try {
            $rows = $this->fetchSheetRows('Kelompok');
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Tab 'Kelompok' tidak ditemukan di Google Sheet.",
                'added' => 0,
                'updated' => 0,
            ];
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => "Tab 'Kelompok' belum memiliki baris data.",
                'added' => 0,
                'updated' => 0,
            ];
        }

        $added = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $number = (int) $this->findValue($row, ['nomor_kelompok', 'nomor', 'number', 'no', 'kelompok']);
            $name = $this->findValue($row, ['nama_kelompok', 'nama', 'name', 'area_kelompok']);
            $plant = $this->findValue($row, ['plant', 'lokasi']) ?: 'CIKARANG';

            if ($number <= 0 || !$name) {
                continue;
            }

            $existing = Group::where('number', $number)->first();
            if ($existing) {
                $existing->update([
                    'name' => trim($name),
                    'plant' => strtoupper(trim($plant)),
                    'is_active' => true,
                ]);
                $updated++;
            } else {
                Group::create([
                    'number' => $number,
                    'name' => trim($name),
                    'plant' => strtoupper(trim($plant)),
                    'is_active' => true,
                ]);
                $added++;
            }
        }

        return [
            'success' => true,
            'added' => $added,
            'updated' => $updated,
            'total' => $added + $updated,
            'message' => "Sinkronisasi kelompok berhasil: {$added} ditambah, {$updated} diperbarui.",
        ];
    }

    /**
     * Sinkronkan Data Posisi Job
     */
    public function syncJobs(): array
    {
        $rows = [];
        try {
            $rows = $this->fetchSheetRows('Posisi_Job');
        } catch (\Exception $e) {
            try {
                $rows = $this->fetchSheetRows('Jobs');
            } catch (\Exception $ex) {
                return [
                    'success' => false,
                    'message' => "Tab 'Posisi_Job' atau 'Jobs' tidak ditemukan di Google Sheet.",
                    'added' => 0,
                    'updated' => 0,
                ];
            }
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => "Tab 'Posisi_Job' belum memiliki baris data.",
                'added' => 0,
                'updated' => 0,
            ];
        }

        $added = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $groupNum = (int) $this->findValue($row, ['nomor_kelompok', 'kelompok', 'group_number', 'no_kelompok', 'no']);
            $code = $this->findValue($row, ['kode_job', 'kode', 'code', 'job_code']);
            $name = $this->findValue($row, ['nama_job', 'nama', 'name', 'job_name']);
            $targetQuota = $this->findValue($row, ['target_kuota', 'target_employees', 'target', 'kuota']);

            if (!$code || !$name) {
                continue;
            }

            $group = null;
            if ($groupNum > 0) {
                $group = Group::where('number', $groupNum)->first();
            }

            if (!$group) {
                $group = Group::first();
            }

            $existing = Job::where('group_id', $group->id)->where('code', $code)->first();

            $jobData = [
                'group_id' => $group->id,
                'name' => trim($name),
                'target_employees' => is_numeric($targetQuota) ? (int)$targetQuota : null,
            ];

            if ($existing) {
                $existing->update($jobData);
                $updated++;
            } else {
                Job::create(array_merge(['code' => trim($code)], $jobData));
                $added++;
            }
        }

        return [
            'success' => true,
            'added' => $added,
            'updated' => $updated,
            'total' => $added + $updated,
            'message' => "Sinkronisasi posisi job berhasil: {$added} ditambah, {$updated} diperbarui.",
        ];
    }

    /**
     * Sinkronkan Data Tim Penilai dari Google Sheet (tab 'tim_penilai')
     */
    public function syncAssessors(): array
    {
        $rows = [];
        try {
            $rows = $this->fetchSheetRows('tim_penilai');
        } catch (\Exception $e) {
            try {
                $rows = $this->fetchSheetRows('Tim_Penilai');
            } catch (\Exception $ex) {
                return [
                    'success' => false,
                    'message' => "Tab 'tim_penilai' tidak ditemukan di Google Sheet.",
                    'added' => 0,
                    'updated' => 0,
                ];
            }
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => "Tab 'tim_penilai' belum memiliki baris data.",
                'added' => 0,
                'updated' => 0,
            ];
        }

        $added = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $groupNumber = (int) $this->findValue($row, ['nomor_kelompok', 'kelompok', 'group_number', 'no', 'nomor']);
            $nrp = $this->findValue($row, ['nrp_penilai', 'nrp', 'nik', 'id_penilai', 'no_karyawan']);
            $name = $this->findValue($row, ['nama_penilai', 'nama', 'fullname', 'penilai', 'nama_lengkap']);
            $roleStr = strtolower($this->findValue($row, ['peran', 'role', 'jabatan_tim']) ?: '');
            $isLeader = str_contains($roleStr, 'ketua');

            if ($groupNumber <= 0 || (!$nrp && !$name)) {
                continue;
            }

            $group = Group::where('number', $groupNumber)->first();
            if (!$group) continue;

            $employee = null;
            if ($nrp) {
                $employee = Employee::where('nrp', trim($nrp))->first();
            }
            if (!$employee && $name) {
                $employee = Employee::where('fullname', trim($name))->first();
            }

            if (!$employee && $name) {
                $employee = Employee::create([
                    'area_wla' => $group->name,
                    'nrp' => $nrp ?: ('P-' . str_pad((string)((Employee::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT)),
                    'fullname' => strtoupper(trim($name)),
                    'division' => $group->name,
                    'dept' => $group->name,
                    'section' => $isLeader ? 'Ketua Kelompok' : 'Penilai',
                    'plant' => $group->plant ?: 'CIKARANG',
                ]);
            }

            if (!$employee) continue;

            $existing = \App\Models\GroupAssessor::where('group_id', $group->id)
                ->where('employee_id', $employee->id)
                ->first();

            if ($existing) {
                if ($isLeader && !$existing->is_leader) {
                    \App\Models\GroupAssessor::where('group_id', $group->id)->update(['is_leader' => false]);
                    $existing->update(['is_leader' => true]);
                    $updated++;
                }
            } else {
                if ($isLeader) {
                    \App\Models\GroupAssessor::where('group_id', $group->id)->update(['is_leader' => false]);
                }
                \App\Models\GroupAssessor::create([
                    'group_id' => $group->id,
                    'employee_id' => $employee->id,
                    'is_leader' => $isLeader,
                ]);
                $added++;
            }
        }

        return [
            'success' => true,
            'added' => $added,
            'updated' => $updated,
            'total' => $added + $updated,
            'message' => "Sinkronisasi tim penilai berhasil: {$added} ditambah, {$updated} diperbarui.",
        ];
    }

    /**
     * Cari nilai pada row dengan berbagai alternatif nama kolom
     */
    protected function findValue(array $row, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== '') {
                return (string) $row[$k];
            }
        }
        return null;
    }

    /**
     * Cek status koneksi ke Google Sheet
     */
    public function testConnection(): array
    {
        $settings = $this->getSettings();
        $id = $settings['spreadsheet_id'];

        if (!$id) {
            return ['ok' => false, 'message' => 'Spreadsheet ID belum diisi.'];
        }

        try {
            $url = "https://docs.google.com/spreadsheets/d/{$id}/gviz/tq?tqx=out:json";
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Terhubung ke Google Spreadsheet! Status publik aktif.',
                    'url' => $settings['url'],
                    'id' => $id,
                ];
            }

            return [
                'ok' => false,
                'message' => "Gagal mengakses spreadsheet (HTTP {$response->status()}). Pastikan link berstatus 'Siapa saja yang memiliki link dapat melihat (Viewer)'.",
            ];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate template CSV untuk copy-paste ke Google Sheets
     */
    public function generateTemplateCsv(string $type): string
    {
        $output = fopen('php://temp', 'r+');

        if ($type === 'karyawan') {
            fputcsv($output, ['NRP', 'Nama_Lengkap', 'Departemen', 'Divisi', 'Plant', 'Area_WLA']);
            $employees = Employee::orderBy('nrp')->limit(50)->get();
            foreach ($employees as $emp) {
                fputcsv($output, [
                    $emp->nrp,
                    $emp->fullname,
                    $emp->dept,
                    $emp->division,
                    $emp->plant,
                    $emp->area_wla,
                ]);
            }
        } elseif ($type === 'kelompok') {
            fputcsv($output, ['Nomor_Kelompok', 'Nama_Kelompok', 'Plant']);
            $groups = Group::orderBy('number')->get();
            foreach ($groups as $g) {
                fputcsv($output, [$g->number, $g->name, $g->plant]);
            }
        } elseif ($type === 'posisi_job') {
            fputcsv($output, ['Nomor_Kelompok', 'Kode_Job', 'Nama_Job', 'Target_Kuota']);
            $jobs = Job::with('group')->orderBy('group_id')->orderBy('code')->get();
            foreach ($jobs as $j) {
                fputcsv($output, [
                    $j->group ? $j->group->number : 1,
                    $j->code,
                    $j->name,
                    $j->target_employees,
                ]);
            }
        } elseif ($type === 'hasil_penilaian') {
            fputcsv($output, ['Tanggal_Waktu', 'Nomor_Kelompok', 'NRP_Penilai', 'Penilai', 'NRP_Dinilai', 'Karyawan_Dinilai', 'Posisi_Job', 'Status', 'Catatan']);
            $assessments = Assessment::with(['group', 'assessor.employee', 'employee', 'job'])->orderBy('assessed_at', 'desc')->get();
            foreach ($assessments as $a) {
                fputcsv($output, [
                    $a->assessed_at ? $a->assessed_at->format('Y-m-d H:i') : '',
                    $a->group ? $a->group->number : '-',
                    $a->assessor?->employee?->nrp ?? '-',
                    $a->assessor?->employee?->fullname ?? 'Ketua/Penilai',
                    $a->employee?->nrp ?? '-',
                    $a->employee?->fullname ?? '-',
                    $a->job?->name ?? '-',
                    $a->is_checked ? 'Dinilai' : 'Belum',
                    $a->notes ?? '-',
                ]);
            }
        } elseif ($type === 'tim_penilai') {
            fputcsv($output, ['Nomor_Kelompok', 'Nama_Kelompok', 'NRP_Penilai', 'Nama_Penilai', 'Peran', 'Departemen', 'Plant', 'Total_Penilaian_Dibuat']);
            $assessors = \App\Models\GroupAssessor::with(['group', 'employee', 'assessments'])->get();
            foreach ($assessors as $a) {
                fputcsv($output, [
                    $a->group ? $a->group->number : '-',
                    $a->group ? $a->group->name : '-',
                    $a->employee ? $a->employee->nrp : '-',
                    $a->employee ? $a->employee->fullname : 'Penilai',
                    $a->is_leader ? 'Ketua Kelompok' : 'Anggota Penilai',
                    $a->employee ? $a->employee->dept : '-',
                    $a->group ? $a->group->plant : ($a->employee ? $a->employee->plant : '-'),
                    $a->assessments->count(),
                ]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Tambah data karyawan baru langsung ke Google Spreadsheet via Webhook Apps Script
     */
    public function appendEmployee(Employee $employee): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return [
                'success' => false,
                'configured' => false,
                'message' => 'URL Webhook Google Apps Script belum dikonfigurasi.',
            ];
        }

        try {
            $rawNrp = trim((string) $employee->nrp);
            // Tambahkan prefix apostrof (') jika NRP memiliki leading zero agar Google Sheets tidak mengubahnya menjadi integer
            $sheetNrp = (strlen($rawNrp) > 1 && $rawNrp[0] === '0') ? "'" . $rawNrp : $rawNrp;

            $payload = [
                'action' => 'append',
                'sheet' => 'Karyawan',
                'data' => [
                    'nrp' => $sheetNrp,
                    'fullname' => $employee->fullname,
                    'dept' => $employee->dept,
                    'division' => $employee->division,
                    'plant' => $employee->plant,
                    'area_wla' => $employee->area_wla ?? '',
                ],
                'row' => [
                    $sheetNrp,
                    $employee->fullname,
                    $employee->dept,
                    $employee->division,
                    $employee->plant,
                    $employee->area_wla ?? '',
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 5,
                        'strict' => false,
                        'referer' => true,
                        'protocols' => ['http', 'https'],
                    ]
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                $body = $response->json();
                $msg = $body['message'] ?? "Data karyawan {$employee->fullname} ({$employee->nrp}) berhasil ditambahkan ke Google Spreadsheet.";
                Log::info("Append employee to Google Sheets success: {$employee->nrp}");
                return [
                    'success' => true,
                    'configured' => true,
                    'message' => $msg,
                ];
            }

            Log::warning("Append employee to Google Sheets failed HTTP {$response->status()}: " . $response->body());
            return [
                'success' => false,
                'configured' => true,
                'message' => "Google Apps Script merespons HTTP {$response->status()}",
            ];
        } catch (\Exception $e) {
            Log::error("Exception in appendEmployee: " . $e->getMessage());
            return [
                'success' => false,
                'configured' => true,
                'message' => 'Gagal menghubungi Google Apps Script: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Perbarui data karyawan di Google Spreadsheet via Webhook Apps Script
     */
    public function updateEmployee(Employee $employee): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'update',
                'sheet' => 'Karyawan',
                'nrp' => $employee->nrp,
                'data' => [
                    'nrp' => $employee->nrp,
                    'fullname' => $employee->fullname,
                    'dept' => $employee->dept,
                    'division' => $employee->division,
                    'plant' => $employee->plant,
                    'area_wla' => $employee->area_wla ?? '',
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in updateEmployee Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Hapus data karyawan dari Google Spreadsheet via Webhook Apps Script
     */
    public function deleteEmployee(string $nrp, ?string $fullname = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $trimmedNrp = trim($nrp);
            $nrpVariations = [$trimmedNrp];

            // Tambahkan variasi tanpa leading zero jika bernilai numerik (misal '0000' -> '0')
            if (is_numeric($trimmedNrp)) {
                $nrpVariations[] = (string) (int) $trimmedNrp;
                $nrpVariations[] = ltrim($trimmedNrp, '0') ?: '0';
            }
            $nrpVariations = array_values(array_unique($nrpVariations));

            $payload = [
                'action' => 'delete',
                'sheet' => 'Karyawan',
                'nrp' => $trimmedNrp,
                'nrps' => $nrpVariations,
                'fullname' => $fullname ? trim($fullname) : null,
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            // Jika ada variasi numerik (misal '0000' vs '0') kirimkan juga variasi alternatif untuk memastikan terhapus di Apps Script versi lama
            if (count($nrpVariations) > 1) {
                foreach (array_slice($nrpVariations, 1) as $altNrp) {
                    try {
                        Http::timeout(10)
                            ->withOptions(['allow_redirects' => true])
                            ->post($webhookUrl, [
                                'action' => 'delete',
                                'sheet' => 'Karyawan',
                                'nrp' => $altNrp,
                                'fullname' => $fullname ? trim($fullname) : null,
                            ]);
                    } catch (\Exception $ex) {
                        // ignore secondary
                    }
                }
            }

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in deleteEmployee Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kosongkan seluruh baris data di tab Karyawan Google Spreadsheet (pertahankan header)
     */
    public function clearEmployeesSheet(): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'clear',
                'sheet' => 'Karyawan',
            ];

            $response = Http::timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in clearEmployeesSheet: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tambah kelompok baru ke Google Spreadsheet (tab 'Kelompok')
     */
    public function appendGroup(Group $group): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'append',
                'sheet' => 'Kelompok',
                'row' => [
                    $group->number,
                    $group->name,
                    $group->plant ?: 'CIKARANG',
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in appendGroup Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Hapus kelompok dari Google Spreadsheet (tab 'Kelompok')
     */
    public function deleteGroup(int|string $groupNumber, ?string $groupName = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'delete',
                'sheet' => 'Kelompok',
                'nrp' => (string) $groupNumber,
                'name' => $groupName,
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in deleteGroup Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tambah akun pengguna baru ke Google Spreadsheet (tab 'Pengguna')
     */
    public function appendUser(User $user, ?string $role = null, ?string $plainPassword = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $userRole = $role ?: ($user->roles->pluck('name')->first() ?? 'ketua');
            $groupName = $user->group ? "Kelompok {$user->group->number} ({$user->group->name})" : 'Semua Area';

            $payload = [
                'action' => 'append',
                'sheet' => 'Pengguna',
                'row' => [
                    $user->name,
                    $user->username ?: $user->email,
                    $user->email,
                    $userRole,
                    $groupName,
                    $plainPassword ?: '',
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in appendUser Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update data person/akun secara relasional ke seluruh tab Google Spreadsheet (Pengguna, tim_penilai, Karyawan, Hasil_Penilaian)
     * Menggunakan NRP sebagai kunci relasi utama, didukung Email & Nama.
     */
    public function cascadeUpdatePerson(array $data): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $nrp = trim((string)($data['nrp'] ?? ''));
            $oldNrp = trim((string)($data['old_nrp'] ?? $nrp));
            $name = trim((string)($data['name'] ?? ''));
            $oldName = trim((string)($data['old_name'] ?? $name));
            $email = trim((string)($data['email'] ?? ''));
            $oldEmail = trim((string)($data['old_email'] ?? $email));
            $role = trim((string)($data['role'] ?? 'ketua'));
            $groupNumber = $data['group_number'] ?? '';
            $groupName = $data['group_name'] ?? '';
            $password = $data['password'] ?? '';

            $payload = [
                'action' => 'cascade_update_person',
                'nrp' => $nrp,
                'old_nrp' => $oldNrp,
                'name' => $name,
                'old_name' => $oldName,
                'email' => $email,
                'old_email' => $oldEmail,
                'role' => $role,
                'group_number' => (string) $groupNumber,
                'group_name' => $groupName,
                'password' => $password,
            ];

            // 1. Kirim permintaan cascade update utama ke Google Apps Script
            $response = Http::timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            $body = $response->json();
            $report = $body['report'] ?? null;

            // 2. Dukungan kompatibilitas ganda (fallback update tab Karyawan jika webhook lama belum cascade)
            if (!empty($nrp) || !empty($oldNrp)) {
                $targetNrp = !empty($nrp) ? $nrp : $oldNrp;

                // Update tab Karyawan via webhook aksi update hanya jika cascade_update_person belum mengupdate Karyawan
                if (!$report || (isset($report['karyawan']) && (int)$report['karyawan'] === 0)) {
                    Http::timeout(10)->withOptions(['allow_redirects' => true])->post($webhookUrl, [
                        'action' => 'update',
                        'sheet' => 'Karyawan',
                        'nrp' => $targetNrp,
                        'data' => [
                            'nrp' => $targetNrp,
                            'fullname' => $name,
                        ],
                    ]);
                }
            }

            return [
                'success' => $response->successful(),
                'configured' => true,
                'report' => $report,
                'message' => $body['message'] ?? 'Data berhasil diperbarui di Google Spreadsheet.',
            ];
        } catch (\Exception $e) {
            Log::error("Exception in cascadeUpdatePerson Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Autentikasi akun pengguna langsung dari tab 'Pengguna' di Google Spreadsheet
     */
    public function authenticateFromSheet(string $identifier, string $password): ?User
    {
        $settings = $this->getSettings();
        $spreadsheetId = $settings['spreadsheet_id'] ?? null;
        if (!$spreadsheetId) {
            return null;
        }

        try {
            $url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:json&headers=1&sheet=" . urlencode('Pengguna');
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return null;
            }

            $body = $response->body();
            if (!preg_match('/google\.visualization\.Query\.setResponse\((.*)\);/s', $body, $matches)) {
                return null;
            }

            $data = json_decode($matches[1], true);
            $rows = $data['table']['rows'] ?? [];

            $identifierClean = strtolower(trim($identifier));

            foreach ($rows as $r) {
                $cells = $r['c'] ?? [];
                $name = trim($cells[0]['v'] ?? '');
                $username = trim($cells[1]['v'] ?? '');
                $email = trim($cells[2]['v'] ?? '');
                $role = strtolower(trim($cells[3]['v'] ?? 'ketua'));
                $groupStr = trim($cells[4]['v'] ?? '');
                $sheetPassword = isset($cells[5]['v']) ? trim((string)$cells[5]['v']) : '';

                // Lewati baris header jika terbaca
                if (strtolower($name) === 'nama' && strtolower($email) === 'email') {
                    continue;
                }

                // Cocokkan identifier dengan Email, Username / NRP, atau prefix email
                $emailPrefix = !empty($email) ? strtolower(explode('@', $email)[0]) : '';
                $matchEmail = (!empty($email) && (strtolower($email) === $identifierClean || $emailPrefix === $identifierClean));
                $matchUsername = (!empty($username) && strtolower($username) === $identifierClean);

                if ($matchEmail || $matchUsername) {
                    $passwordValid = false;

                    if ($sheetPassword !== '') {
                        // Password ada di kolom spreadsheet: dukung teks biasa ATAU hash bcrypt
                        if ($password === $sheetPassword || (str_starts_with($sheetPassword, '$2y$') && Hash::check($password, $sheetPassword))) {
                            $passwordValid = true;
                        }
                    } else {
                        // Fallback jika admin belum mengisi kolom password di spreadsheet:
                        // Cocokkan dengan password umum sistem ('password', 'password123', 'musashi123', atau sama dengan username)
                        if (in_array($password, ['password', 'password123', 'musashi123', $username])) {
                            $passwordValid = true;
                        }
                    }

                    if ($passwordValid) {
                        // Tentukan group_id jika role = ketua
                        $groupId = null;
                        if ($role === 'ketua' && preg_match('/Kelompok\s*(\d+)/i', $groupStr, $m)) {
                            $groupNum = (int)$m[1];
                            $group = Group::where('number', $groupNum)->first();
                            $groupId = $group?->id;
                        }

                        // Cari employee_id jika ada
                        $employeeId = null;
                        if (!empty($username)) {
                            $emp = Employee::where('nrp', $username)->first();
                            $employeeId = $emp?->id;
                        }

                        // Buat atau sinkronkan akun di SQLite lokal
                        $user = User::updateOrCreate(
                            ['email' => $email ?: ($username . '@musashi.co.id')],
                            [
                                'name' => $name ?: $identifier,
                                'username' => $username ?: null,
                                'password' => Hash::make($password),
                                'group_id' => $groupId,
                                'employee_id' => $employeeId,
                                'is_active' => true,
                            ]
                        );

                        // Sinkronkan role Spatie
                        $assignedRole = in_array($role, ['superadmin', 'admin', 'ketua']) ? $role : 'ketua';
                        $user->syncRoles([$assignedRole]);

                        if ($user->hasRole('ketua') && $user->group_id) {
                            $user->group?->syncLeaderAssessor();
                        }

                        return $user;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Exception in authenticateFromSheet: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Hapus akun pengguna dari Google Spreadsheet (tab 'Pengguna')
     */
    public function deleteUser(string $name, ?string $email = null, ?string $username = null, int|string|null $groupNumber = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'delete',
                'sheet' => 'Pengguna',
                'nrp' => trim($name), // Kolom 0 di tab Pengguna adalah Nama
                'name' => trim($name),
                'username' => $username ? trim($username) : null,
                'email' => $email ? trim($email) : null,
                'group_number' => $groupNumber ? (string)$groupNumber : null,
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in deleteUser Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tambah posisi job ke Google Spreadsheet (tab 'Posisi_Job')
     */
    public function appendJob(Job $job, Group $group): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'append',
                'sheet' => 'Posisi_Job',
                'row' => [
                    $group->number,
                    $job->code,
                    $job->name,
                    $job->target_employees !== null ? (string)$job->target_employees : '',
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in appendJob Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Hapus posisi job dari Google Spreadsheet (tab 'Posisi_Job')
     */
    public function deleteJob(string $jobName, int|string $groupNumber, ?string $jobCode = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'delete',
                'sheet' => 'Posisi_Job',
                'job_name' => trim($jobName),
                'name' => trim($jobName),
                'group_number' => (string) $groupNumber,
                'job_code' => $jobCode,
                'nrp' => trim($jobName),
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in deleteJob Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tambah / perbarui data penilai ke Google Spreadsheet (tab 'tim_penilai')
     */
    public function appendAssessor(\App\Models\GroupAssessor $assessor, ?Group $group = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $group = $group ?: $assessor->group;
            $employee = $assessor->employee;

            $groupNumber = $group ? $group->number : 1;
            $groupName = $group ? $group->name : '-';
            $nrp = $employee ? $employee->nrp : '-';
            $fullname = $employee ? $employee->fullname : 'Penilai';
            $role = $assessor->is_leader ? 'Ketua Kelompok' : 'Anggota Penilai';
            $dept = $employee ? $employee->dept : ($groupName ?: '-');
            $plant = $group ? $group->plant : ($employee ? $employee->plant : 'CIKARANG');
            $totalAssessments = $assessor->assessments()->count();

            $payload = [
                'action' => 'append',
                'sheet' => 'tim_penilai',
                'group_number' => (string) $groupNumber,
                'nrp' => (string) $nrp,
                'name' => $fullname,
                'fullname' => $fullname,
                'role' => $role,
                'row' => [
                    $groupNumber,
                    $groupName,
                    $nrp,
                    $fullname,
                    $role,
                    $dept,
                    $plant,
                    $totalAssessments,
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in appendAssessor Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Hapus data penilai dari Google Spreadsheet (tab 'tim_penilai')
     */
    public function deleteAssessor(int|string $groupNumber, ?string $nrp = null, ?string $name = null, array $allNrps = []): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'delete',
                'sheet' => 'tim_penilai',
                'group_number' => (string) $groupNumber,
                'nrp' => $nrp ? trim((string)$nrp) : '',
                'name' => $name ? trim((string)$name) : '',
                'fullname' => $name ? trim((string)$name) : '',
            ];

            if (!empty($allNrps)) {
                $payload['nrps'] = array_values(array_filter(array_map('trim', $allNrps)));
            }

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            $data = $response->json();
            $deletedCount = isset($data['deleted_count']) ? (int) $data['deleted_count'] : null;

            // Fallback: Jika tidak ada baris yang terhapus dan groupNumber sempat diisi, coba lagi tanpa membatasi nomor kelompok
            if ($deletedCount === 0 && !empty($groupNumber) && (!empty($nrp) || !empty($name))) {
                $payload['group_number'] = '';
                $fallbackResp = Http::timeout(10)
                    ->withOptions(['allow_redirects' => true])
                    ->post($webhookUrl, $payload);
                if ($fallbackResp->successful()) {
                    $fbData = $fallbackResp->json();
                    if (isset($fbData['deleted_count']) && (int)$fbData['deleted_count'] > 0) {
                        return [
                            'success' => true,
                            'configured' => true,
                            'deleted_count' => (int)$fbData['deleted_count'],
                            'message' => $fbData['message'] ?? null,
                        ];
                    }
                }
            }

            return [
                'success' => $response->successful(),
                'configured' => true,
                'deleted_count' => $deletedCount,
                'message' => $data['message'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("Exception in deleteAssessor Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tambah checklist hasil penilaian ke Google Spreadsheet (tab 'Hasil_Penilaian')
     */
    public function appendAssessment(Assessment $assessment): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $assessment->loadMissing(['group', 'assessor.employee', 'employee', 'job']);

            $dateStr = $assessment->checked_at 
                ? $assessment->checked_at->format('Y-m-d H:i') 
                : now()->format('Y-m-d H:i');
            $groupNum = $assessment->group ? $assessment->group->number : 1;
            $assessorNrp = $assessment->assessor?->employee?->nrp ?? '-';
            $assessorName = $assessment->assessor?->employee?->fullname ?? 'Penilai';
            $employeeNrp = $assessment->employee?->nrp ?? '-';
            $employeeName = $assessment->employee?->fullname ?? '-';
            $jobName = $assessment->job?->name ?? '-';

            // Ambil catatan evaluasi jika ada dari JobComment
            $commentText = '-';
            if ($assessment->job_id) {
                $jobComment = JobComment::where('job_id', $assessment->job_id)
                    ->where(function ($q) use ($assessment) {
                        $q->where('employee_id', $assessment->employee_id)
                          ->orWhereNull('employee_id');
                    })
                    ->latest()
                    ->value('comment');
                if ($jobComment) {
                    $commentText = $jobComment;
                }
            }

            $payload = [
                'action' => 'append',
                'sheet' => 'Hasil_Penilaian',
                'group_number' => (string) $groupNum,
                'assessor_nrp' => (string) $assessorNrp,
                'assessor_name' => $assessorName,
                'employee_nrp' => (string) $employeeNrp,
                'employee_name' => $employeeName,
                'job_name' => $jobName,
                'status' => 'Dinilai',
                'comment' => $commentText,
                'row' => [
                    $dateStr,
                    $groupNum,
                    $assessorNrp,
                    $assessorName,
                    $employeeNrp,
                    $employeeName,
                    $jobName,
                    'Dinilai',
                    $commentText,
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in appendAssessment Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sinkronkan catatan / komentar evaluasi ke tab 'Hasil_Penilaian' di Google Spreadsheet
     */
    public function syncJobComment(JobComment $comment, Group $group, Job $job, ?\App\Models\GroupAssessor $assessor = null, ?Employee $employee = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $assessorNrp = $assessor && $assessor->employee ? $assessor->employee->nrp : ($comment->assessor?->employee?->nrp ?? '-');
            $assessorName = $assessor && $assessor->employee ? $assessor->employee->fullname : ($comment->assessor?->employee?->fullname ?? 'Penilai');
            $employeeNrp = $employee ? $employee->nrp : ($comment->employee ? $comment->employee->nrp : '-');
            $employeeName = $employee ? $employee->fullname : ($comment->employee ? $comment->employee->fullname : 'Semua Karyawan (Umum)');
            $dateStr = $comment->created_at ? $comment->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i');
            $commentText = trim($comment->comment);

            $payload = [
                'action' => 'comment',
                'sheet' => 'Hasil_Penilaian',
                'group_number' => (string) $group->number,
                'assessor_nrp' => (string) $assessorNrp,
                'assessor_name' => $assessorName,
                'employee_nrp' => (string) $employeeNrp,
                'employee_name' => $employeeName,
                'job_name' => $job->name,
                'comment' => $commentText,
                'row' => [
                    $dateStr,
                    $group->number,
                    $assessorNrp,
                    $assessorName,
                    $employeeNrp,
                    $employeeName,
                    $job->name,
                    $employee ? 'Dinilai' : 'Catatan',
                    $commentText,
                ],
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            // Fallback jika Google Apps Script lama belum mengenal action 'comment'
            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['status']) && $body['status'] === 'error' && str_contains($body['message'] ?? '', 'Aksi tidak dikenal')) {
                    $payload['action'] = 'append';
                    $response = Http::timeout(10)
                        ->withOptions(['allow_redirects' => true])
                        ->post($webhookUrl, $payload);
                }
            }

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in syncJobComment Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Hapus catatan / komentar dari Google Spreadsheet tab 'Hasil_Penilaian'
     */
    public function deleteJobComment(JobComment $comment, Group $group): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $comment->loadMissing(['employee', 'job', 'assessor.employee']);
            $employeeName = $comment->employee ? $comment->employee->fullname : 'Semua Karyawan (Umum)';
            $jobName = $comment->job ? $comment->job->name : '';

            $payload = [
                'action' => 'delete',
                'sheet' => 'Hasil_Penilaian',
                'employee_name' => $employeeName,
                'job_name' => $jobName,
                'group_number' => (string) $group->number,
                'nrp' => $employeeName,
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in deleteJobComment Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Hapus checklist hasil penilaian dari Google Spreadsheet (tab 'Hasil_Penilaian')
     */
    public function deleteAssessment(string $employeeName, string $jobName, ?string $assessorName = null, ?int $groupNumber = null, ?string $employeeNrp = null, ?string $assessorNrp = null): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false];
        }

        try {
            $payload = [
                'action' => 'delete',
                'sheet' => 'Hasil_Penilaian',
                'employee_name' => trim($employeeName),
                'employee_nrp' => $employeeNrp ? trim($employeeNrp) : null,
                'job_name' => trim($jobName),
                'assessor_name' => $assessorName ? trim($assessorName) : null,
                'assessor_nrp' => $assessorNrp ? trim($assessorNrp) : null,
                'group_number' => $groupNumber !== null ? (string)$groupNumber : null,
                'nrp' => trim($employeeName),
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return ['success' => $response->successful(), 'configured' => true];
        } catch (\Exception $e) {
            Log::error("Exception in deleteAssessment Google Sheets: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sinkronkan seluruh data Hasil_Penilaian ke Google Spreadsheet dengan format 9 kolom (NRP_Penilai & NRP_Dinilai)
     */
    public function bulkSyncAssessmentsToSheet(): array
    {
        $settings = $this->getSettings();
        $webhookUrl = $settings['apps_script_url'] ?? null;

        if (!$webhookUrl) {
            return ['success' => false, 'configured' => false, 'message' => 'URL Webhook Google Apps Script belum dikonfigurasi.'];
        }

        try {
            $assessments = Assessment::with(['group', 'assessor.employee', 'employee', 'job'])
                ->orderBy('assessed_at', 'desc')
                ->get();

            $rows = [];
            foreach ($assessments as $a) {
                $dateStr = $a->assessed_at ? $a->assessed_at->format('Y-m-d H:i') : '';
                $groupNum = $a->group ? $a->group->number : 1;
                $assessorNrp = $a->assessor?->employee?->nrp ?? '-';
                $assessorName = $a->assessor?->employee?->fullname ?? 'Ketua/Penilai';
                $employeeNrp = $a->employee?->nrp ?? '-';
                $employeeName = $a->employee?->fullname ?? '-';
                $jobName = $a->job?->name ?? '-';
                $status = $a->is_checked ? 'Dinilai' : 'Belum';
                $comment = $a->notes ?? '-';

                $rows[] = [
                    $dateStr,
                    $groupNum,
                    $assessorNrp,
                    $assessorName,
                    $employeeNrp,
                    $employeeName,
                    $jobName,
                    $status,
                    $comment,
                ];
            }

            $payload = [
                'action' => 'bulk_sync_hasil_penilaian',
                'sheet' => 'Hasil_Penilaian',
                'rows' => $rows,
            ];

            $response = Http::timeout(25)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            return [
                'success' => $response->successful(),
                'configured' => true,
                'count' => count($rows),
                'message' => $response->json('message') ?? 'Berhasil disinkronkan ke Webhook Apps Script.',
            ];
        } catch (\Exception $e) {
            Log::error("Exception in bulkSyncAssessmentsToSheet: " . $e->getMessage());
            return ['success' => false, 'configured' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Uji koneksi Webhook Google Apps Script
     */
    public function testWebhook(?string $webhookUrl = null): array
    {
        $url = $webhookUrl ?: ($this->getSettings()['apps_script_url'] ?? null);

        if (!$url) {
            return [
                'ok' => false,
                'message' => 'URL Webhook Google Apps Script belum diisi.',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($url, ['action' => 'test']);

            if ($response->successful()) {
                $json = $response->json();
                return [
                    'ok' => true,
                    'message' => $json['message'] ?? 'Koneksi Webhook Google Apps Script Berhasil & Terhubung!',
                ];
            }

            return [
                'ok' => false,
                'message' => "Gagal menghubungi Webhook (HTTP {$response->status()}). Pastikan 'Akses: Siapa saja' saat Deploy.",
            ];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Koneksi Webhook Gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dapatkan kode Google Apps Script siap pakai untuk integrasi 2 arah
     */
    public function getAppsScriptCode(): string
    {
        return <<<'JS'
function doPost(e) {
  try {
    var contents = e.postData ? e.postData.contents : "{}";
    var data = JSON.parse(contents);
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheetName = data.sheet || "Karyawan";
    var sheet = ss.getSheetByName(sheetName);
    
    if (!sheet) {
      sheet = ss.insertSheet(sheetName);
      if (sheetName === "Karyawan") {
        sheet.appendRow(["NRP", "Nama_Lengkap", "Departemen", "Divisi", "Plant", "Area_WLA"]);
      } else if (sheetName === "Kelompok") {
        sheet.appendRow(["Nomor_Kelompok", "Nama_Kelompok", "Plant"]);
      } else if (sheetName === "tim_penilai") {
        sheet.appendRow(["Nomor_Kelompok", "Nama_Kelompok", "NRP_Penilai", "Nama_Penilai", "Peran", "Departemen", "Plant", "Total_Penilaian_Dibuat"]);
      } else if (sheetName === "Posisi_Job") {
        sheet.appendRow(["Nomor_Kelompok", "Kode_Job", "Nama_Job", "Target_Kuota"]);
      } else if (sheetName === "Pengguna") {
        sheet.appendRow(["Nama", "Username", "Email", "Role", "Kelompok"]);
      } else if (sheetName === "Hasil_Penilaian") {
        sheet.appendRow(["Tanggal_Waktu", "Nomor_Kelompok", "NRP_Penilai", "Penilai", "NRP_Dinilai", "Karyawan_Dinilai", "Posisi_Job", "Status", "Catatan"]);
      }
    }
    
    var action = data.action || "append";
    
    // 1. UJI KONEKSI
    if (action === "test") {
      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        message: "Koneksi Webhook Google Spreadsheet Aktif & Terhubung!"
      })).setMimeType(ContentService.MimeType.JSON);
    }
    
    // 2. TAMBAH / UPDATE BARIS (DEDULIKASI OTOMATIS)
    if (action === "append" || action === "comment") {
      var row = data.row;
      if (!row && data.data) {
        var rawNrp = String(data.data.nrp || "");
        row = [
          rawNrp,
          data.data.fullname || "",
          data.data.dept || "",
          data.data.division || "",
          data.data.plant || "",
          data.data.area_wla || ""
        ];
      }

      // Khusus sheet Hasil_Penilaian: Lindungi dari baris duplikat untuk kombinasi Kelompok + Penilai + Karyawan + Job yang sama
      if (sheetName === "Hasil_Penilaian" && row && row.length >= 7) {
        var values = sheet.getDataRange().getValues();
        var targetGrp = String(data.group_number || row[1] || "").trim();
        var is9Col = (values.length > 0 && values[0].length >= 9 && String(values[0][2]).toLowerCase().indexOf("nrp") !== -1);
        
        var targetAssNrp = String(data.assessor_nrp || (is9Col ? row[2] : "")).trim();
        var targetAss = String(data.assessor_name || (is9Col ? row[3] : row[2]) || "").trim().toLowerCase();
        var targetEmpNrp = String(data.employee_nrp || (is9Col ? row[4] : "")).trim();
        var targetEmp = String(data.employee_name || (is9Col ? row[5] : row[3]) || "").trim().toLowerCase();
        var targetJob = String(data.job_name || (is9Col ? row[6] : row[4]) || "").trim().toLowerCase();
        var targetStatus = (is9Col ? row[7] : row[5]) || data.status || "Dinilai";
        var targetComment = (is9Col ? row[8] : row[6]) || data.comment || "";
        var updated = false;

        for (var i = 1; i < values.length; i++) {
          var colGrp = String(values[i][1] || "").trim();
          var colAssNrp = is9Col ? String(values[i][2] || "").trim() : "";
          var colAss = String(is9Col ? values[i][3] : values[i][2] || "").trim().toLowerCase();
          var colEmpNrp = is9Col ? String(values[i][4] || "").trim() : "";
          var colEmp = String(is9Col ? values[i][5] : values[i][3] || "").trim().toLowerCase();
          var colJob = String(is9Col ? values[i][6] : values[i][4] || "").trim().toLowerCase();

          var matchGrp = (targetGrp === "" || colGrp === targetGrp);
          var matchAss = (targetAssNrp !== "" && colAssNrp === targetAssNrp) || (targetAss === "" || colAss === targetAss);
          var matchEmp = (targetEmpNrp !== "" && colEmpNrp === targetEmpNrp) || (colEmp === targetEmp);
          var matchJob = (colJob === targetJob);

          if (matchGrp && matchAss && matchEmp && matchJob) {
            sheet.getRange(i + 1, 1).setValue(row[0] || Utilities.formatDate(new Date(), "GMT+7", "yyyy-MM-dd HH:mm"));
            if (is9Col) {
              if (row[2]) sheet.getRange(i + 1, 3).setValue(row[2]);
              if (row[3]) sheet.getRange(i + 1, 4).setValue(row[3]);
              if (row[4]) sheet.getRange(i + 1, 5).setValue(row[4]);
              if (row[5]) sheet.getRange(i + 1, 6).setValue(row[5]);
              if (targetStatus) sheet.getRange(i + 1, 8).setValue(targetStatus);
              if (targetComment && String(targetComment).trim() !== "" && String(targetComment).trim() !== "-") {
                sheet.getRange(i + 1, 9).setValue(targetComment);
              }
            } else {
              if (targetStatus) sheet.getRange(i + 1, 6).setValue(targetStatus);
              if (targetComment && String(targetComment).trim() !== "" && String(targetComment).trim() !== "-") {
                sheet.getRange(i + 1, 7).setValue(targetComment);
              }
            }
            updated = true;
            break;
          }
        }

        if (updated) {
          return ContentService.createTextOutput(JSON.stringify({
            status: "success",
            action: "updated",
            message: "Baris penilaian sudah ada dan berhasil diperbarui (mencegah data ganda)."
          })).setMimeType(ContentService.MimeType.JSON);
        }
      }

      // Khusus sheet tim_penilai: Lindungi dari baris ganda untuk kombinasi Kelompok + NRP / Nama penilai
      if (sheetName === "tim_penilai" && row && row.length >= 4) {
        var values = sheet.getDataRange().getValues();
        var targetGrp = String(data.group_number || row[0] || "").trim();
        var targetNrp = String(data.nrp || row[2] || "").trim().toLowerCase();
        var targetName = String(data.fullname || data.name || row[3] || "").trim().toLowerCase();
        var updated = false;

        for (var i = 1; i < values.length; i++) {
          var colGrp = String(values[i][0] || "").trim();
          var colNrp = String(values[i][2] || "").trim().toLowerCase();
          var colName = String(values[i][3] || "").trim().toLowerCase();

          var cleanTargetGrp = targetGrp.replace(/[^0-9]/g, '');
          var cleanColGrp = colGrp.replace(/[^0-9]/g, '');
          var matchGrp = (targetGrp === "" || colGrp === targetGrp || (cleanTargetGrp !== "" && cleanColGrp === cleanTargetGrp));
          var matchNrp = (targetNrp !== "" && (colNrp === targetNrp || (!isNaN(colNrp) && !isNaN(targetNrp) && Number(colNrp) === Number(targetNrp))));
          var matchName = (targetName !== "" && (colName === targetName || colName.indexOf(targetName) !== -1 || targetName.indexOf(colName) !== -1));

          if (matchGrp && (matchNrp || matchName)) {
            for (var c = 0; c < row.length; c++) {
              sheet.getRange(i + 1, c + 1).setValue(row[c]);
            }
            updated = true;
            break;
          }
        }

        if (updated) {
          return ContentService.createTextOutput(JSON.stringify({
            status: "success",
            action: "updated",
            message: "Data penilai sudah ada dan berhasil diperbarui di sheet tim_penilai."
          })).setMimeType(ContentService.MimeType.JSON);
        }
      }

      sheet.appendRow(row);
      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        action: "appended",
        message: "Data berhasil ditambahkan ke sheet " + sheetName + " Google Spreadsheet."
      })).setMimeType(ContentService.MimeType.JSON);
    }
    
    // 3. PERBARUI KARYAWAN BERDASARKAN NRP
    if (action === "update") {
      var targetNrp = data.nrp || (data.data ? data.data.nrp : null);
      if (targetNrp) {
        var values = sheet.getDataRange().getValues();
        for (var i = 1; i < values.length; i++) {
          if (String(values[i][0]).trim().toLowerCase() === String(targetNrp).trim().toLowerCase()) {
            var rowIdx = i + 1;
            if (data.data) {
              if (data.data.fullname) sheet.getRange(rowIdx, 2).setValue(data.data.fullname);
              if (data.data.dept) sheet.getRange(rowIdx, 3).setValue(data.data.dept);
              if (data.data.division) sheet.getRange(rowIdx, 4).setValue(data.data.division);
              if (data.data.plant) sheet.getRange(rowIdx, 5).setValue(data.data.plant);
              if (data.data.area_wla !== undefined) sheet.getRange(rowIdx, 6).setValue(data.data.area_wla);
            }
            break;
          }
        }
      }
      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        message: "Data berhasil diperbarui di Google Spreadsheet."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 4. CASCADE UPDATE RELASIONAL BERDASARKAN NRP (Pengguna, tim_penilai, Karyawan, Hasil_Penilaian)
    if (action === "cascade_update_person" || action === "update_user") {
      var targetNrp = String(data.nrp || data.username || "").trim();
      var oldNrp = String(data.old_nrp || targetNrp).trim();
      var targetName = String(data.name || data.fullname || "").trim();
      var oldName = String(data.old_name || targetName).trim();
      var targetEmail = String(data.email || "").trim().toLowerCase();
      var oldEmail = String(data.old_email || targetEmail).trim().toLowerCase();
      var targetRole = String(data.role || "ketua").trim();
      var targetGroupNum = String(data.group_number || "").trim();
      var targetGroupName = String(data.group_name || "").trim();
      var targetPassword = String(data.password || "").trim();
      
      var cleanTargetNrp = targetNrp.replace(/[^0-9]/g, '');
      var cleanOldNrp = oldNrp.replace(/[^0-9]/g, '');
      
      var report = { pengguna: 0, tim_penilai: 0, karyawan: 0, hasil_penilaian: 0 };
      
      // A. PERBARUI TAB 'Pengguna'
      var sheetPengguna = ss.getSheetByName("Pengguna");
      if (sheetPengguna) {
        var vals = sheetPengguna.getDataRange().getValues();
        var cleanTargetGrp = targetGroupNum.replace(/[^0-9]/g, '');
        for (var i = 1; i < vals.length; i++) {
          var pName = String(vals[i][0] || "").trim();
          var pUser = String(vals[i][1] || "").trim();
          var pEmail = String(vals[i][2] || "").trim().toLowerCase();
          var pGrp = String(vals[i][4] || "").trim();
          var cleanPUser = pUser.replace(/[^0-9]/g, '');
          var cleanPGrp = pGrp.replace(/[^0-9]/g, '');
          
          var matchEmail = (oldEmail !== "" && pEmail === oldEmail) || (targetEmail !== "" && pEmail === targetEmail);
          var matchNrp = (cleanOldNrp !== "" && cleanPUser === cleanOldNrp) || (cleanTargetNrp !== "" && cleanPUser === cleanTargetNrp);
          var matchName = (oldName !== "" && pName.toLowerCase() === oldName.toLowerCase()) || (targetName !== "" && pName.toLowerCase() === targetName.toLowerCase());
          var matchGrp = (targetRole === "ketua" && cleanTargetGrp !== "" && (cleanPGrp === cleanTargetGrp || pGrp.indexOf("Kelompok " + cleanTargetGrp + " ") !== -1 || pGrp === "Kelompok " + cleanTargetGrp));
          
          if (matchEmail || matchNrp || matchName || matchGrp) {
            var rowIdx = i + 1;
            if (targetName) sheetPengguna.getRange(rowIdx, 1).setValue(targetName);
            if (targetNrp) sheetPengguna.getRange(rowIdx, 2).setValue(targetNrp);
            if (targetEmail) sheetPengguna.getRange(rowIdx, 3).setValue(targetEmail);
            if (targetRole) sheetPengguna.getRange(rowIdx, 4).setValue(targetRole);
            if (targetGroupName) sheetPengguna.getRange(rowIdx, 5).setValue(targetGroupName);
            if (targetPassword) sheetPengguna.getRange(rowIdx, 6).setValue(targetPassword);
            report.pengguna++;
            break;
          }
        }
      }
      
      // B. PERBARUI TAB 'tim_penilai'
      var sheetPenilai = ss.getSheetByName("tim_penilai");
      if (sheetPenilai) {
        var vals = sheetPenilai.getDataRange().getValues();
        var cleanTargetGrp = targetGroupNum.replace(/[^0-9]/g, '');
        for (var i = 1; i < vals.length; i++) {
          var tGrp = String(vals[i][0] || "").trim();
          var tNrp = String(vals[i][2] || "").trim();
          var tName = String(vals[i][3] || "").trim();
          var tRole = String(vals[i][4] || "").trim().toLowerCase();
          var cleanTNrp = tNrp.replace(/[^0-9]/g, '');
          var cleanTGrp = tGrp.replace(/[^0-9]/g, '');
          
          var matchNrp = (cleanOldNrp !== "" && cleanTNrp === cleanOldNrp) || (cleanTargetNrp !== "" && cleanTNrp === cleanTargetNrp);
          var matchGrpAndName = (cleanTargetGrp !== "" && (cleanTGrp === cleanTargetGrp || tGrp === targetGroupNum)) && 
                                ((oldName !== "" && tName.toLowerCase() === oldName.toLowerCase()) || (targetName !== "" && tName.toLowerCase() === targetName.toLowerCase()));
          var matchLeaderOfGrp = (targetRole === "ketua" && cleanTargetGrp !== "" && cleanTGrp === cleanTargetGrp && tRole.indexOf("ketua") !== -1);
          
          if (matchNrp || matchGrpAndName || matchLeaderOfGrp) {
            var rowIdx = i + 1;
            if (targetNrp) sheetPenilai.getRange(rowIdx, 3).setValue(targetNrp);
            if (targetName) sheetPenilai.getRange(rowIdx, 4).setValue(targetName);
            report.tim_penilai++;
          }
        }
      }
      
      // C. PERBARUI TAB 'Karyawan'
      var sheetKaryawan = ss.getSheetByName("Karyawan");
      if (sheetKaryawan) {
        var vals = sheetKaryawan.getDataRange().getValues();
        for (var i = 1; i < vals.length; i++) {
          var kNrp = String(vals[i][0] || "").trim();
          var kName = String(vals[i][1] || "").trim();
          var cleanKNrp = kNrp.replace(/[^0-9]/g, '');
          
          var matchNrp = (cleanOldNrp !== "" && cleanKNrp === cleanOldNrp) || (cleanTargetNrp !== "" && cleanKNrp === cleanTargetNrp);
          var matchName = (oldName !== "" && kName.toLowerCase() === oldName.toLowerCase());
          
          if (matchNrp || matchName) {
            var rowIdx = i + 1;
            if (targetNrp) sheetKaryawan.getRange(rowIdx, 1).setValue(targetNrp);
            if (targetName) sheetKaryawan.getRange(rowIdx, 2).setValue(targetName);
            report.karyawan++;
            break;
          }
        }
      }
      
      // D. PERBARUI TAB 'Hasil_Penilaian' (Mendukung 9 Kolom dengan NRP_Penilai & NRP_Dinilai)
      var sheetHasil = ss.getSheetByName("Hasil_Penilaian");
      if (sheetHasil) {
        var vals = sheetHasil.getDataRange().getValues();
        var is9ColHasil = (vals.length > 0 && vals[0].length >= 9 && String(vals[0][2]).toLowerCase().indexOf("nrp") !== -1);
        for (var i = 1; i < vals.length; i++) {
          var rowIdx = i + 1;
          if (is9ColHasil) {
            // [0: Tanggal_Waktu, 1: Nomor_Kelompok, 2: NRP_Penilai, 3: Penilai, 4: NRP_Dinilai, 5: Karyawan_Dinilai, 6: Posisi_Job, 7: Status, 8: Catatan]
            var hAssNrp = String(vals[i][2] || "").trim();
            var hAssName = String(vals[i][3] || "").trim();
            var hEmpNrp = String(vals[i][4] || "").trim();
            var hEmpName = String(vals[i][5] || "").trim();

            var cleanHAssNrp = hAssNrp.replace(/[^0-9]/g, '');
            var cleanHEmpNrp = hEmpNrp.replace(/[^0-9]/g, '');

            var matchAssNrp = (cleanOldNrp !== "" && cleanHAssNrp === cleanOldNrp) || (cleanTargetNrp !== "" && cleanHAssNrp === cleanTargetNrp);
            var matchAssName = (oldName !== "" && hAssName.toLowerCase() === oldName.toLowerCase()) || (targetName !== "" && hAssName.toLowerCase() === targetName.toLowerCase());

            var matchEmpNrp = (cleanOldNrp !== "" && cleanHEmpNrp === cleanOldNrp) || (cleanTargetNrp !== "" && cleanHEmpNrp === cleanTargetNrp);
            var matchEmpName = (oldName !== "" && hEmpName.toLowerCase() === oldName.toLowerCase()) || (targetName !== "" && hEmpName.toLowerCase() === targetName.toLowerCase());

            if (matchAssNrp || matchAssName) {
              if (targetNrp) sheetHasil.getRange(rowIdx, 3).setValue(targetNrp);
              if (targetName) sheetHasil.getRange(rowIdx, 4).setValue(targetName);
              report.hasil_penilaian++;
            }
            if (matchEmpNrp || matchEmpName) {
              if (targetNrp) sheetHasil.getRange(rowIdx, 5).setValue(targetNrp);
              if (targetName) sheetHasil.getRange(rowIdx, 6).setValue(targetName);
              report.hasil_penilaian++;
            }
          } else {
            // Format 7 kolom lama [0: Tanggal, 1: Nomor_Kelompok, 2: Penilai, 3: Karyawan_Dinilai, 4: Posisi_Job, 5: Status, 6: Catatan]
            var hPenilai = String(vals[i][2] || "").trim();
            var hKaryawan = String(vals[i][3] || "").trim();

            if (oldName !== "" && (hPenilai.toLowerCase() === oldName.toLowerCase() || hPenilai.toLowerCase() === targetName.toLowerCase())) {
              sheetHasil.getRange(rowIdx, 3).setValue(targetName);
              report.hasil_penilaian++;
            }
            if (oldName !== "" && (hKaryawan.toLowerCase() === oldName.toLowerCase() || hKaryawan.toLowerCase() === targetName.toLowerCase())) {
              sheetHasil.getRange(rowIdx, 4).setValue(targetName);
              report.hasil_penilaian++;
            }
          }
        }
      }

      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        action: "cascade_updated",
        report: report,
        message: "Berhasil memperbarui data pengguna & relasinya di seluruh tab Google Spreadsheet (Pengguna: " + report.pengguna + ", tim_penilai: " + report.tim_penilai + ", Karyawan: " + report.karyawan + ", Hasil_Penilaian: " + report.hasil_penilaian + ")."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 5. BULK SYNC SELURUH TAB HASIL_PENILAIAN (Format Baru 9 Kolom dengan NRP_Penilai & NRP_Dinilai)
    if (action === "bulk_sync_hasil_penilaian") {
      var headerRow = ["Tanggal_Waktu", "Nomor_Kelompok", "NRP_Penilai", "Penilai", "NRP_Dinilai", "Karyawan_Dinilai", "Posisi_Job", "Status", "Catatan"];
      sheet.clearContents();
      sheet.appendRow(headerRow);
      var newRows = data.rows || [];
      if (newRows.length > 0) {
        sheet.getRange(2, 1, newRows.length, 9).setValues(newRows);
      }
      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        count: newRows.length,
        message: "Berhasil menyinkronkan " + newRows.length + " baris data ke sheet Hasil_Penilaian dengan format 9 kolom (NRP)."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 6. HAPUS BARIS SECARA CERDAS SESUAI TABEL SHEET
    if (action === "delete") {
      var targetNrp = data.nrp !== undefined && data.nrp !== null ? String(data.nrp).trim() : "";
      var targetName = (data.fullname || data.name || (data.data ? data.data.fullname : "") || "").trim().toLowerCase();
      var targetJob = (data.job_name || "").trim().toLowerCase();
      var targetGroup = data.group_number !== undefined && data.group_number !== null ? String(data.group_number).trim() : "";
      var targetEmp = (data.employee_name || "").trim().toLowerCase();
      var targetUser = (data.username || "").trim().toLowerCase();
      var targetEmail = (data.email || "").trim().toLowerCase();

      var nrpList = data.nrps || [];
      var nrpSet = {};
      for (var k = 0; k < nrpList.length; k++) {
        var n = String(nrpList[k]).trim();
        nrpSet[n] = true;
        if (!isNaN(n) && n !== "") {
          nrpSet[String(Number(n))] = true;
        }
      }
      if (targetNrp !== "") {
        nrpSet[targetNrp] = true;
        if (!isNaN(targetNrp)) {
          nrpSet[String(Number(targetNrp))] = true;
        }
      }

      var values = sheet.getDataRange().getValues();
      var deletedCount = 0;

      for (var i = values.length - 1; i >= 1; i--) {
        var rowValues = [];
        for (var c = 0; c < values[i].length; c++) {
          rowValues.push(String(values[i][c] !== null && values[i][c] !== undefined ? values[i][c] : "").trim());
        }
        var shouldDelete = false;

        if (sheetName === "Posisi_Job") {
          // Columns: [0: Nomor_Kelompok, 1: Kode_Job, 2: Nama_Job, 3: Target_Kuota]
          var colGrp = rowValues[0] || "";
          var colJobCode = (rowValues[1] || "").toLowerCase();
          var colJobName = (rowValues[2] || "").toLowerCase();

          var matchJob = (targetJob !== "" && (colJobName === targetJob || colJobName.indexOf(targetJob) !== -1))
                      || (targetName !== "" && (colJobName === targetName || colJobName.indexOf(targetName) !== -1))
                      || (targetNrp !== "" && (colJobName === targetNrp.toLowerCase() || colJobCode === targetNrp.toLowerCase()));
          var matchGrp = (targetGroup === "" || colGrp === targetGroup);

          if (matchJob && matchGrp) shouldDelete = true;

        } else if (sheetName === "Pengguna") {
          // Columns: [0: Nama, 1: Username, 2: Email, 3: Role, 4: Kelompok]
          var colName = (rowValues[0] || "").toLowerCase();
          var colUser = (rowValues[1] || "").toLowerCase();
          var colMail = (rowValues[2] || "").toLowerCase();
          var colGrp = (rowValues[4] || "").toLowerCase();
          var q = targetNrp.toLowerCase();
          var cleanTargetGrp = targetGroup.replace(/[^0-9]/g, '');
          var cleanColGrp = colGrp.replace(/[^0-9]/g, '');

          if ((q !== "" && (colName === q || colUser === q || colMail === q)) ||
              (targetName !== "" && colName === targetName) ||
              (targetUser !== "" && colUser === targetUser) ||
              (targetEmail !== "" && colMail === targetEmail) ||
              (cleanTargetGrp !== "" && (cleanColGrp === cleanTargetGrp || colGrp.indexOf("kelompok " + cleanTargetGrp + " ") !== -1 || colGrp === "kelompok " + cleanTargetGrp))) {
            shouldDelete = true;
          }

        } else if (sheetName === "Hasil_Penilaian") {
          // Mendukung Format 9 Kolom (Baru dengan NRP) dan 7 Kolom (Lama)
          var is9Col = (values.length > 0 && values[0].length >= 9 && String(values[0][2]).toLowerCase().indexOf("nrp") !== -1);
          var colAssGrp = rowValues[1] || "";
          var colAssNrp = is9Col ? (rowValues[2] || "") : "";
          var colAssessor = (rowValues[is9Col ? 3 : 2] || "").toLowerCase();
          var colEmpNrp = is9Col ? (rowValues[4] || "") : "";
          var colEmp = (rowValues[is9Col ? 5 : 3] || "").toLowerCase();
          var colJob = (rowValues[is9Col ? 6 : 4] || "").toLowerCase();

          var targetEmpNrp = String(data.employee_nrp || "").trim().toLowerCase();
          var targetAssNrp = String(data.assessor_nrp || "").trim().toLowerCase();

          var matchEmp = (targetEmpNrp !== "" && (colEmpNrp.toLowerCase() === targetEmpNrp || (!isNaN(colEmpNrp) && !isNaN(targetEmpNrp) && Number(colEmpNrp) === Number(targetEmpNrp))))
                      || (targetEmp !== "" && (colEmp === targetEmp || colEmp.indexOf(targetEmp) !== -1))
                      || (targetNrp !== "" && (colEmp === targetNrp.toLowerCase() || colEmp.indexOf(targetNrp.toLowerCase()) !== -1 || colEmpNrp.toLowerCase() === targetNrp.toLowerCase()));
          var matchJob = (targetJob !== "" && (colJob === targetJob || colJob.indexOf(targetJob) !== -1));
          var matchAss = (!data.assessor_name || colAssessor === String(data.assessor_name).trim().toLowerCase())
                      || (targetAssNrp !== "" && (colAssNrp.toLowerCase() === targetAssNrp || (!isNaN(colAssNrp) && !isNaN(targetAssNrp) && Number(colAssNrp) === Number(targetAssNrp))));
          var matchGrp = (targetGroup === "" || colAssGrp === targetGroup);

          if (matchEmp && (matchJob || targetJob === "") && matchAss && matchGrp) {
            shouldDelete = true;
          }

        } else if (sheetName === "Kelompok") {
          // Columns: [0: Nomor_Kelompok, 1: Nama_Kelompok, 2: Plant]
          var colNum = rowValues[0] || "";
          var colKName = (rowValues[1] || "").toLowerCase();
          if (colNum === targetNrp || (targetName !== "" && colKName === targetName)) {
            shouldDelete = true;
          }

        } else if (sheetName === "tim_penilai") {
          // Columns: [0: Nomor_Kelompok, 1: Nama_Kelompok, 2: NRP_Penilai, 3: Nama_Penilai, 4: Peran, 5: Departemen, 6: Plant, 7: Total_Penilaian_Dibuat]
          var colGrp = String(rowValues[0] || "").trim();
          var colNrp = String(rowValues[2] || "").trim().toLowerCase();
          var colName = String(rowValues[3] || "").trim().toLowerCase();

          var cleanTargetGrp = targetGroup.replace(/[^0-9]/g, '');
          var cleanColGrp = colGrp.replace(/[^0-9]/g, '');
          var matchGrp = (targetGroup === "" || colGrp === targetGroup || (cleanTargetGrp !== "" && cleanColGrp === cleanTargetGrp));
          var matchNrp = (targetNrp !== "" && (colNrp === targetNrp.toLowerCase() || (!isNaN(colNrp) && !isNaN(targetNrp) && Number(colNrp) === Number(targetNrp)) || (nrpSet && nrpSet[colNrp])));
          var matchName = (targetName !== "" && (colName === targetName || colName.indexOf(targetName) !== -1 || targetName.indexOf(colName) !== -1));
          var matchAllInGroup = (data.all_group === true && cleanTargetGrp !== "" && cleanColGrp === cleanTargetGrp);

          if ((matchGrp && (matchNrp || matchName)) || matchAllInGroup) {
            shouldDelete = true;
          }

        } else {
          // Default (Karyawan)
          var cellNrp = rowValues[0] || "";
          var cellName = (rowValues[1] || "").toLowerCase();

          var matchNrp = nrpSet[cellNrp] === true;
          var matchNumeric = (!isNaN(cellNrp) && !isNaN(targetNrp) && targetNrp !== "" && Number(cellNrp) === Number(targetNrp));
          var matchName = (targetName !== "" && cellName === targetName);

          if (matchNrp || matchNumeric || matchName) {
            shouldDelete = true;
          }
        }

        if (shouldDelete) {
          sheet.deleteRow(i + 1);
          deletedCount++;
        }
      }

      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        deleted_count: deletedCount,
        message: deletedCount > 0 
          ? "Berhasil menghapus " + deletedCount + " baris dari sheet " + sheetName + "."
          : "Data tidak ditemukan di sheet " + sheetName + "."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 7. BERSIHKAN DUPLIKAT DI HASIL_PENILAIAN (Maintenance Anti-Ganda)
    if (action === "deduplicate" || action === "cleanup_duplicates") {
      var values = sheet.getDataRange().getValues();
      var is9Col = (values.length > 0 && values[0].length >= 9 && String(values[0][2]).toLowerCase().indexOf("nrp") !== -1);
      var seen = {};
      var deleted = 0;
      for (var i = values.length - 1; i >= 1; i--) {
        var key = is9Col 
          ? (String(values[i][1]).trim() + "___" + String(values[i][2]).trim() + "___" + String(values[i][4]).trim() + "___" + String(values[i][6]).trim().toLowerCase())
          : (String(values[i][1]).trim() + "___" + String(values[i][2]).trim().toLowerCase() + "___" + String(values[i][3]).trim().toLowerCase() + "___" + String(values[i][4]).trim().toLowerCase());
        if (seen[key]) {
          sheet.deleteRow(i + 1);
          deleted++;
        } else {
          seen[key] = true;
        }
      }
      return ContentService.createTextOutput(JSON.stringify({
        status: "success",
        deleted: deleted,
        message: "Berhasil membersihkan " + deleted + " baris duplikat di sheet " + sheetName + "."
      })).setMimeType(ContentService.MimeType.JSON);
    }
    
    return ContentService.createTextOutput(JSON.stringify({
      status: "error",
      message: "Aksi tidak dikenal: " + action
    })).setMimeType(ContentService.MimeType.JSON);
    
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({
      status: "error",
      message: err.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  return ContentService.createTextOutput(JSON.stringify({
    status: "success",
    message: "Webhook Google Spreadsheet aktif. Gunakan metode POST untuk mengirim data."
  })).setMimeType(ContentService.MimeType.JSON);
}
JS;
    }
}
