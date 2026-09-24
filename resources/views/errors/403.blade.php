<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Permission denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    @php
        $exceptionMessage = isset($exception) ? trim((string) $exception->getMessage()) : '';
        $message = $exceptionMessage !== '' && $exceptionMessage !== 'Forbidden'
            ? $exceptionMessage
            : 'You are not allowed to access this page. Please contact an administrator if you need access.';
        $landingRouteName = auth()->user()?->landingRouteName();
        $actionUrl = $landingRouteName ? route($landingRouteName) : route('login');
        $actionLabel = match ($landingRouteName) {
            'dashboard' => 'Back to Dashboard',
            'banners.index' => 'Back to Banners',
            'users.index' => 'Back to Users',
            default => 'Back to Login',
        };
    @endphp

    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <x-permission-denied
            title="Permission denied"
            :message="$message"
            :action-url="$actionUrl"
            :action-label="$actionLabel"
        />
    </div>
</body>
</html>
