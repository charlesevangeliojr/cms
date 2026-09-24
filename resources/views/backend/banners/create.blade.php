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
        <div class="flex gap-3">
            <a href="{{ route('banners.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Banner</button>
        </div>
    </div>

    @include('backend.banners._form', ['positions' => $positions])
</form>
@endsection
