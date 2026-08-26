<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Services\EdunexusAuthorizationService;

class ScoreController extends Controller
{
    public function __construct(private EdunexusAuthorizationService $authorization)
    {
    }

    /**
     * Show score entry form
     */
    public function index()
    {
        abort_unless(auth()->user()->can('results.view'), 403);

        return view('scores.index', [
            'classes' => $this->authorization->accessibleClasses(auth()->user())->get(),
            'subjects' => Subject::all(),
            'academicYears' => AcademicYear::all(),
            'terms' => Term::all(),
        ]);
    }

    /**
     * Load students for selected class
     */
    public function loadStudents(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'term_id' => 'required',
            'student_class_id' => 'required',
            'subject_id' => 'required',
        ]);

        abort_unless(auth()->user()->can('results.view'), 403);
        abort_unless(
            $this->authorization->canAccessClassSubject(
                auth()->user(),
                (int) $request->student_class_id,
                (int) $request->subject_id,
                (int) $request->academic_year_id
            ),
            403
        );

        $students = Student::whereHas('classAssignments', function ($query) use ($request) {
            $query->where('student_class_id', $request->student_class_id);
        })->get();

        return view('scores.students-table', [
            'students' => $students,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
            'student_class_id' => $request->student_class_id,
            'subject_id' => $request->subject_id,
        ]);
    }

    /**
     * Save scores
     */
    public function save(Request $request)
    {
        abort_unless(auth()->user()->can('results.create') || auth()->user()->can('results.edit'), 403);

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'results' => 'required|array',
        ]);

        abort_unless(
            $this->authorization->canAccessClassSubject(
                auth()->user(),
                (int) $request->student_class_id,
                (int) $request->subject_id,
                (int) $request->academic_year_id
            ),
            403
        );

        foreach ($request->results as $result) {

            $classScore = $result['class_score'] ?? 0;
            $examScore  = $result['exam_score'] ?? 0;

            $totalScore = $classScore + $examScore;

            StudentResult::updateOrCreate(

                [
                    'student_id'       => $result['student_id'],
                    'subject_id'       => $request->subject_id,
                    'term_id'          => $request->term_id,
                    'academic_year_id' => $request->academic_year_id,
                ],

                [
                    'student_class_id' => $request->student_class_id,
                    'class_score'      => $classScore,
                    'exam_score'       => $examScore,
                    'total_score'      => $totalScore,
                    'grade'            => $this->grade($totalScore),
                    'remark'           => $this->remark($totalScore),
                ]
            );
        }

        return redirect()
            ->route('scores.index')
            ->with('success', 'Scores saved successfully.');
    }

    /**
     * Calculate Grade
     */
    private function grade($score)
    {
        if ($score >= 80) {
            return '1';
        }

        if ($score >= 70) {
            return '2';
        }

        if ($score >= 60) {
            return '3';
        }

        if ($score >= 50) {
            return '4';
        }

        if ($score >= 40) {
            return '5';
        }

        return '6';
    }

    /**
     * Calculate Remark
     */
    private function remark($score)
    {
        if ($score >= 80) {
            return 'Excellent';
        }

        if ($score >= 70) {
            return 'Very Good';
        }

        if ($score >= 60) {
            return 'Good';
        }

        if ($score >= 50) {
            return 'Credit';
        }

        if ($score >= 40) {
            return 'Pass';
        }

        return 'Fail';
    }
}