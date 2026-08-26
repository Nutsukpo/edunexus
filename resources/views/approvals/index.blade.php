@extends('layouts.master')

@section('title', 'Lesson Notes Approval')

@section('content')

<style>
    /* =========================================================
       LESSON NOTE APPROVAL - SELF CONTAINED UI
       Does not depend on Bootstrap modal JavaScript.
       Compatible with the existing Bootstrap 4/SB Admin layout.
       ========================================================= */

    .approval-page {
        padding: 20px 0 40px;
    }

    .approval-header {
        margin-bottom: 24px;
    }

    .approval-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #343a40;
        margin: 0 0 6px;
    }

    .approval-header p {
        margin: 0;
        color: #6c757d;
    }

    .stat-card {
        border: 0;
        border-radius: 10px;
        min-height: 115px;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
    }

    .stat-card .card-body {
        padding: 18px;
    }

    .stat-card .stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
        opacity: .9;
        margin-bottom: 8px;
    }

    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }

    .filter-card,
    .notes-card {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }

    .filter-card .card-body,
    .notes-card .card-body {
        padding: 20px;
    }

    .filter-label {
        display: block;
        font-weight: 600;
        color: #495057;
        margin-bottom: 7px;
    }

    .approval-select,
    .approval-input {
        width: 100%;
        height: 40px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background: #fff !important;
        color: #212529 !important;
        padding: 7px 12px;
        outline: none;
    }

    .approval-select option {
        background: #fff !important;
        color: #212529 !important;
    }

    .approval-select:focus,
    .approval-input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 .15rem rgba(0,123,255,.12);
    }

    .notes-table {
        margin-bottom: 0;
    }

    .notes-table thead th {
        background: #fff !important;
        color: black !important;
        border-color: #343a40 !important;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .notes-table td {
        vertical-align: middle;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-draft {
        background: #e2e3e5;
        color: #383d41;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .action-buttons .btn {
        min-width: 36px;
    }

    /*
     * CUSTOM MODAL
     * Completely independent of Bootstrap's modal JS.
     * This avoids conflicts caused by Bootstrap 4 + Bootstrap 5
     * attributes/scripts in the master layout.
     */
    .approval-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100000;
        overflow-y: auto;
        padding: 30px 15px;
        background: rgba(0,0,0,.55);
    }

    .approval-modal.is-open {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .approval-modal-dialog {
        position: relative;
        width: 100%;
        max-width: 560px;
        margin: auto;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 15px 50px rgba(0,0,0,.30);
        animation: approvalModalIn .18s ease-out;
    }

    @keyframes approvalModalIn {
        from {
            opacity: 0;
            transform: translateY(-15px) scale(.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .approval-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-radius: 10px 10px 0 0;
    }

    .approval-modal-header.approve {
        background: #28a745;
        color: #fff;
    }

    .approval-modal-header.reject {
        background: #dc3545;
        color: #fff;
    }

    .approval-modal-header.changes {
        background: #ffc107;
        color: #212529;
    }

    .approval-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
    }

    .approval-modal-close {
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 27px;
        line-height: 1;
        cursor: pointer;
        opacity: .9;
        padding: 0 3px;
    }

    .approval-modal-close:hover {
        opacity: 1;
    }

    .approval-modal-body {
        padding: 20px;
    }

    .approval-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
    }

    .note-info {
        margin-bottom: 15px;
    }

    .note-info-row {
        display: flex;
        padding: 6px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .note-info-label {
        width: 105px;
        flex: 0 0 105px;
        font-weight: 700;
        color: #495057;
    }

    .note-info-value {
        flex: 1;
        color: #212529;
        word-break: break-word;
    }

    .modal-message {
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 16px;
    }

    .modal-message.info {
        background: #d1ecf1;
        color: #0c5460;
    }

    .modal-message.warning {
        background: #fff3cd;
        color: #856404;
    }

    .approval-textarea {
        width: 100%;
        min-height: 100px;
        resize: vertical;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 10px 12px;
        background: #fff !important;
        color: #212529 !important;
    }

    .approval-textarea:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 .15rem rgba(0,123,255,.12);
    }

    .approval-textarea.invalid {
        border-color: #dc3545;
    }

    .field-error {
        display: none;
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
    }

    .field-error.show {
        display: block;
    }

    .page-alert {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 110000;
        min-width: 300px;
        max-width: 450px;
        box-shadow: 0 5px 20px rgba(0,0,0,.15);
    }

    body.approval-modal-open {
        overflow: hidden;
    }

    @media (max-width: 767.98px) {
        .approval-header h1 {
            font-size: 23px;
        }

        .stat-card {
            margin-bottom: 12px;
        }

        .approval-modal {
            padding: 15px;
        }

        .approval-modal-dialog {
            max-width: 100%;
        }

        .note-info-row {
            display: block;
        }

        .note-info-label {
            width: auto;
            margin-bottom: 2px;
        }

        .approval-modal-footer {
            flex-direction: column-reverse;
        }

        .approval-modal-footer .btn {
            width: 100%;
        }

        .page-alert {
            left: 15px;
            right: 15px;
            min-width: 0;
        }
    }
</style>

<div class="container-fluid approval-page">

    {{-- PAGE HEADER --}}
    <div class="approval-header">
        <h1>
            <i class="fas fa-check-double"></i>
            Lesson Notes Approval
        </h1>
        <p>Review and manage all lesson notes.</p>
    </div>

    {{-- SESSION MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success page-alert" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger page-alert" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning page-alert" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('warning') }}
        </div>
    @endif

    {{-- STATISTICS --}}
    <div class="row mb-4">
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-primary">
                <div class="card-body">
                    <div class="stat-label">Total</div>
                    <div class="stat-value">{{ $stats['all'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-warning">
                <div class="card-body">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-secondary">
                <div class="card-body">
                    <div class="stat-label">Draft</div>
                    <div class="stat-value">{{ $stats['draft'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-success">
                <div class="card-body">
                    <div class="stat-label">Approved</div>
                    <div class="stat-value">{{ $stats['approved'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-danger">
                <div class="card-body">
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('approvals.index') }}">
                <div class="row">

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label for="status" class="filter-label">Status</label>
                        <select name="status" id="status" class="approval-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-4 mb-3">
                        <label for="staff_id" class="filter-label">Staff</label>
                        <select name="staff_id" id="staff_id" class="approval-select">
                            <option value="">All Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-4 mb-3">
                        <label for="student_class_id" class="filter-label">Class</label>
                        <select name="student_class_id" id="student_class_id" class="approval-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('student_class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label for="subject_id" class="filter-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="approval-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label for="search" class="filter-label">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="approval-input"
                            placeholder="Search..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filter
                        </button>

                        <a href="{{ route('approvals.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i>
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- LESSON NOTES --}}
    <div class="card notes-card">
        <div class="card-body">

            @if($lessonNotes->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover table-striped notes-table">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Topic</th>
                                <th>Staff</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th style="min-width: 180px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($lessonNotes as $index => $note)

                                @php
                                    $status = $note->status ?? 'pending';

                                    $statusClass = match($status) {
                                        'pending' => 'status-pending',
                                        'draft' => 'status-draft',
                                        'approved' => 'status-approved',
                                        'rejected' => 'status-rejected',
                                        default => 'status-draft',
                                    };

                                    $statusLabel = match($status) {
                                        'pending' => 'Pending',
                                        'draft' => 'Draft',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        default => ucfirst($status),
                                    };

                                    $canReview = in_array($status, ['pending', 'draft', null], true);
                                @endphp

                                <tr>
                                    {{-- Do not use firstItem() because the collection may not be paginated. --}}
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <strong>{{ $note->note_code }}</strong>
                                    </td>

                                    <td>
                                        <span title="{{ $note->sub_topic ?? '' }}">
                                            {{ Str::limit($note->topic, 30) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $note->staff->full_name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $note->studentClass->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $note->subject->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $note->lesson_date ? $note->lesson_date->format('d/m/Y') : 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="action-buttons">

                                            {{-- VIEW --}}
                                            <a
                                                href="{{ route('approvals.show', $note->id) }}"
                                                class="btn btn-sm btn-info"
                                                title="View Details"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- APPROVE --}}
                                            @if($canReview)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success js-open-approval-modal"
                                                    data-modal-id="approveModal{{ $note->id }}"
                                                    title="Approve"
                                                >
                                                    <i class="fas fa-check"></i>
                                                </button>

                                                {{-- REJECT --}}
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger js-open-approval-modal"
                                                    data-modal-id="rejectModal{{ $note->id }}"
                                                    title="Reject"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>

                                                {{-- REQUEST CHANGES --}}
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-warning js-open-approval-modal"
                                                    data-modal-id="changesModal{{ $note->id }}"
                                                    title="Request Changes"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @elseif($status === 'approved')
                                                <span class="status-badge status-approved">
                                                    <i class="fas fa-check-circle"></i>
                                                    Approved
                                                </span>
                                            @elseif($status === 'rejected')
                                                <span class="status-badge status-rejected">
                                                    <i class="fas fa-times-circle"></i>
                                                    Rejected
                                                </span>
                                            @endif

                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-4 d-flex justify-content-center">
                    @if(method_exists($lessonNotes, 'links'))
                        {{ $lessonNotes->appends(request()->query())->links() }}
                    @endif
                </div>

            @else

                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>

                    <h4>No Lesson Notes Found</h4>

                    <p class="text-muted">
                        There are no lesson notes matching your current filters.
                    </p>

                    <a href="{{ route('lesson-notes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create First Lesson Note
                    </a>
                </div>

            @endif

        </div>
    </div>
</div>


{{-- =========================================================
     MODALS
     They are intentionally placed OUTSIDE the table.
     Bootstrap's modal JS is NOT used.
     ========================================================= --}}

@foreach($lessonNotes as $note)

    @php
        $status = $note->status ?? 'pending';
        $canReview = in_array($status, ['pending', 'draft', null], true);
    @endphp

    @if($canReview)

        {{-- APPROVE MODAL --}}
        <div
            class="approval-modal"
            id="approveModal{{ $note->id }}"
            aria-hidden="true"
        >
            <div class="approval-modal-dialog">

                <div class="approval-modal-header approve">
                    <h5 class="approval-modal-title">
                        <i class="fas fa-check-circle"></i>
                        Approve Lesson Note
                    </h5>

                    <button
                        type="button"
                        class="approval-modal-close js-close-approval-modal"
                        aria-label="Close"
                    >&times;</button>
                </div>

                <form
                    action="{{ route('approvals.approve', $note->id) }}"
                    method="POST"
                    class="approval-form"
                >
                    @csrf

                    <div class="approval-modal-body">

                        <div class="modal-message info">
                            <i class="fas fa-info-circle"></i>
                            You are about to approve this lesson note.
                        </div>

                        <div class="note-info">
                            <div class="note-info-row">
                                <div class="note-info-label">Note Code</div>
                                <div class="note-info-value">{{ $note->note_code }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Topic</div>
                                <div class="note-info-value">{{ $note->topic }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Staff</div>
                                <div class="note-info-value">{{ $note->staff->full_name ?? 'N/A' }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Class</div>
                                <div class="note-info-value">{{ $note->studentClass->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="approval_notes{{ $note->id }}" class="filter-label">
                                Approval Notes
                                <span class="text-muted">(Optional)</span>
                            </label>

                            <textarea
                                name="approval_notes"
                                id="approval_notes{{ $note->id }}"
                                class="approval-textarea"
                                rows="4"
                                placeholder="Add any notes for approval..."
                            ></textarea>
                        </div>

                    </div>

                    <div class="approval-modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary js-close-approval-modal"
                        >
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i>
                            Confirm Approve
                        </button>
                    </div>

                </form>
            </div>
        </div>


        {{-- REJECT MODAL --}}
        <div
            class="approval-modal"
            id="rejectModal{{ $note->id }}"
            aria-hidden="true"
        >
            <div class="approval-modal-dialog">

                <div class="approval-modal-header reject">
                    <h5 class="approval-modal-title">
                        <i class="fas fa-times-circle"></i>
                        Reject Lesson Note
                    </h5>

                    <button
                        type="button"
                        class="approval-modal-close js-close-approval-modal"
                        aria-label="Close"
                    >&times;</button>
                </div>

                <form
                    action="{{ route('approvals.reject', $note->id) }}"
                    method="POST"
                    class="approval-form rejection-form"
                >
                    @csrf

                    <div class="approval-modal-body">

                        <div class="modal-message warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            You are about to reject this lesson note.
                        </div>

                        <div class="note-info">
                            <div class="note-info-row">
                                <div class="note-info-label">Note Code</div>
                                <div class="note-info-value">{{ $note->note_code }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Topic</div>
                                <div class="note-info-value">{{ $note->topic }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Staff</div>
                                <div class="note-info-value">{{ $note->staff->full_name ?? 'N/A' }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Class</div>
                                <div class="note-info-value">{{ $note->studentClass->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="rejection_reason{{ $note->id }}" class="filter-label">
                                Rejection Reason
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="rejection_reason"
                                id="rejection_reason{{ $note->id }}"
                                class="approval-textarea rejection-reason"
                                rows="4"
                                placeholder="Provide reason for rejection..."
                                required
                            ></textarea>

                            <div class="field-error">
                                Please provide a reason for rejection.
                            </div>
                        </div>

                    </div>

                    <div class="approval-modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary js-close-approval-modal"
                        >
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i>
                            Confirm Reject
                        </button>
                    </div>

                </form>
            </div>
        </div>


        {{-- REQUEST CHANGES MODAL --}}
        <div
            class="approval-modal"
            id="changesModal{{ $note->id }}"
            aria-hidden="true"
        >
            <div class="approval-modal-dialog">

                <div class="approval-modal-header changes">
                    <h5 class="approval-modal-title">
                        <i class="fas fa-edit"></i>
                        Request Changes
                    </h5>

                    <button
                        type="button"
                        class="approval-modal-close js-close-approval-modal"
                        aria-label="Close"
                    >&times;</button>
                </div>

                <form
                    action="{{ route('approvals.request-changes', $note->id) }}"
                    method="POST"
                    class="approval-form changes-form"
                >
                    @csrf

                    <div class="approval-modal-body">

                        <div class="modal-message info">
                            <i class="fas fa-info-circle"></i>
                            Request changes to this lesson note. Its status will be changed to Draft.
                        </div>

                        <div class="note-info">
                            <div class="note-info-row">
                                <div class="note-info-label">Note Code</div>
                                <div class="note-info-value">{{ $note->note_code }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Topic</div>
                                <div class="note-info-value">{{ $note->topic }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Staff</div>
                                <div class="note-info-value">{{ $note->staff->full_name ?? 'N/A' }}</div>
                            </div>

                            <div class="note-info-row">
                                <div class="note-info-label">Class</div>
                                <div class="note-info-value">{{ $note->studentClass->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="feedback{{ $note->id }}" class="filter-label">
                                Feedback
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="feedback"
                                id="feedback{{ $note->id }}"
                                class="approval-textarea changes-feedback"
                                rows="4"
                                placeholder="Provide detailed feedback for changes..."
                                required
                            ></textarea>

                            <div class="field-error">
                                Please provide feedback for changes.
                            </div>
                        </div>

                    </div>

                    <div class="approval-modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary js-close-approval-modal"
                        >
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Send Feedback
                        </button>
                    </div>

                </form>
            </div>
        </div>

    @endif

@endforeach


@push('scripts')
<script>
(function () {
    'use strict';

    /*
     * ==========================================================
     * CUSTOM MODAL ENGINE
     * ==========================================================
     * We deliberately do NOT use:
     *     data-toggle="modal"
     *     data-target="..."
     *     data-bs-toggle
     *     data-bs-target
     *     bootstrap.Modal
     *
     * This makes the page independent of the Bootstrap JS version
     * loaded by layouts.master.
     */

    function openModal(modal) {
        if (!modal) {
            return;
        }

        // Close any other open modal first.
        document.querySelectorAll('.approval-modal.is-open').forEach(function (item) {
            item.classList.remove('is-open');
            item.setAttribute('aria-hidden', 'true');
        });

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('approval-modal-open');

        var firstInput = modal.querySelector('textarea, input, select, button');
        if (firstInput) {
            setTimeout(function () {
                try {
                    firstInput.focus();
                } catch (e) {}
            }, 100);
        }
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.approval-modal.is-open')) {
            document.body.classList.remove('approval-modal-open');
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.approval-modal.is-open').forEach(function (modal) {
            closeModal(modal);
        });
    }

    /*
     * OPEN MODAL
     */
    document.addEventListener('click', function (event) {
        var openButton = event.target.closest('.js-open-approval-modal');

        if (openButton) {
            event.preventDefault();
            event.stopPropagation();

            var modalId = openButton.getAttribute('data-modal-id');
            var modal = document.getElementById(modalId);

            openModal(modal);
            return;
        }

        /*
         * CLOSE BUTTON
         */
        var closeButton = event.target.closest('.js-close-approval-modal');

        if (closeButton) {
            event.preventDefault();
            event.stopPropagation();

            var modal = closeButton.closest('.approval-modal');
            closeModal(modal);
            return;
        }

        /*
         * CLICK ON BACKDROP
         */
        if (event.target.classList.contains('approval-modal')) {
            closeModal(event.target);
        }
    });

    /*
     * ESC KEY CLOSES MODAL
     */
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllModals();
        }
    });

    /*
     * REJECTION VALIDATION
     */
    document.querySelectorAll('.rejection-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var textarea = form.querySelector('.rejection-reason');
            var error = form.querySelector('.field-error');

            if (!textarea || !textarea.value.trim()) {
                event.preventDefault();

                if (textarea) {
                    textarea.classList.add('invalid');
                    textarea.focus();
                }

                if (error) {
                    error.classList.add('show');
                }

                return false;
            }

            var button = form.querySelector('button[type="submit"]');

            if (button) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });

        var textarea = form.querySelector('.rejection-reason');

        if (textarea) {
            textarea.addEventListener('input', function () {
                if (this.value.trim()) {
                    this.classList.remove('invalid');

                    var error = form.querySelector('.field-error');

                    if (error) {
                        error.classList.remove('show');
                    }
                }
            });
        }
    });

    /*
     * REQUEST CHANGES VALIDATION
     */
    document.querySelectorAll('.changes-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var textarea = form.querySelector('.changes-feedback');
            var error = form.querySelector('.field-error');

            if (!textarea || !textarea.value.trim()) {
                event.preventDefault();

                if (textarea) {
                    textarea.classList.add('invalid');
                    textarea.focus();
                }

                if (error) {
                    error.classList.add('show');
                }

                return false;
            }

            var button = form.querySelector('button[type="submit"]');

            if (button) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });

        var textarea = form.querySelector('.changes-feedback');

        if (textarea) {
            textarea.addEventListener('input', function () {
                if (this.value.trim()) {
                    this.classList.remove('invalid');

                    var error = form.querySelector('.field-error');

                    if (error) {
                        error.classList.remove('show');
                    }
                }
            });
        }
    });

    /*
     * APPROVE FORM LOADING STATE
     */
    document.querySelectorAll('.approval-form:not(.rejection-form):not(.changes-form)').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');

            if (button) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });
    });

    /*
     * AUTO HIDE SERVER ALERTS
     */
    setTimeout(function () {
        document.querySelectorAll('.page-alert').forEach(function (alert) {
            alert.style.transition = 'opacity .4s ease';
            alert.style.opacity = '0';

            setTimeout(function () {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 450);
        });
    }, 5000);

})();
</script>
@endpush

@endsection
