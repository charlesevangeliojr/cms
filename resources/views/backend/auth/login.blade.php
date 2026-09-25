<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">

    <title>Login — CMS</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>
    <script type="module">
        import * as Turbo from 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.23/+esm';
        Turbo.start();
    </script>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow p-8">
            <h1 class="text-2xl font-bold mb-1">CMS Login</h1>
            <p class="text-sm text-gray-500 mb-6">Sign in to access the dashboard.</p>

            @if ($errors->any())
                <x-alert type="error" class="mb-4">{{ $errors->first() }}</x-alert>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" data-turbo="false" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full rounded-xl border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" value="1" class="rounded-xl border-gray-300">
                    Remember me
                </label>

                <x-button type="submit" variant="dark" class="w-full justify-center">Log in</x-button>
            </form>
        </div>
    </div>
    @include('backend.partials.notifications')
</body>
</html>
