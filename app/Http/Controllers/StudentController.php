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
        $students = Student::orderBy('created_at', 'desc')
            ->paginate(20);

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
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',

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

        /*
        |--------------------------------------------------------------------------
        | PHOTO
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request
                ->file('photo')
                ->store('students', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT VALUES
        |--------------------------------------------------------------------------
        */
        $validated['is_active'] = true;
        $validated['student_id'] = $this->generateStudentId();

        $validated['disability_type'] =
            $validated['disability_type'] ?? null;

        $validated['father_occupation'] =
            $validated['father_occupation'] ?? null;

        $validated['mother_occupation'] =
            $validated['mother_occupation'] ?? null;

        $validated['guardian_name'] =
            $validated['guardian_name'] ?? null;

        $validated['guardian_phone'] =
            $validated['guardian_phone'] ?? null;

        $validated['guardian_email'] =
            $validated['guardian_email'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | CREATE STUDENT
        |--------------------------------------------------------------------------
        */
        Student::create($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * GENERATE UNIQUE STUDENT ID
     *
     * Format:
     * STD-20260001
     */
    private function generateStudentId(): string
    {
        $year = date('Y');
        $prefix = 'STD-' . $year;

        $latestStudent = Student::where(
            'student_id',
            'like',
            $prefix . '%'
        )
            ->orderBy('student_id', 'desc')
            ->first();

        if ($latestStudent) {
            $lastNumber = (int) substr(
                $latestStudent->student_id,
                -4
            );

            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad(
            $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );
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
            'studentResults.term',
        ])->findOrFail($id);

        return view('students.show', compact('student'));
    }

    /**
     * SHOW EDIT FORM
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

        /*
        |--------------------------------------------------------------------------
        | UPDATE PHOTO
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('photo')) {

            if ($student->photo) {
                Storage::disk('public')->delete(
                    $student->photo
                );
            }

            $validated['photo'] = $request
                ->file('photo')
                ->store('students', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE STUDENT
        |--------------------------------------------------------------------------
        */
        $student->update($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * DELETE STUDENT
     *
     * IMPORTANT:
     * ONLY SUPER ADMIN CAN DELETE STUDENTS.
     */
    public function destroy(Student $student)
    {
        /*
        |--------------------------------------------------------------------------
        | RBAC SECURITY CHECK
        |--------------------------------------------------------------------------
        |
        | Do NOT use:
        |
        | auth()->user()->role
        |
        | We use Spatie roles instead.
        |
        */
        $user = auth()->user();

        if (!$user || !$user->hasRole('Super Admin')) {
            abort(
                403,
                'Only the Super Admin can delete students.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE STUDENT PHOTO
        |--------------------------------------------------------------------------
        */
        if ($student->photo) {
            Storage::disk('public')->delete(
                $student->photo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE STUDENT
        |--------------------------------------------------------------------------
        */
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                'Student deleted successfully.'
            );
    }

    /**
     * GET STUDENTS BY CLASS
     */
    public function getClassStudents($classId)
    {
        $students = Student::whereHas(
            'classAssignments',
            function ($query) use ($classId) {
                $query
                    ->where('student_class_id', $classId)
                    ->whereNull('end_date');
            }
        )
            ->orderBy('first_name')
            ->get([
                'id',
                'student_id',
                'first_name',
                'middle_name',
                'last_name',
            ]);

        $formattedStudents = $students->map(
            function ($student) {
                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'name' => $student->full_name
                        ?? trim(
                            $student->first_name
                            . ' '
                            . $student->middle_name
                            . ' '
                            . $student->last_name
                        ),
                ];
            }
        );

        return response()->json($formattedStudents);
    }
}