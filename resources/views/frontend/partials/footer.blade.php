<footer class="site-footer">
    <div class="site-container site-footer-main">
        <div class="site-footer-info">
            <a href="{{ route('home') }}" class="site-brand">
                <img src="{{ asset('images/cms-logo.png') }}" alt="" width="44" height="44">
                <span>{{ $site['name'] ?? 'CMS Template' }}</span>
            </a>
            <p>Stay connected. We'd love to hear from you.</p>
            <nav aria-label="Footer navigation">
            @foreach (($nav ?? [['label' => 'Home', 'url' => '/'], ['label' => 'About', 'url' => '/about']]) as $item)
                <a href="{{ url($item['url']) }}">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('home') }}#contact">Contact Us</a>
            <a href="{{ route('home') }}#newsletter">Newsletter</a>
            </nav>
        </div>
    <section id="newsletter" class="site-footer-newsletter" aria-labelledby="newsletter-heading">
        <div class="site-footer-newsletter-heading">
            <h2 id="newsletter-heading" class="text-xl font-bold tracking-tight text-slate-100">Newsletter</h2>
            <p class="text-sm text-slate-300 mt-1">Subscribe to receive our latest updates.</p>
        </div>
        <form method="POST" action="{{ route('newsletter.store') }}" class="space-y-4" data-turbo="false">
            @csrf
            <div>
                <label for="newsletter_email" class="block text-sm font-semibold text-slate-100 mb-1.5">Email *</label>
                <input id="newsletter_email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="juan@example.com"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Subscribe</button>
        </form>
    </section>
    </div>
    <div class="site-container site-footer-bottom">&copy; {{ date('Y') }} {{ $site['name'] ?? 'CMS Template' }}. All rights reserved.</div>
</footer>
