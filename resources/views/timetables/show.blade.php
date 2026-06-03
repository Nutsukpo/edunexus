@extends('layouts.master')

@section('title','View Timetable')

@section('content')

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header d-flex justify-content-between">

            <h4>
                {{ $timetable->title }}
            </h4>

            <a href="{{ route('timetables.index') }}"
               class="btn btn-secondary">

                Back

            </a>

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th>Academic Year</th>
                    <td>{{ $timetable->academicYear->name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Class</th>
                    <td>{{ $timetable->studentClass->name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Description</th>
                    <td>{{ $timetable->description }}</td>
                </tr>

                <tr>
                    <th>File Type</th>
                    <td>{{ strtoupper($timetable->file_type) }}</td>
                </tr>

            </table>

            <hr>

            @if(in_array($timetable->file_type,['pdf']))

                <iframe
                    src="{{ asset('storage/'.$timetable->file_path) }}"
                    width="100%"
                    height="700px">
                </iframe>

            @elseif(in_array($timetable->file_type,['jpg','jpeg','png']))

                <img
                    src="{{ asset('storage/'.$timetable->file_path) }}"
                    class="img-fluid">

            @else

                <div class="alert alert-info">

                    Preview not available.

                    <a href="{{ route('timetables.download',$timetable->id) }}">
                        Download File
                    </a>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection