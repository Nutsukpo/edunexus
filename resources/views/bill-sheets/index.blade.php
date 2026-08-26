@extends('layouts.master')

@section('title', 'Bill Sheets')

@section('content')

<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">

        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                Bill Sheets
            </h4>

            <p class="text-muted mb-0">
                Manage student bill sheets by academic year, class and term.
            </p>
        </div>

        <a href="{{ route('bill-sheets.create') }}"
           class="btn btn-primary">

            <i class="fas fa-plus me-1"></i>
            Generate Bill Sheets

        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle me-1"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- ERROR MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle me-1"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- VALIDATION ERRORS --}}
    {{-- ========================================================= --}}

    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show">

            <strong>
                <i class="fas fa-exclamation-triangle me-1"></i>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- FILTERS --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold text-dark">

                    <i class="fas fa-filter text-primary me-2"></i>

                    Filter Bill Sheets

                </h5>


                @if(
                    request('academic_year_id') ||
                    request('class_id') ||
                    request('term_id') ||
                    request('status')
                )

                    <a href="{{ route('bill-sheets.index') }}"
                       class="btn btn-sm btn-outline-secondary">

                        <i class="fas fa-times me-1"></i>

                        Clear Filters

                    </a>

                @endif

            </div>

        </div>


        <div class="card-body">

            <form method="GET"
                  action="{{ route('bill-sheets.index') }}">

                <div class="row g-3">


                    {{-- ================================================= --}}
                    {{-- ACADEMIC YEAR --}}
                    {{-- ================================================= --}}

                    <div class="col-lg-3 col-md-6">

                        <label for="academic_year_id"
                               class="form-label fw-semibold text-dark">

                            Academic Year

                        </label>

                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-select filter-select text-dark">

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}"
                                    {{ (string) request('academic_year_id') === (string) $year->id ? 'selected' : '' }}>

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- ================================================= --}}
                    {{-- CLASS --}}
                    {{-- ================================================= --}}

                    <div class="col-lg-3 col-md-6">

                        <label for="class_id"
                               class="form-label fw-semibold text-dark">

                            Class

                        </label>

                        <select name="class_id"
                                id="class_id"
                                class="form-select filter-select text-dark">

                            <option value="">
                                All Classes
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}"
                                    {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>

                                    {{ $class->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- ================================================= --}}
                    {{-- TERM --}}
                    {{-- ================================================= --}}

                    <div class="col-lg-3 col-md-6">

                        <label for="term_id"
                               class="form-label fw-semibold text-dark">

                            Term

                        </label>

                        <select name="term_id"
                                id="term_id"
                                class="form-select filter-select text-dark">

                            <option value="">
                                All Terms
                            </option>

                            @foreach($terms as $term)

                                <option value="{{ $term->id }}"
                                    {{ (string) request('term_id') === (string) $term->id ? 'selected' : '' }}>

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- ================================================= --}}
                    {{-- STATUS --}}
                    {{-- ================================================= --}}

                    <div class="col-lg-3 col-md-6">

                        <label for="status"
                               class="form-label fw-semibold text-dark">

                            Status

                        </label>

                        <select name="status"
                                id="status"
                                class="form-select filter-select text-dark">

                            <option value="">
                                All Statuses
                            </option>

                            @foreach([
                                'draft'     => 'Draft',
                                'pending'   => 'Pending',
                                'approved'  => 'Approved',
                                'rejected'  => 'Rejected',
                                'published' => 'Published',
                                'archived'  => 'Archived',
                            ] as $value => $label)

                                <option value="{{ $value }}"
                                    {{ request('status') === $value ? 'selected' : '' }}>

                                    {{ $label }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- ================================================= --}}
                    {{-- BUTTONS --}}
                    {{-- ================================================= --}}

                    <div class="col-12">

                        <div class="d-flex justify-content-end gap-2">

                            @if(
                                request('academic_year_id') ||
                                request('class_id') ||
                                request('term_id') ||
                                request('status')
                            )

                                <a href="{{ route('bill-sheets.index') }}"
                                   class="btn btn-outline-secondary">

                                    <i class="fas fa-times me-1"></i>

                                    Clear

                                </a>

                            @endif

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="fas fa-search me-1"></i>

                                Apply Filters

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SUMMARY --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">


        {{-- TOTAL --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                Total Bill Sheets
                            </div>

                            <h4 class="fw-bold mb-0 text-dark">

                                {{ $billSheets->total() }}

                            </h4>

                        </div>

                        <div class="bg-primary bg-opacity-10
                                    text-primary rounded-circle p-3">

                            <i class="fas fa-file-invoice fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- DRAFT --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                Draft
                            </div>

                            <h4 class="fw-bold mb-0 text-dark">

                                {{ $billSheets->where('status', 'draft')->count() }}

                            </h4>

                        </div>

                        <div class="bg-secondary bg-opacity-10
                                    text-secondary rounded-circle p-3">

                            <i class="fas fa-file-alt fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- PENDING --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                Pending
                            </div>

                            <h4 class="fw-bold mb-0 text-dark">

                                {{ $billSheets->where('status', 'pending')->count() }}

                            </h4>

                        </div>

                        <div class="bg-warning bg-opacity-10
                                    text-warning rounded-circle p-3">

                            <i class="fas fa-clock fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- PUBLISHED --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="text-muted small">
                                Published
                            </div>

                            <h4 class="fw-bold mb-0 text-dark">

                                {{ $billSheets->where('status', 'published')->count() }}

                            </h4>

                        </div>

                        <div class="bg-success bg-opacity-10
                                    text-success rounded-circle p-3">

                            <i class="fas fa-check-circle fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- BILL SHEETS TABLE --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-1 fw-bold text-dark">

                        <i class="fas fa-list text-primary me-2"></i>

                        Student Bill Sheets

                    </h5>

                    <small class="text-muted">

                        Each row represents one student's Bill Sheet for
                        the selected class assignment, academic year and term.

                    </small>

                </div>


                @if($billSheets->total() > 0)

                    <span class="badge bg-light text-dark border">

                        {{ $billSheets->total() }}

                        {{ Str::plural('Bill Sheet', $billSheets->total()) }}

                    </span>

                @endif

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-3 text-dark">
                                #
                            </th>

                            <th class="text-dark">
                                Student
                            </th>

                            <th class="text-dark">
                                Class
                            </th>

                            <th class="text-dark">
                                Academic Year
                            </th>

                            <th class="text-dark">
                                Term
                            </th>

                            <th class="text-end text-dark">
                                Amount
                            </th>

                            <th class="text-dark">
                                Status
                            </th>

                            <th class="text-dark">
                                Generated
                            </th>

                            <th class="text-center text-dark">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($billSheets as $billSheet)

                            @php

                                /*
                                |--------------------------------------------------------------------------
                                | STUDENT CLASS ASSIGNMENT
                                |--------------------------------------------------------------------------
                                */

                                $assignment =
                                    $billSheet->studentClassAssignment;


                                $student =
                                    $assignment?->student;


                                /*
                                |--------------------------------------------------------------------------
                                | STUDENT NAME
                                |--------------------------------------------------------------------------
                                */

                                $studentName = $student
                                    ? trim(
                                        collect([
                                            $student->first_name ?? null,
                                            $student->middle_name ?? null,
                                            $student->last_name ?? null,
                                        ])
                                        ->filter()
                                        ->implode(' ')
                                    )
                                    : 'N/A';


                                /*
                                |--------------------------------------------------------------------------
                                | STATUS
                                |--------------------------------------------------------------------------
                                */

                                $statusClasses = [

                                    'draft'     => 'secondary',
                                    'pending'   => 'warning',
                                    'approved'  => 'success',
                                    'rejected'  => 'danger',
                                    'published' => 'info',
                                    'archived'  => 'dark',

                                ];


                                $statusClass =
                                    $statusClasses[$billSheet->status]
                                    ?? 'secondary';


                            @endphp


                            <tr>


                                {{-- ================================================= --}}
                                {{-- NUMBER --}}
                                {{-- ================================================= --}}

                                <td class="px-3 text-dark">

                                    {{ $billSheets->firstItem() + $loop->index }}

                                </td>


                                {{-- ================================================= --}}
                                {{-- STUDENT --}}
                                {{-- ================================================= --}}

                                <td>

                                    <div class="d-flex align-items-center">

                                    


                                        <div>

                                            <div class="fw-semibold text-dark">

                                                {{ $studentName }}

                                            </div>


                                            @if($student?->student_id)

                                                <small class="text-muted">

                                                    ID:
                                                    {{ $student->student_id }}

                                                </small>

                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- ================================================= --}}
                                {{-- CLASS --}}
                                {{-- ================================================= --}}

                                <td>

                                    <span class="fw-semibold text-dark">

                                        {{ $assignment?->studentClass?->name ?? 'N/A' }}

                                    </span>

                                </td>


                                {{-- ================================================= --}}
                                {{-- ACADEMIC YEAR --}}
                                {{-- ================================================= --}}

                                <td class="text-dark">

                                    {{ $billSheet->academicYear?->name ?? 'N/A' }}

                                </td>


                                {{-- ================================================= --}}
                                {{-- TERM --}}
                                {{-- ================================================= --}}

                                <td class="text-dark">

                                    {{ $billSheet->term?->name ?? 'N/A' }}

                                </td>


                                {{-- ================================================= --}}
                                {{-- AMOUNT --}}
                                {{-- ================================================= --}}

                                <td class="text-end">

                                    <div class="fw-bold text-dark">

                                        GHS
                                        {{ number_format((float) $billSheet->net_amount, 2) }}

                                    </div>


                                    @if((float) $billSheet->discount_amount > 0)

                                        <small class="text-muted">

                                            Discount:
                                            GHS
                                            {{ number_format((float) $billSheet->discount_amount, 2) }}

                                        </small>

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- STATUS --}}
                                {{-- ================================================= --}}

                                <td>

                                    <span class="badge bg-{{ $statusClass }} badge-lg">

                                        {{ ucfirst($billSheet->status) }}

                                    </span>

                                </td>


                                {{-- ================================================= --}}
                                {{-- GENERATED --}}
                                {{-- ================================================= --}}

                                <td class="text-dark">

                                    @if($billSheet->generated_date)

                                        {{ $billSheet->generated_date->format('d M Y') }}

                                    @else

                                        <span class="text-muted">
                                            N/A
                                        </span>

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- ACTIONS --}}
                                {{-- ================================================= --}}

                                <td class="text-center">

                                    <div class="btn-group"
                                         role="group">


                                        {{-- VIEW --}}
                                        <a href="{{ route('bill-sheets.show', $billSheet->id) }}"
                                           class="btn btn-sm btn-outline-info"
                                           title="View Bill Sheet">

                                            <i class="fas fa-eye"></i>

                                        </a>


                                        {{-- EDIT --}}
                                        @if($billSheet->status === 'draft')

                                            <a href="{{ route('bill-sheets.edit', $billSheet->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit Bill Sheet">

                                                <i class="fas fa-edit"></i>

                                            </a>

                                        @endif


                                        {{-- ================================================= --}}
                                        {{-- REGENERATE CLASS --}}
                                        {{-- ================================================= --}}

                                        @if($billSheet->status === 'draft' && $assignment)
                                            <form action="{{ route('bill-sheets.regenerate', $billSheet->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Regenerate all bills for this class"
                                                        onclick="return confirm('This will use this Bill Sheet as the template and regenerate the draft Bill Sheets for all eligible students in this class, academic year and term. Bill Sheets with payments will be skipped. Continue?')">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                        @endif


                                        {{-- ================================================= --}}
                                        {{-- TOGGLE STATUS --}}
                                        {{-- ================================================= --}}

                                        <form action="{{ route('bill-sheets.toggle-status', $billSheet->id) }}"
                                              method="POST"
                                              class="d-inline">

                                            @csrf

                                            @php

                                                $statusAction = match($billSheet->status) {

                                                    'draft' =>
                                                        [
                                                            'label' => 'Publish',
                                                            'icon'  => 'fa-check-circle',
                                                        ],

                                                    'published' =>
                                                        [
                                                            'label' => 'Archive',
                                                            'icon'  => 'fa-archive',
                                                        ],

                                                    'archived' =>
                                                        [
                                                            'label' => 'Activate',
                                                            'icon'  => 'fa-undo',
                                                        ],

                                                    default =>
                                                        [
                                                            'label' => 'Change Status',
                                                            'icon'  => 'fa-exchange-alt',
                                                        ],

                                                };

                                            @endphp


                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="{{ $statusAction['label'] }}"
                                                    onclick="return confirm('Are you sure you want to change the status of this Bill Sheet?')">

                                                <i class="fas {{ $statusAction['icon'] }}"></i>

                                            </button>

                                        </form>


                                        {{-- ================================================= --}}
                                        {{-- PRINT --}}
                                        {{-- ================================================= --}}

                                        <!-- <a href="{{ route('bill-sheets.print', $billSheet->id) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Print">

                                            <i class="fas fa-print"></i>

                                        </a> -->


                                        {{-- ================================================= --}}
                                        {{-- PDF --}}
                                        {{-- ================================================= --}}

                                        <a href="{{ route('bill-sheets.pdf', $billSheet->id) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Download PDF">

                                            <i class="fas fa-file-pdf"></i>

                                        </a>


                                        {{-- ================================================= --}}
                                        {{-- DUPLICATE --}}
                                        {{-- ================================================= --}}
