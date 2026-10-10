<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Http\Requests\StoreInvoiceRequest;
use Modules\Invoice\Services\InvoiceService;
use Modules\Service\Models\Service;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Services\InvoiceDataTableService;
use Modules\Invoice\Http\Requests\IssueInvoiceRequest;
use Modules\Invoice\Http\Requests\CancelInvoiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Expense\Http\Requests\StoreExpenseRequest;
use Modules\Expense\Models\Expense;
use Modules\Expense\Services\ExpenseService;

class InvoiceController extends Controller
{
    public function index(): View { return view('invoice::index'); }
    public function data(InvoiceDataTableService $service): JsonResponse { return $service->response(); }
    public function show(Invoice $invoice, InvoiceService $service): View
    {
        $expenses = Expense::where('invoice_id', $invoice->id)->orderByDesc('expense_date')->orderByDesc('id')->get();
        $actualExpense = $expenses->reduce(fn ($total, $expense) => bcadd($total, $expense->amount, 2), '0.00');
        $remainingCost = bcsub($invoice->total_cost, $actualExpense, 2);
        return view('invoice::show', [
            'invoice' => $service->details($invoice), 'expenses' => $expenses, 'actualExpense' => $actualExpense,
            'suggestedExpense' => bccomp($remainingCost, '0', 2) > 0 ? $remainingCost : '',
        ]);
    }
    public function recordExpense(StoreExpenseRequest $request, Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($request, $invoice) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (in_array($invoice->status, ['draft', 'cancelled'], true)) {
                throw ValidationException::withMessages(['invoice' => 'Expenses can only be recorded against an issued invoice.']);
            }
            app(ExpenseService::class)->create(
                array_replace($request->validated(), ['invoice_id' => $invoice->id]), $request->user()->id
            );
        });
        return redirect()->route('invoices.show', $invoice)->with('success', 'Actual expense recorded successfully.');
    }
    public function print(Invoice $invoice, InvoiceService $service): View
    {
        return view('invoice::print', ['invoice' => $service->details($invoice)]);
    }
    public function issue(IssueInvoiceRequest $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $service->issue($invoice);
        return back()->with('success', 'Invoice issued successfully.');
    }
    public function cancel(CancelInvoiceRequest $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $service->cancel($invoice, $request->validated('cancellation_reason'), $request->user()->id);
        return back()->with('success', 'Invoice cancelled successfully.');
    }
    public function create(): View
    {
        return view('invoice::create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'description', 'default_rate', 'default_cost']),
            'invoiceNumber' => app(\Modules\Invoice\Services\InvoiceNumberService::class)->preview(today()->toDateString()),
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoiceService $service): RedirectResponse
    {
        try {
            $invoice = $service->create($request->validated(), $request->user()->id);
        } catch (UniqueConstraintViolationException $exception) {
            if (!Invoice::withTrashed()->where('invoice_number', $request->validated('invoice_number'))->exists()) {
                throw $exception;
            }
            throw ValidationException::withMessages(['invoice_number' => 'This invoice number is already in use.']);
        }
        return redirect()->route($request->user()->can('invoices.view') ? 'invoices.show' : 'invoices.create', $request->user()->can('invoices.view') ? $invoice : [])
            ->with('success', ($invoice->status === 'draft' ? 'Draft invoice ' : 'Invoice ').$invoice->invoice_number.' saved successfully.');
    }
}
