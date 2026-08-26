@extends('layouts.master')

@section('title', 'Grievance Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-exclamation-circle text-warning"></i> Grievance Details
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('grievance.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            @if($grievance->canEdit())
                                <a href="{{ route('grievance.edit', $grievance->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            @endif
                            @if($grievance->canAppeal())
                                <button type="button" class="btn btn-info btn-sm" onclick="appealGrievance()">
                                    <i class="fas fa-gavel"></i> Appeal
                                </button>
                            @endif
                            @if($grievance->canDelete())
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                
                <div class="card-body">
                    <!-- Status Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="text-muted">Grievance Code:</span>
                                        <strong class="ml-2">{{ $grievance->grievance_code }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted">Status:</span>
                                        <span class="ml-2 text-dark badge-lg">
                                            {{ $grievance->status_label }}
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted">Priority:</span>
                                        <span class="ml-2 text-dark  badge-lg">
                                            {{ $grievance->priority_label }}
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted">Submitted:</span>
                                        <span class="ml-2">{{ $grievance->submission_date ? $grievance->submission_date->format('d/m/Y') : 'N/A' }}</span>
                                    </div>
                                </div>
                                @if($grievance->assigned_to)
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <span class="text-muted">Assigned To:</span>
                                        <span class="ml-2">
                                            <i class="fas fa-user-circle text-primary"></i>
                                            {{ $grievance->assignedTo->full_name ?? 'N/A' }}
                                            @if($grievance->department)
                                                <span class="text-muted">({{ $grievance->department->name }})</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Grievance Details -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle text-info"></i> Grievance Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4>{{ $grievance->title }}</h4>
                                            <hr>
                                            <strong>Description:</strong>
                                            <div class="p-3 mt-2" style="background: #f8f9fa; border-radius: 5px; border-left: 4px solid #17a2b8;">
                                                {!! nl2br(e($grievance->description)) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-tag"></i> Category:</strong>
                                            <span class="ml-2">{{ $grievance->category->name ?? 'Uncategorized' }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-user"></i> Submitted By:</strong>
                                            <span class="ml-2">
                                                @if($grievance->is_anonymous)
                                                    <span class="text-muted"><i class="fas fa-user-secret"></i> Anonymous</span>
                                                @else
                                                    {{ $grievance->staff->full_name ?? 'N/A' }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    @if($grievance->remarks)
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <strong><i class="fas fa-sticky-note"></i> Remarks:</strong>
                                            <p class="mt-2">{{ $grievance->remarks }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    @if($grievance->attachment || ($grievance->attachments && count($grievance->attachments) > 0))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-paperclip text-primary"></i> Attachments
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if($grievance->attachment)
                                        <div class="col-md-3">
                                            <div class="card text-center">
                                                <div class="card-body">
                                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                                    <p class="mt-2 mb-0">
                                                        <small class="text-muted">{{ basename($grievance->attachment) }}</small>
                                                    </p>
                                                    <a href="{{ Storage::url($grievance->attachment) }}" target="_blank" class="btn btn-sm btn-primary mt-2" download>
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($grievance->attachments && count($grievance->attachments) > 0)
                                            @foreach($grievance->attachments as $file)
                                            <div class="col-md-3">
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        @php
                                                            $extension = pathinfo($file, PATHINFO_EXTENSION);
                                                            $icon = 'fa-file';
                                                            if(in_array($extension, ['pdf'])) $icon = 'fa-file-pdf text-danger';
                                                            elseif(in_array($extension, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                                            elseif(in_array($extension, ['xls', 'xlsx'])) $icon = 'fa-file-excel text-success';
                                                            elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'fa-file-image text-warning';
                                                            elseif(in_array($extension, ['zip', 'rar'])) $icon = 'fa-file-archive text-secondary';
                                                            else $icon = 'fa-file text-info';
                                                        @endphp
                                                        <i class="fas {{ $icon }} fa-3x"></i>
                                                        <p class="mt-2 mb-0">
                                                            <small class="text-muted">{{ basename($file) }}</small>
                                                        </p>
                                                        <a href="{{ Storage::url($file) }}" target="_blank" class="btn btn-sm btn-primary mt-2" download>
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Timeline/History -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history text-info"></i> Timeline & History
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($grievance->histories && count($grievance->histories) > 0)
                                        <div class="timeline">
                                            @foreach($grievance->histories as $history)
                                                <div class="timeline-item">
                                                    <div class="timeline-marker">
                                                        <i class="fas fa-circle text-{{ $history->action === 'submitted' ? 'info' : ($history->action === 'resolved' ? 'success' : ($history->action === 'rejected' ? 'danger' : 'secondary')) }}"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <h6 class="timeline-header">
                                                            <strong>{{ $history->action_label }}</strong>
                                                            <small class="text-muted float-right">
                                                                <i class="fas fa-clock"></i> {{ $history->formatted_date }}
                                                            </small>
                                                        </h6>
                                                        <p class="mb-0">{{ $history->description }}</p>
                                                        @if($history->performedBy)
                                                            <small class="text-muted">
                                                                <i class="fas fa-user"></i> By: {{ $history->performedBy->full_name }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No history available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-comments text-info"></i> Comments
                                    </h5>
                                    <span class="badge bg-light text-dark">{{ count($grievance->comments ?? []) }}</span>
                                </div>
                                <div class="card-body">
                                    @if($grievance->comments && count($grievance->comments) > 0)
                                        <div class="comments-container">
                                            @foreach($grievance->comments as $comment)
                                                <div class="comment-item mb-3 p-3" style="background: #f8f9fa; border-radius: 5px; border-left: 3px solid {{ $comment->is_internal ? '#ffc107' : '#17a2b8' }};">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>
                                                                <i class="fas fa-user-circle text-primary"></i>
                                                                {{ $comment->staff_name }}
                                                                @if($comment->is_internal)
                                                                    <span class="badge badge-warning ml-2">Internal</span>
                                                                @endif
                                                            </strong>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i> {{ $comment->formatted_date }}
                                                        </small>
                                                    </div>
                                                    <p class="mt-2 mb-0">{{ $comment->comment }}</p>
                                                    @if($comment->attachment)
                                                        <div class="mt-2">
                                                            <a href="{{ Storage::url($comment->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                                                <i class="fas fa-paperclip"></i> Attachment
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center mb-0"><i class="fas fa-info-circle"></i> No comments available.</p>
                                    @endif

                                    <!-- Add Comment Form -->
                                    <div class="mt-4 p-3" style="background: #f1f3f5; border-radius: 5px;">
                                        <h6><i class="fas fa-plus-circle text-success"></i> Add Comment</h6>
                                        <form action="{{ route('grievance.add-comment', $grievance->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <textarea name="comment" class="form-control" rows="2" placeholder="Write your comment here..." required></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-file">
                                                            <input type="file" name="attachment" class="custom-file-input" id="commentAttachment">
                                                            <label class="custom-file-label" for="commentAttachment">Attach file</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr')
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" name="is_internal" class="custom-control-input" id="isInternal" value="1">
                                                                <label class="custom-control-label" for="isInternal">
                                                                    <i class="fas fa-lock text-warning"></i> Internal Note
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endif
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

                    <!-- Admin/HR Actions -->
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tools"></i> Admin Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Assign Staff -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0">Assign Staff</h6>
                                                </div>
                                                <div class="card-body">
                                                    <form action="{{ route('grievance.assign', $grievance->id) }}" method="POST">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label for="assigned_to">Assign To</label>
                                                            <select name="assigned_to" id="assigned_to" class="form-control" required>
                                                                <option value="">Select Staff</option>
                                                                @foreach($staff as $staffMember)
                                                                    <option value="{{ $staffMember->id }}" {{ $grievance->assigned_to == $staffMember->id ? 'selected' : '' }}>
                                                                        {{ $staffMember->full_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="department_id">Department</label>
                                                            <select name="department_id" id="department_id" class="form-control">
                                                                <option value="">Select Department</option>
                                                                @foreach($departments ?? [] as $department)
                                                                    <option value="{{ $department->id }}">
                                                                        {{ $department->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="remarks">Remarks</label>
                                                            <textarea name="remarks" class="form-control" rows="2" placeholder="Assignment remarks"></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-user-check"></i> Assign
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Update Status -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0">Update Status</h6>
                                                </div>
                                                <div class="card-body">
                                                    <form action="{{ route('grievance.update-status', $grievance->id) }}" method="POST">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label for="status">Status</label>
                                                            <select name="status" id="status" class="form-control" required>
                                                                <option value="">Select Status</option>
                                                                <option value="under_review" {{ $grievance->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                                                <option value="investigation" {{ $grievance->status == 'investigation' ? 'selected' : '' }}>Investigation</option>
                                                                <option value="resolution_proposed" {{ $grievance->status == 'resolution_proposed' ? 'selected' : '' }}>Resolution Proposed</option>
                                                                <option value="resolved" {{ $grievance->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                                <option value="closed" {{ $grievance->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                                                <option value="rejected" {{ $grievance->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="remarks">Remarks</label>
                                                            <textarea name="remarks" class="form-control" rows="2" placeholder="Status update remarks"></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="resolution">Resolution Details</label>
                                                            <textarea name="resolution" class="form-control" rows="2" placeholder="Provide resolution details if applicable"></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="fas fa-sync"></i> Update Status
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Escalation -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0">Escalate Grievance</h6>
                                                </div>
                                                <div class="card-body">
                                                    <form action="{{ route('grievance.escalate', $grievance->id) }}" method="POST">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="to_staff_id">Escalate To</label>
                                                                    <select name="to_staff_id" id="to_staff_id" class="form-control" required>
                                                                        <option value="">Select Staff</option>
                                                                        @foreach($staff as $staffMember)
                                                                            <option value="{{ $staffMember->id }}">
                                                                                {{ $staffMember->full_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="level">Escalation Level</label>
                                                                    <select name="level" id="level" class="form-control" required>
                                                                        <option value="level_1">Level 1</option>
                                                                        <option value="level_2">Level 2</option>
                                                                        <option value="level_3">Level 3</option>
                                                                        <option value="level_4">Level 4</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="reason">Reason for Escalation</label>
                                                                    <textarea name="reason" id="reason" class="form-control" rows="2" placeholder="Reason..." required></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-arrow-up"></i> Escalate
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                                    <span class="info-box-number">{{ $grievance->created_at->format('l, d F Y h:i A') }}</span>
                                                    <small class="text-muted">by {{ $grievance->staff->full_name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Last Updated</span>
                                                    <span class="info-box-number">{{ $grievance->updated_at->format('l, d F Y h:i A') }}</span>
                                                    @if($grievance->created_at != $grievance->updated_at)
                                                        <small class="text-muted">Updated</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($grievance->resolution_date)
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Resolution Date</span>
                                                    <span class="info-box-number">{{ $grievance->resolution_date->format('l, d F Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($grievance->appeal_deadline)
                                        <div class="col-md-6">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted">Appeal Deadline</span>
                                                    <span class="info-box-number">{{ $grievance->appeal_deadline->format('l, d F Y') }}</span>
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
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<!-- Forms -->
<form id="delete-form" action="{{ route('grievance.destroy', $grievance->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="appeal-form" action="{{ route('grievance.appeal', $grievance->id) }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this grievance? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }

    function appealGrievance() {
        if (confirm('Are you sure you want to appeal this grievance? This will reopen it for review.')) {
            document.getElementById('appeal-form').submit();
        }
    }

    // File input display
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
</script>
@endpush

@push('styles')
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 5px;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-marker .fa-circle {
        font-size: 14px;
    }
    .timeline-content {
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 5px;
        border-left: 3px solid #17a2b8;
    }
    .timeline-header {
        margin-bottom: 5px;
    }
    .custom-file-label::after {
        content: "Browse";
    }
</style>
@endpush