<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Services\InvoiceNumberService;
use Modules\Invoice\Services\InvoiceService;
use Tests\TestCase;

class InvoiceNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_serials_are_allocated_on_save_and_preview_does_not_consume_them(): void
    {
        $user = User::factory()->create();
        $data = ['customer_id' => Customer::create(['customer_code' => 'NUM', 'name' => 'Client'])->id,
            'auto_invoice_number' => true, 'invoice_number' => 'untrusted-preview', 'invoice_date' => '2026-10-10', 'currency_code' => 'BDT',
            'items' => [['description' => 'Service', 'quantity' => '1', 'unit' => 'item', 'rate' => '100', 'unit_cost' => '0']]];
        $numbers = app(InvoiceNumberService::class);
        $this->assertSame('INV-20261010-0001', $numbers->preview('2026-10-10'));
        $this->assertSame('INV-20261010-0001', $numbers->preview('2026-10-10'));
        $service = app(InvoiceService::class);
        $first = $service->create($data, $user->id);
        $this->assertSame('INV-20261010-0001', $first->invoice_number);
        $first->delete();
        $this->assertSame('INV-20261010-0002', $service->create($data, $user->id)->invoice_number);
        $data['invoice_date'] = '2026-10-11';
        $this->assertSame('INV-20261011-0001', $service->create($data, $user->id)->invoice_number);
        unset($data['auto_invoice_number']);
        $data['invoice_number'] = 'INV-20261011-0002';
        $service->create($data, $user->id);
        $data['auto_invoice_number'] = true;
        $this->assertSame('INV-20261011-0003', $service->create($data, $user->id)->invoice_number);
    }

    public function test_number_reservation_rolls_back_with_transaction(): void
    {
        DB::beginTransaction();
        $this->assertSame('INV-20261010-0001', app(InvoiceNumberService::class)->next('2026-10-10'));
        DB::rollBack();
        $this->assertSame('INV-20261010-0001', app(InvoiceNumberService::class)->preview('2026-10-10'));
    }

    public function test_two_forms_with_the_same_preview_save_with_distinct_numbers(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'invoices.create', 'guard_name' => 'web']));
        $this->actingAs($user);
        $data = ['customer_id' => Customer::create(['customer_code' => 'HTTP-NUM', 'name' => 'Client'])->id,
            'auto_invoice_number' => '1', 'invoice_number' => 'INV-20261010-0001', 'invoice_date' => '2026-10-10',
            'currency_code' => 'BDT', 'discount_type' => 'fixed', 'discount_value' => '0', 'tax_rate' => '0',
            'items' => [['description' => 'Service', 'quantity' => '1', 'unit' => 'item', 'rate' => '100', 'unit_cost' => '0']]];
        $this->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-20261010-0001']);
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-20261010-0002']);
    }
}
