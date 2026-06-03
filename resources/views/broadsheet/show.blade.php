@extends('layouts.master')

@section('title', 'Class Broadsheet')

@section('content')

@php
    $academicYear = \App\Models\AcademicYear::find($request->academic_year_id);
    $term = \App\Models\Term::find($request->term_id);
    $class = \App\Models\StudentClass::find($request->student_class_id);

    $studentCount = count($students);
    $subjectCount = count($subjects);

    $classAverage = collect($rankings)->avg('average');

    $highestAverage = collect($rankings)->max('average');

    $lowestAverage = collect($rankings)->min('average');

    $passCount = collect($rankings)
                    ->filter(fn($item) => ($item['average'] ?? 0) >= 50)
                    ->count();

    $passRate = $studentCount > 0
                ? round(($passCount / $studentCount) * 100, 1)
                : 0;
@endphp

<div class="container-fluid">

    <!-- SCHOOL HEADER -->
    <div class="card shadow-sm border-0 mb-1">

        <div class="card-body">

            <div class="row align-items-center">

                <!-- School Logo -->
                <div class="col-md-2 text-center">

                    <img src="{{ asset('img/images.jpeg') }}"
                         alt="School Logo"
                         style="max-height:100px;"
                         class="img-fluid">

                </div>

                <!-- School Info -->
                <div class="col-md-8 text-center">

                    <h2 class="fw-bold text-danger mb-1">
                        <!-- {{ config('app.name', 'EDUNEXUS SCHOOL') }} -->
                        KABORE SCHOOL COMPLEX
                    </h2>

                    <h5 class="mb-1">
                        CLASS RESULTS
                    </h5>

                    <p class="mb-0">
                        Academic Year:
                        <strong>{{ $academicYear->name ?? 'N/A' }}</strong>
                    </p>

                    <p class="mb-0">
                        Term:
                        <strong>{{ $term->name ?? 'N/A' }}</strong>
                    </p>

                    <p class="mb-0">
                        Class:
                        <strong>{{ $class->name ?? 'N/A' }}</strong>
                    </p>

                </div>

                <!-- Export Buttons -->
                <div class="col-md-2 text-end no-print">

                    <div class="d-grid gap-2">
                        <button
                            onclick="exportToPDF()"
                            class="btn btn-white text-danger">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>

                        <button
                            onclick="exportToExcel()"
                            class="btn btn-white text-success">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card shadow-sm mb-1">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-2">

                    <input
                        type="text"
                        id="studentSearch"
                        class="form-control"
                        placeholder="Search Student Name or ID">

                </div>

                <div class="col-md-3 mb-2">

                    <select
                        id="positionFilter"
                        class="form-control">

                        <option value="">
                            All Positions
                        </option>

                        <option value="top3">
                            Top 3
                        </option>

                        <option value="top10">
                            Top 10
                        </option>

                        <option value="others">
                            Others
                        </option>

                    </select>

                </div>

                <div class="col-md-3 mb-2">

                    <select
                        id="performanceFilter"
                        class="form-control">

                        <option value="">
                            All Performance
                        </option>

                        <option value="excellent">
                            Excellent (80+)
                        </option>

                        <option value="good">
                            Good (70-79)
                        </option>

                        <option value="average">
                            Average (50-69)
                        </option>

                        <option value="poor">
                            Poor (<50)
                        </option>

                    </select>

                </div>

                <div class="col-md-2">

                    <button
                        class="btn btn-outline-secondary w-100"
                        onclick="resetFilters()">

                        Reset

                    </button>

                </div>

            </div>

        </div>

    </div>
    <!-- BROADSHEET TABLE -->
<div class="card shadow mb-4">

