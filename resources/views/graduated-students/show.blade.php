@extends('layouts.master')

@section('title', 'Graduate Details')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-user-graduate me-2"></i>
                Graduate Details
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Student ID</dt>
                        <dd class="col-sm-8">{{ $graduate->student->student_id ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8">{{ $graduate->student->full_name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $graduate->student->email ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $graduate->student->phone ?? 'N/A' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Class</dt>
                        <dd class="col-sm-8">{{ $graduate->studentClass->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Academic Year</dt>
                        <dd class="col-sm-8">{{ $graduate->academicYear->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Graduated
                            </span>
                        </dd>

                        <dt class="col-sm-4">Graduation Date</dt>
                        <dd class="col-sm-8">
                            {{ $graduate->updated_at ? $graduate->updated_at->format('d M Y') : 'N/A' }}
                        </dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $graduate->remarks ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Additional Information
                            </h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Record Created</small>
                                    <p class="mb-0">{{ $graduate->created_at ? $graduate->created_at->format('d M Y h:i A') : 'N/A' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Last Updated</small>
                                    <p class="mb-0">{{ $graduate->updated_at ? $graduate->updated_at->format('d M Y h:i A') : 'N/A' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Assignment ID</small>
                                    <p class="mb-0">#{{ $graduate->id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('graduates.index', request()->query()) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('graduates.certificate', $graduate->id) }}" class="btn btn-success btn-sm" target="_blank">
                <i class="fas fa-certificate me-1"></i> Download Certificate
            </a>
            <button onclick="window.print()" class="btn btn-info btn-sm">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dl-horizontal dt {
        font-weight: 600;
        color: #555;
    }
    .dl-horizontal dd {
        margin-bottom: 10px;
    }
    .card-footer .btn {
        margin-right: 5px;
    }
    @media print {
        .btn, .card-footer {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush