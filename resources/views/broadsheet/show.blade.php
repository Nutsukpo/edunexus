@extends('layouts.master')

@section('title', 'Class Broadsheet')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | SELECTED PERIOD
    |--------------------------------------------------------------------------
    */

    $selectedAcademicYearId =
        request('academic_year_id');

    $selectedTermId =
        request('term_id');

    $selectedClassId =
        request('student_class_id');


    /*
    |--------------------------------------------------------------------------
    | LOAD SELECTED PERIOD SAFELY
    |--------------------------------------------------------------------------
    */

    $academicYear =
        $academicYear
        ?? (
            $selectedAcademicYearId
                ? \App\Models\AcademicYear::find($selectedAcademicYearId)
                : null
        );


    $term =
        $term
        ?? (
            $selectedTermId
                ? \App\Models\Term::find($selectedTermId)
                : null
        );


    $studentClass =
        $studentClass
        ?? (
            $selectedClassId
                ? \App\Models\StudentClass::find($selectedClassId)
                : null
        );


    /*
    |--------------------------------------------------------------------------
    | LOAD RESULTS FOR THE SELECTED PERIOD
    |--------------------------------------------------------------------------
    |
    | We deliberately query the results using all three dimensions:
    |
    | Academic Year
    | Term
    | Class
    |
    | This prevents results from another academic period appearing.
    |
    */

    $periodResults =
        \App\Models\StudentResult::query()
            ->where(
                'academic_year_id',
                $selectedAcademicYearId
            )
            ->where(
                'term_id',
                $selectedTermId
            )
            ->where(
                'student_class_id',
                $selectedClassId
            )
            ->with([
                'student',
                'subject'
            ])
            ->get();


    /*
    |--------------------------------------------------------------------------
    | SUBJECTS WITH ACTUAL RESULTS
    |--------------------------------------------------------------------------
    */

    $subjectIds =
        $periodResults
            ->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();


    $subjects =
        \App\Models\Subject::query()
            ->whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | STUDENTS WITH ACTUAL RESULTS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We do NOT get all students assigned to the class.
    |
    | We get only students appearing in StudentResult records for the
    | selected academic year + term + class.
    |
    */

    $resultStudentIds =
        $periodResults
            ->pluck('student_id')
            ->filter()
            ->unique()
            ->values();


    $studentsWithResults =
        \App\Models\Student::query()
            ->whereIn(
                'id',
                $resultStudentIds
            )
            ->get()
            ->sortBy(function ($student) {

                return strtolower(
                    trim(
                        implode(
                            ' ',
                            array_filter([
                                $student->first_name,
                                $student->middle_name,
                                $student->last_name,
                            ])
                        )
                    )
                );

            })
            ->values();


    /*
    |--------------------------------------------------------------------------
    | RESULT LOOKUP
    |--------------------------------------------------------------------------
    */

    $resultLookup =
        $periodResults->keyBy(function ($result) {

            return $result->student_id
                . '_'
                . $result->subject_id;

        });


    /*
    |--------------------------------------------------------------------------
    | STUDENT PERFORMANCE
    |--------------------------------------------------------------------------
    */

    $performance = [];


    foreach ($studentsWithResults as $student) {

        $total = 0;

        $resultCount = 0;


        foreach ($subjects as $subject) {

            $key =
                $student->id
                . '_'
                . $subject->id;


            $result =
                $resultLookup[$key]
                ?? null;


            if ($result) {

                $total +=
                    (float) (
                        $result->total_score
                        ?? 0
                    );

                $resultCount++;

            }

        }


        $average =
            $resultCount > 0
                ? round(
                    $total / $resultCount,
                    2
                )
                : 0;


        $performance[$student->id] = [

            'total' =>
                $total,

            'average' =>
                $average,

            'result_count' =>
                $resultCount,

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL POSITIONS
    |--------------------------------------------------------------------------
    |
    | Position is calculated ONLY among students who have results.
    |
    */

    $sortedPerformance =
        collect($performance)
            ->sortByDesc(function ($data) {

                return $data['average'];

            });


    $positions = [];

    $currentPosition = 0;

    $previousAverage = null;

    $rankCounter = 0;


    foreach (
        $sortedPerformance
        as $studentId => $data
    ) {

        $rankCounter++;


        if (
            $previousAverage === null
            ||
            (float) $data['average']
                !==
            (float) $previousAverage
        ) {

            $currentPosition =
                $rankCounter;

            $previousAverage =
                $data['average'];

        }


        $positions[$studentId] =
            $currentPosition;

    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT POSITIONS
    |--------------------------------------------------------------------------
    |
    | Only students with an actual result in that subject participate.
    |
    */

    $subjectPositions = [];


    foreach ($subjects as $subject) {

        $subjectStudents = [];


        foreach (
            $studentsWithResults
            as $student
        ) {

            $key =
                $student->id
                . '_'
                . $subject->id;


            $result =
                $resultLookup[$key]
                ?? null;


            if ($result) {

                $subjectStudents[$student->id] =
                    (float) (
                        $result->total_score
                        ?? 0
                    );

            }

        }


        arsort($subjectStudents);


        $subjectPosition = 0;

        $subjectCounter = 0;

        $previousScore = null;


        foreach (
            $subjectStudents
            as $studentId => $score
        ) {

            $subjectCounter++;


            if (
                $previousScore === null
                ||
                (float) $score
                    !==
                (float) $previousScore
            ) {

                $subjectPosition =
                    $subjectCounter;

                $previousScore =
                    $score;

            }


            $subjectPositions[
                $subject->id
            ][$studentId] =
                $subjectPosition;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

    $studentCount =
        $studentsWithResults->count();


    $subjectCount =
        $subjects->count();


    $averageCollection =
        collect($performance)
            ->pluck('average');


    $classAverage =
        $averageCollection->count() > 0
            ? $averageCollection->avg()
            : 0;


    $highestAverage =
        $averageCollection->count() > 0
            ? $averageCollection->max()
            : 0;


    $lowestAverage =
        $averageCollection->count() > 0
            ? $averageCollection->min()
            : 0;


    $passCount =
        $averageCollection
            ->filter(function ($average) {

                return $average >= 50;

            })
            ->count();


    $passRate =
        $studentCount > 0
            ? (
                $passCount
                /
                $studentCount
            ) * 100
            : 0;


    $totalClassMarks =
        collect($performance)
            ->sum('total');

@endphp


<div class="container-fluid broadsheet-page">


    {{-- ================================================================
         HEADER
    ================================================================= --}}

    <div class="card shadow-sm border-0 mb-3">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-2">

                        <div class="broadsheet-icon bg-primary text-white">

                            <i class="fas fa-table"></i>

                        </div>

                        <div>

                            <h4 class="fw-bold mb-0">
                                Class Broadsheet
                            </h4>

                            <div class="text-muted small">
                                Academic performance summary
                            </div>

                        </div>

                    </div>


                    <div class="class-information">

                        <span class="info-item">

                            <i class="fas fa-school text-primary me-1"></i>

                            <strong>
                                {{ $studentClass?->name ?? 'N/A' }}
                            </strong>

                        </span>


                        <span class="separator">
                            |
                        </span>


                        <span class="info-item">

                            <i class="fas fa-calendar-alt text-primary me-1"></i>

                            {{ $academicYear?->name ?? 'N/A' }}

                        </span>


                        <span class="separator">
                            |
                        </span>


                        <span class="info-item">

                            <i class="fas fa-clock text-success me-1"></i>

                            {{ $term?->name ?? 'N/A' }}

                        </span>

                    </div>

                </div>


                {{-- ACTIONS --}}

                <div class="d-flex flex-wrap gap-2 no-print">

                    @if($studentCount > 0)

                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            onclick="exportBroadsheetPDF()"
                        >

                            <i class="fas fa-file-pdf me-1"></i>

                            PDF

                        </button>


                        <button
                            type="button"
                            class="btn btn-success btn-sm"
                            onclick="exportBroadsheetExcel()"
                        >

                            <i class="fas fa-file-excel me-1"></i>

                            Excel

                        </button>


                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            onclick="printBroadsheet()"
                        >

                            <i class="fas fa-print me-1"></i>

                            Print

                        </button>

                    @endif


                    <a
                        href="{{ route('broadsheet.index') }}"
                        class="btn btn-outline-secondary btn-sm"
                    >

                        <i class="fas fa-arrow-left me-1"></i>

                        Back

                    </a>

                </div>

            </div>

        </div>

    </div>



    {{-- ================================================================
         PERIOD INFORMATION
    ================================================================= --}}

    <div class="alert alert-light border shadow-sm mb-3">

        <div class="d-flex align-items-center flex-wrap gap-2">

            <i class="fas fa-calendar-check text-primary"></i>

            <span class="text-muted">
                Selected Academic Period:
            </span>


            <span class="badge bg-primary">

                {{ $academicYear?->name ?? 'N/A' }}

            </span>


            <span class="badge bg-success">

                {{ $term?->name ?? 'N/A' }}

            </span>


            <span class="text-muted">

                —

                {{ $studentClass?->name ?? 'N/A' }}

            </span>

        </div>


        @if(
            $term
            &&
            $term->start_date
            &&
            $term->end_date
        )

            <div class="small text-muted mt-2">

                <i class="fas fa-clock me-1"></i>

                Term period:

                {{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}

                —

                {{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}

            </div>

        @endif

    </div>



    {{-- ================================================================
         RESULTS-ONLY NOTICE
    ================================================================= --}}

    @if($studentCount > 0)

        <div class="alert alert-info border shadow-sm mb-3">

            <div class="d-flex align-items-start gap-2">

                <i class="fas fa-info-circle mt-1"></i>

                <div>

                    <strong>
                        Results-Based Broadsheet
                    </strong>

                    <div class="small mt-1">

                        This broadsheet contains only students who have
                        at least one result recorded for the selected
                        academic year, term and class.

                    </div>

                </div>

            </div>

        </div>

    @endif



    {{-- ================================================================
         STATISTICS
    ================================================================= --}}

    @if($studentCount > 0)

        <div class="row g-3 mb-3 no-print">


            {{-- STUDENTS --}}

            <div class="col-xl-3 col-md-6">

                <div class="card shadow-sm border-0 stat-card h-100">

                    <div class="card-body">

                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">

                            <i class="fas fa-users"></i>

                        </div>

                        <div>

                            <div class="stat-label">
                                Students With Results
                            </div>

                            <div class="stat-value">

                                {{ number_format(
                                    $studentCount
                                ) }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- SUBJECTS --}}

            <div class="col-xl-3 col-md-6">

                <div class="card shadow-sm border-0 stat-card h-100">

                    <div class="card-body">

                        <div class="stat-icon bg-success bg-opacity-10 text-success">

                            <i class="fas fa-book"></i>

                        </div>

                        <div>

                            <div class="stat-label">
                                Subjects With Results
                            </div>

                            <div class="stat-value">

                                {{ number_format(
                                    $subjectCount
                                ) }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- CLASS AVERAGE --}}

            <div class="col-xl-3 col-md-6">

                <div class="card shadow-sm border-0 stat-card h-100">

                    <div class="card-body">

                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">

                            <i class="fas fa-chart-line"></i>

                        </div>

                        <div>

                            <div class="stat-label">
                                Class Average
                            </div>

                            <div class="stat-value">

                                {{ number_format(
                                    $classAverage,
                                    2
                                ) }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PASS RATE --}}

            <div class="col-xl-3 col-md-6">

                <div class="card shadow-sm border-0 stat-card h-100">

                    <div class="card-body">

                        <div class="stat-icon bg-info bg-opacity-10 text-info">

                            <i class="fas fa-graduation-cap"></i>

                        </div>

                        <div>

                            <div class="stat-label">
                                Pass Rate
                            </div>

                            <div class="stat-value">

                                {{ number_format(
                                    $passRate,
                                    1
                                ) }}%

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- ============================================================
             SUMMARY BAR
        ============================================================= --}}

        <div class="card shadow-sm border-0 mb-3 no-print">

            <div class="card-body py-2">

                <div class="row g-2">


                    <div class="col-md-3">

                        <div class="summary-item">

                            <span>

                                <i class="fas fa-arrow-up text-success me-1"></i>

                                Highest Average

                            </span>

                            <strong>

                                {{ number_format(
                                    $highestAverage,
                                    2
                                ) }}

                            </strong>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="summary-item">

                            <span>

                                <i class="fas fa-arrow-down text-danger me-1"></i>

                                Lowest Average

                            </span>

                            <strong>

                                {{ number_format(
                                    $lowestAverage,
                                    2
                                ) }}

                            </strong>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="summary-item">

                            <span>

                                <i class="fas fa-check-circle text-success me-1"></i>

                                Students Passed

                            </span>

                            <strong>
                                {{ $passCount }}
                            </strong>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="summary-item">

                            <span>

                                <i class="fas fa-star text-warning me-1"></i>

                                Total Marks

                            </span>

                            <strong>

                                {{ number_format(
                                    $totalClassMarks,
                                    2
                                ) }}

                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    @endif



    {{-- ================================================================
         BROADSHEET
    ================================================================= --}}

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <h6 class="fw-bold mb-1">

                        <i class="fas fa-list-ol text-primary me-2"></i>

                        Student Performance

                    </h6>


                    <small class="text-muted">

                        {{ $studentCount }}

                        students with results

                        •

                        {{ $subjectCount }}

                        subjects

                    </small>

                </div>


                @if($studentCount > 0)

                    <div class="no-print">

                        <div class="input-group input-group-sm">

                            <span class="input-group-text">

                                <i class="fas fa-search"></i>

                            </span>


                            <input
                                type="text"
                                id="broadsheetSearch"
                                class="form-control"
                                placeholder="Search student..."
                            >

                        </div>

                    </div>

                @endif

            </div>

        </div>


        <div class="card-body p-0">


            @if(
                $studentCount > 0
                &&
                $subjectCount > 0
            )


                <div class="table-responsive broadsheet-table-wrapper">

                    <table
                        class="table table-bordered table-hover align-middle mb-0"
                        id="broadsheet-table"
                    >


                        {{-- HEADER --}}

                        <thead class="table-primary">

                            <tr>

                                <th class="text-center sticky-column sticky-number">
                                    #
                                </th>


                                <th class="sticky-column sticky-student">
                                    Student
                                </th>


                                @foreach($subjects as $subject)

                                    <th
                                        class="text-center subject-column"
                                        title="{{ $subject->name }}"
                                    >

                                        <div class="subject-name">

                                            {{ $subject->name }}

                                        </div>

                                        <small>
                                            /100
                                        </small>

                                    </th>

                                @endforeach


                                <th class="text-center summary-column">
                                    Total
                                </th>


                                <th class="text-center summary-column">
                                    Average
                                </th>


                                <th class="text-center summary-column">
                                    Position
                                </th>

                            </tr>

                        </thead>



                        {{-- BODY --}}

                        <tbody id="broadsheet-tbody">

                            @foreach(
                                $studentsWithResults
                                as $student
                            )

                                @php

                                    $studentPerformance =
                                        $performance[
                                            $student->id
                                        ]
                                        ?? [
                                            'total' => 0,
                                            'average' => 0,
                                            'result_count' => 0,
                                        ];


                                    $studentPosition =
                                        $positions[
                                            $student->id
                                        ]
                                        ?? null;


                                    $studentName =
                                        trim(
                                            implode(
                                                ' ',
                                                array_filter([
                                                    $student->first_name,
                                                    $student->middle_name,
                                                    $student->last_name,
                                                ])
                                            )
                                        );


                                    $average =
                                        (float) (
                                            $studentPerformance[
                                                'average'
                                            ]
                                            ?? 0
                                        );

                                @endphp


                                <tr
                                    class="student-result-row"
                                    data-name="{{ strtolower($studentName) }}"
                                    data-id="{{ strtolower($student->student_id ?? '') }}"
                                    data-position="{{ $studentPosition ?? 999 }}"
                                    data-average="{{ $average }}"
                                >


                                    {{-- NUMBER --}}

                                    <td class="text-center sticky-column sticky-number">

                                        {{ $loop->iteration }}

                                    </td>



                                    {{-- STUDENT --}}

                                    <td class="sticky-column sticky-student">

                                        <div class="fw-semibold">

                                            {{ $studentName }}

                                        </div>


                                        @if($student->student_id)

                                            <small class="text-muted">

                                                {{ $student->student_id }}

                                            </small>

                                        @endif

                                    </td>



                                    {{-- SUBJECTS --}}

                                    @foreach($subjects as $subject)

                                        @php

                                            $resultKey =
                                                $student->id
                                                . '_'
                                                . $subject->id;


                                            $result =
                                                $resultLookup[
                                                    $resultKey
                                                ]
                                                ?? null;


                                            $score =
                                                $result
                                                    ? (float) (
                                                        $result->total_score
                                                        ?? 0
                                                    )
                                                    : null;


                                            $grade =
                                                $result?->grade;


                                            $remark =
                                                $result?->remark;


                                            $subjectPosition =
                                                $subjectPositions[
                                                    $subject->id
                                                ][$student->id]
                                                ?? null;

                                        @endphp


                                        <td
                                            class="text-center subject-score-cell"
                                            title="{{ $result ? ($remark ?? '') : 'No result recorded' }}"
                                        >

                                            @if($result)

                                                <div class="score-value">

                                                    {{ number_format(
                                                        $score,
                                                        2
                                                    ) }}

                                                </div>


                                                <div class="score-meta">

                                                    @if($grade)

                                                        <span class="badge bg-primary-subtle text-primary">

                                                            {{ $grade }}

                                                        </span>

                                                    @endif


                                                    @if($subjectPosition)

                                                        <small class="text-muted ms-1">

                                                            #{{ $subjectPosition }}

                                                        </small>

                                                    @endif

                                                </div>

                                            @else

                                                <span class="text-muted">
                                                    —
                                                </span>

                                            @endif

                                        </td>

                                    @endforeach



                                    {{-- TOTAL --}}

                                    <td class="text-center fw-bold">

                                        {{ number_format(
                                            $studentPerformance['total'] ?? 0,
                                            2
                                        ) }}

                                    </td>



                                    {{-- AVERAGE --}}

                                    <td class="text-center">

                                        <span
                                            class="badge average-badge
                                            @if($average >= 80)
                                                bg-success
                                            @elseif($average >= 70)
                                                bg-primary
                                            @elseif($average >= 50)
                                                bg-warning text-dark
                                            @else
                                                bg-danger
                                            @endif"
                                        >

                                            {{ number_format(
                                                $average,
                                                2
                                            ) }}

                                        </span>

                                    </td>



                                    {{-- POSITION --}}

                                    <td class="text-center">

                                        @if($studentPosition == 1)

                                            <span class="position-badge first">
                                                🏆 1st
                                            </span>

                                        @elseif($studentPosition == 2)

                                            <span class="position-badge second">
                                                🥈 2nd
                                            </span>

                                        @elseif($studentPosition == 3)

                                            <span class="position-badge third">
                                                🥉 3rd
                                            </span>

                                        @elseif($studentPosition)

                                            @php

                                                $positionSuffix =
                                                    in_array(
                                                        $studentPosition % 100,
                                                        [11, 12, 13]
                                                    )
                                                    ? 'th'
                                                    : match(
                                                        $studentPosition % 10
                                                    ) {
                                                        1 => 'st',
                                                        2 => 'nd',
                                                        3 => 'rd',
                                                        default => 'th'
                                                    };

                                            @endphp


                                            <span class="position-badge">

                                                {{ $studentPosition }}{{ $positionSuffix }}

                                            </span>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>



                        {{-- =================================================
                             CLASS SUMMARY
                        ================================================== --}}

                        <tfoot class="table-light">

                            <tr>

                                <th
                                    colspan="2"
                                    class="text-end"
                                >

                                    Class Summary

                                </th>


                                @foreach($subjects as $subject)

                                    @php

                                        $subjectTotal = 0;

                                        $subjectResultCount = 0;


                                        foreach(
                                            $studentsWithResults
                                            as $student
                                        ) {

                                            $key =
                                                $student->id
                                                . '_'
                                                . $subject->id;


                                            $result =
                                                $resultLookup[
                                                    $key
                                                ]
                                                ?? null;


                                            if($result) {

                                                $subjectTotal +=
                                                    (float) (
                                                        $result->total_score
                                                        ?? 0
                                                    );

                                                $subjectResultCount++;

                                            }

                                        }


                                        $subjectAverage =
                                            $subjectResultCount > 0
                                                ? (
                                                    $subjectTotal
                                                    /
                                                    $subjectResultCount
                                                )
                                                : 0;

                                    @endphp


                                    <th class="text-center">

                                        <div class="small fw-bold">

                                            {{ number_format(
                                                $subjectAverage,
                                                1
                                            ) }}

                                        </div>


                                        <small class="text-muted">
                                            Avg
                                        </small>

                                    </th>

                                @endforeach


                                <th class="text-center">

                                    {{ number_format(
                                        $totalClassMarks,
                                        2
                                    ) }}

                                </th>


                                <th class="text-center">

                                    {{ number_format(
                                        $classAverage,
                                        2
                                    ) }}

                                </th>


                                <th class="text-center">
                                    —
                                </th>

                            </tr>

                        </tfoot>

                    </table>

                </div>


            @else


                {{-- =========================================================
                     NO RESULTS
                ========================================================== --}}

                <div class="text-center py-5">

                    <div class="empty-icon mb-3">

                        <i class="fas fa-chart-bar"></i>

                    </div>


                    <h6 class="fw-bold">
                        No Student Results
                    </h6>


                    <p class="text-muted mb-3">

                        No results have been recorded for

                        <strong>
                            {{ $studentClass?->name ?? 'this class' }}
                        </strong>

                        in

                        <strong>
                            {{ $academicYear?->name ?? 'this academic year' }}
                        </strong>

                        —

                        <strong>
                            {{ $term?->name ?? 'this term' }}
                        </strong>.

                    </p>


                    <a
                        href="{{ route('scores.index') }}"
                        class="btn btn-primary btn-sm"
                    >

                        <i class="fas fa-edit me-1"></i>

                        Enter Scores

                    </a>

                </div>

            @endif

        </div>

    </div>



    {{-- ================================================================
         LEGEND
    ================================================================= --}}

    @if($studentCount > 0)

        <div class="card shadow-sm border-0 mt-3 no-print">

            <div class="card-body py-2">

                <div class="d-flex align-items-center flex-wrap gap-3">

                    <strong class="small">

                        <i class="fas fa-info-circle text-primary me-1"></i>

                        Legend

                    </strong>


                    <span class="small text-muted">
                        Subject score
                    </span>


                    <span class="small text-muted">

                        <span class="badge bg-primary-subtle text-primary">
                            Grade
                        </span>

                        Grade

                    </span>


                    <span class="small text-muted">
                        # Position
                    </span>


                    <span class="small text-muted">
                        — No result recorded
                    </span>

                </div>


                <div class="small text-muted mt-2">

                    <i class="fas fa-filter me-1"></i>

                    Only students with results for the selected
                    academic year, term and class are displayed.

                </div>

            </div>

        </div>

    @endif

</div>



<style>

/* ================================================================
   PAGE
================================================================ */

.broadsheet-page {
    width: 100%;
}


/* ================================================================
   HEADER
================================================================ */

.broadsheet-icon {

    width: 42px;
    height: 42px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

}


.class-information {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 8px;

    margin-top: 10px;

    font-size: 13px;

}


.info-item {
    color: #495057;
}


.separator {
    color: #adb5bd;
}


/* ================================================================
   STATISTICS
================================================================ */

.stat-card {
    border-radius: 10px;
}


.stat-card .card-body {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px;

}


.stat-icon {

    width: 42px;

    height: 42px;

    border-radius: 9px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 17px;

}


.stat-label {

    font-size: 10px;

    color: #6c757d;

    text-transform: uppercase;

    font-weight: 600;

    margin-bottom: 2px;

}


.stat-value {

    font-size: 20px;

    font-weight: 700;

}


/* ================================================================
   SUMMARY
================================================================ */

.summary-item {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 5px 10px;

    font-size: 12px;

}


.summary-item span {
    color: #6c757d;
}


.summary-item strong {
    font-size: 13px;
}


/* ================================================================
   TABLE
================================================================ */

.broadsheet-table-wrapper {

    max-height: 650px;

    overflow: auto;

}


#broadsheet-table {

    min-width: max-content;

}


#broadsheet-table th,
#broadsheet-table td {

    font-size: 11px;

    white-space: nowrap;

    vertical-align: middle;

}


