@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Candidate" description="Add candidate data and assign it to a new employee request.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.recruitment.candidates._form', [
                'formAction' => route('recruitment-candidates.store'),
                'submitLabel' => 'Simpan Candidate',
            ])
        </div>
    </x-common.page-shell>
@endsection
