@props([
    'label', 
    'name', 
    'id' => null, 
    'placeholder' => '', 
    'rows' => 3,
    'value' => ''
])

<div>
    <label for="{{ $id ?? $name }}" class="font-semibold text-sm text-slate-700 block mb-1">{{ $label }}</label>
    <textarea id="{{ $id ?? $name }}" 
              name="{{ $name }}" 
              rows="{{ $rows }}" 
              placeholder="{{ $placeholder }}"
              {{ $attributes->merge([
                  'class' => 'w-full border border-slate-300 rounded-md p-2 bg-white text-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400'
              ]) }}>{{ $value }}</textarea>
</div>
