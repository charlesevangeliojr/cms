<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-8">
                <a href="{{ url('/') }}" class="flex items-center gap-2 font-bold text-lg text-red-600"><img src="{{ asset('images/cms-logo.png') }}" alt="CMS logo" class="h-8 w-8 object-contain">{{ $site['name'] ?? 'CMS Template' }}</a>
                <div class="flex gap-4 text-sm">
                    @foreach (($nav ?? [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about']]) as $item)
                        <a href="{{ url($item['url']) }}" class="px-3 py-2 rounded hover:bg-gray-100">{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</nav>
