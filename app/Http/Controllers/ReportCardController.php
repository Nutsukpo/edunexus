<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentResult;
use App\Models\SystemSetting;
use App\Models\Term;
use App\Services\EdunexusAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportCardController extends Controller
{
    public function __construct(
        private EdunexusAuthorizationService $authorization
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        abort_unless(
            auth()->user()->can('report-cards.view'),
            403
        );

        $settings = SystemSetting::instance();

        $academicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();

        $classes = $this->authorization
            ->accessibleClasses(auth()->user())
            ->orderBy('name')
            ->get();

        return view('report_cards.index', [
            'settings' => $settings,
            'academicYears' => $academicYears,
            'classes' => $classes,
            'currentAcademicYear' => $settings->currentAcademicYear,
            'currentTerm' => $settings->currentTerm,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GET TERMS FOR ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    public function getTermsByAcademicYear(AcademicYear $academicYear)
    {
        abort_unless(
            auth()->user()->can('report-cards.view'),
            403
        );

        return response()->json(
            Term::query()
                ->where('academic_year_id', $academicYear->id)
                ->orderBy('start_date')
                ->get([
                    'id',
                    'academic_year_id',
                    'name',
                    'start_date',
                    'end_date',
                ])
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS WITH RESULTS
    |--------------------------------------------------------------------------
    */

    public function getStudentsByClass(Request $request)
    {
        abort_unless(
            auth()->user()->can('report-cards.view'),
            403
        );

        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],
        ]);

        $academicYearId = (int) $validated['academic_year_id'];
        $termId = (int) $validated['term_id'];
        $classId = (int) $validated['student_class_id'];

        /*
        |--------------------------------------------------------------------------
        | TERM MUST BELONG TO SELECTED YEAR
        |--------------------------------------------------------------------------
        */

        $termBelongsToYear = Term::query()
            ->whereKey($termId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        abort_unless(
            $termBelongsToYear,
            422,
            'The selected term does not belong to the selected academic year.'
        );

        /*
        |--------------------------------------------------------------------------
        | CLASS ACCESS
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $this->authorization
                ->accessibleClasses(auth()->user())
                ->whereKey($classId)
                ->exists(),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | ONLY STUDENTS WITH RESULTS
        |--------------------------------------------------------------------------
        */

        $students = Student::query()
            ->whereHas('results', function ($query) use (
                $academicYearId,
                $termId,
                $classId
            ) {
                $query
                    ->where('academic_year_id', $academicYearId)
                    ->where('term_id', $termId)
                    ->where('student_class_id', $classId);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get([
                'id',
                'student_id',
                'first_name',
                'middle_name',
                'last_name',
            ]);

        return response()->json([
            'success' => true,

            'students' => $students->map(
                function ($student) {
                    return [
                        'id' => $student->id,

                        'student_id' =>
                            $student->student_id,

                        'name' => trim(
                            implode(' ', array_filter([
                                $student->first_name,
                                $student->middle_name,
                                $student->last_name,
                            ]))
                        ),
                    ];
                }
            )->values(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIVE REPORT CARD REQUEST
    |--------------------------------------------------------------------------
    */

    public function show(Request $request)
    {
        abort_unless(
            auth()->user()->can('report-cards.view'),
            403
        );

        $validated = $request->validate([
            'period_mode' => [
                'nullable',
                'in:current,historical',
            ],

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
        ]);

        $settings = SystemSetting::instance();

        $periodMode =
            $validated['period_mode'] ?? 'current';

        /*
        |--------------------------------------------------------------------------
        | CURRENT PERIOD
        |--------------------------------------------------------------------------
        |
        | NEVER trust the submitted academic year/term when the user chooses
        | Current Period.
        |
        | System Settings are the source of truth.
        |
        */

        if ($periodMode === 'current') {

            abort_unless(
                $settings->current_academic_year_id
                &&
                $settings->current_term_id,
                422,
                'The current academic period has not been configured.'
            );

            $academicYearId =
                (int) $settings->current_academic_year_id;

            $termId =
                (int) $settings->current_term_id;

        } else {

            /*
            |--------------------------------------------------------------------------
            | HISTORICAL PERIOD
            |--------------------------------------------------------------------------
            |
            | Historical selections are allowed, but the term must belong
            | to the selected academic year.
            |
            */

            $academicYearId =
                (int) $validated['academic_year_id'];

            $termId =
                (int) $validated['term_id'];
        }

        $classId =
            (int) $validated['student_class_id'];

        $studentId =
            (int) $validated['student_id'];


        /*
        |--------------------------------------------------------------------------
        | VERIFY TERM/YEAR COMBINATION
        |--------------------------------------------------------------------------
        */

        $term = Term::query()
            ->whereKey($termId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if (!$term) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected term does not belong to the selected academic year.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY CLASS ACCESS
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $this->authorization
                ->accessibleClasses(auth()->user())
                ->whereKey($classId)
                ->exists(),
            403
        );


        /*
        |--------------------------------------------------------------------------
        | VERIFY STUDENT HAS RESULTS
        |--------------------------------------------------------------------------
        */

        $hasResults = StudentResult::query()
            ->where('student_id', $studentId)
            ->where('student_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->exists();

        if (!$hasResults) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No results have been recorded for this student for the selected academic period.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | OPEN REPORT CARD
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'report-cards.show-single',
            [
                'student' => $studentId,

                'academic_year_id' =>
                    $academicYearId,

                'term_id' =>
                    $termId,

                'student_class_id' =>
                    $classId,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SINGLE REPORT CARD
    |--------------------------------------------------------------------------
    */

    public function reportCard(
        Request $request,
        Student $student
    ) {
        abort_unless(
            auth()->user()->can('report-cards.view'),
            403
        );

        $settings = SystemSetting::instance();


        /*
        |--------------------------------------------------------------------------
        | PERIOD
        |--------------------------------------------------------------------------
        */

        $academicYearId = $request->filled(
            'academic_year_id'
        )
            ? (int) $request->academic_year_id
            : (int) $settings->current_academic_year_id;


        $termId = $request->filled(
            'term_id'
        )
            ? (int) $request->term_id
            : (int) $settings->current_term_id;


        /*
        |--------------------------------------------------------------------------
        | LOAD PERIOD
        |--------------------------------------------------------------------------
        */

        $academicYear = AcademicYear::findOrFail(
            $academicYearId
        );

        $term = Term::query()
            ->whereKey($termId)
            ->where('academic_year_id', $academicYearId)
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | DETERMINE CLASS FROM REQUEST OR RESULTS
        |--------------------------------------------------------------------------
        */

        $classId = $request->filled(
            'student_class_id'
        )
            ? (int) $request->student_class_id
            : null;


        if (!$classId) {

            $classId = StudentResult::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('term_id', $termId)
                ->orderByDesc('id')
                ->value('student_class_id');
        }


        if (!$classId) {

            abort(
                404,
                'No results found for this student in the selected academic period.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY CLASS ACCESS
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $this->authorization
                ->accessibleClasses(auth()->user())
                ->whereKey($classId)
                ->exists(),
            403
        );


        /*
        |--------------------------------------------------------------------------
        | CLASS
        |--------------------------------------------------------------------------
        */

        $studentClass = StudentClass::with(
            'classTeacher'
        )->findOrFail($classId);


        /*
        |--------------------------------------------------------------------------
        | RESULTS
        |--------------------------------------------------------------------------
        */

        $results = StudentResult::query()
            ->with([
                'student',
                'subject',
                'studentClass',
                'academicYear',
                'term',
            ])
            ->where('student_id', $student->id)
            ->where('student_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->orderBy('subject_id')
            ->get();


        if ($results->isEmpty()) {

            abort(
                404,
                'No results found for this student in the selected academic period.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ALL CLASS RESULTS
        |--------------------------------------------------------------------------
        */

        $allClassResults = StudentResult::query()
            ->where('student_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->get([
                'student_id',
                'subject_id',
                'total_score',
            ]);


        /*
        |--------------------------------------------------------------------------
        | SUBJECT POSITIONS
        |--------------------------------------------------------------------------
        */

        $subjectPositions = [];

        foreach (
            $allClassResults
                ->groupBy('subject_id')
            as $subjectId => $subjectResults
        ) {

            $ordered = $subjectResults
                ->sortByDesc('total_score')
                ->values();

            $position = 0;
            $counter = 0;
            $previousScore = null;

            foreach ($ordered as $row) {

                $counter++;

                if (
                    $previousScore === null
                    ||
                    (float) $row->total_score
                    !==
                    (float) $previousScore
                ) {

                    $position = $counter;

                    $previousScore =
                        $row->total_score;
                }

                $subjectPositions[
                    $subjectId
                ][
                    $row->student_id
                ] = $position;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ATTACH SUBJECT POSITIONS
        |--------------------------------------------------------------------------
        */

        foreach ($results as $result) {

            $result->subject_position =
                $subjectPositions[
                    $result->subject_id
                ][$result->student_id]
                ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | OVERALL STUDENT AVERAGES
        |--------------------------------------------------------------------------
        */

        $studentAverages = $allClassResults
            ->groupBy('student_id')
            ->map(function ($rows) {

                return round(
                    $rows->avg('total_score'),
                    2
                );
            })
            ->sortDesc();


        /*
        |--------------------------------------------------------------------------
        | OVERALL POSITIONS
        |--------------------------------------------------------------------------
        */

        $overallPositions = [];

        $counter = 0;
        $position = 0;
        $previousAverage = null;

        foreach (
            $studentAverages as $studentId => $studentAverage
        ) {

            $counter++;

            if (
                $previousAverage === null
                ||
                (float) $studentAverage
                !==
                (float) $previousAverage
            ) {

                $position = $counter;

                $previousAverage =
                    $studentAverage;
            }

            $overallPositions[$studentId] =
                $position;
        }


        $overallPosition =
            $overallPositions[$student->id]
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | TOTAL STUDENTS
        |--------------------------------------------------------------------------
        */

        $totalStudents =
            $studentAverages->count();


        /*
        |--------------------------------------------------------------------------
        | TOTAL SCORE / AVERAGE
        |--------------------------------------------------------------------------
        */

        $subjectCount =
            $results->count();

        $totalScore =
            (float) $results->sum('total_score');

        $average =
            $subjectCount > 0
                ? round(
                    $totalScore / $subjectCount,
                    2
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $attendanceRows = Attendance::query()
            ->where(
                'student_id',
                $student->id
            )
            ->whereHas(
                'attendanceSession',
                function ($query) use (
                    $classId,
                    $academicYearId,
                    $termId
                ) {

                    $query
                        ->where(
                            'student_class_id',
                            $classId
                        )
                        ->where(
                            'academic_year_id',
                            $academicYearId
                        )
                        ->where(
                            'term_id',
                            $termId
                        );
                }
            )
            ->get();


        $attendanceSummary = [

            'present' =>
                $attendanceRows
                    ->where('status', 'present')
                    ->count(),

            'late' =>
                $attendanceRows
                    ->where('status', 'late')
                    ->count(),

            'absent' =>
                $attendanceRows
                    ->where('status', 'absent')
                    ->count(),

            'excused' =>
                $attendanceRows
                    ->where('status', 'excused')
                    ->count(),

            'total' =>
                $attendanceRows->count(),
        ];


        $attendanceRate =
            $attendanceSummary['total'] > 0
                ? round(
                    (
                        (
                            $attendanceSummary['present']
                            +
                            $attendanceSummary['late']
                        )
                        /
                        $attendanceSummary['total']
                    ) * 100,
                    1
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | CLASS TEACHER
        |--------------------------------------------------------------------------
        */

        $classTeacher =
            $studentClass->classTeacher
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | REMARKS
        |--------------------------------------------------------------------------
        */

        $teacherRemarks =
            'Good performance. Keep it up!';


        $headRemarks =
            $average >= 80
                ? 'Excellent academic performance. Keep up the good work.'
                : (
                    $average >= 60
                        ? 'Good academic performance. Continue working hard.'
                        : (
                            $average >= 50
                                ? 'Satisfactory performance. More effort is required.'
                                : 'Performance requires significant improvement.'
                        )
                );


        /*
        |--------------------------------------------------------------------------
        | SCHOOL INFORMATION
        |--------------------------------------------------------------------------
        */

        $schoolName =
            config(
                'app.school_name',
                'TALHA PREMIER INTERNATIONAL ACADEMY'
            );


        $schoolMotto =
            config(
                'app.school_motto',
                'Excellence in Education'
            );


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT COMPATIBILITY VARIABLES
        |--------------------------------------------------------------------------
        |
        | The report-card Blade uses these names.
        |
        */

        $class = $studentClass;

        $classPosition =
            $overallPosition;


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return view(
            'report_cards.show',
            compact(
                'student',
                'studentClass',
                'class',
                'academicYear',
                'term',
                'results',
                'subjectCount',
                'totalScore',
                'average',
                'overallPosition',
                'classPosition',
                'totalStudents',
                'classTeacher',
                'teacherRemarks',
                'headRemarks',
                'attendanceSummary',
                'attendanceRate',
                'schoolName',
                'schoolMotto',
                'settings'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT PORTAL REPORT CARD
    |--------------------------------------------------------------------------
    */

    public function studentReportCard()
    {
        $student = Auth::guard('student')->user();

        abort_unless($student, 403);

        $settings = SystemSetting::instance();

        $academicYearId =
            $settings->current_academic_year_id;

        $termId =
            $settings->current_term_id;


        if (
            !$academicYearId
            ||
            !$termId
        ) {

            abort(
                404,
                'The current academic period has not been configured.'
            );
        }


        $classId = StudentResult::query()
            ->where(
                'student_id',
                $student->id
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'term_id',
                $termId
            )
            ->orderByDesc('id')
            ->value(
                'student_class_id'
            );


        if (!$classId) {

            abort(
                404,
                'No report card is available for the current academic period.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | REUSE THE SAME REPORT-CARD ENGINE
        |--------------------------------------------------------------------------
        */

        return $this->reportCard(
            Request::create(
                route(
                    'report-cards.show-single',
                    [
                        'student' =>
                            $student->id,

                        'academic_year_id' =>
                            $academicYearId,

                        'term_id' =>
                            $termId,

                        'student_class_id' =>
                            $classId,
                    ]
                ),
                'GET',
                [
                    'academic_year_id' =>
                        $academicYearId,

                    'term_id' =>
                        $termId,

                    'student_class_id' =>
                        $classId,
                ]
            ),
            $student
        );
    }
}