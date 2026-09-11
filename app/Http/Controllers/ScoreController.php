<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Services\EdunexusAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScoreController extends Controller
{
    public function __construct(
        private EdunexusAuthorizationService $authorization
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT ACADEMIC PERIOD
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
    | CHECK CLASS ACCESS
    |--------------------------------------------------------------------------
    |
    | Unrestricted users only need class access.
    |
    | Scoped staff must also have access to the selected subject.
    |
    */

    private function authorizeClassSubject(
        int $classId,
        int $subjectId,
        int $academicYearId
    ): void {
        $user = auth()->user();

        if (
            $this->authorization->isScopedStaff($user)
        ) {
            abort_unless(
                $this->authorization->canAccessClassSubject(
                    $user,
                    $classId,
                    $subjectId,
                    $academicYearId
                ),
                403
            );

            return;
        }

        abort_unless(
            $this->authorization->canAccessClass(
                $user,
                $classId
            ),
            403
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SCORE ENTRY PAGE
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $period = $this->currentPeriod();

        $classes = $this->authorization
            ->accessibleClasses(auth()->user())
            ->orderBy('name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        return view('scores.index', [
            'classes' => $classes,
            'subjects' => $subjects,

            'academicYear' => $period['academicYear'],
            'term' => $period['term'],
            'settings' => $period['settings'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS
    |--------------------------------------------------------------------------
    */

    public function loadStudents(Request $request)
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],
        ]);

        $period = $this->currentPeriod();

        $academicYear = $period['academicYear'];
        $term = $period['term'];

        $classId = (int) $validated['student_class_id'];
        $subjectId = (int) $validated['subject_id'];

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        $this->authorizeClassSubject(
            $classId,
            $subjectId,
            (int) $academicYear->id
        );

        /*
        |--------------------------------------------------------------------------
        | SELECTED CLASS
        |--------------------------------------------------------------------------
        */

        $studentClass = StudentClass::findOrFail(
            $classId
        );

        $subject = Subject::findOrFail(
            $subjectId
        );

        /*
        |--------------------------------------------------------------------------
        | CURRENT-PERIOD STUDENTS ONLY
        |--------------------------------------------------------------------------
        */

        $students = Student::query()
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
                        ->where(
                            'is_current',
                            true
                        )
                        ->whereIn(
                            'status',
                            [
                                'active',
                                'repeated',
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
        | EXISTING RESULTS
        |--------------------------------------------------------------------------
        */

        $existingResults = StudentResult::query()
            ->where(
                'student_class_id',
                $classId
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->where(
                'academic_year_id',
                $academicYear->id
            )
            ->where(
                'term_id',
                $term->id
            )
            ->get()
            ->keyBy('student_id');

        return view('scores.students-table', [
            'students' => $students,
            'existingResults' => $existingResults,

            'studentClass' => $studentClass,
            'subject' => $subject,

            'academicYear' => $academicYear,
            'term' => $term,
            'settings' => $period['settings'],

            'academic_year_id' => $academicYear->id,
            'term_id' => $term->id,
            'student_class_id' => $classId,
            'subject_id' => $subjectId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE SCORES
    |--------------------------------------------------------------------------
    */

    public function save(Request $request)
    {
        abort_unless(
            auth()->user()->can('results.create')
            || auth()->user()->can('results.edit'),
            403
        );

        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'results' => [
                'required',
                'array',
                'min:1',
            ],

            'results.*.student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],

            'results.*.class_score' => [
                'required',
                'numeric',
                'min:0',
                'max:30',
            ],

            'results.*.exam_score' => [
                'required',
                'numeric',
                'min:0',
                'max:70',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SYSTEM SETTINGS ARE AUTHORITATIVE
        |--------------------------------------------------------------------------
        */

        $period = $this->currentPeriod();

        $academicYear = $period['academicYear'];
        $term = $period['term'];

        $classId = (int) $validated['student_class_id'];
        $subjectId = (int) $validated['subject_id'];

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        $this->authorizeClassSubject(
            $classId,
            $subjectId,
            (int) $academicYear->id
        );

        /*
        |--------------------------------------------------------------------------
        | VERIFY STUDENTS
        |--------------------------------------------------------------------------
        */

        $studentIds = collect(
            $validated['results']
        )
            ->pluck('student_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();

        $validStudentIds = StudentClassAssignment::query()
            ->whereIn(
                'student_id',
                $studentIds
            )
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
            ->where(
                'is_current',
                true
            )
            ->whereIn(
                'status',
                [
                    'active',
                    'repeated',
                ]
            )
            ->pluck('student_id')
            ->unique();

        if (
            $studentIds
                ->diff($validStudentIds)
                ->isNotEmpty()
        ) {
            throw ValidationException::withMessages([
                'results' =>
                    'One or more selected students are not currently assigned to this class for the current academic period.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE RESULTS
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $validated,
                $academicYear,
                $term,
                $classId,
                $subjectId
            ) {

                foreach (
                    $validated['results']
                    as $result
                ) {

                    $classScore =
                        (float) $result['class_score'];

                    $examScore =
                        (float) $result['exam_score'];

                    $totalScore =
                        $classScore + $examScore;

                    StudentResult::updateOrCreate(
                        [
                            'student_id' =>
                                (int) $result['student_id'],

                            'subject_id' =>
                                $subjectId,

                            'academic_year_id' =>
                                $academicYear->id,

                            'term_id' =>
                                $term->id,
                        ],
                        [
                            'student_class_id' =>
                                $classId,

                            'class_score' =>
                                $classScore,

                            'exam_score' =>
                                $examScore,

                            'total_score' =>
                                $totalScore,

                            'grade' =>
                                $this->grade(
                                    $totalScore
                                ),

                            'remark' =>
                                $this->remark(
                                    $totalScore
                                ),
                        ]
                    );
                }
            }
        );

        return redirect()
            ->route('scores.index')
            ->with(
                'success',
                "Scores saved successfully for {$academicYear->name} — {$term->name}."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | GRADE
    |--------------------------------------------------------------------------
    */

    private function grade(
        float $score
    ): string {
        return match (true) {

            $score >= 80 => '1',

            $score >= 70 => '2',

            $score >= 60 => '3',

            $score >= 50 => '4',

            $score >= 40 => '5',

            default => '6',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | REMARK
    |--------------------------------------------------------------------------
    */

    private function remark(
        float $score
    ): string {
        return match (true) {

            $score >= 80 => 'Excellent',

            $score >= 70 => 'Very Good',

            $score >= 60 => 'Good',

            $score >= 50 => 'Credit',

            $score >= 40 => 'Pass',

            default => 'Fail',
        };
    }
}