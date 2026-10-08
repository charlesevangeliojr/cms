@extends('backend.layouts.sidebar')
@section('title', $banner->exists ? 'Edit Page Banner' : 'Add Page Banner')
@section('content')
@php($editing = $banner->exists)
<div class="mx-auto max-w-4xl space-y-6">
    <div><a href="{{ route('page-banners.index') }}" class="text-sm text-gray-500">← Page banners</a><h2 class="mt-2 text-2xl font-bold">{{ $editing ? 'Edit page banner' : 'Add page banner' }}</h2><p class="text-sm text-gray-500">The public banner title is taken from the selected page. Images use a 16:3 ratio.</p></div>
    @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $editing ? route('page-banners.update', $banner) : route('page-banners.store') }}" enctype="multipart/form-data" class="space-y-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6" data-turbo="false">
        @csrf @if($editing) @method('PUT') @endif
        <div><label for="banner-page" class="mb-1.5 block text-sm font-semibold">Banner placement</label><select id="banner-page" name="banner_page_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5">@foreach($pageBannerPages as $page)<option value="{{ $page->id }}" @selected((string) old('banner_page_id', $banner->banner_page_id ?? $pageBannerPages->firstWhere('slug', request('target'))?->id ?? $pageBannerPages->first()->id) === (string) $page->id)>{{ $page->name }}</option>@endforeach</select></div>
        @if($editing)<div><p class="mb-2 text-sm font-semibold">Current image</p><img src="{{ $banner->image_url }}" alt="" class="aspect-[16/3] w-full rounded-xl object-cover"></div>@endif
        <div>
            <label for="banner-image" class="mb-1.5 block text-sm font-semibold">{{ $editing ? 'Replace image' : 'Banner image' }}</label>
            <input id="banner-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(!$editing) class="block w-full rounded-xl border border-gray-300 text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:font-semibold">
            <p class="mt-1 text-xs text-gray-500">JPG, PNG, or WebP · max 2 MB · crop to 16:3 (recommended 1600 × 300 px).</p>
            <p data-page-crop-status role="status" hidden class="mt-2 text-sm font-medium text-emerald-700"></p>
            <div data-page-crop-editor hidden class="mt-4 overflow-hidden rounded-xl border border-indigo-200 bg-white">
                <div class="bg-indigo-700 px-4 py-3 text-white"><p class="font-semibold">Crop banner image to 16:3</p><p class="text-xs text-indigo-100">Drag the image to choose which part appears in the banner.</p></div>
                <div class="page-banner-crop-stage"><img data-page-crop-image alt="Adjust page banner crop"></div>
                <div class="flex items-center justify-end bg-gray-50 px-4 py-3"><button type="button" data-page-crop-cancel class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700">Choose another image</button></div>
                <p data-page-crop-error hidden role="alert" class="px-4 pb-3 text-sm text-rose-700"></p>
            </div>
        </div>
        <label class="inline-flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true)) class="rounded border-gray-300">Active</label>
        <div class="flex justify-end border-t border-gray-100 pt-4"><button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Save</button></div>
    </form>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="{{ asset('backend/banners/page-banner-cropper.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" referrerpolicy="no-referrer"></script>
<script src="{{ asset('backend/banners/page-banner-cropper.js') }}"></script>
@endsection
