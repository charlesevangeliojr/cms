@extends('backend.layouts.sidebar')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<form action="{{ route('users.store') }}" method="POST" class="max-w-7xl mx-auto" data-turbo="false">
    @csrf
    
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ isset($user) ? 'Edit User' : 'Create User' }}</h2>
            <p class="text-sm text-gray-500">Manage account information and role access</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-900 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 transition">Save User</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="lg:col-span-12">
                <x-alert type="error">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            </div>
        @endif
        
        {{-- Left Column: Account Information --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    <h3 class="text-xs font-bold text-gray-700 tracking-widest uppercase">Account Information</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Maria Santos" class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="user@nweb.solutions" class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Contact Number</label>
                        <input type="text" name="contact" value="{{ old('contact') }}" placeholder="09XXXXXXXXX" class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Password *</label>
                        <div class="relative">
                            <input type="password" name="password" placeholder="Enter password" class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Confirm Password *</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 border border-gray-200 rounded-lg bg-red-50/30 p-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Active Account</p>
                            <p class="text-xs text-gray-500 mt-0.5">Allow this user to log in</p>
                        </div>
                        <label class="inline-flex cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', true) ? 'checked' : '' }}>
                            <div class="w-6 h-6 rounded bg-gray-200 peer-checked:bg-rose-700 peer-checked:[&_svg]:opacity-100 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4 text-white opacity-0 transition-opacity" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Role & Access --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-2 shrink-0">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    <h3 class="text-xs font-bold text-gray-700 tracking-widest uppercase">Role & Access</h3>
                </div>
                <div class="p-6 flex flex-col flex-1 gap-6">
                    
                    {{-- Assign Role Segments --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Assign Role *</label>
                        <div class="flex rounded-lg border border-gray-200 p-1 bg-gray-50/50">
                            <button type="button" class="flex-1 rounded-md bg-indigo-900 py-2.5 text-xs font-bold text-white shadow-sm transition">Select Existing Role</button>
                            <button type="button" class="flex-1 rounded-md py-2.5 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">Create New Role</button>
                        </div>
                    </div>

                    {{-- Role Select --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Select Role *</label>
                        <select name="role" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 appearance-none">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Full System Access --}}
                    <div class="rounded-lg border border-amber-200 bg-amber-50/30 p-4 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Full System Access</h4>
                            <p class="text-xs text-amber-600/80 mt-0.5">Check all available permissions for this user.</p>
                        </div>
                        <label class="inline-flex cursor-pointer">
                            <input type="checkbox" id="fullAccessToggle" name="full_access" class="sr-only peer" onchange="toggleAllPermissions(this.checked)" {{ isset($user) && $user['full_access'] ? 'checked' : '' }}>
                            <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:[&_svg]:opacity-100 flex items-center justify-center transition-colors">
                                <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                        </label>
                    </div>

                    {{-- Module Permissions --}}
                    <div class="flex-1 flex flex-col min-h-0">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <h4 class="text-xs font-bold text-gray-700 tracking-widest uppercase">Module Permissions</h4>
                            <button type="button" onclick="toggleAllPermissions(false)" class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline">Clear All</button>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-left text-sm text-gray-700">
                                <thead class="bg-indigo-950 text-white text-[10px] font-bold tracking-widest uppercase sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3">Module</th>
                                        <th class="px-2 py-3 text-center">Can View</th>
                                        <th class="px-2 py-3 text-center">Can Add</th>
                                        <th class="px-2 py-3 text-center">Can Edit</th>
                                        <th class="px-2 py-3 text-center">Can Delete</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($modules as $module)
                                    <tr class="hover:bg-gray-50 transition group">
                                        <td class="px-4 py-3 font-semibold text-gray-800 text-xs">{{ $module['name'] }}</td>
                                        
                                        @foreach(['view', 'add', 'edit', 'delete'] as $action)
                                        <td class="px-2 py-3 text-center">
                                            <label class="inline-flex cursor-pointer group-hover:scale-110 transition-transform">
                                                <input type="checkbox" name="permissions[{{ $module['key'] }}][{{ $action }}]" value="1" class="perm-check sr-only peer">
                                                <div class="w-4 h-4 rounded border-2 border-gray-300 bg-white peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:[&_svg]:opacity-100 flex items-center justify-center transition-colors">
                                                    <svg class="w-3 h-3 text-white opacity-0 transition-opacity" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                </div>
                                            </label>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 px-1">Selected permissions become user-specific access settings.</p>
                    </div>

                </div>
            </div>
        </div>

    </div>
</form>

<script>
    function toggleAllPermissions(checked) {
        document.querySelectorAll('.perm-check').forEach(cb => {
            cb.checked = checked;
        });
        if(!checked) {
            document.getElementById('fullAccessToggle').checked = false;
        }
    }

    // Pre-check the matrix from the selected role's default permissions.
    const roleDefaults = @json($roleDefaults ?? []);
    function applyRoleDefaults(role) {
        const perms = roleDefaults[role] || {};
        document.querySelectorAll('.perm-check').forEach(cb => {
            const m = cb.name.match(/^permissions\[(.+?)\]\[(.+?)\]$/);
            cb.checked = !!(m && perms[m[1]] && perms[m[1]][m[2]]);
        });
    }
    const roleSelect = document.querySelector('select[name="role"]');
    roleSelect?.addEventListener('change', e => applyRoleDefaults(e.target.value));
    applyRoleDefaults(roleSelect?.value);
</script>
@endsection
