<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Http\Requests\StoreInvoiceRequest;
use Modules\Invoice\Services\InvoiceService;
use Modules\ReferralSource\Models\ReferralSource;
use Modules\Service\Models\Service;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Services\InvoiceDataTableService;
use Modules\Invoice\Http\Requests\IssueInvoiceRequest;
use Modules\Invoice\Http\Requests\CancelInvoiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(): View { return view('invoice::index'); }
    public function data(InvoiceDataTableService $service): JsonResponse { return $service->response(); }
    public function show(Invoice $invoice, InvoiceService $service): View
    {
        return view('invoice::show', ['invoice' => $service->details($invoice)]);
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
            'referrals' => ReferralSource::orderBy('name')->get(['id', 'name']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'description', 'default_rate', 'default_cost']),
            'invoiceNumber' => 'INV-'.now()->format('Ymd').'-'.Str::upper((string) Str::ulid()),
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
            ->with('success', 'Draft invoice '.$invoice->invoice_number.' saved successfully.');
    }
}
