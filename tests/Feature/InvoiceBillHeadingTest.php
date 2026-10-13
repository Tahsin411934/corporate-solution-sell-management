<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CompanySettings\Models\CompanySetting;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Models\Invoice;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceBillHeadingTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): void
    {
        $user = User::factory()->create();
        foreach (['invoices.create', 'invoices.view', 'invoices.print'] as $name) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        $this->actingAs($user);
    }

    private function payload(): array
    {
        return ['customer_id' => Customer::create(['customer_code' => uniqid(), 'name' => 'Client'])->id,
            'auto_invoice_number' => 1, 'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT',
            'discount_type' => 'fixed', 'discount_value' => 0, 'tax_rate' => 0,
            'items' => [['description' => 'Consulting', 'quantity' => 1, 'unit' => 'service', 'rate' => 3000, 'unit_cost' => 0]]];
    }

    public function test_company_defaults_are_saved_as_invoice_snapshot(): void
    {
        $this->signIn();
        $this->post(route('companysettings.store'), ['company_name' => 'Corporate Solution', 'invoice_prefix' => 'INV',
            'invoice_next_number' => 1, 'currency_code' => 'BDT', 'currency_symbol' => 'BDT',
            'bill_title' => 'CONSULTANCY BILL', 'bill_subtitle' => 'Business Advisory'])->assertSessionHasNoErrors();
        $company = CompanySetting::firstOrFail();
        $this->get(route('companysettings.index'))->assertOk()->assertSee('Default bill title');
        $this->get(route('invoices.create'))->assertOk()->assertSee('CONSULTANCY BILL')->assertSee('Business Advisory');
        $this->post(route('invoices.store'), $this->payload())->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('CONSULTANCY BILL', $invoice->bill_title);
        $this->assertSame('Business Advisory', $invoice->bill_subtitle);
        $this->put(route('companysettings.update', $company), ['bill_title' => 'NEW DEFAULT', 'bill_subtitle' => 'New subtitle'])->assertSessionHasNoErrors();
        foreach (['show', 'print'] as $page) {
            $this->get(route('invoices.'.$page, $invoice))->assertOk()->assertSee('CONSULTANCY BILL')->assertSee('Business Advisory')->assertDontSee('NEW DEFAULT');
        }
    }

    public function test_invoice_can_override_title_and_hide_subtitle(): void
    {
        $this->signIn();
        CompanySetting::create(['company_name' => 'Corporate Solution', 'bill_title' => 'DEFAULT', 'bill_subtitle' => 'Default subtitle']);
        $this->post(route('invoices.store'), $this->payload() + ['bill_title' => 'VAT & TAX BILL', 'bill_subtitle' => ''])->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('VAT & TAX BILL', $invoice->bill_title);
        $this->assertNull($invoice->bill_subtitle);
        $this->get(route('invoices.print', $invoice))->assertOk()->assertSee('VAT &amp; TAX BILL', false)->assertDontSee('Default subtitle');
    }

    public function test_heading_validation_and_legacy_defaults(): void
    {
        $this->signIn();
        $payload = $this->payload();
        $this->post(route('invoices.store'), $payload + ['bill_title' => ''])->assertSessionHasErrors('bill_title');
        $this->post(route('invoices.store'), $payload + ['bill_title' => str_repeat('x', 151), 'bill_subtitle' => str_repeat('x', 251)])
            ->assertSessionHasErrors(['bill_title', 'bill_subtitle']);
        $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->assertSame(Invoice::DEFAULT_BILL_TITLE, $invoice->bill_title);
        $this->assertSame(Invoice::DEFAULT_BILL_SUBTITLE, $invoice->bill_subtitle);
        $company = CompanySetting::create(['company_name' => 'Corporate Solution']);
        $this->put(route('companysettings.update', $company), ['bill_title' => '', 'bill_subtitle' => str_repeat('x', 251)])
            ->assertSessionHasErrors(['bill_title', 'bill_subtitle']);
        $this->put(route('companysettings.update', $company), ['bill_subtitle' => ''])->assertSessionHasNoErrors();
        $this->post(route('invoices.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertNull(Invoice::latest('id')->firstOrFail()->bill_subtitle);
    }
}
