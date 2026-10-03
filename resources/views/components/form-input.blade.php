@props([
    'label', 
    'name', 
    'id' => null, 
    'type' => 'text', 
    'placeholder' => '', 
    'value' => '',
    'required' => false
])

<div>
    <label for="{{ $id ?? $name }}" class="font-semibold text-sm text-slate-700 dark:text-slate-300 block mb-1">
        {{ $label }}
        @if($required)
            <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
        @endif
    </label>
    <input type="{{ $type }}"
           id="{{ $id ?? $name }}"
           name="{{ $name }}"
           placeholder="{{ $placeholder }}"
           value="{{ $value }}"
           {{ $required ? 'required' : '' }}
           {{ $attributes->merge([
               'class' => 'w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500'
           ]) }}>
</div>
