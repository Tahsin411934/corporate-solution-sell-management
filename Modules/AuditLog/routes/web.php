<?php
use Illuminate\Support\Facades\Route;
use Modules\AuditLog\Http\Controllers\AuditLogController;
Route::middleware(['auth', 'verified', 'can:audit-logs.view'])->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/data', [AuditLogController::class, 'data'])->name('audit-logs.data');
});
