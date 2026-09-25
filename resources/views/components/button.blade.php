@props(['href' => null, 'type' => 'button', 'variant' => 'primary'])

@php
$styles = [
    'primary' => 'bg-red-600 text-white hover:bg-red-700',
    'dark' => 'bg-gray-900 text-white hover:bg-gray-700',
    'light' => 'bg-gray-100 text-gray-900 hover:bg-gray-200',
];
$classes = 'inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium transition ' . ($styles[$variant] ?? $styles['primary']) . ' ' . ($attributes->get('class') ?? '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
