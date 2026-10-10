<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        foreach ([
            'customers.view', 'customers.create', 'customers.update',
            'customers.delete', 'customers.restore',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'services.view', 'services.create', 'services.update', 'services.delete',
            'payments.view', 'payments.create', 'payments.update', 'payments.delete',
            'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete',
            'audit-logs.view',
            'reports.view',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'invoices.issue', 'invoices.cancel', 'invoices.print',
        ] as $permission) {
            $adminRole->givePermissionTo(Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@corporatesolution.com'],
            [
                'name' => 'Corporate Solution Admin',
                'password' => Hash::make('ChangeMe123!'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole($adminRole);
    }
}
