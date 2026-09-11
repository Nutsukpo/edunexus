@extends('layouts.master')

@section('title', 'Bill Sheet Approval')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | PAGE DATA
    |--------------------------------------------------------------------------
    */

    $loadedBillSheets = $billSheets ?? collect();

    $actionableStatuses = [
        'draft',
        'pending',
    ];

    $lockedStatuses = [
        'approved',
        'published',
        'rejected',
    ];

    $totalBills = $summary['total'] ?? 0;

    $draftBills = $summary['draft'] ?? 0;

    $pendingBills = $summary['pending'] ?? 0;

    $approvableBills = $summary['approvable'] ?? (
        $draftBills + $pendingBills
    );

    $approvedBills = $summary['approved'] ?? 0;

    $rejectedBills = $summary['rejected'] ?? 0;

    $publishedBills = $summary['published'] ?? 0;

    $actionableAmount = $summary['pending_amount'] ?? 0;

    $filtersApplied = request()->filled([
        'student_class_id',
        'academic_year_id',
        'term_id',
    ]);

@endphp


<div class="container-fluid py-4 approval-page">


    {{-- ================================================================
         PAGE HEADER
    ================================================================= --}}

    <div class="d-flex flex-wrap justify-content-between
                align-items-center mb-4">

        <div class="mb-3 mb-md-0">

            <div class="d-flex align-items-center mb-2">

                <div class="approval-header-icon mr-3">
                    <i class="fas fa-file-signature"></i>
                </div>

                <div>

                    <h4 class="font-weight-bold text-dark mb-1">
                        Bill Sheet Approval
                    </h4>

                    <p class="text-muted mb-0">
                        Review, approve, or reject student Bill Sheets.
                    </p>

                </div>

            </div>

            <div class="small text-muted ml-md-5">

                <span class="mr-3">
                    <i class="fas fa-circle text-secondary mr-1"></i>
                    Draft
                </span>

                <span class="mr-3">
                    <i class="fas fa-circle text-warning mr-1"></i>
                    Pending
                </span>

                <span>
                    <i class="fas fa-lock text-success mr-1"></i>
                    Approved/Published Locked
                </span>

            </div>

        </div>

    </div>


    {{-- ================================================================
         FLASH MESSAGES
    ================================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="fas fa-check-circle mr-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <i class="fas fa-exclamation-circle mr-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif


    @if(session('warning'))

        <div class="alert alert-warning alert-dismissible fade show shadow-sm">

            <i class="fas fa-exclamation-triangle mr-2"></i>

            {{ session('warning') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

            <strong>
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2 pl-4">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif



    {{-- ================================================================
         FILTERS
    ================================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex justify-content-between
                        align-items-center flex-wrap">

                <div>

                    <h5 class="font-weight-bold text-dark mb-1">

                        <i class="fas fa-sliders-h text-primary mr-2"></i>

                        Filter Bill Sheets

                    </h5>

                    <small class="text-muted">
                        Select a class, academic year and term.
                    </small>

                </div>

                @if($filtersApplied)

                    <span class="badge badge-primary px-3 py-2">

                        <i class="fas fa-filter mr-1"></i>

                        Filters Applied

                    </span>

                @endif

            </div>

        </div>


        <div class="card-body">

            <form method="GET"
                  action="{{ route('bill-sheet-approvals.index') }}"
                  id="filterForm">

                <div class="row">

                    {{-- Class --}}
                    <div class="col-xl-3 col-lg-6 mb-3">

                        <label for="student_class_id"
                               class="form-label-custom">

                            <i class="fas fa-users text-primary mr-1"></i>

                            Class

                            <span class="text-danger">*</span>

                        </label>

                        <select name="student_class_id"
                                id="student_class_id"
                                class="form-control"
                                required>

                            <option value="">
                                Select Class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}"
                                    @selected(
                                        request('student_class_id')
                                        == $class->id
                                    )>

                                    {{ $class->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Academic Year --}}
                    <div class="col-xl-3 col-lg-6 mb-3">

                        <label for="academic_year_id"
                               class="form-label-custom">

                            <i class="fas fa-calendar-alt text-primary mr-1"></i>

                            Academic Year

                            <span class="text-danger">*</span>

                        </label>

                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-control"
                                required>

                            <option value="">
                                Select Academic Year
                            </option>

                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}"
                                    @selected(
                                        request('academic_year_id')
                                        == $year->id
                                    )>

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Term --}}
                    <div class="col-xl-2 col-lg-6 mb-3">

                        <label for="term_id"
                               class="form-label-custom">

                            <i class="fas fa-clock text-primary mr-1"></i>

                            Term

                            <span class="text-danger">*</span>

                        </label>

                        <select name="term_id"
                                id="term_id"
                                class="form-control"
                                required
                                disabled>

                            <option value="">
                                Select Academic Year First
                            </option>

                        </select>

                        {{-- Source terms. Only matching academic-year terms are
                             copied into the visible select by JavaScript. --}}
                        <template id="termOptionsTemplate">
                            @foreach($terms as $term)
                                <option
                                    value="{{ $term->id }}"
                                    data-academic-year-id="{{ $term->academic_year_id }}"
                                    data-start-date="{{ $term->start_date }}"
                                    data-end-date="{{ $term->end_date }}"
                                >
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </template>

                        <small class="form-text text-muted"
                               id="termHelp">
                            Select an Academic Year to load its terms.
                        </small>

                    </div>


                    {{-- Status --}}
                    <div class="col-xl-2 col-lg-6 mb-3">

                        <label for="status_filter"
                               class="form-label-custom">

                            <i class="fas fa-tag text-primary mr-1"></i>

                            Status

                        </label>

                        <select name="status_filter"
                                id="status_filter"
                                class="form-control">

                            <option value="">
                                All Statuses
                            </option>

                            <option value="draft"
                                @selected(request('status_filter') === 'draft')>
                                Draft
                            </option>

                            <option value="pending"
                                @selected(request('status_filter') === 'pending')>
                                Pending
                            </option>

                            <option value="approved"
                                @selected(request('status_filter') === 'approved')>
                                Approved
                            </option>

                            <option value="rejected"
                                @selected(request('status_filter') === 'rejected')>
                                Rejected
                            </option>

                            <option value="published"
                                @selected(request('status_filter') === 'published')>
                                Published
                            </option>

                        </select>

                    </div>


                    {{-- Filter Actions --}}
                    <div class="col-xl-2 col-lg-6 mb-3">

                        <label class="form-label-custom">
                            Actions
                        </label>

                        <div class="d-flex">

                            <button type="submit"
                                    class="btn btn-primary flex-grow-1 mr-2"
                                    id="loadBillsBtn">

                                <i class="fas fa-search mr-1"></i>

                                Load

                            </button>

                            <a href="{{ route(
                                'bill-sheet-approvals.index'
                            ) }}"
                               class="btn btn-outline-secondary"
                               title="Reset Filters">

                                <i class="fas fa-undo-alt"></i>

                            </a>

                        </div>

                    </div>

                </div>


                {{-- Selected Filter Summary --}}

                @if($filtersApplied)

                    <div class="filter-summary mt-2 pt-3">

                        <div class="d-flex flex-wrap
                                    align-items-center">

                            <span class="text-muted small mr-3">

                                <i class="fas fa-info-circle mr-1"></i>

                                Showing results for:

                            </span>


                            <span class="filter-chip">

                                <i class="fas fa-users mr-1"></i>

                                {{ $classes->find(
                                    request('student_class_id')
                                )?->name ?? 'N/A' }}

                            </span>


                            <span class="filter-chip">

                                <i class="fas fa-calendar-alt mr-1"></i>

                                {{ $academicYears->find(
                                    request('academic_year_id')
                                )?->name ?? 'N/A' }}

                            </span>


                            <span class="filter-chip">

                                <i class="fas fa-clock mr-1"></i>

                                {{ $terms->find(
                                    request('term_id')
                                )?->name ?? 'N/A' }}

                            </span>


                            @if(request('status_filter'))

                                <span class="filter-chip">

                                    <i class="fas fa-tag mr-1"></i>

                                    {{ ucfirst(
                                        request('status_filter')
                                    ) }}

                                </span>

                            @endif


                            <span class="ml-auto text-muted small">

                                <i class="fas fa-file-invoice mr-1"></i>

                                <strong>
                                    {{ $loadedBillSheets->total()
                                        ?? $loadedBillSheets->count() }}
                                </strong>

                                record(s)

                            </span>

                        </div>

                    </div>

                @endif

            </form>

        </div>

    </div>



    {{-- ================================================================
         SUMMARY CARDS
    ================================================================= --}}

    <div class="row mb-4">

        {{-- Total --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">

            <div class="card border-0 shadow-sm summary-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Total
                            </small>

                            <h4 class="font-weight-bold mb-0">
                                {{ $totalBills }}
                            </h4>

                        </div>

                        <div class="summary-icon primary">
                            <i class="fas fa-file-invoice"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Draft --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">

            <div class="card border-0 shadow-sm summary-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Draft
                            </small>

                            <h4 class="font-weight-bold text-secondary mb-0">
                                {{ $draftBills }}
                            </h4>

                        </div>

                        <div class="summary-icon secondary">
                            <i class="fas fa-file"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Pending --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">

            <div class="card border-0 shadow-sm summary-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Pending
                            </small>

                            <h4 class="font-weight-bold text-warning mb-0">
                                {{ $pendingBills }}
                            </h4>

                        </div>

                        <div class="summary-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Actionable --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">

            <div class="card border-0 shadow-sm summary-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Actionable
                            </small>

                            <h4 class="font-weight-bold text-info mb-0">
                                {{ $approvableBills }}
                            </h4>

                            <small class="text-muted">
                                Draft + Pending
                            </small>

                        </div>

                        <div class="summary-icon info">
                            <i class="fas fa-tasks"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Approved --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">

            <div class="card border-0 shadow-sm summary-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Approved
                            </small>

                            <h4 class="font-weight-bold text-success mb-0">
                                {{ $approvedBills }}
                            </h4>

                        </div>

                        <div class="summary-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Rejected --}}
        <div class="col-xl-2 col-md-4 col-6 mb-3">

            <div class="card border-0 shadow-sm summary-card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Rejected
                            </small>

                            <h4 class="font-weight-bold text-danger mb-0">
                                {{ $rejectedBills }}
                            </h4>

                        </div>

                        <div class="summary-icon danger">
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

    @if($filtersApplied && $approvableBills > 0)

        <div class="card border-0 shadow-sm mb-4 bulk-action-card">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-lg-7 mb-3 mb-lg-0">

                        <div class="d-flex align-items-center">

                            <div class="bulk-icon mr-3">

                                <i class="fas fa-layer-group"></i>

                            </div>

                            <div>

                                <h6 class="font-weight-bold mb-1">

                                    Bulk Approval Actions

                                </h6>

                                <p class="small text-muted mb-0">

                                    Select individual Bill Sheets below
                                    or process all
                                    <strong>{{ $approvableBills }}</strong>
                                    actionable records.

                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="col-lg-5">

                        <div class="d-flex flex-wrap
                                    justify-content-lg-end">

                            {{-- Selected counter --}}
                            <div class="selection-counter mr-2 mb-2">

                                <span id="selectedCount">
                                    0
                                </span>

                                Selected

                            </div>


                            {{-- Approve Selected --}}
                            <button type="button"
                                    class="btn btn-success mr-2 mb-2"
                                    id="approveSelectedBtn"
                                    disabled>

                                <i class="fas fa-check mr-1"></i>

                                Approve Selected

                            </button>


                            {{-- Reject Selected --}}
                            <button type="button"
                                    class="btn btn-danger mr-2 mb-2"
                                    id="rejectSelectedBtn"
                                    disabled>

                                <i class="fas fa-times mr-1"></i>

                                Reject Selected

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    @endif



    {{-- ================================================================
         BILL SHEETS TABLE
    ================================================================= --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex justify-content-between
                        align-items-center flex-wrap">

                <div>

                    <h5 class="font-weight-bold text-dark mb-1">

                        <i class="fas fa-list text-primary mr-2"></i>

                        Bill Sheets

                    </h5>

                    <small class="text-muted">

                        @if($filtersApplied)

                            Showing
                            <strong>
                                {{ $loadedBillSheets->count() }}
                            </strong>
                            record(s)

                        @else

                            Select a class, academic year and term
                            to load Bill Sheets.

                        @endif

                    </small>

                </div>


                @if($filtersApplied)

                    <div class="mt-2 mt-md-0">

                        <span class="badge badge-light border px-3 py-2">

                            <i class="fas fa-check-square text-primary mr-1"></i>

                            <span id="headerSelectedCount">
                                0
                            </span>

                            selected

                        </span>

                    </div>

                @endif

            </div>

        </div>


        <div class="card-body p-0">

            @if($loadedBillSheets->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover mb-0 approval-table">

                        <thead>

                            <tr>

                                {{-- Select All --}}
                                <th width="55"
                                    class="text-center">

                                    <div class="custom-control custom-checkbox">

                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="selectAll">

                                        <label class="custom-control-label"
                                               for="selectAll">
                                        </label>

                                    </div>

                                </th>

                                <th width="55">
                                    #
                                </th>

                                <th>
                                    Student
                                </th>

                                <th>
                                    Class
                                </th>

                                <th>
                                    Academic Year
                                </th>

                                <th>
                                    Term
                                </th>

                                <th class="text-right">
                                    Amount (GHS)
                                </th>

                                <th class="text-center">
                                    Status
                                </th>

                                <th class="text-center"
                                    width="150">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        @foreach($loadedBillSheets as $index => $billSheet)

                            @php

                                $assignment =
                                    $billSheet->studentClassAssignment;

                                $student =
                                    $assignment?->student;

                                $studentClass =
                                    $assignment?->studentClass;

                                $studentName = $student
                                    ? trim(
                                        collect([
                                            $student->first_name ?? null,
                                            $student->middle_name ?? null,
                                            $student->last_name ?? null
                                        ])
                                        ->filter()
                                        ->implode(' ')
                                    )
                                    : 'N/A';

                                $status = strtolower(
                                    (string) $billSheet->status
                                );

                                $isActionable =
                                    in_array(
                                        $status,
                                        $actionableStatuses,
                                        true
                                    );

                                $statusIcon = [
                                    'draft' =>
                                        'fa-file',

                                    'pending' =>
                                        'fa-clock',

                                    'approved' =>
                                        'fa-check-circle',

                                    'rejected' =>
                                        'fa-times-circle',

                                    'published' =>
                                        'fa-bullhorn',

                                ][$status] ?? 'fa-circle';

                            @endphp


                            <tr class="{{ $isActionable
                                ? ''
                                : 'locked-row' }}">


                                {{-- Checkbox --}}
                                <td class="text-center">

                                    @if($isActionable)

                                        <div class="custom-control custom-checkbox">

                                            <input type="checkbox"
                                                   class="custom-control-input bill-checkbox"
                                                   id="bill_{{ $billSheet->id }}"
                                                   value="{{ $billSheet->id }}"
                                                   data-student="{{ e($studentName) }}">

                                            <label class="custom-control-label"
                                                   for="bill_{{ $billSheet->id }}">
                                            </label>

                                        </div>

                                    @else

                                        <span class="locked-icon"
                                              title="Locked">

                                            <i class="fas fa-lock"></i>

                                        </span>

                                    @endif

                                </td>


                                {{-- Number --}}
                                <td class="font-weight-bold text-muted">

                                    {{ ($loadedBillSheets->firstItem() ?? 1)
                                        + $index }}

                                </td>


                                {{-- Student --}}
                                <td>

                                    <div class="font-weight-bold text-dark">

                                        {{ $studentName }}

                                    </div>

                                    @if($student?->student_id)

                                        <small class="text-muted">

                                            ID:
                                            {{ $student->student_id }}

                                        </small>

                                    @endif

                                </td>


                                {{-- Class --}}
                                <td>

                                    {{ $studentClass?->name ?? 'N/A' }}

                                </td>


                                {{-- Academic Year --}}
                                <td>

                                    {{ $billSheet->academicYear?->name
                                        ?? 'N/A' }}

                                </td>


                                {{-- Term --}}
                                <td>

                                    {{ $billSheet->term?->name
                                        ?? 'N/A' }}

                                </td>


                                {{-- Amount --}}
                                <td class="text-right font-weight-bold">

                                    {{ number_format(
                                        (float) $billSheet->net_amount,
                                        2
                                    ) }}

                                </td>


                                {{-- Status --}}
                                <td class="text-center">

                                    <span class="status-badge status-{{ $status }}">

                                        <i class="fas {{ $statusIcon }} mr-1"></i>

                                        {{ ucfirst($status) }}

                                    </span>

                                </td>


                                {{-- Actions --}}
                                <td class="text-center">

                                    <div class="btn-group btn-group-sm">

                                        {{-- View --}}
                                        <a href="{{ route(
                                            'bill-sheets.show',
                                            $billSheet
                                        ) }}"
                                           class="btn btn-outline-primary"
                                           title="View Bill Sheet">

                                            <i class="fas fa-eye"></i>

                                        </a>


                                        @if($isActionable)

                                            {{-- Approve --}}
                                            <button type="button"
                                                    class="btn btn-outline-success js-approve-bill"
                                                    data-bill-id="{{ $billSheet->id }}"
                                                    data-student-name="{{ e($studentName) }}"
                                                    data-status="{{ $status }}"
                                                    title="Approve">

                                                <i class="fas fa-check"></i>

                                            </button>


                                            {{-- Reject --}}
                                            <button type="button"
                                                    class="btn btn-outline-danger js-reject-bill"
                                                    data-bill-id="{{ $billSheet->id }}"
                                                    data-student-name="{{ e($studentName) }}"
                                                    data-status="{{ $status }}"
                                                    title="Reject">

                                                <i class="fas fa-times"></i>

                                            </button>

                                        @else

                                            <button type="button"
                                                    class="btn btn-outline-secondary"
                                                    disabled
                                                    title="This Bill Sheet is locked">

                                                <i class="fas fa-lock"></i>

                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}

                @if(
                    is_object($loadedBillSheets)
                    &&
                    method_exists(
                        $loadedBillSheets,
                        'hasPages'
                    )
                    &&
                    $loadedBillSheets->hasPages()
                )

                    <div class="p-3 border-top">

                        {{ $loadedBillSheets
                            ->withQueryString()
                            ->links() }}

                    </div>

                @endif


            @else

                <div class="empty-state">

                    <div class="empty-state-icon">

                        <i class="fas fa-file-invoice"></i>

                    </div>

                    <h5 class="font-weight-bold text-dark">

                        No Bill Sheets Found

                    </h5>

                    <p class="text-muted mb-0">

                        @if(!$filtersApplied)

                            Select a class, academic year and term
                            to load Bill Sheets for approval.

                        @else

                            No Bill Sheets match the selected criteria.

                        @endif

                    </p>

                </div>

            @endif

        </div>

    </div>

