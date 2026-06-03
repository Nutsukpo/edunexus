<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\StudentClass;
use App\Models\Term;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BroadsheetController extends Controller
{
    public function index()
    {
        return view('broadsheet.index', [
            'academicYears' => AcademicYear::all(),
            'terms' => Term::all(),
            'classes' => StudentClass::all(),
        ]);
    }

    public function exportPDF(Request $request)
    {
        // Similar to generate() but returns PDF
        $data = $this->prepareBroadsheetData($request);
        $pdf = PDF::loadView('broadsheet.pdf', $data);
        $pdf->setPaper('A3', 'landscape');
        return $pdf->download('class-broadsheet.pdf');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'term_id' => 'required',
            'student_class_id' => 'required',
        ]);

        $classId = $request->student_class_id;

        // Fetch subjects that have results for this class
        // This gets distinct subjects from student_results table for the selected class
        $subjects = Subject::whereIn('id', function($query) use ($request, $classId) {
            $query->select('subject_id')
                  ->from('student_results')
                  ->where('student_class_id', $classId)
                  ->where('academic_year_id', $request->academic_year_id)
                  ->where('term_id', $request->term_id)
                  ->distinct();
        })
        ->orderBy('name')
        ->get();

        // If no subjects found, show error
        if ($subjects->isEmpty()) {
            return back()->with('error', 'No results have been recorded for this class in the selected academic year and term.');
        }

        // Load students in selected class
        $students = Student::whereHas('classAssignments', function ($query) use ($classId) {
            $query->where('student_class_id', $classId);
        })->get();

        // If no students found
        if ($students->isEmpty()) {
            return back()->with('error', 'No students found in this class.');
        }

        // Load all results for selected term, year, and class
        $results = StudentResult::where('academic_year_id', $request->academic_year_id)
            ->where('term_id', $request->term_id)
            ->where('student_class_id', $classId)
            ->get()
            ->keyBy(function ($result) {
                return $result->student_id . '_' . $result->subject_id;
            });

        // Calculate rankings and prepare data
        $rankings = [];
        $subjectTotals = [];
        $subjectCounts = [];

        foreach ($students as $student) {
            $total = 0;
            $count = 0;
            $subjectScores = [];

            foreach ($subjects as $subject) {
                $key = $student->id . '_' . $subject->id;
                $result = $results[$key] ?? null;
                $score = $result ? $result->total_score : 0;
                
                $subjectScores[$subject->id] = [
                    'subject_name' => $subject->name,
                    'score' => $score,
                    'grade' => $result ? $result->grade : null,
                    'remark' => $result ? $result->remark : null,
                    'class_score' => $result ? $result->class_score : null,
                    'exam_score' => $result ? $result->exam_score : null,
                ];
                
                $total += $score;
                
                if ($score > 0) {
                    $count++;
                }

                // Track subject totals for subject-based ranking
                if (!isset($subjectTotals[$subject->id][$student->id])) {
                    $subjectTotals[$subject->id][$student->id] = $score;
                }
            }

            $average = $count > 0 ? round($total / $count, 2) : 0;

            $rankings[$student->id] = [
                'student' => $student,
                'total' => $total,
                'average' => $average,
                'subject_scores' => $subjectScores,
            ];
        }

        // Calculate overall positions (based on average)
        $rankingsCollection = collect($rankings);
        $sortedRankings = $rankingsCollection->sortByDesc(function ($item) {
            return $item['average'];
        });

        // Assign positions with tie handling
        $positions = [];
        $position = 1;
        $previousAverage = null;
        $samePositionCount = 0;

        foreach ($sortedRankings as $studentId => $data) {
            if ($previousAverage !== null && $data['average'] == $previousAverage) {
                $positions[$studentId] = $position - $samePositionCount;
                $samePositionCount++;
            } else {
                $positions[$studentId] = $position;
                $previousAverage = $data['average'];
                $samePositionCount = 1;
            }
            $position++;
        }

        // Calculate subject-specific positions
        $subjectPositions = [];
        foreach ($subjects as $subject) {
            $subjectScores = [];
            foreach ($students as $student) {
                $key = $student->id . '_' . $subject->id;
                $score = isset($results[$key]) ? $results[$key]->total_score : 0;
                $subjectScores[$student->id] = $score;
            }
            
            // Sort by score descending
            arsort($subjectScores);
            
            // Assign positions
            $subPosition = 1;
            $prevScore = null;
            $samePosCount = 0;
            foreach ($subjectScores as $studentId => $score) {
                if ($prevScore !== null && $score == $prevScore) {
                    $subjectPositions[$subject->id][$studentId] = $subPosition - $samePosCount;
                    $samePosCount++;
                } else {
                    $subjectPositions[$subject->id][$studentId] = $subPosition;
                    $prevScore = $score;
                    $samePosCount = 1;
                }
                $subPosition++;
            }
        }

        return view('broadsheet.show', compact(
            'students',
            'subjects',
            'results',
            'positions',
            'subjectPositions',
            'rankings',
            'request'
        ));
    }

        /**
 * AJAX endpoint for loading broadsheet data
 */
    public function ajaxLoad(Request $request)
    {
        try {
            $request->validate([
                'academic_year_id' => 'required',
                'term_id' => 'required',
                'student_class_id' => 'required',
            ]);

            $classId = $request->student_class_id;

            // Get distinct subject IDs that have results for this class
            $subjectIds = StudentResult::where('student_class_id', $classId)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('term_id', $request->term_id)
                ->distinct()
                ->pluck('subject_id');

            // Fetch the subject details
            $subjects = Subject::whereIn('id', $subjectIds)
                ->orderBy('name')
                ->get();

            // If no subjects found
            if ($subjects->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No results have been recorded for this class.'
                ]);
            }

            // Load students in selected class
            $students = Student::whereHas('classAssignments', function ($query) use ($classId) {
                $query->where('student_class_id', $classId);
            })->get();

            // If no students found
            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found in this class.'
                ]);
            }

            // Load all results for selected term, year, and class
            $results = StudentResult::where('academic_year_id', $request->academic_year_id)
                ->where('term_id', $request->term_id)
                ->where('student_class_id', $classId)
                ->get()
                ->keyBy(function ($result) {
                    return $result->student_id . '_' . $result->subject_id;
                });

            // Calculate rankings and prepare data
            $rankings = [];
            foreach ($students as $student) {
                $total = 0;
                $count = 0;

                foreach ($subjects as $subject) {
                    $key = $student->id . '_' . $subject->id;
                    $result = $results[$key] ?? null;
                    $score = $result ? $result->total_score : 0;
                    
                    $total += $score;
                    if ($score > 0) {
                        $count++;
                    }
                }

                $average = $count > 0 ? round($total / $count, 2) : 0;

                $rankings[$student->id] = [
                    'total' => $total,
                    'average' => $average,
                ];
            }

            // Calculate overall positions (based on average)
            $rankingsCollection = collect($rankings);
            $sortedRankings = $rankingsCollection->sortByDesc(function ($item) {
                return $item['average'];
            });

            // Assign positions with tie handling
            $positions = [];
            $position = 1;
            $previousAverage = null;
            $samePositionCount = 0;

            foreach ($sortedRankings as $studentId => $data) {
                if ($previousAverage !== null && $data['average'] == $previousAverage) {
                    $positions[$studentId] = $position - $samePositionCount;
                    $samePositionCount++;
                } else {
                    $positions[$studentId] = $position;
                    $previousAverage = $data['average'];
                    $samePositionCount = 1;
                }
                $position++;
            }

            // Calculate class statistics
            $classAverage = $rankingsCollection->avg('average');
            $passCount = $rankingsCollection->filter(fn($item) => ($item['average'] ?? 0) >= 50)->count();
            $passRate = $students->count() > 0 ? round(($passCount / $students->count()) * 100, 1) : 0;

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'students' => $students,
                'results' => $results,
                'rankings' => $rankings,
                'positions' => $positions,
                'classAverage' => $classAverage,
                'passRate' => $passRate,
                'studentCount' => $students->count(),
                'subjectCount' => $subjects->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}