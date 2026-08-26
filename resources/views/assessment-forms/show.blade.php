@extends('layouts.master')

@section('title', 'Assessment Form Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-file-alt text-primary"></i> Assessment Form Details
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('assessment-forms.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            
                                <a href="{{ route('assessment-forms.edit', $assessmentForm->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('assessment-forms.download', $assessmentForm->id) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                        
                            <a href="{{ route('assessment-forms.view', $assessmentForm->id) }}" target="_blank" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> View File
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                
                <div class="card-body">
                    <!-- Status Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <span class="text-muted">File:</span>
                                    <strong class="ml-2">
                                        <i class="fas {{ $assessmentForm->file_icon }}"></i>
                                        {{ $assessmentForm->file_name }}
                                    </strong>
                                </div>
                                <div>
                                    <span class="text-muted">Status:</span>
                                    <span class="ml-2 badge badge-{{ $assessmentForm->status_badge }}">
                                        {{ ucfirst($assessmentForm->status) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted">Type:</span>
                                    <span class="ml-2 badge badge-{{ $assessmentForm->assessment_type_badge }}">
                                        {{ ucfirst($assessmentForm->assessment_type) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted">Size:</span>
                                    <span class="ml-2">{{ $assessmentForm->formatted_file_size }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle text-info"></i> Basic Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Staff</span>
                                                    <span class="info-box-number">{{ $assessmentForm->staff->full_name ?? $assessmentForm->staff->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Class</span>
                                                    <span class="info-box-number">{{ $assessmentForm->studentClass->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Subject</span>
                                                    <span class="info-box-number">{{ $assessmentForm->subject->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Academic Year</span>
                                                    <span class="info-box-number">{{ $assessmentForm->academicYear->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Term</span>
                                                    <span class="info-box-number">{{ $assessmentForm->term->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Assessment Date</span>
                                                    <span class="info-box-number">{{ $assessmentForm->assessment_date->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($assessmentForm->due_date)
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Due Date</span>
                                                        <span class="info-box-number">{{ $assessmentForm->due_date->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    @if($assessmentForm->title || $assessmentForm->description)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-align-left text-primary"></i> Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($assessmentForm->title)
                                        <h5>{{ $assessmentForm->title }}</h5>
                                    @endif
                                    @if($assessmentForm->description)
                                        <p class="mb-0">{{ $assessmentForm->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- File Information -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle text-info"></i> File Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">File Name</span>
                                                    <span class="info-box-number">{{ $assessmentForm->file_name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">File Type</span>
                                                    <span class="info-box-number">{{ strtoupper($assessmentForm->file_type) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">File Size</span>
                                                    <span class="info-box-number">{{ $assessmentForm->formatted_file_size }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">MIME Type</span>
                                                    <span class="info-box-number">{{ $assessmentForm->file_mime ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock text-secondary"></i> Metadata
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Created At</span>
                                                    <span class="info-box-number">{{ $assessmentForm->created_at->format('l, d F Y h:i A') }}</span>
                                                    <small class="text-muted">by {{ $assessmentForm->creator->full_name ?? $assessmentForm->creator->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Last Updated</span>
                                                    <span class="info-box-number">{{ $assessmentForm->updated_at->format('l, d F Y h:i A') }}</span>
                                                    @if($assessmentForm->created_at != $assessmentForm->updated_at)
                                                        <small class="text-muted">Updated</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Statistics</span>
                                                    <span class="info-box-number">
                                                        <i class="fas fa-eye"></i> {{ $assessmentForm->views_count ?? 0 }}
                                                        <span class="ml-3"><i class="fas fa-download"></i> {{ $assessmentForm->downloads_count ?? 0 }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        padding: 15px;
        border-radius: 5px;
    }
    .info-box-content {
        text-align: center;
    }
    .info-box-text {
        font-size: 14px;
    }
    .info-box-number {
        font-size: 18px;
        font-weight: bold;
    }
    .image-preview img {
        max-width: 100%;
        height: auto;
    }
    .embed-responsive {
        position: relative;
        display: block;
        width: 100%;
        padding: 0;
        overflow: hidden;
    }
    .embed-responsive-4by3 {
        padding-bottom: 75%;
    }
    .embed-responsive-item {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
</style>
@endpush