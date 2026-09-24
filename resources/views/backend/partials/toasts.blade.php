@php
    $toasts = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'info', 'message' => session('info')],
    ])->filter(fn ($toast) => filled($toast['message']))->values();

    $toastStyles = [
        'success' => [
            'title' => 'Success',
            'card' => 'border-emerald-200 bg-white',
            'icon' => 'bg-emerald-100 text-emerald-700',
            'bar' => 'bg-emerald-500',
            'role' => 'status',
        ],
        'error' => [
            'title' => 'Error',
            'card' => 'border-red-200 bg-white',
            'icon' => 'bg-red-100 text-red-700',
            'bar' => 'bg-red-500',
            'role' => 'alert',
        ],
        'info' => [
            'title' => 'Notice',
            'card' => 'border-sky-200 bg-white',
            'icon' => 'bg-sky-100 text-sky-700',
            'bar' => 'bg-sky-500',
            'role' => 'status',
        ],
    ];
@endphp

@if ($toasts->isNotEmpty())
    <div id="toastStack" class="pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto flex w-[min(92vw,24rem)] flex-col gap-2 px-2 sm:px-0" aria-live="polite">
        @foreach ($toasts as $toast)
            @php($style = $toastStyles[$toast['type']] ?? $toastStyles['info'])
            <div data-toast data-timeout="5000" role="{{ $style['role'] }}" class="pointer-events-auto relative overflow-hidden rounded-xl border {{ $style['card'] }} p-4 pr-10 shadow-lg transition-all duration-300">
                <div class="flex items-start gap-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $style['icon'] }}">
                        @if ($toast['type'] === 'success')
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        @elseif ($toast['type'] === 'error')
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008Zm-7.5-3.75a7.5 7.5 0 1 1 15 0 7.5 7.5 0 0 1-15 0Z" /></svg>
                        @else
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900">{{ $style['title'] }}</p>
                        <p class="mt-0.5 break-words text-sm text-gray-600">{{ $toast['message'] }}</p>
                    </div>
                </div>
                <button type="button" data-toast-close aria-label="Dismiss notification" class="absolute right-2 top-2 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
                <span data-toast-progress class="absolute inset-x-0 bottom-0 h-1 {{ $style['bar'] }} origin-left scale-x-100"></span>
            </div>
        @endforeach
    </div>
@endif
