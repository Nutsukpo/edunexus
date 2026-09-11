<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\SystemSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StudentClassAssignmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $settings = SystemSetting::instance();

        $query = StudentClassAssignment::with([
            'student',
            'studentClass',
            'academicYear',
            'term',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR FILTER
        |--------------------------------------------------------------------------
        */
        if ($request->filled('academic_year_id')) {
            $query->where(
                'academic_year_id',
                $request->academic_year_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TERM FILTER
        |--------------------------------------------------------------------------
        */
        if ($request->filled('term_id')) {
            $query->where(
                'term_id',
                $request->term_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT FILTER
        |--------------------------------------------------------------------------
        */
        if ($request->filled('is_current')) {
            $query->where(
                'is_current',
                $request->is_current
            );
        }

        $assignments = $query
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        $terms = Term::query()
            ->when(
                $request->filled('academic_year_id'),
                fn ($q) => $q->where(
                    'academic_year_id',
                    $request->academic_year_id
                )
            )
            ->orderBy('start_date')
            ->get();

        return view(
            'student_class_assignments.index',
            compact(
                'assignments',
                'academicYears',
                'terms',
                'settings'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $settings = SystemSetting::instance();

        $currentAcademicYear =
            $settings->currentAcademicYear;

        $currentTerm =
            $settings->currentTerm;

        $students = Student::query()
            ->whereNotIn(
                'id',
                StudentClassAssignment::where(
                    'status',
                    'graduated'
                )->pluck('student_id')
            )
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        $terms = collect();

        if ($currentAcademicYear) {
            $terms = Term::query()
                ->where(
                    'academic_year_id',
                    $currentAcademicYear->id
                )
                ->where('is_active', true)
                ->orderBy('start_date')
                ->get();
        }

        return view(
            'student_class_assignments.create',
            compact(
                'students',
                'classes',
                'academicYears',
                'terms',
                'settings',
                'currentAcademicYear',
                'currentTerm'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | Academic year and term come from System Settings.
    | The form only needs student + class.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
            ],

            'student_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'status' => [
                'nullable',
                'in:active,repeated',
            ],

            'assigned_date' => [
                'nullable',
                'date',
            ],
        ]);

        $settings = SystemSetting::instance();

        $academicYear =
            $settings->currentAcademicYear;

        $term =
            $settings->currentTerm;

        /*
        |--------------------------------------------------------------------------
        | SYSTEM PERIOD MUST BE CONFIGURED
        |--------------------------------------------------------------------------
        */
        if (!$academicYear || !$term) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current academic year and term have not been configured in System Settings.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATE TERM/YEAR RELATIONSHIP
        |--------------------------------------------------------------------------
        */
        if (
            $term->academic_year_id !==
            $academicYear->id
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current term does not belong to the current academic year.'
                );
        }

        if (!$academicYear->is_active) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current academic year is not active.'
                );
        }

        if (!$term->is_active) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current term is not active.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT
        |--------------------------------------------------------------------------
        */
        $student = Student::findOrFail(
            $validated['student_id']
        );

        /*
        |--------------------------------------------------------------------------
        | GRADUATED STUDENT CHECK
        |--------------------------------------------------------------------------
        */
        $isGraduated =
            StudentClassAssignment::where(
                'student_id',
                $student->id
            )
                ->where('status', 'graduated')
                ->exists();

        if ($isGraduated) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'A graduated student cannot be assigned to a class.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CURRENT PERIOD CHECK
        |--------------------------------------------------------------------------
        */
        $duplicate =
            StudentClassAssignment::where(
                'student_id',
                $student->id
            )
                ->where(
                    'student_class_id',
                    $validated['student_class_id']
                )
                ->where(
                    'academic_year_id',
                    $academicYear->id
                )
                ->where(
                    'term_id',
                    $term->id
                )
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                )
                ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This student already has this class assignment for the current academic period.'
                );
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | RETIRE EXISTING CURRENT ASSIGNMENT
            |--------------------------------------------------------------------------
            |
            | Manual reassignment is different from automatic rollover.
            | We preserve the old record but mark it inactive.
            |--------------------------------------------------------------------------
            */
            StudentClassAssignment::where(
                'student_id',
                $student->id
            )
                ->where('is_current', true)
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                )
                ->update([
                    'is_current' => false,
                    'status' => 'inactive',
                ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE CURRENT ASSIGNMENT
            |--------------------------------------------------------------------------
            */
            $assignment =
                StudentClassAssignment::create([
                    'student_id' =>
                        $student->id,

                    'student_class_id' =>
                        $validated['student_class_id'],

                    'academic_year_id' =>
                        $academicYear->id,

                    'term_id' =>
                        $term->id,

                    'status' =>
                        $validated['status']
                        ?? 'active',

                    'is_current' => true,

                    'assigned_date' =>
                        $validated['assigned_date']
                        ?? now(),
                ]);

            DB::commit();

            return redirect()
                ->route(
                    'student-class-assignments.show',
                    $assignment->id
                )
                ->with(
                    'success',
                    "Student assigned successfully to {$assignment->studentClass->name} for {$academicYear->name} — {$term->name}."
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Student assignment creation error',
                [
                    'message' => $e->getMessage(),
                    'student_id' => $student->id,
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the student assignment.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $assignment =
            StudentClassAssignment::with([
                'student',
                'studentClass',
                'academicYear',
                'term',
            ])->findOrFail($id);

        $settings =
            SystemSetting::instance();

        return view(
            'student_class_assignments.show',
            compact(
                'assignment',
                'settings'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $assignment =
            StudentClassAssignment::with([
                'student',
                'studentClass',
                'academicYear',
                'term',
            ])->findOrFail($id);

        if ($assignment->status === 'graduated') {
            return redirect()
                ->route(
                    'student-class-assignments.show',
                    $assignment->id
                )
                ->with(
                    'error',
                    'Graduated assignments cannot be edited.'
                );
        }

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        $terms = Term::query()
            ->where(
                'academic_year_id',
                $assignment->academic_year_id
            )
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();

        $settings =
            SystemSetting::instance();

        return view(
            'student_class_assignments.edit',
            compact(
                'assignment',
                'classes',
                'academicYears',
                'terms',
                'settings'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $assignment =
            StudentClassAssignment::findOrFail($id);

        if ($assignment->status === 'graduated') {
            return back()
                ->with(
                    'error',
                    'Cannot update a graduated student\'s assignment.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | Historical assignments may need to remain historical.
        | Therefore editing an old record is allowed, but the period
        | itself must remain valid.
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'exists:terms,id',
            ],

            'status' => [
                'nullable',
                'in:active,repeated',
            ],

            'assigned_date' => [
                'nullable',
                'date',
            ],
        ]);

        $academicYear =
            AcademicYear::findOrFail(
                $validated['academic_year_id']
            );

        if (!$academicYear->is_active) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected academic year is not active.'
                );
        }

        $term = Term::query()
            ->where(
                'id',
                $validated['term_id']
            )
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->where('is_active', true)
            ->first();

        if (!$term) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected term does not belong to the selected academic year or is not active.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */
        $duplicate =
            StudentClassAssignment::where(
                'student_id',
                $assignment->student_id
            )
                ->where(
                    'student_class_id',
                    $validated['student_class_id']
                )
                ->where(
                    'academic_year_id',
                    $validated['academic_year_id']
                )
                ->where(
                    'term_id',
                    $validated['term_id']
                )
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                )
                ->where(
                    'id',
                    '!=',
                    $assignment->id
                )
                ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This student already has an active assignment for this class, academic year and term.'
                );
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | IF THIS IS CURRENT
            |--------------------------------------------------------------------------
            */
            if ($assignment->is_current) {

                StudentClassAssignment::where(
                    'student_id',
                    $assignment->student_id
                )
                    ->where(
                        'is_current',
                        true
                    )
                    ->where(
                        'id',
                        '!=',
                        $assignment->id
                    )
                    ->whereIn(
                        'status',
                        ['active', 'repeated']
                    )
                    ->update([
                        'is_current' => false,
                        'status' => 'inactive',
                    ]);
            }

            $assignment->update([
                'student_class_id' =>
                    $validated['student_class_id'],

                'academic_year_id' =>
                    $validated['academic_year_id'],

                'term_id' =>
                    $validated['term_id'],

                'status' =>
                    $validated['status']
                    ?? $assignment->status,

                'assigned_date' =>
                    $validated['assigned_date']
                    ?? $assignment->assigned_date,
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'student-class-assignments.show',
                    $assignment->id
                )
                ->with(
                    'success',
                    'Assignment updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Assignment update error',
                [
                    'message' => $e->getMessage(),
                    'assignment_id' => $assignment->id,
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the assignment.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $assignment =
            StudentClassAssignment::findOrFail($id);

        if ($assignment->status === 'graduated') {
            return back()
                ->with(
                    'error',
                    'Cannot delete a graduated student\'s assignment.'
                );
        }

        DB::beginTransaction();

        try {

            $assignment->delete();

            DB::commit();

            return redirect()
                ->route(
                    'student-class-assignments.index'
                )
                ->with(
                    'success',
                    'Assignment deleted successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Assignment deletion error',
                [
                    'message' => $e->getMessage(),
                    'assignment_id' => $id,
                ]
            );

            return back()
                ->with(
                    'error',
                    'Unable to delete the assignment.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GET STUDENTS BY CLASS
    |--------------------------------------------------------------------------
    */
    public function getStudentsByClass(Request $request)
    {
        $validated = $request->validate([
            'class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'nullable',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'exists:terms,id',
            ],
        ]);

        $query =
            StudentClassAssignment::with('student')
                ->where(
                    'student_class_id',
                    $validated['class_id']
                )
                ->where(
                    'is_current',
                    true
                )
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                );

        if (!empty($validated['academic_year_id'])) {
            $query->where(
                'academic_year_id',
                $validated['academic_year_id']
            );
        }

        if (!empty($validated['term_id'])) {
            $query->where(
                'term_id',
                $validated['term_id']
            );
        }

        $students = $query
            ->get()
            ->map(function ($assignment) {

                return [
                    'id' =>
                        $assignment->student->id,

                    'student_id' =>
                        $assignment->student->student_id,

                    'full_name' =>
                        $assignment->student->full_name,

                    'email' =>
                        $assignment->student->email ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GET AVAILABLE STUDENTS
    |--------------------------------------------------------------------------
    */
    public function getAvailableStudents(Request $request)
    {
        $settings =
            SystemSetting::instance();

        $academicYearId =
            $request->input(
                'academic_year_id',
                $settings->current_academic_year_id
            );

        $termId =
            $request->input(
                'term_id',
                $settings->current_term_id
            );

        /*
        |--------------------------------------------------------------------------
        | GRADUATED
        |--------------------------------------------------------------------------
        */
        $graduatedStudentIds =
            StudentClassAssignment::where(
                'status',
                'graduated'
            )
                ->pluck('student_id')
                ->unique();

        /*
        |--------------------------------------------------------------------------
        | CURRENTLY ASSIGNED IN PERIOD
        |--------------------------------------------------------------------------
        */
        $assignedStudentIds =
            StudentClassAssignment::query()
                ->where(
                    'is_current',
                    true
                )
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                )
                ->when(
                    $academicYearId,
                    fn ($query) =>
                        $query->where(
                            'academic_year_id',
                            $academicYearId
                        )
                )
                ->when(
                    $termId,
                    fn ($query) =>
                        $query->where(
                            'term_id',
                            $termId
                        )
                )
                ->pluck('student_id')
                ->unique();

        $excludedIds =
            $graduatedStudentIds
                ->merge($assignedStudentIds)
                ->unique()
                ->values();

        $students =
            Student::query()
                ->when(
                    $excludedIds->isNotEmpty(),
                    fn ($query) =>
                        $query->whereNotIn(
                            'id',
                            $excludedIds
                        )
                )
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get([
                    'id',
                    'student_id',
                    'first_name',
                    'last_name',
                    'email',
                ]);

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BULK ASSIGN
    |--------------------------------------------------------------------------
    |
    | Uses System Settings automatically.
    |--------------------------------------------------------------------------
    */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'student_ids.*' => [
                'exists:students,id',
            ],

            'student_class_id' => [
                'required',
                'exists:student_classes,id',
            ],
        ]);

        $settings =
            SystemSetting::instance();

        $academicYear =
            $settings->currentAcademicYear;

        $term =
            $settings->currentTerm;

        if (!$academicYear || !$term) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current academic year and term have not been configured in System Settings.'
                );
        }

        if (
            $term->academic_year_id !==
            $academicYear->id
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current term does not belong to the current academic year.'
                );
        }

        if (
            !$academicYear->is_active ||
            !$term->is_active
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The current academic year or term is inactive.'
                );
        }

        $processed = 0;
        $errors = [];
        $skippedGraduated = [];

        DB::beginTransaction();

        try {

            foreach (
                $validated['student_ids']
                as $studentId
            ) {

                /*
                |--------------------------------------------------------------------------
                | GRADUATED
                |--------------------------------------------------------------------------
                */
                $isGraduated =
                    StudentClassAssignment::where(
                        'student_id',
                        $studentId
                    )
                        ->where(
                            'status',
                            'graduated'
                        )
                        ->exists();

                if ($isGraduated) {

                    $student =
                        Student::find($studentId);

                    $skippedGraduated[] =
                        $student->full_name
                        ?? $studentId;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DUPLICATE
                |--------------------------------------------------------------------------
                */
                $duplicate =
                    StudentClassAssignment::where(
                        'student_id',
                        $studentId
                    )
                        ->where(
                            'student_class_id',
                            $validated['student_class_id']
                        )
                        ->where(
                            'academic_year_id',
                            $academicYear->id
                        )
                        ->where(
                            'term_id',
                            $term->id
                        )
                        ->whereIn(
                            'status',
                            ['active', 'repeated']
                        )
                        ->exists();

                if ($duplicate) {

                    $student =
                        Student::find($studentId);

                    $errors[] =
                        (
                            $student->full_name
                            ?? $studentId
                        )
                        . ' already has this assignment.';

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | RETIRE CURRENT ASSIGNMENT
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::where(
                    'student_id',
                    $studentId
                )
                    ->where(
                        'is_current',
                        true
                    )
                    ->whereIn(
                        'status',
                        ['active', 'repeated']
                    )
                    ->update([
                        'is_current' => false,
                        'status' => 'inactive',
                    ]);

                /*
                |--------------------------------------------------------------------------
                | CREATE
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::create([
                    'student_id' =>
                        $studentId,

                    'student_class_id' =>
                        $validated['student_class_id'],

                    'academic_year_id' =>
                        $academicYear->id,

                    'term_id' =>
                        $term->id,

                    'status' =>
                        'active',

                    'is_current' =>
                        true,

                    'assigned_date' =>
                        $term->start_date
                        ?? now(),
                ]);

                $processed++;
            }

            DB::commit();

            $message =
                "{$processed} students assigned successfully.";

            if (!empty($skippedGraduated)) {
                $message .=
                    ' Skipped '
                    . count($skippedGraduated)
                    . ' graduated students.';
            }

            if (!empty($errors)) {
                $message .=
                    ' '
                    . count($errors)
                    . ' students were skipped because they already had the selected assignment.';
            }

            return redirect()
                ->route(
                    'student-class-assignments.index'
                )
                ->with(
                    'success',
                    $message
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Bulk assignment error',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to complete the bulk assignment.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DEACTIVATE
    |--------------------------------------------------------------------------
    */
    public function deactivate($id)
    {
        $assignment =
            StudentClassAssignment::findOrFail($id);

        if ($assignment->status === 'graduated') {
            return back()
                ->with(
                    'error',
                    'Cannot deactivate a graduated student\'s assignment.'
                );
        }

        DB::beginTransaction();

        try {

            $assignment->update([
                'is_current' => false,
                'status' => 'inactive',
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'student-class-assignments.index'
                )
                ->with(
                    'success',
                    'Assignment deactivated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Assignment deactivation error',
                [
                    'message' =>
                        $e->getMessage(),

                    'assignment_id' =>
                        $id,
                ]
            );

            return back()
                ->with(
                    'error',
                    'Unable to deactivate the assignment.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REACTIVATE
    |--------------------------------------------------------------------------
    */
    public function reactivate($id)
    {
        $assignment =
            StudentClassAssignment::findOrFail($id);

        if ($assignment->status === 'graduated') {
            return back()
                ->with(
                    'error',
                    'Cannot reactivate a graduated student\'s assignment.'
                );
        }

        if (
            !$assignment->academic_year_id ||
            !$assignment->term_id
        ) {
            return back()
                ->with(
                    'error',
                    'This historical assignment has no academic term. It must be corrected before it can be reactivated.'
                );
        }

        $existing =
            StudentClassAssignment::where(
                'student_id',
                $assignment->student_id
            )
                ->where(
                    'is_current',
                    true
                )
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                )
                ->where(
                    'id',
                    '!=',
                    $assignment->id
                )
                ->first();

        DB::beginTransaction();

        try {

            if ($existing) {
                $existing->update([
                    'is_current' => false,
                    'status' => 'inactive',
                ]);
            }

            $assignment->update([
                'is_current' => true,
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'student-class-assignments.index'
                )
                ->with(
                    'success',
                    'Assignment reactivated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Assignment reactivation error',
                [
                    'message' =>
                        $e->getMessage(),

                    'assignment_id' =>
                        $id,
                ]
            );

            return back()
                ->with(
                    'error',
                    'Unable to reactivate the assignment.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT HISTORY
    |--------------------------------------------------------------------------
    */
    public function getStudentHistory($studentId)
    {
        $student =
            Student::findOrFail($studentId);

        $assignments =
            StudentClassAssignment::with([
                'studentClass',
                'academicYear',
                'term',
            ])
                ->where(
                    'student_id',
                    $studentId
                )
                ->orderByDesc('created_at')
                ->get();

        return view(
            'student_class_assignments.history',
            compact(
                'student',
                'assignments'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE STATISTICS
    |--------------------------------------------------------------------------
    */
    public function getAttendanceStats($classId)
    {
        try {

            $assignments =
                StudentClassAssignment::with(
                    'student'
                )
                    ->where(
                        'student_class_id',
                        $classId
                    )
                    ->where(
                        'is_current',
                        true
                    )
                    ->whereIn(
                        'status',
                        ['active', 'repeated']
                    )
                    ->get();

            $studentIds =
                $assignments
                    ->pluck('student_id')
                    ->toArray();

            $totalStudents =
                count($studentIds);

            $sessions =
                AttendanceSession::where(
                    'student_class_id',
                    $classId
                )
                    ->orderByDesc(
                        'attendance_date'
                    )
                    ->get();

            $totalSessions =
                $sessions->count();

            $attendanceData = [];

            foreach (
                $assignments as $assignment
            ) {

                $student =
                    $assignment->student;

                $studentAttendance =
                    Attendance::where(
                        'student_id',
                        $student->id
                    )
                        ->whereHas(
                            'attendanceSession',
                            function ($query) use (
                                $classId
                            ) {
                                $query->where(
                                    'student_class_id',
                                    $classId
                                );
                            }
                        )
                        ->get();

                $present =
                    $studentAttendance
                        ->where(
                            'status',
                            'present'
                        )
                        ->count();

                $absent =
                    $studentAttendance
                        ->where(
                            'status',
                            'absent'
                        )
                        ->count();

                $late =
                    $studentAttendance
                        ->where(
                            'status',
                            'late'
                        )
                        ->count();

                $excused =
                    $studentAttendance
                        ->where(
                            'status',
                            'excused'
                        )
                        ->count();

                $total =
                    $studentAttendance->count();

                $attendanceData[] = [
                    'student_id' =>
                        $student->id,

                    'student_name' =>
                        trim(
                            $student->first_name
                            . ' '
                            . $student->last_name
                        ),

                    'student_number' =>
                        $student->student_id,

                    'present' =>
                        $present,

                    'absent' =>
                        $absent,

                    'late' =>
                        $late,

                    'excused' =>
                        $excused,

                    'total' =>
                        $total,

                    'rate' =>
                        $total > 0
                            ? round(
                                (
                                    $present
                                    / $total
                                ) * 100,
                                1
                            )
                            : 0,
                ];
            }

            $collection =
                collect($attendanceData);

            return response()->json([
                'success' => true,

                'total_students' =>
                    $totalStudents,

                'total_sessions' =>
                    $totalSessions,

                'class_average' =>
                    round(
                        $collection->avg(
                            'rate'
                        ) ?? 0,
                        1
                    ),

                'total_present' =>
                    $collection->sum(
                        'present'
                    ),

                'total_absent' =>
                    $collection->sum(
                        'absent'
                    ),

                'total_late' =>
                    $collection->sum(
                        'late'
                    ),

                'total_excused' =>
                    $collection->sum(
                        'excused'
                    ),

                'total_records' =>
                    $collection->sum(
                        'total'
                    ),

                'students' =>
                    $attendanceData,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Attendance stats error',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Error fetching attendance statistics: '
                    . $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function getStudentAttendance(
        $studentId,
        $classId = null
    ) {
        try {

            $student =
                Student::findOrFail(
                    $studentId
                );

            $query =
                Attendance::with([
                    'attendanceSession.studentClass',
                ])
                    ->where(
                        'student_id',
                        $studentId
                    );

            if ($classId) {

                $query->whereHas(
                    'attendanceSession',
                    function ($q) use (
                        $classId
                    ) {
                        $q->where(
                            'student_class_id',
                            $classId
                        );
                    }
                );
            }

            $records =
                $query
                    ->orderByDesc(
                        'created_at'
                    )
                    ->get();

            $stats = [
                'present' =>
                    $records
                        ->where(
                            'status',
                            'present'
                        )
                        ->count(),

                'absent' =>
                    $records
                        ->where(
                            'status',
                            'absent'
                        )
                        ->count(),

                'late' =>
                    $records
                        ->where(
                            'status',
                            'late'
                        )
                        ->count(),

                'excused' =>
                    $records
                        ->where(
                            'status',
                            'excused'
                        )
                        ->count(),

                'total' =>
                    $records->count(),

                'rate' =>
                    $records->count() > 0
                        ? round(
                            (
                                $records
                                    ->where(
                                        'status',
                                        'present'
                                    )
                                    ->count()
                                / $records->count()
                            ) * 100,
                            1
                        )
                        : 0,
            ];

            return response()->json([
                'success' => true,

                'student' => [
                    'id' =>
                        $student->id,

                    'name' =>
                        trim(
                            $student->first_name
                            . ' '
                            . $student->last_name
                        ),

                    'student_id' =>
                        $student->student_id,
                ],

                'stats' =>
                    $stats,

                'records' =>
                    $records->map(
                        function ($record) {

                            return [
                                'date' =>
                                    optional(
                                        $record->attendanceSession
                                    )->attendance_date
                                    ??
                                    $record->created_at,

                                'class' =>
                                    optional(
                                        optional(
                                            $record
                                                ->attendanceSession
                                        )->studentClass
                                    )->name
                                    ??
                                    'N/A',

                                'status' =>
                                    $record->status,

                                'remarks' =>
                                    $record->remarks,
                            ];
                        }
                    ),
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Student attendance error',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Error fetching student attendance: '
                    . $e->getMessage(),
            ], 500);
        }
    }
}