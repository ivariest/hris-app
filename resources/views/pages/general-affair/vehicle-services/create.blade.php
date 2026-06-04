@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Input Service Kendaraan" description="Catat rekap service kendaraan asset.">
        <x-slot:actions>
            <a href="{{ route('general-affair.vehicle-services.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Back to report</a>
        </x-slot:actions>
        <form method="POST" action="{{ route('general-affair.vehicle-services.store') }}" enctype="multipart/form-data">
            @csrf
            @include('pages.general-affair.vehicle-services._form')
        </form>
    </x-common.page-shell>
@endsection
