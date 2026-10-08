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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-semibold text-gray-900">All Subscribers <span class="text-gray-500">({{ $totalSubscribers }})</span></h3>
            <form method="GET" action="{{ route('newsletters.index') }}" class="flex flex-col gap-2 sm:flex-row sm:flex-wrap" data-turbo="false">
                <label for="newsletter-search" class="sr-only">Search subscribers</label><input id="newsletter-search" type="search" name="q" value="{{ $search }}" placeholder="Search subscribers..."
                       oninput="scheduleNewsletterSearch(this)" class="rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <label for="newsletter-status" class="sr-only">Subscriber status</label><select id="newsletter-status" name="status" onchange="this.form.requestSubmit()" class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <label for="newsletter-from" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600">From
                    <input id="newsletter-from" type="date" name="from" value="{{ $from }}" onchange="this.form.requestSubmit()" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-normal focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </label>
                <label for="newsletter-to" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600">To
                    <input id="newsletter-to" type="date" name="to" value="{{ $to }}" onchange="this.form.requestSubmit()" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-normal focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </label>
                <a id="newsletter-export-link" href="{{ route('newsletters.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-center text-sm font-semibold text-emerald-700 hover:bg-emerald-100">Export to .xls</a>
                @if ($search !== '' || $status !== null || $dateFilter)
                    <a href="{{ route('newsletters.index') }}" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Clear</a>
                @endif
            </form>
        </div>

        @if ($subscribers->count())
            <form id="newsletter-bulk-form" method="POST" action="{{ route('newsletters.bulk') }}" data-turbo="false" class="flex items-center gap-3 border-b border-gray-200 px-6 py-3">
                @csrf
                <div class="relative">
                    <button id="newsletter-actions-button" type="button" onclick="toggleNewsletterActions()" disabled aria-expanded="false" class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                        Actions <span id="newsletter-selected-count" class="rounded-full bg-white/20 px-2 py-0.5 text-xs">0</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                    <div id="newsletter-actions-menu" class="absolute left-0 top-full z-20 mt-2 hidden min-w-48 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                        @if ($canEdit)
                            <button type="submit" name="action" value="active" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">Mark as active</button>
                            <button type="submit" name="action" value="inactive" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">Mark as inactive</button>
                        @endif
                        @if ($canDelete)
                            <button type="submit" name="action" value="delete" data-confirm="Delete the selected subscribers? This cannot be undone." class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Delete selected</button>
                        @endif
                        <button type="button" onclick="exportSelectedSubscribers()" class="block w-full px-4 py-2 text-left text-sm text-emerald-700 hover:bg-emerald-50">Export selected</button>
                    </div>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="admin-banner-table min-w-[720px] w-full text-left text-sm text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-center"><label class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-xs font-semibold normal-case text-gray-600"><input id="newsletter-select-all" type="checkbox" aria-label="Select all subscribers" onchange="toggleAllSubscribers(this)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> All</label></th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Subscriber</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 text-right font-semibold whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($subscribers as $subscriber)
                            <tr class="transition hover:bg-gray-50/80">
                                <td class="px-4 py-4 text-center align-middle"><input type="checkbox" class="newsletter-select rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" name="ids[]" value="{{ $subscriber->id }}" form="newsletter-bulk-form" aria-label="Select {{ $subscriber->email }}"></td>
                                <td class="px-6 py-4 align-middle">
                                    <p class="font-semibold text-gray-900">{{ $subscriber->email }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $subscriber->created_at?->format('M j, Y') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap">
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
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap">
                                        @if ($canEdit)
                                            <form method="POST" action="{{ route('newsletters.update', $subscriber) }}" data-turbo="false" class="inline-flex">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $subscriber->is_active ? '0' : '1' }}">
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[96px] rounded-xl border border-indigo-200 bg-white px-3 text-xs font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition whitespace-nowrap">
                                                    {{ $subscriber->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('newsletters.destroy', $subscriber) }}" data-turbo="false" class="inline-flex"
                                                  data-confirm="Delete {{ $subscriber->email }}? This cannot be undone."
                                                  >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Subscriber" class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 transition whitespace-nowrap">Delete
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

            @if ($subscribers->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $subscribers->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">No subscribers found</p>
                <p class="mt-1 text-sm text-gray-400">
                    @if ($search !== '' || $status !== null || $dateFilter)
                        Try changing or clearing the current filters.
                    @else
                        New newsletter signups will appear here.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
<script>
let newsletterSearchTimer;
function scheduleNewsletterSearch(input) {
    clearTimeout(newsletterSearchTimer);
    sessionStorage.setItem('newsletter-search-focus', '1');
    newsletterSearchTimer = setTimeout(() => input.form.requestSubmit(), 500);
}
if (sessionStorage.getItem('newsletter-search-focus') === '1') {
    sessionStorage.removeItem('newsletter-search-focus');
    const input = document.getElementById('newsletter-search');
    input?.focus();
    input?.setSelectionRange(input.value.length, input.value.length);
}
function updateNewsletterSelection() {
    const checkboxes = [...document.querySelectorAll('.newsletter-select')];
    const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
    document.getElementById('newsletter-selected-count').textContent = selected;
    document.getElementById('newsletter-actions-button').disabled = selected === 0;
    const all = document.getElementById('newsletter-select-all');
    all.checked = checkboxes.length > 0 && selected === checkboxes.length;
    all.indeterminate = selected > 0 && selected < checkboxes.length;
}
function toggleAllSubscribers(master) {
    document.querySelectorAll('.newsletter-select').forEach((checkbox) => checkbox.checked = master.checked);
    updateNewsletterSelection();
}
function toggleNewsletterActions() {
    const menu = document.getElementById('newsletter-actions-menu');
    const button = document.getElementById('newsletter-actions-button');
    const open = menu.classList.toggle('hidden') === false;
    button.setAttribute('aria-expanded', String(open));
}
function exportSelectedSubscribers() {
    const ids = [...document.querySelectorAll('.newsletter-select:checked')].map((checkbox) => checkbox.value);
    if (!ids.length) {
        window.location.href = document.getElementById('newsletter-export-link').href;
        return;
    }
    const params = new URLSearchParams(window.location.search);
    params.delete('ids[]');
    ids.forEach((id) => params.append('ids[]', id));
    window.location.href = `{{ route('newsletters.export') }}?${params.toString()}`;
}
document.querySelectorAll('.newsletter-select').forEach((checkbox) => checkbox.addEventListener('change', updateNewsletterSelection));
document.addEventListener('click', (event) => {
    const menu = document.getElementById('newsletter-actions-menu');
    const button = document.getElementById('newsletter-actions-button');
    if (menu && button && !menu.contains(event.target) && !button.contains(event.target)) {
        menu.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
    }
});
</script>
@endsection
