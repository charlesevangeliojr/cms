@extends('backend.layouts.sidebar')
@section('title', 'Home Banner')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Home banner</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage the headline and slideshow shown at the top of your home page.</p>
        </div>
    </header>

    @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="mb-1 font-semibold">Please check the following:</p><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="grid items-start gap-6 xl:grid-cols-5">
        <form method="POST" action="{{ route('home-banners.update') }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm xl:col-span-2" data-turbo="false">
            @csrf @method('PUT')
            <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></span><div><h3 class="font-semibold text-gray-900">Headline & description</h3><p class="text-xs text-gray-500">Shared by every slide</p></div></div>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                <div><label for="home-banner-title" class="mb-1.5 block text-sm font-semibold text-gray-800">Title</label><input id="home-banner-title" name="title" required maxlength="255" value="{{ old('title', $banner->title) }}" placeholder="Write a short, clear headline" class="w-full rounded-xl border border-gray-300 px-3.5 py-3 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"><p class="mt-1.5 text-xs text-gray-500">This is the main heading visitors see.</p></div>
                <div><label for="home-banner-description" class="mb-1.5 block text-sm font-semibold text-gray-800">Description</label><textarea id="home-banner-description" name="description" required maxlength="5000" rows="5" placeholder="Add a sentence to explain your announcement" class="w-full resize-y rounded-xl border border-gray-300 px-3.5 py-3 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('description', $banner->description) }}</textarea><p class="mt-1.5 text-xs text-gray-500">A short supporting message works best.</p></div>
                <div class="flex justify-end border-t border-gray-100 pt-4"><button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"><svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 3a1 1 0 0 1 1-1h9.586A1 1 0 0 1 14.293 2.293l3.414 3.414A1 1 0 0 1 18 6.414V17a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V3Zm3 0v4h8V4H6Zm0 9a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v4H6v-4Z"/></svg>Save</button></div>
                <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3.5"><input type="checkbox" data-home-banner-visibility data-url="{{ route('home-banners.visibility') }}" @checked($banner->is_active) class="mt-0.5 size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"><span><span class="block text-sm font-semibold text-gray-800">Show Home Banner</span><span data-home-banner-visibility-error role="status" class="mt-1 block text-xs text-rose-700"></span></span></label>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm xl:col-span-3">
            <div class="flex flex-col justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                <div class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 20"/></svg></span><div><h3 class="font-semibold text-gray-900">Home Banner Image</h3><p class="text-xs text-gray-500">{{ $banner->images->count() }} {{ \Illuminate\Support\Str::plural('image', $banner->images->count()) }} · Displayed at 16:6</p></div></div>
                <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">Up to 2 MB each</span>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <form id="home-banner-image-upload" method="POST" action="{{ route('home-banners.images.store') }}" enctype="multipart/form-data" class="rounded-xl border border-dashed border-indigo-300 bg-indigo-50/50 p-4 sm:p-5" data-turbo="false">
                    @csrf
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="min-w-0 flex-1"><label for="home-banner-images" class="mb-1.5 block text-sm font-semibold text-gray-800">Choose images to add</label><input id="home-banner-images" type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp" class="block w-full cursor-pointer text-sm text-gray-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 file:shadow-sm hover:file:bg-indigo-100"><p class="mt-2 text-xs leading-relaxed text-gray-500">JPG, PNG, or WebP. Select any number of images. Each is cropped to 16:6 before upload.</p></div>
                    </div>
                    <div data-crop-editor hidden class="mt-4 overflow-hidden rounded-xl border border-indigo-200 bg-white">
                        <div class="flex flex-wrap items-center justify-between gap-2 bg-indigo-700 px-4 py-3 text-white"><div><p class="font-semibold">Crop image to 16:6</p><p data-crop-count class="text-xs text-indigo-100"></p></div><button type="button" data-crop-reset class="rounded-lg bg-white/15 px-3 py-1.5 text-sm font-medium hover:bg-white/25">Reset crop</button></div>
                        <div class="home-banner-crop-stage"><img data-crop-image alt="Adjust image crop"></div>
                        <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-50 px-4 py-3"><button type="button" data-crop-cancel class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700">Cancel</button><button type="button" data-crop-next class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Crop and continue</button></div>
                        <p data-crop-error hidden role="alert" class="px-4 pb-3 text-sm text-rose-700"></p>
                    </div>
                    <p data-crop-status hidden role="status" class="mt-3 text-sm font-medium text-indigo-700"></p>
                </form>

                @if($errors->has('images') || $errors->has('images.*'))<p class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $errors->first('images') ?: $errors->first('images.*') }}</p>@endif

                @if($banner->images->isNotEmpty())
                    <p data-order-status role="status" class="text-sm text-rose-700"></p>
                    <div class="grid gap-4 sm:grid-cols-2" data-home-image-grid data-reorder-url="{{ route('home-banners.images.order') }}">
                        @foreach($banner->images as $image)
                            <article data-image-id="{{ $image->id }}" class="group overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:border-gray-300 hover:shadow-sm">
                                <div class="relative"><img src="{{ $image->image_url }}" alt="Home banner slide {{ $loop->iteration }}" class="aspect-[16/6] w-full bg-gray-100 object-cover"><span data-slide-number class="absolute left-2 top-2 rounded-md bg-gray-900/70 px-2 py-1 text-xs font-medium text-white">Slide {{ $loop->iteration }}</span></div>
                                <div class="flex flex-wrap items-center justify-between gap-3 px-3.5 py-3">
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700"><input type="checkbox" data-image-visibility data-url="{{ route('home-banners.images.update', $image) }}" @checked($image->is_active) class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"><span data-visibility-label>{{ $image->is_active ? 'Shown' : 'Hidden' }}</span></label>
                                        <span data-visibility-error role="status" class="text-xs text-rose-600"></span>
                                    </div>
                                    <div class="flex items-center gap-1"><span class="mr-1 text-xs text-gray-400">Order</span><button type="button" data-order-up aria-label="Move slide {{ $loop->iteration }} up" @disabled($loop->first) class="rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40">↑</button><button type="button" data-order-down aria-label="Move slide {{ $loop->iteration }} down" @disabled($loop->last) class="rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40">↓</button></div>
                                    <form method="POST" action="{{ route('home-banners.images.destroy', $image) }}" data-turbo="false" data-confirm="Remove this Home banner image?">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">Remove</button></form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-8 text-center">
                        <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 20"/></svg></span>
                        <h4 class="mt-3 text-sm font-semibold text-gray-900">No images added yet</h4><p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Choose one or more images above to start your home page slideshow.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="{{ asset('backend/banners/home-banner-cropper.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" referrerpolicy="no-referrer"></script>
<script src="{{ asset('backend/banners/home-banner-manager.js') }}"></script>
@endsection