#broadsheet-table thead th {

    position: sticky;

    top: 0;

    z-index: 20;

    background: #cfe2ff;

}


#broadsheet-table tfoot th {

    position: sticky;

    bottom: 0;

    z-index: 15;

    background: #f1f3f5;

}


/* ================================================================
   STICKY COLUMNS
================================================================ */

.sticky-column {

    position: sticky !important;

    background: #fff !important;

    z-index: 10 !important;

}


#broadsheet-table thead .sticky-column {

    background: #cfe2ff !important;

    z-index: 30 !important;

}


#broadsheet-table tfoot .sticky-column {

    background: #f1f3f5 !important;

    z-index: 25 !important;

}


.sticky-number {

    left: 0;

    width: 45px;

    min-width: 45px;

    text-align: center;

}


.sticky-student {

    left: 45px;

    width: 210px;

    min-width: 210px;

}


/* ================================================================
   SUBJECT COLUMNS
================================================================ */

.subject-column {

    min-width: 95px;

    max-width: 120px;

    text-align: center;

}


.subject-name {

    max-width: 105px;

    overflow: hidden;

    text-overflow: ellipsis;

}


.subject-column small {

    font-size: 8px;

    color: #6c757d;

}


.subject-score-cell {

    min-width: 95px;

}


.score-value {

    font-weight: 700;

    font-size: 11px;

}


