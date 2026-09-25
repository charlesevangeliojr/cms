@props([
    'title' => 'Permission denied',
    'message' => 'You are not allowed to access this page.',
    'actionUrl' => url('/'),
    'actionLabel' => 'Back to safety',
])

<div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600">
        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008Zm-7.5-3.75a7.5 7.5 0 1 1 15 0 7.5 7.5 0 0 1-15 0Z" />
        </svg>
    </div>
    <p class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-gray-400">403</p>
    <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $title }}</h1>
    <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
    <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
        <x-button :href="$actionUrl" variant="dark" class="justify-center">{{ $actionLabel }}</x-button>
        <x-button type="button" variant="light" class="justify-center" onclick="history.back()">Go back</x-button>
    </div>
</div>
