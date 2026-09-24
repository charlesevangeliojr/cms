@extends('backend.layouts.sidebar')

@section('title', 'Banner Management')

@php
    $canCreate = auth()->user()?->canAccess('banners', 'add');
    $canEdit = auth()->user()?->canAccess('banners', 'edit');
    $canDelete = auth()->user()?->canAccess('banners', 'delete');
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Banners &amp; Promotional Media</h2>
            <p class="text-sm text-gray-500">Manage promotional banners, slider placements, and media across your website.</p>
        </div>
        @if ($canCreate)
            <a href="{{ route('banners.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create New Banner
            </a>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Banners</p>
                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalBanners }}</p>
            <p class="mt-1 text-xs text-gray-400">Banners in your library</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active Banners</p>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $activeBanners }}</p>
            <p class="mt-1 text-xs text-gray-400">Currently marked visible</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Inactive Banners</p>
                <span class="rounded-lg bg-gray-100 p-2 text-gray-500">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $inactiveBanners }}</p>
            <p class="mt-1 text-xs text-gray-400">Paused or hidden</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-semibold text-gray-900">All Banners</h3>
            <form method="GET" action="{{ route('banners.index') }}" class="flex flex-col gap-2 sm:flex-row" data-turbo="false">
                <label for="banner-search" class="sr-only">Search banners</label><input id="banner-search" type="search" name="q" value="{{ $search }}" placeholder="Search banners..."
                       class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <label for="banner-status" class="sr-only">Banner status</label><select id="banner-status" name="status" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Apply filters</button>
                @if ($search !== '' || $status !== null)
                    <a href="{{ route('banners.index') }}" class="self-center text-sm font-medium text-gray-500 hover:text-gray-800">Clear</a>
                @endif
            </form>
        </div>

        @if ($banners->count())
            <div class="overflow-x-auto">
                <table class="admin-banner-table w-full text-left text-sm text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Banner Info</th>
                            <th class="px-6 py-3 font-semibold">Position Placement</th>
                            <th class="px-6 py-3 font-semibold">Target URL</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($banners as $banner)
                            <tr class="transition hover:bg-gray-50/80">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                                             class="h-14 w-24 rounded-lg border border-gray-200 object-cover shadow-sm">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $banner->title }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ $banner->created_at?->format('M j, Y') }} · {{ number_format($banner->clicks) }} clicks
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Placement" class="px-6 py-4 font-medium text-gray-700">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        {{ $banner->position }}
                                    </span>
                                </td>
                                <td data-label="Destination" class="max-w-[200px] truncate px-6 py-4 font-mono text-xs text-indigo-600">
                                    <a href="{{ $banner->target_url }}" target="_blank" rel="noopener noreferrer" class="hover:underline">
                                        {{ $banner->target_url }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($banner->is_active)
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
                                        <a href="{{ $banner->target_url }}" target="_blank" rel="noopener noreferrer" title="View Preview"
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100">View
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                        @if ($canEdit)
                                            <a href="{{ route('banners.edit', $banner) }}" title="Edit Banner"
                                               class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 px-2.5 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">Edit
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('banners.destroy', $banner) }}" data-turbo="false"
                                                  data-confirm="Delete {{ $banner->title }}? This cannot be undone."
                                                  onsubmit="return confirm(this.dataset.confirm);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Banner" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

            @if ($banners->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $banners->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <p class="font-semibold text-gray-700">No banners found</p>
                <p class="mt-1 text-sm text-gray-400">
                    @if ($search !== '' || $status !== null)
                        Try changing or clearing the current filters.
                    @else
                        Create the first banner to add it to the CMS.
                    @endif
                </p>
                @if ($canCreate && $search === '' && $status === null)
                    <a href="{{ route('banners.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Create Banner
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
