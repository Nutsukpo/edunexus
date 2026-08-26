<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EdunexusAuthorizationService
{
    /**
     * Roles whose access is restricted by staff/class/subject assignments.
     * Other roles are school-wide unless a future scope rule says otherwise.
     */
    private const SCOPED_ROLES = [
        'Teaching Staff',
    ];

    public function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function isScopedStaff(User $user): bool
    {
        return $user->hasAnyRole(self::SCOPED_ROLES);
    }

    public function staffFor(User $user): ?Staff
    {
        return $user->staff()->first();
    }

    /**
     * Classes the user can access for class-level work such as attendance.
     */
    public function accessibleClasses(User $user, ?int $academicYearId = null): Builder
    {
        $query = StudentClass::query();

        if ($this->isSuperAdmin($user) || !$this->isScopedStaff($user)) {
            return $this->applyAcademicYear($query, $academicYearId);
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return $query->whereKey(-1);
        }

        $query->where(function (Builder $q) use ($staff) {
            // Class teacher
            $q->where('student_classes.staff_id', $staff->id)

              // General class/staff assignment
              ->orWhereHas('staff', function (Builder $staffQuery) use ($staff) {
                  $staffQuery->where('staff.id', $staff->id);
              })

              // Subject/class assignment
              ->orWhereHas('subjectStaff', function (Builder $assignmentQuery) use ($staff) {
                  $assignmentQuery->where('staff_id', $staff->id);
              });
        });

        return $this->applyAcademicYear($query, $academicYearId);
    }

    /**
     * Classes specifically assigned to the staff member for a subject.
     */
    public function accessibleClassSubjects(User $user, ?int $academicYearId = null): Builder
    {
        $query = StudentClass::query();

        if ($this->isSuperAdmin($user) || !$this->isScopedStaff($user)) {
            return $this->applyAcademicYear($query, $academicYearId);
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return $query->whereKey(-1);
        }

        $query->whereHas('subjectStaff', function (Builder $q) use ($staff) {
            $q->where('staff_id', $staff->id);
        });

        return $this->applyAcademicYear($query, $academicYearId);
    }

    public function canAccessClass(User $user, int $classId, ?int $academicYearId = null): bool
    {
        return $this->accessibleClasses($user, $academicYearId)
            ->whereKey($classId)
            ->exists();
    }

    /**
     * Subject-level access for Teaching Staff is intentionally stricter:
     * the staff member must be explicitly assigned to the class + subject.
     */
    public function canAccessClassSubject(
        User $user,
        int $classId,
        int $subjectId,
        ?int $academicYearId = null
    ): bool {
        if ($this->isSuperAdmin($user) || !$this->isScopedStaff($user)) {
            return $this->classMatchesAcademicYear($classId, $academicYearId);
        }

        $staff = $this->staffFor($user);

        if (!$staff) {
            return false;
        }

        $query = \App\Models\ClassSubjectStaff::query()
            ->where('student_class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('staff_id', $staff->id);

        if ($academicYearId) {
            $query->whereHas('studentClass', function (Builder $q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        } else {
            $activeYear = AcademicYear::where('is_active', true)->first();

            if ($activeYear) {
                $query->whereHas('studentClass', function (Builder $q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                });
            }
        }

        return $query->exists();
    }

    public function canAccessStudent(User $user, int $studentId, ?int $academicYearId = null): bool
    {
        if ($this->isSuperAdmin($user) || !$this->isScopedStaff($user)) {
            return true;
        }

        $classQuery = $this->accessibleClasses($user, $academicYearId);

        return StudentClassAssignment::query()
            ->where('student_id', $studentId)
            ->where('is_current', true)
            ->where('status', 'active')
            ->whereIn('student_class_id', $classQuery->select('student_classes.id'))
            ->exists();
    }

    public function classMatchesAcademicYear(int $classId, ?int $academicYearId = null): bool
    {
        $query = StudentClass::whereKey($classId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        } else {
            $activeYear = AcademicYear::where('is_active', true)->first();

            if ($activeYear) {
                $query->where('academic_year_id', $activeYear->id);
            }
        }

        return $query->exists();
    }

    private function applyAcademicYear(Builder $query, ?int $academicYearId): Builder
    {
        if ($academicYearId) {
            return $query->where('academic_year_id', $academicYearId);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        return $query;
    }
}
