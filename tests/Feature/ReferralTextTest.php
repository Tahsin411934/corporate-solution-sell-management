<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\Customer\Models\Customer;
use Modules\Expense\Models\Expense;
use Modules\Invoice\Models\Invoice;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReferralTextTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): void
    {
        $user = User::factory()->create();
        foreach (['invoices.create', 'invoices.view', 'expenses.view', 'expenses.create', 'expenses.update'] as $name) {
            $user->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        }
        $this->actingAs($user);
    }

    private function invoicePayload(): array
    {
        return [
            'customer_id' => Customer::create(['customer_code' => 'TEXT-REF', 'name' => 'Customer'])->id,
            'invoice_number' => 'INV-TEXT', 'invoice_date' => today()->toDateString(),
            'currency_code' => 'BDT', 'discount_type' => 'fixed', 'discount_value' => '0', 'tax_rate' => '0',
            'referral_source' => 'A name typed without creating a referral',
            'items' => [['description' => 'Consulting', 'quantity' => '1', 'unit' => 'service', 'rate' => '100', 'unit_cost' => '0']],
        ];
    }

    public function test_invoice_accepts_free_text_and_referral_management_is_removed(): void
    {
        $this->signIn();
        $this->get(route('invoices.create'))->assertOk()->assertSee('name="referral_source"', false)->assertDontSee('name="referral_source_id"', false);
        $this->post(route('invoices.store'), $this->invoicePayload())->assertSessionHasNoErrors();
        $this->assertSame('A name typed without creating a referral', Invoice::firstOrFail()->referral_source);
        $this->get(route('invoices.show', Invoice::firstOrFail()))->assertOk()->assertSee('A name typed without creating a referral');
        $this->assertFalse(Schema::hasTable('referral_sources'));
        $this->assertFalse(Schema::hasColumn('invoices', 'referral_source_id'));
        $this->assertFalse(Schema::hasColumn('expenses', 'referral_source_id'));
        $this->assertFalse(Route::has('referral-sources.index'));
        $this->assertFalse(Permission::where('name', 'like', 'referral-sources.%')->exists());
    }

    public function test_referral_text_is_validated_and_expenses_can_edit_and_clear_it(): void
    {
        $this->signIn();
        $payload = $this->invoicePayload();
        $payload['referral_source'] = str_repeat('x', 201);
        $this->post(route('invoices.store'), $payload)->assertSessionHasErrors('referral_source');
        $payload['referral_source'] = '';
        $this->post(route('invoices.store'), $payload)->assertSessionHasNoErrors();
        $this->assertNull(Invoice::firstOrFail()->referral_source);
        $expense = ['expense_date' => today()->toDateString(), 'category' => 'Referral fee', 'amount' => '10', 'payment_method' => 'cash', 'referral_source' => 'Typed partner'];
        $this->get(route('expenses.index'))->assertOk()->assertSee('name="referral_source"', false);
        $this->postJson(route('expenses.store'), $expense)->assertOk();
        $record = Expense::firstOrFail();
        $this->assertSame('Typed partner', $record->referral_source);
        $this->getJson(route('expenses.data', ['draw' => 1]))->assertOk()->assertJsonPath('data.0.referral_source', 'Typed partner');
        $expense['referral_source'] = '';
        $this->putJson(route('expenses.update', $record), $expense)->assertOk();
        $this->assertNull($record->fresh()->referral_source);
    }

    public function test_upgrade_preserves_legacy_names_including_soft_deleted_records(): void
    {
        $this->signIn();
        $this->post(route('invoices.store'), $this->invoicePayload())->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $expense = Expense::create(['expense_date' => today(), 'category' => 'Referral fee', 'amount' => '10', 'payment_method' => 'cash']);
        $migration = require database_path('migrations/2026_10_10_000002_replace_referral_sources_with_text.php');
        $migration->down();
        $id = DB::table('referral_sources')->insertGetId(['name' => 'Legacy partner', 'deleted_at' => now()]);
        DB::table('invoices')->where('id', $invoice->id)->update(['referral_source_id' => $id, 'deleted_at' => now()]);
        DB::table('expenses')->where('id', $expense->id)->update(['referral_source_id' => $id]);
        $migration->up();
        $this->assertSame('Legacy partner', DB::table('invoices')->where('id', $invoice->id)->value('referral_source'));
        $this->assertSame('Legacy partner', $expense->fresh()->referral_source);
        $this->assertFalse(Schema::hasTable('referral_sources'));
        DB::table('v_invoice_balances')->get();
        DB::table('v_customer_balances')->get();
    }
}