</div>



{{-- ====================================================================
     APPROVE ALL MODAL
===================================================================== --}}

<div class="modal fade"
     id="approveAllModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title">

                    <i class="fas fa-check-double mr-2"></i>

                    Approve All Bill Sheets

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    <i class="fas fa-info-circle mr-2"></i>

                    You are about to approve

                    <strong>
                        {{ $approvableBills }}
                    </strong>

                    draft/pending Bill Sheet(s).

                </div>


                <div class="bulk-summary-box">

                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Actionable Bill Sheets
                        </span>

                        <strong>
                            {{ $approvableBills }}
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between">

                        <span>
                            Total Amount
                        </span>

                        <strong>
                            GHS
                            {{ number_format(
                                (float) $actionableAmount,
                                2
                            ) }}
                        </strong>

                    </div>

                </div>


                <p class="text-muted small mt-3 mb-0">

                    Approved Bill Sheets will become locked
                    and cannot be approved again.

                </p>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light border"
                        data-dismiss="modal">

                    Cancel

                </button>


                <form method="POST"
                      action="{{ route(
                          'bill-sheet-approvals.approve-all'
                      ) }}">

                    @csrf

                    <input type="hidden"
                           name="student_class_id"
                           value="{{ request('student_class_id') }}">

                    <input type="hidden"
                           name="academic_year_id"
                           value="{{ request('academic_year_id') }}">

                    <input type="hidden"
                           name="term_id"
                           value="{{ request('term_id') }}">

                    <button type="submit"
                            class="btn btn-success">

                        <i class="fas fa-check-double mr-1"></i>

                        Confirm Approve All

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>



