@php
    $peak = max(1, ...$analyticsRealtime['minutes']);
    $number = fn ($value) => $value === null ? '—' : number_format($value);
@endphp
<section data-dashboard-traffic data-endpoint="{{ route('dashboard.traffic', [], false) }}" data-traffic-updated-at="{{ $analyticsRealtime['realtimeUpdatedAt'] ?? '' }}" data-traffic-timezone="{{ $analyticsRealtime['timezone'] }}" data-traffic-available="{{ $analyticsRealtime['available'] ? 'true' : 'false' }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="analytics-heading">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
        <div><h2 id="analytics-heading" class="text-base font-semibold text-gray-900">Website traffic</h2><p class="mt-1 text-xs text-gray-500">Google Analytics · realtime activity and monthly audience · Timezone: {{ $analyticsRealtime['timezone'] }}</p></div>
        <span class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600"><span data-traffic-indicator class="h-2 w-2 rounded-full {{ $analyticsRealtime['available'] ? 'bg-emerald-500' : 'bg-gray-400' }}"></span><span data-traffic-state>{{ $analyticsRealtime['available'] ? 'Live · updates every minute' : 'Awaiting data' }}</span></span>
    </div>
    <div class="p-5 sm:p-6">
        <div class="grid gap-5 sm:grid-cols-3">
            <div><h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Active users in last 30 minutes</h3><p data-traffic-value="active30m" class="mt-2 text-4xl font-medium tabular-nums text-gray-900">{{ $number($analyticsRealtime['active30m']) }}</p></div>
            <div><h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Active users in last 5 minutes</h3><p data-traffic-value="active5m" class="mt-2 text-4xl font-medium tabular-nums text-gray-900">{{ $number($analyticsRealtime['active5m']) }}</p></div>
            <div class="border-t border-gray-100 pt-4 sm:border-l sm:border-t-0 sm:pl-6 sm:pt-0"><h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total users this month</h3><p data-traffic-value="monthlyUsers" class="mt-2 text-4xl font-medium tabular-nums text-indigo-700">{{ $number($analyticsRealtime['monthlyUsers']) }}</p><p data-traffic-month class="mt-1 text-xs text-gray-500">{{ $analyticsRealtime['monthLabel'] }} · month to date</p></div>
        </div>
        <div class="mt-7"><h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Active users per minute</h3>
            <div class="relative mt-3 pr-9">
                <div class="relative h-36" role="group" data-traffic-chart aria-label="{{ $analyticsRealtime['available'] ? 'Active users per minute, oldest to newest, for the last 30 minutes.' : 'Active users per minute: data unavailable.' }}">
                    <div class="pointer-events-none absolute inset-0 flex flex-col justify-between" aria-hidden="true"><div class="border-t border-gray-100"></div><div class="border-t border-gray-100"></div><div class="border-t border-gray-200"></div></div>
                    <div class="absolute inset-0 flex items-end gap-1">
                        @foreach($analyticsRealtime['minutes'] as $minute => $users)
                            <div data-traffic-minute data-minute-offset="{{ 29 - $minute }}" data-users="{{ $users }}" tabindex="0" role="img" aria-label="{{ $analyticsRealtime['available'] ? $users.' active users' : 'Analytics data unavailable' }}" class="group relative flex h-full min-w-0 flex-1 items-end outline-none focus-visible:ring-2 focus-visible:ring-indigo-400" title="{{ $analyticsRealtime['available'] ? $users.' active users' : 'Analytics data unavailable' }}"><div data-traffic-bar class="w-full origin-bottom cursor-crosshair rounded-t-sm bg-indigo-500 transition-[height,background-color,transform] duration-150 ease-out group-hover:scale-y-110 group-hover:bg-indigo-700 group-hover:shadow-md group-focus-within:bg-indigo-700" style="height: {{ $analyticsRealtime['available'] ? $users / $peak * 100 : 0 }}%"></div></div>
                        @endforeach
                    </div>
                    <div data-traffic-tooltip hidden role="status" class="pointer-events-none absolute top-0 z-20 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white shadow-lg"></div>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex w-7 flex-col justify-between text-right text-[11px] text-gray-400" aria-hidden="true"><span data-traffic-peak>{{ $peak }}</span><span data-traffic-mid>{{ $peak / 2 }}</span><span>0</span></div>
            </div>
            <div class="mr-9 mt-2 flex justify-between text-[10px] text-gray-400" aria-hidden="true"><span>−30 min</span><span>−25 min</span><span>−20 min</span><span>−15 min</span><span>−10 min</span><span>−5 min</span><span>Now</span></div>
        </div>
    </div>
</section>
<script src="{{ asset('backend/dashboard-traffic.js') }}" defer></script>
