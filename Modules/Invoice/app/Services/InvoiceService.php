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
            $invoice = Invoice::create(Arr::only($data, [
                'customer_id', 'referral_source', 'invoice_number', 'invoice_date',
                'due_date', 'currency_code', 'discount_type', 'discount_value',
                'tax_rate', 'payment_terms', 'notes',
            ]) + ['created_by' => $userId, 'company_setting_id' => CompanySetting::active()->orderBy('id')->value('id')]);
            foreach (array_values($data['items']) as $index => $item) {
                $this->items->create($invoice, Arr::only($item, [
                    'service_id', 'description', 'quantity', 'unit', 'rate', 'unit_cost',
                ]) + ['sort_order' => $index + 1]);
            }
            return $invoice->fresh('items');
        });
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
