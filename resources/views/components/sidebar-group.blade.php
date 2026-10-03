@props(['id', 'label', 'icon', 'active' => false])
<div class="sidebar-group" data-group-label="{{ $label }}">
    <button type="button" class="has-sub nav-item sidebar-group-toggle flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-semibold {{ $active ? 'open text-primary bg-primary-soft' : 'text-gray-600 hover:bg-gray-50' }}"
        data-sub="{{ $id }}" data-label="{{ $label }}" aria-controls="{{ $id }}" aria-expanded="{{ $active ? 'true' : 'false' }}">
        <i class="fas {{ $icon }} w-4 text-center flex-shrink-0" aria-hidden="true"></i><span class="nav-label flex-1 text-left">{{ $label }}</span><i class="fas fa-chevron-down nav-chevron text-[10px]" aria-hidden="true"></i>
    </button>
    <div id="{{ $id }}" class="submenu {{ $active ? 'open' : '' }}" @if(!$active) hidden @endif>{{ $slot }}</div>
</div>
