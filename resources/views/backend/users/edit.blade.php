@extends('backend.layouts.sidebar')

@section('title', 'Edit User')

@section('content')
<form id="user-account-form" action="{{ route('users.update', $user) }}" method="POST" class="max-w-7xl mx-auto" data-turbo="false">
    @csrf
    @method('PUT')
    
    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">Edit User</h2>
            <p class="text-sm text-gray-500">Update account details and permissions in one place.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-center text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
        </div>
    </div>

    <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="xl:col-span-12">
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
        <div id="account-panel" aria-labelledby="account-heading" class="min-w-0 space-y-6 xl:col-span-5">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-4 border-b border-gray-200 bg-gray-50/70 flex items-center gap-2 sm:px-6">
                    <svg class="w-5 h-5 text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    <h3 id="account-heading" class="text-sm font-bold text-gray-900">Account Information</h3>
                </div>
                <div class="p-4 space-y-5 sm:p-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Full Name *</label>
                        <input type="text" name="name" required maxlength="255" value="{{ old('name', $user->name) }}" placeholder="e.g. Maria Santos" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Email Address *</label>
                        <input type="email" name="email" required maxlength="255" value="{{ old('email', $user->email) }}" placeholder="user@nweb.solutions" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    @include('backend.partials.account-contact')
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">New Password <span class="font-normal text-gray-400">(leave blank to keep current)</span></label>
                        <div class="relative">
                            <input type="password" name="password" minlength="8" placeholder="Enter new password" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5">Confirm Password *</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </button>
                        </div>
                    </div>

                    @if($user->is_protected)
                        <div class="mt-4 border border-amber-200 rounded-xl bg-amber-50 p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Active Account</p>
                                <p class="text-xs text-amber-700 mt-0.5">Protected account — always active and allowed to log in</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                        </div>
                        <input type="hidden" name="is_active" value="1">
                    @else
                        <div class="mt-4 border border-gray-200 rounded-xl bg-red-50/30 p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Active Account</p>
                                <p class="text-xs text-gray-500 mt-0.5">Allow this user to log in</p>
                            </div>
                            <label class="inline-flex cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <div class="w-6 h-6 rounded bg-gray-200 peer-checked:bg-rose-700 peer-checked:[&_svg]:opacity-100 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 text-white opacity-0 transition-opacity" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </div>
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Role & Access --}}
        <div id="access-panel" aria-labelledby="access-heading" class="min-w-0 space-y-6 xl:col-span-7">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-4 py-4 border-b border-gray-200 bg-gray-50/70 flex items-center gap-2 shrink-0 sm:px-6">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    <h3 id="access-heading" class="text-sm font-bold text-gray-900">Role & Permissions</h3>
                </div>
                <div class="p-4 flex flex-col flex-1 gap-6 sm:p-6">
                    
                    @include('backend.partials.user-role-options')

                    {{-- Role Select --}}
                    <div id="role-select-fields">
                        <label class="block text-sm font-semibold text-gray-900 mb-1.5" for="role">Select Role *</label>
                        <select id="role" name="role" onchange="handleRoleSelection(event)" required data-role-defaults='@json($roleDefaults ?? [])' data-preserve-custom="1" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                        @if (auth()->user()?->isSuperAdmin())
                            <button id="delete-role-button" type="button" onclick="openRoleDialog('delete')" hidden class="mt-2 text-sm font-semibold text-red-700 disabled:opacity-50">Delete selected role</button>
                            <script>handleRoleSelection();</script>
                        @endif
                        <p class="mt-1.5 text-xs text-gray-400">Choosing a different role fills the permissions below. You can still adjust individual permissions.</p>
                    </div>

                    {{-- Full System Access --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50/30 p-4 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Full System Access</h4>
                            <p class="text-xs text-amber-600/80 mt-0.5">Check all available permissions for this user.</p>
                        </div>
                        <label class="inline-flex cursor-pointer">
                            <input type="checkbox" id="fullAccessToggle" name="full_access" class="sr-only peer" onchange="toggleAllPermissions(this.checked)">
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
                        
                        <div class="flex-1 overflow-x-auto overflow-y-auto border border-gray-200 rounded-xl">
                            <table class="min-w-[560px] w-full text-left text-sm text-gray-700">
                                <thead class="bg-indigo-950 text-white text-[10px] font-bold tracking-widest uppercase sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3">Module</th>
                                        <th class="px-2 py-3 text-center">
                                            <label class="inline-flex flex-col items-center gap-1 cursor-pointer" title="Check/uncheck all Can View">
                                                <input type="checkbox" class="col-toggle size-4 rounded border-2 border-white bg-white/20 text-indigo-600 focus:ring-white focus:ring-offset-0" data-action="view" onchange="toggleColumn('view', this.checked)">
                                                <span>Can View</span>
                                            </label>
                                        </th>
                                        <th class="px-2 py-3 text-center">
                                            <label class="inline-flex flex-col items-center gap-1 cursor-pointer" title="Check/uncheck all Can Add">
                                                <input type="checkbox" class="col-toggle size-4 rounded border-2 border-white bg-white/20 text-indigo-600 focus:ring-white focus:ring-offset-0" data-action="add" onchange="toggleColumn('add', this.checked)">
                                                <span>Can Add</span>
                                            </label>
                                        </th>
                                        <th class="px-2 py-3 text-center">
                                            <label class="inline-flex flex-col items-center gap-1 cursor-pointer" title="Check/uncheck all Can Edit">
                                                <input type="checkbox" class="col-toggle size-4 rounded border-2 border-white bg-white/20 text-indigo-600 focus:ring-white focus:ring-offset-0" data-action="edit" onchange="toggleColumn('edit', this.checked)">
                                                <span>Can Edit</span>
                                            </label>
                                        </th>
                                        <th class="px-2 py-3 text-center">
                                            <label class="inline-flex flex-col items-center gap-1 cursor-pointer" title="Check/uncheck all Can Delete">
                                                <input type="checkbox" class="col-toggle size-4 rounded border-2 border-white bg-white/20 text-indigo-600 focus:ring-white focus:ring-offset-0" data-action="delete" onchange="toggleColumn('delete', this.checked)">
                                                <span>Can Delete</span>
                                            </label>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($modules as $module)
                                    <tr class="hover:bg-gray-50 transition group">
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center gap-2 cursor-pointer" title="Check/uncheck all for {{ $module['name'] }}">
                                                <input type="checkbox" class="row-toggle size-4 rounded border-2 border-gray-300 bg-white text-indigo-600 focus:ring-indigo-500" data-module="{{ $module['key'] }}" onchange="toggleRow('{{ $module['key'] }}', this.checked)">
                                                <span class="font-semibold text-gray-800 text-xs">{{ $module['name'] }}</span>
                                            </label>
                                        </td>
                                        
                                        @foreach(['view', 'add', 'edit', 'delete'] as $action)
                                        <td class="px-2 py-3 text-center">
                                            <label class="inline-flex cursor-pointer group-hover:scale-110 transition-transform">
                                                <input type="checkbox" name="permissions[{{ $module['key'] }}][{{ $action }}]" value="1" class="perm-check sr-only peer" @checked(! empty($checkedPermissions[$module['key']][$action] ?? null))>
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
    @include('backend.partials.user-form-actions')
</form>

@endsection