<!-- 
                                        <a href="{{ route('bill-sheets.duplicate', $billSheet->id) }}"
                                           class="btn btn-sm btn-outline-dark"
                                           title="Duplicate Bill Sheet"
                                           onclick="return confirm('Are you sure you want to duplicate this Bill Sheet?')">

                                            <i class="fas fa-copy"></i>

                                        </a> -->


                                        {{-- ================================================= --}}
                                        {{-- DELETE --}}
                                        {{-- ================================================= --}}

                                        @if($billSheet->status === 'draft')

                                            <form action="{{ route('bill-sheets.destroy', $billSheet->id) }}"
                                                  method="POST"
                                                  class="d-inline">

                                                @csrf

                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this Bill Sheet?')">

                                                    <i class="fas fa-trash"></i>

                                                </button>

                                            </form>

                                        @endif


                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="9"
                                    class="text-center py-5">

                                    <div class="mb-3">

                                        <i class="fas fa-file-invoice fa-3x text-muted opacity-50"></i>

                                    </div>

                                    <h5 class="text-muted">

                                        No Bill Sheets Found

                                    </h5>

                                    <p class="text-muted mb-3">

                                        There are no Bill Sheets matching
                                        your current filters.

                                    </p>


                                    <a href="{{ route('bill-sheets.create') }}"
                                       class="btn btn-primary">

                                        <i class="fas fa-plus me-1"></i>

                                        Generate Bill Sheets

                                    </a>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- PAGINATION --}}
        {{-- ========================================================= --}}

        @if($billSheets->hasPages())

            <div class="card-footer bg-white border-top">

                <div class="d-flex justify-content-between
                            align-items-center
                            flex-wrap
                            gap-2">

                    <div class="text-muted small">

                        Showing

                        <strong class="text-dark">
                            {{ $billSheets->firstItem() ?? 0 }}
                        </strong>

                        to

                        <strong class="text-dark">
                            {{ $billSheets->lastItem() ?? 0 }}
                        </strong>

                        of

                        <strong class="text-dark">
                            {{ $billSheets->total() }}
                        </strong>

                        Bill Sheets

                    </div>


                    <div>

                        {{ $billSheets->withQueryString()->links() }}

                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

