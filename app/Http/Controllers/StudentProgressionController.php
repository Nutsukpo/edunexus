<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassProgression;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentProgression;
use App\Models\SystemSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StudentProgressionController extends Controller
{
    /**
     * Display the student progression management page.
     */
    public function index(Request $request)
    {
        $settings = SystemSetting::instance()->load([
            'currentAcademicYear',
            'currentTerm',
        ]);

        $currentAcademicYear = $settings->currentAcademicYear;
        $currentTerm = $settings->currentTerm;

        $classes = StudentClass::query()
            ->orderBy('name')
            ->get();

        $students = collect();
        $selectedClass = null;

        /*
        |--------------------------------------------------------------------------
        | Load students for selected class
        |--------------------------------------------------------------------------
        */
        if ($request->filled('class_id')) {

            $selectedClass = StudentClass::find(
                $request->input('class_id')
            );

            if ($selectedClass && $currentAcademicYear && $currentTerm) {

                $students = Student::query()
                    ->where('is_active', true)

                    /*
                    |--------------------------------------------------------------------------
                    | Graduated students should not appear
                    |--------------------------------------------------------------------------
                    */
                    ->whereDoesntHave('classAssignments', function ($query) {
                        $query->where('status', 'graduated');
                    })

                    /*
                    |--------------------------------------------------------------------------
                    | Student must have an active current assignment
                    |--------------------------------------------------------------------------
                    */
                    ->whereHas('classAssignments', function ($query) use (
                        $selectedClass,
                        $currentAcademicYear,
                        $currentTerm
                    ) {
                        $query
                            ->where(
                                'student_class_id',
                                $selectedClass->id
                            )
                            ->where(
                                'academic_year_id',
                                $currentAcademicYear->id
                            )
                            ->where(
                                'term_id',
                                $currentTerm->id
                            )
                            ->where('is_current', true)
                            ->where('status', 'active');
                    })

                    ->with([
                        'classAssignments' => function ($query) use (
                            $currentAcademicYear,
                            $currentTerm
                        ) {
                            $query
                                ->where(
                                    'academic_year_id',
                                    $currentAcademicYear->id
                                )
                                ->where(
                                    'term_id',
                                    $currentTerm->id
                                )
                                ->where('is_current', true)
                                ->where('status', 'active')
                                ->with([
                                    'studentClass',
                                    'academicYear',
                                    'term',
                                ]);
                        }
                    ])

                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Determine next academic year
        |--------------------------------------------------------------------------
        */
        $nextAcademicYear = null;
        $nextFirstTerm = null;

        if ($currentAcademicYear) {

            $nextAcademicYear = $this->getNextAcademicYear(
                $currentAcademicYear
            );

            if ($nextAcademicYear) {
                $nextFirstTerm = $this->getFirstTerm(
                    $nextAcademicYear
                );
            }
        }

        return view(
            'student-progressions.index',
            compact(
                'settings',
                'currentAcademicYear',
                'currentTerm',
                'classes',
                'students',
                'selectedClass',
                'nextAcademicYear',
                'nextFirstTerm'
            )
        );
    }


    /**
     * Process selected students for promotion, repetition or graduation.
     *
     * IMPORTANT:
     *
     * Promotion/repetition DOES NOT retire the current assignment.
     *
     * Instead, a future assignment is prepared as:
     *
     *     status     = pending
     *     is_current = false
     *
     * The current assignment remains:
     *
     *     status     = active
     *     is_current = true
     *
     * until Academic Period is opened.
     */
    public function process(Request $request)
    {
        $request->validate([
            'students' => [
                'required',
                'array',
                'min:1',
            ],

            'students.*.selected' => [
                'nullable',
                'boolean',
            ],

            'students.*.action' => [
                'required',
                'in:promoted,repeated,graduated',
            ],

            'students.*.remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $settings = SystemSetting::instance()->load([
            'currentAcademicYear',
            'currentTerm',
        ]);

        $currentAcademicYear = $settings->currentAcademicYear;
        $currentTerm = $settings->currentTerm;

        /*
        |--------------------------------------------------------------------------
        | Validate current academic year
        |--------------------------------------------------------------------------
        */
        if (!$currentAcademicYear) {
            return back()->with(
                'error',
                'No current academic year has been configured in System Settings.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate current term
        |--------------------------------------------------------------------------
        */
        if (!$currentTerm) {
            return back()->with(
                'error',
                'No current term has been configured in System Settings.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Progression is processed during Third Term
        |--------------------------------------------------------------------------
        */
        if (
            strtolower(trim($currentTerm->name)) !== 'third term'
        ) {
            return back()->with(
                'error',
                'Student promotion, repetition, and graduation can only be processed during Third Term. The current system term is ' .
                $currentTerm->name .
                '.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find next academic year
        |--------------------------------------------------------------------------
        */
        $nextAcademicYear = $this->getNextAcademicYear(
            $currentAcademicYear
        );

        if (!$nextAcademicYear) {
            return back()->with(
                'error',
                'The next academic year has not been configured. Promotion cannot continue.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find next academic year's First Term
        |--------------------------------------------------------------------------
        */
        $nextFirstTerm = $this->getFirstTerm(
            $nextAcademicYear
        );

        if (!$nextFirstTerm) {
            return back()->with(
                'error',
                'First Term has not been configured for the next academic year (' .
                $nextAcademicYear->name .
                '). Promotion cannot continue.'
            );
        }

        $processed = 0;
        $successes = [];
        $errors = [];

        DB::beginTransaction();

        try {

            foreach ($request->students as $studentId => $data) {

                /*
                |--------------------------------------------------------------------------
                | Process only selected students
                |--------------------------------------------------------------------------
                */
                if (
                    !isset($data['selected']) ||
                    (string) $data['selected'] !== '1'
                ) {
                    continue;
                }

                $student = Student::find($studentId);

                if (!$student) {
                    $errors[] =
                        "Student ID {$studentId} could not be found.";

                    continue;
                }

                $action = $data['action'] ?? null;

                if (!$action) {
                    $errors[] =
                        "{$student->first_name} {$student->last_name} has no action selected.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Get student's current assignment
                |--------------------------------------------------------------------------
                |
                | This MUST remain active/current during preparation.
                |--------------------------------------------------------------------------
                */
                $currentAssignment = StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'academic_year_id',
                        $currentAcademicYear->id
                    )
                    ->where(
                        'term_id',
                        $currentTerm->id
                    )
                    ->where(
                        'is_current',
                        true
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->with([
                        'studentClass',
                        'academicYear',
                        'term',
                    ])
                    ->first();

                if (!$currentAssignment) {
                    $errors[] =
                        "{$student->first_name} {$student->last_name} does not have a current active assignment for {$currentAcademicYear->name} - {$currentTerm->name}.";

                    continue;
                }

                $currentClass = $currentAssignment->studentClass;

                if (!$currentClass) {
                    $errors[] =
                        "{$student->first_name} {$student->last_name} has an invalid current class assignment.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | GRADUATION
                |--------------------------------------------------------------------------
                */
                if ($action === 'graduated') {

                    if (!$this->isGraduatingClass($currentClass)) {
                        $errors[] =
                            "{$student->first_name} {$student->last_name} is in {$currentClass->name} and is not eligible for graduation. Only JHS 3 students can graduate.";

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent duplicate graduation
                    |--------------------------------------------------------------------------
                    */
                    $alreadyGraduated = StudentProgression::query()
                        ->where(
                            'student_id',
                            $student->id
                        )
                        ->where(
                            'action',
                            'graduated'
                        )
                        ->where(
                            'academic_year_id',
                            $currentAcademicYear->id
                        )
                        ->exists();

                    if ($alreadyGraduated) {
                        $errors[] =
                            "{$student->first_name} {$student->last_name} has already been processed for graduation for {$currentAcademicYear->name}.";

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Record graduation history
                    |--------------------------------------------------------------------------
                    */
                    StudentProgression::create([
                        'student_id' => $student->id,
                        'from_class_id' => $currentClass->id,
                        'to_class_id' => null,
                        'academic_year_id' => $currentAcademicYear->id,
                        'action' => 'graduated',
                        'remarks' => $data['remarks'] ?? null,
                        'processed_by' => auth()->id(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Graduation retires the current assignment.
                    |--------------------------------------------------------------------------
                    |
                    | Graduation is different from promotion/repetition because
                    | there is no future class assignment to prepare.
                    |--------------------------------------------------------------------------
                    */
                    $currentAssignment->update([
                        'is_current' => false,
                        'status' => 'graduated',
                        'promotion_date' => now(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Synchronize direct student class reference if present
                    |--------------------------------------------------------------------------
                    */
                    if ($this->studentHasClassColumn()) {
                        $student->update([
                            'student_class_id' => null,
                        ]);
                    }

                    $processed++;

                    $successes[] =
                        "{$student->first_name} {$student->last_name} graduated from {$currentClass->name}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Determine destination class
                |--------------------------------------------------------------------------
                */
                $targetClassId = $currentClass->id;
                $toClass = $currentClass;

                /*
                |--------------------------------------------------------------------------
                | PROMOTION
                |--------------------------------------------------------------------------
                */
                if ($action === 'promoted') {

                    $progression = ClassProgression::query()
                        ->where(
                            'from_class_id',
                            $currentClass->id
                        )
                        ->first();

                    if (!$progression) {
                        $errors[] =
                            "No progression rule has been configured for {$currentClass->name}. {$student->first_name} {$student->last_name} cannot be promoted.";

                        continue;
                    }

                    $toClass = StudentClass::find(
                        $progression->to_class_id
                    );

                    if (!$toClass) {
                        $errors[] =
                            "The destination class configured for {$currentClass->name} does not exist.";

                        continue;
                    }

                    $targetClassId = $toClass->id;
                }

                /*
                |--------------------------------------------------------------------------
                | Check for an existing assignment for the next period
                |--------------------------------------------------------------------------
                */
                $existingTarget = StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'academic_year_id',
                        $nextAcademicYear->id
                    )
                    ->where(
                        'term_id',
                        $nextFirstTerm->id
                    )
                    ->first();

                if ($existingTarget) {

                    $errors[] =
                        "{$student->first_name} {$student->last_name} already has an assignment for {$nextAcademicYear->name} - {$nextFirstTerm->name}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Create progression history
                |--------------------------------------------------------------------------
                */
                StudentProgression::create([
                    'student_id' => $student->id,
                    'from_class_id' => $currentClass->id,
                    'to_class_id' => $action === 'promoted'
                        ? $toClass->id
                        : $currentClass->id,
                    'academic_year_id' => $nextAcademicYear->id,
                    'action' => $action,
                    'remarks' => $data['remarks'] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | PREPARE NEXT ACADEMIC PERIOD
                |--------------------------------------------------------------------------
                |
                | DO NOT retire the current assignment here.
                |
                | Current assignment remains:
                |
                |     active
                |     is_current = true
                |
                | The next assignment is:
                |
                |     pending
                |     is_current = false
                |
                | AcademicPeriodController will activate it later.
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::create([
                    'student_id' => $student->id,
                    'student_class_id' => $targetClassId,
                    'academic_year_id' => $nextAcademicYear->id,
                    'term_id' => $nextFirstTerm->id,
                    'status' => 'pending',
                    'assigned_date' => $nextFirstTerm->start_date ?? now(),
                    'promotion_date' => null,
                    'is_current' => false,
                ]);

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT:
                |
                | Do NOT update students.student_class_id here.
                |
                | The student is still officially in the current class until
                | the next academic period is opened.
                |--------------------------------------------------------------------------
                */

                $processed++;

                if ($action === 'promoted') {

                    $successes[] =
                        "{$student->first_name} {$student->last_name} has been prepared for promotion from {$currentClass->name} to {$toClass->name} for {$nextAcademicYear->name} - {$nextFirstTerm->name}.";

                } else {

                    $successes[] =
                        "{$student->first_name} {$student->last_name} has been prepared to repeat {$currentClass->name} for {$nextAcademicYear->name} - {$nextFirstTerm->name}.";
                }
            }

            DB::commit();

            Log::info(
                'EDUNEXUS student progression preparation completed',
                [
                    'processed' => $processed,
                    'successes' => $successes,
                    'errors' => $errors,
                    'current_academic_year_id' => $currentAcademicYear->id,
                    'current_term_id' => $currentTerm->id,
                    'next_academic_year_id' => $nextAcademicYear->id,
                    'next_term_id' => $nextFirstTerm->id,
                    'processed_by' => auth()->id(),
                ]
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'EDUNEXUS student progression preparation failed',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request' => $request->all(),
                ]
            );

            return back()->with(
                'error',
                'Student progression could not be completed: ' .
                $e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | No students processed
        |--------------------------------------------------------------------------
        */
        if ($processed === 0) {

            return back()->with(
                'error',
                !empty($errors)
                    ? implode(' ', $errors)
                    : 'No students were processed.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */
        $message = $processed === 1
            ? '1 student progression was prepared successfully.'
            : "{$processed} student progressions were prepared successfully.";

        if (!empty($errors)) {
            $message .= ' Some students were skipped.';
        }

        return redirect()
            ->route('student-progressions.index')
            ->with('success', $message)
            ->with('progression_details', $successes)
            ->with('progression_errors', $errors);
    }


    /**
     * Bulk promotion endpoint.
     *
     * Kept for compatibility with existing routes.
     *
     * Bulk promotion follows exactly the same preparation workflow:
     *
     *     current assignment stays active/current
     *     future assignment becomes pending/non-current
     */
    public function bulkPromote(Request $request)
    {
        $request->validate([
            'from_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'to_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'student_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'student_ids.*' => [
                'exists:students,id',
            ],

            'remarks' => [
                'nullable',
                'array',
            ],
        ]);

        $settings = SystemSetting::instance()->load([
            'currentAcademicYear',
            'currentTerm',
        ]);

        $currentAcademicYear = $settings->currentAcademicYear;
        $currentTerm = $settings->currentTerm;

        if (!$currentAcademicYear || !$currentTerm) {
            return back()->with(
                'error',
                'Current academic year and term must be configured in System Settings.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Bulk promotion only during Third Term
        |--------------------------------------------------------------------------
        */
        if (
            strtolower(trim($currentTerm->name)) !== 'third term'
        ) {
            return back()->with(
                'error',
                'Bulk promotion is only available during Third Term.'
            );
        }

        $nextAcademicYear = $this->getNextAcademicYear(
            $currentAcademicYear
        );

        if (!$nextAcademicYear) {
            return back()->with(
                'error',
                'The next academic year has not been configured.'
            );
        }

        $nextFirstTerm = $this->getFirstTerm(
            $nextAcademicYear
        );

        if (!$nextFirstTerm) {
            return back()->with(
                'error',
                'First Term has not been configured for the next academic year.'
            );
        }

        $fromClass = StudentClass::findOrFail(
            $request->from_class_id
        );

        $toClass = StudentClass::findOrFail(
            $request->to_class_id
        );

        $processed = 0;
        $errors = [];

        DB::beginTransaction();

        try {

            foreach ($request->student_ids as $studentId) {

                $student = Student::find($studentId);

                if (!$student) {
                    $errors[] =
                        "Student ID {$studentId} was not found.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Find current assignment
                |--------------------------------------------------------------------------
                */
                $currentAssignment = StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'student_class_id',
                        $fromClass->id
                    )
                    ->where(
                        'academic_year_id',
                        $currentAcademicYear->id
                    )
                    ->where(
                        'term_id',
                        $currentTerm->id
                    )
                    ->where(
                        'is_current',
                        true
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->first();

                if (!$currentAssignment) {
                    $errors[] =
                        "{$student->first_name} {$student->last_name} does not have a valid current assignment in {$fromClass->name}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Prevent duplicate target assignment
                |--------------------------------------------------------------------------
                */
                $alreadyAssigned = StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'academic_year_id',
                        $nextAcademicYear->id
                    )
                    ->where(
                        'term_id',
                        $nextFirstTerm->id
                    )
                    ->exists();

                if ($alreadyAssigned) {
                    $errors[] =
                        "{$student->first_name} {$student->last_name} already has an assignment for {$nextAcademicYear->name} - {$nextFirstTerm->name}.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Create progression history
                |--------------------------------------------------------------------------
                */
                StudentProgression::create([
                    'student_id' => $student->id,
                    'from_class_id' => $fromClass->id,
                    'to_class_id' => $toClass->id,
                    'academic_year_id' => $nextAcademicYear->id,
                    'action' => 'promoted',
                    'remarks' =>
                        $request->remarks[$studentId] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Prepare future assignment
                |--------------------------------------------------------------------------
                |
                | DO NOT retire current assignment.
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::create([
                    'student_id' => $student->id,
                    'student_class_id' => $toClass->id,
                    'academic_year_id' => $nextAcademicYear->id,
                    'term_id' => $nextFirstTerm->id,
                    'status' => 'pending',
                    'assigned_date' => $nextFirstTerm->start_date ?? now(),
                    'promotion_date' => null,
                    'is_current' => false,
                ]);

                /*
                |--------------------------------------------------------------------------
                | DO NOT update students.student_class_id here.
                |--------------------------------------------------------------------------
                */

                $processed++;
            }

            DB::commit();

            Log::info(
                'EDUNEXUS bulk student progression preparation completed',
                [
                    'processed' => $processed,
                    'errors' => $errors,
                    'from_class_id' => $fromClass->id,
                    'to_class_id' => $toClass->id,
                    'current_academic_year_id' => $currentAcademicYear->id,
                    'current_term_id' => $currentTerm->id,
                    'next_academic_year_id' => $nextAcademicYear->id,
                    'next_term_id' => $nextFirstTerm->id,
                    'processed_by' => auth()->id(),
                ]
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'EDUNEXUS bulk student progression preparation failed',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return back()->with(
                'error',
                'Bulk promotion failed: ' .
                $e->getMessage()
            );
        }

        if ($processed === 0) {

            return back()->with(
                'error',
                !empty($errors)
                    ? implode(' ', $errors)
                    : 'No students were processed.'
            );
        }

        $message = $processed === 1
            ? "1 student has been prepared for promotion from {$fromClass->name} to {$toClass->name}."
            : "{$processed} students have been prepared for promotion from {$fromClass->name} to {$toClass->name}.";

        if (!empty($errors)) {
            $message .= ' Some students were skipped.';
        }

        return back()
            ->with('success', $message)
            ->with('progression_errors', $errors);
    }


    /**
     * AJAX: Get students for a specific class and academic period.
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'exists:terms,id',
            ],
        ]);

        $students = Student::query()
            ->where('is_active', true)

            ->whereHas('classAssignments', function ($query) use ($request) {

                $query
                    ->where(
                        'student_class_id',
                        $request->class_id
                    )
                    ->where(
                        'academic_year_id',
                        $request->academic_year_id
                    )
                    ->where(
                        'is_current',
                        true
                    )
                    ->where(
                        'status',
                        'active'
                    );

                if ($request->filled('term_id')) {
                    $query->where(
                        'term_id',
                        $request->term_id
                    );
                }
            })

            ->with([
                'classAssignments' => function ($query) use ($request) {

                    $query
                        ->where(
                            'student_class_id',
                            $request->class_id
                        )
                        ->where(
                            'academic_year_id',
                            $request->academic_year_id
                        )
                        ->where(
                            'is_current',
                            true
                        )
                        ->where(
                            'status',
                            'active'
                        );

                    if ($request->filled('term_id')) {
                        $query->where(
                            'term_id',
                            $request->term_id
                        );
                    }

                    $query->with([
                        'studentClass',
                        'academicYear',
                        'term',
                    ]);
                }
            ])

            ->orderBy('last_name')
            ->orderBy('first_name')

            ->get([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'student_id',
            ]);

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }


    /**
     * Display progression history for a student.
     */
    public function history($studentId)
    {
        $student = Student::findOrFail($studentId);

        $progressions = StudentProgression::query()
            ->with([
                'fromClass',
                'toClass',
                'academicYear',
                'processor',
            ])
            ->where(
                'student_id',
                $studentId
            )
            ->orderByDesc('created_at')
            ->get();

        return view(
            'student-progressions.history',
            compact(
                'student',
                'progressions'
            )
        );
    }


    /**
     * Display progression statistics.
     */
    public function statistics(Request $request)
    {
        $totalPromoted = StudentProgression::query()
            ->where(
                'action',
                'promoted'
            )
            ->count();

        $totalRepeated = StudentProgression::query()
            ->where(
                'action',
                'repeated'
            )
            ->count();

        $totalGraduated = StudentProgression::query()
            ->where(
                'action',
                'graduated'
            )
            ->count();

        $recentProgressions = StudentProgression::query()
            ->with([
                'student',
                'fromClass',
                'toClass',
                'academicYear',
            ])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view(
            'student-progressions.statistics',
            compact(
                'totalPromoted',
                'totalRepeated',
                'totalGraduated',
                'recentProgressions'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the next academic year after the current academic year.
     */
    private function getNextAcademicYear(
        AcademicYear $currentAcademicYear
    ): ?AcademicYear {
        return AcademicYear::query()
            ->where(
                'start_date',
                '>',
                $currentAcademicYear->start_date
            )
            ->orderBy('start_date')
            ->first();
    }


    /**
     * Get First Term for an academic year.
     */
    private function getFirstTerm(
        AcademicYear $academicYear
    ): ?Term {
        return Term::query()
            ->where(
                'academic_year_id',
                $academicYear->id
            )
            ->whereRaw(
                'LOWER(name) = ?',
                ['first term']
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('start_date')
            ->first();
    }


    /**
     * Determine whether a class is a graduating class.
     *
     * EDUNEXUS currently ends progression at JHS 3.
     */
    private function isGraduatingClass(
        StudentClass $class
    ): bool {
        return str_contains(
            strtoupper(trim($class->name)),
            'JHS 3'
        );
    }


    /**
     * Check whether students table contains student_class_id.
     *
     * This allows EDUNEXUS to continue operating with the
     * assignment-based architecture without requiring the
     * direct student_class_id column.
     */
    private function studentHasClassColumn(): bool
    {
        return Schema::hasColumn(
            'students',
            'student_class_id'
        );
    }
}