@php($banners = collect($banners ?? []))
@php($primaryBanner = $banners->first())
@php($isPagesBanner = $primaryBanner instanceof \App\Models\PageBanner || ! request()->routeIs('home'))
<section @class(['site-hero', 'site-hero-pages' => $isPagesBanner]) data-hero aria-label="Featured highlights" aria-roledescription="carousel" tabindex="0">
    @forelse($banners ?? [] as $banner)
        <article class="site-slide" data-slide @if(!$loop->first) hidden @endif aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
            <img class="site-hero-image" src="{{ $banner->image_url ?: asset('images/login-background.png') }}" alt="" width="1600" height="{{ $isPagesBanner ? 300 : 600 }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" @if($loop->first) fetchpriority="high" @endif>
            @if($isPagesBanner)
                <div class="site-container site-hero-content">
                    @if(!empty($breadcrumbItems))<x-breadcrumb :items="$breadcrumbItems" :in-banner="true" />@endif
                    <h1 class="site-hero-title">{{ $banner->title }}</h1>
                </div>
            @endif
        </article>
    @empty
        <article class="site-slide" data-slide>
            <img class="site-hero-image" src="{{ asset($isPagesBanner ? 'images/page-banner-default.svg' : 'images/login-background.png') }}" alt="" width="1600" height="{{ $isPagesBanner ? 300 : 600 }}" fetchpriority="high">
            @if($isPagesBanner)
                <div class="site-container site-hero-content">
                    @if(!empty($breadcrumbItems))<x-breadcrumb :items="$breadcrumbItems" :in-banner="true" />@endif
                    <h1 class="site-hero-title">{{ $page['heading'] ?? $layoutBannerPage?->name ?? 'Welcome' }}</h1>
                </div>
            @else
                <div class="site-container site-hero-content">
                    <p class="site-eyebrow">{{ $page['heading'] ?? 'Welcome' }}</p>
                    <h1 class="site-hero-title">Discover more.<br><span>Stay connected.</span></h1>
                    <p class="site-hero-description">{{ $page['text'] ?? 'Explore our latest updates and get in touch with our team.' }}</p>
                </div>
            @endif
        </article>
    @endforelse
    @if(!$isPagesBanner && count($banners ?? []) > 0)
        <div class="site-container site-hero-content">
            <h1 class="site-hero-title">{{ $primaryBanner->title }}</h1>
            @unless($isPagesBanner)
                <p class="site-hero-description">{{ $primaryBanner->description }}</p>
                <a class="site-button" href="{{ route('contact') }}">Contact Us <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="m9 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg></a>
            @endunless
        </div>
    @endif
    @if(count($banners ?? []) > 1)
        <div class="site-hero-controls" data-hero-controls hidden>
            <button class="site-hero-arrow site-hero-prev" type="button" data-previous aria-label="Previous banner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="m15 6-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <button class="site-hero-arrow site-hero-next" type="button" data-next aria-label="Next banner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="m9 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <div class="site-container site-hero-pagination">
                @foreach($banners as $banner)
                    <button type="button" class="site-hero-dot" data-slide-to="{{ $loop->index }}" aria-label="Show banner {{ $loop->iteration }}: {{ $banner->title }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <span class="site-sr-only" data-hero-status role="status" aria-live="polite"></span>
        </div>
    @endif
</section>