@endsection


{{-- ============================================================= --}}
{{-- SELECT2 STYLES --}}
{{-- ============================================================= --}}

@push('styles')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
      rel="stylesheet">


<style>

    /*
    |--------------------------------------------------------------------------
    | BADGE LARGE
    |--------------------------------------------------------------------------
    */

    .badge-lg {
        font-size: 90%;
        padding: 0.5rem 1rem;
        border-radius: 0.3rem;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT2 CONTAINER
    |--------------------------------------------------------------------------
    */

    .select2-container {
        width: 100% !important;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECT2 SINGLE SELECT
    |--------------------------------------------------------------------------
    */

    .select2-container--default
    .select2-selection--single {

        height: 38px !important;

        min-height: 38px !important;

        border: 1px solid #ced4da !important;

        border-radius: .375rem !important;

        background-color: #fff !important;

        display: flex !important;

        align-items: center !important;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECTED TEXT
    |--------------------------------------------------------------------------
    */

    .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {

        color: #212529 !important;

        line-height: 38px !important;

        padding-left: 12px !important;

        padding-right: 35px !important;

    }


    /*
    |--------------------------------------------------------------------------
    | ARROW
    |--------------------------------------------------------------------------
    */

    .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {

        height: 36px !important;

        right: 5px !important;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECT2 DROPDOWN
    |--------------------------------------------------------------------------
    */

    .select2-dropdown {

        z-index: 99999 !important;

        border: 1px solid #ced4da !important;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECT2 SEARCH
    |--------------------------------------------------------------------------
    */

    .select2-search__field {

        color: #212529 !important;

        background-color: #fff !important;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECT2 OPTIONS
    |--------------------------------------------------------------------------
    */

    .select2-container--default
    .select2-results__option {

        color: #212529 !important;

        background-color: #ffffff !important;

        padding: 8px 12px !important;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECT2 HIGHLIGHTED OPTION
    |--------------------------------------------------------------------------
    */

    .select2-container--default
    .select2-results__option--highlighted[aria-selected] {

        color: #ffffff !important;

        background-color: #0d6efd !important;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECT2 SELECTED OPTION
    |--------------------------------------------------------------------------
    */

    .select2-container--default
    .select2-results__option[aria-selected="true"] {

        background-color: #e9ecef !important;

        color: #212529 !important;

    }


    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    .table > :not(caption) > * > * {

        padding-top: .85rem;

        padding-bottom: .85rem;

    }


    /*
    |--------------------------------------------------------------------------
    | ACTION BUTTONS
    |--------------------------------------------------------------------------
    */

    .btn-group .btn {

        min-width: 34px;

    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {

        .btn-group {

            display: flex;

            flex-wrap: wrap;

            gap: 2px;

        }

        .btn-group .btn {

            border-radius: .25rem !important;

        }

    }

</style>

@endpush


{{-- ============================================================= --}}
{{-- SELECT2 SCRIPTS --}}
{{-- ============================================================= --}}

@push('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>

    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | INITIALIZE SELECT2
        |--------------------------------------------------------------------------
        */

        $('.filter-select').select2({

            width: '100%',

            allowClear: true,

            minimumResultsForSearch: 0,

        });


        /*
        |--------------------------------------------------------------------------
        | KEEP SELECT2 ACCESSIBLE
        |--------------------------------------------------------------------------
        */

        $('.filter-select').on('select2:open', function () {

            setTimeout(function () {

                document
                    .querySelector('.select2-container--open .select2-search__field')
                    ?.focus();

            }, 50);

        });

    });

</script>

@endpush
