<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSubjectStaff;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\StudentClassStaff;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffAssignmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | Display Form Master and Subject Teacher assignments for the
    | current operational academic year and term.
    |
    */

    public function index()
    {
        $setting = SystemSetting::instance();

        $academicYearId = $setting->operational_academic_year_id;
        $termId = $setting->operational_term_id;

        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : null;

        $term = $termId
            ? Term::find($termId)
            : null;

        /*
        |--------------------------------------------------------------------------
        | Teaching Staff
        |--------------------------------------------------------------------------
        |
        | The staff table stores teaching staff as:
        |
        | staff_type = Teaching
        | status     = Active
        |
        | We deliberately query the database values directly instead of
        | using Staff::teachers(), because that scope currently returns 0.
        |
        */

        $staff = Staff::query()
            ->where('staff_type', 'Teaching')
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Classes
        |--------------------------------------------------------------------------
        */

        $classes = StudentClass::query()
            ->when(
                $academicYearId,
                function ($query) use ($academicYearId) {
                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            )
            ->active()
            ->orderBy('name')
            ->orderBy('stream')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Subjects
        |--------------------------------------------------------------------------
        */

        $subjects = Subject::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Form Master Assignments
        |--------------------------------------------------------------------------
        */

        $formMasterAssignments = StudentClassStaff::query()
            ->with([
                'staff',
                'studentClass',
                'academicYear',
                'term',
            ])
            ->when(
                $academicYearId,
                function ($query) use ($academicYearId) {
                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            )
            ->when(
                $termId,
                function ($query) use ($termId) {
                    $query->where(
                        'term_id',
                        $termId
                    );
                }
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Subject Teacher Assignments
        |--------------------------------------------------------------------------
        */

        $subjectTeacherAssignments = ClassSubjectStaff::query()
            ->with([
                'staff',
                'studentClass',
                'subject',
                'academicYear',
                'term',
            ])
            ->when(
                $academicYearId,
                function ($query) use ($academicYearId) {
                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            )
            ->when(
                $termId,
                function ($query) use ($termId) {
                    $query->where(
                        'term_id',
                        $termId
                    );
                }
            )
            ->get();

        return view(
            'staff_assignments.index',
            compact(
                'academicYear',
                'term',
                'staff',
                'classes',
                'subjects',
                'formMasterAssignments',
                'subjectTeacherAssignments'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE FORM MASTER
    |--------------------------------------------------------------------------
    */

    public function storeFormMaster(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(
                    function ($query) {
                        $query
                            ->where('staff_type', 'Teaching')
                            ->where('status', 'Active');
                    }
                ),
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],
        ]);

        $setting = SystemSetting::instance();

        $academicYearId =
            $setting->operational_academic_year_id;

        $termId =
            $setting->operational_term_id;

        if (!$academicYearId || !$termId) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No operational academic year and term have been configured.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Selected Class
        |--------------------------------------------------------------------------
        */

        $class = StudentClass::query()
            ->whereKey($validated['student_class_id'])
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->active()
            ->first();

        if (!$class) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected class does not belong to the operational academic year.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Form Master
        |--------------------------------------------------------------------------
        |
        | One Form Master per class for a specific academic year and term.
        |
        */

        $alreadyAssigned = StudentClassStaff::query()
            ->where(
                'student_class_id',
                $class->id
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'term_id',
                $termId
            )
            ->exists();

        if ($alreadyAssigned) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'A Form Master is already assigned to this class for the operational term.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Form Master Assignment
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $validated,
            $class,
            $academicYearId,
            $termId
        ) {
            StudentClassStaff::create([
                'student_class_id' => $class->id,
                'staff_id' => $validated['staff_id'],
                'academic_year_id' => $academicYearId,
                'term_id' => $termId,
            ]);
        });

        return back()->with(
            'success',
            'Form Master assigned successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */

    public function storeSubjectTeacher(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(
                    function ($query) {
                        $query
                            ->where('staff_type', 'Teaching')
                            ->where('status', 'Active');
                    }
                ),
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'day' => [
                'nullable',
                'string',
                'max:50',
            ],

            'start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
            ],

            'room' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $setting = SystemSetting::instance();

        $academicYearId =
            $setting->operational_academic_year_id;

        $termId =
            $setting->operational_term_id;

        if (!$academicYearId || !$termId) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No operational academic year and term have been configured.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Selected Class
        |--------------------------------------------------------------------------
        */

        $class = StudentClass::query()
            ->whereKey($validated['student_class_id'])
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->active()
            ->first();

        if (!$class) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected class does not belong to the operational academic year.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Subject
        |--------------------------------------------------------------------------
        */

        $subject = Subject::query()
            ->whereKey($validated['subject_id'])
            ->where('is_active', true)
            ->first();

        if (!$subject) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected subject is not active.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Subject Teacher
        |--------------------------------------------------------------------------
        |
        | One Subject Teacher can be assigned to a particular subject/class
        | combination for the same academic year and term.
        |
        */

        $alreadyAssigned = ClassSubjectStaff::query()
            ->where(
                'student_class_id',
                $class->id
            )
            ->where(
                'subject_id',
                $validated['subject_id']
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'term_id',
                $termId
            )
            ->exists();

        if ($alreadyAssigned) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'A Subject Teacher is already assigned to this subject and class for the operational term.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Subject Teacher Assignment
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $validated,
            $class,
            $academicYearId,
            $termId
        ) {
            ClassSubjectStaff::create([
                'student_class_id' =>
                    $class->id,

                'subject_id' =>
                    $validated['subject_id'],

                'staff_id' =>
                    $validated['staff_id'],

                'academic_year_id' =>
                    $academicYearId,

                'term_id' =>
                    $termId,

                'day' =>
                    $validated['day'] ?? null,

                'start_time' =>
                    $validated['start_time'] ?? null,

                'end_time' =>
                    $validated['end_time'] ?? null,

                'room' =>
                    $validated['room'] ?? null,
            ]);
        });

        return back()->with(
            'success',
            'Subject Teacher assigned successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE FORM MASTER
    |--------------------------------------------------------------------------
    */

    public function destroyFormMaster(
        StudentClassStaff $assignment
    ) {
        $setting = SystemSetting::instance();

        if (
            $assignment->academic_year_id !==
                $setting->operational_academic_year_id
            ||
            $assignment->term_id !==
                $setting->operational_term_id
        ) {
            return back()->with(
                'error',
                'Only assignments belonging to the operational period can be removed here.'
            );
        }

        $assignment->delete();

        return back()->with(
            'success',
            'Form Master assignment removed successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */

    public function destroySubjectTeacher(
        ClassSubjectStaff $assignment
    ) {
        $setting = SystemSetting::instance();

        if (
            $assignment->academic_year_id !==
                $setting->operational_academic_year_id
            ||
            $assignment->term_id !==
                $setting->operational_term_id
        ) {
            return back()->with(
                'error',
                'Only assignments belonging to the operational period can be removed here.'
            );
        }

        $assignment->delete();

        return back()->with(
            'success',
            'Subject Teacher assignment removed successfully.'
        );
    }
}