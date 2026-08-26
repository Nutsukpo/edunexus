@extends('layouts.master')

@section('title', 'Bill Sheet Approval')

@section('content')

@php
    $loadedBillSheets = $billSheets ?? collect();

    // Status constants for better maintainability
    $actionableStatuses = ['draft', 'pending'];
    $lockedStatuses = ['approved', 'published', 'rejected'];

    $totalBills = $summary['total'] ?? $loadedBillSheets->count();
    $draftBills = $summary['draft'] ?? $loadedBillSheets->where('status', 'draft')->count();
    $pendingBills = $summary['pending'] ?? $loadedBillSheets->where('status', 'pending')->count();
    $approvableBills = $summary['approvable'] ?? ($draftBills + $pendingBills);
    $approvedBills = $summary['approved'] ?? $loadedBillSheets->where('status', 'approved')->count();
    $rejectedBills = $summary['rejected'] ?? $loadedBillSheets->where('status', 'rejected')->count();
    $publishedBills = $summary['published'] ?? $loadedBillSheets->where('status', 'published')->count();

    $actionableAmount = $summary['pending_amount'] ?? $loadedBillSheets
        ->whereIn('status', $actionableStatuses)
        ->sum('net_amount');

    // Determine if filters are applied
    $filtersApplied = request()->filled(['student_class_id', 'academic_year_id', 'term_id']);
@endphp

