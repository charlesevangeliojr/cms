@extends('backend.layouts.sidebar')

@section('title', 'Blog Categories')

@section('content')
@php
    $canAdd = auth()->user()->canAccess('blogs', 'add');
    $canDelete = auth()->user()->canAccess('blogs', 'delete');
@endphp
<div class="mx-auto max-w-6xl space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('blogs.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Blog posts</a>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">Blog categories</h2>
            <p class="mt-1 text-sm text-gray-500">Create categories for organizing blog posts. Categories with posts cannot be removed.</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">{{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }}</span>
    </header>

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="font-semibold text-gray-900">Existing categories</h3>
                <p class="mt-1 text-sm text-gray-500">Category names appear on the public blog and in the post editor.</p>
            </div>
            @if($categories->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($categories as $category)
                        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <h4 class="truncate font-semibold text-gray-900">{{ $category->name }}</h4>
                                <p class="mt-1 truncate text-xs text-gray-500">/blogs/{{ $category->slug }} <span class="px-1">·</span> {{ $category->posts_count }} {{ \Illuminate\Support\Str::plural('post', $category->posts_count) }}</p>
                            </div>
                            @if($canDelete)
                                @if($category->posts_count === 0 && $categories->count() > 1)
                                    <form method="POST" action="{{ route('blog-categories.destroy', $category) }}" data-turbo="false" data-confirm="Delete the {{ $category->name }} category? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-rose-200 px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                    </form>
                                @else
                                    <span class="inline-flex min-h-10 items-center rounded-xl bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-500">{{ $category->posts_count ? 'In use' : 'Keep one category' }}</span>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="font-semibold text-gray-900">No categories yet</p>
                    <p class="mt-1 text-sm text-gray-500">Add a category to start organizing your posts.</p>
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                <h3 class="font-semibold text-gray-900">Add a category</h3>
                <p class="mt-1 text-sm text-gray-500">The public URL is generated from the category name.</p>
            </div>
            @if($canAdd)
                <form method="POST" action="{{ route('blog-categories.store') }}" class="space-y-4 p-5" data-turbo="false">
                    @csrf
                    <div>
                        <label for="category-name" class="mb-1.5 block text-sm font-semibold text-gray-700">Category name</label>
                        <input id="category-name" name="name" value="{{ old('name') }}" maxlength="100" required placeholder="e.g. Company news" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        @error('name')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                        @error('slug')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Add category</button>
                </form>
            @else
                <p class="p-5 text-sm text-gray-500">You have view-only access and cannot add categories.</p>
            @endif
        </section>
    </div>
</div>
@endsection
