@extends('layouts.master')

@section('title', 'Broadsheet')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        {{-- ============================================================
             HEADER
        ============================================================= --}}

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <h5 class="mb-1 fw-bold">

                        <i class="fas fa-table me-2"></i>

                        Generate Broadsheet

                    </h5>

                    <small class="opacity-75">

                        Generate class academic performance reports
                        for the current or a previous academic period.

                    </small>

                </div>

                @if($academicYear && $term)

                    <div class="text-end">

                        <div class="small opacity-75">
                            Current System Period
                        </div>

                        <strong>

                            {{ $academicYear->name }}

                            <span class="mx-1">
                                •
                            </span>

                            {{ $term->name }}

                        </strong>

                    </div>

                @endif

            </div>

        </div>


        <div class="card-body">

            @if(!$academicYear || !$term)

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-triangle me-2"></i>

                    <strong>
                        Academic period not configured.
                    </strong>

                    Please configure the current Academic Year and Term
                    under System Settings.

                </div>

            @else

                {{-- ====================================================
                     CURRENT SYSTEM PERIOD
                ===================================================== --}}

                <div class="alert alert-light border mb-4">

                    <div class="d-flex align-items-center flex-wrap gap-2">

                        <i class="fas fa-calendar-check text-primary"></i>

                        <span class="text-muted">
                            Current system period:
                        </span>

                        <span class="badge bg-primary">

                            {{ $academicYear->name }}

                        </span>

                        <span class="badge bg-success">

                            {{ $term->name }}

                        </span>

                    </div>

                    <div class="small text-muted mt-2">

                        <i class="fas fa-info-circle me-1"></i>

                        This is the period configured in System Settings.
                        Selecting a historical period below will only affect
                        this report and will not change System Settings.

                    </div>

                </div>


                {{-- ====================================================
                     REPORT PERIOD
                ===================================================== --}}

                <div class="mb-4">

                    <label class="form-label fw-bold">

                        <i class="fas fa-calendar-alt text-primary me-1"></i>

                        Report Period

                    </label>


                    <div class="period-mode-container">

                        {{-- CURRENT --}}

                        <label
                            class="period-mode-card active"
                            id="currentPeriodCard"
                        >

                            <input
                                type="radio"
                                name="period_mode_radio"
                                value="current"
                                checked
                            >

                            <div class="period-mode-content">

                                <div class="period-mode-icon bg-primary-subtle text-primary">

                                    <i class="fas fa-calendar-check"></i>

                                </div>

                                <div>

                                    <div class="fw-bold">
                                        Current Period
                                    </div>

                                    <small class="text-muted">

                                        {{ $academicYear->name }}
                                        •
                                        {{ $term->name }}

                                    </small>

                                </div>

                            </div>

                        </label>


                        {{-- HISTORICAL --}}

                        <label
                            class="period-mode-card"
                            id="historicalPeriodCard"
                        >

                            <input
                                type="radio"
                                name="period_mode_radio"
                                value="historical"
                            >

                            <div class="period-mode-content">

                                <div class="period-mode-icon bg-warning-subtle text-warning">

                                    <i class="fas fa-history"></i>

                                </div>

                                <div>

                                    <div class="fw-bold">
                                        Historical Period
                                    </div>

                                    <small class="text-muted">

                                        View previous academic results

                                    </small>

                                </div>

                            </div>

                        </label>

                    </div>

                </div>


                {{-- ====================================================
                     FORM
                ===================================================== --}}

                <form
                    method="POST"
                    action="{{ route('broadsheet.generate') }}"
                    id="broadsheetForm"
                >

                    @csrf


                    <input
                        type="hidden"
                        name="period_mode"
                        id="period_mode"
                        value="current"
                    >


                    <div class="row g-3">


                        {{-- =================================================
                             ACADEMIC YEAR
                        ================================================== --}}

                        <div class="col-md-4">

                            <label
                                for="academic_year_id"
                                class="form-label fw-semibold"
                            >

                                Academic Year

                            </label>


                            <div id="currentAcademicYearDisplay">

                                <div class="form-control bg-light">

                                    <i class="fas fa-calendar-alt text-primary me-2"></i>

                                    {{ $academicYear->name }}

                                </div>

                            </div>


                            <div
                                id="historicalAcademicYearSelect"
                                class="d-none"
                            >

                                <select
                                    name="academic_year_id"
                                    id="academic_year_id"
                                    class="form-select"
                                >

                                    @foreach($academicYears as $year)

                                        <option
                                            value="{{ $year->id }}"
                                            {{ (int) $year->id === (int) $academicYear->id ? 'selected' : '' }}
                                        >

                                            {{ $year->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>


                        {{-- =================================================
                             TERM
                        ================================================== --}}

                        <div class="col-md-4">

                            <label
                                for="term_id"
                                class="form-label fw-semibold"
                            >

                                Term

                            </label>


                            <div id="currentTermDisplay">

                                <div class="form-control bg-light">

                                    <i class="fas fa-clock text-success me-2"></i>

                                    {{ $term->name }}

                                </div>

                            </div>


                            <div
                                id="historicalTermSelect"
                                class="d-none"
                            >

                                <select
                                    name="term_id"
                                    id="term_id"
                                    class="form-select"
                                >

                                    @foreach($terms as $availableTerm)

                                        <option
                                            value="{{ $availableTerm->id }}"
                                            {{ (int) $availableTerm->id === (int) $term->id ? 'selected' : '' }}
                                        >

                                            {{ $availableTerm->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>


                        {{-- =================================================
                             CLASS
                        ================================================== --}}

                        <div class="col-md-4">

                            <label
                                for="student_class_id"
                                class="form-label fw-semibold"
                            >

                                Class

                            </label>

                            <select
                                name="student_class_id"
                                id="student_class_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Class
                                </option>

                                @foreach($classes as $class)

                                    <option
                                        value="{{ $class->id }}"
                                    >

                                        {{ $class->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- ====================================================
                         HISTORICAL NOTICE
                    ===================================================== --}}

                    <div
                        id="historicalNotice"
                        class="alert alert-warning border mt-4 d-none"
                    >

                        <div class="d-flex align-items-start gap-2">

                            <i class="fas fa-history mt-1"></i>

                            <div>

                                <strong>
                                    Historical Reporting
                                </strong>

                                <div class="small mt-1">

                                    You are viewing a previous academic period.
                                    This selection only affects the broadsheet
                                    report and does not change the school's
                                    current academic period.

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ====================================================
                         CURRENT NOTICE
                    ===================================================== --}}

                    <div
                        id="currentNotice"
                        class="alert alert-primary border mt-4"
                    >

                        <div class="d-flex align-items-start gap-2">

                            <i class="fas fa-calendar-check mt-1"></i>

                            <div>

                                <strong>
                                    Current Academic Period
                                </strong>

                                <div class="small mt-1">

                                    This broadsheet will use the academic
                                    period configured in System Settings:

                                    <strong>
                                        {{ $academicYear->name }}
                                        —
                                        {{ $term->name }}
                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ====================================================
                         SUBMIT
                    ===================================================== --}}

                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="generateButton"
                        >

                            <i class="fas fa-chart-bar me-2"></i>

                            Generate Class Results

                        </button>

                    </div>

                </form>

            @endif

        </div>

    </div>

