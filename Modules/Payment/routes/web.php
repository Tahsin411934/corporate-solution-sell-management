<?php
use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;
Route::middleware(['auth', 'verified'])->controller(PaymentController::class)->group(function () {
    Route::get('payments', 'index')->middleware('can:payments.view')->name('payments.index');
    Route::get('payments/data', 'data')->middleware('can:payments.view')->name('payments.data');
    Route::post('payments', 'store')->middleware('can:payments.create')->name('payments.store');
    Route::put('payments/{entity}', 'update')->middleware('can:payments.update')->name('payments.update');
    Route::delete('payments/{entity}', 'destroy')->middleware('can:payments.delete')->name('payments.destroy');
});
