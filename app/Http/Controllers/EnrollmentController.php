<?php

namespace App\Http\Controllers;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\StudentEnrollment;
use App\Models\Term;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * DISPLAY ALL ENROLLMENTS
     */
    public function index()
    {
        $enrollments = StudentEnrollment::with([
            'student',
            'studentClass',
            'academicYear',
            'term'
        ])->latest()->get();

        return view('enrollments.index', compact('enrollments'));
    }

    /**
     * SHOW CREATE FORM
     */
    public function create()
    {
        return view('enrollments.create', [
            'students' => Student::orderBy('first_name')->get(),
            'classes' => StudentClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::latest()->get(),
            'terms' => Term::orderBy('name')->get(),
        ]);
    }

    /**
     * STORE ENROLLMENT
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'enrollment_date' => 'nullable|date',
        ]);

        /*
        |--------------------------------------------------------------------------
        | PREVENT DUPLICATE IN SAME CLASS
        |--------------------------------------------------------------------------
        */

        $exists = StudentEnrollment::where('student_id', $validated['student_id'])
            ->where('student_class_id', $validated['student_class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('term_id', $validated['term_id'] ?? null)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'student_id' => 'This student is already enrolled in this class for this term.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DEACTIVATE CURRENT ENROLLMENT (ONLY ONE ACTIVE CLASS)
        |--------------------------------------------------------------------------
        */

        StudentEnrollment::where('student_id', $validated['student_id'])
            ->where('is_current', true)
            ->update([
                'is_current' => false
            ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE NEW ENROLLMENT
        |--------------------------------------------------------------------------
        */

        StudentEnrollment::create([
            'student_id' => $validated['student_id'],
            'student_class_id' => $validated['student_class_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'term_id' => $validated['term_id'] ?? null,
            'enrollment_date' => $validated['enrollment_date'] ?? now(),
            'is_current' => true,
        ]);

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Student enrolled successfully');
    }

    /**
     * SHOW SINGLE ENROLLMENT
     */
    public function show(StudentEnrollment $enrollment)
    {
        $enrollment->load([
            'student',
            'studentClass',
            'academicYear',
            'term'
        ]);

        return view('enrollments.show', compact('enrollment'));
    }

    /**
     * EDIT FORM
     */
    public function edit(StudentEnrollment $enrollment)
    {
        return view('enrollments.edit', [
            'enrollment' => $enrollment,
            'students' => Student::orderBy('first_name')->get(),
            'classes' => StudentClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::latest()->get(),
            'terms' => Term::orderBy('name')->get(),
        ]);
    }

    /**
     * UPDATE ENROLLMENT
     */
    public function update(Request $request, StudentEnrollment $enrollment)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'enrollment_date' => 'nullable|date',
            'is_current' => 'boolean',
        ]);

        $enrollment->update($validated);

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Enrollment updated successfully');
    }

    /**
     * DELETE ENROLLMENT
     */
    public function destroy(StudentEnrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Enrollment deleted successfully');
    }
}