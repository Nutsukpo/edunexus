@extends('layouts.master')

@section('title', 'Attendance Report')

@section('content')
<div class="container-fluid">

    <h3>Staff Attendance Report</h3>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Staff</th>
                <th>Present</th>
                <th>Late</th>
                <th>Absent</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($report as $r)
                <tr>
                    <td>{{ $r->staff->name }}</td>
                    <td>{{ $r->present_days }}</td>
                    <td>{{ $r->late_days }}</td>
                    <td>{{ $r->absent_days }}</td>
                    <td>{{ $r->total_days }}</td>
                </tr>
            @endforeach
        </tbody>

    </table>

</div>
@endsection