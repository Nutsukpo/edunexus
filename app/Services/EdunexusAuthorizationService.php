<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSubjectStaff;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentClassStaff;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class EdunexusAuthorizationService
{
    /*
    |--------------------------------------------------------------------------
    | SCOPED ROLES
    |--------------------------------------------------------------------------
    |
    | These roles have their module permissions controlled by Spatie,
    | while their class/subject access is additionally restricted by
    | staff assignments.
    |
    */

    private const SCOPED_ROLES = [
        'Teaching Staff',
    ];

    /*
    |--------------------------------------------------------------------------
    | ROLE CHECKS
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function isScopedStaff(User $user): bool
    {
        return $user->hasAnyRole(self::SCOPED_ROLES);
    }

    /**
     * Get the Staff record linked to the User account.
     */
    public function staffFor(User $user): ?Staff
    {
        return $user->staff()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | OPERATIONAL PERIOD
    |--------------------------------------------------------------------------
    */

    public function operationalAcademicYearId(): ?int
    {
        $setting = SystemSetting::instance();

        return $setting->operational_academic_year_id
            ? (int) $setting->operational_academic_year_id
            : null;
    }

    public function operationalTermId(): ?int
    {
        $setting = SystemSetting::instance();

        return $setting->operational_term_id
            ? (int) $setting->operational_term_id
            : null;
    }

    protected function resolveAcademicYearId(
        ?int $academicYearId = null
    ): ?int {
        return $academicYearId !== null
            ? $academicYearId
            : $this->operationalAcademicYearId();
    }

    protected function resolveTermId(
        ?int $termId = null
    ): ?int {
        return $termId !== null
            ? $termId
            : $this->operationalTermId();
    }

    /*
    |--------------------------------------------------------------------------
    | FORM MASTER
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether a Teaching Staff member is the Form Master
     * of a class for the requested academic period.
     */
    public function isFormMaster(
        User $user,
        int $classId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Non-scoped roles
        |--------------------------------------------------------------------------
        |
        | Administrator, MIS, Accountant, etc. are not restricted by
        | Teaching Staff class assignments.
        |
        */

        if (!$this->isScopedStaff($user)) {
            return true;
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return false;
        }

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        $termId = $this->resolveTermId(
            $termId
        );

        $query = StudentClassStaff::query()
            ->where('student_class_id', $classId)
            ->where('staff_id', $staff->id);

        if ($academicYearId !== null) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        if ($termId !== null) {
            $query->where(
                'term_id',
                $termId
            );
        }

        return $query->exists();
    }

    /**
     * Return classes where the user is assigned as Form Master.
     */
    public function formMasterClasses(
        User $user,
        ?int $academicYearId = null,
        ?int $termId = null
    ): Builder {
        $query = StudentClass::query();

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        $termId = $this->resolveTermId(
            $termId
        );

        /*
        |--------------------------------------------------------------------------
        | Super Admin / Non-scoped
        |--------------------------------------------------------------------------
        */

        if (
            $this->isSuperAdmin($user)
            || !$this->isScopedStaff($user)
        ) {
            return $this->applyAcademicYear(
                $query,
                $academicYearId
            );
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return $query->whereKey(-1);
        }

        $query->whereHas(
            'formMasterAssignments',
            function (Builder $assignmentQuery) use (
                $staff,
                $academicYearId,
                $termId
            ) {
                $assignmentQuery
                    ->where('staff_id', $staff->id)

                    ->when(
                        $academicYearId !== null,
                        fn (Builder $q) =>
                            $q->where(
                                'academic_year_id',
                                $academicYearId
                            )
                    )

                    ->when(
                        $termId !== null,
                        fn (Builder $q) =>
                            $q->where(
                                'term_id',
                                $termId
                            )
                    );
            }
        );

        return $this->applyAcademicYear(
            $query,
            $academicYearId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether a staff member is assigned to a specific
     * class and subject for the requested academic period.
     */
    public function isSubjectTeacher(
        User $user,
        int $classId,
        int $subjectId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$this->isScopedStaff($user)) {
            return true;
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return false;
        }

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        $termId = $this->resolveTermId(
            $termId
        );

        $query = ClassSubjectStaff::query()
            ->where('student_class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('staff_id', $staff->id);

        if ($academicYearId !== null) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        if ($termId !== null) {
            $query->where(
                'term_id',
                $termId
            );
        }

        return $query->exists();
    }

    /**
     * Return classes where the user has at least one subject
     * teacher assignment.
     */
    public function subjectTeacherClasses(
        User $user,
        ?int $academicYearId = null,
        ?int $termId = null
    ): Builder {
        $query = StudentClass::query();

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        $termId = $this->resolveTermId(
            $termId
        );

        if (
            $this->isSuperAdmin($user)
            || !$this->isScopedStaff($user)
        ) {
            return $this->applyAcademicYear(
                $query,
                $academicYearId
            );
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return $query->whereKey(-1);
        }

        $query->whereHas(
            'subjectStaff',
            function (Builder $assignmentQuery) use (
                $staff,
                $academicYearId,
                $termId
            ) {
                $assignmentQuery
                    ->where('staff_id', $staff->id)

                    ->when(
                        $academicYearId !== null,
                        fn (Builder $q) =>
                            $q->where(
                                'academic_year_id',
                                $academicYearId
                            )
                    )

                    ->when(
                        $termId !== null,
                        fn (Builder $q) =>
                            $q->where(
                                'term_id',
                                $termId
                            )
                    );
            }
        );

        return $this->applyAcademicYear(
            $query,
            $academicYearId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS ACCESS
    |--------------------------------------------------------------------------
    */

    /**
     * Classes accessible to the user.
     *
     * Teaching Staff:
     *
     *   Form Master
     *       -> class access
     *
     *   Subject Teacher
     *       -> class access
     *
     *   Neither
     *       -> no class access
     *
     * Super Admin / non-scoped roles:
     *
     *       -> school-wide class access
     */
    public function accessibleClasses(
        User $user,
        ?int $academicYearId = null,
        ?int $termId = null
    ): Builder {
        $query = StudentClass::query();

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        $termId = $this->resolveTermId(
            $termId
        );

        /*
        |--------------------------------------------------------------------------
        | Super Admin / Non-scoped
        |--------------------------------------------------------------------------
        */

        if (
            $this->isSuperAdmin($user)
            || !$this->isScopedStaff($user)
        ) {
            return $this->applyAcademicYear(
                $query,
                $academicYearId
            );
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return $query->whereKey(-1);
        }

        /*
        |--------------------------------------------------------------------------
        | Form Master OR Subject Teacher
        |--------------------------------------------------------------------------
        */

        $query->where(function (Builder $q) use (
            $staff,
            $academicYearId,
            $termId
        ) {

            /*
            |--------------------------------------------------------------------------
            | FORM MASTER
            |--------------------------------------------------------------------------
            */

            $q->whereHas(
                'formMasterAssignments',
                function (Builder $assignmentQuery) use (
                    $staff,
                    $academicYearId,
                    $termId
                ) {
                    $assignmentQuery
                        ->where(
                            'staff_id',
                            $staff->id
                        )

                        ->when(
                            $academicYearId !== null,
                            fn (Builder $query) =>
                                $query->where(
                                    'academic_year_id',
                                    $academicYearId
                                )
                        )

                        ->when(
                            $termId !== null,
                            fn (Builder $query) =>
                                $query->where(
                                    'term_id',
                                    $termId
                                )
                        );
                }
            );

            /*
            |--------------------------------------------------------------------------
            | SUBJECT TEACHER
            |--------------------------------------------------------------------------
            */

            $q->orWhereHas(
                'subjectStaff',
                function (Builder $assignmentQuery) use (
                    $staff,
                    $academicYearId,
                    $termId
                ) {
                    $assignmentQuery
                        ->where(
                            'staff_id',
                            $staff->id
                        )

                        ->when(
                            $academicYearId !== null,
                            fn (Builder $query) =>
                                $query->where(
                                    'academic_year_id',
                                    $academicYearId
                                )
                        )

                        ->when(
                            $termId !== null,
                            fn (Builder $query) =>
                                $query->where(
                                    'term_id',
                                    $termId
                                )
                        );
                }
            );
        });

        return $this->applyAcademicYear(
            $query,
            $academicYearId
        );
    }

    /**
     * Determine whether the user can access a class.
     */
    public function canAccessClass(
        User $user,
        int $classId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        return $this->accessibleClasses(
            $user,
            $academicYearId,
            $termId
        )
            ->whereKey($classId)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS WRITE ACCESS
    |--------------------------------------------------------------------------
    */

    /**
     * Form Masters can edit their assigned classes.
     *
     * Subject Teachers can view their assigned classes but cannot
     * edit the class itself.
     */
    public function canEditClass(
        User $user,
        int $classId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$this->isScopedStaff($user)) {
            return true;
        }

        return $this->isFormMaster(
            $user,
            $classId,
            $academicYearId,
            $termId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS SUBJECT ACCESS
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether a user can access a subject within a class.
     *
     * Form Master:
     *     Can access all subjects in the assigned class.
     *
     * Subject Teacher:
     *     Can access only the assigned subject.
     */
    public function canAccessClassSubject(
        User $user,
        int $classId,
        int $subjectId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return $this->classMatchesAcademicYear(
                $classId,
                $academicYearId
            );
        }

        if (!$this->isScopedStaff($user)) {
            return $this->classMatchesAcademicYear(
                $classId,
                $academicYearId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FORM MASTER
        |--------------------------------------------------------------------------
        */

        if (
            $this->isFormMaster(
                $user,
                $classId,
                $academicYearId,
                $termId
            )
        ) {
            return $this->classMatchesAcademicYear(
                $classId,
                $academicYearId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT TEACHER
        |--------------------------------------------------------------------------
        */

        return $this->isSubjectTeacher(
            $user,
            $classId,
            $subjectId,
            $academicYearId,
            $termId
        );
    }

    /**
     * Determine whether a user can edit a subject.
     *
     * Form Master:
     *     Can edit any subject in their assigned class.
     *
     * Subject Teacher:
     *     Can edit only their assigned subject.
     */
    public function canEditClassSubject(
        User $user,
        int $classId,
        int $subjectId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$this->isScopedStaff($user)) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | FORM MASTER
        |--------------------------------------------------------------------------
        */

        if (
            $this->isFormMaster(
                $user,
                $classId,
                $academicYearId,
                $termId
            )
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT TEACHER
        |--------------------------------------------------------------------------
        */

        return $this->isSubjectTeacher(
            $user,
            $classId,
            $subjectId,
            $academicYearId,
            $termId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXISTING CONTROLLER COMPATIBILITY
    |--------------------------------------------------------------------------
    */

    /**
     * Existing controllers use this method name.
     */
    public function accessibleClassSubjects(
        User $user,
        ?int $academicYearId = null,
        ?int $termId = null
    ): Builder {
        return $this->subjectTeacherClasses(
            $user,
            $academicYearId,
            $termId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT ACCESS
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether a Teaching Staff member can access a student.
     *
     * Access is based on the student's current active class assignment
     * and whether that class is accessible to the staff member.
     */
    public function canAccessStudent(
        User $user,
        int $studentId,
        ?int $academicYearId = null,
        ?int $termId = null
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Super Admin / Non-scoped
        |--------------------------------------------------------------------------
        */

        if (
            $this->isSuperAdmin($user)
            || !$this->isScopedStaff($user)
        ) {
            return true;
        }

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        $termId = $this->resolveTermId(
            $termId
        );

        /*
        |--------------------------------------------------------------------------
        | Accessible classes
        |--------------------------------------------------------------------------
        */

        $classQuery = $this->accessibleClasses(
            $user,
            $academicYearId,
            $termId
        );

        /*
        |--------------------------------------------------------------------------
        | Current student assignment
        |--------------------------------------------------------------------------
        */

        $query = StudentClassAssignment::query()
            ->where(
                'student_id',
                $studentId
            )
            ->where(
                'is_current',
                true
            )
            ->where(
                'status',
                'active'
            )
            ->whereIn(
                'student_class_id',
                $classQuery->select(
                    'student_classes.id'
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Academic year
        |--------------------------------------------------------------------------
        */

        if ($academicYearId !== null) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Term
        |--------------------------------------------------------------------------
        */

        if (
            $termId !== null
            && Schema::hasColumn(
                'student_class_assignments',
                'term_id'
            )
        ) {
            $query->where(
                'term_id',
                $termId
            );
        }

        return $query->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS / ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether a class belongs to an academic year.
     */
    public function classMatchesAcademicYear(
        int $classId,
        ?int $academicYearId = null
    ): bool {
        $query = StudentClass::query()
            ->whereKey($classId);

        $academicYearId = $this->resolveAcademicYearId(
            $academicYearId
        );

        if ($academicYearId !== null) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        return $query->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Apply academic-year filtering to a StudentClass query.
     */
    private function applyAcademicYear(
        Builder $query,
        ?int $academicYearId
    ): Builder {
        if ($academicYearId !== null) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        return $query;
    }

    /**
     * StudentClass belongs to an academic year, not directly to a term.
     *
     * Therefore term filtering is performed through the relevant
     * assignment relationships rather than directly on student_classes.
     */
    private function applyPeriod(
        Builder $query,
        ?int $academicYearId = null,
        ?int $termId = null
    ): Builder {
        return $this->applyAcademicYear(
            $query,
            $academicYearId
        );
    }
}