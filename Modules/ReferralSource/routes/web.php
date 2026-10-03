<?php
use Illuminate\Support\Facades\Route;
use Modules\ReferralSource\Http\Controllers\ReferralSourceController;

Route::middleware(['auth', 'verified'])->controller(ReferralSourceController::class)->group(function () {
    Route::get('referral-sources', 'index')->middleware('can:referral-sources.view')->name('referral-sources.index');
    Route::get('referral-sources/data', 'data')->middleware('can:referral-sources.view')->name('referral-sources.data');
    Route::post('referral-sources', 'store')->middleware('can:referral-sources.create')->name('referral-sources.store');
    Route::put('referral-sources/{entity}', 'update')->middleware('can:referral-sources.update')->name('referral-sources.update');
    Route::delete('referral-sources/{entity}', 'destroy')->middleware('can:referral-sources.delete')->name('referral-sources.destroy');
});
