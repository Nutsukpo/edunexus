@extends('layouts.master')

@section('title', 'Attendance Details')

@section('content')
<div class="container-fluid">

    <h3>Attendance Details</h3>

    <div class="card p-3">

        <p><strong>Staff:</strong> {{ $attendance->staff->name }}</p>
        <p><strong>Date:</strong> {{ $attendance->date }}</p>
        <p><strong>Clock In:</strong> {{ $attendance->clock_in_time }}</p>
        <p><strong>Status:</strong> {{ $attendance->status }}</p>

    </div>

</div>
@endsection