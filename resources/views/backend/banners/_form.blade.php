@php($editing = isset($banner) && $banner->exists)

@if ($errors->any())
    <x-alert type="error">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Banner Details</h3>
        </div>
        <div class="space-y-5 p-6">
            <div>
                <label for="title" class="mb-1.5 block text-sm font-semibold text-gray-900">Title *</label>
                <input id="title" name="title" type="text" value="{{ old('title', $banner->title ?? '') }}" required maxlength="255"
                       placeholder="e.g. Autumn Promotional Hero Banner"
                       class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="position" class="mb-1.5 block text-sm font-semibold text-gray-900">Position *</label>
                <select id="position" name="position" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Select a position</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position }}" @selected(old('position', $banner->position ?? '') === $position)>
                            {{ $position }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="target_url" class="mb-1.5 block text-sm font-semibold text-gray-900">Target URL *</label>
                <input id="target_url" name="target_url" type="text" value="{{ old('target_url', $banner->target_url ?? '') }}" required maxlength="2048"
                       placeholder="/promotions/autumn or https://example.com/promotion"
                       class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <p class="mt-1.5 text-xs text-gray-400">Use an internal path or an absolute HTTP/HTTPS URL.</p>
            </div>

            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Active Banner</p>
                    <p class="mt-0.5 text-xs text-gray-500">Inactive banners are hidden from the public site.</p>
                </div>
                <label class="inline-flex cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" aria-label="Active banner" name="is_active" value="1" class="peer sr-only"
                           @checked(old('is_active', $banner->is_active ?? true))>
                    <span class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-white transition peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 peer-checked:bg-emerald-600 peer-checked:[&_svg]:opacity-100">
                        <svg class="h-4 w-4 opacity-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Banner Image</h3>
        </div>
        <div class="space-y-5 p-6">
            @if ($editing && $banner->image_path)
                <div>
                    <p class="mb-2 text-sm font-semibold text-gray-900">Current image</p>
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                         class="h-52 w-full rounded-lg border border-gray-200 object-cover shadow-sm">
                </div>
            @endif

            <div>
                <label for="image" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    {{ $editing ? 'Replacement image' : 'Banner image *' }}
                </label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)
                       class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-600 file:mr-4 file:cursor-pointer file:border-0 file:bg-gray-200 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-100">
                <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, or WebP up to 2 MB{{ $editing ? '. Leave empty to keep the current image.' : '.' }}</p>
            </div>
        </div>
    </div>
</div>
