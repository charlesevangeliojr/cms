@extends('backend.layouts.sidebar')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome hero --}}
    <div class="overflow-hidden rounded-xl bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 text-white shadow-sm">
        <div class="flex flex-col gap-4 px-6 py-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-400">{{ now()->format('l, F j, Y') }}</p>
                <h2 class="mt-1 text-2xl font-bold">Welcome back, {{ auth()->user()->name }}!</h2>
                <p class="mt-1 text-sm text-gray-300">Signed in as {{ auth()->user()->email }} — {{ auth()->user()->role }} — latest updates at top.</p>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-white px-4 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-100">
                    View Website
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-semibold text-white hover:bg-white/20">
                    My Profile
                </a>
            </div>
        </div>
    </div>

    {{-- Stat cards — 4 columns, latest counts --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-200 hover:shadow transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    </span>
                    <p class="text-sm font-medium text-gray-500">Users</p>
                </div>
                <span class="text-xs font-semibold text-gray-400">{{ $activeBanners ?? '' }}</span>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $totalUsers ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">Total • latest at top</p>
        </a>

        <a href="{{ route('banners.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-200 hover:shadow transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Z" /></svg>
                    </span>
                    <p class="text-sm font-medium text-gray-500">Banners</p>
                </div>
                <span class="text-xs font-medium text-emerald-600">{{ $activeBanners ?? 0 }} active</span>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $totalBanners ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $totalBanners ?? 0 }} total • {{ $activeBanners ?? 0 }} active</p>
        </a>

        <a href="{{ route('contacts.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-200 hover:shadow transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    </span>
                    <p class="text-sm font-medium text-gray-500">Contacts</p>
                </div>
                <span class="text-xs font-medium text-amber-600">{{ $unreadContacts ?? 0 }} unread</span>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $totalContacts ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $unreadContacts ?? 0 }} unread • latest at top</p>
        </a>

        <a href="{{ route('newsletters.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-200 hover:shadow transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
                    </span>
                    <p class="text-sm font-medium text-gray-500">Newsletters</p>
                </div>
                <span class="text-xs font-medium text-emerald-600">{{ $activeNewsletters ?? 0 }} active</span>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ $totalNewsletters ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $activeNewsletters ?? 0 }} active • latest at top</p>
        </a>
    </div>

    {{-- Recent tables — all latest at top --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent Banners --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
                <div>
                    <h3 class="font-semibold">Recent Banners</h3>
                    <p class="text-xs text-gray-400">Latest 5 • newest at top</p>
                </div>
                <a href="{{ route('banners.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            @if(!empty($recentBanners) && count($recentBanners))
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Banner</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentBanners as $banner)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            @if($banner->image_url)
                                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="h-8 w-14 rounded-xl border border-gray-200 object-cover shrink-0">
                                            @else
                                                <span class="h-8 w-14 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-400">—</span>
                                            @endif
                                            <span class="font-medium truncate max-w-[160px]">{{ $banner->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex h-6 items-center rounded-full px-2.5 text-xs font-semibold {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">{{ $banner->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-right text-xs text-gray-400">{{ $banner->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                    <p class="text-sm font-medium text-gray-500">No banners yet</p>
                    <p class="text-xs text-gray-400 mt-1">Create one — it will appear here at top.</p>
                </div>
            @endif
        </div>

        {{-- Recent Contacts --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
                <div>
                    <h3 class="font-semibold">Recent Contacts</h3>
                    <p class="text-xs text-gray-400">Latest 5 • newest at top</p>
                </div>
                <a href="{{ route('contacts.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            @if(!empty($recentContacts) && count($recentContacts))
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">From</th>
                                <th class="px-6 py-3 font-medium">Subject</th>
                                <th class="px-6 py-3 font-medium text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentContacts as $c)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <p class="font-medium truncate max-w-[140px]">{{ $c->name }}</p>
                                        <p class="text-xs text-gray-400 truncate max-w-[140px]">{{ $c->email }}</p>
                                    </td>
                                    <td class="px-6 py-3"><span class="truncate max-w-[140px] inline-block">{{ $c->subject }}</span> <span class="ml-2 inline-flex h-5 items-center rounded-full px-2 text-xs font-semibold {{ $c->is_read ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $c->is_read ? 'Read' : 'Unread' }}</span></td>
                                    <td class="px-6 py-3 text-right text-xs text-gray-400">{{ $c->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                    <p class="text-sm font-medium text-gray-500">No messages</p>
                    <p class="text-xs text-gray-400 mt-1">Contact form posts appear here at top.</p>
                </div>
            @endif
        </div>

        {{-- Recent Newsletters --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
                <div>
                    <h3 class="font-semibold">Recent Newsletters</h3>
                    <p class="text-xs text-gray-400">Latest 5 • newest at top</p>
                </div>
                <a href="{{ route('newsletters.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            @if(!empty($recentNewsletters) && count($recentNewsletters))
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Email</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentNewsletters as $n)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <p class="font-medium truncate max-w-[180px]">{{ $n->email }}</p>
                                        <p class="text-xs text-gray-400">{{ $n->name ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-3"><span class="inline-flex h-6 items-center rounded-full px-2.5 text-xs font-semibold {{ $n->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">{{ $n->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="px-6 py-3 text-right text-xs text-gray-400">{{ $n->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                    <p class="text-sm font-medium text-gray-500">No subscribers</p>
                    <p class="text-xs text-gray-400 mt-1">Newsletter signups appear here at top.</p>
                </div>
            @endif
        </div>

        {{-- Recent Users --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
                <div>
                    <h3 class="font-semibold">Recent Users</h3>
                    <p class="text-xs text-gray-400">Latest 5 • newest at top</p>
                </div>
                <a href="{{ route('users.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            @if (!empty($recentUsers) && count($recentUsers))
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">User</th>
                                <th class="px-6 py-3 font-medium">Role</th>
                                <th class="px-6 py-3 font-medium text-right">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentUsers as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            @if($user->avatar_url)
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="size-8 rounded-full object-cover border border-gray-200 shrink-0">
                                            @else
                                                <span class="flex size-8 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white shrink-0">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            @endif
                                            <span class="font-medium truncate max-w-[120px]">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3"><span class="inline-flex h-6 items-center rounded-full bg-gray-100 px-2.5 text-xs font-semibold">{{ $user->role }}</span></td>
                                    <td class="px-6 py-3 text-right text-xs text-gray-400">{{ $user->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                    <p class="text-sm font-medium text-gray-500">No users yet</p>
                    <p class="text-xs text-gray-400 mt-1">New registrations will show up here.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- System & Quick Actions --}}
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h3 class="font-semibold">Quick Actions</h3>
            <p class="text-xs text-gray-400">Jump to sections • all lists latest at top</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach (($pages ?? []) as $link)
                    <a href="{{ url($link['url']) }}" class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium hover:border-gray-900 hover:bg-gray-900 hover:text-white transition">
                        {{ $link['label'] }} Page
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                @endforeach
                <a href="{{ route('banners.create') }}" class="flex items-center justify-between rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition">Create Banner →</a>
                <a href="{{ route('users.create') }}" class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium hover:border-gray-900 hover:bg-gray-900 hover:text-white transition">Add User →</a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="relative flex size-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex size-2.5 rounded-full bg-green-500"></span>
                </span>
                <h3 class="font-semibold">System Status</h3>
            </div>
            <dl class="mt-4 space-y-2.5 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Environment</dt><dd class="font-medium capitalize">{{ $environment ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Laravel</dt><dd class="font-medium">{{ $laravelVersion ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">PHP</dt><dd class="font-medium">{{ $phpVersion ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Database</dt><dd class="font-medium capitalize">{{ $dbDriver ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Timezone</dt><dd class="font-medium">{{ $timezone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Uploads</dt><dd class="font-medium">{{ $uploadCount ?? 0 }} files</dd></div>
            </dl>
        </div>
    </div>

</div>
@endsection
