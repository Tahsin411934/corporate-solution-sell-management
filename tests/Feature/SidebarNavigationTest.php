<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;
    public function test_empty_permission_groups_are_hidden(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()
            ->assertSee('sidebar-settings', false)->assertDontSee('sidebar-billing', false)
            ->assertDontSee('sidebar-administration', false)->assertDontSee('sidebar-finance', false);
    }
    public function test_active_report_group_is_expanded_and_link_marked_current(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']));
        $this->actingAs($user)->get(route('reports.index'))->assertOk()
            ->assertSee('aria-controls="sidebar-finance" aria-expanded="true"', false)
            ->assertSee('aria-current="page"', false)->assertDontSee('sidebar-billing', false);
    }
}
