<?php

use Illuminate\Support\Facades\Route;
use Modules\CompanySettings\Http\Controllers\CompanySettingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('company-settings', [CompanySettingController::class, 'index'])->name('companysettings.index');
    Route::post('company-settings', [CompanySettingController::class, 'store'])->name('companysettings.store');
    Route::put('company-settings/{companySetting}', [CompanySettingController::class, 'update'])->name('companysettings.update');
    Route::delete('company-settings/{companySetting}/logo', [CompanySettingController::class, 'destroyLogo'])->name('companysettings.logo.destroy');
    Route::get('company-settings/invoice-number/preview', [CompanySettingController::class, 'previewInvoiceNumber'])->name('companysettings.invoice-number.preview');
});
