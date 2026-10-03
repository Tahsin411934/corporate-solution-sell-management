<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Services\InvoiceService;
use Modules\Payment\Services\PaymentService;
use Modules\Expense\Models\Expense;
use Modules\Report\Services\ReportService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class ReportTest extends TestCase
{
    use RefreshDatabase;
    private function setupReports(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']));
        $this->actingAs($user);
        return $user;
    }
    private function invoice(User $user, string $currency = 'BDT')
    {
        $customer = Customer::create(['customer_code' => uniqid(), 'name' => 'Report customer', 'opening_balance' => '10']);
        return app(InvoiceService::class)->create(['customer_id' => $customer->id, 'invoice_number' => 'R-'.uniqid(), 'invoice_date' => '2026-10-01',
            'currency_code' => $currency, 'discount_type' => 'fixed', 'discount_value' => '0', 'tax_rate' => '0',
            'items' => [['description' => 'Service', 'quantity' => '1', 'unit' => 'item', 'rate' => '100', 'unit_cost' => '40']]], $user->id);
    }
    public function test_all_report_pages_and_endpoints_render(): void
    {
        $this->setupReports();
        foreach (array_keys(ReportService::TYPES) as $type) {
            $this->get(route('reports.index', ['report' => $type]))->assertOk();
            $this->getJson(route('reports.data', ['report' => $type, 'draw' => 1]))->assertOk()->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
        }
    }
    public function test_balances_exclude_deleted_payments(): void
    {
        $user = $this->setupReports();
        $invoice = $this->invoice($user);
        app(InvoiceService::class)->issue($invoice);
        $payment = app(PaymentService::class)->create(['invoice_id' => $invoice->id, 'amount' => '30', 'payment_date' => '2026-10-02', 'payment_method' => 'cash'], $user->id);
        $service = app(ReportService::class);
        $this->assertEquals(70, $service->query('invoices', [])->first()->balance_amount);
        $this->assertEquals(80, $service->query('customers', [])->first()->outstanding_balance);
        app(PaymentService::class)->delete($payment);
        $this->assertEquals(100, $service->query('invoices', [])->first()->balance_amount);
        $this->assertEquals(110, $service->query('customers', [])->first()->outstanding_balance);
        $this->assertSame(0, $service->query('payments', [])->count());
    }
    public function test_profit_excludes_drafts_and_cancellations_and_groups_currency(): void
    {
        $user = $this->setupReports();
        $service = app(InvoiceService::class);
        $service->issue($this->invoice($user));
        $service->issue($this->invoice($user, 'USD'));
        $this->invoice($user);
        $cancelled = $this->invoice($user);
        $service->cancel($cancelled, 'Test', $user->id);
        $summary = app(ReportService::class)->summary('profit', []);
        $this->assertCount(2, $summary);
        foreach ($summary as $group) $this->assertEquals(60, $group['Gross profit (excluding tax)']);
        $this->assertSame(0, app(ReportService::class)->query('profit', ['from' => '2026-10-02'])->count());
    }
    public function test_expense_filters_and_soft_deletes(): void
    {
        $this->setupReports();
        $expense = Expense::create(['expense_date' => '2026-10-01', 'category' => 'Office', 'amount' => '25', 'payment_method' => 'cash']);
        $service = app(ReportService::class);
        $this->assertEquals(25, $service->summary('expenses', [])[0]['Expenses']);
        $this->assertSame(0, $service->query('expenses', ['from' => '2026-10-02'])->count());
        $expense->delete();
        $this->assertSame(0, $service->query('expenses', [])->count());
    }
    public function test_access_and_filter_validation(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('reports.index'))->assertForbidden();
        $this->getJson(route('reports.data'))->assertForbidden();
        $this->setupReports();
        $this->getJson(route('reports.data', ['report' => 'invalid']))->assertUnprocessable();
        $this->getJson(route('reports.data', ['from' => '2026-10-02', 'to' => '2026-10-01']))->assertUnprocessable();
        $this->getJson(route('reports.data', ['to' => '2026-10-02']))->assertOk();
    }
}
