@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Holiday" description="Update data libur attendance.">
        <form method="POST" action="{{ route('attendance-holidays.update', $holiday) }}">
            @csrf
            @method('PUT')
            @include('pages.attendance.holidays._form')
        </form>
    </x-common.page-shell>
@endsection
