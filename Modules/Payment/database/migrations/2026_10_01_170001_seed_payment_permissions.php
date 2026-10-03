<?php
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
return new class extends Migration {
    public function up(): void
    {
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $permission = Permission::firstOrCreate(['name' => 'payments.'.$action, 'guard_name' => 'web']);
            Role::where('name', 'admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void {}
};
