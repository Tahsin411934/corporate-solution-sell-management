<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\Customer;
use Modules\Expense\Models\Expense;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Services\InvoiceService;
use Modules\Report\Services\ReportService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InvoiceExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(bool $issued = true): Invoice
    {
        $user = User::factory()->create();
        foreach (['invoices.view', 'invoices.cancel', 'expenses.create'] as $name) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        $this->actingAs($user);
        $invoice = app(InvoiceService::class)->create([
            'customer_id' => Customer::create(['customer_code' => 'EXP-1', 'name' => 'Expense customer'])->id,
            'invoice_number' => 'INV-EXP-1', 'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT',
            'save_mode' => $issued ? 'issue' : 'draft',
            'items' => [['description' => 'Consulting', 'quantity' => '2', 'unit' => 'service', 'rate' => '1500', 'unit_cost' => '500']],
        ], $user->id);
        return $invoice;
    }

    private function expense(string $amount = '400'): array
    {
        return ['expense_date' => today()->toDateString(), 'category' => 'Service cost',
            'amount' => $amount, 'payment_method' => 'bank', 'description' => 'Actual service cost'];
    }

    public function test_cost_is_a_prefilled_estimate_and_actual_expense_does_not_double_reduce_profit(): void
    {
        $invoice = $this->invoice();
        $this->assertDatabaseCount('expenses', 0);
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('Record expense')
            ->assertViewHas('suggestedExpense', '1000.00');
        $this->post(route('invoices.expenses.store', $invoice), $this->expense())
            ->assertSessionHasNoErrors()->assertRedirect(route('invoices.show', $invoice));
        $expense = Expense::firstOrFail();
        $this->assertSame($invoice->id, $expense->invoice_id);
        $this->assertSame(auth()->id(), $expense->created_by);
        $this->assertSame('400.00', $expense->amount);
        $this->get(route('invoices.show', $invoice))->assertOk()
            ->assertViewHas('actualExpense', '400.00')->assertViewHas('suggestedExpense', '600.00')
            ->assertSee('Actual service cost');
        $this->assertSame('1000.00', $invoice->fresh()->total_cost);
        $this->assertSame('2000.00', $invoice->fresh()->total_profit);
        $this->assertEquals(2000, app(ReportService::class)->query('profit', [])->first()->total_profit);
        $this->post(route('invoices.expenses.store', $invoice), $this->expense('700'))->assertSessionHasNoErrors();
        $this->get(route('invoices.show', $invoice))->assertViewHas('actualExpense', '1100.00')->assertViewHas('suggestedExpense', '');
        $this->assertSame('2000.00', $invoice->fresh()->total_profit);
        $expense->delete();
        $this->get(route('invoices.show', $invoice))->assertViewHas('actualExpense', '700.00')->assertViewHas('suggestedExpense', '300.00');
    }

    public function test_drafts_cancelled_invoices_and_invalid_expenses_are_rejected(): void
    {
        $invoice = $this->invoice(false);
        $this->get(route('invoices.show', $invoice))->assertOk()->assertDontSee('Record expense');
        $this->post(route('invoices.expenses.store', $invoice), $this->expense())->assertSessionHasErrors('invoice');
        app(InvoiceService::class)->issue($invoice);
        $invalid = $this->expense('0');
        $invalid['expense_date'] = 'invalid';
        $invalid['payment_method'] = 'invalid';
        $this->post(route('invoices.expenses.store', $invoice), $invalid)
            ->assertSessionHasErrors(['amount', 'expense_date', 'payment_method']);
        $this->assertDatabaseCount('expenses', 0);
        $this->post(route('invoices.expenses.store', $invoice), $this->expense())->assertSessionHasNoErrors();
        $this->post(route('invoices.cancel', $invoice), ['cancellation_reason' => 'Customer cancelled'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('expenses', 1);
        $this->post(route('invoices.expenses.store', $invoice), $this->expense())->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('expenses', 1);
    }

    public function test_expense_permission_is_required_and_posted_invoice_cannot_change_link(): void
    {
        $invoice = $this->invoice();
        $this->post(route('invoices.expenses.store', $invoice), $this->expense() + ['invoice_id' => null])->assertSessionHasNoErrors();
        $this->assertSame($invoice->id, Expense::firstOrFail()->invoice_id);
        auth()->user()->revokePermissionTo('expenses.create');
        $this->get(route('invoices.show', $invoice))->assertOk()->assertDontSee('Record expense');
        $this->post(route('invoices.expenses.store', $invoice), $this->expense())->assertForbidden();
        $this->assertDatabaseCount('expenses', 1);
    }
}
