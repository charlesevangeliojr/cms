@if ($errors->any())
    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="site-contact-section">
    <section id="contact" class="site-form-panel" aria-labelledby="contact-heading">
        <div class="border-b border-gray-200 bg-gray-50/50 px-6 py-4">
            <h2 id="contact-heading" class="text-xl font-bold tracking-tight text-gray-900">Send us a message</h2>
            <p class="mt-1 text-sm text-gray-500">We’d be happy to hear from you.</p>
        </div>
        <form id="contact-form" method="POST" action="{{ route('contact.store') }}" class="space-y-4 p-6" data-turbo="false" data-ajax-form>
            @csrf
            <div><label for="contact_name" class="mb-1.5 block text-sm font-semibold text-gray-900">Name *</label><input id="contact_name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Juan Dela Cruz" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"></div>
            <div><label for="contact_email" class="mb-1.5 block text-sm font-semibold text-gray-900">Email *</label><input id="contact_email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="juan@example.com" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"></div>
            @include('frontend.partials.contact-number')
            <div><label for="contact_subject" class="mb-1.5 block text-sm font-semibold text-gray-900">Subject *</label><input id="contact_subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="255" placeholder="e.g. Inquiry about services" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"></div>
            <div><label for="contact_message" class="mb-1.5 block text-sm font-semibold text-gray-900">Message *</label><textarea id="contact_message" name="message" rows="4" required maxlength="5000" placeholder="Write your message..." class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('message') }}</textarea></div>
            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Send Message</button>
        </form>
    </section>
</div>
