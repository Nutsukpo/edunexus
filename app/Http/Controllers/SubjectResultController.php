<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\StudentResult;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\Term;
use App\Services\EdunexusAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SubjectResultController extends Controller
{
    public function __construct(
        private EdunexusAuthorizationService $authorization
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT RESULTS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $settings = SystemSetting::instance();

        $currentAcademicYear = $settings->currentAcademicYear;
        $currentTerm = $settings->currentTerm;

        /*
        |--------------------------------------------------------------------------
        | DEFAULT TO CURRENT PERIOD
        |--------------------------------------------------------------------------
        */

        $academicYearId = $request->filled('academic_year_id')
            ? (int) $request->academic_year_id
            : ($currentAcademicYear?->id);

        $termId = $request->filled('term_id')
            ? (int) $request->term_id
            : ($currentTerm?->id);

        /*
        |--------------------------------------------------------------------------
        | BASE QUERY
        |--------------------------------------------------------------------------
        */

        $query = StudentResult::query()
            ->with([
                'student',
                'subject',
                'studentClass',
                'academicYear',
                'term',
            ]);

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC PERIOD
        |--------------------------------------------------------------------------
        */

        if ($academicYearId) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        if ($termId) {
            $query->where(
                'term_id',
                $termId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CLASS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('student_class_id')) {

            $classId = (int) $request->student_class_id;

            if ($request->filled('subject_id')) {

                $subjectId = (int) $request->subject_id;

                abort_unless(
                    $this->authorization->canAccessClassSubject(
                        auth()->user(),
                        $classId,
                        $subjectId,
                        $academicYearId
                    ),
                    403
                );

            }

            $query->where(
                'student_class_id',
                $classId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        if ($request->filled('subject_id')) {

            $subjectId = (int) $request->subject_id;

            if ($request->filled('student_class_id')) {

                $classId = (int) $request->student_class_id;

                abort_unless(
                    $this->authorization->canAccessClassSubject(
                        auth()->user(),
                        $classId,
                        $subjectId,
                        $academicYearId
                    ),
                    403
                );

            } elseif ($this->authorization->isScopedStaff(auth()->user())) {

                /*
                |--------------------------------------------------------------------------
                | SUBJECT-ONLY FILTER FOR SCOPED STAFF
                |--------------------------------------------------------------------------
                */

                $staff = $this->authorization->staffFor(
                    auth()->user()
                );

                abort_unless($staff, 403);

                $query->whereHas(
                    'studentClass.subjectStaff',
                    function ($q) use ($staff, $subjectId) {

                        $q->where(
                            'staff_id',
                            $staff->id
                        )
                        ->where(
                            'subject_id',
                            $subjectId
                        );
                    }
                );
            }

            $query->where(
                'subject_id',
                $subjectId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SCOPED STAFF CLASS ACCESS
        |--------------------------------------------------------------------------
        */

        if (
            $this->authorization->isScopedStaff(
                auth()->user()
            )
        ) {

            $classQuery =
                $this->authorization->accessibleClassSubjects(
                    auth()->user(),
                    $academicYearId
                );

            $query->whereIn(
                'student_class_id',
                $classQuery->select('student_classes.id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RESULTS
        |--------------------------------------------------------------------------
        */

        $results = $query
            ->orderBy('student_class_id')
            ->orderBy('subject_id')
            ->orderByDesc('total_score')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | POSITIONS
        |--------------------------------------------------------------------------
        */

        $results = $this->calculatePositions($results);

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $currentPage = Paginator::resolveCurrentPage();

        $perPage = 50;

        $total = $results->count();

        $currentPageResults = $results
            ->slice(
                ($currentPage - 1) * $perPage,
                $perPage
            )
            ->values();

        $paginatedResults = new LengthAwarePaginator(
            $currentPageResults,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | FILTER DATA
        |--------------------------------------------------------------------------
        */

        $classes = StudentClass::query()
            ->orderBy('name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TERMS SHOULD MATCH SELECTED YEAR
        |--------------------------------------------------------------------------
        */

        $terms = collect();

        if ($academicYearId) {

            $terms = Term::query()
                ->where(
                    'academic_year_id',
                    $academicYearId
                )
                ->orderBy('start_date')
                ->get();
        }

        return view('scores.subject-results', [
            'results' => $paginatedResults,

            'classes' => $classes,
            'subjects' => $subjects,
            'academicYears' => $academicYears,
            'terms' => $terms,

            'settings' => $settings,
            'currentAcademicYear' => $currentAcademicYear,
            'currentTerm' => $currentTerm,

            'selectedAcademicYearId' => $academicYearId,
            'selectedTermId' => $termId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POSITION CALCULATION
    |--------------------------------------------------------------------------
    */

    private function calculatePositions($results)
    {
        $grouped = $results->groupBy(function ($result) {
            return implode('_', [
                $result->student_class_id,
                $result->subject_id,
                $result->academic_year_id,
                $result->term_id,
            ]);
        });

        $positioned = collect();

        foreach ($grouped as $group) {

            $sorted = $group
                ->sortByDesc('total_score')
                ->values();

            $position = 1;
            $samePosition = 1;
            $previousScore = null;

            foreach ($sorted as $result) {

                if (
                    $previousScore !== null
                    && (float) $result->total_score === (float) $previousScore
                ) {
                    $result->position = $samePosition;
                } else {
                    $result->position = $position;
                    $samePosition = $position;
                }

                $previousScore = $result->total_score;

                $position++;

                $positioned->push($result);
            }
        }

        return $positioned
            ->sortBy([
                ['student_class_id', 'asc'],
                ['subject_id', 'asc'],
                ['position', 'asc'],
            ])
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW RESULT
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        abort_unless(
            auth()->user()->can('results.view'),
            403
        );

        $result = StudentResult::with([
            'student',
            'subject',
            'studentClass',
            'academicYear',
            'term',
        ])->findOrFail($id);

        abort_unless(
            $this->authorization->canAccessClassSubject(
                auth()->user(),
                (int) $result->student_class_id,
                (int) $result->subject_id,
                (int) $result->academic_year_id
            ),
            403
        );

        $position = $this->getStudentPositionInSubject(
            $result->student_id,
            $result->subject_id,
            $result->student_class_id,
            $result->academic_year_id,
            $result->term_id
        );

        return view(
            'results.show',
            compact(
                'result',
                'position'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SINGLE STUDENT POSITION
    |--------------------------------------------------------------------------
    */

    private function getStudentPositionInSubject(
        $studentId,
        $subjectId,
        $classId,
        $academicYearId,
        $termId
    ) {
        $results = StudentResult::query()
            ->where(
                'subject_id',
                $subjectId
            )
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
            )
            ->orderByDesc('total_score')
            ->get();

        $position = 1;
        $samePosition = 1;
        $previousScore = null;

        foreach ($results as $result) {

            if (
                $previousScore !== null
                && (float) $result->total_score === (float) $previousScore
            ) {
                $currentPosition = $samePosition;
            } else {
                $currentPosition = $position;
                $samePosition = $position;
            }

            if (
                (int) $result->student_id ===
                (int) $studentId
            ) {
                return $currentPosition;
            }

            $previousScore = $result->total_score;

            $position++;
        }

        return null;
    }
}