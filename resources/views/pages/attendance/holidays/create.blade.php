@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Holiday" description="Tambahkan libur nasional atau cuti bersama.">
        <form method="POST" action="{{ route('attendance-holidays.store') }}">
            @csrf
            @include('pages.attendance.holidays._form')
        </form>
    </x-common.page-shell>
@endsection