{{-- ====================================================================
     REJECT ALL MODAL
===================================================================== --}}

<div class="modal fade"
     id="rejectAllModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">

                    <i class="fas fa-times-circle mr-2"></i>

                    Reject All Bill Sheets

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <form method="POST"
                  action="{{ route(
                      'bill-sheet-approvals.reject-all'
                  ) }}">

                @csrf


                <input type="hidden"
                       name="student_class_id"
                       value="{{ request('student_class_id') }}">

                <input type="hidden"
                       name="academic_year_id"
                       value="{{ request('academic_year_id') }}">

                <input type="hidden"
                       name="term_id"
                       value="{{ request('term_id') }}">


                <div class="modal-body">

                    <div class="alert alert-warning">

                        <i class="fas fa-exclamation-triangle mr-2"></i>

                        You are about to reject

                        <strong>
                            {{ $approvableBills }}
                        </strong>

                        Bill Sheet(s).

                    </div>


                    <div class="form-group mb-0">

                        <label for="bulk_rejection_reason"
                               class="font-weight-bold">

                            Rejection Reason

                            <span class="text-danger">*</span>

                        </label>

                        <textarea name="rejection_reason"
                                  id="bulk_rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="Enter the reason for rejecting these Bill Sheets..."></textarea>

                        <small class="text-muted">
                            Maximum 1000 characters.
                        </small>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light border"
                            data-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-danger">

                        <i class="fas fa-times-circle mr-1"></i>

                        Confirm Reject All

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- ====================================================================
     APPROVE SELECTED MODAL
