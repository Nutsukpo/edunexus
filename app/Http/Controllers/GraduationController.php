<?php

namespace App\Http\Controllers;

use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use App\Models\StudentClass;
use App\Models\AcademicYear;

class GraduationController extends Controller
{
    /**
     * Display all graduated students.
     */
    public function index(Request $request)
    {
        $query = StudentClassAssignment::with(['student', 'studentClass', 'academicYear'])
            ->where('status', 'Graduated');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('student_id', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by Class
        if ($request->filled('class_id')) {
            $query->where('student_class_id', $request->class_id);
        }
        
        // Filter by Academic Year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        // Sort
        $sortField = $request->get('sort', 'updated_at');
        $sortDirection = $request->get('direction', 'desc');
        
        switch($sortField) {
            case 'student_id':
                $query->join('students', 'student_class_assignments.student_id', '=', 'students.id')
                    ->orderBy('students.student_id', $sortDirection)
                    ->select('student_class_assignments.*');
                break;
            case 'name':
                $query->join('students', 'student_class_assignments.student_id', '=', 'students.id')
                    ->orderBy('students.full_name', $sortDirection)
                    ->select('student_class_assignments.*');
                break;
            case 'class':
                $query->join('student_classes', 'student_class_assignments.student_class_id', '=', 'student_classes.id')
                    ->orderBy('student_classes.name', $sortDirection)
                    ->select('student_class_assignments.*');
                break;
            case 'academic_year':
                $query->join('academic_years', 'student_class_assignments.academic_year_id', '=', 'academic_years.id')
                    ->orderBy('academic_years.name', $sortDirection)
                    ->select('student_class_assignments.*');
                break;
            case 'graduation_date':
                $query->orderBy('student_class_assignments.updated_at', $sortDirection);
                break;
            default:
                $query->orderBy('student_class_assignments.updated_at', 'desc');
        }
        
        $graduates = $query->paginate(15);
        $classes = StudentClass::all();
        $academicYears = AcademicYear::all();
        
        return view('graduated-students.index', compact('graduates', 'classes', 'academicYears'));
    }

    /**
     * Show a single graduate record.
     */
    public function show($id)
    {
        $graduate = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear'
        ])->where('status', 'Graduated')->findOrFail($id);

        return view('graduated-students.show', compact('graduate'));
    }

    /**
     * Export graduates to CSV
     */
    public function export(Request $request)
    {
        $query = StudentClassAssignment::with(['student', 'studentClass', 'academicYear'])
            ->where('status', 'Graduated');
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('student_id', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('class_id')) {
            $query->where('student_class_id', $request->class_id);
        }
        
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        $graduates = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="graduates-' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($graduates) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'Student ID', 
                'Full Name', 
                'Email', 
                'Phone', 
                'Class', 
                'Academic Year', 
                'Graduation Date'
            ]);
            
            foreach ($graduates as $graduate) {
                fputcsv($file, [
                    $graduate->student->student_id ?? 'N/A',
                    $graduate->student->full_name ?? 'N/A',
                    $graduate->student->email ?? 'N/A',
                    $graduate->student->phone ?? 'N/A',
                    $graduate->studentClass->name ?? 'N/A',
                    $graduate->academicYear->name ?? 'N/A',
                    $graduate->updated_at ? $graduate->updated_at->format('Y-m-d') : 'N/A',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print view for graduates
     */
    public function printView(Request $request)
    {
        $query = StudentClassAssignment::with(['student', 'studentClass', 'academicYear'])
            ->where('status', 'Graduated');
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('student_id', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('class_id')) {
            $query->where('student_class_id', $request->class_id);
        }
        
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        $graduates = $query->get();
        
        
    }

    /**
     * Download certificate for a graduate
     */
    public function certificate($id)
    {
        $graduate = StudentClassAssignment::with([
            'student', 
            'studentClass', 
            'academicYear'
        ])->where('status', 'Graduated')->findOrFail($id);
        
        return view('graduated-students.certificate', compact('graduate'));
    }

    /**
     * Graduate a student.
     */
    public function graduate(Request $request, $assignmentId)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000'
        ]);

        $assignment = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear'
        ])->findOrFail($assignmentId);

        if ($assignment->status === 'Graduated') {
            return back()->with(
                'error',
                'Student has already been graduated.'
            );
        }

        $assignment->update([
            'status' => 'Graduated',
            'is_current' => false,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('graduates.index')
            ->with('success', 'Student graduated successfully.');
    }

    /**
     * Restore a graduated student (mark as active again).
     */
    public function restore($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);

        if ($assignment->status !== 'Graduated') {
            return back()->with('error', 'This student is not graduated.');
        }

        $assignment->update([
            'status' => 'Active',
            'is_current' => true,
        ]);

        return back()->with(
            'success',
            'Graduate restored successfully.'
        );
    }

    /**
     * Delete graduate record.
     */
    public function destroy($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);
        
        // You might want to just update status instead of deleting
        $assignment->update([
            'status' => 'Active',
            'is_current' => true,
        ]);

        return back()->with(
            'success',
            'Graduate record deleted successfully.'
        );
    }
}