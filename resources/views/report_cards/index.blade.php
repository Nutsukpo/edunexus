@extends('layouts.master')

@section('title', 'Report Cards')

@section('content')

<div class="container-fluid report-card-page">

    {{-- HEADER --}}
    <div class="card border-0 shadow-sm mb-3">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="header-icon">
                        <i class="fas fa-id-card"></i>
                    </div>

                    <div>

                        <h4 class="fw-bold mb-1">
                            Report Cards
                        </h4>

                        <div class="text-muted small">
                            Generate and view student report cards
                        </div>

                    </div>

                </div>


                @if($currentAcademicYear && $currentTerm)

                    <div class="text-end">

                        <div class="small text-muted">
                            Current Academic Period
                        </div>

                        <div class="mt-1">

                            <span class="badge bg-primary">
                                {{ $currentAcademicYear->name }}
                            </span>

                            <span class="badge bg-success">
                                {{ $currentTerm->name }}
                            </span>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- MAIN --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-primary text-white py-3">

            <div class="d-flex align-items-center gap-2">

                <i class="fas fa-file-alt"></i>

                <strong>
                    Generate Report Card
                </strong>

            </div>

        </div>


        <div class="card-body">

            @if(session('error'))

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle me-2"></i>

                    {{ session('error') }}

                </div>

            @endif


            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-1">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- CURRENT PERIOD --}}
            @if($currentAcademicYear && $currentTerm)

                <div class="period-banner mb-4">

                    <div class="d-flex align-items-center gap-2">

                        <i class="fas fa-calendar-check text-primary"></i>

                        <strong>
                            Current Academic Period
                        </strong>

                    </div>

                    <div class="mt-2">

                        <span class="badge bg-primary">
                            {{ $currentAcademicYear->name }}
                        </span>

                        <span class="badge bg-success">
                            {{ $currentTerm->name }}
                        </span>

                    </div>

                    @if($currentTerm->start_date && $currentTerm->end_date)

                        <div class="small text-muted mt-2">

                            <i class="fas fa-clock me-1"></i>

                            {{ \Carbon\Carbon::parse($currentTerm->start_date)->format('d M Y') }}

                            —

                            {{ \Carbon\Carbon::parse($currentTerm->end_date)->format('d M Y') }}

                        </div>

                    @endif

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('report-cards.show') }}"
                id="reportCardForm"
            >

                @csrf


                {{-- PERIOD MODE --}}
                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Report Period
                    </label>

                    <div class="period-options">

                        <label
                            class="period-option active"
                            id="currentPeriodOption"
                        >

                            <input
                                type="radio"
                                name="period_mode"
                                value="current"
                                checked
                            >

                            <div>

                                <strong>
                                    <i class="fas fa-calendar-check me-1"></i>
                                    Current Period
                                </strong>

                                <small>
                                    Use the period configured in System Settings.
                                </small>

                            </div>

                        </label>


                        <label
                            class="period-option"
                            id="historicalPeriodOption"
                        >

                            <input
                                type="radio"
                                name="period_mode"
                                value="historical"
                            >

                            <div>

                                <strong>
                                    <i class="fas fa-history me-1"></i>
                                    Historical Period
                                </strong>

                                <small>
                                    View results from a previous academic period.
                                </small>

                            </div>

                        </label>

                    </div>

                </div>


                <div class="row g-3">

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-lg-4">

                        <label
                            for="academicYear"
                            class="form-label fw-semibold"
                        >
                            Academic Year
                        </label>

                        <select
                            name="academic_year_display"
                            id="academicYear"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Academic Year
                            </option>

                            @foreach($academicYears as $year)

                                <option
                                    value="{{ $year->id }}"
                                    {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}
                                >

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                    
                        {{-- This hidden field is submitted even when the visible selector is disabled. --}}
                        <input
                            type="hidden"
                            name="academic_year_id"
                            id="submittedAcademicYearId"
                            value="{{ old('academic_year_id', $currentAcademicYear?->id) }}"
                        >

                    </div>


                    {{-- TERM --}}
                    <div class="col-lg-4">

                        <label
                            for="term"
                            class="form-label fw-semibold"
                        >
                            Term
                        </label>

                        <select
                            name="term_id"
                            id="term"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Loading...
                            </option>

                        </select>

                        <div
                            id="termLoading"
                            class="small text-primary mt-2 d-none"
                        >

                            <span class="spinner-border spinner-border-sm me-1"></span>

                            Loading terms...

                        </div>

                    </div>


                    {{-- CLASS --}}
                    <div class="col-lg-4">

                        <label
                            for="studentClass"
                            class="form-label fw-semibold"
                        >
                            Class
                        </label>

                        <select
                            name="student_class_id"
                            id="studentClass"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}">
                                    {{ $class->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- STUDENT --}}
                    <div class="col-lg-8">

                        <label
                            for="student"
                            class="form-label fw-semibold"
                        >
                            Student
                        </label>

                        <select
                            name="student_id"
                            id="student"
                            class="form-select"
                            required
                            disabled
                        >

                            <option value="">
                                Select period and class first
                            </option>

                        </select>

                        <div
                            id="studentLoading"
                            class="small text-primary mt-2 d-none"
                        >

                            <span class="spinner-border spinner-border-sm me-1"></span>

                            Loading students with results...

                        </div>

                        <div
                            id="studentMessage"
                            class="small text-muted mt-2"
                        >
                            Only students with results for the selected period
                            will appear.
                        </div>

                    </div>


                    {{-- SUBMIT --}}
                    <div class="col-lg-4 d-flex align-items-end">

                        <button
                            type="submit"
                            id="generateButton"
                            class="btn btn-primary w-100"
                            disabled
                        >

                            <i class="fas fa-file-alt me-1"></i>

                            View Report Card

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- INFO --}}
    <div class="row g-3 mt-3">

        <div class="col-md-4">

            <div class="info-card">

                <div class="info-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>

                <div>

                    <strong>
                        Current Period
                    </strong>

                    <small>
                        Follows System Settings.
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="info-card">

                <div class="info-icon history">
                    <i class="fas fa-history"></i>
                </div>

                <div>

                    <strong>
                        Historical Results
                    </strong>

                    <small>
                        View results from previous years and terms.
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="info-card">

                <div class="info-icon filter">
                    <i class="fas fa-filter"></i>
                </div>

                <div>

                    <strong>
                        Results Only
                    </strong>

                    <small>
                        Students without results are excluded.
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

