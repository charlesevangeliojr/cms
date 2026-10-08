@extends('backend.layouts.sidebar')

@section('title', 'Meta Tags')

@section('content')
@php($canEdit = auth()->user()->canAccess('metadata', 'edit'))
<div class="mx-auto max-w-7xl space-y-6" data-metadata-page>
    <header class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-7">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7.25"/><path stroke-linecap="round" d="m16.5 16.5 4 4M8 11h6M11 8v6"/></svg>
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[.16em] text-indigo-600">Website settings</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Meta Tags</h2>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Control the default search and social-sharing details shown when a page does not have its own metadata.</p>
            </div>
        </div>
        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Search indexing is on
        </span>
    </header>

    @if(!$canEdit)
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 11v5m0-8h.01"/></svg>
            <p>You have view-only access. Ask an administrator with edit access to change these settings.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('seo-metadata.update') }}" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_420px]" data-turbo="false" data-metadata-form>
        @csrf
        @method('PUT')

        <div class="min-w-0 space-y-6">
            <fieldset @disabled(!$canEdit) class="min-w-0 space-y-6">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h3 class="font-semibold text-slate-900">Site identity</h3>
                        <p class="mt-1 text-sm text-slate-500">The name that identifies your website in browser and social search details.</p>
                    </div>
                    <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
                        <div class="sm:col-span-2">
                            <label for="site_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Site name</label>
                            <input id="site_name" name="site_name" value="{{ old('site_name', $metadata->site_name) }}" maxlength="100" required data-preview-site-name class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <p class="mt-1.5 text-xs text-slate-500">Appended to page titles and used as the social-sharing site name.</p>
                            @error('site_name')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        @foreach(['favicon' => ['Favicon', 'image/png,image/x-icon,image/vnd.microsoft.icon', 'PNG or ICO · max 1 MB · recommended 32 × 32 px.', $metadata->favicon ?: 'images/cms-logo.png'], 'image' => ['Social preview image', 'image/jpeg,image/png,image/webp', 'JPG, PNG, or WebP · max 2 MB · recommended 1200 × 630 px.', $metadata->image]] as $field => [$label, $accept, $help, $path])
                            <div class="min-w-0 {{ $field === 'image' ? 'sm:col-span-2' : '' }}">
                                <label for="{{ $field }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="file" accept="{{ $accept }}" aria-describedby="{{ $field }}-help" data-image-input class="block w-full rounded-xl border border-slate-300 bg-white text-sm file:mr-3 file:border-0 file:bg-slate-100 file:px-4 file:py-3 file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                <p id="{{ $field }}-help" class="mt-2 text-xs leading-5 text-slate-500">{{ $help }} Leave empty to keep the current image.</p>
                                @error($field)<p role="alert" class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                                <div class="mt-3 flex {{ $field === 'favicon' ? 'h-24' : 'h-44' }} items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3">
                                    <img data-image-preview @if($field === 'image') data-social-image-preview @endif src="{{ asset($path) }}" alt="Current {{ $label }} preview" class="{{ $field === 'favicon' ? 'h-12 w-12' : 'h-full w-full' }} object-contain">
                                </div>
                                <p data-upload-status role="status" class="mt-1.5 text-xs text-slate-500">Current image</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h3 class="font-semibold text-slate-900">Search result details</h3>
                        <p class="mt-1 text-sm text-slate-500">Clear, accurate summaries help visitors understand what your website offers.</p>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="title" class="text-sm font-semibold text-slate-700">Meta title</label>
                                <span class="text-xs tabular-nums text-slate-500"><span data-character-count="title">{{ mb_strlen(old('title', $metadata->title)) }}</span>/150</span>
                            </div>
                            <input id="title" name="title" value="{{ old('title', $metadata->title) }}" maxlength="150" required data-preview-title class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <p class="mt-1.5 text-xs text-slate-500">This is the default title used in search results. Individual pages may have their own title.</p>
                            @error('title')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="description" class="text-sm font-semibold text-slate-700">Meta description</label>
                                <span class="text-xs tabular-nums text-slate-500"><span data-character-count="description">{{ mb_strlen(old('description', $metadata->description)) }}</span>/320</span>
                            </div>
                            <textarea id="description" name="description" rows="4" maxlength="320" required data-preview-description class="w-full resize-y rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('description', $metadata->description) }}</textarea>
                            <p class="mt-1.5 text-xs text-slate-500">Use plain language to summarize the site. Maximum 320 characters.</p>
                            @error('description')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h3 class="font-semibold text-slate-900">Social sharing</h3>
                        <p class="mt-1 text-sm text-slate-500">Optional Open Graph overrides control how links look when shared.</p>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div>
                            <label for="og_title" class="mb-1.5 block text-sm font-semibold text-slate-700">Social title <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="og_title" name="og_title" value="{{ old('og_title', $metadata->og_title) }}" maxlength="150" data-preview-og-title class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <p class="mt-1.5 text-xs text-slate-500">Leave blank to use the meta title.</p>
                            @error('og_title')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="og_description" class="text-sm font-semibold text-slate-700">Social description <span class="font-normal text-slate-400">(optional)</span></label>
                                <span class="text-xs tabular-nums text-slate-500"><span data-character-count="og_description">{{ mb_strlen(old('og_description', $metadata->og_description ?? '')) }}</span>/320</span>
                            </div>
                            <textarea id="og_description" name="og_description" rows="3" maxlength="320" data-preview-og-description class="w-full resize-y rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('og_description', $metadata->og_description) }}</textarea>
                            <p class="mt-1.5 text-xs text-slate-500">Leave blank to use the meta description. Also used for Twitter / X cards.</p>
                            @error('og_description')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6 [&::-webkit-details-marker]:hidden">
                        <span><span class="block font-semibold text-slate-900">Meta keywords</span><span class="mt-1 block text-sm text-slate-500">Optional keywords associated with your site.</span></span>
                        <svg class="h-5 w-5 shrink-0 text-slate-500 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </summary>
                    <div class="border-t border-slate-100 p-5 sm:p-6">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-slate-700">Add keywords</p>
                            <button type="button" data-add-keyword class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">+ Add keyword</button>
                        </div>
                        <div data-keywords class="space-y-2">
                            @foreach(old('keywords', $metadata->keywords ?: ['']) as $keyword)
                                <div class="flex items-center gap-2" data-keyword-row>
                                    <input id="keyword-{{ $loop->index }}" name="keywords[]" value="{{ $keyword }}" maxlength="80" placeholder="e.g. services, community" aria-label="Meta keyword {{ $loop->iteration }}" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <button type="button" data-remove-keyword aria-label="Remove keyword {{ $loop->iteration }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-rose-50 hover:text-rose-700">Remove</button>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs leading-5 text-slate-500">Up to 30 keywords, 80 characters each. Search engines may give keywords little or no ranking weight.</p>
                        @error('keywords')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        @foreach($errors->get('keywords.*') as $messages) @foreach($messages as $message)<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@endforeach @endforeach
                    </div>
                </details>
            </fieldset>

            <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-xs leading-5 text-slate-500">Changes apply to pages using the site-wide defaults.</p>
                @if($canEdit)
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 3.75A1.75 1.75 0 0 1 4.75 2h8.19c.46 0 .9.18 1.22.51l2.33 2.33c.33.32.51.76.51 1.22v9.19A1.75 1.75 0 0 1 15.25 17h-10.5A1.75 1.75 0 0 1 3 15.25V3.75Zm3.5-.25v4h7v-4h-7Zm3.5 12.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"/></svg>
                        Save settings
                    </button>
                @endif
            </div>
        </div>

        <aside class="space-y-5 xl:sticky xl:top-24">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="search-preview-heading">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 id="search-preview-heading" class="font-semibold text-slate-900">Search preview</h3>
                    <p class="mt-1 text-xs text-slate-500">An example of your default search listing.</p>
                </div>
                <div class="p-5">
                    <p class="truncate text-xs text-emerald-800">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourwebsite.com' }} › Home</p>
                    <p data-preview-search-title class="mt-2 line-clamp-2 text-2xl leading-7 text-blue-800">{{ old('title', $metadata->title) }} | {{ old('site_name', $metadata->site_name) }}</p>
                    <p data-preview-search-description class="mt-2 line-clamp-4 text-base leading-6 text-slate-600">{{ old('description', $metadata->description) }}</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="social-preview-heading">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 id="social-preview-heading" class="font-semibold text-slate-900">Social share preview</h3>
                    <p class="mt-1 text-xs text-slate-500">Preview of the default link card.</p>
                </div>
                <div class="overflow-hidden bg-slate-50">
                    <div class="aspect-[1.91/1] bg-slate-100">
                        <img data-social-card-image src="{{ asset($metadata->image) }}" alt="Social sharing card image preview" class="h-full w-full object-cover">
                    </div>
                    <div class="space-y-1 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourwebsite.com' }}</p>
                        <p data-preview-social-title class="line-clamp-2 text-lg font-semibold leading-6 text-slate-900">{{ old('og_title', $metadata->og_title) ?: old('title', $metadata->title) }}</p>
                        <p data-preview-social-description class="line-clamp-4 text-base leading-6 text-slate-600">{{ old('og_description', $metadata->og_description) ?: old('description', $metadata->description) }}</p>
                    </div>
                </div>
            </section>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
                <h3 class="text-sm font-semibold text-indigo-950">Good to know</h3>
                <ul class="mt-3 space-y-2 text-xs leading-5 text-indigo-900">
                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>Specific pages and blog posts can use their own metadata.</span></li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>Keep titles and descriptions accurate and easy to scan.</span></li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>The site is always set to index and follow links.</span></li>
                </ul>
            </div>
        </aside>
    </form>
</div>
<script src="{{ asset('backend/metadata/settings.js') }}"></script>
@endsection
