@extends('backend.layouts.sidebar')

@section('title', 'Create Banner')

@section('content')
<form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data" class="mx-auto max-w-7xl space-y-6" data-turbo="false">
    @csrf

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Create Banner</h2>
            <p class="text-sm text-gray-500">Add a promotional banner to the database.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('banners.index') }}" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition whitespace-nowrap">Cancel</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition whitespace-nowrap">Create Banner</button>
        </div>
    </div>

    @include('backend.banners._form')
</form>
@endsection
