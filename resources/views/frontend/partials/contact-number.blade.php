@php
    $contactCountries = config('countries', []);
    $selectedContactCountry = old('contact_country', '+63');
    $selectedContact = collect($contactCountries)->firstWhere('dial', $selectedContactCountry);
    if (! $selectedContact) {
        $selectedContactCountry = '+63';
        $selectedContact = collect($contactCountries)->firstWhere('dial', '+63');
    }
    $selectedIso = strtolower($selectedContact['iso'] ?? 'ph');
@endphp
<div>
    <label for="contact" class="block text-sm font-semibold text-gray-900 mb-1.5">Contact Number <span class="font-normal text-gray-400">(optional)</span></label>
    <div class="flex gap-2">
        <div class="relative shrink-0" data-country-dropdown>
            <input type="hidden" id="contact_country" name="contact_country" value="{{ $selectedContactCountry }}" data-country-input autocomplete="tel-country-code">
            <button type="button" data-country-button aria-haspopup="listbox" aria-expanded="false" title="Choose country code"
                    class="inline-flex h-full min-h-[44px] items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <img data-country-flag src="https://flagcdn.com/w40/{{ $selectedIso }}.png" alt="" class="h-4 w-6 rounded-sm object-cover" loading="lazy" onerror="this.style.display='none'">
                <span data-country-code>{{ $selectedContactCountry }}</span>
                <svg data-country-chevron class="h-4 w-4 text-gray-400 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
            </button>
            <div data-country-list class="hidden absolute left-0 z-30 mt-1 w-64 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg">
                <div class="border-b border-gray-100 bg-white p-1.5">
                    <input type="text" data-country-search placeholder="Search country..." autocomplete="off"
                           class="w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <ul role="listbox" aria-label="Country code" class="max-h-56 overflow-y-auto py-1">
                    @foreach ($contactCountries as $country)
                        @php $iso = strtolower($country['iso']); @endphp
                        <li role="option" aria-selected="{{ $selectedContactCountry === $country['dial'] && $selectedIso === $iso ? 'true' : 'false' }}">
                            <button type="button" data-country-option data-code="{{ $country['dial'] }}" data-iso="{{ $iso }}" data-name="{{ strtolower($country['name']) }}" aria-selected="{{ $selectedContactCountry === $country['dial'] && $selectedIso === $iso ? 'true' : 'false' }}"
                                    class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 {{ $selectedContactCountry === $country['dial'] && $selectedIso === $iso ? 'bg-indigo-50 font-semibold text-indigo-700' : '' }}">
                                <span class="flex min-w-0 items-center gap-2">
                                    <img src="https://flagcdn.com/w40/{{ $iso }}.png" alt="" class="h-4 w-6 shrink-0 rounded-sm object-cover" loading="lazy" onerror="this.style.display='none'">
                                    <span class="truncate">{{ $country['name'] }}</span>
                                </span>
                                <span class="flex shrink-0 items-center gap-1 text-xs text-gray-400">
                                    {{ $country['dial'] }}
                                    <svg data-country-check class="h-4 w-4 text-indigo-600 {{ $selectedContactCountry === $country['dial'] && $selectedIso === $iso ? '' : 'hidden' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <input id="contact" name="contact" type="tel" inputmode="numeric" autocomplete="tel-national" maxlength="10" pattern="[0-9]{1,10}"
               value="{{ old('contact') }}" placeholder="e.g. 9123456789"
               @error('contact') aria-invalid="true" aria-describedby="contact-error" @enderror
               class="min-w-0 w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
    </div>
    <p class="mt-1.5 text-xs text-gray-400">Choose the country code and enter up to 10 digits for the local number.</p>
    @error('contact')
        <p id="contact-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
