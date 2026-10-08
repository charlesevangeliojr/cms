@extends('backend.layouts.sidebar')
@section('title', 'Page Banners')
@section('content')
@php($canEdit = auth()->user()?->canAccess('banners', 'edit'))
@php($canDelete = auth()->user()?->canAccess('banners', 'delete'))
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4"><div><h2 class="text-2xl font-bold text-gray-900">Page banners</h2><p class="text-sm text-gray-500">Manage page-specific title and image banners. Images use a 16:3 crop.</p></div><a href="{{ route('page-banners.create', array_filter(['target' => $target])) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Add page banner</a></div>
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <form id="page-banner-filter-form" method="GET" action="{{ route('page-banners.index') }}" class="flex flex-col gap-2 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-end" data-turbo="false">
            <input id="page-banner-search" type="search" name="q" value="{{ $search }}" placeholder="Search pages" aria-label="Search page banners" class="h-10 rounded-xl border border-gray-300 px-3 text-sm sm:w-56">
            <select name="target" onchange="this.form.requestSubmit()" class="h-10 rounded-xl border border-gray-300 bg-white px-3 text-sm"><option value="">All page banners</option>@foreach($pageBannerPages as $page)<option value="{{ $page->slug }}" @selected($target === $page->slug)>{{ $page->name }}</option>@endforeach</select>
            <select name="status" onchange="this.form.requestSubmit()" class="h-10 rounded-xl border border-gray-300 bg-white px-3 text-sm"><option value="">All statuses</option><option value="active" @selected($status === 'active')>Active</option><option value="inactive" @selected($status === 'inactive')>Inactive</option></select>
            @if($search !== '' || $target || $status)<a href="{{ route('page-banners.index') }}" class="h-10 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-center text-sm font-semibold text-rose-700">Clear</a>@endif
        </form>
        @if($banners->isNotEmpty())
            <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Banner</th><th class="px-5 py-3">Placement</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-gray-100">
                @foreach($banners as $banner)<tr><td class="px-5 py-3"><div class="flex items-center gap-3"><img src="{{ $banner->image_url }}" alt="" class="w-32 aspect-[16/3] rounded-lg object-cover"><span class="font-semibold text-gray-900">{{ $banner->title }}</span></div></td><td class="px-5 py-3">{{ $banner->page->name }}</td><td class="px-5 py-3">{{ $banner->is_active ? 'Active' : 'Inactive' }}</td><td class="px-5 py-3 text-right whitespace-nowrap">@if($canEdit)<a href="{{ route('page-banners.edit', $banner) }}" class="font-semibold text-indigo-600">Edit</a>@endif @if($canDelete)<form method="POST" action="{{ route('page-banners.destroy', $banner) }}" class="ml-3 inline" data-turbo="false" data-confirm="Delete this page banner and its image?">@csrf @method('DELETE')<button class="font-semibold text-rose-600">Delete</button></form>@endif</td></tr>@endforeach
            </tbody></table></div>
            @if($banners->hasPages())<div class="border-t border-gray-200 px-5 py-4">{{ $banners->links() }}</div>@endif
        @else
            <div class="p-10 text-center text-sm text-gray-500">No page banners found.</div>
        @endif
    </section>
</div>
<script>
(() => {
    const form = document.getElementById('page-banner-filter-form');
    const search = document.getElementById('page-banner-search');
    if (!form || !search) return;
    let timer;
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.requestSubmit(), 300);
    });
})();
</script>
@endsection
