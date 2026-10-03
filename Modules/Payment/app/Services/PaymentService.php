<?php
namespace Modules\Payment\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Invoice\Models\Invoice;
use Modules\Payment\Models\Payment;
class PaymentService
{
    public function create(array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $invoice = $this->lockInvoice((int) $data['invoice_id']);
            $this->validateAmount($invoice, (string) $data['amount']);
            $payment = new Payment($data);
            $payment->invoice_id = $invoice->id;
            $payment->customer_id = $invoice->customer_id;
            $payment->received_by = $userId ?? auth()->id();
            $payment->save();
            $this->updateStatus($invoice);
            return $payment;
        });
    }
    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $invoice = $this->lockInvoice($payment->invoice_id);
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if (isset($data['invoice_id']) && (int) $data['invoice_id'] !== $invoice->id) {
                throw ValidationException::withMessages(['invoice_id' => 'A payment cannot be moved to another invoice.']);
            }
            $this->validateAmount($invoice, (string) $data['amount'], $payment->id);
            $payment->fill($data)->save();
            $this->updateStatus($invoice);
            return $payment;
        });
    }
    public function delete(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $invoice = $this->lockInvoice($payment->invoice_id);
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $deleted = (bool) $payment->delete();
            $this->updateStatus($invoice);
            return $deleted;
        });
    }
    private function lockInvoice(int $id): Invoice
    {
        $invoice = Invoice::whereKey($id)->lockForUpdate()->firstOrFail();
        if (in_array($invoice->status, ['draft', 'cancelled'])) {
            throw ValidationException::withMessages(['invoice_id' => 'Payments require an issued, active invoice.']);
        }
        return $invoice;
    }
    private function validateAmount(Invoice $invoice, string $amount, ?int $exclude = null): void
    {
        $paid = (string) Payment::where('invoice_id', $invoice->id)->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))->sum('amount');
        $due = bcsub($invoice->total_amount, $paid, 2);
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount) || bccomp($amount, '0', 2) <= 0 || bccomp($amount, $due, 2) > 0) {
            throw ValidationException::withMessages(['amount' => 'Payment must be positive and cannot exceed the outstanding amount.']);
        }
    }
    private function updateStatus(Invoice $invoice): void
    {
        $paid = (string) Payment::where('invoice_id', $invoice->id)->sum('amount');
        $invoice->status = bccomp($paid, $invoice->total_amount, 2) >= 0 ? 'paid'
            : (bccomp($paid, '0', 2) > 0 ? 'partial' : ($invoice->due_date?->isBefore(today()) ? 'overdue' : 'issued'));
        $invoice->save();
    }
}
