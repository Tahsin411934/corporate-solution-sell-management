<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('customers', [CustomerController::class, 'apiIndex'])->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});
