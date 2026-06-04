@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Candidate" description="Update candidate data and category.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.recruitment.candidates._form', [
                'formAction' => route('recruitment-candidates.update', $candidate),
                'submitLabel' => 'Update Candidate',
            ])
        </div>
    </x-common.page-shell>
@endsection
