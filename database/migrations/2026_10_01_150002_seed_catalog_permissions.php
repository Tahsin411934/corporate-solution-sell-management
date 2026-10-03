<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['services', 'referral-sources'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $permission = Permission::firstOrCreate(['name' => $module.'.'.$action, 'guard_name' => 'web']);
                Role::where('name', 'admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        // Permissions may be assigned to roles after deployment; preserve them.
    }
};
