<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * DISPLAY ALL STUDENTS
     */
    public function index()
    {
        $students = Student::orderBy('created_at', 'desc')->paginate(20);
        return view('students.index', compact('students'));
    }

    /**
     * SHOW CREATE FORM
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * STORE NEW STUDENT
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            // PERSONAL INFO
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',

            'nationality' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'address' => 'nullable|string',

            // DISABILITY
            'has_disability' => 'required|boolean',
            'disability_type' => 'nullable|string|max:255',

            // FATHER
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:30',
            'father_email' => 'nullable|email|max:255',
            'father_occupation' => 'nullable|string|max:255',

            // MOTHER
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:30',
            'mother_email' => 'nullable|email|max:255',
            'mother_occupation' => 'nullable|string|max:255',

            // GUARDIAN
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_email' => 'nullable|email|max:255',

            // SCHOOL INFO
            'admission_date' => 'nullable|date',

            // PHOTO
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // HANDLE PHOTO UPLOAD
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        // DEFAULT ACTIVE STATUS
        $validated['is_active'] = true;

        Student::create($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student created successfully');
    }

    /**
     * SHOW SINGLE STUDENT
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /**
     * EDIT FORM
     */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * UPDATE STUDENT
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([

            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'nullable|date',

            'nationality' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'address' => 'nullable|string',

            'has_disability' => 'required|boolean',
            'disability_type' => 'nullable|string|max:255',

            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:30',
            'father_email' => 'nullable|email|max:255',
            'father_occupation' => 'nullable|string|max:255',

            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:30',
            'mother_email' => 'nullable|email|max:255',
            'mother_occupation' => 'nullable|string|max:255',

            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_email' => 'nullable|email|max:255',

            'admission_date' => 'required|date',

            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // UPDATE PHOTO
        if ($request->hasFile('photo')) {

            // delete old photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully');
    }

    /**
     * DELETE STUDENT
     */
    public function destroy(Student $student)
    {
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully');
    }

        public function getClassStudents($classId)
    {
        $students = Student::where('student_class_id', $classId)
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }
}