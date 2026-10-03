<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/data', [CustomerController::class, 'dataTable'])->name('customers.data');
    Route::get('customers/next-code', [CustomerController::class, 'nextCode'])->name('customers.next-code');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
});
