<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\StudentClass;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Staff;
use Illuminate\Support\Facades\Log;

class ReportCardController extends Controller
{
    /**
     * Report Card Filter Page
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $terms = Term::orderBy('name')->get();
        $classes = StudentClass::orderBy('name')->get();
        $students = Student::orderBy('first_name')->get();

        return view('report_cards.index', compact(
            'academicYears',
            'terms',
            'classes',
            'students'
        ));
    }

    public function reportCard($studentId)
    {
    $results = StudentResult::with([
    'student',
    'subject',
    'studentClass',
    'academicYear',
    'term'
    ])
    ->where('student_id', $studentId)
    ->get();
    
    if ($results->isEmpty()) {
        abort(404, 'No results found for this student');
    }
    
    $student = $results->first()->student;
    $studentClass = $results->first()->studentClass;
    $academicYear = $results->first()->academicYear;
    $term = $results->first()->term;
    
    $totalScore = $results->sum('total_score');
    
    $averageScore = round(
        $results->avg('total_score'),
        2
    );
    
    $subjectsOffered = $results->count();
    
    return view(
        'results.report-card',
        compact(
            'student',
            'studentClass',
            'academicYear',
            'term',
            'results',
            'totalScore',
            'averageScore',
            'subjectsOffered'
        )
    );
    
    }
    

    /**
     * Get students by class for AJAX request
     */
    public function getStudentsByClass(Request $request)
    {
        $classId = $request->class_id;
        
        if (!$classId) {
            return response()->json([]);
        }
        
        $students = Student::whereHas('classAssignments', function($query) use ($classId) {
            $query->where('student_class_id', $classId)
                  ->where('is_current', true);
        })
        ->orderBy('first_name')
        ->get();
        
        return response()->json($students);
    }

    /**
     * Generate Student Report Card
     */
    public function show(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id'          => 'required|exists:terms,id',
                'student_class_id' => 'required|exists:student_classes,id',
                'student_id'       => 'required|exists:students,id',
            ]);
           
            // Get all required data
            $student = Student::findOrFail($request->student_id);
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $term = Term::findOrFail($request->term_id);
            $class = StudentClass::findOrFail($request->student_class_id);

            // Get student results
            $results = StudentResult::with('subject')
                ->where('academic_year_id', $request->academic_year_id)
                ->where('term_id', $request->term_id)
                ->where('student_class_id', $request->student_class_id)
                ->where('student_id', $request->student_id)
                ->orderBy('subject_id')
                ->get();

            // Calculate subject positions
            foreach ($results as $result) {
                $ranking = StudentResult::where('academic_year_id', $request->academic_year_id)
                    ->where('term_id', $request->term_id)
                    ->where('student_class_id', $request->student_class_id)
                    ->where('subject_id', $result->subject_id)
                    ->orderByDesc('total_score')
                    ->pluck('student_id')
                    ->toArray();

                $position = array_search($student->id, $ranking);
                $result->subject_position = ($position !== false) ? $position + 1 : '-';
            }

            // Calculate class position
            $classTotals = StudentResult::selectRaw('student_id, SUM(total_score) as grand_total')
                ->where('academic_year_id', $request->academic_year_id)
                ->where('term_id', $request->term_id)
                ->where('student_class_id', $request->student_class_id)
                ->groupBy('student_id')
                ->orderByDesc('grand_total')
                ->get();

            $classPosition = null;
            $totalStudents = $classTotals->count();

            foreach ($classTotals as $index => $row) {
                if ($row->student_id == $student->id) {
                    $classPosition = $index + 1;
                    break;
                }
            }

            // Calculate totals
            $totalMarks = $results->sum('total_score');
            $subjectsCount = $results->count();
            $average = $subjectsCount > 0 ? round($totalMarks / $subjectsCount, 2) : 0;
            
            // Get class teacher
            $classTeacher = $class->classTeacher;
            
            // School settings
            $schoolName = 'KABORE SCHOOL COMPLEX';
            $schoolMotto = 'Excellence in Education';
            
            // Get teacher remarks (you can customize this)
            $teacherRemarks = $this->getTeacherRemarks($average);
            
            // Get headmaster remarks based on term and performance
            $headRemarks = $this->getHeadmasterRemarks($term->name, $average);

            return view('report_cards.show', compact(
                'student',
                'results',
                'academicYear',
                'term',
                'class',
                'classPosition',
                'totalMarks',
                'average',
                'totalStudents',
                'classTeacher',
                'schoolName',
                'schoolMotto',
                'teacherRemarks',
                'headRemarks'
            ));
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Report Card Generation Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return back with error
            return redirect()->route('report-cards.index')
                ->with('error', 'Error generating report card: ' . $e->getMessage());
        }
    }

    /**
     * Generate Teacher's Remarks based on average performance
     */
    private function getTeacherRemarks($average)
    {
        if ($average >= 80) {
            return " EXCELLENT! The student has shown outstanding academic performance this term. Exceptional understanding of all subjects. Keep up the remarkable work!";
        } elseif ($average >= 70) {
            return " VERY GOOD! The student has performed very well this term. Consistent effort and good understanding of concepts. Aim for excellence next term!";
        } elseif ($average >= 60) {
            return " GOOD! The student has shown satisfactory performance. With a little more dedication, can achieve even better results. Keep pushing forward!";
        } elseif ($average >= 50) {
            return " AVERAGE! The student needs to put in more effort. Regular study habits and homework completion will help improve performance.";
        } else {
            return " NEEDS IMPROVEMENT! The student's performance is below expectations. Please focus on weak areas and seek extra help where needed.";
        }
    }

    /**
     * Generate Headmaster's Remarks based on term and performance
     */
    private function getHeadmasterRemarks($termName, $average)
    {
        $termName = strtolower($termName);
        
        // For First Term
        if (str_contains($termName, 'first') || str_contains($termName, '1st')) {
            if ($average >= 70) {
                return "GOOD START! The student has begun the academic year well. Maintain this momentum for even better results. Room for excellence exists.";
            } elseif ($average >= 50) {
                return "⚠️ FAIR START! Acceptable performance but requires more dedication. Focus on improving weak areas next term.";
            } else {
                return " POOR START! Immediate intervention needed. Parents are advised to monitor studies closely and provide necessary support.";
            }
        }
        
        // For Second Term
        if (str_contains($termName, 'second') || str_contains($termName, '2nd')) {
            if ($average >= 70) {
                return " IMPROVING WELL! The student shows consistent progress. Keep up the good work. Final term excellence is achievable.";
            } elseif ($average >= 50) {
                return "⚠️ ROOM FOR IMPROVEMENT! Performance is average. More effort and focus needed to meet academic targets.";
            } else {
                return " SERIOUS CONCERN! Performance is below standard. Academic probation is advised. Parents must take immediate action.";
            }
        }
        
        // For Third Term (Final Term - Promotion Decision)
        if (str_contains($termName, 'third') || str_contains($termName, '3rd')) {
            if ($average >= 60) {
                return " APPROVED. The student is PROMOTED to the next class. Keep up the good work and maintain this excellence in the coming academic year!";
            } elseif ($average >= 50) {
                return "⚠️ CONDITIONALLY APPROVED. The student is promoted with a warning. More effort required in the next class. Improvement expected.";
            } else {
                return " NOT APPROVED FOR PROMOTION. This student must REPEAT the class for better foundation and academic improvement. Extra classes and parental support required.";
            }
        }
        
        // Default remark
        return "The student has completed the term. Continue to work hard for academic excellence.";
    }
}