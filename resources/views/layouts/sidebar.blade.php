@php
    $groups = [
        'billing' => ['Billing', 'fa-file-invoice-dollar', [
            ['Customers', 'customers.index', 'customers.*', 'fa-users', 'customers.view'],
            ['Invoices', 'invoices.index', 'invoices.*', 'fa-file-invoice', 'invoices.view'],
            ['Payments', 'payments.index', 'payments.*', 'fa-money-bill-wave', 'payments.view'],
        ]],
        'catalog' => ['Catalog & Partners', 'fa-briefcase', [
            ['Services', 'services.index', 'services.*', 'fa-briefcase', 'services.view'],
            ['Referral Sources', 'referral-sources.index', 'referral-sources.*', 'fa-user-group', 'referral-sources.view'],
        ]],
        'finance' => ['Finance & Reports', 'fa-chart-line', [
            ['Expenses', 'expenses.index', 'expenses.*', 'fa-wallet', 'expenses.view'],
            ['Reports', 'reports.index', 'reports.*', 'fa-chart-bar', 'reports.view'],
        ]],
        'administration' => ['Administration', 'fa-shield-halved', [
            ['Users', 'users.index', 'users.*', 'fa-user-shield', 'users.view'],
            ['Roles', 'roles.index', 'roles.*', 'fa-key', 'roles.view'],
            ['Permissions', 'permissions.index', 'permissions.*', 'fa-lock', 'admin'],
            ['Audit Logs', 'audit-logs.index', 'audit-logs.*', 'fa-clock-rotate-left', 'audit-logs.view'],
        ]],
        'settings' => ['Settings', 'fa-gear', [
            ['Company Settings', 'companysettings.index', 'companysettings.*', 'fa-building', null],
        ]],
    ];
@endphp
<div class="nav-tooltip" id="navTooltip" role="tooltip"></div>
<aside id="sidebar" class="w-56 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 z-30 overflow-hidden" aria-label="Main navigation">
    <div class="mobile-sidebar-header"><span class="font-semibold text-gray-800">Menu</span><button id="mobileSidebarClose" type="button" aria-label="Close navigation" class="w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100"><i class="fas fa-times" aria-hidden="true"></i></button></div>
    <div class="h-[60px] bg-primary text-white flex items-center px-3.5 flex-shrink-0">
        <img src="{{ asset('logo.png') }}" alt="" class="w-8 h-8 rounded-lg object-contain flex-shrink-0">
        <span class="logo-text font-semibold text-base whitespace-nowrap ml-2">Corporate Solution</span>
    </div>
    <div id="searchBox" class="px-3 py-3 border-b border-gray-100">
        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 focus-within:border-primary">
            <i class="fas fa-search text-gray-400 text-xs" aria-hidden="true"></i>
            <input id="sidebarSearch" type="search" aria-label="Search navigation" placeholder="Search menus..." class="bg-transparent border-0 focus:ring-0 text-xs w-full min-w-0">
            <button id="searchClear" type="button" aria-label="Clear navigation search" class="hidden text-gray-400"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <p id="searchEmptyMsg" role="status" class="hidden text-xs text-gray-400 mt-2">No matching menus</p>
    </div>
    <nav id="sideNav" class="flex-1 overflow-y-auto overflow-x-hidden p-2 space-y-2">
        <x-sidebar-link :href="route('dashboard')" label="Dashboard" icon="fa-th-large" :active="request()->routeIs('dashboard')" />
        @foreach($groups as $key => [$label, $icon, $items])
            @php
                $visibleItems = array_values(array_filter($items, fn ($item) => $item[4] === 'admin' ? auth()->user()?->hasRole('admin') : (!$item[4] || auth()->user()?->can($item[4]))));
                $activeGroup = collect($visibleItems)->contains(fn ($item) => request()->routeIs($item[2]));
            @endphp
            @if(count($visibleItems))
                <x-sidebar-group :id="'sidebar-'.$key" :label="$label" :icon="$icon" :active="$activeGroup">
                    @foreach($visibleItems as [$itemLabel, $route, $pattern, $itemIcon])
                        <x-sidebar-link :href="route($route)" :label="$itemLabel" :icon="$itemIcon" :active="request()->routeIs($pattern)" />
                    @endforeach
                </x-sidebar-group>
            @endif
        @endforeach
    </nav>
</aside>
