<!-- Sidebar -->
@php($landingRouteName = auth()->user()?->landingRouteName() ?? 'login')
<aside id="adminSidebar" class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 -translate-x-full shrink-0 transform flex-col bg-gray-900 text-gray-200 transition-transform duration-200 ease-in-out md:sticky md:top-0 md:translate-x-0">
    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-800 px-6 py-5">
        <div class="min-w-0">
            <a href="{{ route($landingRouteName) }}" class="font-bold text-lg tracking-tight text-white">CMS Workspace</a>
            <p class="text-xs text-gray-400 mt-1 truncate">{{ auth()->user()->email ?? '' }}</p>
        </div>
        <button type="button" onclick="toggleAdminSidebar(false)" aria-label="Close navigation menu" class="rounded-xl p-1.5 text-gray-400 hover:bg-gray-800 hover:text-white md:hidden">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <nav aria-label="Admin navigation" class="min-h-0 flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @if (auth()->user()?->canAccess('dashboard', 'view'))
        <a href="{{ route('dashboard') }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
            Dashboard
        </a>
        @endif

        @if (auth()->user()?->canAccess('contacts', 'view'))
        <a href="{{ route('contacts.index') }}" aria-current="{{ request()->routeIs('contacts.*') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('contacts.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
            Contact Us
        </a>
        @endif

        @if (auth()->user()?->canAccess('newsletters', 'view'))
        <a href="{{ route('newsletters.index') }}" aria-current="{{ request()->routeIs('newsletters.*') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('newsletters.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
            Newsletter
        </a>
        @endif

        @if (auth()->user()?->canAccess('banners', 'view'))
        <a href="{{ route('banners.index') }}" aria-current="{{ request()->routeIs('banners.*') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('banners.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            Banners
        </a>
        @endif

        @if (auth()->user()?->canAccess('users', 'view'))
        <a href="{{ route('users.index') }}" aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            User Management
        </a>
        @endif
    </nav>

    <div class="shrink-0 border-t border-gray-800 px-4 py-4">
        @php($sidebarUser = auth()->user())
        @if($sidebarUser)
            {{-- Desktop: static profile, no dropdown --}}
            <div class="hidden md:flex items-center gap-3 rounded-xl border border-gray-800 bg-gray-800 px-3 py-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white overflow-hidden">
                    @if($sidebarUser->avatar_url)
                        <img src="{{ $sidebarUser->avatar_url }}" alt="{{ $sidebarUser->name }}" class="h-9 w-9 rounded-full object-cover">
                    @else
                        {{ strtoupper(substr($sidebarUser->name, 0, 1)) }}
                    @endif
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-white">{{ $sidebarUser->name }}</span>
                    <span class="block truncate text-xs text-gray-400">{{ $sidebarUser->role }}</span>
                </span>
            </div>
            {{-- Mobile: dropdown (navbar is hidden on mobile) --}}
            <div class="relative md:hidden">
                <button type="button" id="sidebarProfileButton" aria-haspopup="menu" aria-expanded="false" aria-controls="sidebarProfileMenu"
                        class="w-full flex items-center gap-3 rounded-xl border border-gray-800 bg-gray-800 px-3 py-3 shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition text-left">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white overflow-hidden">
                        @if($sidebarUser->avatar_url)
                            <img src="{{ $sidebarUser->avatar_url }}" alt="{{ $sidebarUser->name }}" class="h-9 w-9 rounded-full object-cover">
                        @else
                            {{ strtoupper(substr($sidebarUser->name, 0, 1)) }}
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ $sidebarUser->name }}</span>
                        <span class="block truncate text-xs text-gray-400">{{ $sidebarUser->role }}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div id="sidebarProfileMenu" class="hidden absolute bottom-full left-0 right-0 mb-2 rounded-xl border border-gray-700 bg-gray-800 shadow-xl overflow-hidden z-50" role="menu" aria-labelledby="sidebarProfileButton">
                    <div class="p-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" role="menuitem">
                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                            My Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 text-left" role="menuitem">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</aside>
