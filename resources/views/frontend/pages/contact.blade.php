@extends('frontend.layouts.app')
@section('title', $page->title)
@section('main-class', 'site-home')
@section('content')
<div class="site-container site-contact-content">
    <div class="site-contact-intro">
        <p>{{ $page->content }}</p>
    </div>
    @include('frontend.partials.contact-form')
</div>
@endsection