.report-card-page {
    width: 100%;
}

.header-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: #e7f1ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
}

.period-banner {
    border: 1px solid #dbe7f5;
    background: #f8fbff;
    border-radius: 10px;
    padding: 14px 16px;
}

.period-options {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.period-option {
    flex: 1;
    min-width: 260px;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 13px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    background: #fff;
    transition: .2s ease;
}

.period-option:hover {
    border-color: #86b7fe;
    background: #f8fbff;
}

.period-option.active {
    border-color: #0d6efd;
    background: #f0f6ff;
}

.period-option input {
    margin: 0;
}

.period-option strong {
    display: block;
    font-size: 13px;
}

.period-option small {
    display: block;
    color: #6c757d;
    margin-top: 3px;
    font-size: 11px;
}

.form-label {
    font-size: 13px;
}

.form-select {
    min-height: 42px;
}

.info-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 14px;
    display: flex;
    gap: 12px;
    align-items: center;
    height: 100%;
}

.info-icon {
    width: 42px;
    height: 42px;
    border-radius: 9px;
    background: #e7f1ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-icon.history {
    background: #eaf8ef;
    color: #198754;
}

.info-icon.filter {
    background: #e8f8fb;
    color: #0dcaf0;
}

.info-card strong {
    display: block;
    font-size: 12px;
}

