<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\Customer;
use Modules\Expense\Models\Expense;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Services\InvoiceService;
use Modules\Payment\Services\PaymentService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceEditTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(bool $issued = false): Invoice
    {
        $user = User::factory()->create();
        foreach (['invoices.view', 'invoices.update'] as $name) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        $this->actingAs($user);
        return app(InvoiceService::class)->create([
            'customer_id' => Customer::create(['customer_code' => uniqid(), 'name' => 'Edit client'])->id,
            'invoice_number' => 'INV-EDIT', 'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT',
            'save_mode' => $issued ? 'issue' : 'draft', 'bill_title' => 'Original heading', 'bill_subtitle' => 'Original subtitle',
            'items' => [['description' => 'Original item', 'quantity' => 1, 'unit' => 'service', 'rate' => 100, 'unit_cost' => 20]],
        ], $user->id);
    }

    private function payload(Invoice $invoice, string $rate = '150'): array
    {
        return ['customer_id' => $invoice->customer_id, 'invoice_number' => $invoice->invoice_number,
            'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT', 'discount_type' => 'fixed',
            'discount_value' => 0, 'tax_rate' => 0, 'bill_title' => 'Updated heading', 'bill_subtitle' => '',
            'items' => [['description' => 'Updated item', 'quantity' => 1, 'unit' => 'service', 'rate' => $rate, 'unit_cost' => 30]]];
    }

    public function test_edit_prefills_invoice_and_recalculates_draft_without_new_invoice_or_payment(): void
    {
        $invoice = $this->invoice();
        $this->get(route('invoices.show', $invoice))->assertSee('Edit invoice');
        $this->get(route('invoices.edit', $invoice))->assertOk()->assertSee('Original heading')->assertSee('Original subtitle')
            ->assertSee('Original item')->assertSee('Update invoice')->assertDontSee('id="save-draft"', false)->assertDontSee('id="paid-now"', false);
        $this->put(route('invoices.update', $invoice), $this->payload($invoice) + ['initial_payment_amount' => 50, 'save_mode' => 'issue'])
            ->assertSessionHasNoErrors()->assertRedirect(route('invoices.show', $invoice));
        $invoice->refresh();
        $this->assertSame('draft', $invoice->status);
        $this->assertSame('150.00', $invoice->total_amount);
        $this->assertSame('120.00', $invoice->total_profit);
        $this->assertSame('Updated heading', $invoice->bill_title);
        $this->assertNull($invoice->bill_subtitle);
        $this->assertSame('Updated item', $invoice->items->sole()->description);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_paid_invoice_edits_preserve_payments_expenses_and_recalculate_status(): void
    {
        $invoice = $this->invoice(true);
        $payment = app(PaymentService::class)->create(['invoice_id' => $invoice->id, 'amount' => 100,
            'payment_date' => today()->toDateString(), 'payment_method' => 'cash']);
        $expense = Expense::create(['invoice_id' => $invoice->id, 'expense_date' => today()->toDateString(),
            'category' => 'Service', 'amount' => 20, 'payment_method' => 'cash']);
        $this->put(route('invoices.update', $invoice), $this->payload($invoice))->assertSessionHasNoErrors();
        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertSame('100.00', $payment->fresh()->amount);
        $this->assertSame('20.00', $expense->fresh()->amount);
        $this->put(route('invoices.update', $invoice), $this->payload($invoice, '100'))->assertSessionHasNoErrors();
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->put(route('invoices.update', $invoice), $this->payload($invoice, '90'))->assertSessionHasErrors('items');
        $this->assertSame('100.00', $invoice->fresh()->total_amount);
        $this->assertSame('100.00', $invoice->fresh()->items->sole()->rate);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('expenses', 1);
    }

    public function test_customer_and_currency_cannot_change_when_financial_records_exist(): void
    {
        $invoice = $this->invoice(true);
        app(PaymentService::class)->create(['invoice_id' => $invoice->id, 'amount' => 10,
            'payment_date' => today()->toDateString(), 'payment_method' => 'cash']);
        $other = Customer::create(['customer_code' => uniqid(), 'name' => 'Other']);
        $data = $this->payload($invoice);
        $data['customer_id'] = $other->id;
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasErrors('customer_id');
        $data['customer_id'] = $invoice->customer_id;
        $data['currency_code'] = 'USD';
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasErrors('customer_id');
        $this->assertSame('BDT', $invoice->fresh()->currency_code);
    }

    public function test_permission_cancelled_invoice_and_validation_are_enforced(): void
    {
        $invoice = $this->invoice();
        $this->put(route('invoices.update', $invoice), [])->assertSessionHasErrors(['customer_id', 'items', 'bill_title']);
        $invoice->forceFill(['status' => 'cancelled'])->save();
        $this->get(route('invoices.edit', $invoice))->assertUnprocessable();
        $this->put(route('invoices.update', $invoice), $this->payload($invoice))->assertSessionHasErrors('invoice');
        $this->actingAs(User::factory()->create());
        $this->get(route('invoices.edit', $invoice))->assertForbidden();
        $this->put(route('invoices.update', $invoice), $this->payload($invoice))->assertForbidden();
    }
}
