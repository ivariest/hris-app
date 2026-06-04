@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Mutation" description="Move employee to a new department, sub department, and position.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.employee-management.mutations._form', [
                'formAction' => route('employee-mutations.store'),
                'submitLabel' => 'Simpan Mutation',
            ])
        </div>
    </x-common.page-shell>
@endsection
