<div>
    <label for="contact" class="mb-1.5 block text-sm font-semibold text-gray-900">Contact Number</label>
    <input id="contact" name="contact" type="tel" autocomplete="tel" maxlength="20"
           value="{{ old('contact', $user->contact ?? '') }}" placeholder="e.g. +63 912 345 6789"
           @error('contact') aria-invalid="true" aria-describedby="contact-error" @enderror
           class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
    @error('contact')
        <p id="contact-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
