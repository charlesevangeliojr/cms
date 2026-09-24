@extends('backend.layouts.sidebar')

@section('title', 'Contact Us')

@php
    $canEdit = auth()->user()?->canAccess('contacts', 'edit');
    $canDelete = auth()->user()?->canAccess('contacts', 'delete');
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Contact Messages</h2>
            <p class="text-sm text-gray-500">Review and manage messages sent through the contact form.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Messages</p>
                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalMessages }}</p>
            <p class="mt-1 text-xs text-gray-400">Messages in your inbox</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Unread Messages</p>
                <span class="rounded-lg bg-amber-50 p-2 text-amber-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $unreadMessages }}</p>
            <p class="mt-1 text-xs text-gray-400">Waiting for review</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Read Messages</p>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $readMessages }}</p>
            <p class="mt-1 text-xs text-gray-400">Already reviewed</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-semibold text-gray-900">All Messages</h3>
            <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-col gap-2 sm:flex-row" data-turbo="false">
                <label for="contact-search" class="sr-only">Search messages</label><input id="contact-search" type="search" name="q" value="{{ $search }}" placeholder="Search messages..."
                       class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <label for="contact-status" class="sr-only">Message status</label><select id="contact-status" name="status" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="unread" @selected($status === 'unread')>Unread</option>
                    <option value="read" @selected($status === 'read')>Read</option>
                </select>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Apply filters</button>
                @if ($search !== '' || $status !== null)
                    <a href="{{ route('contacts.index') }}" class="self-center text-sm font-medium text-gray-500 hover:text-gray-800">Clear</a>
                @endif
            </form>
        </div>

        @if ($messages->count())
            <div class="overflow-x-auto">
                <table class="admin-banner-table w-full text-left text-sm text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Message</th>
                            <th class="px-6 py-3 font-semibold">Subject</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($messages as $message)
                            <tr class="transition hover:bg-gray-50/80">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900">{{ $message->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $message->email }} · {{ $message->created_at?->format('M j, Y') }}</p>
                                    <p class="mt-1 max-w-md truncate text-sm text-gray-500">{{ $message->message }}</p>
                                </td>
                                <td data-label="Subject" class="px-6 py-4 font-medium text-gray-700">
                                    {{ $message->subject }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($message->is_read)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Read
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Unread
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($canEdit)
                                            <form method="POST" action="{{ route('contacts.update', $message) }}" data-turbo="false">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_read" value="{{ $message->is_read ? '0' : '1' }}">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 px-2.5 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">
                                                    {{ $message->is_read ? 'Mark unread' : 'Mark read' }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('contacts.destroy', $message) }}" data-turbo="false"
                                                  data-confirm="Delete this message? This cannot be undone."
                                                  onsubmit="return confirm(this.dataset.confirm);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Message" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($messages->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $messages->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">No messages found</p>
                <p class="mt-1 text-sm text-gray-400">
                    @if ($search !== '' || $status !== null)
                        Try changing or clearing the current filters.
                    @else
                        New contact form submissions will appear here.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
