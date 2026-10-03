<?php
use Illuminate\Support\Facades\Route;
use Modules\Service\Http\Controllers\ServiceController;

Route::middleware(['auth', 'verified'])->controller(ServiceController::class)->group(function () {
    Route::get('services', 'index')->middleware('can:services.view')->name('services.index');
    Route::get('services/data', 'data')->middleware('can:services.view')->name('services.data');
    Route::post('services', 'store')->middleware('can:services.create')->name('services.store');
    Route::put('services/{entity}', 'update')->middleware('can:services.update')->name('services.update');
    Route::delete('services/{entity}', 'destroy')->middleware('can:services.delete')->name('services.destroy');
});
