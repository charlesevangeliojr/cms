<section class="site-hero" data-hero aria-label="Featured highlights" aria-roledescription="carousel" tabindex="0">
    @forelse($banners ?? [] as $banner)
        <article class="site-slide" data-slide @if(!$loop->first) hidden @endif aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
            <img class="site-hero-image" src="{{ $banner->image_url ?: asset('images/login-background.png') }}" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}" @if($loop->first) fetchpriority="high" @endif>
            <div class="site-container site-hero-content">
                <p class="site-eyebrow">{{ $site['name'] ?? 'CMS Template' }}</p>
                <h1 class="site-hero-title">{{ $banner->title }}</h1>
                <p class="site-hero-description">{{ $banner->description }}</p>
                <a class="site-button" href="#contact">Get in Touch <span aria-hidden="true">›</span></a>
            </div>
        </article>
    @empty
        <article class="site-slide" data-slide>
            <img class="site-hero-image" src="{{ asset('images/login-background.png') }}" alt="" fetchpriority="high">
            <div class="site-container site-hero-content">
                <p class="site-eyebrow">Welcome to {{ $site['name'] ?? 'CMS Template' }}</p>
                <h1 class="site-hero-title">Discover more.<br><span>Stay connected.</span></h1>
                <p class="site-hero-description">{{ $page['text'] ?? 'Explore our latest updates and get in touch with our team.' }}</p>
            </div>
        </article>
    @endforelse
    @if(count($banners ?? []) > 1)
        <div class="site-hero-controls" data-hero-controls hidden>
            <button class="site-hero-arrow site-hero-prev" type="button" data-previous aria-label="Previous banner">‹</button>
            <button class="site-hero-arrow site-hero-next" type="button" data-next aria-label="Next banner">›</button>
            <div class="site-container site-hero-pagination">
                @foreach($banners as $banner)
                    <button type="button" class="site-hero-dot" data-slide-to="{{ $loop->index }}" aria-label="Show banner {{ $loop->iteration }}: {{ $banner->title }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <span class="site-sr-only" data-hero-status role="status" aria-live="polite"></span>
        </div>
    @endif
</section>
