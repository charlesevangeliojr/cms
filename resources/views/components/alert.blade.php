@props(['type' => 'info'])

@php
$styles = [
    'error' => 'bg-red-50 border-red-200 text-red-700',
    'success' => 'bg-green-50 border-green-200 text-green-700',
    'info' => 'bg-blue-50 border-blue-200 text-blue-700',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border text-sm px-4 py-3 ' . ($styles[$type] ?? $styles['info'])]) }}>
    {{ $slot }}
</div>
