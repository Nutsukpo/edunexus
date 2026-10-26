@extends('layouts.master')

@section('title', 'Student Report Card')

@section('content')

<style>

.report-card-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px;
}

.report-card {
    background: #fff;
    border: 1px solid #dfe5ec;
    border-radius: 10px;
    overflow: hidden;
}

.school-header {
    background: #1e3c72;
    color: #fff;
    text-align: center;
    padding: 20px;
}

.school-logo {
    height: 55px;
    margin-bottom: 8px;
}

.school-header h2 {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
}

.school-header p {
    font-size: 12px;
    margin: 4px 0 0;
}

.report-title {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 14px;
    border-radius: 20px;
    background: rgba(255,255,255,.15);
    font-size: 11px;
    font-weight: 600;
}

.student-info {
    margin: 15px;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.info-label {
    display: block;
    color: #6c757d;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
}

.info-value {
    display: block;
    margin-top: 3px;
    color: #1f2937;
    font-size: 12px;
    font-weight: 700;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin: 0 15px 15px;
}

.stat-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    background: #fff;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #1e3c72;
}

.stat-label {
    font-size: 9px;
    color: #6c757d;
    text-transform: uppercase;
}

.score-table-wrapper {
    overflow-x: auto;
    margin: 0 15px 15px;
}

.score-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}

.score-table th {
    background: #1e3c72;
    color: #fff;
    padding: 7px;
    border: 1px solid #43597a;
    text-align: center;
    white-space: nowrap;
}

.score-table td {
    padding: 6px;
    border: 1px solid #dfe5ec;
    text-align: center;
}

.score-table .subject {
    text-align: left;
    font-weight: 600;
}

.grade-badge {
    display: inline-block;
    min-width: 28px;
    padding: 2px 7px;
    border-radius: 12px;
    font-weight: 700;
    background: #eef2f7;
}

.remarks-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin: 0 15px 15px;
}

.remarks-card {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #4299e1;
}

.remarks-card.head {
    border-left-color: #48bb78;
}

.remarks-title {
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 7px;
}

.remarks-text {
    min-height: 45px;
    font-size: 11px;
    font-style: italic;
    line-height: 1.5;
}

.remarks-footer {
    border-top: 1px solid #dee2e6;
    padding-top: 6px;
    margin-top: 6px;
    font-size: 9px;
    color: #6c757d;
}

.report-footer {
    padding: 10px;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px solid #e2e8f0;
    font-size: 9px;
    color: #6c757d;
}

.actions {
    max-width: 1100px;
    margin: 20px auto;
    text-align: center;
}

@media (max-width: 768px) {

    .info-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .remarks-grid {
        grid-template-columns: 1fr;
    }

}

@media print {

    body {
        background: #fff !important;
    }

    .no-print {
        display: none !important;
    }

    .report-card-wrapper {
        max-width: none;
        padding: 0;
        margin: 0;
    }

    .report-card {
        border: none;
        box-shadow: none;
    }

    .score-table-wrapper {
        overflow: visible;
    }

    .score-table {
        font-size: 9px;
    }

    .score-table th,
    .score-table td {
        padding: 4px;
    }

    @page {
        size: A4 portrait;
        margin: 10mm;
    }

}

</style>


