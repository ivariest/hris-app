@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Employee Agreement" description="Create new employee agreement based on recruitment request.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.employee-management.agreements._form', [
                'formAction' => route('employee-agreements.store'),
                'submitLabel' => 'Save Agreement',
            ])
        </div>
    </x-common.page-shell>
@endsection
