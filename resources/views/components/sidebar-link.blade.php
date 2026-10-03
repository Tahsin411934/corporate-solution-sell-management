@props(['href', 'label', 'icon', 'active' => false])
<a href="{{ $href }}" data-label="{{ $label }}" @if($active) aria-current="page" @endif
    class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ $active ? 'bg-primary !text-white active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}">
    <i class="fas {{ $icon }} w-4 text-center flex-shrink-0" aria-hidden="true"></i><span class="nav-label">{{ $label }}</span>
</a>
