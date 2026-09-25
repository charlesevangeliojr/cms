@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow p-6']) }}>
    @if ($title)
        <h2 class="text-2xl font-bold mb-2">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>
