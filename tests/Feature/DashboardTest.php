<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class DashboardTest extends TestCase
{
    use RefreshDatabase;
    public function test_dashboard_requires_login(): void { $this->get(route('dashboard'))->assertRedirect(route('login')); }
    public function test_financial_data_is_hidden_without_permission(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertSee('Your workspace is ready')->assertDontSee('Invoice performance')->assertDontSee('Collections this month');
    }
    public function test_authorized_dashboard_renders_empty_states(): void
    {
        $user = User::factory()->create();
        foreach (['invoices.view', 'customers.view', 'payments.view', 'expenses.view'] as $name) $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Invoice performance')->assertSee('No overdue balances')->assertSee('Expenses this month');
    }
    public function test_outstanding_uses_active_payments_and_excludes_drafts(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'invoices.view', 'guard_name' => 'web']));
        $customer = \Modules\Customer\Models\Customer::create(['customer_code' => 'DASH-1', 'name' => 'Customer']);
        $payload = ['customer_id' => $customer->id, 'invoice_number' => 'DASH-1', 'invoice_date' => today()->subDays(2)->toDateString(), 'due_date' => today()->subDay()->toDateString(), 'currency_code' => 'BDT', 'discount_type' => 'fixed', 'discount_value' => '0', 'tax_rate' => '0', 'items' => [['description' => 'Service', 'quantity' => '1', 'unit' => 'item', 'rate' => '100', 'unit_cost' => '20']]];
        $service = app(\Modules\Invoice\Services\InvoiceService::class);
        $invoice = $service->create($payload, $user->id);
        $service->issue($invoice);
        $paymentService = app(\Modules\Payment\Services\PaymentService::class);
        $payment = $paymentService->create(['invoice_id' => $invoice->id, 'amount' => '30', 'payment_date' => today()->toDateString(), 'payment_method' => 'cash'], $user->id);
        $payload['invoice_number'] = 'DASH-DRAFT';
        $service->create($payload, $user->id);
        $data = app(\App\Services\DashboardService::class)->overview($user);
        $this->assertEquals(70, $data['invoiceMetrics']->first()->outstanding);
        $this->assertEquals(1, $data['invoiceMetrics']->first()->invoice_count);
        $this->assertCount(1, $data['overdueInvoices']);
        $paymentService->delete($payment);
        $this->assertEquals(100, app(\App\Services\DashboardService::class)->overview($user)['invoiceMetrics']->first()->outstanding);
    }
}
