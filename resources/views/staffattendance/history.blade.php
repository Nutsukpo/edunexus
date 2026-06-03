@extends('layouts.master')

@section('title', 'Staff Attendance History')

@section('content')
<div class="container-fluid">

    <h3>{{ $staff->name }} Attendance History</h3>

    <table class="table table-striped">

        <thead>
            <tr>
                <th>Date</th>
                <th>Clock In</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach($history as $h)
                <tr>
                    <td>{{ $h->date }}</td>
                    <td>{{ $h->clock_in_time }}</td>
                    <td>{{ $h->status }}</td>
                </tr>
            @endforeach
        </tbody>

    </table>

</div>
@endsection