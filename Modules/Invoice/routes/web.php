<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoice\Http\Controllers\InvoiceController;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('can:invoices.view')->name('invoices.index');
    Route::get('/invoices/data', [InvoiceController::class, 'data'])->middleware('can:invoices.view')->name('invoices.data');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->middleware('can:invoices.create')->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->middleware('can:invoices.create')->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('can:invoices.view')->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->middleware('can:invoices.print')->name('invoices.print');
    Route::post('/invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->middleware('can:invoices.issue')->name('invoices.issue');
    Route::post('/invoices/{invoice}/expenses', [InvoiceController::class, 'recordExpense'])->middleware(['can:invoices.view', 'can:expenses.create'])->name('invoices.expenses.store');
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->middleware('can:invoices.cancel')->name('invoices.cancel');
});