===================================================================== --}}

<div class="modal fade"
     id="approveSelectedModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title">

                    <i class="fas fa-check mr-2"></i>

                    Approve Selected

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    <i class="fas fa-info-circle mr-2"></i>

                    You have selected

                    <strong id="approveSelectedModalCount">
                        0
                    </strong>

                    Bill Sheet(s).

                </div>


                <p class="mb-0">

                    Are you sure you want to approve the selected
                    Bill Sheets?

                </p>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light border"
                        data-dismiss="modal">

                    Cancel

                </button>


                <form method="POST"
                      action="{{ route(
                          'bill-sheet-approvals.bulk-approve'
                      ) }}"
                      id="bulkApproveForm">

                    @csrf

                    <div id="approveSelectedInputs"></div>


                    <input type="hidden"
                           name="student_class_id"
                           value="{{ request('student_class_id') }}">

                    <input type="hidden"
                           name="academic_year_id"
                           value="{{ request('academic_year_id') }}">

                    <input type="hidden"
                           name="term_id"
                           value="{{ request('term_id') }}">


                    <button type="submit"
                            class="btn btn-success">

                        <i class="fas fa-check mr-1"></i>

                        Confirm Approval

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>



{{-- ====================================================================
     REJECT SELECTED MODAL
===================================================================== --}}

