@extends('layouts.app')
@section('content')
    <x-common.page-shell title="Edit Position Level" description="Update the level name.">
        <x-slot:actions><a href="{{ route('position-levels.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back to list</a></x-slot:actions>
        <form method="POST" action="{{ route('position-levels.update', $positionLevel) }}">@csrf @method('PUT') @include('pages.organization.position-levels._form', ['positionLevel' => $positionLevel])</form>
    </x-common.page-shell>
@endsection
