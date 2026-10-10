<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Services\InvoiceService;
use Modules\Payment\Services\PaymentService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(): User
    {
        $user = User::factory()->create();
        foreach (['view', 'create', 'issue', 'cancel', 'print'] as $action) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => 'invoices.'.$action, 'guard_name' => 'web']));
        }
        return $user;
    }

    private function payload(): array
    {
        return [
            'customer_id' => Customer::create(['customer_code' => 'TEST-'.uniqid(), 'name' => 'Test customer'])->id,
            'invoice_number' => 'INV-'.uniqid(), 'invoice_date' => today()->toDateString(),
            'currency_code' => 'BDT', 'discount_type' => 'percentage', 'discount_value' => '10', 'tax_rate' => '5',
            'items' => [['description' => 'Consulting', 'quantity' => '2', 'unit' => 'item', 'rate' => '100', 'unit_cost' => '40']],
        ];
    }

    public function test_create_saves_items_and_ignores_client_totals(): void
    {
        $this->actingAs($this->userWithPermissions());
        $this->get(route('invoices.create'))->assertOk()->assertSee('Customer & invoice details', false)
            ->assertSee('invoice-summary', false)->assertSee('Duplicate')->assertSee('Save draft invoice');
        $this->post(route('invoices.store'), $this->payload() + ['total_amount' => '1', 'status' => 'paid'])->assertSessionHasNoErrors()->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('189.00', $invoice->total_amount);
        $this->assertSame('100.00', $invoice->total_profit);
        $this->assertSame('draft', $invoice->status);
        $this->assertCount(1, $invoice->items);
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('Consulting');
        $this->get(route('invoices.print', $invoice))->assertOk()->assertSee('Due:')->assertDontSee('Outstanding balance');
        $this->get(route('invoices.index'))->assertOk();
    }

    public function test_issue_cancel_and_invalid_transitions(): void
    {
        $user = $this->userWithPermissions();
        $invoice = app(InvoiceService::class)->create($this->payload(), $user->id);
        $this->actingAs($user)->post(route('invoices.issue', $invoice))->assertSessionHasNoErrors();
        $this->assertSame('issued', $invoice->fresh()->status);
        $this->post(route('invoices.issue', $invoice))->assertSessionHasErrors('invoice');
        $this->post(route('invoices.cancel', $invoice), [])->assertSessionHasErrors('cancellation_reason');
        $this->post(route('invoices.cancel', $invoice), ['cancellation_reason' => 'Customer request'])->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $invoice->fresh()->status);
        $this->assertSame($user->id, $invoice->fresh()->cancelled_by);
        $this->post(route('invoices.issue', $invoice))->assertSessionHasErrors('invoice');
    }

    public function test_payments_block_cancellation_and_deleted_payments_do_not_count(): void
    {
        $user = $this->userWithPermissions();
        $invoice = app(InvoiceService::class)->create($this->payload(), $user->id);
        app(InvoiceService::class)->issue($invoice);
        $payment = app(PaymentService::class)->create(['invoice_id' => $invoice->id, 'amount' => '50', 'payment_date' => today()->toDateString(), 'payment_method' => 'cash'], $user->id);
        $this->actingAs($user)->post(route('invoices.cancel', $invoice), ['cancellation_reason' => 'Test'])->assertSessionHasErrors('invoice');
        $this->getJson(route('invoices.data', ['draw' => 1]))->assertOk()->assertJsonPath('data.0.received_amount', 50)->assertJsonPath('data.0.balance_amount', 139);
        app(PaymentService::class)->delete($payment);
        $details = app(InvoiceService::class)->details($invoice->fresh());
        $this->assertEquals(0, $details->received_amount);
    }

    public function test_permissions_protect_all_endpoints(): void
    {
        $owner = $this->userWithPermissions();
        $invoice = app(InvoiceService::class)->create($this->payload(), $owner->id);
        $this->actingAs(User::factory()->create());
        foreach (['index', 'data', 'create', 'show', 'print'] as $action) {
            $this->get(route('invoices.'.$action, in_array($action, ['show', 'print']) ? $invoice : []))->assertForbidden();
        }
        $this->post(route('invoices.store'), [])->assertForbidden();
        $this->post(route('invoices.issue', $invoice))->assertForbidden();
        $this->post(route('invoices.cancel', $invoice), ['cancellation_reason' => 'Test'])->assertForbidden();
    }

    public function test_failed_item_calculation_rolls_back_invoice_and_items(): void
    {
        $user = $this->userWithPermissions();
        $payload = $this->payload();
        $payload['items'][] = ['description' => 'Overflow', 'quantity' => '999999999.999', 'unit' => 'item', 'rate' => '999999999999.99', 'unit_cost' => '0'];
        $this->actingAs($user)->post(route('invoices.store'), $payload)->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('invoice_items', 0);
    }

    public function test_datatable_search_sort_and_pagination(): void
    {
        $user = $this->userWithPermissions();
        $first = $this->payload();
        $first['invoice_number'] = 'INV-ALPHA';
        $second = $this->payload();
        $second['invoice_number'] = 'INV-BETA';
        $second['items'][0]['rate'] = '200';
        app(InvoiceService::class)->create($first, $user->id);
        app(InvoiceService::class)->create($second, $user->id);
        $parameters = ['draw' => 1, 'start' => 0, 'length' => 1,
            'columns' => [
                ['data' => 'invoice_number', 'name' => 'invoices.invoice_number', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'balance_amount', 'name' => 'balance_amount', 'searchable' => 'true', 'orderable' => 'true'],
            ], 'order' => [['column' => 1, 'dir' => 'desc']]];
        $this->actingAs($user)->getJson(route('invoices.data', $parameters))->assertOk()
            ->assertJsonPath('recordsTotal', 2)->assertJsonCount(1, 'data')->assertJsonPath('data.0.invoice_number', 'INV-BETA');
        $parameters['search'] = ['value' => 'ALPHA', 'regex' => 'false'];
        $this->getJson(route('invoices.data', $parameters))->assertOk()
            ->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.invoice_number', 'INV-ALPHA');
        $parameters['search']['value'] = '189';
        $this->getJson(route('invoices.data', $parameters))->assertOk()->assertJsonPath('recordsFiltered', 1);
    }

    public function test_quick_customer_create_returns_selection_data_and_validates(): void
    {
        $user = $this->userWithPermissions();
        $this->actingAs($user)->postJson(route('customers.store'), ['customer_code' => 'QUICK-1', 'name' => 'Quick customer'])->assertForbidden();
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'customers.create', 'guard_name' => 'web']));
        $this->get(route('invoices.create'))->assertOk()->assertSee('new-customer-toggle', false)->assertSee('new-customer-details', false)->assertDontSee('quick-customer-drawer', false);
        $this->postJson(route('customers.store'), ['customer_code' => 'QUICK-1', 'name' => 'Quick customer'])->assertCreated()
            ->assertJsonPath('data.name', 'Quick customer')->assertJsonPath('data.customer_code', 'CS-00001')->assertJsonStructure(['data' => ['id']]);
        $this->postJson(route('customers.store'), ['name' => ''])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('customers', 1);
        $this->getJson(route('customers.next-code'))->assertOk()->assertJsonPath('customer_code', 'CS-00002');
        Customer::firstOrFail()->delete();
        $this->postJson(route('customers.store'), ['customer_code' => 'CS-00001', 'name' => 'Next customer'])->assertCreated()->assertJsonPath('data.customer_code', 'CS-00002');
    }
}
