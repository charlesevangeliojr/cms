@extends('frontend.layouts.app')
@section('title', $post->seo?->seo_title ?: $post->title)
@section('meta_description', $post->search_description)
@section('structured-data')
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    <meta property="article:author" content="{{ $post->author }}">
    @php
        $articleSchema = [
            '@context' => 'https://schema.org', '@type' => 'BlogPosting',
            'headline' => $post->title, 'description' => $post->search_description,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $post->public_url],
            'datePublished' => $post->published_at->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $post->author],
            'publisher' => ['@type' => 'Organization', 'name' => config('metadata.defaults.site_name', 'CMS Template')],
        ];
        if ($post->image_url) $articleSchema['image'] = [$post->image_url];
    @endphp
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
@section('content')
<article class="blog-article">
    <header class="blog-article-header"><p class="site-eyebrow">{{ $post->category->name }}</p><h1>{{ $post->title }}</h1><p class="blog-byline">By {{ $post->author }} <span aria-hidden="true">·</span> <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->format('F j, Y') }}</time></p></header>
    @if($post->article_image_url)<img class="blog-featured-image" src="{{ $post->article_image_url }}" alt="{{ $post->search_description }}" width="1200" height="675" fetchpriority="high">@endif
    <div class="blog-prose">{!! $safeContent !!}</div>
    @if($post->tags)<ul class="blog-tags" aria-label="Post tags">@foreach($post->tags as $tag)<li>{{ $tag }}</li>@endforeach</ul>@endif
    <a class="blog-back" href="{{ route('blog.index') }}">← Back to all stories</a>
</article>
@endsection
