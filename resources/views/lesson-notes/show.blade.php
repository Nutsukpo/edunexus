@extends('layouts.master')

@section('title', 'Lesson Note Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-book text-primary"></i> Lesson Note Details
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('lesson-notes.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="{{ route('lesson-notes.edit', $lessonNote->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-success btn-sm" onclick="cloneNote({{ $lessonNote->id }})">
                                <i class="fas fa-copy"></i> Clone
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $lessonNote->id }})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                
                <div class="card-body">
                    <!-- Note Code and Status Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <span class="text-muted">Note Code:</span>
                                    <strong class="ml-2">{{ $lessonNote->note_code }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted">Status:</span>
                                    <span class="ml-2">
                                        @if($lessonNote->status)
                                            {{ ucfirst($lessonNote->status) }}
                                        @else
                                            <span class="text-warning">Pending</span>
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted">Type:</span>
                                    <span class="ml-2">{{ ucfirst($lessonNote->type) }}</span>
                                </div>
                                <div>
                                    <span class="text-muted">Date:</span>
                                    <span class="ml-2">{{ $lessonNote->lesson_date->format('d/m/Y') }}</span>
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
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Staff</span>
                                                    <span class="info-box-number">{{ $lessonNote->staff->full_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Class</span>
                                                    <span class="info-box-number">{{ $lessonNote->studentClass->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Subject</span>
                                                    <span class="info-box-number">{{ $lessonNote->subject->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Academic Year</span>
                                                    <span class="info-box-number">{{ $lessonNote->academicYear->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Term</span>
                                                    <span class="info-box-number">{{ $lessonNote->term->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Duration</span>
                                                    <span class="info-box-number">{{ $lessonNote->duration ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Start Time</span>
                                                    <span class="info-box-number">{{ $lessonNote->start_time ? $lessonNote->start_time->format('h:i A') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">End Time</span>
                                                    <span class="info-box-number">{{ $lessonNote->end_time ? $lessonNote->end_time->format('h:i A') : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0"><i class="fas fa-heading text-primary"></i> Topic</h6>
                                                </div>
                                                <div class="card-body">
                                                    <h5>{{ $lessonNote->topic }}</h5>
                                                    @if($lessonNote->sub_topic)
                                                        <hr>
                                                        <strong>Sub Topic:</strong>
                                                        <p class="mb-0">{{ $lessonNote->sub_topic }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0"><i class="fas fa-align-left text-primary"></i> Description</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0">{{ $lessonNote->description ?? 'No description provided.' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file-alt text-primary"></i> Lesson Content
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="p-4" style="background: #f8f9fa; border-radius: 5px; border-left: 4px solid #17a2b8;">
                                        {!! nl2br(e($lessonNote->content)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Learning Objectives & Outcomes -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bullseye text-success"></i> Learning Objectives
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($lessonNote->learning_objectives && count($lessonNote->learning_objectives) > 0)
                                        <ul class="list-group list-group-flush">
                                            @foreach($lessonNote->learning_objectives as $index => $objective)
                                                <li class="list-group-item">
                                                    <span class="badge bg-light text-dark mr-2">{{ $index + 1 }}</span>
                                                    {{ $objective }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted text-center mb-0"><i class="fas fa-info-circle"></i> No learning objectives provided.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-check-double text-success"></i> Learning Outcomes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($lessonNote->learning_outcomes && count($lessonNote->learning_outcomes) > 0)
                                        <ul class="list-group list-group-flush">
                                            @foreach($lessonNote->learning_outcomes as $index => $outcome)
                                                <li class="list-group-item">
                                                    <span class="badge bg-light text-dark mr-2">{{ $index + 1 }}</span>
                                                    {{ $outcome }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted text-center mb-0"><i class="fas fa-info-circle"></i> No learning outcomes provided.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery & Assessment -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chalkboard-teacher text-warning"></i> Delivery Method
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p>
                                        @if($lessonNote->delivery_method)
                                            {{ ucfirst(str_replace('_', ' ', $lessonNote->delivery_method)) }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </p>
                                    
                                    @if($lessonNote->teaching_aids && count($lessonNote->teaching_aids) > 0)
                                        <hr>
                                        <strong><i class="fas fa-tools"></i> Teaching Aids:</strong>
                                        <div class="mt-2">
                                            @foreach($lessonNote->teaching_aids as $aid)
                                                <span class="badge bg-light text-dark mr-1">{{ $aid }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tasks text-warning"></i> Assessment
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($lessonNote->assessment_methods && count($lessonNote->assessment_methods) > 0)
                                        <strong>Methods:</strong>
                                        <ul class="list-unstyled mt-2">
                                            @foreach($lessonNote->assessment_methods as $method)
                                                <li><i class="fas fa-check-circle text-success"></i> {{ $method }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">No assessment methods provided.</p>
                                    @endif
                                    
                                    @if($lessonNote->homework)
                                        <hr>
                                        <strong><i class="fas fa-home"></i> Homework:</strong>
                                        <p class="mt-2 mb-0">{{ $lessonNote->homework }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users text-warning"></i> Student Engagement
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <div class="text-muted small">Expected</div>
                                                <h4 class="mb-0">{{ $lessonNote->expected_students ?? 'N/A' }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <div class="text-muted small">Actual</div>
                                                <h4 class="mb-0">{{ $lessonNote->actual_students ?? 'N/A' }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    @if($lessonNote->student_participation && count($lessonNote->student_participation) > 0)
                                        <hr>
                                        <strong>Participation:</strong>
                                        <div class="mt-2">
                                            @foreach($lessonNote->student_participation as $key => $value)
                                                <div class="d-flex justify-content-between">
                                                    <span>{{ ucfirst($key) }}:</span>
                                                    <span class="badge bg-light text-dark">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle text-secondary"></i> Additional Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0"><i class="fas fa-comment"></i> Remarks</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0">{{ $lessonNote->remarks ?? 'No remarks provided.' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0"><i class="fas fa-exclamation-triangle text-warning"></i> Challenges</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0">{{ $lessonNote->challenges ?? 'No challenges reported.' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0"><i class="fas fa-lightbulb text-success"></i> Recommendations</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0">{{ $lessonNote->recommendations ?? 'No recommendations provided.' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($lessonNote->resources && count($lessonNote->resources) > 0)
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-header bg-light">
                                                        <h6 class="card-title mb-0"><i class="fas fa-link text-primary"></i> Resources</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach($lessonNote->resources as $resource)
                                                                <li class="list-group-item">
                                                                    <i class="fas fa-external-link-alt text-info"></i>
                                                                    <a href="{{ $resource }}" target="_blank">{{ $resource }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments - FIXED SECTION -->
                    @php
                        $hasSingleAttachment = !empty($lessonNote->attachment);
                        $hasMultipleAttachments = !empty($lessonNote->attachments) && is_array($lessonNote->attachments) && count($lessonNote->attachments) > 0;
                        $hasAttachments = $hasSingleAttachment || $hasMultipleAttachments;
                        
                        // Ensure attachments is always an array
                        $allAttachments = [];
                        if ($hasSingleAttachment) {
                            $allAttachments[] = $lessonNote->attachment;
                        }
                        if ($hasMultipleAttachments) {
                            $allAttachments = array_merge($allAttachments, $lessonNote->attachments);
                        }
                    @endphp

                    @if($hasAttachments)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-paperclip text-primary"></i> Attachments
                                        <span class="badge bg-primary ml-2">{{ count($allAttachments) }}</span>
                                    </h5>
                                    <button type="button" class="btn btn-success btn-sm" onclick="downloadAllAttachments()">
                                        <i class="fas fa-download"></i> Download All
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="10%">Type</th>
                                                    <th width="35%">File Name</th>
                                                    <th width="15%">Size</th>
                                                    <th width="20%">Uploaded</th>
                                                    <th width="15%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($allAttachments as $index => $file)
                                                    @php
                                                        $filePath = $file;
                                                        $fileName = basename($filePath);
                                                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                        
                                                        // Determine file icon and color
                                                        $iconClass = 'fa-file';
                                                        $iconColor = 'text-secondary';
                                                        
                                                        if (in_array($extension, ['pdf'])) {
                                                            $iconClass = 'fa-file-pdf';
                                                            $iconColor = 'text-danger';
                                                        } elseif (in_array($extension, ['doc', 'docx'])) {
                                                            $iconClass = 'fa-file-word';
                                                            $iconColor = 'text-primary';
                                                        } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                                                            $iconClass = 'fa-file-excel';
                                                            $iconColor = 'text-success';
                                                        } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                                            $iconClass = 'fa-file-powerpoint';
                                                            $iconColor = 'text-warning';
                                                        } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'])) {
                                                            $iconClass = 'fa-file-image';
                                                            $iconColor = 'text-info';
                                                        } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                                                            $iconClass = 'fa-file-archive';
                                                            $iconColor = 'text-warning';
                                                        } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'])) {
                                                            $iconClass = 'fa-file-video';
                                                            $iconColor = 'text-danger';
                                                        } elseif (in_array($extension, ['mp3', 'wav', 'aac', 'ogg', 'wma'])) {
                                                            $iconClass = 'fa-file-audio';
                                                            $iconColor = 'text-success';
                                                        } elseif (in_array($extension, ['txt', 'log'])) {
                                                            $iconClass = 'fa-file-alt';
                                                            $iconColor = 'text-secondary';
                                                        } elseif (in_array($extension, ['html', 'htm', 'css', 'js', 'php'])) {
                                                            $iconClass = 'fa-file-code';
                                                            $iconColor = 'text-info';
                                                        }
                                                        
                                                        // FIX: Use the public disk explicitly
                                                        $fileSize = '';
                                                        $fileExists = false;
                                                        try {
                                                            if (Storage::disk('public')->exists($filePath)) {
                                                                $fileSize = Storage::disk('public')->size($filePath);
                                                                $fileExists = true;
                                                                
                                                                // Format file size
                                                                if ($fileSize >= 1073741824) {
                                                                    $fileSize = number_format($fileSize / 1073741824, 2) . ' GB';
                                                                } elseif ($fileSize >= 1048576) {
                                                                    $fileSize = number_format($fileSize / 1048576, 2) . ' MB';
                                                                } elseif ($fileSize >= 1024) {
                                                                    $fileSize = number_format($fileSize / 1024, 2) . ' KB';
                                                                } else {
                                                                    $fileSize = $fileSize . ' bytes';
                                                                }
                                                            } else {
                                                                $fileSize = 'N/A';
                                                            }
                                                        } catch (\Exception $e) {
                                                            $fileSize = 'N/A';
                                                        }
                                                        
                                                        // Get last modified date - FIX: Use public disk
                                                        $lastModified = '';
                                                        try {
                                                            if ($fileExists && Storage::disk('public')->exists($filePath)) {
                                                                $lastModified = \Carbon\Carbon::createFromTimestamp(Storage::disk('public')->lastModified($filePath))->format('d/m/Y h:i A');
                                                            }
                                                        } catch (\Exception $e) {
                                                            $lastModified = 'N/A';
                                                        }
                                                        
                                                        // FIX: Generate correct public URL
                                                        $fileUrl = Storage::disk('public')->url($filePath);
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td class="text-center">
                                                            <i class="fas {{ $iconClass }} {{ $iconColor }} fa-2x" title="{{ strtoupper($extension) }} File"></i>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <strong>{{ $fileName }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ strtoupper($extension) }} File</small>
                                                                    @if(!$fileExists)
                                                                        <span class="badge bg-warning ml-1">File Missing</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted">{{ $fileSize }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $lastModified }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                @if($fileExists)
                                                                    <a href="{{ route('lesson-notes.download-attachment', ['lessonNote' => $lessonNote->id, 'file' => base64_encode($filePath)]) }}" 
                                                                       class="btn btn-primary btn-sm" 
                                                                       title="Download">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']))
                                                                        <button type="button" 
                                                                                class="btn btn-info btn-sm" 
                                                                                onclick="previewAttachment('{{ $fileUrl }}', '{{ $fileName }}')"
                                                                                title="Preview">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                    @elseif($extension === 'pdf')
                                                                        <a href="{{ $fileUrl }}" 
                                                                           class="btn btn-info btn-sm" 
                                                                           target="_blank"
                                                                           title="View PDF">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    <span class="btn btn-secondary btn-sm disabled" title="File not found">
                                                                        <i class="fas fa-exclamation-triangle"></i>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Comments -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-comments text-info"></i> Comments
                                    </h5>
                                    <span class="badge bg-light text-dark">{{ count($lessonNote->comments ?? []) }}</span>
                                </div>
                                <div class="card-body">
                                    @if($lessonNote->comments && count($lessonNote->comments) > 0)
                                        <div class="comments-container">
                                            @foreach($lessonNote->comments as $index => $comment)
                                                <div class="comment-item mb-3 p-3" style="background: #f8f9fa; border-radius: 5px; border-left: 3px solid #17a2b8;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>
                                                                <i class="fas fa-user-circle text-primary"></i>
                                                                @php
                                                                    $commentedStaff = $staffs->where('id', $comment['commented_by'] ?? null)->first();
                                                                @endphp
                                                                {{ $commentedStaff ? $commentedStaff->full_name : 'Unknown Staff' }}
                                                            </strong>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i> 
                                                            {{ isset($comment['commented_at']) ? \Carbon\Carbon::parse($comment['commented_at'])->format('d/m/Y h:i A') : 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <p class="mt-2 mb-0">{{ $comment['comment'] ?? 'N/A' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center mb-0"><i class="fas fa-info-circle"></i> No comments available.</p>
                                    @endif

                                    <!-- Add Comment Form -->
                                    <div class="mt-4 p-3" style="background: #f1f3f5; border-radius: 5px;">
                                        <h6><i class="fas fa-plus-circle text-success"></i> Add Comment</h6>
                                        <form action="{{ route('lesson-notes.comments.store', $lessonNote->id) }}" method="POST">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <textarea name="comment" class="form-control" rows="2" placeholder="Write your comment here..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="commented_by" class="form-control" required>
                                                            <option value="">Select Staff</option>
                                                            @foreach($staffs as $staff)
                                                                <option value="{{ $staff->id }}" {{ auth()->user()->staff_id == $staff->id ? 'selected' : '' }}>
                                                                    {{ $staff->full_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="fas fa-paper-plane"></i> Post Comment
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
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
                                        <div class="col-md-6">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Created At</span>
                                                    <span class="info-box-number">{{ $lessonNote->created_at->format('l, d F Y h:i A') }}</span>
                                                    <small class="text-muted">by {{ $lessonNote->staff->full_name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Last Updated</span>
                                                    <span class="info-box-number">{{ $lessonNote->updated_at->format('l, d F Y h:i A') }}</span>
                                                    @if($lessonNote->created_at != $lessonNote->updated_at)
                                                        <small class="text-muted">Updated</small>
                                                    @endif
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

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewLabel">File Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid" style="max-height: 80vh;">
            </div>
            <div class="modal-footer">
                <a id="downloadPreviewBtn" href="#" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Clone Form -->
<form id="clone-form" action="" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this lesson note? This action cannot be undone.')) {
            document.getElementById('delete-form').action = '{{ url("lesson-notes") }}/' + id;
            document.getElementById('delete-form').submit();
        }
    }

    function cloneNote(id) {
        if (confirm('Clone this lesson note? This will create a new copy.')) {
            document.getElementById('clone-form').action = '{{ url("lesson-notes") }}/' + id + '/clone';
            document.getElementById('clone-form').submit();
        }
    }

    function previewAttachment(fileUrl, fileName) {
        $('#previewImage').attr('src', fileUrl);
        $('#imagePreviewLabel').text('Preview: ' + fileName);
        $('#downloadPreviewBtn').attr('href', fileUrl);
        $('#imagePreviewModal').modal('show');
    }

    function downloadAllAttachments() {
        @if(isset($allAttachments) && count($allAttachments) > 0)
            @foreach($allAttachments as $file)
                window.open('{{ route("lesson-notes.download-attachment", ["lessonNote" => $lessonNote->id, "file" => base64_encode($file)]) }}', '_blank');
            @endforeach
        @endif
    }

    $(document).ready(function() {
        // Auto-hide success messages after 5 seconds
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 5000);

        // Initialize tooltips
        $('[title]').tooltip();
    });
</script>
@endpush