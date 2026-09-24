@extends('backend.layouts.sidebar')

@section('title', 'Newsletter')

@php
    $canEdit = auth()->user()?->canAccess('newsletters', 'edit');
    $canDelete = auth()->user()?->canAccess('newsletters', 'delete');
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Newsletter Subscribers</h2>
            <p class="text-sm text-gray-500">Manage newsletter subscriptions and audience status.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Subscribers</p>
                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalSubscribers }}</p>
            <p class="mt-1 text-xs text-gray-400">People in your audience</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active Subscribers</p>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $activeSubscribers }}</p>
            <p class="mt-1 text-xs text-gray-400">Currently subscribed</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Inactive Subscribers</p>
                <span class="rounded-lg bg-gray-100 p-2 text-gray-500">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $inactiveSubscribers }}</p>
            <p class="mt-1 text-xs text-gray-400">Unsubscribed or paused</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-semibold text-gray-900">All Subscribers</h3>
            <form method="GET" action="{{ route('newsletters.index') }}" class="flex flex-col gap-2 sm:flex-row" data-turbo="false">
                <label for="newsletter-search" class="sr-only">Search subscribers</label><input id="newsletter-search" type="search" name="q" value="{{ $search }}" placeholder="Search subscribers..."
                       class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <label for="newsletter-status" class="sr-only">Subscriber status</label><select id="newsletter-status" name="status" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Apply filters</button>
                @if ($search !== '' || $status !== null)
                    <a href="{{ route('newsletters.index') }}" class="self-center text-sm font-medium text-gray-500 hover:text-gray-800">Clear</a>
                @endif
            </form>
        </div>

        @if ($subscribers->count())
            <div class="overflow-x-auto">
                <table class="admin-banner-table w-full text-left text-sm text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Subscriber</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($subscribers as $subscriber)
                            <tr class="transition hover:bg-gray-50/80">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900">{{ $subscriber->email }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $subscriber->name ?? 'No name' }} · {{ $subscriber->created_at?->format('M j, Y') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($subscriber->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($canEdit)
                                            <form method="POST" action="{{ route('newsletters.update', $subscriber) }}" data-turbo="false">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $subscriber->is_active ? '0' : '1' }}">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 px-2.5 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">
                                                    {{ $subscriber->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('newsletters.destroy', $subscriber) }}" data-turbo="false"
                                                  data-confirm="Delete {{ $subscriber->email }}? This cannot be undone."
                                                  onsubmit="return confirm(this.dataset.confirm);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Subscriber" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($subscribers->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $subscribers->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">No subscribers found</p>
                <p class="mt-1 text-sm text-gray-400">
                    @if ($search !== '' || $status !== null)
                        Try changing or clearing the current filters.
                    @else
                        New newsletter signups will appear here.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
