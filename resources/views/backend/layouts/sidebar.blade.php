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
        .admin-workspace main button, .admin-workspace main a[class*="rounded"] { min-height: 40px; }
        .admin-skip { position: fixed; top: -100px; left: 16px; z-index: 60; background: white; padding: 12px 20px; border-radius: 8px; }
        .admin-skip:focus { top: 12px; }
        @media (max-width: 767px) {
            .admin-banner-table, .admin-banner-table tbody, .admin-banner-table tr, .admin-banner-table td { display: block; width: 100%; }
            .admin-banner-table thead { display: none; }
            .admin-banner-table tr { padding: 16px; }
            .admin-banner-table td { padding: 6px 0; max-width: none; white-space: normal; overflow-wrap: anywhere; }
            .admin-banner-table td[data-label]::before { content: attr(data-label); display: block; color: #64748b; font-size: 11px; font-weight: 600; margin-bottom: 4px; }
            .admin-banner-table td:last-child > div { justify-content: flex-start; flex-wrap: wrap; }
        }
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
                    <button type="button" onclick="toggleAdminSidebar(true)" id="adminMenuToggle" aria-controls="adminSidebar" aria-expanded="false" aria-label="Open navigation menu" class="inline-flex rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-50 md:hidden">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                                        <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-gray-500">Workspace / Administration</p>
                        <h1 class="truncate text-lg font-semibold sm:text-xl">@yield('title', 'Dashboard')</h1>
                    </div>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">View site <span class="sr-only">(opens in a new tab)</span><span aria-hidden="true">&nearr;</span></a>
                    <span class="hidden max-w-40 truncate rounded-full bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 lg:block">{{ auth()->user()?->name }}</span>
                </div>
            </header>

            <main id="adminContent" tabindex="-1" class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @include('backend.partials.toasts')

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

        function toggleAllPermissions(checked) {
            document.querySelectorAll('.perm-check').forEach((box) => {
                box.checked = checked;
            });
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
            syncFullAccessToggle();
        }

        function initializeRolePermissionForms() {
            document.querySelectorAll('select[data-role-defaults]').forEach((select) => {
                if (select.dataset.rolePermissionInitialized === 'true') {
                    return;
                }

                select.dataset.rolePermissionInitialized = 'true';
                const preserveCustom = select.dataset.preserveCustom === '1';
                const hasCustomSelection = Array.from(document.querySelectorAll('.perm-check')).some((box) => box.checked);

                if (!preserveCustom || !hasCustomSelection) {
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
                box.addEventListener('change', syncFullAccessToggle);
            });
        }

        function dismissToast(toast) {
            if (!toast || toast.dataset.dismissed === 'true') {
                return;
            }

            toast.dataset.dismissed = 'true';
            if (toast.dataset.timeoutId) {
                clearTimeout(Number(toast.dataset.timeoutId));
            }
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 320);
        }

        function initializeToasts() {
            document.querySelectorAll('[data-toast]').forEach((toast) => {
                if (toast.dataset.initialized === 'true') {
                    return;
                }

                toast.dataset.initialized = 'true';
                const timeout = Number(toast.dataset.timeout || 5000);
                const progress = toast.querySelector('[data-toast-progress]');
                if (progress) {
                    progress.style.transition = `transform ${timeout}ms linear`;
                    requestAnimationFrame(() => {
                        progress.classList.remove('scale-x-100');
                        progress.classList.add('scale-x-0');
                    });
                }
                toast.dataset.timeoutId = String(setTimeout(() => dismissToast(toast), timeout));
                toast.querySelector('[data-toast-close]')?.addEventListener('click', () => dismissToast(toast));
            });
        }

                function initializeAdminNavigation() {
            const sidebar = document.getElementById('adminSidebar');
            if (sidebar) sidebar.inert = window.innerWidth < 768;
        }
        document.addEventListener('DOMContentLoaded', initializeAdminNavigation);
        document.addEventListener('turbo:load', initializeAdminNavigation);
        document.addEventListener('DOMContentLoaded', () => {
            initializeRolePermissionForms();
            initializeToasts();
        });
        document.addEventListener('turbo:load', () => {
            initializeRolePermissionForms();
            initializeToasts();
        });
    </script>
</body>
</html>
