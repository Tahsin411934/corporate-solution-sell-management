<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CompanySettings\Models\CompanySetting;
use Modules\Customer\Models\Customer;
use Modules\Invoice\Services\InvoiceService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CompanySignatoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_signatory_details_save_and_render_in_order_on_invoice(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('companysettings.store'), [
            'company_name' => 'Corporate Solution', 'invoice_prefix' => 'CS', 'invoice_next_number' => 1,
            'currency_code' => 'BDT', 'currency_symbol' => '৳',
            'authorized_person' => 'Md. Jaheed Hossain',
            'authorized_person_qualifications' => "ITP(NBR), CA (CC), CMA (PL)\nL.L.B; B.B.A; M.B.A",
            'authorized_person_designation' => 'C.E.O',
        ])->assertSessionHasNoErrors();
        $company = CompanySetting::firstOrFail();
        $this->assertSame("ITP(NBR), CA (CC), CMA (PL)\nL.L.B; B.B.A; M.B.A", $company->authorized_person_qualifications);
        $this->assertSame('C.E.O', $company->authorized_person_designation);
        $this->get(route('companysettings.index'))->assertOk()->assertSee('Qualifications')->assertSee('Designation');
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'invoices.print', 'guard_name' => 'web']));
        $invoice = app(InvoiceService::class)->create([
            'customer_id' => Customer::create(['customer_code' => 'TEST-SIGN', 'name' => 'Client'])->id,
            'invoice_number' => 'CS-1', 'invoice_date' => today()->toDateString(), 'currency_code' => 'BDT',
            'items' => [['description' => 'Tax return', 'quantity' => '1', 'unit' => 'service', 'rate' => '3000', 'unit_cost' => '0']],
        ], $user->id);
        $this->get(route('invoices.print', $invoice))->assertOk()->assertSeeInOrder([
            'Sincerely yours,', 'Md. Jaheed Hossain', 'ITP(NBR), CA (CC), CMA (PL)', 'L.L.B; B.B.A; M.B.A', 'C.E.O', 'Corporate Solution',
        ]);
    }

    public function test_signatory_details_can_be_updated_cleared_and_validated(): void
    {
        $company = CompanySetting::create(['company_name' => 'Corporate Solution', 'authorized_person' => 'Original name']);
        $this->actingAs(User::factory()->create());
        $this->put(route('companysettings.update', $company), [
            'authorized_person_qualifications' => 'MBA', 'authorized_person_designation' => 'Director',
        ])->assertSessionHasNoErrors();
        $this->assertSame('MBA', $company->fresh()->authorized_person_qualifications);
        $this->assertSame('Director', $company->fresh()->authorized_person_designation);
        $this->assertSame('Original name', $company->fresh()->authorized_person);
        $this->put(route('companysettings.update', $company), [
            'authorized_person_qualifications' => str_repeat('x', 1001), 'authorized_person_designation' => str_repeat('x', 151),
        ])->assertSessionHasErrors(['authorized_person_qualifications', 'authorized_person_designation']);
        $this->put(route('companysettings.update', $company), [
            'authorized_person_qualifications' => '', 'authorized_person_designation' => '',
        ])->assertSessionHasNoErrors();
        $this->assertNull($company->fresh()->authorized_person_qualifications);
        $this->assertNull($company->fresh()->authorized_person_designation);
    }
}
