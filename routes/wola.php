<?php

use Illuminate\Support\Facades\Route;
use App\Models\Group;
use App\Http\Controllers\Wola\EmployeeController;
use App\Http\Controllers\Wola\UserController;
use App\Http\Controllers\Wola\GroupController;
use App\Http\Controllers\Wola\AssessmentController;
use App\Http\Controllers\Wola\DashboardController;
use App\Http\Controllers\Wola\ReportController;
use App\Http\Controllers\Wola\ActivityLogController;
use App\Http\Controllers\Wola\GoogleSheetController;

/*
|--------------------------------------------------------------------------
| WOLA (Workload Assessment) Routes
|--------------------------------------------------------------------------
| Seluruh rute modul Penilaian Beban Kerja WOLA MUSASHI diisolasi di sini.
*/

Route::middleware(['auth'])->prefix('wola')->name('wola.')->group(function () {
    
    // =========================================================================
    // RUTE KHUSUS ADMINISTRATOR (SUPERADMIN & ADMIN)
    // =========================================================================
    Route::middleware(['role:superadmin|admin'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');

        // Integrasi Google Spreadsheet (Khusus Superadmin)
        Route::middleware(['role:superadmin'])->prefix('google-sheets')->name('google-sheets.')->group(function () {
            Route::get('/', [GoogleSheetController::class, 'index'])->name('index');
            Route::post('/settings', [GoogleSheetController::class, 'updateSettings'])->name('settings.update');
            Route::post('/test-webhook', [GoogleSheetController::class, 'testWebhook'])->name('test-webhook');
            Route::post('/sync', [GoogleSheetController::class, 'sync'])->name('sync');
            Route::post('/push-hasil-penilaian', [GoogleSheetController::class, 'pushHasilPenilaian'])->name('push-hasil-penilaian');
            Route::get('/template', [GoogleSheetController::class, 'downloadTemplate'])->name('template');
            Route::get('/download-full-database', [GoogleSheetController::class, 'downloadFullDatabase'])->name('download-full-database');
        });

        // Master Karyawan
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/create', [EmployeeController::class, 'create'])->name('create');
            Route::post('/', [EmployeeController::class, 'store'])->name('store');
            Route::get('/template/download', [EmployeeController::class, 'downloadTemplate'])->name('template');
            Route::post('/import/preview', [EmployeeController::class, 'importPreview'])->name('import.preview');
            Route::post('/import/confirm', [EmployeeController::class, 'importConfirm'])->name('import.confirm');
            Route::get('/export/excel', [EmployeeController::class, 'export'])->name('export');
            Route::delete('/destroy-all', [EmployeeController::class, 'destroyAll'])->name('destroy.all');
            Route::delete('/destroy-bulk', [EmployeeController::class, 'destroyBulk'])->name('destroy.bulk');
            Route::delete('/destroy-category', [EmployeeController::class, 'destroyByCategory'])->name('destroy.category');
            Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
            Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        });

        // Manajemen Pengguna (Hierarkis: Superadmin kelola Admin & Ketua, Admin kelola Ketua)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::post('/sync-sheet', [UserController::class, 'syncFromSheet'])->name('sync-sheet');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Manajemen Kelompok (CRUD Kelompok)
        Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
        Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
        Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

        // Audit Trail / Log Aktivitas Sistem
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::middleware(['role:superadmin'])->prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::delete('/destroy-all', [ActivityLogController::class, 'destroyAll'])->name('destroy.all');
            Route::delete('/destroy-bulk', [ActivityLogController::class, 'destroyBulk'])->name('destroy.bulk');
            Route::delete('/{activityLog}', [ActivityLogController::class, 'destroy'])->whereNumber('activityLog')->name('destroy');
        });
    });

    // =========================================================================
    // RUTE UTAMA LEMBAR PENILAIAN WOLA (Redirect Cerdas sesuai Hak Akses)
    // =========================================================================
    Route::get('/assessments', [AssessmentController::class, 'rootIndex'])->name('assessments.root');

    // =========================================================================
    // RUTE DETAIL KELOMPOK (Bisa diakses Superadmin, Admin, dan Ketua kelompok ybs)
    // =========================================================================
    Route::prefix('groups/{group}')->name('groups.')->group(function () {
        Route::get('/', [GroupController::class, 'show'])->name('show');

        // Kelola Jabatan
        Route::post('/jobs', [GroupController::class, 'addJob'])->name('jobs.add');
        Route::put('/jobs/{job}', [GroupController::class, 'updateJob'])->name('jobs.update');
        Route::patch('/jobs/{job}/toggle', [GroupController::class, 'toggleJobActive'])->name('jobs.toggle');
        Route::delete('/jobs/{job}', [GroupController::class, 'removeJob'])->name('jobs.remove');

        // Sesuaikan Target Manual / Otomatis
        Route::post('/target/update', [GroupController::class, 'updateTarget'])->name('target.update');

        // Kelola Penilai
        Route::post('/assessors', [GroupController::class, 'addAssessor'])->name('assessors.add');
        Route::put('/assessors/{assessor}', [GroupController::class, 'updateAssessor'])->name('assessors.update');
        Route::patch('/assessors/{assessor}/leader', [GroupController::class, 'setLeader'])->name('assessors.leader');
        Route::delete('/assessors/{assessor}', [GroupController::class, 'removeAssessor'])->name('assessors.remove');

        // Kelola Karyawan Dinilai (Target Employees)
        Route::get('/employees/search', [GroupController::class, 'searchMasterEmployees'])->name('employees.search');
        Route::post('/employees/sync', [GroupController::class, 'syncEmployees'])->name('employees.sync');
        Route::delete('/employees/{employee}', [GroupController::class, 'removeEmployee'])->name('employees.remove');

        // Halaman & Aksi Penilaian WOLA Dua Mode
        Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::post('/assessment/toggle', [AssessmentController::class, 'toggle'])->name('assessments.toggle');
        Route::post('/assessment/batch', [AssessmentController::class, 'batch'])->name('assessments.batch');
        Route::post('/assessment/comments', [AssessmentController::class, 'saveJobComment'])->name('assessments.comments.store');
        Route::delete('/assessment/comments/{comment}', [AssessmentController::class, 'deleteJobComment'])->name('assessments.comments.destroy');
    });

    // =========================================================================
    // RUTE KHUSUS KETUA KELOMPOK
    // =========================================================================
    Route::middleware(['role:ketua|admin|superadmin'])->group(function () {
        Route::get('/ketua/dashboard', [DashboardController::class, 'ketuaDashboard'])->name('ketua.dashboard');
    });

    // =========================================================================
    // RUTE PUSAT LAPORAN WOLA (Superadmin, Admin, dan Ketua Kelompok)
    // =========================================================================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/dashboard-excel', [ReportController::class, 'exportDashboardExcel'])->name('export.dashboard-excel');
        Route::get('/print', [ReportController::class, 'print'])->name('print');
        Route::delete('/assessments/destroy-all', [ReportController::class, 'destroyAllAssessments'])->name('assessments.destroy-all');
    });

});
