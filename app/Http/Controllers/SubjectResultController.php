<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use App\Services\EdunexusAuthorizationService;

class SubjectResultController extends Controller
{
    public function __construct(private EdunexusAuthorizationService $authorization)
    {
    }

    /**
     * Display subject results.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('results.view'), 403);

        $query = StudentResult::with([
            'student',
            'subject',
            'studentClass',
            'academicYear',
            'term'
        ]);

        if ($this->authorization->isScopedStaff(auth()->user())) {
            $classQuery = $this->authorization->accessibleClassSubjects(
                auth()->user(),
                $request->filled('academic_year_id') ? (int) $request->academic_year_id : null
            );

            $query->whereIn('student_class_id', $classQuery->select('student_classes.id'));

            if ($request->filled('subject_id')) {
                $allowed = $this->authorization->canAccessClassSubject(
                    auth()->user(),
                    (int) $request->student_class_id,
                    (int) $request->subject_id,
                    $request->filled('academic_year_id') ? (int) $request->academic_year_id : null
                );

                if ($request->filled('student_class_id')) {
                    abort_unless($allowed, 403);
                }

                $staff = $this->authorization->staffFor(auth()->user());
                $query->whereHas('studentClass.subjectStaff', function ($q) use ($staff) {
                    $q->where('staff_id', $staff->id)
                      ->whereColumn('class_subject_staff.student_class_id', 'student_results.student_class_id')
                      ->where('subject_id', request('subject_id'));
                });
            }
        }

        // Filter by Academic Year
        if ($request->filled('academic_year_id')) {
            $query->where(
                'academic_year_id',
                $request->academic_year_id
            );
        }

        // Filter by Term
        if ($request->filled('term_id')) {
            $query->where(
                'term_id',
                $request->term_id
            );
        }

        // Filter by Class
        if ($request->filled('student_class_id')) {
            $query->where(
                'student_class_id',
                $request->student_class_id
            );
        }

        // Filter by Subject
        if ($request->filled('subject_id')) {
            $query->where(
                'subject_id',
                $request->subject_id
            );
        }

        $results = $query
            ->orderBy('student_class_id')
            ->orderBy('total_score', 'DESC')
            ->get();

        // Calculate position for each result within the same class and subject
        $results = $this->calculatePositions($results, $request);

        // Paginate after calculating positions
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 50;
        $currentPageResults = $results->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $results = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageResults,
            $results->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('scores.subject-results', [
            'results'        => $results,
            'subjects'       => Subject::orderBy('name')->get(),
            'classes'        => StudentClass::orderBy('name')->get(),
            'academicYears'  => AcademicYear::orderBy('name')->get(),
            'terms'          => Term::orderBy('name')->get(),
        ]);
    }

    /**
     * Calculate positions for students in a particular subject.
     */
    private function calculatePositions($results, $request)
    {
        // Group by class and subject to calculate positions within each group
        $grouped = $results->groupBy(function ($item) {
            return $item->student_class_id . '_' . $item->subject_id;
        });

        $allResults = collect();

        foreach ($grouped as $groupKey => $group) {
            // Sort by total score descending
            $sorted = $group->sortByDesc('total_score');
            
            $position = 1;
            $previousScore = null;
            $samePosition = 1;
            
            foreach ($sorted as $index => $result) {
                // Handle ties (same score gets same position)
                if ($previousScore !== null && $result->total_score == $previousScore) {
                    $result->position = $samePosition;
                } else {
                    $result->position = $position;
                    $samePosition = $position;
                }
                
                $previousScore = $result->total_score;
                $position++;
                $allResults->push($result);
            }
        }

        return $allResults->sortBy([
            ['student_class_id', 'asc'],
            ['position', 'asc']
        ]);
    }

    /**
     * Show a single result.
     */
    public function show($id)
    {
        abort_unless(auth()->user()->can('results.view'), 403);

        $result = StudentResult::with([
            'student',
            'subject',
            'studentClass',
            'academicYear',
            'term'
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

        // Get position for this specific result in its subject and class
        $position = $this->getStudentPositionInSubject(
            $result->student_id,
            $result->subject_id,
            $result->student_class_id,
            $result->academic_year_id,
            $result->term_id
        );

        return view('results.show', compact('result', 'position'));
    }

    /**
     * Get a student's position in a particular subject.
     */
    private function getStudentPositionInSubject($studentId, $subjectId, $classId, $academicYearId, $termId)
    {
        $results = StudentResult::where('subject_id', $subjectId)
            ->where('student_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->orderBy('total_score', 'DESC')
            ->get();

        $position = 1;
        $previousScore = null;
        $samePosition = 1;
        
        foreach ($results as $index => $result) {
            if ($previousScore !== null && $result->total_score == $previousScore) {
                $currentPosition = $samePosition;
            } else {
                $currentPosition = $position;
                $samePosition = $position;
            }
            
            if ($result->student_id == $studentId) {
                return $currentPosition;
            }
            
            $previousScore = $result->total_score;
            $position++;
        }
        
        return null;
    }
}