<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 — Page expired</title>
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
        <x-card title="419 — Page expired" class="max-w-md w-full text-center">
            <p class="text-gray-600 mb-4">Your session expired. Please refresh and try again.</p>
            <x-button :href="url('/')" variant="dark">Back to Home</x-button>
        </x-card>
    </div>
</body>
</html>