<div class="modal fade"
     id="rejectSelectedModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">

                    <i class="fas fa-times mr-2"></i>

                    Reject Selected

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <form method="POST"
                  action="{{ route(
                      'bill-sheet-approvals.bulk-reject'
                  ) }}"
                  id="bulkRejectForm">

                @csrf


                <div id="rejectSelectedInputs"></div>


                <input type="hidden"
                       name="student_class_id"
                       value="{{ request('student_class_id') }}">

                <input type="hidden"
                       name="academic_year_id"
                       value="{{ request('academic_year_id') }}">

                <input type="hidden"
                       name="term_id"
                       value="{{ request('term_id') }}">


                <div class="modal-body">

                    <div class="alert alert-warning">

                        <i class="fas fa-exclamation-triangle mr-2"></i>

                        You have selected

                        <strong id="rejectSelectedModalCount">
                            0
                        </strong>

                        Bill Sheet(s).

                    </div>


                    <div class="form-group mb-0">

                        <label for="selected_rejection_reason"
                               class="font-weight-bold">

                            Rejection Reason

                            <span class="text-danger">*</span>

                        </label>

                        <textarea name="rejection_reason"
                                  id="selected_rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="Enter the reason for rejecting the selected Bill Sheets..."></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light border"
                            data-dismiss="modal">

                        Cancel

                    </button>


                    <button type="submit"
                            class="btn btn-danger">

                        <i class="fas fa-times mr-1"></i>

                        Confirm Rejection

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- ====================================================================
     SINGLE APPROVE MODAL
===================================================================== --}}

<div class="modal fade"
     id="singleApproveModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title">

                    <i class="fas fa-check-circle mr-2"></i>

                    Approve Bill Sheet

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <div class="modal-body">

                <p>
                    Are you sure you want to approve the Bill Sheet for:
                </p>

                <div class="student-confirm-box">

                    <strong id="approveStudentName">
                        Student
                    </strong>

                    <small id="approveBillStatusText">
                        Current status: Draft
                    </small>

                </div>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light border"
                        data-dismiss="modal">

                    Cancel

                </button>


                <form method="POST"
                      id="singleApproveForm">

                    @csrf

                    <button type="submit"
                            class="btn btn-success">

                        <i class="fas fa-check mr-1"></i>

                        Approve

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>



{{-- ====================================================================
     SINGLE REJECT MODAL
===================================================================== --}}

<div class="modal fade"
     id="singleRejectModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">

                    <i class="fas fa-times-circle mr-2"></i>

                    Reject Bill Sheet

                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <form method="POST"
                  id="singleRejectForm">

                @csrf


                <div class="modal-body">

                    <p>
                        Reject the Bill Sheet for:
                    </p>


                    <div class="student-confirm-box danger">

                        <strong id="rejectStudentName">
                            Student
                        </strong>

                        <small id="rejectBillStatusText">
                            Current status: Draft
                        </small>

                    </div>


                    <div class="form-group mt-3">

                        <label for="single_rejection_reason"
                               class="font-weight-bold">

                            Rejection Reason

                            <span class="text-danger">*</span>

                        </label>

                        <textarea name="rejection_reason"
                                  id="single_rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="Enter rejection reason..."></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light border"
                            data-dismiss="modal">

                        Cancel

                    </button>


                    <button type="submit"
                            class="btn btn-danger">

                        <i class="fas fa-times mr-1"></i>

                        Reject

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection



{{-- ====================================================================
     STYLES
===================================================================== --}}

@push('styles')

