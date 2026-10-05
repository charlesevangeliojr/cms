@extends('frontend.layouts.app')

@section('title', $page['heading'] ?? 'Home')

@section('main-class', 'site-home')

@section('content')
@include('frontend.sections.hero')

<div class="site-container site-home-content">
<section class="site-introduction" aria-labelledby="home-heading">
    <div>
        <p class="site-section-eyebrow">A little about us</p>
        <h2 id="home-heading">Welcome to {{ $site['name'] ?? 'CMS Template' }}</h2>
    </div>
    <div>
        <p>{{ $page['text'] ?? '' }}</p>
    </div>
</section>

{{-- Success / Error feedback for public forms --}}

@if ($errors->any())
    <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Contact form --}}
<div class="site-contact-section">
    <section id="contact" class="site-form-panel" aria-labelledby="contact-heading">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h2 id="contact-heading" class="text-xl font-bold tracking-tight text-gray-900">Contact Us</h2>
            <p class="text-sm text-gray-500 mt-1">Have a question? Send us a message.</p>
        </div>
        <form id="contact-form" method="POST" action="{{ route('contact.store') }}" class="p-6 space-y-4" data-turbo="false" data-ajax-form>
            @csrf
            <div>
                <label for="contact_name" class="block text-sm font-semibold text-gray-900 mb-1.5">Name *</label>
                <input id="contact_name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Juan Dela Cruz"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <label for="contact_email" class="block text-sm font-semibold text-gray-900 mb-1.5">Email *</label>
                <input id="contact_email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="juan@example.com"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            @include('frontend.partials.contact-number')
            <div>
                <label for="contact_subject" class="block text-sm font-semibold text-gray-900 mb-1.5">Subject *</label>
                <input id="contact_subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="255" placeholder="e.g. Inquiry about services"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <label for="contact_message" class="block text-sm font-semibold text-gray-900 mb-1.5">Message *</label>
                <textarea id="contact_message" name="message" rows="4" required maxlength="5000" placeholder="Write your message..."
                          class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 h-10 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Send Message</button>
        </form>
    </section>


</div>
</div>
@endsection
