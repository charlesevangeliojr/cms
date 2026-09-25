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
            <p class="text-sm text-gray-500">Manage banners with title, description, image and visibility.</p>
        </div>
        @if ($canCreate)
            <a href="{{ route('banners.create') }}"
               class="inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition whitespace-nowrap">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create New Banner
            </a>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-semibold text-gray-900">All Banners</h3>
            <form method="GET" action="{{ route('banners.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" data-turbo="false">
                <label for="banner-search" class="sr-only">Search banners</label>
                <input id="banner-search" type="search" name="q" value="{{ $search }}" placeholder="Search banners..."
                       class="h-10 rounded-xl border border-gray-300 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <label for="banner-status" class="sr-only">Banner status</label>
                <select id="banner-status" name="status" class="h-10 rounded-xl border border-gray-300 bg-white px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition whitespace-nowrap">Apply filters</button>
                @if ($search !== '' || $status !== null)
                    <a href="{{ route('banners.index') }}" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 whitespace-nowrap">Clear</a>
                @endif
            </form>
        </div>

        @if ($banners->count())
            <div class="overflow-x-auto">
                <table class="admin-banner-table min-w-[860px] w-full text-left text-sm text-gray-600">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Banner</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Description</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 text-right font-semibold whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($banners as $banner)
                            <tr class="transition hover:bg-gray-50/80">
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center gap-4">
                                        @if($banner->image_url)
                                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                                                 class="w-32 aspect-[16/6] rounded-xl border border-gray-200 object-cover shadow-sm shrink-0" loading="lazy" onerror="this.onerror=null;this.src='https://via.placeholder.com/320x120?text=No+Image';">
                                        @else
                                            <div class="w-32 aspect-[16/6] rounded-xl border border-gray-200 bg-gray-100 flex items-center justify-center text-xs text-gray-400 shrink-0">No image</div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 truncate">{{ $banner->title }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ $banner->created_at?->format('M j, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Description" class="max-w-[400px] px-6 py-4 align-middle text-sm text-gray-600">
                                    <p class="line-clamp-3">{{ \Illuminate\Support\Str::limit($banner->description, 150) }}</p>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    @if ($banner->is_active)
                                        <span class="inline-flex items-center justify-center gap-1.5 h-7 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 text-xs font-semibold text-emerald-700 whitespace-nowrap">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center gap-1.5 h-7 rounded-full border border-gray-200 bg-gray-100 px-2.5 text-xs font-semibold text-gray-600 whitespace-nowrap">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap">
                                        @if ($canEdit)
                                            <a href="{{ route('banners.edit', $banner) }}" title="Edit Banner"
                                               class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-indigo-200 bg-white px-3 text-xs font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition whitespace-nowrap">Edit
                                                <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>
                                        @endif
                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('banners.destroy', $banner) }}" data-turbo="false" class="inline-flex"
                                                  data-confirm="Delete {{ $banner->title }}? This cannot be undone."
                                                  >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Banner" class="inline-flex items-center justify-center gap-1.5 h-8 min-w-[76px] rounded-xl border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 transition whitespace-nowrap">Delete
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
                    <a href="{{ route('banners.create') }}" class="mt-4 inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition whitespace-nowrap">
                        Create Banner
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
