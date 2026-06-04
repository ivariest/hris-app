@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Demotion" description="Record a demotion and update the employee's active position and level.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.employee-management.demotions._form', [
                'formAction' => route('employee-demotions.store'),
                'submitLabel' => 'Simpan Demotion',
            ])
        </div>
    </x-common.page-shell>
@endsection