.score-meta {

    margin-top: 2px;

    font-size: 8px;

}


.score-meta .badge {

    font-size: 8px;

}


/* ================================================================
   SUMMARY COLUMNS
================================================================ */

.summary-column {

    min-width: 80px;

    background: #f8f9fa;

}


.average-badge {

    min-width: 55px;

    font-size: 10px;

}


/* ================================================================
   POSITION BADGES
================================================================ */

.position-badge {

    display: inline-block;

    min-width: 48px;

    padding: 4px 6px;

    border-radius: 5px;

    background: #f1f3f5;

    color: #495057;

    font-size: 10px;

    font-weight: 600;

}


.position-badge.first {

    background: #fff3cd;

    color: #856404;

}


.position-badge.second {

    background: #e2e3e5;

    color: #495057;

}


.position-badge.third {

    background: #f8d7da;

    color: #842029;

}


/* ================================================================
   EMPTY STATE
================================================================ */

.empty-icon {

    width: 70px;

    height: 70px;

    border-radius: 50%;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f1f3f5;

    color: #6c757d;

    font-size: 28px;

}


/* ================================================================
   SCROLLBAR
================================================================ */

.broadsheet-table-wrapper::-webkit-scrollbar {

    width: 7px;

    height: 7px;

}


.broadsheet-table-wrapper::-webkit-scrollbar-track {

    background: #f1f1f1;

}


