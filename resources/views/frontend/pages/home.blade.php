@extends('frontend.layouts.app')

@section('title', $page['heading'] ?? 'Home')

@section('content')
<x-card :title="$page['heading'] ?? 'Home'">
    <p class="text-gray-600 mb-4">{{ $page['text'] ?? '' }}</p>
    @if (!empty($page['cta']))
        <x-button :href="url($page['cta']['url'])" variant="primary">{{ $page['cta']['label'] }}</x-button>
    @endif
</x-card>

@include('frontend.sections.features', ['features' => $page['features'] ?? []])
@endsection
