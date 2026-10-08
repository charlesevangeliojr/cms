@extends('backend.layouts.sidebar')
@section('title', $blog->exists ? 'Edit blog post' : 'Add blog post')
@section('content')
@php
    $editing = $blog->exists;
    $field = 'w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $label = 'mb-1.5 block text-sm font-semibold text-gray-800';
@endphp
<link rel="stylesheet" href="{{ asset('backend/blog-editor.css') }}">
<form id="blog-post-form" method="POST" action="{{ $editing ? route('blogs.update', $blog) : route('blogs.store') }}" enctype="multipart/form-data" data-turbo="false" data-blog-form class="space-y-6">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div><a href="{{ route('blogs.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Blog posts</a><h2 class="mt-1 text-2xl font-bold">{{ $editing ? 'Edit blog post' : 'Add blog post' }}</h2></div>
        <div class="flex gap-3">
            @if($editing && $blog->is_visible && $blog->published_at?->isPast())<a href="{{ $blog->public_url }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold">View post ↗</a>@endif
            <a href="{{ route('blogs.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold">Cancel</a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Save post</button>
        </div>
    </div>
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <div class="min-w-0 space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <label for="blog-title" class="{{ $label }}">Title</label>
                <input id="blog-title" name="title" value="{{ old('title', $blog->title) }}" required maxlength="255" placeholder="e.g., Our latest news and stories" class="{{ $field }}">
                <label for="blog-content" class="{{ $label }} mt-5">Content</label>
                <div class="blog-editor-shell">
                    <textarea id="blog-content" name="content" required class="blog-content-source" placeholder="Write your article…">{{ old('content', $blog->content) }}</textarea>
                </div>
                <p class="mt-2 text-xs text-gray-500">Use headings, lists, and links to make your article easy to read.</p>
                <p data-content-error hidden role="alert" class="mt-2 text-sm text-red-600">Please add content to your blog post.</p>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <label for="blog-excerpt" class="{{ $label }}">Excerpt</label>
                <p class="mb-3 text-sm text-gray-500">Add a short summary for the blog listing.</p>
                <textarea id="blog-excerpt" name="excerpt" rows="3" maxlength="2000" class="{{ $field }}" placeholder="What will readers discover in this post?">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-5 sm:p-6">
                    <h3 class="font-semibold">Search engine listing</h3>
                    <p class="mt-1 text-sm text-gray-500">Preview the title, link, and description a search engine could show.</p>
                    <div class="mt-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm" aria-label="Search result preview">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/cms-logo.png') }}" alt="" class="h-7 w-7 rounded-full border border-gray-100 object-contain">
                            <div class="min-w-0"><p class="truncate text-xs font-medium text-gray-800">{{ config('metadata.defaults.site_name', config('app.name')) }}</p><p data-seo-url class="truncate text-xs text-gray-500"></p></div>
                        </div>
                        <p data-seo-title class="mt-3 break-words text-lg leading-snug text-blue-800"></p>
                        <p data-seo-description class="mt-1 break-words text-sm leading-relaxed text-gray-600"></p>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-gray-500">Search engines may change how they display this preview. A good page title is clear and specific; a useful description summarizes the article.</p>
                </div>
                <div class="space-y-5 p-5 sm:p-6">
                    <div><label for="seo-title" class="{{ $label }}">Page title <span class="font-normal text-gray-500">(optional)</span></label><input id="seo-title" name="seo_title" maxlength="70" value="{{ old('seo_title', $blog->seo?->seo_title) }}" class="{{ $field }}" placeholder="Leave blank to use the post title"><p class="mt-1.5 text-xs leading-relaxed text-gray-500">This is the search/browser title. If left blank, the post’s <strong>Title</strong> above is used automatically.</p><p data-title-count class="mt-1 text-xs text-gray-500"></p></div>
                    <div><label for="meta-description" class="{{ $label }}">Meta description <span class="font-normal text-gray-500">(optional)</span></label><textarea id="meta-description" name="meta_description" maxlength="160" rows="3" class="{{ $field }}" placeholder="Leave blank to use the excerpt">{{ old('meta_description', $blog->seo?->meta_description) }}</textarea><p class="mt-1.5 text-xs leading-relaxed text-gray-500">If blank, the post excerpt is used; if there is no excerpt, a summary is taken from the article content.</p><p data-description-count class="mt-1 text-xs text-gray-500"></p></div>
                    <div><label for="blog-slug" class="{{ $label }}">URL handle</label><div class="flex items-center overflow-hidden rounded-xl border border-gray-300 bg-white"><span data-slug-prefix class="shrink-0 pl-3 text-sm text-gray-500">blogs/news/</span><input id="blog-slug" name="slug" maxlength="180" value="{{ old('slug', $blog->slug) }}" class="min-w-0 flex-1 border-0 bg-transparent px-2 py-2.5 text-sm focus:outline-none" placeholder="your-post-title"></div><p class="mt-1.5 text-xs text-gray-500">A unique handle is generated from your title when left blank.</p>@if($editing)<p class="mt-1 text-xs text-amber-700">Changing the handle or blog changes this post’s URL.</p>@endif</div>
                </div>
            </section>
        </div>
        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h3 class="mb-4 font-semibold">Visibility</h3><div class="space-y-3">@foreach([1 => 'Visible', 0 => 'Hidden'] as $value => $text)<label class="flex items-center gap-2 text-sm"><input type="radio" name="is_visible" value="{{ $value }}" @checked((int) old('is_visible', $blog->is_visible ?? false) === $value) class="h-4 w-4 accent-indigo-600">{{ $text }}</label>@endforeach</div><p class="mt-4 text-xs leading-relaxed text-gray-500">Visible posts appear on your website. Hidden posts are saved privately.</p></section>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h3 class="mb-1 font-semibold">Thumbnail / sharing image</h3><p class="mb-4 text-xs leading-relaxed text-gray-500">Used on blog cards and as the image shown when the post link is shared.</p>
                <div class="blog-image-upload" data-image-drop>
                    <img data-image-preview @if($blog->image_url) src="{{ $blog->image_url }}" @endif alt="Thumbnail preview" @if(!$blog->image_url) hidden @endif class="blog-image-preview blog-thumbnail-preview mb-4 w-full rounded-lg object-cover">
                    <svg class="mx-auto mb-3 h-9 w-9 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 7.5 12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                    <p class="text-sm font-semibold text-gray-800">Drop the thumbnail / share image here</p>
                    <p class="mt-1 text-xs text-gray-500">or</p>
                    <label for="blog-image" class="mt-3 inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">{{ $blog->image_path ? 'Choose a different image' : 'Browse images' }}</label>
                    <input id="blog-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-max-bytes="2097152" class="sr-only">
                    <p class="mt-3 text-xs text-gray-500">JPG, PNG, or WebP · maximum 2 MB</p>
                    <p class="mt-1 text-xs text-gray-400">Recommended: 1200 × 630 px (wide social preview)</p>
                    <p data-image-error hidden role="alert" class="mt-3 text-xs font-medium text-rose-600"></p>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-500">Image alt text is automatically taken from this post’s meta description.</p>
            </section>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h3 class="mb-1 font-semibold">Image inside the blog post</h3><p class="mb-4 text-xs leading-relaxed text-gray-500">This separate image is displayed beneath the article heading, inside the post.</p>
                <div class="blog-image-upload" data-image-drop>
                    <img data-image-preview @if($blog->article_image_url) src="{{ $blog->article_image_url }}" @endif alt="Article image preview" @if(!$blog->article_image_url) hidden @endif class="blog-image-preview mb-4 w-full rounded-lg object-cover">
                    <svg class="mx-auto mb-3 h-9 w-9 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 7.5 12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                    <p class="text-sm font-semibold text-gray-800">Drop the in-post image here</p>
                    <p class="mt-1 text-xs text-gray-500">or</p>
                    <label for="blog-article-image" class="mt-3 inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">{{ $blog->article_image_path ? 'Choose a different image' : 'Browse images' }}</label>
                    <input id="blog-article-image" type="file" name="article_image" accept="image/jpeg,image/png,image/webp" data-max-bytes="2097152" class="sr-only">
                    <p class="mt-3 text-xs text-gray-500">JPG, PNG, or WebP · maximum 2 MB</p>
                    <p class="mt-1 text-xs text-gray-400">Recommended: 1200 × 675 px (16:9 landscape)</p>
                    <p data-image-error hidden role="alert" class="mt-3 text-xs font-medium text-rose-600"></p>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-500">Alt text uses the post’s meta description.</p>
            </section>
            <section class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h3 class="font-semibold">Organization</h3>
                <div><label for="blog-author" class="{{ $label }}">Author</label><input id="blog-author" name="author" required maxlength="255" value="{{ old('author', $blog->author ?? auth()->user()->name) }}" class="{{ $field }}"></div>
                <div><label for="blog-category" class="{{ $label }}">Blog</label><select id="blog-category" name="blog_category_id" required class="{{ $field }}">@foreach($categories as $category)<option value="{{ $category->id }}" data-slug="{{ $category->slug }}" @selected((string) old('blog_category_id', $blog->blog_category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="relative">
                    <label for="blog-tags" class="{{ $label }}">Tags</label>
                    <input id="blog-tags" name="tags" maxlength="1000" value="{{ old('tags', implode(', ', $blog->tags ?? [])) }}" class="{{ $field }}" placeholder="Type tags separated by commas">
                    <div id="blog-tag-suggestions" role="listbox" aria-label="Matching existing tags" hidden class="absolute left-0 right-0 top-full z-30 mt-1 max-h-40 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1 shadow-lg"></div>
                    <p class="mt-1.5 text-xs text-gray-500">Separate multiple tags with commas.</p>
                </div>
            </section>
        </aside>
    </div>
    <div class="flex justify-end border-t border-gray-200 pt-5">
        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Save post</button>
    </div>
</form>
<script>
    (() => {
        const input = document.querySelector('#blog-tags');
        const suggestions = document.querySelector('#blog-tag-suggestions');
        const availableTags = {{ Illuminate\Support\Js::from($availableTags) }};
        if (!input || !suggestions) return;

        function hideSuggestions() {
            suggestions.hidden = true;
            suggestions.replaceChildren();
        }

        input.addEventListener('input', () => {
            const parts = input.value.split(',');
            const query = parts.at(-1).trim().toLowerCase();
            if (!query) return hideSuggestions();

            const alreadyEntered = parts.slice(0, -1).map(tag => tag.trim().toLowerCase());
            const matches = availableTags
                .filter(tag => tag.toLowerCase().includes(query) && !alreadyEntered.includes(tag.toLowerCase()))
                .slice(0, 8);
            suggestions.replaceChildren();
            matches.forEach(tag => {
                const option = document.createElement('button');
                option.type = 'button';
                option.role = 'option';
                option.className = 'block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700';
                option.textContent = tag;
                option.addEventListener('mousedown', event => event.preventDefault());
                option.addEventListener('click', () => {
                    parts[parts.length - 1] = ` ${tag}`;
                    input.value = `${parts.join(',').replace(/^\s+/, '')}, `;
                    hideSuggestions();
                    input.focus();
                });
                suggestions.append(option);
            });
            suggestions.hidden = matches.length === 0;
        });

        input.addEventListener('blur', () => setTimeout(hideSuggestions, 120));
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') hideSuggestions();
        });
    })();
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js" defer></script>
<script src="{{ asset('backend/blog-editor.js') }}" defer></script>
@endsection
