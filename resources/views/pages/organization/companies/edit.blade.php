@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Company" description="Update company code, name, or address.">
        <x-slot:actions>
            <a href="{{ route('companies.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                Back to list
            </a>
        </x-slot:actions>

        <form method="POST" action="{{ route('companies.update', $company) }}">
            @csrf
            @method('PUT')
            @include('pages.organization.companies._form', ['company' => $company])
        </form>
    </x-common.page-shell>
@endsection
