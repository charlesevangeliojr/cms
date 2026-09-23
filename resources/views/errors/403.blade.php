<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Forbidden</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <x-card title="403 — Forbidden" class="max-w-md w-full text-center">
            <p class="text-gray-600 mb-4">You don't have permission to access this page.</p>
            <x-button :href="auth()->check() ? route('dashboard') : url('/')" variant="dark">Back to Dashboard</x-button>
        </x-card>
    </div>
</body>
</html>
