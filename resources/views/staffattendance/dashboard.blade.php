@extends('layouts.master')

@section('title', 'Staff Attendance Dashboard')

@section('content')
<div class="container-fluid">

    <h3 class="mb-4">Staff Attendance Dashboard</h3>

    <div class="row">

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h6>Total Staff</h6>
                <h3>{{ $totalStaff }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm bg-success text-white">
                <h6>Present Today</h6>
                <h3>{{ $presentToday }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm bg-warning text-white">
                <h6>Late Today</h6>
                <h3>{{ $lateToday }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm bg-danger text-white">
                <h6>Absent Today</h6>
                <h3>{{ $absentToday }}</h3>
            </div>
        </div>

    </div>

</div>
@endsection