.broadsheet-table-wrapper::-webkit-scrollbar-thumb {

    background: #adb5bd;

    border-radius: 5px;

}


/* ================================================================
   MOBILE
================================================================ */

@media (max-width: 768px) {

    .class-information {

        font-size: 11px;

    }


    .sticky-student {

        width: 170px;

        min-width: 170px;

    }


    .subject-column,
    .subject-score-cell {

        min-width: 75px;

    }


    #broadsheet-table th,
    #broadsheet-table td {

        font-size: 9px;

    }

}


/* ================================================================
   PRINT
================================================================ */

@media print {

    body {

        background: #fff !important;

    }


    .no-print {

        display: none !important;

    }


    .container-fluid {

        width: 100% !important;

        max-width: none !important;

        padding: 0 !important;

    }


    .card {

        border: none !important;

        box-shadow: none !important;

    }


    .broadsheet-table-wrapper {

        max-height: none !important;

        overflow: visible !important;

    }


    #broadsheet-table {

        width: 100% !important;

        font-size: 7px !important;

    }


    #broadsheet-table th,
    #broadsheet-table td {

        font-size: 7px !important;

        padding: 2px !important;

    }


    #broadsheet-table thead th,
    #broadsheet-table tfoot th,
    .sticky-column {

        position: static !important;

    }


    .broadsheet-icon {

        display: none;

    }

}

