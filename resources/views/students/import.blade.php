@extends('layouts.master')

@section('title', 'Import Students')

@section('content')

<div class="container-fluid py-3">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h5 class="mb-1 fw-bold">
                <i class="fas fa-file-import text-primary me-2"></i>
                Import Students
            </h5>

            <small class="text-muted">
                Bulk import student records and assign them to a class.
            </small>

        </div>

        <div>

            <a href="{{ route('students.index') }}"
               class="btn btn-outline-secondary">

                <i class="fas fa-arrow-left me-1"></i>

                Back to Students

            </a>

        </div>

    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
    ========================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- =========================================================
         ERROR MESSAGE
    ========================================================== --}}
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle me-2"></i>

            <strong>Import failed.</strong>

            <div class="mt-1">
                {{ session('error') }}
            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <div class="fw-bold mb-2">

                <i class="fas fa-exclamation-triangle me-2"></i>

                Please correct the following:

            </div>

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         PREVIEW
    ========================================================== --}}
    @if(!empty($preview))

        <div class="card border-0 shadow-sm mb-4">

            {{-- HEADER --}}

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>

                        <h5 class="mb-1">

                            <i class="fas fa-table text-primary me-2"></i>

                            Import Preview

                        </h5>

                        <small class="text-muted">

                            Review the student records before importing them.

                        </small>

                    </div>

                    <span class="badge bg-primary fs-6">

                        {{ $studentClass->name }}

                    </span>

                </div>

            </div>


            {{-- BODY --}}

            <div class="card-body">


                {{-- =================================================
                     SUMMARY CARDS
                ================================================== --}}

                <div class="row g-3 mb-4">

                    {{-- TOTAL --}}

                    <div class="col-md-3">

                        <div class="summary-box">

                            <div class="summary-icon">

                                <i class="fas fa-users"></i>

                            </div>

                            <div class="summary-number">

                                {{ $totalRows }}

                            </div>

                            <div class="summary-label">

                                Total Rows

                            </div>

                        </div>

                    </div>


                    {{-- READY --}}

                    <div class="col-md-3">

                        <div class="summary-box success-box">

                            <div class="summary-icon">

                                <i class="fas fa-check-circle"></i>

                            </div>

                            <div class="summary-number">

                                {{ $validRows }}

                            </div>

                            <div class="summary-label">

                                Ready to Import

                            </div>

                        </div>

                    </div>


                    {{-- ERRORS --}}

                    <div class="col-md-3">

                        <div class="summary-box danger-box">

                            <div class="summary-icon">

                                <i class="fas fa-triangle-exclamation"></i>

                            </div>

                            <div class="summary-number">

                                {{ $invalidRows }}

                            </div>

                            <div class="summary-label">

                                Errors

                            </div>

                        </div>

                    </div>


                    {{-- EXISTING --}}

                    <div class="col-md-3">

                        <div class="summary-box warning-box">

                            <div class="summary-icon">

                                <i class="fas fa-user-check"></i>

                            </div>

                            <div class="summary-number">

                                {{ $existingRows }}

                            </div>

                            <div class="summary-label">

                                Existing Students

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     SELECTED PERIOD
                ================================================== --}}

                <div class="period-summary mb-4">

                    <div class="period-item">

                        <span class="period-label">
                            Academic Year
                        </span>

                        <strong>
                            {{ $academicYear->name }}
                        </strong>

                    </div>


                    <div class="period-divider"></div>


                    <div class="period-item">

                        <span class="period-label">
                            Term
                        </span>

                        <strong>
                            {{ $term->name }}
                        </strong>

                    </div>


                    <div class="period-divider"></div>


                    <div class="period-item">

                        <span class="period-label">
                            Class
                        </span>

                        <strong>
                            {{ $studentClass->name }}

                            @if($studentClass->stream)
                                - {{ $studentClass->stream }}
                            @endif

                        </strong>

                    </div>

                </div>


                {{-- =================================================
                     INFORMATION
                ================================================== --}}

                <div class="alert alert-info border-0">

                    <div class="d-flex align-items-start">

                        <i class="fas fa-circle-info me-2 mt-1"></i>

                        <div>

                            <strong>Before importing</strong>

                            <div class="small mt-1">

                                New students will automatically receive a
                                permanent EDUNEXUS Student ID.

                                Existing students can be identified using
                                their EDUNEXUS Student ID.

                                Previous class history will not be deleted.

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     PREVIEW TABLE
                ================================================== --}}

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th style="width: 70px;">
                                    Row
                                </th>

                                <th>
                                    EDUNEXUS Student ID
                                </th>

                                <th>
                                    Student
                                </th>

                                <th>
                                    Gender
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Details
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($previewRows as $row)

                                @php

                                    $data =
                                        $row['data'] ?? [];

                                    $rowErrors =
                                        $row['errors'] ?? [];

                                    $hasErrors =
                                        !empty($rowErrors);

                                    $existingStudentId =
                                        $row['existing_student_id'] ?? null;

                                    $studentName =
                                        trim(
                                            implode(
                                                ' ',
                                                array_filter([
                                                    $data['first_name'] ?? '',
                                                    $data['middle_name'] ?? '',
                                                    $data['last_name'] ?? '',
                                                ])
                                            )
                                        );

                                    $suppliedStudentId =
                                        trim(
                                            $data['student_id'] ?? ''
                                        );

                                @endphp


                                <tr>

                                    {{-- ROW --}}

                                    <td>

                                        <span class="text-muted">

                                            {{ $row['row_number'] ?? '-' }}

                                        </span>

                                    </td>


                                    {{-- STUDENT ID --}}

                                    <td>

                                        @if($suppliedStudentId)

                                            <span class="fw-semibold">

                                                {{ $suppliedStudentId }}

                                            </span>

                                            @if($existingStudentId)

                                                <br>

                                                <small class="text-success">

                                                    <i class="fas fa-user-check me-1"></i>

                                                    Existing EDUNEXUS student

                                                </small>

                                            @endif

                                        @else

                                            <span class="badge bg-success">

                                                <i class="fas fa-magic me-1"></i>

                                                Auto-generated

                                            </span>

                                            <br>

                                            <small class="text-muted">

                                                EDUNEXUS will generate the ID

                                            </small>

                                        @endif

                                    </td>


                                    {{-- STUDENT --}}

                                    <td>

                                        <div class="fw-semibold">

                                            {{ $studentName ?: 'Unnamed Student' }}

                                        </div>

                                    </td>


                                    {{-- GENDER --}}

                                    <td>

                                        {{ $data['gender'] ?? '-' }}

                                    </td>


                                    {{-- STATUS --}}

                                    <td>

                                        @if($hasErrors)

                                            <span class="badge bg-danger">

                                                <i class="fas fa-times me-1"></i>

                                                Error

                                            </span>

                                        @elseif($existingStudentId)

                                            <span class="badge bg-warning text-dark">

                                                <i class="fas fa-user-check me-1"></i>

                                                Existing

                                            </span>

                                        @else

                                            <span class="badge bg-success">

                                                <i class="fas fa-check me-1"></i>

                                                Ready

                                            </span>

                                        @endif

                                    </td>


                                    {{-- DETAILS --}}

                                    <td>

                                        @if($hasErrors)

                                            <ul class="mb-0 ps-3 text-danger small">

                                                @foreach($rowErrors as $error)

                                                    <li>
                                                        {{ $error }}
                                                    </li>

                                                @endforeach

                                            </ul>

                                        @elseif($existingStudentId)

                                            <small class="text-muted">

                                                This existing student will be
                                                assigned to

                                                <strong>
                                                    {{ $studentClass->name }}
                                                </strong>

                                                for

                                                <strong>
                                                    {{ $academicYear->name }}
                                                </strong>

                                                -

                                                <strong>
                                                    {{ $term->name }}
                                                </strong>.

                                            </small>

                                        @else

                                            <small class="text-success">

                                                <i class="fas fa-plus-circle me-1"></i>

                                                New student will be created
                                                and assigned to the selected
                                                class.

                                            </small>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- =================================================
                     NO VALID RECORDS
                ================================================== --}}

                @if($validRows <= 0)

                    <div class="alert alert-danger mt-4 mb-0">

                        <i class="fas fa-ban me-2"></i>

                        <strong>No students are ready for import.</strong>

                        Please correct the errors and upload the CSV again.

                    </div>

                @endif


                {{-- =================================================
                     ACTIONS
                ================================================== --}}

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">

                    <a href="{{ route('students.import') }}"
                       class="btn btn-outline-secondary">

                        <i class="fas fa-redo me-1"></i>

                        Upload Different CSV

                    </a>


                    @if($validRows > 0)

                        <form method="POST"
                              action="{{ route('students.import.store') }}"
                              id="finalImportForm">

                            @csrf

                            <input type="hidden"
                                   name="import_token"
                                   value="{{ $importToken }}">


                            <button type="submit"
                                    class="btn btn-success btn-lg"
                                    id="finalImportButton">

                                <i class="fas fa-file-import me-1"></i>

                                Import {{ $validRows }} Student(s)

                            </button>

                        </form>

                    @endif

                </div>

            </div>

        </div>


    @else


        {{-- =========================================================
             UPLOAD FORM
        ========================================================== --}}

        <div class="row g-4">

            {{-- =====================================================
                 LEFT: UPLOAD
            ====================================================== --}}

            <div class="col-xl-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-1">

                            <i class="fas fa-upload text-primary me-2"></i>

                            Upload Student CSV

                        </h5>

                        <small class="text-muted">

                            Select the academic period and class before
                            uploading your student records.

                        </small>

                    </div>


                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('students.import.preview') }}"
                              enctype="multipart/form-data"
                              id="studentImportForm">

                            @csrf


                            {{-- =================================================
                                 ACADEMIC YEAR
                            ================================================== --}}

                            <div class="mb-4">

                                <label for="academicYearSelect"
                                       class="form-label fw-semibold">

                                    Academic Year

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="academic_year_id"
                                        id="academicYearSelect"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Academic Year
                                    </option>


                                    @foreach($academicYears as $year)

                                        <option value="{{ $year->id }}"
                                            {{ old(
                                                'academic_year_id',
                                                $currentAcademicYearId
                                            ) == $year->id
                                                ? 'selected'
                                                : '' }}>

                                            {{ $year->name }}

                                            @if($year->is_active)

                                                (Active)

                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                                <div class="form-text">

                                    The selected academic year will be used
                                    for the new class assignment.

                                </div>

                            </div>


                            {{-- =================================================
                                 TERM
                            ================================================== --}}

                            <div class="mb-4">

                                <label for="termSelect"
                                       class="form-label fw-semibold">

                                    Term

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="term_id"
                                        id="termSelect"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Term
                                    </option>


                                    @foreach($terms as $termOption)

                                        <option value="{{ $termOption->id }}"
                                            {{ old(
                                                'term_id',
                                                $currentTermId
                                            ) == $termOption->id
                                                ? 'selected'
                                                : '' }}>

                                            {{ $termOption->name }}

                                        </option>

                                    @endforeach

                                </select>

                                <div class="form-text">

                                    Only terms belonging to the selected
                                    academic year are available.

                                </div>

                            </div>


                            {{-- =================================================
                                 CLASS
                            ================================================== --}}

                            <div class="mb-4">

                                <label for="studentClassSelect"
                                       class="form-label fw-semibold">

                                    Assign All Imported Students To

                                    <span class="text-danger">*</span>

                                </label>


                                <select name="student_class_id"
                                        id="studentClassSelect"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Select Class
                                    </option>


                                    @foreach($classes as $class)

                                        <option value="{{ $class->id }}"
                                            {{ old('student_class_id') == $class->id
                                                ? 'selected'
                                                : '' }}>

                                            {{ $class->name }}

                                            @if($class->stream)

                                                - {{ $class->stream }}

                                            @endif

                                        </option>

                                    @endforeach

                                </select>


                                <div class="form-text">

                                    Every valid new student in this CSV will
                                    be assigned to this class.

                                </div>

                            </div>


                            {{-- =================================================
                                 CSV FILE
                            ================================================== --}}

                            <div class="mb-4">

                                <label for="csvFile"
                                       class="form-label fw-semibold">

                                    Student CSV File

                                    <span class="text-danger">*</span>

                                </label>


                                <input type="file"
                                       name="csv_file"
                                       id="csvFile"
                                       class="form-control"
                                       accept=".csv,.txt"
                                       required>


                                <div class="form-text">

                                    Accepted formats: CSV or TXT.
                                    Maximum file size: 10 MB.

                                </div>

                            </div>


                            {{-- =================================================
                                 CSV ID INFORMATION
                            ================================================== --}}

                            <div class="alert alert-light border mb-4">

                                <div class="d-flex align-items-start">

                                    <i class="fas fa-id-card text-primary me-2 mt-1"></i>

                                    <div>

                                        <strong>
                                            Student ID handling
                                        </strong>

                                        <div class="small text-muted mt-1">

                                            You do not need to provide an
                                            EDUNEXUS Student ID for new
                                            students. EDUNEXUS will generate
                                            one automatically.

                                        </div>

                                        <div class="small text-muted mt-1">

                                            If the CSV contains an existing
                                            EDUNEXUS Student ID, EDUNEXUS
                                            will use that existing student
                                            instead of creating a duplicate.

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                                 ACTIONS
                            ================================================== --}}

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                                <a href="{{ route('students.import.template') }}"
                                   class="btn btn-outline-success">

                                    <i class="fas fa-file-csv me-1"></i>

                                    Download CSV Template

                                </a>


                                <button type="submit"
                                        class="btn btn-primary btn-lg"
                                        id="previewButton">

                                    <i class="fas fa-search me-1"></i>

                                    Validate & Preview

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 RIGHT: INSTRUCTIONS
            ====================================================== --}}

            <div class="col-xl-4">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">

                        <h6 class="mb-0 fw-bold">

                            <i class="fas fa-circle-info text-primary me-2"></i>

                            How It Works

                        </h6>

                    </div>


                    <div class="card-body">


                        {{-- STEP 1 --}}

                        <div class="instruction">

                            <span>1</span>

                            <div>

                                <strong>
                                    Download Template
                                </strong>

                                <p>
                                    Download the EDUNEXUS CSV template and
                                    enter the school's student records.
                                </p>

                            </div>

                        </div>


                        {{-- STEP 2 --}}

                        <div class="instruction">

                            <span>2</span>

                            <div>

                                <strong>
                                    Select Academic Year
                                </strong>

                                <p>
                                    Select the academic year in which these
                                    students are being assigned.
                                </p>

                            </div>

                        </div>


                        {{-- STEP 3 --}}

                        <div class="instruction">

                            <span>3</span>

                            <div>

                                <strong>
                                    Select Term
                                </strong>

                                <p>
                                    Select the term belonging to the chosen
                                    academic year.
                                </p>

                            </div>

                        </div>


                        {{-- STEP 4 --}}

                        <div class="instruction">

                            <span>4</span>

                            <div>

                                <strong>
                                    Select Class
                                </strong>

                                <p>
                                    All valid students will be assigned to
                                    this class.
                                </p>

                            </div>

                        </div>


                        {{-- STEP 5 --}}

                        <div class="instruction">

                            <span>5</span>

                            <div>

                                <strong>
                                    Upload CSV
                                </strong>

                                <p>
                                    Upload the completed student spreadsheet.
                                </p>

                            </div>

                        </div>


                        {{-- STEP 6 --}}

                        <div class="instruction mb-0">

                            <span>6</span>

                            <div>

                                <strong>
                                    Validate, Review & Import
                                </strong>

                                <p>
                                    EDUNEXUS validates the file before anything
                                    is saved to the database.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     IMPORTANT NOTICE
                ================================================== --}}

                <div class="alert alert-warning mt-3">

                    <div class="d-flex align-items-start">

                        <i class="fas fa-shield-halved me-2 mt-1"></i>

                        <div>

                            <strong>
                                Important
                            </strong>

                            <p class="mb-0 mt-2 small">

                                Existing student records are not deleted or
                                overwritten. Previous class assignments remain
                                in the student's history.

                            </p>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     ID NOTICE
                ================================================== --}}

                <div class="alert alert-primary mt-3">

                    <div class="d-flex align-items-start">

                        <i class="fas fa-wand-magic-sparkles me-2 mt-1"></i>

                        <div>

                            <strong>
                                Automatic Student IDs
                            </strong>

                            <p class="mb-0 mt-2 small">

                                New students receive permanent EDUNEXUS
                                Student IDs automatically, for example:

                                <strong>
                                    STD-000001
                                </strong>

                                ,

                                <strong>
                                    STD-000002
                                </strong>

                                ,

                                <strong>
                                    STD-000003
                                </strong>

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>


