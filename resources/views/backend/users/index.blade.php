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
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 transition">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add User
        </a>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    {{-- Users table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if ($users->count())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-400">
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Role</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium text-right">Joined</th>
                            <th class="px-6 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 items-center justify-center rounded-full bg-indigo-900 text-xs font-bold text-white">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $roleBadges[$user->role] ?? 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                        {{ $user->role ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-500">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right text-gray-400">{{ $user->created_at?->format('M j, Y') }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}"
                                           class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" data-turbo="false"
                                              onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                        </form>
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
                <a href="{{ route('users.create') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-900 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-800">Add User</a>
            </div>
        @endif
    </div>

</div>
@endsection
