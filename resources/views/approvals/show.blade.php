@extends('layouts.master')

@section('title', 'Lesson Note Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-file-alt"></i> Lesson Note Details</h1>
            <p class="text-muted">Review lesson note #{{ $lessonNote->note_code }}</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('approvals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Approvals
            </a>
            @if($lessonNote->canBeApproved())
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times"></i> Reject
                </button>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changesModal">
                    <i class="fas fa-edit"></i> Request Changes
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Lesson Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lesson Information</h5>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'draft' => 'secondary',
                                'approved' => 'success',
                                'rejected' => 'danger'
                            ];
                            $statusLabels = [
                                'pending' => 'Pending',
                                'draft' => 'Draft',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected'
                            ];
                            $status = $lessonNote->status ?? 'pending';
                            $color = $statusColors[$status] ?? 'secondary';
                            $label = $statusLabels[$status] ?? ucfirst($status);
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ $label }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Note Code:</strong> {{ $lessonNote->note_code }}</p>
                            <p><strong>Topic:</strong> {{ $lessonNote->topic }}</p>
                            @if($lessonNote->sub_topic)
                                <p><strong>Sub Topic:</strong> {{ $lessonNote->sub_topic }}</p>
                            @endif
                            <p><strong>Type:</strong> {{ ucfirst($lessonNote->type) }}</p>
                            <p><strong>Lesson Date:</strong> {{ $lessonNote->lesson_date->format('d/m/Y') }}</p>
                            @if($lessonNote->start_time && $lessonNote->end_time)
                                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($lessonNote->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($lessonNote->end_time)->format('H:i') }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p><strong>Staff:</strong> {{ $lessonNote->staff->full_name ?? 'N/A' }}</p>
                            <p><strong>Class:</strong> {{ $lessonNote->studentClass->name ?? 'N/A' }}</p>
                            <p><strong>Subject:</strong> {{ $lessonNote->subject->name ?? 'N/A' }}</p>
                            <p><strong>Academic Year:</strong> {{ $lessonNote->academicYear->name ?? 'N/A' }}</p>
                            <p><strong>Term:</strong> {{ $lessonNote->term->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Lesson Content</h5>
                </div>
                <div class="card-body">
                    @if($lessonNote->description)
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p>{{ $lessonNote->description }}</p>
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>Content:</strong>
                        <div class="content-wrapper p-3 bg-light rounded">
                            {!! nl2br(e($lessonNote->content)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($lessonNote->learning_objectives)
                                <strong>Learning Objectives:</strong>
                                <ul>
                                    @foreach($lessonNote->learning_objectives as $objective)
                                        <li>{{ $objective }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($lessonNote->learning_outcomes)
                                <strong>Learning Outcomes:</strong>
                                <ul>
                                    @foreach($lessonNote->learning_outcomes as $outcome)
                                        <li>{{ $outcome }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($lessonNote->teaching_aids)
                                <strong>Teaching Aids:</strong>
                                <ul>
                                    @foreach($lessonNote->teaching_aids as $aid)
                                        <li>{{ $aid }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($lessonNote->assessment_methods)
                                <strong>Assessment Methods:</strong>
                                <ul>
                                    @foreach($lessonNote->assessment_methods as $method)
                                        <li>{{ $method }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    @if($lessonNote->homework)
                        <div class="mt-3">
                            <strong>Homework:</strong>
                            <p>{{ $lessonNote->homework }}</p>
                        </div>
                    @endif

                    @if($lessonNote->remarks)
                        <div class="mt-3">
                            <strong>Remarks:</strong>
                            <p>{{ $lessonNote->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attachments - Display only file names without download links -->
            @if($lessonNote->hasAttachment())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paperclip"></i> Attachments</h5>
                    </div>
                    <div class="card-body">
                        @if($lessonNote->attachment)
                            <div class="mb-2">
                                <i class="fas fa-file"></i>
                                <span>{{ basename($lessonNote->attachment) }}</span>
                            </div>
                        @endif
                        @if($lessonNote->attachments)
                            @foreach($lessonNote->attachments as $attachment)
                                <div class="mb-2">
                                    <i class="fas fa-file"></i>
                                    <span>{{ basename($attachment) }}</span>
                                </div>
                            @endforeach
                        @endif
                        <small class="text-muted">Note: Attachments are available for reference only.</small>
                    </div>
                </div>
            @endif

            <!-- Comments History -->
            @if($lessonNote->comments && count($lessonNote->comments) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Approval History & Comments</h5>
                    </div>
                    <div class="card-body">
                        @foreach($lessonNote->comments as $comment)
                            <div class="comment-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <strong>
                                        @if(isset($comment['type']))
                                            {{ ucfirst(str_replace('_', ' ', $comment['type'])) }}
                                        @else
                                            Comment
                                        @endif
                                    </strong>
                                    <small class="text-muted">
                                        {{ isset($comment['commented_at']) ? \Carbon\Carbon::parse($comment['commented_at'])->format('d/m/Y H:i') : 'N/A' }}
                                    </small>
                                </div>
                                @if(isset($comment['status']))
                                    <span class="badge bg-{{ $comment['status'] === 'approved' ? 'success' : ($comment['status'] === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($comment['status']) }}
                                    </span>
                                @endif
                                <p class="mt-2 mb-0">{{ $comment['comment'] ?? 'No comment provided' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar - Approval Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Approval Actions</h5>
                </div>
                <div class="card-body">
                    @if($lessonNote->status === 'approved')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> This lesson note has been approved.
                            @if($lessonNote->approved_at)
                                <br><small>Approved on: {{ $lessonNote->approved_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    @elseif($lessonNote->status === 'rejected')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> This lesson note has been rejected.
                            @if($lessonNote->rejected_at)
                                <br><small>Rejected on: {{ $lessonNote->rejected_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    @elseif($lessonNote->canBeApproved())
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="fas fa-check"></i> Approve Lesson Note
                            </button>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changesModal">
                                <i class="fas fa-edit"></i> Request Changes
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject Lesson Note
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This lesson note cannot be approved in its current state.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Created:</span>
                        <span>{{ $lessonNote->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span>Last Updated:</span>
                        <span>{{ $lessonNote->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($lessonNote->approved_at)
                        <div class="d-flex justify-content-between mt-2">
                            <span>Approved:</span>
                            <span>{{ $lessonNote->approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if($lessonNote->rejected_at)
                        <div class="d-flex justify-content-between mt-2">
                            <span>Rejected:</span>
                            <span>{{ $lessonNote->rejected_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('approvals.approve', $lessonNote->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveModalLabel">
                        <i class="fas fa-check-circle"></i> Approve Lesson Note
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You are about to approve this lesson note.
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Note Code:</strong></div>
                        <div class="col-8">{{ $lessonNote->note_code }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Topic:</strong></div>
                        <div class="col-8">{{ $lessonNote->topic }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Staff:</strong></div>
                        <div class="col-8">{{ $lessonNote->staff->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="approval_notes" class="form-label">
                            Approval Notes <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea name="approval_notes" id="approval_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes for approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirm Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('approvals.reject', $lessonNote->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="fas fa-times-circle"></i> Reject Lesson Note
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> You are about to reject this lesson note. This action cannot be undone.
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Note Code:</strong></div>
                        <div class="col-8">{{ $lessonNote->note_code }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Topic:</strong></div>
                        <div class="col-8">{{ $lessonNote->topic }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Staff:</strong></div>
                        <div class="col-8">{{ $lessonNote->staff->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" 
                                  placeholder="Provide reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Confirm Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Changes Modal -->
<div class="modal fade" id="changesModal" tabindex="-1" aria-labelledby="changesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('approvals.request-changes', $lessonNote->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="changesModalLabel">
                        <i class="fas fa-edit"></i> Request Changes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Request changes to this lesson note. The status will be changed to Draft.
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Note Code:</strong></div>
                        <div class="col-8">{{ $lessonNote->note_code }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Topic:</strong></div>
                        <div class="col-8">{{ $lessonNote->topic }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Staff:</strong></div>
                        <div class="col-8">{{ $lessonNote->staff->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">
                            Feedback <span class="text-danger">*</span>
                        </label>
                        <textarea name="feedback" id="feedback" class="form-control" rows="3" 
                                  placeholder="Provide detailed feedback for changes..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Send Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert:not(.modal .alert)');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Toast notifications for session messages
        @if(session('success'))
            var successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
            successAlert.style.zIndex = '9999';
            successAlert.style.maxWidth = '400px';
            successAlert.innerHTML = `
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(successAlert);
            setTimeout(function() {
                if (successAlert.parentNode) {
                    successAlert.remove();
                }
            }, 5000);
        @endif

        @if(session('error'))
            var errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3';
            errorAlert.style.zIndex = '9999';
            errorAlert.style.maxWidth = '400px';
            errorAlert.innerHTML = `
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(errorAlert);
            setTimeout(function() {
                if (errorAlert.parentNode) {
                    errorAlert.remove();
                }
            }, 5000);
        @endif

        @if(session('warning'))
            var warningAlert = document.createElement('div');
            warningAlert.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 end-0 m-3';
            warningAlert.style.zIndex = '9999';
            warningAlert.style.maxWidth = '400px';
            warningAlert.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(warningAlert);
            setTimeout(function() {
                if (warningAlert.parentNode) {
                    warningAlert.remove();
                }
            }, 5000);
        @endif
    });
</script>
@endpush
@endsection