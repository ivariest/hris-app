@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Create Shift" description="Tambahkan shift untuk perhitungan rekap attendance.">
        <form method="POST" action="{{ route('attendance-shifts.store') }}">
            @csrf
            @include('pages.attendance.shifts._form')
        </form>
    </x-common.page-shell>
@endsection
