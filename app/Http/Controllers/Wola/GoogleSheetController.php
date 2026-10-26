<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Services\GoogleSheetService;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Job;
use App\Models\Assessment;
use App\Exports\FullDatabaseExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleSheetController extends Controller
{
    protected GoogleSheetService $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * Tampilkan Halaman Integrasi Google Spreadsheet
     */
    public function index()
    {
        $settings = $this->googleSheetService->getSettings();
        $connection = $this->googleSheetService->testConnection();
        $appsScriptCode = $this->googleSheetService->getAppsScriptCode();
        $webhookStatus = !empty($settings['apps_script_url'])
            ? $this->googleSheetService->testWebhook()
            : null;

        $stats = [
            'total_employees' => Employee::count(),
            'total_groups' => Group::count(),
            'total_jobs' => Job::count(),
            'total_assessments' => Assessment::count(),
        ];

        return view('wola.google_sheets.index', compact('settings', 'connection', 'stats', 'appsScriptCode', 'webhookStatus'));
    }

    /**
     * Update URL Google Spreadsheet
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'apps_script_url' => 'nullable|url',
        ]);

        $this->googleSheetService->saveSettings(
            $request->input('url'),
            $request->input('apps_script_url')
        );

        return redirect()->route('wola.google-sheets.index')
            ->with('success', 'Konfigurasi Google Spreadsheet berhasil diperbarui.');
    }

    /**
     * Jalankan Sinkronisasi dari Google Sheet ke Database
     */
    public function sync(Request $request)
    {
        $scope = $request->input('scope', 'all');

        $messages = [];
        $totalAdded = 0;
        $totalUpdated = 0;
        $errors = [];

        if (in_array($scope, ['all', 'groups'])) {
            $resGroup = $this->googleSheetService->syncGroups();
            if ($resGroup['success']) {
                $messages[] = $resGroup['message'];
                $totalAdded += $resGroup['added'];
                $totalUpdated += $resGroup['updated'];
            } else {
                $errors[] = $resGroup['message'];
            }
        }

        if (in_array($scope, ['all', 'jobs'])) {
            $resJob = $this->googleSheetService->syncJobs();
            if ($resJob['success']) {
                $messages[] = $resJob['message'];
                $totalAdded += $resJob['added'];
                $totalUpdated += $resJob['updated'];
            } else {
                $errors[] = $resJob['message'];
            }
        }

        if (in_array($scope, ['all', 'employees'])) {
            $resEmp = $this->googleSheetService->syncEmployees();
            if ($resEmp['success']) {
                $messages[] = $resEmp['message'];
                $totalAdded += $resEmp['added'];
                $totalUpdated += $resEmp['updated'];
            } else {
                $errors[] = $resEmp['message'];
            }
        }

        if (in_array($scope, ['all', 'assessors'])) {
            $resAss = $this->googleSheetService->syncAssessors();
            if ($resAss['success']) {
                $messages[] = $resAss['message'];
                $totalAdded += $resAss['added'];
                $totalUpdated += $resAss['updated'];
            } else {
                $errors[] = $resAss['message'];
            }
        }

        // Catat timestamp sinkronisasi
        $summary = count($messages) > 0 ? implode(' | ', $messages) : 'Tidak ada data yang diproses.';
        $this->googleSheetService->saveSettings(
            $this->googleSheetService->getSettings()['url'],
            null,
            [
                'last_synced_at' => now()->format('Y-m-d H:i:s'),
                'last_sync_status' => count($errors) > 0 ? 'warning' : 'success',
                'last_sync_summary' => $summary,
            ]
        );

        if (count($errors) > 0 && count($messages) === 0) {
            return redirect()->route('wola.google-sheets.index')
                ->with('error', implode(' ', $errors));
        }

        $flashMsg = "Sinkronisasi selesai! {$totalAdded} data baru ditambahkan, {$totalUpdated} data diperbarui.";
        if (count($errors) > 0) {
            $flashMsg .= " Catatan: " . implode(' ', $errors);
        }

        return redirect()->route('wola.google-sheets.index')
            ->with('success', $flashMsg);
    }

    /**
     * Unduh Template CSV yang siap dicopy/diunggah ke Google Sheet
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->input('type', 'karyawan');
        $validTypes = ['karyawan', 'kelompok', 'posisi_job', 'hasil_penilaian', 'tim_penilai'];

        if (!in_array($type, $validTypes)) {
            $type = 'karyawan';
        }

        $csv = $this->googleSheetService->generateTemplateCsv($type);
        $filename = "Template_WOLA_{$type}_" . date('Ymd_His') . ".csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Unduh Seluruh Database dalam 1 Berkas Excel Multi-Sheet Siap Impor ke Google Spreadsheet
     */
    public function downloadFullDatabase()
    {
        return Excel::download(new FullDatabaseExport(), 'DATABASE_WOLA_UNTUK_SPREADSHEET_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Kirim / Timpa Seluruh Data Hasil Penilaian ke Google Spreadsheet (Format Baru 9 Kolom dengan NRP_Penilai & NRP_Dinilai)
     */
    public function pushHasilPenilaian()
    {
        $result = $this->googleSheetService->bulkSyncAssessmentsToSheet();

        if ($result['success']) {
            return redirect()->route('wola.google-sheets.index')
                ->with('success', "Berhasil mengirim {$result['count']} baris hasil penilaian ke Google Spreadsheet dengan format lengkap 9 kolom (NRP Penilai & NRP Dinilai).");
        }

        return redirect()->route('wola.google-sheets.index')
            ->with('error', 'Gagal mengirim data penilaian: ' . ($result['message'] ?? $result['error'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Uji koneksi Webhook Google Apps Script via AJAX / POST
     */
    public function testWebhook(Request $request)
    {
        $url = $request->input('apps_script_url');
        $result = $this->googleSheetService->testWebhook($url);
        return response()->json($result);
    }
}