.info-card small {
    display: block;
    color: #6c757d;
    margin-top: 3px;
    font-size: 10px;
}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const form =
        document.getElementById('reportCardForm');

    const academicYear =
        document.getElementById('academicYear');

    
    const submittedAcademicYearId =
        document.getElementById('submittedAcademicYearId');

    const term =
        document.getElementById('term');

    const studentClass =
        document.getElementById('studentClass');

    const student =
        document.getElementById('student');

    const generateButton =
        document.getElementById('generateButton');

    const studentLoading =
        document.getElementById('studentLoading');

    const termLoading =
        document.getElementById('termLoading');

    const studentMessage =
        document.getElementById('studentMessage');

    const currentPeriodOption =
        document.getElementById('currentPeriodOption');

    const historicalPeriodOption =
        document.getElementById('historicalPeriodOption');


    const currentAcademicYearId =
        @json(
            $currentAcademicYear
                ? $currentAcademicYear->id
                : null
        );


    const currentTermId =
        @json(
            $currentTerm
                ? $currentTerm->id
                : null
        );


    const termsUrlTemplate =
        @json(
            route(
                'settings.academic-years.terms',
                [
                    'academicYear' => '__YEAR__'
                ]
            )
        );


    const studentsUrl =
        @json(
            route(
                'report-cards.get-students-by-class'
            )
        );


    let periodMode = 'current';


    /*
    |--------------------------------------------------------------------------
    | RESET STUDENT
    |--------------------------------------------------------------------------
    */
    function syncSubmittedAcademicYear() {
        submittedAcademicYearId.value = academicYear.value || '';
    }


    function resetStudent()
    {
        student.innerHTML = `
            <option value="">
                Select period and class first
            </option>
        `;

        student.disabled = true;

        generateButton.disabled = true;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD TERMS
    |--------------------------------------------------------------------------
    */

    async function loadTerms(
        yearId,
        preferredTermId = null
    ) {

        term.innerHTML = `
            <option value="">
                Loading terms...
            </option>
        `;

        term.disabled = true;

        resetStudent();


        if (!yearId) {

            term.innerHTML = `
                <option value="">
                    Select Academic Year first
                </option>
            `;

            return;
        }


        termLoading.classList.remove('d-none');


        try {

            const url =
                termsUrlTemplate.replace(
                    '__YEAR__',
                    yearId
                );


            const response =
                await fetch(
                    url,
                    {
                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Unable to load terms.'
                );
            }


            const terms =
                await response.json();


            term.innerHTML = `
                <option value="">
                    Select Term
                </option>
            `;


            terms.forEach(function (item) {

                const option =
                    document.createElement('option');

                option.value =
                    item.id;

                option.textContent =
                    item.name;

                term.appendChild(option);

            });


            /*
            |--------------------------------------------------------------------------
            | ONLY SELECT THE PREFERRED TERM IF IT BELONGS TO THIS YEAR
            |--------------------------------------------------------------------------
            */

            if (preferredTermId) {

                const option =
                    Array.from(
                        term.options
                    ).find(
                        option =>
                            String(option.value)
                            ===
                            String(preferredTermId)
                    );


                if (option) {

                    term.value =
                        preferredTermId;

                }

            }


            term.disabled =
                terms.length === 0;


        } catch (error) {

            console.error(error);

            term.innerHTML = `
                <option value="">
                    Unable to load terms
                </option>
            `;

        } finally {

            termLoading
                .classList
                .add('d-none');

        }

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS WITH RESULTS
    |--------------------------------------------------------------------------
    */

    async function loadStudents()
    {

        resetStudent();


        const yearId =
            academicYear.value;

        const termId =
            term.value;

        const classId =
            studentClass.value;


        if (
            !yearId ||
            !termId ||
            !classId
        ) {

            return;

        }


        studentLoading
            .classList
            .remove('d-none');


        student.innerHTML = `
            <option value="">
                Loading students...
            </option>
        `;


        try {

            const url =
                studentsUrl
                +
                '?academic_year_id='
                +
                encodeURIComponent(yearId)
                +
                '&term_id='
                +
                encodeURIComponent(termId)
                +
                '&student_class_id='
                +
                encodeURIComponent(classId);


            const response =
                await fetch(
                    url,
                    {
                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Unable to load students.'
                );
            }


            const data =
                await response.json();


            student.innerHTML = `
                <option value="">
                    Select Student
                </option>
            `;


            if (
                data.success &&
                Array.isArray(data.students) &&
                data.students.length > 0
            ) {

                data.students.forEach(function (item) {

                    const option =
                        document.createElement('option');

                    option.value =
                        item.id;

                    option.textContent =
                        item.name
                        +
                        (
                            item.student_id
                                ? ' — ' + item.student_id
                                : ''
                        );

                    student.appendChild(option);

                });


                student.disabled = false;


                studentMessage.textContent =
                    data.students.length
                    +
                    ' student'
                    +
                    (
                        data.students.length === 1
                            ? ''
                            : 's'
                    )
                    +
                    ' with results found.';

            } else {

                student.innerHTML = `
                    <option value="">
                        No students with results
                    </option>
                `;

                studentMessage.textContent =
                    'No students have results for the selected academic period.';

            }

        } catch (error) {

            console.error(error);

            student.innerHTML = `
                <option value="">
                    Unable to load students
                </option>
            `;

            studentMessage.textContent =
                'Unable to load students.';

        } finally {

            studentLoading
                .classList
                .add('d-none');

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT PERIOD
    |--------------------------------------------------------------------------
    */

    async function useCurrentPeriod()
    {

        periodMode = 'current';


        currentPeriodOption
            .classList
            .add('active');

        historicalPeriodOption
            .classList
            .remove('active');


        academicYear.value =
            currentAcademicYearId || '';


        
        syncSubmittedAcademicYear();
/*
        |--------------------------------------------------------------------------
        | Keep academic year locked, but TERM MUST REMAIN ENABLED.
        |
        | This is important because disabled form fields are not submitted.
        |--------------------------------------------------------------------------
        */

        academicYear.disabled = true;


        await loadTerms(
            currentAcademicYearId,
            currentTermId
        );


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Do NOT disable term here.
        |
        */

        term.disabled = false;


        if (
            studentClass.value &&
            term.value
        ) {

            await loadStudents();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | HISTORICAL PERIOD
    |--------------------------------------------------------------------------
    */

    async function useHistoricalPeriod()
    {

        periodMode = 'historical';


        currentPeriodOption
            .classList
            .remove('active');

        historicalPeriodOption
            .classList
            .add('active');


        academicYear.disabled = false;


        /*
        |--------------------------------------------------------------------------
        | CLEAR OLD YEAR AND TERM
        |--------------------------------------------------------------------------
        */

        academicYear.value = '';

        
        syncSubmittedAcademicYear();
term.innerHTML = `
            <option value="">
                Select Academic Year first
            </option>
        `;

        term.disabled = true;


        resetStudent();


        studentMessage.textContent =
            'Select an academic year, term and class.';

    }


    /*
    |--------------------------------------------------------------------------
    | MODE SWITCH
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            'input[name="period_mode"]'
        )
        .forEach(function (radio) {

            radio.addEventListener(
                'change',
                async function () {

                    if (
                        this.value === 'current'
                    ) {

                        await useCurrentPeriod();

                    } else {

                        await useHistoricalPeriod();

                    }

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    academicYear.addEventListener(
        'change',
        async function () {
            syncSubmittedAcademicYear();

            /*
            |--------------------------------------------------------------------------
            | CRITICAL:
            | Clear the old term BEFORE loading the new year's terms.
            |--------------------------------------------------------------------------
            */

            term.value = '';

            term.innerHTML = `
                <option value="">
                    Select Term
                </option>
            `;

            resetStudent();


            await loadTerms(
                this.value
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TERM
    |--------------------------------------------------------------------------
    */

    term.addEventListener(
        'change',
        function () {

            resetStudent();

            loadStudents();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLASS
    |--------------------------------------------------------------------------
    */

    studentClass.addEventListener(
        'change',
        function () {

            resetStudent();

            loadStudents();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | STUDENT
    |--------------------------------------------------------------------------
    */

    student.addEventListener(
        'change',
        function () {

            generateButton.disabled =
                !this.value;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | FINAL SUBMISSION SAFETY
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function (event) {

            if (
                !submittedAcademicYearId.value ||
                !term.value ||
                !studentClass.value ||
                !student.value
            ) {

                event.preventDefault();

                alert(
                    'Please select the academic year, term, class and student.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | In Current mode the controller will use System Settings.
            | In Historical mode, the selected term has already been loaded
            | specifically for the selected academic year.
            |--------------------------------------------------------------------------
            */

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE CURRENT PERIOD
    |--------------------------------------------------------------------------
    */

    useCurrentPeriod();

});

</script>

@endsection