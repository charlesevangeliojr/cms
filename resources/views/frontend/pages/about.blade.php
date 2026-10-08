@extends('frontend.layouts.app')

@section('title', $page['heading'] ?? 'About')
@section('main-class', 'site-home')

@section('content')
<div class="site-container site-home-content">
    <x-card :title="$page['heading'] ?? 'About'">
        <p class="text-gray-600 mb-4">{{ $page['text'] ?? '' }}</p>
        @if (!empty($page['cta']))
            <x-button :href="url($page['cta']['url'])" variant="dark">{{ $page['cta']['label'] }}</x-button>
        @endif
    </x-card>

    @include('frontend.sections.stats', ['stats' => $page['stats'] ?? []])
</div>
@endsection
