@extends('layouts.master')

@section('title', 'View Timetable')

@section('content')

<div class="container-fluid py-3">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h4 class="mb-1">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ $timetable->title }}
                </h4>

                <small class="text-muted">
                    Timetable Details
                </small>
            </div>

            <div class="d-flex gap-2">

                <a href="{{ route('timetables.index') }}"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back
                </a>

                <a href="{{ route('timetables.download', $timetable) }}"
                   class="btn btn-primary">
                    <i class="fas fa-download me-1"></i>
                    Download
                </a>

            </div>

        </div>


        <div class="card-body">

            {{-- Information --}}
            <div class="row mb-4">

                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Academic Year
                        </small>

                        <strong>
                            {{ $timetable->academicYear->name ?? '-' }}
                        </strong>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            Class
                        </small>

                        <strong>
                            {{ $timetable->studentClass->name ?? '-' }}
                        </strong>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            File Type
                        </small>

                        <strong class="text-uppercase">
                            {{ $timetable->file_type ?? '-' }}
                        </strong>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">
                            File Name
                        </small>

                        <strong class="text-break">
                            {{ $timetable->file_name }}
                        </strong>
                    </div>
                </div>

            </div>


            @if($timetable->description)

                <div class="alert alert-light border mb-4">

                    <strong>
                        <i class="fas fa-align-left me-1"></i>
                        Description
                    </strong>

                    <div class="mt-2">
                        {{ $timetable->description }}
                    </div>

                </div>

            @endif


            {{-- Preview --}}
            <div class="card border">

                <div class="card-header">
                    <strong>
                        <i class="fas fa-eye me-2"></i>
                        Preview
                    </strong>
                </div>

                <div class="card-body p-0">

                    @php
                        $fileType = strtolower($timetable->file_type ?? '');
                    @endphp


                    {{-- PDF --}}
                    @if($fileType === 'pdf')

                        <iframe
                            src="{{ route('timetables.preview', $timetable) }}"
                            width="100%"
                            height="800"
                            style="border: 0;"
                            title="PDF Timetable Preview">
                        </iframe>


                    {{-- Images --}}
                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png']))

                        <div class="text-center p-4 bg-light">

                            <img
                                src="{{ route('timetables.preview', $timetable) }}"
                                alt="{{ $timetable->title }}"
                                class="img-fluid rounded shadow-sm"
                                style="max-height: 800px;">

                        </div>


                    {{-- Excel --}}
                    @elseif(in_array($fileType, ['xls', 'xlsx']))

                        <div class="text-center py-5">

                            <i class="fas fa-file-excel fa-4x text-success mb-3"></i>

                            <h5>Excel Spreadsheet</h5>

                            <p class="text-muted">
                                This file cannot be previewed in the browser.
                            </p>

                            <a href="{{ route('timetables.download', $timetable) }}"
                               class="btn btn-success">

                                <i class="fas fa-download me-1"></i>
                                Download Spreadsheet

                            </a>

                        </div>


                    {{-- Unsupported --}}
                    @else

                        <div class="text-center py-5">

                            <i class="fas fa-file fa-4x text-secondary mb-3"></i>

                            <h5>Preview Not Available</h5>

                            <p class="text-muted">
                                This file type cannot be displayed in the browser.
                            </p>

                            <a href="{{ route('timetables.download', $timetable) }}"
                               class="btn btn-primary">

                                <i class="fas fa-download me-1"></i>
                                Download File

                            </a>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection