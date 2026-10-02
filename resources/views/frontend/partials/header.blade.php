<header class="site-header">
    <div class="site-container site-nav">
        <a href="{{ route('home') }}" class="site-brand">
            <img src="{{ asset('images/cms-logo.png') }}" alt="" width="56" height="56">
            <span>{{ $site['name'] ?? 'CMS Template' }}</span>
        </a>
        <nav class="site-desktop-nav" aria-label="Main navigation">
            @foreach (($nav ?? [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about']]) as $item)
                <a href="{{ url($item['url']) }}" @if(url()->current() === url($item['url'])) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <a class="site-button site-header-contact" href="{{ route('home') }}#contact">Contact Us <span aria-hidden="true">›</span></a>
        <details class="site-mobile-nav">
            <summary>Menu <span aria-hidden="true">☰</span></summary>
            <nav aria-label="Mobile navigation">
                @foreach (($nav ?? [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about']]) as $item)
                    <a href="{{ url($item['url']) }}" @if(url()->current() === url($item['url'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('home') }}#contact">Contact Us</a>
            </nav>
        </details>
    </div>
</header>
