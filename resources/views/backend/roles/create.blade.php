@extends('backend.layouts.sidebar')

@section('title', 'Create Role')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Create New Role</h2>
            <p class="text-sm text-gray-500">Create a database role and choose its default module permissions.</p>
        </div>
        <a href="{{ route('users.create') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Back to Users</a>
    </div>

    @if ($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ route('roles.store') }}" class="space-y-6" data-turbo="false">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-gray-900">Role Name *</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                           placeholder="e.g. Release Manager"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div class="flex items-end">
                    <div class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Active Role</p>
                            <p class="mt-0.5 text-xs text-gray-500">Active roles appear in the user role dropdown.</p>
                        </div>
                        <label class="inline-flex cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="peer sr-only" @checked(old('is_active', true))>
                            <span class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-white transition peer-checked:bg-emerald-600 peer-checked:[&_svg]:opacity-100">
                                <svg class="h-4 w-4 opacity-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Default Permissions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Module</th>
                            <th class="px-4 py-3 text-center font-semibold">View</th>
                            <th class="px-4 py-3 text-center font-semibold">Add</th>
                            <th class="px-4 py-3 text-center font-semibold">Edit</th>
                            <th class="px-4 py-3 text-center font-semibold">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($modules as $module)
                            <tr>
                                <td class="px-6 py-3 font-semibold text-gray-800">{{ $module['name'] }}</td>
                                @foreach (['view', 'add', 'edit', 'delete'] as $action)
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="permissions[{{ $module['key'] }}][{{ $action }}]" value="1"
                                               @checked(old("permissions.{$module['key']}.{$action}", false))
                                               class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('users.create') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Role</button>
        </div>
    </form>
</div>
@endsection
