<header class="app-navigation h-[60px] bg-primary text-white border-b border-blue-300 flex items-center justify-between gap-3 px-6 flex-shrink-0 z-20">
    <button id="mobileMenuBtn" type="button" aria-label="Open navigation" aria-expanded="false"
        class="hidden w-10 h-10 rounded-lg border border-white/30 bg-transparent items-center justify-center text-white hover:bg-white/10 shadow-sm">
        <i class="fas fa-bars text-lg"></i>
    </button>
    <button id="collapseBtn"
        class="w-8 h-8 rounded-full border border-white/30 bg-white/10 flex items-center justify-center text-white hover:bg-white/20 flex-shrink-0 shadow-sm">
        <i class="fas fa-chevron-left text-[15px]" id="collapseIcon"></i>
    </button>
    <!-- Search -->
    <div class="header-search flex items-center gap-2 bg-white/10 border border-white/30 rounded-lg px-3 w-96 min-w-0">
        <i class="fas fa-search text-white/70 text-xs"></i>
        <input type="text" placeholder="Search"
            class="bg-transparent border-none outline-none focus:outline-none focus:ring-0 text-sm text-white placeholder-white/70 w-full" />
    </div>

    <!-- Right side -->
    <div class="flex items-center gap-3">

        <!-- Notification bell -->
        <button
            class="relative w-9 h-9 border border-white/30 rounded-lg flex items-center justify-center text-white hover:bg-white/10 transition-colors">
            <i class="fas fa-bell text-[15px]"></i>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>

        <!-- Settings gear -->
        <button
            class="w-9 h-9 border border-white/30 rounded-lg flex items-center justify-center text-white hover:bg-white/10 transition-colors">
            <i class="fas fa-cog text-[15px]"></i>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-white/30"></div>



        <!-- User chip -->
        <details class="profile-menu relative sm:ml-3">

            <!-- Trigger -->
            <summary aria-label="Account menu" class="flex items-center gap-2.5 cursor-pointer list-none rounded-lg focus-visible:ring-2 focus-visible:ring-white">

                <!-- Avatar -->
                <div
                    class="w-[34px] h-[34px] rounded-full overflow-hidden bg-gradient-to-br from-rose-400 to-pink-600 flex items-center justify-center flex-shrink-0 ring-2 ring-white/70 shadow-sm">
                    <span class="text-white text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </span>
                </div>

                <!-- Name -->
                <div class="profile-name leading-tight text-left max-w-32">
                    <p class="text-[11px] text-white/70 font-medium">Admin</p>
                    <p class="text-[13px] text-white font-semibold truncate">
                        {{ Auth::user()->name }}
                    </p>
                </div>

                <!-- Icon -->
                <i
                    class="fas fa-chevron-down text-[10px] text-white/70 group-hover:text-white transition-colors"></i>
            </summary>

            <!-- Dropdown -->
            <div
                class="absolute right-0 top-12 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">

                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-gray-50">
                        Log Out
                    </button>
                </form>

            </div>
        </details>

    </div>
</header>
<div id="sidebarOverlay" class="sidebar-overlay" aria-hidden="true"></div>
