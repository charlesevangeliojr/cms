{{-- Expects: $features = [['title' => ..., 'text' => ...], ...] --}}
@if (!empty($features))
    <div class="grid gap-4 md:grid-cols-3 mt-6">
        @foreach ($features as $feature)
            <x-card :title="$feature['title']">
                <p class="text-gray-600 text-sm">{{ $feature['text'] }}</p>
            </x-card>
        @endforeach
    </div>
@endif
