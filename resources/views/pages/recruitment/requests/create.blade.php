@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create New Employee Request" description="Input detail kebutuhan permohonan karyawan.">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @include('pages.recruitment.requests._form', [
                'formAction' => route('recruitment-requests.store'),
                'submitLabel' => 'Simpan Request',
            ])
        </div>
    </x-common.page-shell>
@endsection
