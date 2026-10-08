@extends('frontend.layouts.app')
@section('title', $category?->exists ? $category->name.' Blog' : 'Blog')
@section('meta_description', 'Read our latest stories, news, guides, and updates.')
@section('content')
<div class="blog-intro"><p class="site-eyebrow">Stories & insights</p><h1>{{ $category?->exists ? $category->name : 'From our journal' }}</h1><p>Discover the latest news, ideas, and updates from our team.</p></div>
<nav id="blog-categories" class="blog-categories" aria-label="Blog categories"><a href="{{ route('blog.index') }}#blog-categories" @if(!$category?->exists) aria-current="page" @endif>All stories</a>@foreach($categories as $item)<a href="{{ route('blog.category', $item->slug) }}#blog-categories" @if($category?->id === $item->id) aria-current="page" @endif>{{ $item->name }}</a>@endforeach</nav>
<div class="blog-grid">
    @forelse($posts as $article)
        <article class="blog-card"><a href="{{ $article->public_url }}" class="blog-card-image">@if($article->image_url)<img src="{{ $article->image_url }}" alt="{{ $article->search_description }}" loading="lazy" width="800" height="450">@else<span class="blog-image-placeholder" aria-hidden="true">{{ $article->category->name }}</span>@endif</a><div class="blog-card-body"><p class="blog-card-meta">{{ $article->category->name }} <span aria-hidden="true">·</span> <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('M j, Y') }}</time></p><h2><a href="{{ $article->public_url }}">{{ $article->title }}</a></h2><p>{{ \Illuminate\Support\Str::limit($article->excerpt ?: html_entity_decode(strip_tags($article->content), ENT_QUOTES, 'UTF-8'), 170) }}</p><a href="{{ $article->public_url }}" class="blog-read-more" aria-label="Read {{ $article->title }}">Read story <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div></article>
    @empty<div class="blog-empty"><h2>New stories are on the way</h2><p>Check back soon for our latest news and updates.</p></div>@endforelse
</div>
@if($posts->hasPages())<div class="blog-pagination">{{ $posts->links() }}</div>@endif
@endsection