<style>

    .approval-page {
        color: #212529;
    }

    .approval-page .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .approval-header-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(13, 110, 253, .10);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
    }

    .form-label-custom {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        color: #495057;
        margin-bottom: 6px;
    }

    .approval-page .form-control {
        min-height: 42px;
        border-radius: 7px;
        border-color: #dee2e6;
    }

    .approval-page .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 .18rem rgba(0,123,255,.12);
    }

    #term_id:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    #termHelp {
        display: block;
        min-height: 18px;
    }

    .filter-summary {
        border-top: 1px solid #e9ecef;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #e1e5e9;
        color: #495057;
        border-radius: 20px;
        padding: 5px 10px;
        margin-right: 6px;
        margin-bottom: 4px;
        font-size: 12px;
    }

    .summary-card {
        border-radius: 12px !important;
    }

    .summary-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    .summary-icon.primary {
        background: #e8f1ff;
        color: #0d6efd;
    }

    .summary-icon.secondary {
        background: #f0f1f2;
        color: #6c757d;
    }

    .summary-icon.warning {
        background: #fff4d6;
        color: #d39e00;
    }

    .summary-icon.info {
        background: #dff5fb;
        color: #0dcaf0;
    }

    .summary-icon.success {
        background: #e2f5ea;
        color: #198754;
    }

    .summary-icon.danger {
        background: #fbe5e7;
        color: #dc3545;
    }

    .bulk-action-card {
        border-left: 4px solid #0d6efd !important;
    }

    .bulk-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #e8f1ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .selection-counter {
        display: inline-flex;
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 7px 12px;
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .selection-counter #selectedCount {
        font-weight: 700;
        color: #0d6efd;
        margin-right: 4px;
    }

    .approval-table {
        min-width: 1100px;
    }

    .approval-table thead th {
        background: #f8f9fa;
        color: #343a40;
        border-top: 0;
        border-bottom: 2px solid #dee2e6;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
        padding: 14px 10px;
        vertical-align: middle;
    }

    .approval-table tbody td {
        padding: 14px 10px;
        vertical-align: middle;
        border-color: #edf0f2;
    }

    .approval-table tbody tr:hover {
        background: #f8fafc;
    }

    .approval-table .custom-checkbox {
        display: inline-block;
        min-height: 20px;
    }

    .approval-table .custom-control-label::before,
    .approval-table .custom-control-label::after {
        top: 0;
    }

    .locked-row {
        background: #fafafa;
    }

    .locked-row td {
        color: #6c757d;
    }

    .locked-icon {
        color: #adb5bd;
        font-size: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
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

    .empty-state {
        padding: 75px 25px;
        text-align: center;
    }

    .empty-state-icon {
        width: 76px;
        height: 76px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: #f1f3f5;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 29px;
    }

    .bulk-summary-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 14px;
    }

    .student-confirm-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 14px;
    }

    .student-confirm-box strong {
        display: block;
        color: #198754;
        font-size: 16px;
    }

    .student-confirm-box small {
        display: block;
        color: #6c757d;
        margin-top: 3px;
    }

    .student-confirm-box.danger strong {
        color: #dc3545;
    }

    .modal-content {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
    }

    .modal-header {
        border-bottom: 0;
    }

    .modal-footer {
        border-top: 1px solid #edf0f2;
    }

    @media (max-width: 768px) {

        .approval-table {
            min-width: 1000px;
        }

        .filter-chip {
            margin-top: 5px;
        }

    }

</style>

@endpush



{{-- ====================================================================
     JAVASCRIPT
===================================================================== --}}

@push('scripts')

<script>

