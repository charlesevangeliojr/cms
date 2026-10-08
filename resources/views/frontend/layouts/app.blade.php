<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">

    @php
        $siteMetadata = \App\Models\SiteMetadata::current();
        $metadata = array_merge(
            config('metadata.defaults', []),
            $siteMetadata->only(['site_name', 'title', 'description', 'image', 'favicon', 'keywords', 'og_title', 'og_description']),
            config('metadata.pages.'.(request()->route()?->getName() ?? ''), []),
        );
        $metaTitle = request()->routeIs('home') ? $siteMetadata->title : trim($__env->yieldContent('title', $metadata['title'] ?? 'CMS Template'));
        $metaDescription = request()->routeIs('home') ? $siteMetadata->description : trim($__env->yieldContent('meta_description', $metadata['description'] ?? ''));
        if ($metaTitle !== ($metadata['site_name'] ?? 'CMS Template')) {
            $metaTitle .= ' | '.($metadata['site_name'] ?? 'CMS Template');
        }
        $ogTitle = isset($post) ? $metaTitle : trim($__env->yieldContent('og_title', $metadata['og_title'] ?: $metaTitle));
        $ogDescription = isset($post) ? $metaDescription : trim($__env->yieldContent('og_description', $metadata['og_description'] ?: $metaDescription));
        $canonicalUrl = isset($post) ? $post->public_url : url()->current();
        if (isset($posts) && $posts->currentPage() > 1) {
            $canonicalUrl .= '?page='.$posts->currentPage();
        }
        $metaImage = isset($post) && $post->image_url ? $post->image_url : asset($metadata['image'] ?? 'images/cms-logo.png');
    @endphp

    <title>{{ $metaTitle }}</title>
    @if ($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <meta name="robots" content="index, follow">
    @if (!empty($metadata['keywords']))
        <meta name="keywords" content="{{ implode(', ', $metadata['keywords']) }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="{{ isset($post) ? 'article' : 'website' }}">
    <meta property="og:site_name" content="{{ $metadata['site_name'] ?? 'CMS Template' }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    @if ($ogDescription !== '')
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    @if ($ogDescription !== '')
        <meta name="twitter:description" content="{{ $ogDescription }}">
    @endif
    <meta name="twitter:image" content="{{ $metaImage }}">
    @yield('structured-data')

    <link rel="icon" href="{{ asset($siteMetadata->favicon ?: 'images/cms-logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('frontend/site.css') }}">
    <script src="{{ asset('frontend/site.js') }}" defer></script>

    @if (config('services.google_analytics.measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode(config('services.google_analytics.measurement_id')) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json(config('services.google_analytics.measurement_id')));
        </script>
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>
    <script type="module">
        import * as Turbo from 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.23/+esm';
        Turbo.start();
    </script>
</head>
<body class="public-site font-sans antialiased text-gray-900">
    <a class="site-skip" href="#main-content">Skip to content</a>
    @include('frontend.partials.header')

    {{-- Home banners are exclusive to the home page; Pages banners are shared by all other public pages. --}}
    @php
        $layoutBannerSlug = request()->routeIs('home') ? 'home' : request()->segment(1);
        $layoutBannerPage = \App\Models\BannerPage::where('slug', $layoutBannerSlug)
            ->where('is_active', true)
            ->first();
        $layoutBanners = $banners ?? ($layoutBannerPage
            ? \App\Models\PageBanner::where('is_active', true)->where('banner_page_id', $layoutBannerPage->id)->with('page')->latest('id')->get()
            : collect());
    @endphp
    @php
        $breadcrumbItems = [];
        if (request()->routeIs('about')) {
            $breadcrumbItems = [['label' => 'About Us']];
        } elseif (request()->routeIs('contact')) {
            $breadcrumbItems = [['label' => 'Contact Us']];
        } elseif (request()->routeIs('blog.index')) {
            $breadcrumbItems = [['label' => 'Blog']];
        } elseif (request()->routeIs('blog.category')) {
            $breadcrumbItems = [
                ['label' => 'Blog', 'url' => route('blog.index')],
                ['label' => $category?->name ?? 'Category'],
            ];
        } elseif (request()->routeIs('blog.show')) {
            $breadcrumbItems = [
                ['label' => 'Blog', 'url' => route('blog.index')],
                ['label' => $post->category->name, 'url' => route('blog.category', $post->category->slug)],
                ['label' => $post->title],
            ];
        }
    @endphp

    @if (isset($banners) || $layoutBannerPage)
        @include('frontend.sections.hero', ['banners' => $layoutBanners])
    @endif
    @if($breadcrumbItems && ! (isset($banners) || $layoutBannerPage))<x-breadcrumb :items="$breadcrumbItems" />@endif
    <main id="main-content" class="@yield('main-class', 'site-container site-page')">
        @yield('content')
    </main>

    @include('frontend.partials.footer')
    @include('shared.notifications')
</body>
</html>
