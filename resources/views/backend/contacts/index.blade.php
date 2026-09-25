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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-semibold text-gray-900">All Messages</h3>
            <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-col gap-2 sm:flex-row" data-turbo="false">
                <label for="contact-search" class="sr-only">Search messages</label><input id="contact-search" type="search" name="q" value="{{ $search }}" placeholder="Search messages..."
                       class="rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <label for="contact-status" class="sr-only">Message status</label><select id="contact-status" name="status" class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="unread" @selected($status === 'unread')>Unread</option>
                    <option value="read" @selected($status === 'read')>Read</option>
                </select>
                <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Apply filters</button>
                @if ($search !== '' || $status !== null)
                    <a href="{{ route('contacts.index') }}" class="self-center text-sm font-medium text-gray-500 hover:text-gray-800">Clear</a>
                @endif
            </form>
        </div>

        @if ($messages->count())
            <div class="overflow-x-auto">
                <table class="admin-banner-table min-w-[860px] w-full text-left text-sm text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Message</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Subject</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 text-right font-semibold whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($messages as $message)
                            <tr class="transition hover:bg-gray-50/80" data-row-id="{{ $message->id }}">
                                <td class="px-6 py-4 align-middle">
                                    <p class="font-semibold text-gray-900">{{ $message->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $message->email }} · {{ $message->created_at?->format('M j, Y') }}</p>
                                    <p class="mt-1 max-w-md truncate text-sm text-gray-500">{{ $message->message }}</p>
                                </td>
                                <td data-label="Subject" class="px-6 py-4 align-middle font-medium text-gray-700 whitespace-nowrap">
                                    {{ $message->subject }}
                                </td>
                                <td class="px-6 py-4 align-middle whitespace-nowrap" data-status-cell="{{ $message->id }}">
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
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap">
                                        <button type="button"
                                                class="contact-view-btn inline-flex items-center justify-center gap-1.5 h-8 min-w-[64px] rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition whitespace-nowrap"
                                                data-id="{{ $message->id }}"
                                                data-name="{{ $message->name }}"
                                                data-email="{{ $message->email }}"
                                                data-subject="{{ $message->subject }}"
                                                data-message="{{ $message->message }}"
                                                data-date="{{ $message->created_at?->format('M j, Y g:i A') }}"
                                                data-is-read="{{ $message->is_read ? '1' : '0' }}"
                                                data-update-url="{{ route('contacts.update', $message) }}"
                                                onclick="openContactModal(this)">
                                            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            View
                                        </button>
                                        @if ($canEdit)
                                            <form method="POST" action="{{ route('contacts.update', $message) }}" data-turbo="false" class="inline-flex" data-mark-form="{{ $message->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_read" value="{{ $message->is_read ? '0' : '1' }}">
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[96px] rounded-xl border border-indigo-200 bg-white px-3 text-xs font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition whitespace-nowrap">
                                                    {{ $message->is_read ? 'Mark unread' : 'Mark read' }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('contacts.destroy', $message) }}" data-turbo="false" class="inline-flex"
                                                  data-confirm="Delete this message? This cannot be undone."
                                                  >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Message" class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 transition whitespace-nowrap">Delete
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

{{-- Contact View Modal --}}
<div id="contactViewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeContactModal()"></div>
    <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
            <h3 class="text-sm font-bold tracking-widest uppercase text-gray-700">View Message</h3>
            <button type="button" onclick="closeContactModal()" class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6 space-y-4 overflow-y-auto">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">From</p>
                <p id="modalName" class="mt-1 font-semibold text-gray-900"></p>
                <p id="modalEmail" class="text-sm text-gray-500"></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</p>
                <p id="modalSubject" class="mt-1 font-medium text-gray-900"></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Message</p>
                <p id="modalMessage" class="mt-1 text-sm text-gray-700 whitespace-pre-wrap break-words bg-gray-50 rounded-xl p-4 border border-gray-200"></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Received</p>
                <p id="modalDate" class="mt-1 text-sm text-gray-600"></p>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end shrink-0">
            <button type="button" onclick="closeContactModal()" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Close</button>
        </div>
    </div>
</div>

<script>
function openContactModal(btn) {
    const name = btn.dataset.name || '';
    const email = btn.dataset.email || '';
    const subject = btn.dataset.subject || '';
    const message = btn.dataset.message || '';
    const date = btn.dataset.date || '';
    const isRead = btn.dataset.isRead === '1';
    const updateUrl = btn.dataset.updateUrl || '';
    const id = btn.dataset.id || '';

    document.getElementById('modalName').textContent = name;
    document.getElementById('modalEmail').textContent = email;
    document.getElementById('modalSubject').textContent = subject;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('modalDate').textContent = date;

    document.getElementById('contactViewModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Auto mark as read if currently unread
    if (!isRead && updateUrl) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({_method: 'PATCH', is_read: '1'})
        }).then(res => {
            if (res.ok) {
                btn.dataset.isRead = '1';
                // Update row status cell
                const statusCell = document.querySelector(`[data-status-cell="${id}"]`);
                if (statusCell) {
                    statusCell.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Read</span>';
                }
                // Update Mark button text if present
                const markForm = document.querySelector(`[data-mark-form="${id}"]`);
                if (markForm) {
                    const btnMark = markForm.querySelector('button');
                    const input = markForm.querySelector('input[name="is_read"]');
                    if (btnMark) btnMark.textContent = 'Mark unread';
                    if (input) input.value = '0';
                }
            }
        }).catch(() => {});
    }
}
function closeContactModal() {
    document.getElementById('contactViewModal')?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeContactModal();
});
</script>
@endsection
