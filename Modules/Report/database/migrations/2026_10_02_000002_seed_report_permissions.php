<?php
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
return new class extends Migration {
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']);
        Role::where('name', 'admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        Permission::where('name', 'reports.view')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
