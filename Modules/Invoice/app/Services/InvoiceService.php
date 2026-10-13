<?php

namespace Modules\Invoice\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Invoice\Models\Invoice;
use Modules\CompanySettings\Models\CompanySetting;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(private readonly InvoiceItemService $items) {}

    public function create(array $data, int $userId): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $company = CompanySetting::active()->orderBy('id')->first();
            $data['bill_title'] = $data['bill_title'] ?? $company?->bill_title ?? Invoice::DEFAULT_BILL_TITLE;
            if (!array_key_exists('bill_subtitle', $data)) {
                $data['bill_subtitle'] = $company ? $company->bill_subtitle : Invoice::DEFAULT_BILL_SUBTITLE;
            }
            if (!empty($data['auto_invoice_number'])) {
                $data['invoice_number'] = app(InvoiceNumberService::class)->next($data['invoice_date']);
            }
            if (!empty($data['new_customer'])) {
                $customer = app(\Modules\Customer\Services\CustomerService::class)->create(
                    Arr::only($data['new_customer_data'], ['name', 'bin_number', 'tin_number', 'address']), $userId
                );
                $data['customer_id'] = $customer->id;
            }
            $invoice = Invoice::create(Arr::only($data, [
                'customer_id', 'referral_source', 'invoice_number', 'invoice_date',
                'due_date', 'currency_code', 'discount_type', 'discount_value',
                'tax_rate', 'payment_terms', 'notes', 'bill_title', 'bill_subtitle',
            ]) + ['created_by' => $userId, 'company_setting_id' => $company?->id]);
            foreach (array_values($data['items']) as $index => $item) {
                $this->items->create($invoice, Arr::only($item, [
                    'service_id', 'description', 'quantity', 'unit', 'rate', 'unit_cost',
                ]) + ['sort_order' => $index + 1]);
            }
            $invoice = $invoice->fresh('items');
            $paid = bcadd((string) ($data['initial_payment_amount'] ?? '0'), '0', 2);
            if (bccomp($paid, $invoice->total_amount, 2) > 0) {
                throw ValidationException::withMessages(['initial_payment_amount' => 'Received now cannot exceed the total bill.']);
            }
            if (($data['save_mode'] ?? 'draft') === 'draft' && bccomp($paid, '0', 2) > 0) {
                throw ValidationException::withMessages(['initial_payment_amount' => 'Draft invoices cannot receive payments.']);
            }
            if (($data['save_mode'] ?? 'draft') === 'issue') {
                $invoice = $this->issue($invoice);
                if (bccomp($paid, '0', 2) > 0) {
                    app(\Modules\Payment\Services\PaymentService::class)->create([
                        'invoice_id' => $invoice->id, 'amount' => $paid,
                        'payment_method' => $data['initial_payment_method'],
                        'payment_date' => $data['initial_payment_date'],
                        'transaction_number' => $data['initial_payment_reference'] ?? null,
                    ], $userId);
                }
            }
            return $invoice->fresh(['items', 'payments']);
        }, 5);
    }

    public function issue(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($invoice->status !== 'draft' || !$invoice->items()->exists()) {
                throw ValidationException::withMessages(['invoice' => 'Only a draft invoice with items can be issued.']);
            }
            if (!$invoice->customer()->exists()) {
                throw ValidationException::withMessages(['invoice' => 'The invoice customer is no longer available.']);
            }
            $invoice->status = bccomp($invoice->total_amount, '0', 2) === 0 ? 'paid'
                : ($invoice->due_date?->isBefore(today()) ? 'overdue' : 'issued');
            $invoice->save();
            return $invoice;
        });
    }

    public function update(Invoice $invoice, array $data, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $userId) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages(['invoice' => 'Cancelled invoices cannot be edited.']);
            }
            $hasRecords = $invoice->payments()->withTrashed()->exists()
                || \Modules\Expense\Models\Expense::withTrashed()->where('invoice_id', $invoice->id)->exists();
            if ($hasRecords && ($data['currency_code'] !== $invoice->currency_code
                || !empty($data['new_customer']) || (int) $data['customer_id'] !== $invoice->customer_id)) {
                throw ValidationException::withMessages(['customer_id' => 'Customer and currency cannot change once payments or expenses have been recorded.']);
            }
            if (!empty($data['new_customer'])) {
                $data['customer_id'] = app(\Modules\Customer\Services\CustomerService::class)->create(
                    Arr::only($data['new_customer_data'], ['name', 'bin_number', 'tin_number', 'address']), $userId
                )->id;
            }
            $invoice->fill(Arr::only($data, ['customer_id', 'referral_source', 'invoice_number', 'invoice_date',
                'due_date', 'currency_code', 'discount_type', 'discount_value', 'tax_rate', 'payment_terms', 'notes', 'bill_title', 'bill_subtitle']));
            $invoice->save();
            $this->items->replace($invoice, $data['items']);
            $invoice->refresh();
            $received = (string) $invoice->payments()->sum('amount');
            if (bccomp($received, $invoice->total_amount, 2) > 0) {
                throw ValidationException::withMessages(['items' => 'Invoice total cannot be less than payments already received.']);
            }
            if ($invoice->status !== 'draft') {
                $invoice->status = bccomp($received, $invoice->total_amount, 2) >= 0 ? 'paid'
                    : (bccomp($received, '0', 2) > 0 ? 'partial' : ($invoice->due_date?->isBefore(today()) ? 'overdue' : 'issued'));
                $invoice->save();
            }
            return $invoice->fresh(['items', 'payments']);
        }, 5);
    }

    public function cancel(Invoice $invoice, string $reason, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason, $userId) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($invoice->status === 'cancelled' || $invoice->payments()->exists()) {
                throw ValidationException::withMessages(['invoice' => 'Already cancelled invoices or invoices with payments cannot be cancelled.']);
            }
            $invoice->forceFill(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $userId, 'cancellation_reason' => $reason])->save();
            return $invoice;
        });
    }

    public function details(Invoice $invoice): Invoice
    {
        return $invoice->load(['items.service', 'customer' => fn ($query) => $query->withTrashed(),
            'companySetting' => fn ($query) => $query->withTrashed(), 'payments', 'creator', 'cancelledBy'])
            ->loadSum('payments as received_amount', 'amount');
    }
}
