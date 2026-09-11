<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\Term;
use App\Services\EdunexusAuthorizationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BroadsheetController extends Controller
{
    public function __construct(
        private EdunexusAuthorizationService $authorization
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | GET CURRENT SYSTEM PERIOD
    |--------------------------------------------------------------------------
    */

    private function currentPeriod(): array
    {
        $settings = SystemSetting::instance();

        $academicYear = $settings->currentAcademicYear;
        $term = $settings->currentTerm;

        if (!$academicYear) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'No current academic year has been configured in System Settings.',
            ]);
        }

        if (!$term) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'No current term has been configured in System Settings.',
            ]);
        }

        if (
            (int) $term->academic_year_id !==
            (int) $academicYear->id
        ) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'The current term does not belong to the current academic year.',
            ]);
        }

        return [
            'settings' => $settings,
            'academicYear' => $academicYear,
            'term' => $term,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE SELECTED REPORTING PERIOD
    |--------------------------------------------------------------------------
    */

    private function validateReportingPeriod(
        int $academicYearId,
        int $termId
    ): array {
        $academicYear = AcademicYear::find($academicYearId);

        if (!$academicYear) {
            throw ValidationException::withMessages([
                'academic_year_id' =>
                    'The selected academic year does not exist.',
            ]);
        }

        $term = Term::query()
            ->where('id', $termId)
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->first();

        if (!$term) {
            throw ValidationException::withMessages([
                'term_id' =>
                    'The selected term does not belong to the selected academic year.',
            ]);
        }

        return [
            'academicYear' => $academicYear,
            'term' => $term,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS ACCESS
    |--------------------------------------------------------------------------
    */

    private function authorizeClass(int $classId): void
    {
        abort_unless(
            $this->authorization->canAccessClass(
                auth()->user(),
                $classId
            ),
            403
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BROADSHEET INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $period = $this->currentPeriod();

        /*
        |--------------------------------------------------------------------------
        | ACCESSIBLE CLASSES
        |--------------------------------------------------------------------------
        */

        $classes = $this->authorization
            ->accessibleClasses(auth()->user())
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ALL ACADEMIC YEARS
        |--------------------------------------------------------------------------
        |
        | Historical reporting requires access to previous academic years.
        |
        */

        $academicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TERMS FOR CURRENT YEAR
        |--------------------------------------------------------------------------
        */

        $terms = Term::query()
            ->where(
                'academic_year_id',
                $period['academicYear']->id
            )
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        return view('broadsheet.index', [
            'classes' => $classes,

            'academicYears' =>
                $academicYears,

            'terms' =>
                $terms,

            'academicYear' =>
                $period['academicYear'],

            'term' =>
                $period['term'],

            'settings' =>
                $period['settings'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE BROADSHEET
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $validated = $request->validate([
            'period_mode' => [
                'required',
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
        ]);

        $systemPeriod =
            $this->currentPeriod();

        /*
        |--------------------------------------------------------------------------
        | DETERMINE REPORTING PERIOD
        |--------------------------------------------------------------------------
        */

        if (
            $validated['period_mode'] ===
            'current'
        ) {

            /*
            |--------------------------------------------------------------------------
            | CURRENT MODE
            |--------------------------------------------------------------------------
            |
            | Ignore the posted year/term and use System Settings.
            |
            */

            $academicYear =
                $systemPeriod['academicYear'];

            $term =
                $systemPeriod['term'];

        } else {

            /*
            |--------------------------------------------------------------------------
            | HISTORICAL MODE
            |--------------------------------------------------------------------------
            */

            $selectedPeriod =
                $this->validateReportingPeriod(
                    (int) $validated['academic_year_id'],
                    (int) $validated['term_id']
                );

            $academicYear =
                $selectedPeriod['academicYear'];

            $term =
                $selectedPeriod['term'];
        }

        $classId =
            (int) $validated['student_class_id'];

        /*
        |--------------------------------------------------------------------------
        | CLASS AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        $this->authorizeClass(
            $classId
        );

        /*
        |--------------------------------------------------------------------------
        | PREPARE BROADSHEET
        |--------------------------------------------------------------------------
        */

        $data =
            $this->prepareBroadsheetData(
                $classId,
                $academicYear,
                $term,
                $systemPeriod['settings']
            );

        /*
        |--------------------------------------------------------------------------
        | NO RESULTS
        |--------------------------------------------------------------------------
        */

        if ($data['subjects']->isEmpty()) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No results have been recorded for this class in '
                    . $academicYear->name
                    . ' — '
                    . $term->name
                    . '.'
                );
        }

        if ($data['students']->isEmpty()) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No students were found for this class in '
                    . $academicYear->name
                    . ' — '
                    . $term->name
                    . '.'
                );
        }

        return view(
            'broadsheet.show',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREPARE BROADSHEET DATA
    |--------------------------------------------------------------------------
    */

    private function prepareBroadsheetData(
        int $classId,
        AcademicYear $academicYear,
        Term $term,
        SystemSetting $settings
    ): array {

        /*
        |--------------------------------------------------------------------------
        | CLASS
        |--------------------------------------------------------------------------
        */

        $studentClass =
            StudentClass::findOrFail(
                $classId
            );

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        |
        | Only subjects with results in this exact
        | class + academic year + term.
        |
        */

        $subjectIds =
            StudentResult::query()
                ->where(
                    'student_class_id',
                    $classId
                )
                ->where(
                    'academic_year_id',
                    $academicYear->id
                )
                ->where(
                    'term_id',
                    $term->id
                )
                ->distinct()
                ->pluck('subject_id');

        $subjects =
            Subject::query()
                ->whereIn(
                    'id',
                    $subjectIds
                )
                ->orderBy('name')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        |
        | Historical:
        | Find students assigned to this class during
        | the selected academic year and term.
        |
        | Current:
        | Same logic, with current assignment.
        |
        */

        $students =
            Student::query()
                ->where('is_active', true)
                ->whereHas(
                    'classAssignments',
                    function ($query) use (
                        $classId,
                        $academicYear,
                        $term
                    ) {

                        $query
                            ->where(
                                'student_class_id',
                                $classId
                            )
                            ->where(
                                'academic_year_id',
                                $academicYear->id
                            )
                            ->where(
                                'term_id',
                                $term->id
                            )
                            ->whereIn(
                                'status',
                                [
                                    'active',
                                    'repeated',
                                    'completed',
                                    'promoted',
                                ]
                            );
                    }
                )
                ->orderBy('first_name')
                ->orderBy('middle_name')
                ->orderBy('last_name')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | RESULTS
        |--------------------------------------------------------------------------
        */

        $results =
            StudentResult::query()
                ->where(
                    'academic_year_id',
                    $academicYear->id
                )
                ->where(
                    'term_id',
                    $term->id
                )
                ->where(
                    'student_class_id',
                    $classId
                )
                ->get()
                ->keyBy(
                    function ($result) {
                        return $result->student_id
                            . '_'
                            . $result->subject_id;
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | RANKINGS
        |--------------------------------------------------------------------------
        */

        $rankings = [];

        foreach ($students as $student) {

            $total = 0;

            $count = 0;

            $subjectScores = [];

            foreach ($subjects as $subject) {

                $key =
                    $student->id
                    . '_'
                    . $subject->id;

                $result =
                    $results[$key] ?? null;

                $score =
                    $result
                    ? (float) $result->total_score
                    : 0;

                $subjectScores[
                    $subject->id
                ] = [

                    'subject_name' =>
                        $subject->name,

                    'score' =>
                        $score,

                    'grade' =>
                        $result?->grade,

                    'remark' =>
                        $result?->remark,

                    'class_score' =>
                        $result?->class_score,

                    'exam_score' =>
                        $result?->exam_score,
                ];

                /*
                |--------------------------------------------------------------------------
                | Average only considers recorded scores
                |--------------------------------------------------------------------------
                */

                if ($result) {

                    $total += $score;

                    $count++;
                }
            }

            $average =
                $count > 0
                    ? round(
                        $total / $count,
                        2
                    )
                    : 0;

            $rankings[
                $student->id
            ] = [

                'student' =>
                    $student,

                'total' =>
                    $total,

                'average' =>
                    $average,

                'subject_scores' =>
                    $subjectScores,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | OVERALL POSITIONS
        |--------------------------------------------------------------------------
        */

        $sortedRankings =
            collect($rankings)
                ->sortByDesc(
                    fn ($item) =>
                        $item['average']
                );

        $positions = [];

        $position = 1;

        $previousAverage = null;

        foreach (
            $sortedRankings
            as $studentId => $data
        ) {

            if (
                $previousAverage !== null
                && (float) $data['average'] ===
                   (float) $previousAverage
            ) {

                $positions[$studentId] =
                    $positions[$studentId] ??
                    ($position - 1);

            } else {

                $positions[$studentId] =
                    $position;

                $previousAverage =
                    $data['average'];
            }

            $position++;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT POSITIONS
        |--------------------------------------------------------------------------
        */

        $subjectPositions = [];

        foreach ($subjects as $subject) {

            $subjectScores = [];

            foreach ($students as $student) {

                $key =
                    $student->id
                    . '_'
                    . $subject->id;

                $result =
                    $results[$key] ?? null;

                /*
                |--------------------------------------------------------------------------
                | Students without a result are not ranked.
                |--------------------------------------------------------------------------
                */

                if ($result) {

                    $subjectScores[
                        $student->id
                    ] = (float) $result->total_score;
                }
            }

            arsort($subjectScores);

            $subjectPosition = 1;

            $previousScore = null;

            $samePosition = null;

            foreach (
                $subjectScores
                as $studentId => $score
            ) {

                if (
                    $previousScore !== null
                    && (float) $score ===
                       (float) $previousScore
                ) {

                    $subjectPositions[
                        $subject->id
                    ][$studentId] =
                        $samePosition;

                } else {

                    $subjectPositions[
                        $subject->id
                    ][$studentId] =
                        $subjectPosition;

                    $samePosition =
                        $subjectPosition;
                }

                $previousScore =
                    $score;

                $subjectPosition++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW DATA
        |--------------------------------------------------------------------------
        */

        return [

            'students' =>
                $students,

            'subjects' =>
                $subjects,

            'results' =>
                $results,

            'positions' =>
                $positions,

            'subjectPositions' =>
                $subjectPositions,

            'rankings' =>
                $rankings,

            'studentClass' =>
                $studentClass,

            'academicYear' =>
                $academicYear,

            'term' =>
                $term,

            'settings' =>
                $settings,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PDF EXPORT
    |--------------------------------------------------------------------------
    */

    public function exportPDF(Request $request)
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $validated = $request->validate([
            'period_mode' => [
                'nullable',
                'in:current,historical',
            ],

            'academic_year_id' => [
                'nullable',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'integer',
                'exists:terms,id',
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],
        ]);

        $systemPeriod =
            $this->currentPeriod();

        $mode =
            $validated['period_mode']
            ?? 'current';

        if ($mode === 'historical') {

            $period =
                $this->validateReportingPeriod(
                    (int) $validated['academic_year_id'],
                    (int) $validated['term_id']
                );

            $academicYear =
                $period['academicYear'];

            $term =
                $period['term'];

        } else {

            $academicYear =
                $systemPeriod['academicYear'];

            $term =
                $systemPeriod['term'];
        }

        $classId =
            (int) $validated['student_class_id'];

        $this->authorizeClass(
            $classId
        );

        $data =
            $this->prepareBroadsheetData(
                $classId,
                $academicYear,
                $term,
                $systemPeriod['settings']
            );

        if (
            $data['students']->isEmpty()
            || $data['subjects']->isEmpty()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'There is no broadsheet data available for the selected period.'
                );
        }

        $pdf =
            Pdf::loadView(
                'broadsheet.pdf',
                $data
            );

        $pdf->setPaper(
            'A3',
            'landscape'
        );

        $className =
            preg_replace(
                '/[^A-Za-z0-9_-]/',
                '-',
                $data['studentClass']->name
            );

        return $pdf->download(
            'broadsheet-'
            . $className
            . '-'
            . $academicYear->name
            . '-'
            . $term->name
            . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX BROADSHEET
    |--------------------------------------------------------------------------
    */

    public function ajaxLoad(Request $request)
    {
        try {

            abort_unless(
                auth()->user()->can('results.view'),
                403
            );

            $validated = $request->validate([
                'period_mode' => [
                    'nullable',
                    'in:current,historical',
                ],

                'academic_year_id' => [
                    'nullable',
                    'integer',
                    'exists:academic_years,id',
                ],

                'term_id' => [
                    'nullable',
                    'integer',
                    'exists:terms,id',
                ],

                'student_class_id' => [
                    'required',
                    'integer',
                    'exists:student_classes,id',
                ],
            ]);

            $systemPeriod =
                $this->currentPeriod();

            $mode =
                $validated['period_mode']
                ?? 'current';

            if (
                $mode ===
                'historical'
            ) {

                $period =
                    $this->validateReportingPeriod(
                        (int) $validated['academic_year_id'],
                        (int) $validated['term_id']
                    );

                $academicYear =
                    $period['academicYear'];

                $term =
                    $period['term'];

            } else {

                $academicYear =
                    $systemPeriod['academicYear'];

                $term =
                    $systemPeriod['term'];
            }

            $classId =
                (int) $validated['student_class_id'];

            $this->authorizeClass(
                $classId
            );

            $data =
                $this->prepareBroadsheetData(
                    $classId,
                    $academicYear,
                    $term,
                    $systemPeriod['settings']
                );

            if (
                $data['subjects']->isEmpty()
            ) {

                return response()->json([
                    'success' => false,

                    'message' =>
                        'No results have been recorded for the selected period.',
                ]);
            }

            if (
                $data['students']->isEmpty()
            ) {

                return response()->json([
                    'success' => false,

                    'message' =>
                        'No students were found for the selected class and period.',
                ]);
            }

            $rankings =
                $data['rankings'];

            $classAverage =
                $rankings
                    ? (float) collect($rankings)
                        ->avg('average')
                    : 0;

            $classAverage =
                round(
                    $classAverage,
                    2
                );

            $passCount =
                collect($rankings)
                    ->filter(
                        fn ($item) =>
                            ($item['average'] ?? 0)
                            >= 50
                    )
                    ->count();

            $passRate =
                $data['students']->count() > 0
                    ? round(
                        (
                            $passCount /
                            $data['students']->count()
                        ) * 100,
                        1
                    )
                    : 0;

            return response()->json([
                'success' => true,

                'academicYear' =>
                    $academicYear,

                'term' =>
                    $term,

                'studentClass' =>
                    $data['studentClass'],

                'subjects' =>
                    $data['subjects'],

                'students' =>
                    $data['students'],

                'results' =>
                    $data['results'],

                'rankings' =>
                    $data['rankings'],

                'positions' =>
                    $data['positions'],

                'subjectPositions' =>
                    $data['subjectPositions'],

                'classAverage' =>
                    $classAverage,

                'passRate' =>
                    $passRate,

                'studentCount' =>
                    $data['students']->count(),

                'subjectCount' =>
                    $data['subjects']->count(),
            ]);

        } catch (
            ValidationException $e
        ) {

            return response()->json([
                'success' => false,

                'message' =>
                    $e->validator
                        ->errors()
                        ->first(),
            ], 422);

        } catch (\Throwable $e) {

            Log::error(
                'Broadsheet AJAX failed',
                [
                    'message' =>
                        $e->getMessage(),

                    'user_id' =>
                        auth()->id(),
                ]
            );

            return response()->json([
                'success' => false,

                'message' =>
                    'Unable to load the broadsheet.',
            ], 500);
        }
    }
}