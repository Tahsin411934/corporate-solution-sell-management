<?php
use Illuminate\Support\Facades\Route;
use Modules\Expense\Http\Controllers\ExpenseController;
Route::middleware(['auth', 'verified'])->controller(ExpenseController::class)->group(function () {
    Route::get('expenses', 'index')->middleware('can:expenses.view')->name('expenses.index');
    Route::get('expenses/data', 'data')->middleware('can:expenses.view')->name('expenses.data');
    Route::post('expenses', 'store')->middleware('can:expenses.create')->name('expenses.store');
    Route::put('expenses/{entity}', 'update')->middleware('can:expenses.update')->name('expenses.update');
    Route::delete('expenses/{entity}', 'destroy')->middleware('can:expenses.delete')->name('expenses.destroy');
});
