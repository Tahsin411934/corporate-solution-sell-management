<x-app-layout>
    <div class="p-4 sm:p-6">
        @if(session('success'))<p class="mb-4 text-green-700" role="status">{{ session('success') }}</p>@endif
        @if($errors->any())<p class="mb-4 text-red-600" role="alert">{{ $errors->first() }}</p>@endif
        @php
            $columns = [
                ['data' => 'name'], ['data' => 'users_count', 'searchable' => false],
                ['data' => 'permission_names', 'orderable' => false, 'searchable' => false],
                ['data' => 'actions', 'orderable' => false, 'searchable' => false],
            ];
        @endphp
        <x-entity-crud id="role" title="Role" icon="fas fa-key" create-permission="roles" :columns="['Role', 'Users', 'Permissions', 'Actions']" :dt-columns="$columns" :ajax-url="route('roles.data')" :store-url="route('roles.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-input label="Role name" name="name" id="role_name" required />
            <fieldset class="space-y-3">
                <legend class="font-semibold text-sm text-slate-700">Assign permissions</legend>
                @forelse($permissions->groupBy(fn ($permission) => explode('.', $permission->name)[0]) as $module => $modulePermissions)
                    <div class="border rounded-lg p-3"><h3 class="font-semibold text-sm mb-2">{{ ucfirst($module) }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 break-words">
                            @foreach($modulePermissions as $permission)
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-gray-300">{{ $permission->name }}</label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">Create permissions first to assign them here.</p>
                @endforelse
            </fieldset>
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'role', 'baseUrl' => url('roles')])
</x-app-layout>
