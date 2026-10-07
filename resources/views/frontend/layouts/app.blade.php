<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">

    @php
        $metadata = array_merge(
            config('metadata.defaults', []),
            config('metadata.pages.'.(request()->route()?->getName() ?? ''), []),
        );
        $metaTitle = trim($__env->yieldContent('title', $metadata['title'] ?? 'CMS Template'));
        $metaDescription = trim($__env->yieldContent('meta_description', $metadata['description'] ?? ''));
        $metaTitle = $metaTitle.' | '.($metadata['site_name'] ?? 'CMS Template');
        $canonicalUrl = request()->route()?->getName()
            ? route(request()->route()->getName())
            : url()->current();
        $metaImage = asset($metadata['image'] ?? 'images/cms-logo.png');
    @endphp

    <title>{{ $metaTitle }}</title>
    @if ($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    <meta name="robots" content="{{ $metadata['robots'] ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $metadata['site_name'] ?? 'CMS Template' }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    @if ($metaDescription !== '')
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @if ($metaDescription !== '')
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
    <meta name="twitter:image" content="{{ $metaImage }}">

    <link rel="icon" type="image/png" href="{{ asset('images/cms-logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('frontend/site.css') }}">
    <script src="{{ asset('frontend/site.js') }}" defer></script>

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

    <main id="main-content" class="@yield('main-class', 'site-container site-page')">
        @yield('content')
    </main>

    @include('frontend.partials.footer')
    @include('backend.partials.notifications')
</body>
</html>
