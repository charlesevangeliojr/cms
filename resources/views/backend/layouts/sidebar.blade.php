<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">

    <title>@yield('title', 'Dashboard') — CMS</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>
    <script type="module">
        import * as Turbo from 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.23/+esm';
        Turbo.start();
    </script>
    <style>
        body { font-family: Figtree, sans-serif; }
        .admin-workspace { background: #f5f7fb; }
        .admin-workspace main { max-width: 1440px; margin-inline: auto; }
        .admin-workspace :is(a, button, input, select, textarea):focus-visible { outline: 3px solid #818cf8; outline-offset: 3px; }
        .admin-workspace main :is(input:not([type=checkbox]):not([type=radio]):not([type=hidden]), select) { min-height: 44px; }
        .admin-workspace main :is(input, select, textarea) { max-width: 100%; }
        .admin-workspace main .text-gray-400 { color: #64748b; }
        .admin-workspace main .rounded-xl { border-radius: 16px; }
        .admin-workspace main .shadow-sm { box-shadow: 0 3px 16px rgb(15 23 42 / 4%); }
        #adminSidebar nav a { min-height: 48px; border-radius: 12px; }
        #adminSidebar nav a[aria-current=page] { background: #4f46e5; color: white; }
        .admin-workspace main button, .admin-workspace main a[class*="rounded-xl"] { min-height: 40px; }
        .admin-skip { position: fixed; top: -100px; left: 16px; z-index: 60; background: white; padding: 12px 20px; border-radius: 8px; }
        .admin-skip:focus { top: 12px; }
        .admin-banner-table { width: 100%; border-collapse: collapse; }
        .admin-banner-table th, .admin-banner-table td { vertical-align: middle; }
        .admin-banner-table thead th { white-space: nowrap; }
        /* Keep tables horizontally scrollable on small screens instead of stacking */
        @media (prefers-reduced-motion: reduce) {
            .admin-workspace *, .admin-workspace *::before, .admin-workspace *::after { transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="admin-workspace font-sans antialiased bg-gray-100 text-gray-900">
    <a href="#adminContent" class="admin-skip">Skip to content</a><div class="relative min-h-screen md:flex">
        <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-gray-900/50 md:hidden" onclick="toggleAdminSidebar(false)"></div>

        @include('backend.partials.sidebar')

        <!-- Main -->
        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6 sm:py-4">
                    <button type="button" onclick="toggleAdminSidebar(true)" id="adminMenuToggle" aria-controls="adminSidebar" aria-expanded="false" aria-label="Open navigation menu" class="inline-flex rounded-xl border border-gray-200 p-2 text-gray-600 hover:bg-gray-50 md:hidden">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                                        <div class="min-w-0 flex-1">
                        <h1 class="truncate text-lg font-semibold sm:text-xl">@yield('title', 'Dashboard')</h1>
                    </div>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="shrink-0 inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">View site</a>
                    @php($navUser = auth()->user())
                    @if($navUser)
                    <div class="relative shrink-0 hidden md:block">
                        <button type="button" id="navbarProfileButton" aria-haspopup="menu" aria-expanded="false" aria-controls="navbarProfileMenu"
                                class="inline-flex items-center gap-2 h-10 rounded-xl border border-gray-200 bg-white pl-1 pr-4 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            <span class="flex size-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white overflow-hidden shrink-0">
                                @if($navUser->avatar_url)
                                    <img src="{{ $navUser->avatar_url }}" alt="{{ $navUser->name }}" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    {{ strtoupper(substr($navUser->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="hidden sm:flex flex-col items-start leading-none min-w-0 max-w-[140px]">
                                <span class="truncate text-sm font-semibold text-gray-900">{{ $navUser->name }}</span>
                                <span class="truncate text-xs text-gray-500">{{ $navUser->role }}</span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div id="navbarProfileMenu" class="hidden absolute right-0 mt-2 w-72 rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden z-50" role="menu" aria-labelledby="navbarProfileButton">
                            <div class="px-4 py-4 flex items-center gap-3 bg-gray-50/70 border-b border-gray-100">
                                <span class="flex size-12 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white overflow-hidden shrink-0">
                                    @if($navUser->avatar_url)
                                        <img src="{{ $navUser->avatar_url }}" alt="{{ $navUser->name }}" class="h-12 w-12 rounded-full object-cover">
                                    @else
                                        {{ strtoupper(substr($navUser->name, 0, 1)) }}
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-gray-900">{{ $navUser->name }}</span>
                                    <span class="block truncate text-xs text-gray-500">{{ $navUser->email }}</span>
                                    <span class="mt-1 inline-flex rounded-full bg-white border border-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $navUser->role }}</span>
                                </span>
                            </div>
                            <div class="p-2 space-y-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500" role="menuitem">
                                    <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    My Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 text-left" role="menuitem">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </header>

            <main id="adminContent" tabindex="-1" class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @include('backend.partials.notifications')

    <script>
        function toggleAdminSidebar(open) {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (!sidebar || !backdrop) {
                return;
            }

                        document.getElementById('adminMenuToggle')?.setAttribute('aria-expanded', String(open));
            document.querySelector('main')?.toggleAttribute('inert', open && window.innerWidth < 768);
            sidebar.classList.toggle('-translate-x-full', !open);
            sidebar.inert = !open && window.innerWidth < 768;
            if (window.innerWidth < 768) {
                if (open) sidebar.querySelector('button')?.focus();
                else document.getElementById('adminMenuToggle')?.focus();
            }
            backdrop.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 768);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                toggleAdminSidebar(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                toggleAdminSidebar(false);
            }
        });

        function syncFullAccessToggle() {
            const boxes = Array.from(document.querySelectorAll('.perm-check'));
            const fullAccess = document.getElementById('fullAccessToggle');
            if (!fullAccess) {
                return;
            }

            fullAccess.checked = boxes.length > 0 && boxes.every((box) => box.checked);
        }

        function toggleColumn(action, checked) {
            document.querySelectorAll(`.perm-check[name$="[${action}]"]`).forEach((box) => {
                box.checked = checked;
            });
            syncColumnToggles();
            syncFullAccessToggle();
        }

        function syncColumnToggles() {
            ['view','add','edit','delete'].forEach((action) => {
                const header = document.querySelector(`.col-toggle[data-action="${action}"]`);
                if (!header) return;
                const boxes = document.querySelectorAll(`.perm-check[name$="[${action}]"]`);
                if (!boxes.length) return;
                const allChecked = Array.from(boxes).every((b) => b.checked);
                const noneChecked = Array.from(boxes).every((b) => !b.checked);
                header.checked = allChecked;
                header.indeterminate = !allChecked && !noneChecked;
            });
        }

        function toggleRow(module, checked) {
            document.querySelectorAll(`.perm-check[name^="permissions[${module}]"]`).forEach((box) => {
                box.checked = checked;
            });
            syncRowToggles();
            syncColumnToggles();
            syncFullAccessToggle();
        }

        function syncRowToggles() {
            document.querySelectorAll('.row-toggle').forEach((header) => {
                const module = header.dataset.module;
                if (!module) return;
                const boxes = document.querySelectorAll(`.perm-check[name^="permissions[${module}]"]`);
                if (!boxes.length) return;
                const allChecked = Array.from(boxes).every((b) => b.checked);
                const noneChecked = Array.from(boxes).every((b) => !b.checked);
                header.checked = allChecked;
                header.indeterminate = !allChecked && !noneChecked;
            });
        }

        function toggleAllPermissions(checked) {
            document.querySelectorAll('.perm-check').forEach((box) => {
                box.checked = checked;
            });
            syncRowToggles();
            syncColumnToggles();
            syncFullAccessToggle();
        }

        function applyRoleDefaults(select, { force = false } = {}) {
            let roleDefaults = {};
            try {
                roleDefaults = JSON.parse(select.dataset.roleDefaults || '{}');
            } catch (error) {
                roleDefaults = {};
            }

            const permissions = roleDefaults[select.value] || {};
            document.querySelectorAll('.perm-check').forEach((box) => {
                const match = box.name.match(/^permissions\[(.+?)\]\[(.+?)\]$/);
                box.checked = !!(match && permissions[match[1]] && permissions[match[1]][match[2]]);
            });
            syncRowToggles();
            syncColumnToggles();
            syncFullAccessToggle();
        }

        function initializeRolePermissionForms() {
            document.querySelectorAll('select[data-role-defaults]').forEach((select) => {
                if (select.dataset.rolePermissionInitialized === 'true') {
                    return;
                }

                select.dataset.rolePermissionInitialized = 'true';
                const preserveCustom = select.dataset.preserveCustom === '1';

                if (!preserveCustom) {
                    applyRoleDefaults(select);
                } else {
                    syncFullAccessToggle();
                }

                select.addEventListener('change', () => applyRoleDefaults(select, { force: true }));
            });

            document.querySelectorAll('.perm-check').forEach((box) => {
                if (box.dataset.permissionInitialized === 'true') {
                    return;
                }

                box.dataset.permissionInitialized = 'true';
                box.addEventListener('change', () => { syncRowToggles(); syncColumnToggles(); syncFullAccessToggle(); });
            });
            syncRowToggles();
            syncColumnToggles();
        }



                function initializeAdminNavigation() {
            const sidebar = document.getElementById('adminSidebar');
            if (sidebar) sidebar.inert = window.innerWidth < 768;
        }
        document.addEventListener('DOMContentLoaded', initializeAdminNavigation);
        document.addEventListener('turbo:load', initializeAdminNavigation);
        function toggleNavbarProfile(open) {
            const btn = document.getElementById('navbarProfileButton');
            const menu = document.getElementById('navbarProfileMenu');
            if (!btn || !menu) return;
            const willOpen = open ?? menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !willOpen);
            btn.setAttribute('aria-expanded', String(willOpen));
        }
        function toggleSidebarProfile(open) {
            const btn = document.getElementById('sidebarProfileButton');
            const menu = document.getElementById('sidebarProfileMenu');
            if (!btn || !menu) return;
            const willOpen = open ?? menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !willOpen);
            btn.setAttribute('aria-expanded', String(willOpen));
        }
        document.getElementById('navbarProfileButton')?.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleNavbarProfile();
        });
        document.getElementById('sidebarProfileButton')?.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebarProfile();
        });
        document.addEventListener('click', (e) => {
            const navMenu = document.getElementById('navbarProfileMenu');
            const navBtn = document.getElementById('navbarProfileButton');
            if (navMenu && navBtn && !navMenu.classList.contains('hidden') && !navMenu.contains(e.target) && !navBtn.contains(e.target)) {
                toggleNavbarProfile(false);
            }
            const sideMenu = document.getElementById('sidebarProfileMenu');
            const sideBtn = document.getElementById('sidebarProfileButton');
            if (sideMenu && sideBtn && !sideMenu.classList.contains('hidden') && !sideMenu.contains(e.target) && !sideBtn.contains(e.target)) {
                toggleSidebarProfile(false);
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { toggleNavbarProfile(false); toggleSidebarProfile(false); }
        });

        document.addEventListener('DOMContentLoaded', () => {
            initializeRolePermissionForms();

        });
        document.addEventListener('turbo:load', () => {
            initializeRolePermissionForms();

        });
    </script>
</body>
</html>
