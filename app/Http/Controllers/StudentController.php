<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'admission_date' => 'required|date',

            // PHOTO
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // HANDLE PHOTO UPLOAD
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        // DEFAULT ACTIVE STATUS
        $validated['is_active'] = true;

        // =============================================
        // FIX: GENERATE STUDENT_ID
        // =============================================
        // Option 1: Generate a unique student ID
        $validated['student_id'] = $this->generateStudentId();

        // Option 2: Or use the format: STU + Year + Random numbers
        // $validated['student_id'] = 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Option 3: Or use the format: YYYY-XXXXX (Year + sequential number)
        // $latestStudent = Student::orderBy('id', 'desc')->first();
        // $nextNumber = $latestStudent ? (intval(substr($latestStudent->student_id, -5)) + 1) : 1;
        // $validated['student_id'] = date('Y') . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // OPTIONAL: Set default values for nullable fields that might be required
        $validated['disability_type'] = $validated['disability_type'] ?? null;
        $validated['father_occupation'] = $validated['father_occupation'] ?? null;
        $validated['mother_occupation'] = $validated['mother_occupation'] ?? null;
        $validated['guardian_name'] = $validated['guardian_name'] ?? null;
        $validated['guardian_phone'] = $validated['guardian_phone'] ?? null;
        $validated['guardian_email'] = $validated['guardian_email'] ?? null;

        Student::create($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student created successfully');
    }

    /**
     * GENERATE UNIQUE STUDENT ID
     */
    private function generateStudentId(): string
    {
        // Format: STU + Year + 5 digit sequential number
        $year = date('Y');
        $prefix = 'STD-' . $year;

        // Get the latest student with this year's prefix
        $latestStudent = Student::where('student_id', 'like', $prefix . '%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($latestStudent) {
            // Extract the number from the last student ID
            $lastNumber = intval(substr($latestStudent->student_id, -5));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad with leading zeros to 5 digits
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * SHOW SINGLE STUDENT
     */
    public function show($id)
    {
        $student = Student::with([
            'classAssignments.studentClass',
            'classAssignments.academicYear',
            'studentResults.academicYear',
            'studentResults.term'
        ])->findOrFail($id);

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

    /**
     * GET STUDENTS BY CLASS
     */
    public function getClassStudents($classId)
    {
        $students = Student::whereHas('classAssignments', function ($query) use ($classId) {
            $query->where('student_class_id', $classId)
                ->whereNull('end_date');
        })
        ->orderBy('first_name')
        ->get(['id', 'student_id', 'first_name', 'middle_name', 'last_name']);

        // Format the response
        $formattedStudents = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => $student->full_name ?? $student->first_name . ' ' . $student->last_name,
            ];
        });

        return response()->json($formattedStudents);
    }
}