</div>


<style>

.period-mode-container {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 15px;

    max-width: 850px;
}

.period-mode-card {
    display: block;

    border: 1px solid #dee2e6;

    border-radius: 10px;

    padding: 14px;

    cursor: pointer;

    background: #fff;

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

.period-mode-card:hover {
    border-color: #86b7fe;

    box-shadow:
        0 3px 10px rgba(0, 0, 0, .06);
}

.period-mode-card.active {
    border-color: #0d6efd;

    background: #f8fbff;

    box-shadow:
        0 3px 12px rgba(13, 110, 253, .10);
}

.period-mode-card input {
    display: none;
}

.period-mode-content {
    display: flex;

    align-items: center;

    gap: 12px;
}

.period-mode-icon {
    width: 42px;

    height: 42px;

    flex-shrink: 0;

    border-radius: 9px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 17px;
}

.form-control.bg-light {
    min-height: 38px;

    display: flex;

    align-items: center;
}

@media (max-width: 768px) {

    .period-mode-container {
        grid-template-columns: 1fr;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const currentCard =
            document.getElementById(
                'currentPeriodCard'
            );

        const historicalCard =
            document.getElementById(
                'historicalPeriodCard'
            );

        const periodMode =
            document.getElementById(
                'period_mode'
            );

        const currentYearDisplay =
            document.getElementById(
                'currentAcademicYearDisplay'
            );

        const historicalYearSelect =
            document.getElementById(
                'historicalAcademicYearSelect'
            );

        const currentTermDisplay =
            document.getElementById(
                'currentTermDisplay'
            );

        const historicalTermSelect =
            document.getElementById(
                'historicalTermSelect'
            );

        const academicYearSelect =
            document.getElementById(
                'academic_year_id'
            );

        const termSelect =
            document.getElementById(
                'term_id'
            );

        const historicalNotice =
            document.getElementById(
                'historicalNotice'
            );

        const currentNotice =
            document.getElementById(
                'currentNotice'
            );

        /*
        |--------------------------------------------------------------------------
        | LOAD TERMS
        |--------------------------------------------------------------------------
        */

        function loadHistoricalTerms(
            academicYearId,
            selectedTermId = null
        ) {

            termSelect.innerHTML =
                '<option value="">Loading terms...</option>';

            if (!academicYearId) {

                termSelect.innerHTML =
                    '<option value="">Select Term</option>';

                return;
            }

            const url =
                "{{ url('/settings/academic-years') }}"
                + "/"
                + academicYearId
                + "/terms";

            fetch(
                url,
                {
                    headers: {
                        'Accept':
                            'application/json'
                    }
                }
            )
            .then(
                response => {

                    if (!response.ok) {
                        throw new Error(
                            'Unable to load terms.'
                        );
                    }

                    return response.json();
                }
            )
            .then(
                terms => {

                    termSelect.innerHTML =
                        '<option value="">Select Term</option>';

                    terms.forEach(
                        term => {

                            const option =
                                document.createElement(
                                    'option'
                                );

                            option.value =
                                term.id;

                            option.textContent =
                                term.name;

                            if (
                                selectedTermId
                                &&
                                Number(term.id) ===
                                Number(selectedTermId)
                            ) {

                                option.selected =
                                    true;
                            }

                            termSelect.appendChild(
                                option
                            );
                        }
                    );
                }
            )
            .catch(
                () => {

                    termSelect.innerHTML =
                        '<option value="">Unable to load terms</option>';

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT PERIOD
        |--------------------------------------------------------------------------
        */

        function activateCurrentPeriod() {

            periodMode.value =
                'current';

            currentCard.classList.add(
                'active'
            );

            historicalCard.classList.remove(
                'active'
            );

            currentYearDisplay.classList.remove(
                'd-none'
            );

            historicalYearSelect.classList.add(
                'd-none'
            );

            currentTermDisplay.classList.remove(
                'd-none'
            );

            historicalTermSelect.classList.add(
                'd-none'
            );

            historicalNotice.classList.add(
                'd-none'
            );

            currentNotice.classList.remove(
                'd-none'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | HISTORICAL PERIOD
        |--------------------------------------------------------------------------
        */

        function activateHistoricalPeriod() {

            periodMode.value =
                'historical';

            currentCard.classList.remove(
                'active'
            );

            historicalCard.classList.add(
                'active'
            );

            currentYearDisplay.classList.add(
                'd-none'
            );

            historicalYearSelect.classList.remove(
                'd-none'
            );

            currentTermDisplay.classList.add(
                'd-none'
            );

            historicalTermSelect.classList.remove(
                'd-none'
            );

            historicalNotice.classList.remove(
                'd-none'
            );

            currentNotice.classList.add(
                'd-none'
            );

            loadHistoricalTerms(
                academicYearSelect.value,
                termSelect.value
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CARD CLICKS
        |--------------------------------------------------------------------------
        */

        currentCard.addEventListener(
            'click',
            activateCurrentPeriod
        );

        historicalCard.addEventListener(
            'click',
            activateHistoricalPeriod
        );


        /*
        |--------------------------------------------------------------------------
        | YEAR CHANGE
        |--------------------------------------------------------------------------
        */

        academicYearSelect.addEventListener(
            'change',
            function () {

                if (
                    periodMode.value !==
                    'historical'
                ) {
                    return;
                }

                loadHistoricalTerms(
                    this.value
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'broadsheetForm'
            )
            .addEventListener(
                'submit',
                function (event) {

                    if (
                        periodMode.value ===
                        'historical'
                    ) {

                        if (
                            !academicYearSelect.value
                        ) {

                            event.preventDefault();

                            alert(
                                'Please select an academic year.'
                            );

                            return;
                        }

                        if (
                            !termSelect.value
                        ) {

                            event.preventDefault();

                            alert(
                                'Please select a term.'
                            );

                            return;
                        }
                    }

                }
            );

    }
);

</script>

@endsection