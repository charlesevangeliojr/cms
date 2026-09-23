{{-- Expects: $stats = [['value' => ..., 'label' => ...], ...] --}}
@if (!empty($stats))
    <div class="grid gap-4 md:grid-cols-3 mt-6">
        @foreach ($stats as $stat)
            <x-card>
                <p class="text-2xl font-bold">{{ $stat['value'] }}</p>
                <p class="text-gray-500 text-sm">{{ $stat['label'] }}</p>
            </x-card>
        @endforeach
    </div>
@endif
