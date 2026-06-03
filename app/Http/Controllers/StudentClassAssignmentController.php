<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;

class StudentClassAssignmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX - SHOW ALL ASSIGNMENTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $assignments = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear'
        ])->latest()->get();

        return view(
            'student_class_assignments.index',
            compact('assignments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $students = Student::orderBy('first_name')->get();
        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();

        return view(
            'student_class_assignments.create',
            compact('students', 'classes', 'academicYears')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'student_id'        => 'required|exists:students,id',
            'student_class_id'  => 'required|exists:student_classes,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
        ]);

        /*
        |----------------------------------------------------------
        | STEP 1: DEACTIVATE CURRENT ASSIGNMENT
        |----------------------------------------------------------
        */
        StudentClassAssignment::where('student_id', $request->student_id)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'status'     => 'inactive'
            ]);

        /*
        |----------------------------------------------------------
        | STEP 2: CREATE NEW ASSIGNMENT
        |----------------------------------------------------------
        */
        StudentClassAssignment::create([
            'student_id'        => $request->student_id,
            'student_class_id'  => $request->student_class_id,
            'academic_year_id'  => $request->academic_year_id,
            'status'            => 'active',
            'is_current'        => true,
            'assigned_date'     => now(),
        ]);

        return redirect()
            ->route('student-class-assignments.index')
            ->with('success', 'Student assigned successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $assignment = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear'
        ])->findOrFail($id);

        return view(
            'student_class_assignments.show',
            compact('assignment')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $assignment = StudentClassAssignment::findOrFail($id);

        $classes = StudentClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();

        return view(
            'student_class_assignments.edit',
            compact('assignment', 'classes', 'academicYears')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (PROMOTION SAFE)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'student_class_id' => 'required|exists:student_classes,id',
        ]);

        $assignment = StudentClassAssignment::findOrFail($id);

        $assignment->update([
            'student_class_id' => $request->student_class_id,
            'status' => 'updated'
        ]);

        return redirect()
            ->route('student_class_assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE (SOFT LOGICAL REMOVE)
    |--------------------------------------------------------------------------
    */
    


    public function destroy($id)
{
    $assignment = StudentClassAssignment::findOrFail($id);
    $assignment->delete();

    return back()->with('success', 'Assignment deleted successfully.');
}
}