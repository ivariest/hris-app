@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Service Kendaraan" description="Perbarui rekap service kendaraan.">
        <x-slot:actions>
            <a href="{{ route('general-affair.vehicle-services.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Back to report</a>
        </x-slot:actions>
        <form method="POST" action="{{ route('general-affair.vehicle-services.update', $service) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('pages.general-affair.vehicle-services._form', ['service' => $service])
        </form>
    </x-common.page-shell>
@endsection
