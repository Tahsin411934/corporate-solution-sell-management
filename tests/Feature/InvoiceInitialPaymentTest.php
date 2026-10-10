<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Models\Invoice;
use Modules\Payment\Models\Payment;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceInitialPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(bool $paymentPermission = true): void
    {
        $user = User::factory()->create();
        foreach (['invoices.create', 'invoices.view', 'invoices.issue', 'customers.create'] as $name) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        if ($paymentPermission) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => 'payments.create', 'guard_name' => 'web']));
        }
        $this->actingAs($user);
    }

    private function payload(string $amount = '0'): array
    {
        return ['customer_id' => Customer::create(['customer_code' => 'PAY-TEST', 'name' => 'Customer'])->id,
            'auto_invoice_number' => '1', 'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT',
            'discount_type' => 'percentage', 'discount_value' => '10', 'tax_rate' => '5',
            'save_mode' => 'issue', 'initial_payment_amount' => $amount,
            'initial_payment_method' => 'bank', 'initial_payment_date' => today()->toDateString(), 'initial_payment_reference' => 'TX-123',
            'items' => [['description' => 'Consulting', 'quantity' => '2', 'unit' => 'service', 'rate' => '100', 'unit_cost' => '0']]];
    }

    public function test_partial_payment_creates_linked_record_and_due_uses_recalculated_total(): void
    {
        $this->signIn();
        $this->get(route('invoices.create'))->assertOk()->assertSee('Received now')->assertSee('Due')->assertSee('Save invoice')->assertSee('Save draft invoice');
        $this->post(route('invoices.store'), $this->payload('50'))->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $payment = Payment::firstOrFail();
        $this->assertSame('189.00', $invoice->total_amount);
        $this->assertSame('partial', $invoice->status);
        $this->assertSame($invoice->id, $payment->invoice_id);
        $this->assertSame($invoice->customer_id, $payment->customer_id);
        $this->assertSame('50.00', $payment->amount);
        $this->assertSame('TX-123', $payment->transaction_number);
        $this->assertSame(auth()->id(), $payment->received_by);
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('139.00');
    }

    public function test_full_payment_marks_invoice_paid(): void
    {
        $this->signIn();
        $this->post(route('invoices.store'), $this->payload('189'))->assertSessionHasNoErrors();
        $this->assertSame('paid', Invoice::firstOrFail()->status);
        $this->assertSame('189.00', Payment::firstOrFail()->amount);
    }

    public function test_zero_payment_issues_invoice_without_payment_row(): void
    {
        $this->signIn();
        $this->post(route('invoices.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertSame('issued', Invoice::firstOrFail()->status);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_save_draft_keeps_invoice_unissued_and_rejects_payment(): void
    {
        $this->signIn();
        $data = $this->payload('10');
        $data['save_mode'] = 'draft';
        $this->post(route('invoices.store'), $data)->assertSessionHasErrors('initial_payment_amount');
        $this->assertDatabaseCount('invoices', 0);
        $data['initial_payment_amount'] = '0';
        $this->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $this->assertSame('draft', Invoice::firstOrFail()->status);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_overpayment_rolls_back_invoice_customer_payment_and_number(): void
    {
        $this->signIn();
        $data = $this->payload('200');
        $data['new_customer'] = '1';
        $data['new_customer_data'] = ['name' => 'Inline customer'];
        $this->post(route('invoices.store'), $data)->assertSessionHasErrors('initial_payment_amount');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('invoice_number_sequences', 0);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_payment_permission_method_and_date_are_required(): void
    {
        $this->signIn(false);
        $data = $this->payload('50');
        $this->post(route('invoices.store'), $data)->assertForbidden();
        $this->signIn();
        unset($data['initial_payment_date'], $data['initial_payment_method']);
        $this->post(route('invoices.store'), $data)->assertSessionHasErrors(['initial_payment_date', 'initial_payment_method']);
        $this->assertDatabaseCount('payments', 0);
    }
}