<div class="container-fluid py-4 approval-page">

    {{-- ================================================================
         PAGE HEADER
    ================================================================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="mb-3 mb-md-0">
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-file-signature text-primary mr-2"></i>
                Bill Sheet Approval
            </h4>
            <p class="text-muted mb-0">
                Review, approve, or reject student Bill Sheets.
                <span class="text-dark">Draft & Pending</span> are actionable.
                <span class="text-dark">Approved & Published</span> are locked.
            </p>
        </div>
        <div>
            
        </div>
    </div>

    {{-- ================================================================
         FLASH MESSAGES
    ================================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i> Please correct the following:</strong>
            <ul class="mb-0 mt-2 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

  {{-- ================================================================
     FILTER SECTION - LANDSCAPE LAYOUT
================================================================= --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-sliders-h text-primary mr-2"></i>
            Filter Bill Sheets
        </h5>
        <span class="badge badge-primary badge-pill">
            <i class="fas fa-filter mr-1"></i> Advanced Filters
        </span>
    </div>
    
    <div class="card-body">
        <form method="GET" action="{{ route('bill-sheet-approvals.index') }}" id="filterForm">
            <div class="row">
                <!-- Class -->
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3 mb-xl-0">
                    <div class="form-group mb-0">
                        <label for="student_class_id" class="font-weight-bold text-dark small text-uppercase mb-1">
                            <i class="fas fa-users text-primary mr-1"></i> Class <span class="text-danger">*</span>
                        </label>
                        <select name="student_class_id" id="student_class_id" class="form-control form-control-sm" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('student_class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Academic Year -->
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3 mb-xl-0">
                    <div class="form-group mb-0">
                        <label for="academic_year_id" class="font-weight-bold text-dark small text-uppercase mb-1">
                            <i class="fas fa-calendar-alt text-primary mr-1"></i> Academic Year <span class="text-danger">*</span>
                        </label>
                        <select name="academic_year_id" id="academic_year_id" class="form-control form-control-sm" required>
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Term -->
                <div class="col-xl-2 col-lg-4 col-md-6 mb-3 mb-xl-0">
                    <div class="form-group mb-0">
                        <label for="term_id" class="font-weight-bold text-dark small text-uppercase mb-1">
                            <i class="fas fa-clock text-primary mr-1"></i> Term <span class="text-danger">*</span>
                        </label>
                        <select name="term_id" id="term_id" class="form-control form-control-sm" required>
                            <option value="">-- Select Term --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Status Filter (Optional) -->
                <div class="col-xl-2 col-lg-4 col-md-6 mb-3 mb-xl-0">
                    <div class="form-group mb-0">
                        <label for="status_filter" class="font-weight-bold text-dark small text-uppercase mb-1">
                            <i class="fas fa-tag text-primary mr-1"></i> Status
                        </label>
                        <select name="status_filter" id="status_filter" class="form-control form-control-sm">
                            <option value="">-- All Statuses --</option>
                            <option value="draft" {{ request('status_filter') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending" {{ request('status_filter') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status_filter') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status_filter') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="published" {{ request('status_filter') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small text-uppercase mb-1 text-muted">
                            <i class="fas fa-bolt text-primary mr-1"></i> Actions
                        </label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 mr-1" id="loadBillsBtn">
                                <i class="fas fa-search mr-1"></i> Load
                            </button>
                            <a href="{{ route('bill-sheet-approvals.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                                <i class="fas fa-undo-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Bar (Hidden until filters are applied) -->
            @if(request()->filled(['student_class_id', 'academic_year_id', 'term_id']))
                <div class="row mt-3 pt-3 border-top">
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="text-muted small mr-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Showing results for:
                                </span>
                                <span class="badge badge-light mr-2">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $classes->find(request('student_class_id'))?->name ?? 'N/A' }}
                                </span>
                                <span class="badge badge-light mr-2">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ $academicYears->find(request('academic_year_id'))?->name ?? 'N/A' }}
                                </span>
                                <span class="badge badge-light mr-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $terms->find(request('term_id'))?->name ?? 'N/A' }}
                                </span>
                                @if(request('status_filter'))
                                    <span class="badge badge-light">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ ucfirst(request('status_filter')) }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="text-muted small">
                                    <i class="fas fa-file-invoice mr-1"></i>
                                    <strong>{{ $loadedBillSheets->count() }}</strong> record(s) found
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

    {{-- ================================================================
         STATISTICS SUMMARY
    ================================================================= --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Total</div>
                            <div class="h4 font-weight-bold text-dark mb-0">{{ $totalBills }}</div>
                        </div>
                        <div class="summary-icon summary-icon-primary">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Draft</div>
                            <div class="h4 font-weight-bold text-secondary mb-0">{{ $draftBills }}</div>
                        </div>
                        <div class="summary-icon summary-icon-secondary">
                            <i class="fas fa-file"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Pending</div>
                            <div class="h4 font-weight-bold text-warning mb-0">{{ $pendingBills }}</div>
                        </div>
                        <div class="summary-icon summary-icon-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Actionable</div>
                            <div class="h4 font-weight-bold text-info mb-0">{{ $approvableBills }}</div>
                            <small class="text-muted">Draft + Pending</small>
                        </div>
                        <div class="summary-icon summary-icon-info">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Approved</div>
                            <div class="h4 font-weight-bold text-success mb-0">{{ $approvedBills }}</div>
                        </div>
                        <div class="summary-icon summary-icon-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Rejected</div>
                            <div class="h4 font-weight-bold text-danger mb-0">{{ $rejectedBills }}</div>
                        </div>
                        <div class="summary-icon summary-icon-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         BULK ACTION BAR
    ================================================================= --}}
    @if($filtersApplied)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-3 mb-md-0">
                        <h6 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-layer-group text-primary mr-1"></i>
                            Bulk Actions
                        </h6>
                        <p class="small text-muted mb-0">
                            Apply to all <strong>draft</strong> and <strong>pending</strong> Bill Sheets
                            in the selected class, academic year, and term.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap">
                        @if($approvableBills > 0)
                            <button type="button" class="btn btn-success mr-2 mb-2 mb-md-0" id="openApproveAllModal">
                                <i class="fas fa-check-double mr-1"></i>
                                Approve All
                                <span class="badge badge-light ml-1">{{ $approvableBills }}</span>
                            </button>

                            <button type="button" class="btn btn-danger mb-2 mb-md-0" id="openRejectAllModal">
                                <i class="fas fa-times-circle mr-1"></i>
                                Reject All
                                <span class="badge badge-light ml-1">{{ $approvableBills }}</span>
                            </button>
                        @else
                            <span class="badge badge-light border text-secondary px-3 py-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                No draft or pending Bill Sheets
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================
         BILL SHEETS TABLE
    ================================================================= --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h5 class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-list text-primary mr-2"></i>
                        Bill Sheets
                    </h5>
                    @if($filtersApplied)
                        <small class="text-muted">
                            Showing {{ $loadedBillSheets->count() }} record(s)
                            @if(request('student_class_id'))
                                for {{ $classes->find(request('student_class_id'))?->name ?? 'selected class' }}
                            @endif
                        </small>
                    @else
                        <small class="text-muted">
                            Select a class, academic year, and term to load Bill Sheets.
                        </small>
                    @endif
                </div>
                <div>
                    @if($filtersApplied && $loadedBillSheets->count() > 0)
                        <span class="badge badge-secondary">
                            <i class="fas fa-print mr-1"></i> {{ $loadedBillSheets->count() }} records
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($loadedBillSheets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 approval-table">
                        <thead>
                            <tr>
                                <th class="pl-3" width="50">#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th class="text-right">Amount (GHS)</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pr-3" width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loadedBillSheets as $index => $billSheet)
                                @php
                                    $assignment = $billSheet->studentClassAssignment;
                                    $student = $assignment?->student;
                                    $studentClass = $assignment?->studentClass;

                                    $studentName = $student
                                        ? trim(collect([
                                            $student->first_name ?? null,
                                            $student->middle_name ?? null,
                                            $student->last_name ?? null
                                        ])->filter()->implode(' '))
                                        : 'N/A';

                                    $isActionable = in_array(
                                        strtolower((string) $billSheet->status),
                                        $actionableStatuses,
                                        true
                                    );

                                    $statusColor = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'published' => 'info'
                                    ][strtolower((string) $billSheet->status)] ?? 'secondary';

                                    $statusIcon = [
                                        'draft' => 'fa-file',
                                        'pending' => 'fa-clock',
                                        'approved' => 'fa-check-circle',
                                        'rejected' => 'fa-times-circle',
                                        'published' => 'fa-bullhorn'
                                    ][strtolower((string) $billSheet->status)] ?? 'fa-circle';
                                @endphp

                                <tr>
                                    <td class="pl-3 font-weight-bold text-muted">
                                        {{ $index + 1 }}
                                    </td>

                                    <td>
                                        <div class="font-weight-bold text-dark">
                                            {{ $studentName }}
                                        </div>
                                        <!-- @if($student?->student_id)
                                            <small class="text-muted">
                                                ID: {{ $student->student_id }}
                                            </small>
                                        @endif -->
                                    </td>

                                    <td>{{ $studentClass?->name ?? 'N/A' }}</td>
                                    <td>{{ $billSheet->academicYear?->name ?? 'N/A' }}</td>
                                    <td>{{ $billSheet->term?->name ?? 'N/A' }}</td>

                                    <td class="text-right font-weight-bold">
                                        {{ number_format((float) $billSheet->net_amount, 2) }}
                                    </td>

                                    <td class="text-center">
                                        <span class="status-badge status-{{ strtolower((string) $billSheet->status) }}">
                                            <i class="fas {{ $statusIcon }} mr-1"></i>
                                            {{ ucfirst(strtolower((string) $billSheet->status)) }}
                                        </span>
                                    </td>

                                    <td class="text-center pr-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('bill-sheets.show', $billSheet) }}"
                                               class="btn btn-outline-primary"
                                               title="View Bill Sheet">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($isActionable)
                                                <button type="button"
                                                        class="btn btn-outline-success js-approve-bill"
                                                        data-bill-id="{{ $billSheet->id }}"
                                                        data-student-name="{{ $studentName }}"
                                                        data-status="{{ strtolower($billSheet->status) }}"
                                                        title="Approve Bill Sheet">
                                                    <i class="fas fa-check"></i>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-outline-danger js-reject-bill"
                                                        data-bill-id="{{ $billSheet->id }}"
                                                        data-student-name="{{ $studentName }}"
                                                        data-status="{{ strtolower($billSheet->status) }}"
                                                        title="Reject Bill Sheet">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small align-self-center" title="This Bill Sheet is locked">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(is_object($loadedBillSheets) && method_exists($loadedBillSheets, 'hasPages') && $loadedBillSheets->hasPages())
                    <div class="p-3 border-top">
                        {{ $loadedBillSheets->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark">No Bill Sheets Found</h5>
                    <p class="text-muted mb-0">
                        @if(!$filtersApplied)
                            Select a class, academic year, and term to load Bill Sheets for approval.
                        @else
                            No Bill Sheets match the selected criteria.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ================================================================
     MODALS
     Custom implementation - Bootstrap 4 compatible
================================================================= --}}

<!-- Approve All Modal -->
<div class="modal fade" id="approveAllModal" tabindex="-1" role="dialog" aria-labelledby="approveAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveAllModalLabel">
                    <i class="fas fa-check-double mr-2"></i> Approve All Bill Sheets
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    You are about to approve <strong>{{ $approvableBills }}</strong>
                    draft and pending Bill Sheet(s) for the selected class, academic year, and term.
                </div>

                <div class="bg-light p-3 rounded">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Actionable:</span>
                        <strong>{{ $approvableBills }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Affected Statuses:</span>
                        <span>
                            <span class="badge badge-secondary">Draft</span>
                            <span class="badge badge-warning">Pending</span>
                        </span>
                    </div>
                </div>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Already approved or published Bill Sheets will not be changed.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <form method="POST" action="{{ route('bill-sheet-approvals.approve-all') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="student_class_id" value="{{ request('student_class_id') }}">
                    <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                    <input type="hidden" name="term_id" value="{{ request('term_id') }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-double mr-1"></i> Approve All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject All Modal -->
<div class="modal fade" id="rejectAllModal" tabindex="-1" role="dialog" aria-labelledby="rejectAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectAllModalLabel">
                    <i class="fas fa-times-circle mr-2"></i> Reject All Bill Sheets
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('bill-sheet-approvals.reject-all') }}">
                @csrf
                <input type="hidden" name="student_class_id" value="{{ request('student_class_id') }}">
                <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                <input type="hidden" name="term_id" value="{{ request('term_id') }}">

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        You are about to reject <strong>{{ $approvableBills }}</strong>
                        draft and pending Bill Sheet(s). This action cannot be undone.
                    </div>

                    <div class="form-group">
                        <label for="bulk_rejection_reason" class="font-weight-bold">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason"
                                  id="bulk_rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="Enter the reason for rejecting these Bill Sheets..."></textarea>
                        <small class="text-muted">Maximum 1000 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle mr-1"></i> Reject All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single Approve Modal -->
<div class="modal fade" id="singleApproveModal" tabindex="-1" role="dialog" aria-labelledby="singleApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="singleApproveModalLabel">
                    <i class="fas fa-check-circle mr-2"></i> Approve Bill Sheet
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Are you sure you want to approve the Bill Sheet for:
                </p>
                <div class="bg-light p-3 rounded mb-3">
                    <strong id="approveStudentName" class="text-primary">this student</strong>
                    <br>
                    <small class="text-muted" id="approveBillStatusText">Current status: Pending</small>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Draft and pending Bill Sheets can be approved.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <form method="POST" id="singleApproveForm" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Single Reject Modal -->
<div class="modal fade" id="singleRejectModal" tabindex="-1" role="dialog" aria-labelledby="singleRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="singleRejectModalLabel">
                    <i class="fas fa-times-circle mr-2"></i> Reject Bill Sheet
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" id="singleRejectForm">
                @csrf
                <div class="modal-body">
                    <p class="mb-2">
                        Reject the Bill Sheet for:
                    </p>
                    <div class="bg-light p-3 rounded mb-3">
                        <strong id="rejectStudentName" class="text-danger">this student</strong>
                        <br>
                        <small class="text-muted" id="rejectBillStatusText">Current status: Pending</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        This action cannot be undone.
                    </div>

                    <div class="form-group">
                        <label for="single_rejection_reason" class="font-weight-bold">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason"
                                  id="single_rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="Enter rejection reason..."></textarea>
                        <small class="text-muted">Maximum 1000 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- ================================================================
     STYLES
================================================================= --}}
@push('styles')
<style>
    .approval-page .card {
        border-radius: 10px;
    }

    .approval-page .card-header {
        padding: 1rem 1.25rem;
    }

    .approval-page .form-control {
        min-height: 42px;
        border-radius: 6px;
    }

    .approval-page .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 .2rem rgba(0,123,255,.15);
    }

    .approval-table {
        min-width: 980px;
    }

    .approval-table thead th {
        background: #f8f9fa;
        color: #343a40;
        border-top: 0;
        border-bottom: 2px solid #dee2e6;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        padding: 13px 10px;
    }

    .approval-table tbody td {
        padding: 13px 10px;
        vertical-align: middle;
    }

    .approval-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .summary-icon {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .summary-icon-primary {
        background: #e8f1ff;
        color: #0d6efd;
    }

    .summary-icon-secondary {
        background: #eeeeee;
        color: #6c757d;
    }

    .summary-icon-warning {
        background: #fff4d6;
        color: #d39e00;
    }

    .summary-icon-info {
        background: #d6ecff;
        color: #0dcaf0;
    }

    .summary-icon-success {
        background: #e7f7ee;
        color: #198754;
    }

    .summary-icon-danger {
        background: #fde2e2;
        color: #dc3545;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        line-height: 1.4;
    }

    .status-draft {
        background: #f1f3f5;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffe69c;
    }

    .status-approved {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #a3cfbb;
    }

    .status-rejected {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f1aeb5;
    }

    .status-published {
        background: #cfe2ff;
        color: #084298;
        border: 1px solid #9ec5fe;
    }

    .status-other {
        background: #e2e3e5;
        color: #41464b;
        border: 1px solid #d3d6d8;
    }

    .empty-state {
        padding: 70px 25px;
        text-align: center;
    }

    .empty-state-icon {
        width: 75px;
        height: 75px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f3f5;
        color: #adb5bd;
        font-size: 30px;
    }

    .btn-group-sm > .btn {
        padding: .25rem .5rem;
        font-size: .875rem;
        line-height: 1.5;
        border-radius: .2rem;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .approval-table {
            min-width: 100%;
        }

        .summary-icon {
            width: 38px;
            height: 38px;
            font-size: 14px;
        }
    }

    /* Modal improvements */
    .modal-header .close {
        padding: 1rem;
        margin: -1rem -1rem -1rem auto;
        opacity: 0.7;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-content {
        border-radius: 10px;
        overflow: hidden;
    }
</style>
@endpush

{{-- ================================================================
     JAVASCRIPT
================================================================= --}}
@push('scripts')
<script>
    (function() {
        'use strict';

        // ================================================================
        // BILL SHEET APPROVAL PAGE
        // ================================================================

        function initBillSheetApprovalPage() {
            const page = this;

            /**
             * Single Approve - Populate and submit
             */
            document.querySelectorAll('.js-approve-bill').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const billId = this.getAttribute('data-bill-id');
                    const studentName = this.getAttribute('data-student-name') || 'this student';
                    const status = this.getAttribute('data-status') || 'pending';

                    const studentNameEl = document.getElementById('approveStudentName');
                    const statusTextEl = document.getElementById('approveBillStatusText');
                    const form = document.getElementById('singleApproveForm');

                    if (studentNameEl) {
                        studentNameEl.textContent = studentName;
                    }

                    if (statusTextEl) {
                        statusTextEl.textContent = 'Current status: ' + status.charAt(0).toUpperCase() + status.slice(1);
                    }

                    if (form && billId) {
                        form.action = "{{ url('/bill-sheet-approvals') }}/" + encodeURIComponent(billId) + "/approve";
                    }

                    $('#singleApproveModal').modal('show');
                });
            });

            /**
             * Single Reject - Populate and submit
             */
            document.querySelectorAll('.js-reject-bill').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const billId = this.getAttribute('data-bill-id');
                    const studentName = this.getAttribute('data-student-name') || 'this student';
                    const status = this.getAttribute('data-status') || 'pending';

                    const studentNameEl = document.getElementById('rejectStudentName');
                    const statusTextEl = document.getElementById('rejectBillStatusText');
                    const form = document.getElementById('singleRejectForm');
                    const reasonField = document.getElementById('single_rejection_reason');

                    if (studentNameEl) {
                        studentNameEl.textContent = studentName;
                    }

                    if (statusTextEl) {
                        statusTextEl.textContent = 'Current status: ' + status.charAt(0).toUpperCase() + status.slice(1);
                    }

                    if (form && billId) {
                        form.action = "{{ url('/bill-sheet-approvals') }}/" + encodeURIComponent(billId) + "/reject";
                    }

                    if (reasonField) {
                        reasonField.value = '';
                        reasonField.classList.remove('is-invalid');
                    }

                    $('#singleRejectModal').modal('show');
                });
            });

            /**
             * Validate reject reason on single reject modal
             */
            $('#singleRejectForm').on('submit', function(e) {
                const reasonField = document.getElementById('single_rejection_reason');
                if (!reasonField || !reasonField.value.trim()) {
                    e.preventDefault();
                    reasonField.classList.add('is-invalid');
                    return false;
                }
                reasonField.classList.remove('is-invalid');
            });

            /**
             * Validate reject reason on bulk reject modal
             */
            $('#rejectAllModal form').on('submit', function(e) {
                const reasonField = document.getElementById('bulk_rejection_reason');
                if (!reasonField || !reasonField.value.trim()) {
                    e.preventDefault();
                    reasonField.classList.add('is-invalid');
                    return false;
                }
                reasonField.classList.remove('is-invalid');
            });

            /**
             * Clear validation on input
             */
            document.querySelectorAll('#single_rejection_reason, #bulk_rejection_reason').forEach(function(field) {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });

            /**
             * Toastr notifications for session messages (optional enhancement)
             */
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            @if(session('warning'))
                toastr.warning('{{ session('warning') }}');
            @endif

            /**
             * Auto-hide alerts after 5 seconds
             */
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);

            console.log('Bill Sheet Approval page initialized successfully.');
        }

        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBillSheetApprovalPage);
        } else {
            initBillSheetApprovalPage();
        }
    })();
</script>
@endpush