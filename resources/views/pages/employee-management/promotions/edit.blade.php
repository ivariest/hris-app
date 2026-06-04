@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Promotion" description="Update promotion details or resolve an active PJS assignment.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.employee-management.promotions._form', [
                'formAction' => route('employee-promotions.update', $promotion),
                'submitLabel' => 'Update Promotion',
                'formMode' => 'edit',
            ])
        </div>
    </x-common.page-shell>
@endsection