(function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | INITIALISE
    |--------------------------------------------------------------------------
    */

    function initApprovalPage() {

        const academicYearSelect =
            document.getElementById('academic_year_id');

        const termSelect =
            document.getElementById('term_id');

        const termOptionsTemplate =
            document.getElementById('termOptionsTemplate');

        const termHelp =
            document.getElementById('termHelp');

        const requestedAcademicYearId =
            @json(request('academic_year_id'));

        const requestedTermId =
            @json(request('term_id'));


        /*
        |--------------------------------------------------------------------------
        | TERM FILTERING
        |--------------------------------------------------------------------------
        |
        | The visible Term dropdown is NEVER populated directly from $terms.
        | All terms are kept in the hidden template above, then only terms whose
        | academic_year_id matches the selected Academic Year are copied into
        | the visible dropdown.
        |--------------------------------------------------------------------------
        */

        function getTermsForAcademicYear(academicYearId) {

            if (!academicYearId || !termOptionsTemplate) {
                return [];
            }

            return Array.from(
                termOptionsTemplate.content.querySelectorAll(
                    'option[data-academic-year-id]'
                )
            ).filter(function (option) {

                return String(
                    option.dataset.academicYearId
                ) === String(
                    academicYearId
                );

            });

        }


        function populateTerms(
            academicYearId,
            selectedTermId = ''
        ) {

            if (!termSelect) {
                return;
            }

            const yearId =
                String(academicYearId || '');

            /*
            |--------------------------------------------------------------------------
            | Remove every term currently visible.
            |--------------------------------------------------------------------------
            */
            termSelect.innerHTML = '';


            /*
            |--------------------------------------------------------------------------
            | No Academic Year selected.
            |--------------------------------------------------------------------------
            */
            if (!yearId) {

                const placeholder =
                    document.createElement('option');

                placeholder.value = '';
                placeholder.textContent =
                    'Select Academic Year First';

                termSelect.appendChild(
                    placeholder
                );

                termSelect.disabled = true;

                if (termHelp) {
                    termHelp.textContent =
                        'Select an Academic Year to load its terms.';
                }

                return;
            }


            const matchingTerms =
                getTermsForAcademicYear(
                    yearId
                );


            /*
            |--------------------------------------------------------------------------
            | Placeholder.
            |--------------------------------------------------------------------------
            */
            const placeholder =
                document.createElement('option');

            placeholder.value = '';

            placeholder.textContent =
                matchingTerms.length
                    ? 'Select Term'
                    : 'No Terms Available';

            termSelect.appendChild(
                placeholder
            );


            /*
            |--------------------------------------------------------------------------
            | No terms for this Academic Year.
            |--------------------------------------------------------------------------
            */
            if (!matchingTerms.length) {

                termSelect.disabled = true;

                if (termHelp) {
                    termHelp.textContent =
                        'No terms are configured for the selected Academic Year.';
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Add ONLY terms belonging to the selected Academic Year.
            |--------------------------------------------------------------------------
            */
            matchingTerms.forEach(function (sourceOption) {

                termSelect.appendChild(
                    sourceOption.cloneNode(true)
                );

            });


            termSelect.disabled = false;


            if (termHelp) {
                termHelp.textContent =
                    matchingTerms.length +
                    ' term' +
                    (
                        matchingTerms.length === 1
                            ? ''
                            : 's'
                    ) +
                    ' available for the selected Academic Year.';
            }


            /*
            |--------------------------------------------------------------------------
            | Restore requested term only when it belongs to this year.
            |--------------------------------------------------------------------------
            */
            if (
                selectedTermId &&
                matchingTerms.some(function (option) {

                    return String(option.value) ===
                        String(selectedTermId);

                })
            ) {

                termSelect.value =
                    String(selectedTermId);

            } else {

                termSelect.value = '';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR CHANGE
        |--------------------------------------------------------------------------
        |
        | Changing the year always clears the old term first.
        |--------------------------------------------------------------------------
        */
        if (academicYearSelect) {

            academicYearSelect.addEventListener(
                'change',
                function () {

                    populateTerms(
                        academicYearSelect.value,
                        ''
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | INITIAL TERM LOAD
        |--------------------------------------------------------------------------
        */
        populateTerms(
            academicYearSelect?.value ||
            requestedAcademicYearId ||
            '',
            requestedTermId || ''
        );


        const checkboxes =
            Array.from(
                document.querySelectorAll('.bill-checkbox')
            );

        const selectAll =
            document.getElementById('selectAll');

        const selectedCount =
            document.getElementById('selectedCount');

        const headerSelectedCount =
            document.getElementById('headerSelectedCount');

        const approveSelectedBtn =
            document.getElementById('approveSelectedBtn');

        const rejectSelectedBtn =
            document.getElementById('rejectSelectedBtn');


        /*
        |--------------------------------------------------------------------------
        | GET SELECTED IDS
        |--------------------------------------------------------------------------
        */

        function getSelectedIds() {

            return checkboxes
                .filter(function (checkbox) {
                    return checkbox.checked;
                })
                .map(function (checkbox) {
                    return checkbox.value;
                });

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE SELECTION UI
        |--------------------------------------------------------------------------
        */

        function updateSelection() {

            const ids = getSelectedIds();

            const count = ids.length;


            if (selectedCount) {
                selectedCount.textContent = count;
            }


            if (headerSelectedCount) {
                headerSelectedCount.textContent = count;
            }


            if (approveSelectedBtn) {
                approveSelectedBtn.disabled = count === 0;
            }


            if (rejectSelectedBtn) {
                rejectSelectedBtn.disabled = count === 0;
            }


            if (selectAll) {

                if (count === 0) {

                    selectAll.checked = false;
                    selectAll.indeterminate = false;

                } else if (count === checkboxes.length) {

                    selectAll.checked = true;
                    selectAll.indeterminate = false;

                } else {

                    selectAll.checked = false;
                    selectAll.indeterminate = true;

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SELECT ALL
        |--------------------------------------------------------------------------
        */

        if (selectAll) {

            selectAll.addEventListener(
                'change',
                function () {

                    checkboxes.forEach(
                        function (checkbox) {

                            checkbox.checked =
                                selectAll.checked;

                        }
                    );

                    updateSelection();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | INDIVIDUAL CHECKBOXES
        |--------------------------------------------------------------------------
        */

        checkboxes.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    'change',
                    updateSelection
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | APPROVE SELECTED
        |--------------------------------------------------------------------------
        */

        if (approveSelectedBtn) {

            approveSelectedBtn.addEventListener(
                'click',
                function () {

                    const ids = getSelectedIds();

                    if (!ids.length) {

                        return;

                    }


                    const container =
                        document.getElementById(
                            'approveSelectedInputs'
                        );

                    container.innerHTML = '';


                    ids.forEach(
                        function (id) {

                            const input =
                                document.createElement('input');

                            input.type = 'hidden';
                            input.name = 'bill_sheet_ids[]';
                            input.value = id;

                            container.appendChild(input);

                        }
                    );


                    const countElement =
                        document.getElementById(
                            'approveSelectedModalCount'
                        );

                    if (countElement) {
                        countElement.textContent = ids.length;
                    }


                    $('#approveSelectedModal').modal('show');

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | REJECT SELECTED
        |--------------------------------------------------------------------------
        */

        if (rejectSelectedBtn) {

            rejectSelectedBtn.addEventListener(
                'click',
                function () {

                    const ids = getSelectedIds();

                    if (!ids.length) {

                        return;

                    }


                    const container =
                        document.getElementById(
                            'rejectSelectedInputs'
                        );

                    container.innerHTML = '';


                    ids.forEach(
                        function (id) {

                            const input =
                                document.createElement('input');

                            input.type = 'hidden';
                            input.name = 'bill_sheet_ids[]';
                            input.value = id;

                            container.appendChild(input);

                        }
                    );


                    const countElement =
                        document.getElementById(
                            'rejectSelectedModalCount'
                        );

                    if (countElement) {
                        countElement.textContent = ids.length;
                    }


                    $('#rejectSelectedModal').modal('show');

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | SINGLE APPROVAL
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            '.js-approve-bill'
        ).forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const billId =
                            this.dataset.billId;

                        const studentName =
                            this.dataset.studentName
                            || 'this student';

                        const status =
                            this.dataset.status
                            || 'draft';


                        const nameElement =
                            document.getElementById(
                                'approveStudentName'
                            );

                        const statusElement =
                            document.getElementById(
                                'approveBillStatusText'
                            );

                        const form =
                            document.getElementById(
                                'singleApproveForm'
                            );


                        if (nameElement) {

                            nameElement.textContent =
                                studentName;

                        }


                        if (statusElement) {

                            statusElement.textContent =
                                'Current status: ' +
                                status.charAt(0).toUpperCase() +
                                status.slice(1);

                        }


                        if (form && billId) {

                            form.action =
                                "{{ url('/bill-sheet-approvals') }}/" +
                                encodeURIComponent(billId) +
                                "/approve";

                        }


                        $('#singleApproveModal').modal('show');

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | SINGLE REJECTION
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            '.js-reject-bill'
        ).forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const billId =
                            this.dataset.billId;

                        const studentName =
                            this.dataset.studentName
                            || 'this student';

                        const status =
                            this.dataset.status
                            || 'draft';


                        const nameElement =
                            document.getElementById(
                                'rejectStudentName'
                            );

                        const statusElement =
                            document.getElementById(
                                'rejectBillStatusText'
                            );

                        const form =
                            document.getElementById(
                                'singleRejectForm'
                            );

                        const reason =
                            document.getElementById(
                                'single_rejection_reason'
                            );


                        if (nameElement) {

                            nameElement.textContent =
                                studentName;

                        }


                        if (statusElement) {

                            statusElement.textContent =
                                'Current status: ' +
                                status.charAt(0).toUpperCase() +
                                status.slice(1);

                        }


                        if (form && billId) {

                            form.action =
                                "{{ url('/bill-sheet-approvals') }}/" +
                                encodeURIComponent(billId) +
                                "/reject";

                        }


                        if (reason) {

                            reason.value = '';

                            reason.classList.remove(
                                'is-invalid'
                            );

                        }


                        $('#singleRejectModal').modal('show');

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | FORM VALIDATION
        |--------------------------------------------------------------------------
        */

        $('#singleRejectForm').on(
            'submit',
            function (event) {

                const field =
                    document.getElementById(
                        'single_rejection_reason'
                    );

                if (!field || !field.value.trim()) {

                    event.preventDefault();

                    if (field) {
                        field.classList.add('is-invalid');
                        field.focus();
                    }

                    return false;

                }

                return true;

            }
        );


        $('#rejectSelectedModal form').on(
            'submit',
            function (event) {

                const field =
                    document.getElementById(
                        'selected_rejection_reason'
                    );

                const ids =
                    getSelectedIds();

                if (!ids.length) {

                    event.preventDefault();

                    $('#rejectSelectedModal').modal('hide');

                    return false;

                }


                if (!field || !field.value.trim()) {

                    event.preventDefault();

                    if (field) {

                        field.classList.add(
                            'is-invalid'
                        );

                        field.focus();

                    }

                    return false;

                }

                return true;

            }
        );


        $('#rejectAllModal form').on(
            'submit',
            function (event) {

                const field =
                    document.getElementById(
                        'bulk_rejection_reason'
                    );

                if (!field || !field.value.trim()) {

                    event.preventDefault();

                    if (field) {

                        field.classList.add(
                            'is-invalid'
                        );

                        field.focus();

                    }

                    return false;

                }

                return true;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR VALIDATION
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            '#single_rejection_reason,' +
            '#selected_rejection_reason,' +
            '#bulk_rejection_reason'
        ).forEach(
            function (field) {

                field.addEventListener(
                    'input',
                    function () {

                        if (this.value.trim()) {

                            this.classList.remove(
                                'is-invalid'
                            );

                        }

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CONFIRM APPROVE SELECTED
        |--------------------------------------------------------------------------
        */

        $('#bulkApproveForm').on(
            'submit',
            function (event) {

                const ids =
                    getSelectedIds();

                if (!ids.length) {

                    event.preventDefault();

                    $('#approveSelectedModal').modal('hide');

                    return false;

                }

                return true;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | RESET SELECTION AFTER MODAL CLOSE
        |--------------------------------------------------------------------------
        */

        $('#approveSelectedModal').on(
            'hidden.bs.modal',
            function () {

                const container =
                    document.getElementById(
                        'approveSelectedInputs'
                    );

                if (container) {
                    container.innerHTML = '';
                }

            }
        );


        $('#rejectSelectedModal').on(
            'hidden.bs.modal',
            function () {

                const container =
                    document.getElementById(
                        'rejectSelectedInputs'
                    );

                if (container) {
                    container.innerHTML = '';
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | AUTO HIDE ALERTS
        |--------------------------------------------------------------------------
        */

        setTimeout(
            function () {

                $('.approval-page .alert')
                    .not('.modal .alert')
                    .alert('close');

            },
            6000
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL STATE
        |--------------------------------------------------------------------------
        */

        updateSelection();

    }


    /*
    |--------------------------------------------------------------------------
    | DOM READY
    |--------------------------------------------------------------------------
    */

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            initApprovalPage
        );

    } else {

        initApprovalPage();

    }

})();

</script>

@endpush