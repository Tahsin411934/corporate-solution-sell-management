<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccessManagementController extends Controller
{
    public function permissions()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        return view('access.permissions', ['permissions' => Permission::where('guard_name', 'web')->orderBy('name')->get()]);
    }
    public function permissionsData()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        return \Yajra\DataTables\Facades\DataTables::of(Permission::where('guard_name', 'web'))
            ->addColumn('actions', function (Permission $record) {
                return view('access.permission-actions', compact('record'))->render();
            })->rawColumns(['actions'])->make(true);
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_if(in_array($permission->name, $this->protectedPermissions()), 422, 'Application permissions cannot be renamed.');
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*\.[a-z][a-z0-9_-]*$/', Rule::unique('permissions')->where('guard_name', 'web')->ignore($permission->id)]]);
        $permission->update($data);
        return response()->json(['status' => 'success']);
    }

    public function deletePermission(Permission $permission)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_if(in_array($permission->name, $this->protectedPermissions()), 422, 'Application permissions cannot be deleted.');
        $permission->delete();
        return response()->json(['status' => 'success']);
    }

    private function protectedPermissions(): array
    {
        return ['customers.view', 'customers.create', 'customers.update', 'customers.delete', 'customers.restore', 'users.view', 'users.create', 'users.update', 'users.delete', 'roles.view', 'roles.create', 'roles.update', 'roles.delete'];
    }
    public function createPermission(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*\.[a-z][a-z0-9_-]*$/', Rule::unique('permissions')->where('guard_name', 'web')],
        ]);
        DB::transaction(function () use ($data) {
            $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
            Role::findByName('admin', 'web')->givePermissionTo($permission);
        });
        return $request->expectsJson() ? response()->json(['status' => 'success']) : redirect()->route('permissions.index')->with('success', 'Permission created.');
    }
    public function usersData()
    {
        return \Yajra\DataTables\Facades\DataTables::of(User::with('roles'))
            ->addColumn('role_names', fn (User $user) => $user->getRoleNames()->join(', ') ?: 'Unassigned')
            ->addColumn('actions', fn (User $account) => view('access.actions', ['entity' => 'user', 'record' => $account])->render())
            ->rawColumns(['actions'])->make(true);
    }

    public function rolesData()
    {
        return \Yajra\DataTables\Facades\DataTables::of(Role::where('guard_name', 'web')->with('permissions')->withCount('users'))
            ->addColumn('permission_names', fn (Role $role) => $role->permissions->pluck('name')->join(', '))
            ->addColumn('actions', fn (Role $record) => view('access.actions', ['entity' => 'role', 'record' => $record])->render())
            ->rawColumns(['actions'])->make(true);
    }
    public function users()
    {
        return view('access.users', ['users' => User::with('roles')->paginate(20), 'roles' => Role::where('guard_name', 'web')->get()]);
    }

    public function saveUser(Request $request, ?User $user = null)
    {
        abort_if($user?->hasRole('admin') && !$request->user()->hasRole('admin'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);
        if ($user?->hasRole('admin') && !in_array('admin', $data['roles'] ?? [])) {
            abort(422, 'Admin role cannot be removed here.');
        }
        foreach ($data['roles'] ?? [] as $name) {
            $selectedRole = Role::findByName($name, 'web');
            abort_if(!$request->user()->hasRole('admin') && ($name === 'admin' || $selectedRole->permissions->contains(fn ($permission) => !$request->user()->can($permission->name))), 403);
        }
        DB::transaction(function () use ($user, $data) {
            $model = $user ?? new User;
            $model->fill(collect($data)->except(['roles', 'password'])->all());
            if (!empty($data['password'])) $model->password = $data['password'];
            $model->save();
            $model->syncRoles($data['roles'] ?? []);
        });
        return $request->expectsJson() ? response()->json(['status' => 'success', 'message' => 'User saved successfully.']) : redirect()->route('users.index')->with('success', 'User saved successfully.');
    }

    public function deleteUser(User $user)
    {
        abort_if($user->id === auth()->id() || $user->hasRole('admin'), 422, 'This account cannot be deleted here.');
        $user->delete();
        return request()->expectsJson() ? response()->json(['status' => 'success', 'message' => 'User deleted.']) : back()->with('success', 'User deleted.');
    }

    public function roles()
    {
        return view('access.roles', ['roles' => Role::with('permissions')->where('guard_name', 'web')->get(), 'permissions' => Permission::where('guard_name', 'web')->orderBy('name')->get()]);
    }

    public function saveRole(Request $request, ?Role $role = null)
    {
        abort_if($role?->name === 'admin' && !$request->user()->hasRole('admin'), 403);
        abort_if($role && $role->permissions->contains(fn ($permission) => !$request->user()->can($permission->name)), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->where('guard_name', 'web')->ignore($role?->id)],
            'permissions' => ['array'],
            'permissions.*' => [Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);
        abort_if(($role?->name === 'admin' && $data['name'] !== 'admin') || ($role?->name !== 'admin' && $data['name'] === 'admin'), 422, 'The admin role name is protected.');
        if ($role?->name === 'admin') {
            foreach (['users.view', 'users.update', 'roles.view', 'roles.create', 'roles.update'] as $requiredPermission) {
                abort_unless(in_array($requiredPermission, $data['permissions'] ?? []), 422, 'Admin management permissions must remain assigned.');
            }
        }
        foreach ($data['permissions'] ?? [] as $permission) {
            abort_unless($request->user()->can($permission), 403);
        }
        DB::transaction(function () use ($role, $data) {
            $model = $role ?? new Role(['guard_name' => 'web']);
            $model->name = $data['name'];
            $model->save();
            $model->syncPermissions($data['permissions'] ?? []);
        });
        return $request->expectsJson() ? response()->json(['status' => 'success', 'message' => 'Role saved successfully.']) : back()->with('success', 'Role saved successfully.');
    }

    public function deleteRole(Role $role)
    {
        abort_if($role->name === 'admin' || $role->users()->exists(), 422, 'Protected or assigned roles cannot be deleted.');
        $role->delete();
        return request()->expectsJson() ? response()->json(['status' => 'success', 'message' => 'Role deleted.']) : back()->with('success', 'Role deleted.');
    }
}
