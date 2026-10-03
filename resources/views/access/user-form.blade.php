<form method="POST" action="{{ $account ? route('users.update', $account) : route('users.store') }}" class="bg-white rounded-xl p-5 space-y-4">
    @csrf
    @if($account)@method('PUT')@endif
    <x-form-input label="Name" name="name" :id="'name_'.($account?->id ?? 'new')" :value="$account?->name ?? ''" required />
    <x-form-input label="Email" name="email" type="email" :id="'email_'.($account?->id ?? 'new')" :value="$account?->email ?? ''" required />
    <x-form-input :label="$account ? 'New password (leave blank to keep current)' : 'Password'" name="password" type="password" :id="'password_'.($account?->id ?? 'new')" :required="!$account" autocomplete="new-password" />
    <x-form-input label="Confirm password" name="password_confirmation" type="password" :id="'confirmation_'.($account?->id ?? 'new')" autocomplete="new-password" />
    <x-form-select label="Roles" name="roles[]" :id="'roles_'.($account?->id ?? 'new')" multiple :placeholder="null">
        @foreach($roles as $role)<option value="{{ $role->name }}" @selected($account?->hasRole($role->name))>{{ $role->name }}</option>@endforeach
    </x-form-select>
    <button class="btn-primary px-4 py-2 rounded-lg">{{ $account ? 'Save user' : 'Create user' }}</button>
</form>
