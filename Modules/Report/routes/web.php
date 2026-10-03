<?php
use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;
Route::middleware(['web', 'auth', 'verified', 'can:reports.view'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');
});