</style>



<script>

/*
|--------------------------------------------------------------------------
| STUDENT SEARCH
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const search =
            document.getElementById(
                'broadsheetSearch'
            );


        if (!search) {
            return;
        }


        search.addEventListener(
            'input',
            function () {

                const value =
                    this.value
                        .trim()
                        .toLowerCase();


                document
                    .querySelectorAll(
                        '#broadsheet-table tbody .student-result-row'
                    )
                    .forEach(function (row) {

                        const name =
                            row.dataset.name
                            || '';


                        const id =
                            row.dataset.id
                            || '';


                        row.style.display =
                            !value
                            ||
                            name.includes(value)
                            ||
                            id.includes(value)
                                ? ''
                                : 'none';

                    });

            }
        );

    }
);


/*
|--------------------------------------------------------------------------
| PRINT
|--------------------------------------------------------------------------
*/

function printBroadsheet()
{
    window.print();
}


/*
|--------------------------------------------------------------------------
| PDF
|--------------------------------------------------------------------------
*/

function exportBroadsheetPDF()
{

    const element =
        document.getElementById(
            'broadsheet-table'
        );


    if (!element) {

        alert(
            'There is no broadsheet available to export.'
        );

        return;

    }


    if (
        typeof html2pdf ===
        'undefined'
    ) {

        alert(
            'PDF export library is not available.'
        );

        return;

    }


    html2pdf()

        .set({

            margin: 0.25,

            filename:
                'Broadsheet-{{ $studentClass?->name ?? 'Class' }}-{{ $academicYear?->name ?? 'Academic-Year' }}-{{ $term?->name ?? 'Term' }}.pdf',

            image: {

                type: 'jpeg',

                quality: 0.98

            },

            html2canvas: {

                scale: 2,

                useCORS: true

            },

            jsPDF: {

                unit: 'in',

                format: 'a3',

                orientation: 'landscape'

            }

        })

        .from(element)

        .save();

}


/*
|--------------------------------------------------------------------------
| EXCEL
|--------------------------------------------------------------------------
*/

function exportBroadsheetExcel()
{

    const table =
        document.getElementById(
            'broadsheet-table'
        );


    if (!table) {

        alert(
            'There is no broadsheet available to export.'
        );

        return;

    }


    if (
        typeof XLSX ===
        'undefined'
    ) {

        alert(
            'Excel export library is not available.'
        );

        return;

    }


    const workbook =
        XLSX.utils.book_new();


    const worksheet =
        XLSX.utils.table_to_sheet(
            table,
            {
                raw: true
            }
        );


    XLSX.utils.book_append_sheet(
        workbook,
        worksheet,
        'Broadsheet'
    );


    XLSX.writeFile(

        workbook,

        'Broadsheet-{{ $studentClass?->name ?? 'Class' }}-{{ $academicYear?->name ?? 'Academic-Year' }}-{{ $term?->name ?? 'Term' }}.xlsx'

    );

}

</script>



{{-- ================================================================
     EXPORT LIBRARIES
================================================================ --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@endsection