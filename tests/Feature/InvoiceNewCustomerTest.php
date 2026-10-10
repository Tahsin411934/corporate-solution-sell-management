<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Models\Invoice;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceNewCustomerTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(bool $canCreateCustomer = true): void
    {
        $user = User::factory()->create();
        foreach ($canCreateCustomer ? ['invoices.create', 'invoices.view', 'customers.create'] : ['invoices.create', 'invoices.view'] as $name) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        $this->actingAs($user);
    }

    private function payload(): array
    {
        return ['new_customer' => '1', 'new_customer_data' => ['name' => 'New Client', 'bin_number' => 'BIN-001', 'tin_number' => 'TIN-001', 'address' => 'Chattogram'],
            'invoice_number' => 'INV-NEW', 'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT',
            'discount_type' => 'fixed', 'discount_value' => '0', 'tax_rate' => '0',
            'items' => [['description' => 'Consulting', 'quantity' => '1', 'unit' => 'service', 'rate' => '100', 'unit_cost' => '0']]];
    }

    public function test_invoice_creates_customer_with_tax_details_and_generated_code(): void
    {
        $this->signIn();
        $this->get(route('invoices.create'))->assertOk()->assertSeeInOrder(['Invoice number', 'New customer', 'Customer name', 'BIN', 'TIN', 'Address']);
        $this->post(route('invoices.store'), $this->payload())->assertSessionHasNoErrors();
        $customer = Customer::firstOrFail();
        $this->assertSame('CS-00001', $customer->customer_code);
        $this->assertSame('BIN-001', $customer->bin_number);
        $this->assertSame('TIN-001', $customer->tin_number);
        $this->assertSame('Chattogram', $customer->address);
        $this->assertSame($customer->id, Invoice::firstOrFail()->customer_id);
    }

    public function test_existing_customer_mode_ignores_new_customer_fields(): void
    {
        $this->signIn(false);
        $customer = Customer::create(['customer_code' => 'EXISTING', 'name' => 'Existing Client']);
        $payload = $this->payload();
        unset($payload['new_customer']);
        $payload['customer_id'] = $customer->id;
        $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('customers', 1);
        $this->assertSame($customer->id, Invoice::firstOrFail()->customer_id);
    }

    public function test_new_customer_requires_permission_and_a_name(): void
    {
        $this->signIn(false);
        $this->post(route('invoices.store'), $this->payload())->assertForbidden();
        $this->signIn();
        $payload = $this->payload();
        $payload['new_customer_data']['name'] = '';
        $this->post(route('invoices.store'), $payload)->assertSessionHasErrors('new_customer_data.name');
        $this->postJson(route('invoices.store'), $payload)->assertUnprocessable()
            ->assertJsonValidationErrors('new_customer_data.name')->assertSee('Please enter the customer name.');
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_failed_invoice_rolls_back_new_customer_and_code_sequence(): void
    {
        $this->signIn();
        $payload = $this->payload();
        $payload['items'][0]['quantity'] = '999999999.999';
        $payload['items'][0]['rate'] = '999999999999.99';
        $this->post(route('invoices.store'), $payload)->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseHas('customer_code_sequences', ['id' => 1, 'next_number' => 1]);
    }
}
