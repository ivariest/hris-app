@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Promotion" description="Record a promotion and update the employee's active position and level.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.employee-management.promotions._form', [
                'formAction' => route('employee-promotions.store'),
                'submitLabel' => 'Simpan Promotion',
                'formMode' => 'edit',
            ])
        </div>
    </x-common.page-shell>
@endsection