<div class="report-card-wrapper">

    <div class="report-card" id="reportCard">


        {{-- ========================================================
            SCHOOL HEADER
        ========================================================= --}}

        <div class="school-header">

            @if(file_exists(public_path('img/ESSTECH_BANNER.png')))

                <img
                    src="{{ asset('img/ESSTECH_BANNER.png') }}"
                    alt="School Logo"
                    class="school-logo"
                >

            @endif


            <h2>
                {{ $schoolName ?? 'ees-Tech INTERNATIONAL ACADEMY' }}
            </h2>


            <p>
                {{ $schoolMotto ?? 'Excellence in Education' }}
            </p>


            <div class="report-title">
                TERMINAL REPORT CARD
            </div>

        </div>


        {{-- ========================================================
            STUDENT INFORMATION
        ========================================================= --}}

        <div class="student-info">

            <div class="info-grid">


                <div>

                    <span class="info-label">
                        Student Name
                    </span>

                    <span class="info-value">

                        {{ $student->first_name ?? '' }}

                        @if(!empty($student->middle_name))
                            {{ $student->middle_name }}
                        @endif

                        {{ $student->last_name ?? '' }}

                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Admission No.
                    </span>

                    <span class="info-value">
                        {{ $student->student_id ?? 'N/A' }}
                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Class
                    </span>

                    <span class="info-value">
                        {{ $class->name ?? 'N/A' }}
                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Academic Year
                    </span>

                    <span class="info-value">
                        {{ $academicYear->name ?? 'N/A' }}
                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Term
                    </span>

                    <span class="info-value">
                        {{ $term->name ?? 'N/A' }}
                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Class Position
                    </span>

                    <span class="info-value">

                        @if($classPosition)

                            @php

                                $position =
                                    (int) $classPosition;

                                if (
                                    $position % 100 >= 11 &&
                                    $position % 100 <= 13
                                ) {
                                    $suffix = 'th';
                                } else {

                                    $suffix = match(
                                        $position % 10
                                    ) {
                                        1 => 'st',
                                        2 => 'nd',
                                        3 => 'rd',
                                        default => 'th',
                                    };

                                }

                            @endphp

                            {{ $position }}{{ $suffix }}

                            / {{ $totalStudents ?? 0 }}

                        @else

                            N/A

                        @endif

                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Overall Average
                    </span>

                    <span class="info-value">

                        {{ number_format((float) $average, 1) }}%

                    </span>

                </div>


                <div>

                    <span class="info-label">
                        Subjects Taken
                    </span>

                    <span class="info-value">
                        {{ $subjectCount ?? $results->count() }}
                    </span>

                </div>

            </div>

        </div>


        {{-- ========================================================
            RESULTS
        ========================================================= --}}

        <div class="score-table-wrapper">

            <table class="score-table">

                <thead>

                    <tr>

                        <th width="30">
                            #
                        </th>

                        <th>
                            Subject
                        </th>

                        <th width="60">
                            Class
                        </th>

                        <th width="60">
                            Exam
                        </th>

                        <th width="60">
                            Total
                        </th>

                        <th width="55">
                            Grade
                        </th>

                        <th>
                            Remarks
                        </th>

                        <th width="60">
                            Position
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($results as $index => $result)

                        @php

                            $score =
                                (float) ($result->total_score ?? 0);


                            if ($score >= 80) {

                                $grade = '1';
                                $remark = 'Excellent';

                            } elseif ($score >= 75) {

                                $grade = '2';
                                $remark = 'Very Good';

                            } elseif ($score >= 70) {

                                $grade = '3';
                                $remark = 'Good';

                            } elseif ($score >= 60) {

                                $grade = '4';
                                $remark = 'Credit';

                            } elseif ($score >= 55) {

                                $grade = '5';
                                $remark = 'Pass';

                            } elseif ($score >= 50) {

                                $grade = '6';
                                $remark = 'Pass';

                            } else {

                                $grade = '9';
                                $remark = 'Needs Improvement';

                            }


                            $displayRemark =
                                $result->remark
                                ??
                                $remark;

                        @endphp


                        <tr>

                            <td>
                                {{ $index + 1 }}
                            </td>


                            <td class="subject">

                                {{ $result->subject->name ?? 'N/A' }}

                            </td>


                            <td>
                                {{ number_format((float) ($result->class_score ?? 0), 1) }}
                            </td>


                            <td>
                                {{ number_format((float) ($result->exam_score ?? 0), 1) }}
                            </td>


                            <td>
                                <strong>
                                    {{ number_format($score, 1) }}
                                </strong>
                            </td>


                            <td>

                                <span class="grade-badge">
                                    {{ $grade }}
                                </span>

                            </td>


                            <td style="text-align:left">

                                {{ $displayRemark }}

                            </td>


                            <td>

                                {{ $result->subject_position ?? '—' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                style="padding:30px"
                            >

                                No results found for this student
                                in the selected academic period.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ========================================================
            REMARKS
        ========================================================= --}}

        <div class="remarks-grid">


            <div class="remarks-card">

                <div class="remarks-title">

                    <i class="fas fa-comment-dots me-1"></i>

                    Class Teacher's Remarks

                </div>


                <div class="remarks-text">

                    "{{ $teacherRemarks ?? 'Good performance. Keep it up!' }}"

                </div>


                <div class="remarks-footer">

                    Teacher:

                    {{ $classTeacher->first_name ?? '________________' }}

                    {{ $classTeacher->last_name ?? '' }}

                </div>

            </div>


            <div class="remarks-card head">

                <div class="remarks-title">

                    <i class="fas fa-user-tie me-1"></i>

                    Headmaster's Remarks

                </div>


                <div class="remarks-text">

                    "{{ $headRemarks ?? 'Keep working hard.' }}"

                </div>


                <div class="remarks-footer">

                    @php

                        $termName =
                            strtolower(
                                $term->name ?? ''
                            );

                        $isThirdTerm =
                            str_contains(
                                $termName,
                                'third'
                            )
                            ||
                            str_contains(
                                $termName,
                                '3rd'
                            );

                    @endphp


                    @if($isThirdTerm)

                        @if($average >= 60)

                            <strong>
                                ✓ Promoted
                            </strong>

                        @elseif($average >= 50)

                            <strong>
                                ⚠ Conditional
                            </strong>

                        @else

                            <strong>
                                ✗ Repeated
                            </strong>

                        @endif

                    @else

                        <strong>
                            In Progress
                        </strong>

                    @endif

                </div>

            </div>

        </div>


        {{-- FOOTER --}}
        <div class="report-footer">

            Generated on
            {{ now()->format('d/m/Y h:i A') }}

        </div>

    </div>

</div>


{{-- ================================================================
    ACTIONS
================================================================= --}}

<div class="actions no-print">

    <button
        type="button"
        onclick="window.print()"
        class="btn btn-primary btn-sm"
    >

        <i class="fas fa-print me-1"></i>

        Print

    </button>


    <button
        type="button"
        onclick="exportToPDF()"
        class="btn btn-danger btn-sm"
    >

        <i class="fas fa-file-pdf me-1"></i>

        PDF

    </button>


    <a
        href="{{ route('report-cards.index') }}"
        class="btn btn-secondary btn-sm"
    >

        <i class="fas fa-arrow-left me-1"></i>

        Back

    </a>

</div>


@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>

function exportToPDF()
{

    const element =
        document.getElementById(
            'reportCard'
        );


    const studentId =
        @json($student->student_id ?? 'student');


    const options = {

        margin: 0.25,

        filename:
            'Report_Card_'
            +
            studentId
            +
            '_'
            +
            @json($academicYear->name ?? 'period')
            +
            '.pdf',

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
            format: 'a4',
            orientation: 'portrait'
        }

    };


    html2pdf()
        .set(options)
        .from(element)
        .save();

}

</script>

@endpush

@endsection