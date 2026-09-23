<footer class="border-t border-gray-200 bg-white mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p class="text-sm text-gray-500">&copy; {{ date('Y') }} {{ $site['name'] ?? 'CMS Template' }}. All rights reserved.</p>
        <div class="flex gap-4 text-sm text-gray-500">
            @foreach (($nav ?? [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about']]) as $item)
                <a href="{{ url($item['url']) }}" class="hover:text-gray-900">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </div>
</footer>
