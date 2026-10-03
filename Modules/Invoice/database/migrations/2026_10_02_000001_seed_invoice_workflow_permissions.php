<?php
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
return new class extends Migration {
    public function up(): void
    {
        foreach (['issue', 'cancel', 'print'] as $action) {
            $permission = Permission::firstOrCreate(['name' => 'invoices.'.$action, 'guard_name' => 'web']);
            Role::where('name', 'admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        Permission::where('guard_name', 'web')->whereIn('name', ['invoices.issue', 'invoices.cancel', 'invoices.print'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
