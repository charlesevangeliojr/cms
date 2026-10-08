@extends('frontend.layouts.app')

@section('title', $page['heading'] ?? 'Home')

@section('main-class', 'site-home')

@section('content')
<div class="site-container site-home-content">
<section class="site-introduction" aria-labelledby="home-heading">
    <div>
        <p class="site-section-eyebrow">A little about us</p>
        <h2 id="home-heading">Welcome</h2>
    </div>
    <div>
        <p>{{ $page['text'] ?? '' }}</p>
    </div>
</section>

</div>
@endsection
