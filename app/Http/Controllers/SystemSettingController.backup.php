<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\StudentClassAssignment;
use App\Models\SystemSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SystemSettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SETTINGS PAGE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $settings = SystemSetting::instance();

        $academicYears = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        $terms = collect();

        if ($settings->current_academic_year_id) {
            $terms = Term::query()
                ->where(
                    'academic_year_id',
                    $settings->current_academic_year_id
                )
                ->where('is_active', true)
                ->orderBy('start_date')
                ->get();
        }

        return view(
            'settings.index',
            compact(
                'settings',
                'academicYears',
                'terms'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET TERMS FOR ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */
    public function terms(AcademicYear $academicYear)
    {
        $terms = Term::query()
            ->where(
                'academic_year_id',
                $academicYear->id
            )
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get([
                'id',
                'academic_year_id',
                'name',
                'start_date',
                'end_date',
            ]);

        return response()->json($terms);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SYSTEM SETTINGS
    |--------------------------------------------------------------------------
    |
    | This is the central academic-period switch.
    |
    | When the current academic year/term changes:
    |
    | 1. Existing current assignments are preserved.
    | 2. Their is_current flag becomes false.
    | 3. Their status becomes completed.
    | 4. A new assignment is created for the new period.
    | 5. A pending promotion assignment is activated when available.
    | 6. Historical assignments are never rewritten.
    |
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'current_term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],

            'auto_assign_current_period' => [
                'nullable',
                'boolean',
            ],

            'use_current_period_defaults' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SELECTED ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */
        $academicYear = AcademicYear::findOrFail(
            $validated['current_academic_year_id']
        );

        if (!$academicYear->is_active) {
            throw ValidationException::withMessages([
                'current_academic_year_id' =>
                    'The selected academic year is not active.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SELECTED TERM
        |--------------------------------------------------------------------------
        */
        $term = Term::query()
            ->where(
                'id',
                $validated['current_term_id']
            )
            ->where(
                'academic_year_id',
                $academicYear->id
            )
            ->where('is_active', true)
            ->first();

        if (!$term) {
            throw ValidationException::withMessages([
                'current_term_id' =>
                    'The selected term does not belong to the selected academic year or is not active.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT SETTINGS
        |--------------------------------------------------------------------------
        */
        $settings = SystemSetting::instance();

        $oldAcademicYearId =
            $settings->current_academic_year_id;

        $oldTermId =
            $settings->current_term_id;

        $newAcademicYearId =
            $academicYear->id;

        $newTermId =
            $term->id;

        /*
        |--------------------------------------------------------------------------
        | DETERMINE WHETHER THE ACADEMIC PERIOD CHANGED
        |--------------------------------------------------------------------------
        */
        $periodChanged =
            $oldAcademicYearId !== $newAcademicYearId
            ||
            $oldTermId !== $newTermId;

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | UPDATE CENTRAL SETTINGS FIRST
            |--------------------------------------------------------------------------
            */
            $settings->update([
                'current_academic_year_id' =>
                    $newAcademicYearId,

                'current_term_id' =>
                    $newTermId,

                'auto_assign_current_period' =>
                    $validated['auto_assign_current_period']
                    ?? false,

                'use_current_period_defaults' =>
                    $validated['use_current_period_defaults']
                    ?? false,
            ]);

            /*
            |--------------------------------------------------------------------------
            | AUTOMATIC STUDENT PERIOD ROLLOVER
            |--------------------------------------------------------------------------
            |
            | Only run when:
            |
            | - the academic period actually changed
            | - automatic assignment is enabled
            |
            |--------------------------------------------------------------------------
            */
            $rolloverResult = [
                'processed' => 0,
                'created' => 0,
                'activated' => 0,
                'already_current' => 0,
                'skipped' => 0,
            ];

            if (
                $periodChanged
                &&
                $settings->auto_assign_current_period
            ) {
                $rolloverResult =
                    $this->rollStudentsToCurrentPeriod(
                        $newAcademicYearId,
                        $newTermId
                    );
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | SUCCESS MESSAGE
            |--------------------------------------------------------------------------
            */
            $message =
                'Academic settings updated successfully.';

            if (
                $periodChanged
                &&
                $settings->auto_assign_current_period
            ) {
                $message .=
                    ' Student assignments were updated for '
                    . $academicYear->name
                    . ' — '
                    . $term->name
                    . '.';

                if ($rolloverResult['created'] > 0) {
                    $message .=
                        ' '
                        . $rolloverResult['created']
                        . ' new assignments created.';
                }

                if ($rolloverResult['activated'] > 0) {
                    $message .=
                        ' '
                        . $rolloverResult['activated']
                        . ' pending assignments activated.';
                }
            }

            return redirect()
                ->route('settings.index')
                ->with(
                    'success',
                    $message
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'System settings update failed',
                [
                    'message' =>
                        $e->getMessage(),

                    'academic_year_id' =>
                        $newAcademicYearId,

                    'term_id' =>
                        $newTermId,

                    'old_academic_year_id' =>
                        $oldAcademicYearId,

                    'old_term_id' =>
                        $oldTermId,
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the academic settings. No student assignments were changed.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ROLL STUDENTS TO CURRENT PERIOD
    |--------------------------------------------------------------------------
    |
    | This method preserves historical assignments and creates the
    | student's assignment for the newly selected academic period.
    |
    |--------------------------------------------------------------------------
    */
    private function rollStudentsToCurrentPeriod(
        int $academicYearId,
        int $termId
    ): array {
        $result = [
            'processed' => 0,
            'created' => 0,
            'activated' => 0,
            'already_current' => 0,
            'skipped' => 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | FIND CURRENT ACTIVE ASSIGNMENTS
        |--------------------------------------------------------------------------
        |
        | We intentionally only process current active/repeated assignments.
        |
        | Historical assignments are never touched here.
        |--------------------------------------------------------------------------
        */
        $currentAssignments =
            StudentClassAssignment::query()
                ->where(
                    'is_current',
                    true
                )
                ->whereIn(
                    'status',
                    ['active', 'repeated']
                )
                ->with([
                    'student',
                    'studentClass',
                ])
                ->get();

        foreach ($currentAssignments as $oldAssignment) {

            $result['processed']++;

            /*
            |--------------------------------------------------------------------------
            | SAFETY CHECK
            |--------------------------------------------------------------------------
            */
            if (!$oldAssignment->student_id) {
                $result['skipped']++;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CHECK WHETHER TARGET ASSIGNMENT ALREADY EXISTS
            |--------------------------------------------------------------------------
            |
            | We check all non-deleted assignments for the target period.
            |--------------------------------------------------------------------------
            */
            $targetAssignments =
                StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $oldAssignment->student_id
                    )
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'term_id',
                        $termId
                    )
                    ->orderByDesc('id')
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | ACTIVE TARGET ASSIGNMENT ALREADY EXISTS
            |--------------------------------------------------------------------------
            */
            $activeTarget =
                $targetAssignments->first(
                    function ($assignment) {
                        return
                            $assignment->is_current
                            &&
                            in_array(
                                $assignment->status,
                                ['active', 'repeated'],
                                true
                            );
                    }
                );

            if ($activeTarget) {

                /*
                |--------------------------------------------------------------------------
                | Retire the old assignment if it is different.
                |--------------------------------------------------------------------------
                */
                if (
                    $activeTarget->id !==
                    $oldAssignment->id
                ) {
                    $oldAssignment->update([
                        'is_current' => false,
                        'status' => 'completed',
                    ]);
                }

                $result['already_current']++;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | PENDING TARGET ASSIGNMENT
            |--------------------------------------------------------------------------
            |
            | This is especially important for promotion.
            |
            | Example:
            |
            | Primary 1B
            | 2025/2026 Third Term
            |
            | Promotion already created:
            |
            | Primary 2
            | 2026/2027 First Term
            | pending / not current
            |
            | We activate that assignment instead of creating another one.
            |--------------------------------------------------------------------------
            */
            $pendingTarget =
                $targetAssignments->first(
                    function ($assignment) {
                        return
                            !$assignment->is_current
                            &&
                            in_array(
                                $assignment->status,
                                ['pending', 'active', 'repeated'],
                                true
                            );
                    }
                );

            if ($pendingTarget) {

                /*
                |--------------------------------------------------------------------------
                | Retire old current assignment
                |--------------------------------------------------------------------------
                */
                $oldAssignment->update([
                    'is_current' => false,
                    'status' => 'completed',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Retire any other current assignment for this student
                |--------------------------------------------------------------------------
                */
                StudentClassAssignment::query()
                    ->where(
                        'student_id',
                        $oldAssignment->student_id
                    )
                    ->where(
                        'id',
                        '!=',
                        $pendingTarget->id
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
                        'status' => 'completed',
                    ]);

                /*
                |--------------------------------------------------------------------------
                | ACTIVATE PENDING ASSIGNMENT
                |--------------------------------------------------------------------------
                */
                $pendingTarget->update([
                    'is_current' => true,
                    'status' => 'active',
                    'assigned_date' =>
                        $pendingTarget->assigned_date
                        ??
                        $this->getTermStartDate($termId),
                ]);

                $result['activated']++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | DO NOT REACTIVATE COMPLETED HISTORICAL TARGET
            |--------------------------------------------------------------------------
            |
            | If a completed assignment already exists for the target period,
            | we create a new assignment rather than rewriting history.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | RETIRE OLD ASSIGNMENT
            |--------------------------------------------------------------------------
            */
            $oldAssignment->update([
                'is_current' => false,
                'status' => 'completed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE NEW ASSIGNMENT
            |--------------------------------------------------------------------------
            |
            | Ordinary rollover keeps the student's existing class.
            |
            | Promotion is handled separately.
            |--------------------------------------------------------------------------
            */
            StudentClassAssignment::create([
                'student_id' =>
                    $oldAssignment->student_id,

                'student_class_id' =>
                    $oldAssignment->student_class_id,

                'academic_year_id' =>
                    $academicYearId,

                'term_id' =>
                    $termId,

                'status' =>
                    'active',

                'is_current' =>
                    true,

                'assigned_date' =>
                    $this->getTermStartDate($termId),
            ]);

            $result['created']++;
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | GET TERM START DATE
    |--------------------------------------------------------------------------
    */
    private function getTermStartDate(int $termId)
    {
        $term = Term::find($termId);

        if (
            $term &&
            $term->start_date
        ) {
            return $term->start_date;
        }

        return now();
    }
}