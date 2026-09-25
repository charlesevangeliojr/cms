@extends('backend.layouts.sidebar')

@section('title', 'User Management')

@php
$roleBadges = [
    'Super Admin' => 'bg-purple-50 text-purple-700 border border-purple-200',
    'Content Manager' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'Editor' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'Viewer / Analyst' => 'bg-amber-50 text-amber-700 border border-amber-200',
];
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">User Management</h2>
            <p class="text-sm text-gray-500">{{ $users->total() }} registered {{ Str::plural('account', $users->total()) }}</p>
        </div>
        @if (auth()->user()?->canAccess('users', 'add'))
<a href="{{ route('users.create') }}"
           class="inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition whitespace-nowrap">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add User
        </a>
@endif
    </div>

    {{-- Users table — aligned columns with horizontal scroll --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if ($users->count())
            <div class="overflow-x-auto">
                <table class="admin-banner-table min-w-[860px] w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">User</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Role</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap text-right">Joined</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white overflow-hidden">
                                            @if($user->avatar_url)
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-gray-900">{{ $user->name }}</p>
                                            <p class="break-all text-xs text-gray-400">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center h-7 rounded-full px-3 text-xs font-semibold whitespace-nowrap {{ $roleBadges[$user->role] ?? 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                        {{ $user->role ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center justify-center gap-1.5 h-7 rounded-full bg-emerald-50 border border-emerald-200 px-3 text-xs font-semibold text-emerald-700 whitespace-nowrap">
                                            <svg class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center gap-1.5 h-7 rounded-full bg-gray-100 border border-gray-200 px-3 text-xs font-semibold text-gray-500 whitespace-nowrap">
                                            <svg class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap text-right text-sm text-gray-500">Joined {{ $user->created_at?->format('M j, Y') }}</td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap">
                                        @if (auth()->user()?->canAccess('users', 'edit'))
<a href="{{ route('users.edit', $user) }}"
                                           class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-indigo-200 bg-white px-3 text-xs font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition whitespace-nowrap">Edit
                                            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
</a>
@endif
                                        @if ($user->is_protected)
                                            <span title="This account cannot be deleted"
                                                  class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 whitespace-nowrap">Protected</span>
                                        @elseif (auth()->user()?->canAccess('users', 'delete'))
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-turbo="false" class="inline-flex"
                                                  data-confirm="Delete {{ $user->name }}? This cannot be undone."
                                                  >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 transition whitespace-nowrap">Delete
                                                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">No users found</p>
                <p class="mt-1 text-sm text-gray-400">Get started by adding your first user.</p>
                @if (auth()->user()?->canAccess('users', 'add'))
<a href="{{ route('users.create') }}"
                   class="mt-4 inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add User</a>
@endif
            </div>
        @endif
    </div>

</div>
@endsection