<div class="card-body p-0">

    <div class="table-responsive">

        <table
            class="table table-bordered table-hover mb-0"
            id="broadsheet-table">

            <thead class="table-primary">

                <tr>

                    <th class="text-center">#</th>

                    <th style="min-width:220px;">
                        Student Name
                    </th>

                    @foreach($subjects as $subject)

                        <th class="text-center"
                            style="min-width:90px;">

                            {{ $subject->name }}

                        </th>

                    @endforeach

                    <th class="text-center bg-light">
                        Total
                    </th>

                    <th class="text-center bg-light">
                        Average
                    </th>

                    <th class="text-center bg-light">
                        Position
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($students as $index => $student)

                    @php

                        $studentRank =
                            $positions[$student->id] ?? '-';

                        $averageScore =
                            $rankings[$student->id]['average'] ?? 0;

                        $totalScore =
                            $rankings[$student->id]['total'] ?? 0;

                        $studentName =
                            $student->full_name ??
                            $student->name ??
                            trim(
                                ($student->first_name ?? '') .
                                ' ' .
                                ($student->last_name ?? '')
                            );

                    @endphp

                    <tr

                        data-name="{{ strtolower($studentName) }}"

                        data-id="{{ strtolower($student->student_id) }}"

                        data-position="{{ $studentRank }}"

                        data-average="{{ $averageScore }}"

                    >

                        <!-- STUDENT ID -->

                        <td class="text-center align-middle">

                            <span class="fw-bold p-2">

                                {{ $student->student_id }}

                            </span>

                        </td>

                        <!-- STUDENT NAME -->

                        <td class="align-middle">

                            <div class="fw-bold p-2">

                                {{ $studentName }}

                            </div>

                        </td>

                        <!-- SUBJECT SCORES -->

                        @foreach($subjects as $subject)

                            @php

                                $key =
                                    $student->id .
                                    '_' .
                                    $subject->id;

                                $mark =
                                    $results[$key]->total_score ?? 0;

                                $grade =
                                    $results[$key]->grade ?? '';

                            @endphp

                            <td class="text-center align-middle">

                                <div>

                                    @if($mark >= 80)

                                        <span
                                            class="badge bg-white text-dark p-2">

                                            {{ $mark }}

                                        </span>

                                    @elseif($mark >= 70)

                                        <span
                                            class="badge bg-white text-dark  p-2">

                                            {{ $mark }}

                                        </span>

                                    @elseif($mark >= 50)

                                        <span
                                            class="badge bg-white text-dark  p-2">

                                            {{ $mark }}

                                        </span>

                                    @else

                                        <span
                                            class="badge bg-white text-dark  p-2">

                                            {{ $mark }}

                                        </span>

                                    @endif

                                </div>

                                @if($grade)

                                    <small
                                        class="d-block text-muted mt-1">

                                        {{ $grade }}

                                    </small>

                                @endif

                            </td>

                        @endforeach

                        <!-- TOTAL -->

                        <td class="text-center align-middle">

                            <span
                                class="fw-bold bg-white text-dark ">

                                {{ number_format($totalScore) }}

                            </span>

                        </td>

                        <!-- AVERAGE -->

                        <td class="text-center align-middle">

                            @if($averageScore >= 80)

                                <span
                                    class="badge bg-white text-dark  p-2">

                                    {{ number_format($averageScore,1) }}

                                </span>

                            @elseif($averageScore >= 70)

                                <span
                                    class="badge bg-white text-dark  p-2">

                                    {{ number_format($averageScore,1) }}

                                </span>

                            @elseif($averageScore >= 50)

                                <span
                                    class="badge bg-white text-dark   p-2">

                                    {{ number_format($averageScore,1) }}

                                </span>

                            @else

                                <span
                                    class="badge bg-white text-dark  p-2">

                                    {{ number_format($averageScore,1) }}

                                </span>

                            @endif

                        </td>

                        <!-- POSITION -->

                        <td class="text-center align-middle">

                            @if($studentRank == 1)

                                <span
                                    class="badge bg-warning text-dark p-2">

                                    🏆 1st

                                </span>

                            @elseif($studentRank == 2)

                                <span
                                    class="badge bg-secondary p-2">

                                    🥈 2nd

                                </span>

                            @elseif($studentRank == 3)

                                <span
                                    class="badge bg-danger p-2">

                                    🥉 3rd

                                </span>

                            @else

                                <span
                                    class="badge bg-light text-dark border p-2">

                                    {{ $studentRank }}

                                    @if($studentRank != '-')

                                        {{
                                            $studentRank == 1
                                            ? 'st'
                                            : (
                                                $studentRank == 2
                                                ? 'nd'
                                                : (
                                                    $studentRank == 3
                                                    ? 'rd'
                                                    : 'th'
                                                )
                                            )
                                        }}
                                    @endif

                                </span>

                            @endif

                        </td>

                    </tr>

                @endforeach

            </tbody>

            <tfoot>

                <tr class="table-light fw-bold">

                    <td colspan="{{ count($subjects) + 5 }}">

                        Total Students:
                        {{ $studentCount }}

                        |
                        Subjects:
                        {{ $subjectCount }}

                        |
                        Class Average:
                        {{ number_format($classAverage,1) }}

                        |
                        Pass Rate:
                        {{ $passRate }}%

                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</div>

</div>


</div>

@endsection

@push('scripts')

<!-- PDF Export -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- Excel Export -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- SweetAlert -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    initializeFilters();
    initializeSorting();

});

/* ==============================
   SEARCH + FILTERS
================================= */

function initializeFilters() {

    const searchInput =
        document.getElementById('studentSearch');

    const positionFilter =
        document.getElementById('positionFilter');

    const performanceFilter =
        document.getElementById('performanceFilter');

    if(searchInput)
        searchInput.addEventListener('keyup', filterRows);

    if(positionFilter)
        positionFilter.addEventListener('change', filterRows);

    if(performanceFilter)
        performanceFilter.addEventListener('change', filterRows);
}

