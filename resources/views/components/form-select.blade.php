@props([
    'label', 
    'name' => null, 
    'id' => null, 
    'placeholder' => 'Select an option',
    'required' => false,
    'searchable' => true,
])

<div>
    <div class="flex items-center justify-between gap-2 mb-1">
    <label for="{{ $id ?? $name }}" class="font-semibold text-sm text-slate-700 dark:text-slate-300 block">
        {{ $label }}
        @if($required)
            <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
        @endif
    </label>
    {{ $labelAction ?? '' }}
    </div>
    <select id="{{ $id ?? $name }}"
            name="{{ $name }}"
            @if ($searchable) data-local-select2 @endif
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all cursor-pointer'
            ]) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
</div>