{{-- ===============================================================
     STYLES
================================================================ --}}

<style>

.card {
    border-radius: 12px;
}

.card-header {
    border-bottom: 1px solid #edf0f4;
}

.form-control,
.form-select {
    border-radius: 8px;
    min-height: 44px;
}

.btn {
    border-radius: 8px;
}

.summary-box {
    padding: 20px;
    border-radius: 12px;
    background: #f8f9fa;
    text-align: center;
    border: 1px solid #e9ecef;
    height: 100%;
}

.summary-icon {
    font-size: 18px;
    margin-bottom: 6px;
    opacity: .75;
}

.success-box {
    background: rgba(25, 135, 84, .08);
    border-color: rgba(25, 135, 84, .15);
}

.danger-box {
    background: rgba(220, 53, 69, .08);
    border-color: rgba(220, 53, 69, .15);
}

.warning-box {
    background: rgba(255, 193, 7, .12);
    border-color: rgba(255, 193, 7, .2);
}

.summary-number {
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
}

.summary-label {
    font-size: 13px;
    color: #6c757d;
    margin-top: 4px;
}

.period-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    padding: 18px 20px;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    background: #f8f9fa;
}

.period-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.period-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.period-divider {
    width: 1px;
    height: 35px;
    background: #dee2e6;
}

