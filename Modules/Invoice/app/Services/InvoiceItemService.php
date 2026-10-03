<?php
namespace Modules\Invoice\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Models\InvoiceItem;

class InvoiceItemService
{
    public function create(Invoice $invoice, array $data): InvoiceItem
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = $this->lockDraft($invoice->id);
            $item = $invoice->items()->make($data);
            $this->calculate($item);
            $item->save();
            $this->updateTotals($invoice);
            return $item;
        });
    }

    public function update(InvoiceItem $item, array $data): InvoiceItem
    {
        return DB::transaction(function () use ($item, $data) {
            $invoice = $this->lockDraft($item->invoice_id);
            $item = InvoiceItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $item->fill($data);
            $this->calculate($item);
            $item->save();
            $this->updateTotals($invoice);
            return $item;
        });
    }

    public function delete(InvoiceItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            $invoice = $this->lockDraft($item->invoice_id);
            $item = InvoiceItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $deleted = (bool) $item->delete();
            $this->updateTotals($invoice);
            return $deleted;
        });
    }

    private function lockDraft(int $id): Invoice
    {
        $invoice = Invoice::whereKey($id)->lockForUpdate()->firstOrFail();
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages(['invoice' => 'Only draft invoices can have their items changed.']);
        }
        return $invoice;
    }

    private function money(string $value): string
    {
        $rounded = bcadd($value, bccomp($value, '0', 6) < 0 ? '-0.005' : '0.005', 2);
        if (bccomp($rounded, '999999999999.99', 2) > 0 || bccomp($rounded, '-999999999999.99', 2) < 0) {
            throw ValidationException::withMessages(['amount' => 'Calculated amount exceeds the supported limit.']);
        }
        return $rounded;
    }

    private function calculate(InvoiceItem $item): void
    {
        if (bccomp((string) $item->quantity, '0', 3) <= 0 || bccomp((string) $item->rate, '0', 2) < 0 || bccomp((string) $item->unit_cost, '0', 2) < 0) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be positive and prices cannot be negative.']);
        }
        $item->amount = $this->money(bcmul((string) $item->quantity, (string) $item->rate, 5));
        $item->total_cost = $this->money(bcmul((string) $item->quantity, (string) $item->unit_cost, 5));
        $item->profit = bcsub($item->amount, $item->total_cost, 2);
    }

    private function updateTotals(Invoice $invoice): void
    {
        $subtotal = '0.00';
        $cost = '0.00';
        foreach ($invoice->items()->get() as $item) {
            $subtotal = bcadd($subtotal, $item->amount, 2);
            $cost = bcadd($cost, $item->total_cost, 2);
        }
        $subtotal = $this->money($subtotal);
        $cost = $this->money($cost);
        if (bccomp($invoice->discount_value, '0', 2) < 0 || bccomp($invoice->tax_rate, '0', 4) < 0 || ($invoice->discount_type === 'percentage' && bccomp($invoice->discount_value, '100', 2) > 0)) {
            throw ValidationException::withMessages(['discount_value' => 'Invalid discount or tax rate.']);
        }
        $discount = $invoice->discount_type === 'percentage'
            ? $this->money(bcdiv(bcmul($subtotal, $invoice->discount_value, 4), '100', 4))
            : $invoice->discount_value;
        if (bccomp($discount, $subtotal, 2) > 0) $discount = $subtotal;
        $net = bcsub($subtotal, $discount, 2);
        $tax = $this->money(bcdiv(bcmul($net, $invoice->tax_rate, 6), '100', 6));
        $invoice->forceFill([
            'subtotal' => $subtotal, 'discount_amount' => $discount, 'tax_amount' => $tax,
            'total_amount' => $this->money(bcadd($net, $tax, 2)), 'total_cost' => $cost,
            'total_profit' => $this->money(bcsub($net, $cost, 2)),
        ])->save();
    }
}
