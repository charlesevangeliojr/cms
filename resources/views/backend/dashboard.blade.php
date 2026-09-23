@extends('backend.layouts.sidebar')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome hero --}}
    <div class="overflow-hidden rounded-xl bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 text-white shadow">
        <div class="flex flex-col gap-4 px-6 py-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-400">{{ now()->format('l, F j, Y') }}</p>
                <h2 class="mt-1 text-2xl font-bold">Welcome back, {{ auth()->user()->name }}!</h2>
                <p class="mt-1 text-sm text-gray-300">Signed in as {{ auth()->user()->email }} — here's what's happening in your CMS.</p>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5a11.964 11.964 0 0 1-10.07-5.918m11.524 6.348a11.953 11.953 0 0 1-7.843 4.582" /></svg>
                    View Website
                </a>
                <a href="{{ url('/about') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-500 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    About Page
                </a>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                </span>
                <p class="text-sm font-medium text-gray-500">Total Users</p>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $totalUsers ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">Registered accounts</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-lg bg-green-100 text-green-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                </span>
                <p class="text-sm font-medium text-gray-500">Frontend Pages</p>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $pageCount ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ isset($pages) ? implode(', ', array_column($pages, 'label')) : '' }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                </span>
                <p class="text-sm font-medium text-gray-500">Uploaded Files</p>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $uploadCount ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">Files in public/uploads</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>
                </span>
                <p class="text-sm font-medium text-gray-500">Framework</p>
            </div>
            <p class="mt-3 text-3xl font-bold">L{{ $laravelVersion ?? '' }}</p>
            <p class="mt-1 text-xs text-gray-400">PHP {{ $phpVersion ?? '' }}</p>
        </div>
    </div>

    {{-- Tables + side panels --}}
    <div class="grid gap-4 lg:grid-cols-3">

        {{-- Recent users --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 class="font-semibold">Recent Users</h3>
                    <p class="text-xs text-gray-400">Latest registered accounts</p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ $totalUsers ?? 0 }} total</span>
            </div>
            @if (!empty($recentUsers) && count($recentUsers))
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                <th class="px-6 py-3 font-medium">Name</th>
                                <th class="px-6 py-3 font-medium">Email</th>
                                <th class="px-6 py-3 font-medium text-right">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentUsers as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-8 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                            <span class="font-medium">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-3 text-right text-gray-400">{{ $user->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-10 text-center">
                    <p class="font-medium text-gray-500">No users yet</p>
                    <p class="mt-1 text-sm text-gray-400">New registrations will show up here.</p>
                </div>
            @endif
        </div>

        {{-- Side column --}}
        <div class="space-y-4">

            {{-- Quick actions --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold">Quick Actions</h3>
                <p class="text-xs text-gray-400">Jump to the public site</p>
                <div class="mt-4 space-y-2">
                    @foreach (($pages ?? []) as $link)
                        <a href="{{ url($link['url']) }}"
                           class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium hover:border-gray-900 hover:bg-gray-900 hover:text-white">
                            {{ $link['label'] }} Page
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- System status --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="relative flex size-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex size-2.5 rounded-full bg-green-500"></span>
                    </span>
                    <h3 class="font-semibold">System Status</h3>
                </div>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Environment</dt>
                        <dd class="font-medium capitalize">{{ $environment ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Laravel</dt>
                        <dd class="font-medium">{{ $laravelVersion ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">PHP</dt>
                        <dd class="font-medium">{{ $phpVersion ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Database</dt>
                        <dd class="font-medium capitalize">{{ $dbDriver ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Timezone</dt>
                        <dd class="font-medium">{{ $timezone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

        </div>
    </div>

</div>
@endsection
