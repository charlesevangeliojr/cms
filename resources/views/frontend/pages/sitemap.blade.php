{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach(['home', 'about', 'blog.index'] as $name)
        <url><loc>{{ route($name) }}</loc></url>
    @endforeach
    @foreach($posts as $post)
        <url><loc>{{ $post->public_url }}</loc><lastmod>{{ $post->updated_at->toIso8601String() }}</lastmod></url>
    @endforeach
</urlset>
