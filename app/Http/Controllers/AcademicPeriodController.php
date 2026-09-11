<?php

namespace App\Http\Controllers;

use App\Models\StudentClassAssignment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AcademicPeriodController extends Controller
{
    /**
     * Display the academic period opening page.
     *
     * Shows:
     * - Pending promotion/repetition assignments prepared for the period.
     * - Students who currently have an active assignment and may need
     *   a normal term rollover.
     */
    public function open()
    {
        abort_unless(
            auth()->user()->can('student-class-assignments.edit'),
            403
        );

        $settings = SystemSetting::instance();

        $academicYear = $settings->operationalAcademicYear;
        $term = $settings->operationalTerm;

        if (!$academicYear || !$term) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'The operational academic year and term have not been configured in System Settings.',
            ]);
        }

        if (
            (int) $term->academic_year_id !==
            (int) $academicYear->id
        ) {
            throw ValidationException::withMessages([
                'academic_period' =>
                    'The operational term does not belong to the operational academic year.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending promotion/repetition assignments
        |--------------------------------------------------------------------------
        */
        $pendingAssignments = StudentClassAssignment::query()
            ->with([
                'student',
                'studentClass',
            ])
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('status', 'pending')
            ->where('is_current', false)
            ->orderBy('student_class_id')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Students who already have an assignment for this period
        |--------------------------------------------------------------------------
        */
        $periodStudentIds = StudentClassAssignment::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->pluck('student_id')
            ->filter()
            ->unique();

        /*
        |--------------------------------------------------------------------------
        | Find current assignments for students who do NOT already have
        | an assignment for the operational period.
        |--------------------------------------------------------------------------
        |
        | These students are candidates for normal term rollover.
        |
        | Example:
        |
        | 2024/2025 First Term
        | Primary 1A
        |
        |          ↓
        |
        | 2024/2025 Second Term
        | Primary 1A
        |--------------------------------------------------------------------------
        */
        $currentAssignments = StudentClassAssignment::query()
            ->with([
                'student',
                'studentClass',
                'academicYear',
                'term',
            ])
            ->where('is_current', true)
            ->whereIn('status', ['active', 'repeated'])
            ->when(
                $periodStudentIds->isNotEmpty(),
                function ($query) use ($periodStudentIds) {
                    $query->whereNotIn(
                        'student_id',
                        $periodStudentIds
                    );
                }
            )
            ->orderBy('student_id')
            ->orderByDesc('id')
            ->get();

        return view(
            'academic-period.open',
            compact(
                'settings',
                'academicYear',
                'term',
                'pendingAssignments',
                'currentAssignments'
            )
        );
    }


    /**
     * Activate/open the operational academic period.
     *
     * Two workflows are supported:
     *
     * 1. Prepared progression:
     *
     *    pending + not current
     *              ↓
     *    active + current
     *
     * 2. Normal term rollover:
     *
     *    Existing current assignment
     *              ↓
     *    New same-class assignment for the new term
     *              ↓
     *    active + current
     *
     * Historical assignments are never deleted.
     *
     * Fee/payment records are deliberately NOT touched.
     */
    public function activate(Request $request)
    {
        abort_unless(
            auth()->user()->can('student-class-assignments.edit'),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Require explicit confirmation
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'confirmation' => [
                'required',
                'accepted',
            ],
        ]);

        $settings = SystemSetting::instance();

        $academicYear = $settings->operationalAcademicYear;
        $term = $settings->operationalTerm;

        if (!$academicYear || !$term) {
            return back()->with(
                'error',
                'The operational academic period is not configured.'
            );
        }

        if (
            (int) $term->academic_year_id !==
            (int) $academicYear->id
        ) {
            return back()->with(
                'error',
                'The operational term does not belong to the operational academic year.'
            );
        }

        $activated = 0;
        $created = 0;
        $alreadyCurrent = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | STEP 1
            |--------------------------------------------------------------------------
            | Lock all assignments already belonging to the target period.
            |
            | This protects against duplicate activation requests.
            |--------------------------------------------------------------------------
            */
            $periodAssignments = StudentClassAssignment::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->lockForUpdate()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | STEP 2
            |--------------------------------------------------------------------------
            | Lock current active/repeated assignments.
            |--------------------------------------------------------------------------
            */
            $currentAssignments = StudentClassAssignment::query()
                ->where('is_current', true)
                ->whereIn('status', ['active', 'repeated'])
                ->lockForUpdate()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | STEP 3
            |--------------------------------------------------------------------------
            | Process students who already have an assignment for the target
            | period.
            |--------------------------------------------------------------------------
            */
            foreach ($periodAssignments as $periodAssignment) {

                /*
                |--------------------------------------------------------------------------
                | Already active/current
                |--------------------------------------------------------------------------
                */
                if (
                    $periodAssignment->status === 'active' &&
                    $periodAssignment->is_current
                ) {
                    $alreadyCurrent++;

                    /*
                    |--------------------------------------------------------------------------
                    | Make sure no other assignment for the same student remains
                    | current.
                    |--------------------------------------------------------------------------
                    */
                    StudentClassAssignment::query()
                        ->where('student_id', $periodAssignment->student_id)
                        ->where('id', '!=', $periodAssignment->id)
                        ->where('is_current', true)
                        ->whereIn('status', ['active', 'repeated'])
                        ->update([
                            'is_current' => false,
                            'status' => 'completed',
                            'promotion_date' => now(),
                        ]);

                    $this->synchronizeStudentClass(
                        $periodAssignment
                    );

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Only pending assignments should be activated automatically.
                |--------------------------------------------------------------------------
                */
                if (
                    $periodAssignment->status !== 'pending' ||
                    $periodAssignment->is_current
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Validate assignment.
                |--------------------------------------------------------------------------
                */
                if (!$periodAssignment->student_id) {

                    $skipped++;

                    $errors[] =
                        "Assignment #{$periodAssignment->id} was skipped because it has no student.";

                    continue;
                }

                if (!$periodAssignment->student_class_id) {

                    $skipped++;

                    $errors[] =
                        "Assignment #{$periodAssignment->id} for student #{$periodAssignment->student_id} was skipped because it has no destination class.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Retire the student's previous current assignment.
                |--------------------------------------------------------------------------
                */
                $oldAssignments = $currentAssignments->where(
                    'student_id',
                    $periodAssignment->student_id
                );

                foreach ($oldAssignments as $oldAssignment) {

                    if ($oldAssignment->id === $periodAssignment->id) {
                        continue;
                    }

                    $oldAssignment->update([
                        'is_current' => false,
                        'status' => 'completed',
                        'promotion_date' => now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Activate prepared assignment.
                |--------------------------------------------------------------------------
                */
                $periodAssignment->update([
                    'status' => 'active',
                    'is_current' => true,
                    'assigned_date' =>
                        $periodAssignment->assigned_date
                        ?: ($term->start_date ?? now()),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Synchronize direct student class reference.
                |--------------------------------------------------------------------------
                */
                $this->synchronizeStudentClass(
                    $periodAssignment
                );

                $activated++;
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 4
            |--------------------------------------------------------------------------
            | Normal term rollover.
            |--------------------------------------------------------------------------
            |
            | Find current students who DO NOT have an assignment in the
            | operational period.
            |--------------------------------------------------------------------------
            */
            foreach ($currentAssignments as $currentAssignment) {

                $studentId = $currentAssignment->student_id;

                if (!$studentId) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Check whether this student already has a target-period
                | assignment.
                |--------------------------------------------------------------------------
                */
                $targetAssignment = $periodAssignments->first(
                    function ($assignment) use ($studentId) {
                        return (int) $assignment->student_id ===
                            (int) $studentId;
                    }
                );

                if ($targetAssignment) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Make sure the current assignment really represents a class.
                |--------------------------------------------------------------------------
                */
                if (!$currentAssignment->student_class_id) {

                    $skipped++;

                    $errors[] =
                        "Student #{$studentId} was skipped because the current assignment has no class.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Safety check against duplicate target assignment.
                |--------------------------------------------------------------------------
                */
                $existingTarget = StudentClassAssignment::query()
                    ->where('student_id', $studentId)
                    ->where(
                        'academic_year_id',
                        $academicYear->id
                    )
                    ->where(
                        'term_id',
                        $term->id
                    )
                    ->lockForUpdate()
                    ->first();

                if ($existingTarget) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Create normal same-class term assignment.
                |--------------------------------------------------------------------------
                |
                | This is NOT a promotion.
                |
                | The student remains in the same class.
                |--------------------------------------------------------------------------
                */
                $newAssignment = StudentClassAssignment::create([
                    'student_id' => $studentId,
                    'student_class_id' =>
                        $currentAssignment->student_class_id,
                    'academic_year_id' =>
                        $academicYear->id,
                    'term_id' =>
                        $term->id,
                    'status' => 'active',
                    'assigned_date' =>
                        $term->start_date ?? now(),
                    'promotion_date' => null,
                    'is_current' => true,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Retire old assignment AFTER successfully creating the new
                | assignment.
                |--------------------------------------------------------------------------
                */
                $currentAssignment->update([
                    'is_current' => false,
                    'status' => 'completed',
                    'promotion_date' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Synchronize direct student class reference.
                |--------------------------------------------------------------------------
                */
                $this->synchronizeStudentClass(
                    $newAssignment
                );

                $created++;
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 5
            |--------------------------------------------------------------------------
            | Commit all changes.
            |--------------------------------------------------------------------------
            */
            DB::commit();

            Log::info(
                'EDUNEXUS academic period opened',
                [
                    'academic_year_id' =>
                        $academicYear->id,

                    'term_id' =>
                        $term->id,

                    'activated' =>
                        $activated,

                    'created' =>
                        $created,

                    'already_current' =>
                        $alreadyCurrent,

                    'skipped' =>
                        $skipped,

                    'errors' =>
                        $errors,

                    'opened_by' =>
                        auth()->id(),
                ]
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'EDUNEXUS academic period activation failed',
                [
                    'academic_year_id' =>
                        $academicYear->id,

                    'term_id' =>
                        $term->id,

                    'message' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),

                    'opened_by' =>
                        auth()->id(),
                ]
            );

            return back()->with(
                'error',
                'The academic period could not be opened. No assignment changes were committed.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build response message
        |--------------------------------------------------------------------------
        */
        $parts = [];

        if ($activated > 0) {
            $parts[] =
                $activated === 1
                    ? '1 prepared student assignment was activated'
                    : "{$activated} prepared student assignments were activated";
        }

        if ($created > 0) {
            $parts[] =
                $created === 1
                    ? '1 normal term assignment was created'
                    : "{$created} normal term assignments were created";
        }

        if ($alreadyCurrent > 0) {
            $parts[] =
                $alreadyCurrent === 1
                    ? '1 assignment was already active'
                    : "{$alreadyCurrent} assignments were already active";
        }

        if (empty($parts)) {
            $message =
                "No student assignments needed activation for {$academicYear->name} - {$term->name}.";
        } else {
            $message =
                implode(', ', $parts) .
                " for {$academicYear->name} - {$term->name}.";
        }

        if ($skipped > 0) {
            $message .=
                " {$skipped} assignment(s) were skipped.";
        }

        return redirect()
            ->route('academic-period.open')
            ->with('success', $message)
            ->with(
                'academic_period_errors',
                $errors
            );
    }


    /**
     * Synchronize students.student_class_id when that legacy/direct
     * column exists.
     *
     * StudentClassAssignment remains the authoritative historical record.
     */
    private function synchronizeStudentClass(
        StudentClassAssignment $assignment
    ): void {
        if (!$this->studentHasClassColumn()) {
            return;
        }

        $student = $assignment->student;

        if (!$student) {
            $student = \App\Models\Student::find(
                $assignment->student_id
            );
        }

        if (!$student) {
            return;
        }

        $student->update([
            'student_class_id' =>
                $assignment->student_class_id,
        ]);
    }


    /**
     * Determine whether students table contains student_class_id.
     */
    private function studentHasClassColumn(): bool
    {
        return Schema::hasColumn(
            'students',
            'student_class_id'
        );
    }
}