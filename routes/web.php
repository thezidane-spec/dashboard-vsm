<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PdcaController;

// Route Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route Dashboard & Project Operations (Memerlukan Login)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Fitur CRUD Proyek
    Route::get('/projects/create', [DashboardController::class, 'create'])->name('projects.create');
    Route::post('/projects', [DashboardController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit', [DashboardController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [DashboardController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [DashboardController::class, 'destroy'])->name('projects.destroy');

    // Fitur Blocker Log / Issue Tracking
    Route::post('/projects/{project}/issues', [DashboardController::class, 'storeIssue'])->name('projects.issues.store');
    Route::put('/projects/{project}/remarks', [DashboardController::class, 'updateRemarks'])->name('projects.remarks.update');
    Route::delete('/projects/{project}/remarks', [DashboardController::class, 'clearRemarks'])->name('projects.remarks.clear');
    // Weekly Report (Foto Progress) -- di bawah S-Curve halaman proyek
    Route::post('/projects/{project}/weekly', [\App\Http\Controllers\WeeklyReportController::class, 'store'])->name('projects.weekly.store');
    Route::put('/projects/{project}/weekly/{report}', [\App\Http\Controllers\WeeklyReportController::class, 'update'])->whereNumber('report')->name('projects.weekly.update');
    Route::delete('/projects/{project}/weekly/{report}', [\App\Http\Controllers\WeeklyReportController::class, 'destroy'])->whereNumber('report')->name('projects.weekly.destroy');
    Route::get('/projects/{project}/weekly/{report}/export', [\App\Http\Controllers\WeeklyReportController::class, 'export'])->whereNumber('report')->name('projects.weekly.export');
    Route::get('/projects/{project}/weekly/{report}/photo/{photo}', [\App\Http\Controllers\WeeklyReportController::class, 'photo'])->whereNumber(['report', 'photo'])->name('projects.weekly.photo');
    Route::get('/projects/{project}/evidence', [DashboardController::class, 'showEvidence'])->name('projects.evidence');
    Route::post('/projects/{project}/progress-history', [DashboardController::class, 'storeProgressHistory'])->name('projects.progress-history.store');
    Route::delete('/projects/{project}/progress-history/{history}', [DashboardController::class, 'destroyProgressHistory'])
        ->where('history', '[0-9]+')
        ->name('projects.progress-history.destroy');
    Route::delete('/projects/{project}/remarks/{type}/{index}', [DashboardController::class, 'destroyRemarkItem'])
        ->where(['type' => 'problems|tasks|pics', 'index' => '[0-9]+'])
        ->name('projects.remarks.item.destroy');
    Route::patch('/issues/{issue}/status', [DashboardController::class, 'updateIssueStatus'])->name('issues.update-status');

    // Fitur Detail Material (Raw Material & Vendor Raw Material) - Divisi Procurement
    Route::prefix('/projects/{project}/materials')->name('projects.materials.')->group(function () {
        Route::get('/', [MaterialController::class, 'index'])->name('index');
        Route::post('/items', [MaterialController::class, 'storeItem'])->name('items.store');
        Route::post('/scan', [MaterialController::class, 'scanPdf'])->name('scan');
        Route::post('/import', [MaterialController::class, 'importItems'])->name('import');
        Route::get('/radar-sources', [MaterialController::class, 'radarSources'])->name('radar.sources');
        Route::post('/radar-import', [MaterialController::class, 'importFromRadar'])->name('radar.import');
        Route::patch('/items/{item}/required-date', [MaterialController::class, 'updateRequiredDate'])->name('items.required-date');
        Route::patch('/items/{item}/mill-cert', [MaterialController::class, 'updateMillCert'])->name('items.mill-cert');
        Route::post('/revision', [MaterialController::class, 'applyRevision'])->name('revision.apply');
        Route::put('/items/{item}', [MaterialController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{item}', [MaterialController::class, 'destroyItem'])->name('items.destroy');
        Route::post('/arrivals', [MaterialController::class, 'storeArrival'])->name('arrivals.store');
        Route::put('/arrivals/{arrival}', [MaterialController::class, 'updateArrival'])->name('arrivals.update');
        Route::delete('/arrivals/{arrival}', [MaterialController::class, 'destroyArrival'])->name('arrivals.destroy');
        Route::get('/export/excel', [MaterialController::class, 'exportExcel'])->name('export.excel');
        Route::get('/print', [MaterialController::class, 'print'])->name('print');
    });

    // Material Radar List (per proyek; impor dari Google Spreadsheet / .xlsx; edit per PIC)
    Route::prefix('/material-radar')->name('radar.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MaterialRadarController::class, 'index'])->name('index');
        Route::post('/import', [\App\Http\Controllers\MaterialRadarController::class, 'import'])->name('import');
        Route::put('/{radar}/items', [\App\Http\Controllers\MaterialRadarController::class, 'updateItems'])->name('items.update');
        Route::post('/{radar}/items', [\App\Http\Controllers\MaterialRadarController::class, 'addItem'])->name('items.add');
        Route::delete('/{radar}/items/{item}', [\App\Http\Controllers\MaterialRadarController::class, 'deleteItem'])->name('items.delete');
        Route::put('/{radar}', [\App\Http\Controllers\MaterialRadarController::class, 'updateMeta'])->name('meta');
        Route::delete('/', [\App\Http\Controllers\MaterialRadarController::class, 'destroyAll'])->name('destroy-all');
        Route::delete('/{radar}', [\App\Http\Controllers\MaterialRadarController::class, 'destroy'])->name('destroy');
    });

    // PDCA Control Center (papan 9 panel)
    Route::get('/pdca', [PdcaController::class, 'index'])->name('pdca');

    // Fitur Summary Report: rekap semua proyek per divisi/tahap (PPIC, Engineering, Production, Finishing)
    Route::prefix('/reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        // Export Summary Report - PPIC (harus di atas '/{key}')
        Route::get('/ppic/export/excel', [ReportController::class, 'exportPpicExcel'])->name('ppic.excel');
        Route::get('/ppic/print', [ReportController::class, 'printPpic'])->name('ppic.print');
        Route::get('/{key}', [ReportController::class, 'show'])
            ->where('key', 'ppic|engineering|preparation|main_production|qc|qaqc|remarks|scurve')
            ->name('show');
    });
});