.instruction {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.instruction > span {
    min-width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(13, 110, 253, .1);
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

.instruction strong {
    display: block;
    margin-bottom: 3px;
}

.instruction p {
    color: #6c757d;
    font-size: 13px;
    margin: 0;
    line-height: 1.5;
}

.table th {
    font-size: 13px;
    white-space: nowrap;
}

.table td {
    font-size: 13px;
}

.table thead th {
    vertical-align: middle;
}

.badge {
    font-weight: 600;
}

@media (max-width: 767.98px) {

    .period-summary {
        flex-direction: column;
        align-items: flex-start;
    }

    .period-divider {
        width: 100%;
        height: 1px;
    }

}

</style>


{{-- ===============================================================
     JAVASCRIPT
================================================================ --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const academicYearSelect =
        document.getElementById('academicYearSelect');

    const termSelect =
        document.getElementById('termSelect');

    const importForm =
        document.getElementById('studentImportForm');

    const previewButton =
        document.getElementById('previewButton');

    const finalImportForm =
        document.getElementById('finalImportForm');

    const finalImportButton =
        document.getElementById('finalImportButton');


    /*
    |--------------------------------------------------------------------------
    | LOAD TERMS WHEN ACADEMIC YEAR CHANGES
    |--------------------------------------------------------------------------
    */

    if (academicYearSelect && termSelect) {

        academicYearSelect.addEventListener(
            'change',
            function () {

                const yearId =
                    this.value;

                termSelect.innerHTML =
                    '<option value="">Loading terms...</option>';

                termSelect.disabled = true;


                if (!yearId) {

                    termSelect.innerHTML =
                        '<option value="">Select Term</option>';

                    termSelect.disabled = false;

                    return;
                }


                const url =
                    "{{ route('students.import.terms') }}" +
                    '?academic_year_id=' +
                    encodeURIComponent(yearId);


                fetch(
                    url,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                )

                .then(function (response) {

                    if (!response.ok) {

                        throw new Error(
                            'Unable to load terms.'
                        );

                    }

                    return response.json();

                })

                .then(function (data) {

                    termSelect.innerHTML =
                        '<option value="">Select Term</option>';


                    if (
                        data.success &&
                        Array.isArray(data.terms)
                    ) {

                        data.terms.forEach(
                            function (item) {

                                const option =
                                    document.createElement(
                                        'option'
                                    );

                                option.value =
                                    item.id;

                                option.textContent =
                                    item.name;

                                termSelect.appendChild(
                                    option
                                );

                            }
                        );

                    }


                    termSelect.disabled = false;

                })

                .catch(function (error) {

                    console.error(error);

                    termSelect.innerHTML =
                        '<option value="">Unable to load terms</option>';

                    termSelect.disabled = false;

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PREVIEW FORM
    |--------------------------------------------------------------------------
    */

    if (importForm) {

        importForm.addEventListener(
            'submit',
            function () {

                if (!importForm.checkValidity()) {

                    return;
                }


                if (previewButton) {

                    previewButton.disabled =
                        true;

                    previewButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' +
                        'Validating CSV...';

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FINAL IMPORT CONFIRMATION
    |--------------------------------------------------------------------------
    */

    if (finalImportForm) {

        finalImportForm.addEventListener(
            'submit',
            function (event) {

                const validCount =
                    {{ (int) ($validRows ?? 0) }};


                const confirmed =
                    confirm(
                        'Are you sure you want to import ' +
                        validCount +
                        ' student(s)?\n\n' +
                        'The valid records will be created or assigned to the selected class.\n\n' +
                        'Existing student records will not be deleted.'
                    );


                if (!confirmed) {

                    event.preventDefault();

                    return;
                }


                if (finalImportButton) {

                    finalImportButton.disabled =
                        true;

                    finalImportButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' +
                        'Importing Students...';

                }

            }
        );

    }

});

</script>

@endsection