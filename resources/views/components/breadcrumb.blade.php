@props(['items' => [], 'inBanner' => false])

<nav @class(['site-breadcrumb', 'site-container' => !$inBanner, 'site-breadcrumb-banner' => $inBanner]) aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Home</a>
    @foreach($items as $item)
        <span class="site-breadcrumb-separator" aria-hidden="true">›</span>
        @if(!$loop->last && !empty($item['url']))
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
            <span aria-current="page">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