function filterRows() {

    const search =
        document.getElementById('studentSearch')
        ?.value
        .toLowerCase() || '';

    const position =
        document.getElementById('positionFilter')
        ?.value || '';

    const performance =
        document.getElementById('performanceFilter')
        ?.value || '';

    const rows =
        document.querySelectorAll(
            '#broadsheet-table tbody tr'
        );

    rows.forEach(row => {

        let visible = true;

        const name =
            row.dataset.name || '';

        const id =
            row.dataset.id || '';

        const rank =
            parseInt(row.dataset.position) || 999;

        const average =
            parseFloat(row.dataset.average) || 0;

        /* SEARCH */

        if(
            search &&
            !name.includes(search) &&
            !id.includes(search)
        ){
            visible = false;
        }

        /* POSITION FILTER */

        if(position === 'top3' && rank > 3)
            visible = false;

        if(position === 'top10' && rank > 10)
            visible = false;

        if(position === 'others' && rank <= 10)
            visible = false;

        /* PERFORMANCE FILTER */

        if(
            performance === 'excellent' &&
            average < 80
        ){
            visible = false;
        }

        if(
            performance === 'good' &&
            (average < 70 || average >= 80)
        ){
            visible = false;
        }

        if(
            performance === 'average' &&
            (average < 50 || average >= 70)
        ){
            visible = false;
        }

        if(
            performance === 'poor' &&
            average >= 50
        ){
            visible = false;
        }

        row.style.display =
            visible ? '' : 'none';

    });

}

/* ==============================
   RESET FILTERS
================================= */

function resetFilters() {

    document.getElementById('studentSearch').value = '';

    document.getElementById('positionFilter').value = '';

    document.getElementById('performanceFilter').value = '';

    filterRows();

}

/* ==============================
   TABLE SORTING
================================= */

function initializeSorting() {

    const headers =
        document.querySelectorAll(
            '#broadsheet-table thead th'
        );

    headers.forEach((header,index) => {

        header.style.cursor = 'pointer';

        header.title =
            'Click to sort';

        header.addEventListener('click', () => {

            sortTable(index);

        });

    });

}

function sortTable(columnIndex) {

    const table =
        document.getElementById(
            'broadsheet-table'
        );

    const tbody =
        table.querySelector('tbody');

    const rows =
        Array.from(
            tbody.querySelectorAll('tr')
        );

    const currentDirection =
        table.dataset.sortDirection || 'asc';

    const direction =
        currentDirection === 'asc'
        ? 'desc'
        : 'asc';

    rows.sort((a,b) => {

        let aVal =
            a.cells[columnIndex]
            ?.innerText
            .trim() || '';

        let bVal =
            b.cells[columnIndex]
            ?.innerText
            .trim() || '';

        let aNum = parseFloat(aVal);
        let bNum = parseFloat(bVal);

        if(
            !isNaN(aNum) &&
            !isNaN(bNum)
        ){

            return direction === 'asc'
                ? aNum - bNum
                : bNum - aNum;

        }

        return direction === 'asc'
            ? aVal.localeCompare(bVal)
            : bVal.localeCompare(aVal);

    });

    rows.forEach(row => {

        tbody.appendChild(row);

    });

    table.dataset.sortDirection =
        direction;
}

/* ==============================
   PRINT
================================= */

function printBroadsheet() {

    window.print();

}

/* ==============================
   PDF EXPORT
================================= */

function exportToPDF() {

    const element =
        document.querySelector(
            '#broadsheet-table'
        );

    Swal.fire({

        title: 'Generating PDF...',
        text: 'Please wait',
        allowOutsideClick: false,

        didOpen: () => {

            Swal.showLoading();

        }

    });

    const options = {

        margin: 0.3,

        filename:
            'Class_Broadsheet_{{ date("Ymd_His") }}.pdf',

        image: {

            type: 'jpeg',
            quality: 0.98

        },

        html2canvas: {

            scale: 2

        },

        jsPDF: {

            unit: 'in',
            format: 'a3',
            orientation: 'landscape'

        }

    };

    html2pdf()
        .set(options)
        .from(element)
        .save()
        .then(() => {

            Swal.fire({

                icon: 'success',
                title: 'PDF Downloaded'

            });

        });

}

/* ==============================
   EXCEL EXPORT
================================= */

function exportToExcel() {

    try {

        Swal.fire({

            title: 'Exporting...',
            text: 'Preparing Excel file',

            allowOutsideClick: false,

            didOpen: () => {

                Swal.showLoading();

            }

        });

        const table =
            document.getElementById(
                'broadsheet-table'
            );

        const workbook =
            XLSX.utils.book_new();

        const worksheet =
            XLSX.utils.table_to_sheet(
                table
            );

        worksheet['!cols'] = [];

        for(
            let i = 0;
            i < table.rows[0].cells.length;
            i++
        ){

            worksheet['!cols'].push({

                wch: 18

            });

        }

        XLSX.utils.book_append_sheet(

            workbook,
            worksheet,
            'Broadsheet'

        );

        XLSX.writeFile(

            workbook,

            'Class_Broadsheet_{{ date("Ymd_His") }}.xlsx'

        );

        Swal.fire({

            icon: 'success',

            title: 'Excel Downloaded'

        });

    } catch(error) {

        Swal.fire({

            icon: 'error',

            title: 'Export Failed',

            text: error.message

        });

    }

}

/* ==============================
   KEYBOARD SHORTCUTS
================================= */

document.addEventListener('keydown', function(e){

    if(e.ctrlKey && e.key === 'p'){

        e.preventDefault();

        printBroadsheet();

    }

});

</script>

@endpush
