@extends('frontend.layouts.app')

@section('title', $page['heading'] ?? 'Home')

@section('content')
<x-card :title="$page['heading'] ?? 'Home'">
    <p class="text-gray-600 mb-4">{{ $page['text'] ?? '' }}</p>
    @if (!empty($page['cta']))
        <x-button :href="url($page['cta']['url'])" variant="primary">{{ $page['cta']['label'] }}</x-button>
    @endif
</x-card>

{{-- Banners — active, latest at top, 16:6 --}}
@if(!empty($banners) && count($banners))
    <div class="mt-8">
        <h2 class="text-lg font-bold tracking-tight text-gray-900">Banners</h2>
        <p class="text-sm text-gray-500">Active banners from CMS — newest at top</p>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($banners as $banner)
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full aspect-[16/6] object-cover" loading="lazy">
                    @endif
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900">{{ $banner->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-3">{{ $banner->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Success / Error feedback for public forms --}}

@if ($errors->any())
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Test inputs for Contact Us & Newsletter — visible on landing page --}}
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Contact Us Test Form --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-sm font-bold tracking-widest uppercase text-gray-700">Test — Contact Us</h3>
            <p class="text-xs text-gray-500 mt-1">Submits to <code class="rounded bg-gray-100 px-1 py-0.5">POST /contact</code> → visible at <a href="{{ route('contacts.index') }}" class="text-indigo-600 hover:underline">Admin → Contact Us</a></p>
        </div>
        <form method="POST" action="{{ route('contact.store') }}" class="p-6 space-y-4" data-turbo="false">
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
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Send Message</button>
        </form>
    </div>

    {{-- Newsletter Test Form --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-sm font-bold tracking-widest uppercase text-gray-700">Test — Newsletter</h3>
            <p class="text-xs text-gray-500 mt-1">Submits to <code class="rounded bg-gray-100 px-1 py-0.5">POST /newsletter</code> → visible at <a href="{{ route('newsletters.index') }}" class="text-indigo-600 hover:underline">Admin → Newsletter</a></p>
        </div>
        <form method="POST" action="{{ route('newsletter.store') }}" class="p-6 space-y-4" data-turbo="false">
            @csrf
            <div>
                <label for="newsletter_name" class="block text-sm font-semibold text-gray-900 mb-1.5">Name <span class="font-normal text-gray-400">(optional)</span></label>
                <input id="newsletter_name" name="name" type="text" value="{{ old('name') }}" maxlength="255" placeholder="e.g. Juan"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <label for="newsletter_email" class="block text-sm font-semibold text-gray-900 mb-1.5">Email *</label>
                <input id="newsletter_email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="juan@example.com"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 h-10 rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition">Subscribe</button>
            <p class="text-xs text-gray-400 text-center">Duplicate emails will show validation error; check admin table for new rows.</p>
        </form>
    </div>
</div>
@endsection
