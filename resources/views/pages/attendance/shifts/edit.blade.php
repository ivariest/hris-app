@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Shift" description="Update jam dan mapping shift karyawan.">
        <form method="POST" action="{{ route('attendance-shifts.update', $shift) }}">
            @csrf
            @method('PUT')
            @include('pages.attendance.shifts._form')
        </form>
    </x-common.page-shell>
@endsection
