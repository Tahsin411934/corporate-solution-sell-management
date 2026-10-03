<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [
                ['data' => 'name'], ['data' => 'email'],
                ['data' => 'role_names', 'orderable' => false, 'searchable' => false],
                ['data' => 'actions', 'orderable' => false, 'searchable' => false],
            ];
        @endphp
        <x-entity-crud id="user" title="User" icon="fas fa-users" create-permission="users" :columns="['Name', 'Email', 'Roles', 'Actions']" :dt-columns="$columns" :ajax-url="route('users.data')" :store-url="route('users.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-input label="Name" name="name" id="user_name" required />
            <x-form-input label="Email" name="email" id="user_email" type="email" required />
            <x-form-input label="Password (leave blank when editing to keep current)" name="password" id="user_password" type="password" autocomplete="new-password" required />
            <x-form-input label="Confirm password" name="password_confirmation" id="user_password_confirmation" type="password" autocomplete="new-password" />
            <x-form-select label="Assign roles" name="roles[]" id="user_roles" multiple :placeholder="null">
                @foreach($roles as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach
            </x-form-select>
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'user', 'baseUrl' => url('users')])
</x-app-layout>
