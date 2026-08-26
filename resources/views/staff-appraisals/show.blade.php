@extends('layouts.master')

@section('title', 'Appraisal Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-file-alt text-primary"></i> Appraisal Details
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('staff-appraisals.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            
                                @if($appraisal->status == 'draft')
                                    <a href="{{ route('staff-appraisals.edit', $appraisal->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif
                                <a href="{{ route('staff-appraisals.download', $appraisal->id) }}" class="btn btn-success btn-sm" target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <a href="{{ route('staff-appraisals.view', $appraisal->id) }}" target="_blank" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                           
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Status Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <span class="text-muted">File:</span>
                                    <strong class="ml-2">
                                        <i class="fas {{ $appraisal->file_icon }}"></i>
                                        {{ $appraisal->file_name }}
                                    </strong>
                                </div>
                                <div>
                                    <span class="text-muted">Status:</span>
                                    <span class="ml-2 badge badge-{{ $appraisal->status_badge }}">
                                        {{ ucfirst($appraisal->status) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted">Size:</span>
                                    <span class="ml-2">{{ $appraisal->formatted_file_size }}</span>
                                </div>
                                <div>
                                    <span class="text-muted">Submitted:</span>
                                    <span class="ml-2">{{ $appraisal->submission_date ? $appraisal->submission_date->format('d/m/Y') : 'N/A' }}</span>
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
                                                    <span class="info-box-number">{{ $appraisal->staff->first_name ?? '' }} {{ $appraisal->staff->last_name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Academic Year</span>
                                                    <span class="info-box-number">{{ $appraisal->academicYear->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Term</span>
                                                    <span class="info-box-number">{{ $appraisal->term->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($appraisal->title || $appraisal->description)
                                    <div class="row mt-3">
                                        @if($appraisal->title)
                                        <div class="col-md-12">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Title</span>
                                                    <span class="info-box-number">{{ $appraisal->title }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if($appraisal->description)
                                        <div class="col-md-12">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Description</span>
                                                    <span class="info-box-number">{{ $appraisal->description }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Information -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file text-primary"></i> File Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">File Name</span>
                                                    <span class="info-box-number">{{ $appraisal->file_name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">File Type</span>
                                                    <span class="info-box-number">{{ strtoupper($appraisal->file_type) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">File Size</span>
                                                    <span class="info-box-number">{{ $appraisal->formatted_file_size }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">MIME Type</span>
                                                    <span class="info-box-number">{{ $appraisal->file_mime ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Information -->
                    @if($appraisal->reviewed_by || $appraisal->reviewer_comments)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clipboard-check text-primary"></i> Review Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Reviewed By</span>
                                                    <span class="info-box-number">{{ $appraisal->reviewer->first_name ?? '' }} {{ $appraisal->reviewer->last_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Reviewed At</span>
                                                    <span class="info-box-number">{{ $appraisal->reviewed_at ? $appraisal->reviewed_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Status</span>
                                                    <span class="info-box-number">
                                                        <span class="badge badge-{{ $appraisal->status_badge }}">
                                                            {{ ucfirst($appraisal->status) }}
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($appraisal->reviewer_comments)
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Reviewer Comments</span>
                                                    <span class="info-box-number">{{ $appraisal->reviewer_comments }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cog text-primary"></i> Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <a href="{{ route('staff-appraisals.download', $appraisal->id) }}" class="btn btn-success btn-block" target="_blank">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('staff-appraisals.view', $appraisal->id) }}" target="_blank" class="btn btn-info btn-block">
                                                <i class="fas fa-eye"></i> View File
                                            </a>
                                        </div>
                                       
                                            @if($appraisal->status == 'draft')
                                                <div class="col-md-3">
                                                    <a href="{{ route('staff-appraisals.edit', $appraisal->id) }}" class="btn btn-warning btn-block">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </div>
                                                <div class="col-md-3">
                                                    <form action="{{ route('staff-appraisals.toggle-status', $appraisal->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Submit this appraisal?')">
                                                            <i class="fas fa-paper-plane"></i> Submit
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        
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
                                                    <span class="info-box-number">{{ $appraisal->created_at->format('l, d F Y h:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Last Updated</span>
                                                    <span class="info-box-number">{{ $appraisal->updated_at->format('l, d F Y h:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Created By</span>
                                                    <span class="info-box-number">{{ $appraisal->creator->first_name ?? '' }} {{ $appraisal->creator->last_name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection