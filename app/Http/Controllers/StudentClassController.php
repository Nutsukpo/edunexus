<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\BillSheet;
use App\Models\ClassSubjectStaff;
use App\Models\FeePayment;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentClassStaff;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Services\EdunexusAuthorizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    protected EdunexusAuthorizationService $authorization;

    public function __construct(
        EdunexusAuthorizationService $authorization
    ) {
        $this->authorization = $authorization;
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY ALL CLASSES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user = auth()->user();

        if ($this->authorization->isScopedStaff($user)) {
            $classes = $this->authorization
                ->accessibleClasses($user)
                ->with([
                    'academicYear',
                    'classTeacher',
                ])
                ->latest()
                ->get();
        } else {
            $classes = StudentClass::with([
                'academicYear',
                'classTeacher',
            ])
                ->latest()
                ->get();
        }

        return view(
            'student_classes.index',
            compact('classes')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $academicYears = AcademicYear::latest()->get();

        $staff = Staff::query()
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view(
            'student_classes.create',
            compact(
                'academicYears',
                'staff'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE CLASS
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:255',
            'education_type'   => 'required|string|max:255',
            'class_type'       => 'required|string|max:255',
            'stream'           => 'nullable|string|max:50',
            'staff_id'         => 'nullable|exists:staff,id',
            'capacity'         => 'nullable|integer|min:1',
        ]);

        StudentClass::create($validated);

        return redirect()
            ->route('student-classes.index')
            ->with('success', 'Class created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE CLASS
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | LOAD CLASS
        |--------------------------------------------------------------------------
        |
        | Keep the existing class relationships intact.
        |
        */
        $studentClass = StudentClass::with([
            'assignments.student',
            'subjects.staff',
            'classTeacher',
            'classPrefect',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | CLASS ACCESS AUTHORIZATION
        |--------------------------------------------------------------------------
        |
        | Teaching Staff can access a class when they are:
        |
        | 1. Form Master, OR
        | 2. Subject Teacher.
        |
        | The authorization service remains the security boundary.
        |
        */
        abort_unless(
            $this->authorization->canAccessClass(
                $user,
                $studentClass->id
            ),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | SYSTEM SETTINGS / CURRENT VIEWING PERIOD
        |--------------------------------------------------------------------------
        */
        $settings = SystemSetting::instance();

        $currentAcademicYear = $settings->currentAcademicYear;
        $currentTerm = $settings->currentTerm;

        /*
        |--------------------------------------------------------------------------
        | VIEW AUTHORIZATION FLAGS
        |--------------------------------------------------------------------------
        |
        | Authorization is calculated here in the controller and passed to
        | the Blade view. The Blade must not call the authorization service
        | directly.
        |
        */
        $canAccessClass = $this->authorization->canAccessClass(
            $user,
            $studentClass->id
        );

        $canEditClass = $this->authorization->canEditClass(
            $user,
            $studentClass->id
        );

        /*
        |--------------------------------------------------------------------------
        | CURRENT FORM MASTER
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The period-aware student_class_staff table is now the source
        | of truth for the Form Master displayed on this page.
        |
        | We deliberately do NOT use:
        |
        |     $studentClass->staff_id
        |
        | because that legacy field is not period-aware.
        |
        */
        $formMaster = null;

        if ($currentAcademicYear && $currentTerm) {
            $formMaster = StudentClassStaff::query()
                ->with('staff')
                ->where(
                    'student_class_id',
                    $studentClass->id
                )
                ->where(
                    'academic_year_id',
                    $currentAcademicYear->id
                )
                ->where(
                    'term_id',
                    $currentTerm->id
                )
                ->first()
                ?->staff;
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENTS / ASSIGNMENTS FOR SELECTED VIEWING PERIOD
        |--------------------------------------------------------------------------
        */
        $activeAssignments = collect();

        if ($currentAcademicYear && $currentTerm) {
            $activeAssignments = $studentClass->assignments
                ->filter(function ($assignment) use (
                    $currentAcademicYear,
                    $currentTerm
                ) {
                    return (int) $assignment->academic_year_id ===
                        (int) $currentAcademicYear->id
                        && (int) $assignment->term_id ===
                        (int) $currentTerm->id
                        && in_array(
                            (string) $assignment->status,
                            [
                                'active',
                                'repeated',
                                'pending',
                                'completed',
                            ],
                            true
                        )
                        && $assignment->student !== null;
                })
                ->sortBy(function ($assignment) {
                    return strtolower(
                        trim(
                            ($assignment->student->first_name ?? '') .
                            ' ' .
                            ($assignment->student->middle_name ?? '') .
                            ' ' .
                            ($assignment->student->last_name ?? '')
                        )
                    );
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT ACTIVE STUDENTS
        |--------------------------------------------------------------------------
        */
        $activeStudentAssignments = $activeAssignments
            ->filter(function ($assignment) {
                return (string) $assignment->status === 'active'
                    && (bool) $assignment->is_current
                    && $assignment->student !== null;
            })
            ->unique('student_id')
            ->values();

        $students = $activeStudentAssignments
            ->pluck('student')
            ->filter()
            ->values();

        $totalStudents = $students
            ->unique('id')
            ->count();

        $maleCount = $students
            ->unique('id')
            ->where('gender', 'Male')
            ->count();

        $femaleCount = $students
            ->unique('id')
            ->where('gender', 'Female')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE
        |--------------------------------------------------------------------------
        */
        $attendanceRate = $this->calculateAttendanceRate(
            $studentClass->id,
            $currentAcademicYear?->id,
            $currentTerm?->id,
            $activeAssignments
                ->pluck('id')
                ->filter()
                ->unique()
                ->values()
        );

        /*
        |--------------------------------------------------------------------------
        | FEES
        |--------------------------------------------------------------------------
        |
        | Fee/payment logic remains unchanged.
        | No payment records are modified here.
        |
        */
        $classBillSheets = collect();
        $classFeePayments = collect();

        if (
            $currentAcademicYear &&
            $currentTerm &&
            $activeAssignments->isNotEmpty()
        ) {
            $assignmentIds = $activeAssignments
                ->pluck('id')
                ->filter()
                ->unique()
                ->values();

            if ($assignmentIds->isNotEmpty()) {
                $classBillSheets = BillSheet::query()
                    ->whereIn(
                        'student_class_assignment_id',
                        $assignmentIds
                    )
                    ->where(
                        'academic_year_id',
                        $currentAcademicYear->id
                    )
                    ->where(
                        'term_id',
                        $currentTerm->id
                    )
                    ->where('is_active', true)
                    ->whereIn('status', [
                        'draft',
                        'pending',
                        'approved',
                        'published',
                    ])
                    ->get();

                $billSheetIds = $classBillSheets
                    ->pluck('id')
                    ->filter()
                    ->unique()
                    ->values();

                if ($billSheetIds->isNotEmpty()) {
                    $classFeePayments = FeePayment::query()
                        ->whereIn(
                            'bill_sheet_id',
                            $billSheetIds
                        )
                        ->where('status', 'completed')
                        ->get();
                }
            }
        }

        $totalClassFees = $classBillSheets->sum(
            fn ($bill) => $this->billTotal($bill)
        );

        $totalClassFeesPaid = $classFeePayments->sum(
            fn ($payment) => (float) (
                $payment->net_amount ??
                $payment->amount ??
                0
            )
        );

        $totalClassFeesBalance = max(
            0,
            round(
                $totalClassFees -
                $totalClassFeesPaid,
                2
            )
        );

        $feesPaidPercentage = $totalClassFees > 0
            ? min(
                100,
                round(
                    (
                        $totalClassFeesPaid /
                        $totalClassFees
                    ) * 100,
                    1
                )
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | PER-STUDENT FEE DATA
        |--------------------------------------------------------------------------
        */
        $feeData = $activeAssignments
            ->map(function ($assignment) use (
                $classBillSheets,
                $classFeePayments
            ) {
                $student = $assignment->student;

                $studentBills = $classBillSheets->where(
                    'student_class_assignment_id',
                    $assignment->id
                );

                $studentBillSheetIds = $studentBills
                    ->pluck('id')
                    ->filter()
                    ->unique()
                    ->values();

                $studentPayments = $classFeePayments->filter(
                    function ($payment) use (
                        $studentBillSheetIds
                    ) {
                        return $studentBillSheetIds->contains(
                            $payment->bill_sheet_id
                        );
                    }
                );

                $totalFees = $studentBills->sum(
                    fn ($bill) => $this->billTotal($bill)
                );

                $amountPaid = $studentPayments->sum(
                    fn ($payment) => (float) (
                        $payment->net_amount ??
                        $payment->amount ??
                        0
                    )
                );

                $balance = max(
                    0,
                    round(
                        $totalFees -
                        $amountPaid,
                        2
                    )
                );

                if ($totalFees <= 0) {
                    $status = 'No Bill';
                    $statusClass = 'secondary';
                    $paymentPercentage = 0;
                } elseif ($balance <= 0) {
                    $status = 'Paid';
                    $statusClass = 'success';
                    $paymentPercentage = 100;
                } elseif ($amountPaid > 0) {
                    $status = 'Partially Paid';
                    $statusClass = 'warning';

                    $paymentPercentage = min(
                        100,
                        round(
                            (
                                $amountPaid /
                                $totalFees
                            ) * 100,
                            1
                        )
                    );
                } else {
                    $status = 'Unpaid';
                    $statusClass = 'danger';
                    $paymentPercentage = 0;
                }

                return [
                    'assignment_id' => $assignment->id,
                    'student' => $student,
                    'assignment_status' => $assignment->status,
                    'total_fees' => $totalFees,
                    'amount_paid' => $amountPaid,
                    'balance' => $balance,
                    'status' => $status,
                    'status_class' => $statusClass,
                    'payment_percentage' => $paymentPercentage,
                ];
            })
            ->filter(function ($fee) {
                return (float) (
                    $fee['total_fees'] ?? 0
                ) > 0;
            })
            ->values();

        $paidStudents = $feeData
            ->where('status', 'Paid')
            ->count();

        $partialStudents = $feeData
            ->where('status', 'Partially Paid')
            ->count();

        $unpaidStudents = $feeData
            ->where('status', 'Unpaid')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | CLASS SUBJECTS
        |--------------------------------------------------------------------------
        */
        $classSubjects = $studentClass->subjects ?? collect();

        /*
        |--------------------------------------------------------------------------
        | RETURN CLASS PAGE
        |--------------------------------------------------------------------------
        */
        return view(
            'student_classes.show',
            compact(
                'studentClass',
                'formMaster',
                'canAccessClass',
                'canEditClass',
                'attendanceRate',
                'settings',
                'currentAcademicYear',
                'currentTerm',
                'activeAssignments',
                'students',
                'activeStudentAssignments',
                'totalStudents',
                'maleCount',
                'femaleCount',
                'classBillSheets',
                'classFeePayments',
                'totalClassFees',
                'totalClassFeesPaid',
                'totalClassFeesBalance',
                'feesPaidPercentage',
                'feeData',
                'paidStudents',
                'partialStudents',
                'unpaidStudents',
                'classSubjects'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(StudentClass $studentClass)
    {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $academicYears = AcademicYear::latest()->get();

        $staff = Staff::query()
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view(
            'student_classes.edit',
            compact(
                'studentClass',
                'academicYears',
                'staff'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        StudentClass $studentClass
    ) {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:255',
            'education_type'   => 'required|string|max:255',
            'class_type'       => 'required|string|max:255',
            'stream'           => 'nullable|string|max:50',
            'staff_id'         => 'nullable|exists:staff,id',
            'capacity'         => 'nullable|integer|min:1',
        ]);

        $studentClass->update($validated);

        return redirect()
            ->route('student-classes.index')
            ->with(
                'success',
                'Class updated successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(StudentClass $studentClass)
    {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $studentClass->delete();

        return redirect()
            ->route('student-classes.index')
            ->with(
                'success',
                'Class deleted successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT ATTACH
    |--------------------------------------------------------------------------
    */
    public function attachSubject(
        Request $request,
        $classId
    ) {
        $class = StudentClass::findOrFail($classId);

        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $class->id
            ),
            403
        );

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $class->subjects()
            ->syncWithoutDetaching(
                $request->subject_id
            );

        return back()->with(
            'success',
            'Subject assigned successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT DETACH
    |--------------------------------------------------------------------------
    */
    public function detachSubject(
        $classId,
        $subjectId
    ) {
        $class = StudentClass::findOrFail($classId);

        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $class->id
            ),
            403
        );

        $class->subjects()->detach($subjectId);

        return back()->with(
            'success',
            'Subject removed successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */
    public function assignSubjectTeacher(
        Request $request,
        StudentClass $studentClass
    ) {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'staff_id'   => 'required|exists:staff,id',
        ]);

        ClassSubjectStaff::updateOrCreate(
            [
                'student_class_id' => $studentClass->id,
                'subject_id'       => $request->subject_id,
            ],
            [
                'staff_id' => $request->staff_id,
            ]
        );

        return back()->with(
            'success',
            'Teacher assigned successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE SUBJECT TEACHER
    |--------------------------------------------------------------------------
    */
    public function removeSubjectTeacher(
        StudentClass $studentClass,
        Subject $subject
    ) {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $studentClass->id
            ),
            403
        );

        ClassSubjectStaff::where(
            'student_class_id',
            $studentClass->id
        )
            ->where(
                'subject_id',
                $subject->id
            )
            ->delete();

        return back()->with(
            'success',
            'Assignment removed.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN PREFECT
    |--------------------------------------------------------------------------
    */
    public function assignPrefect(
        Request $request,
        StudentClass $studentClass
    ) {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canEditClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $exists = StudentClassAssignment::where(
            'student_class_id',
            $studentClass->id
        )
            ->where(
                'student_id',
                $request->student_id
            )
            ->where('is_current', true)
            ->exists();

        if (!$exists) {
            return back()->with(
                'error',
                'Student not in this class.'
            );
        }

        $studentClass->update([
            'class_prefect_id' => $request->student_id,
        ]);

        return back()->with(
            'success',
            'Class prefect assigned successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET ATTENDANCE DATA FOR CHART
    |--------------------------------------------------------------------------
    */
    public function getAttendanceData(
        StudentClass $studentClass,
        Request $request
    ) {
        $user = auth()->user();

        abort_unless(
            $this->authorization->canAccessClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $settings = SystemSetting::instance();

        $academicYear = $settings->currentAcademicYear;
        $term = $settings->currentTerm;

        if (!$academicYear || !$term) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Academic Year and Term are not configured.',
            ], 422);
        }

        $period = $request->get(
            'period',
            'month'
        );

        [$termStart, $termEnd] =
            $this->attendancePeriodBounds($term);

        if ($termEnd->lessThan($termStart)) {
            return response()->json([
                'success' => true,
                'labels' => [],
                'data' => [],
                'termAvg' => 0,
                'totalStudents' => 0,
                'stats' => [
                    'total_sessions' => 0,
                    'overall_rate' => 0,
                    'total_present' => 0,
                    'total_absent' => 0,
                    'total_late' => 0,
                    'total_excused' => 0,
                ],
            ]);
        }

        $endDate = $termEnd->copy();

        switch ($period) {
            case 'week':
                $startDate = $endDate
                    ->copy()
                    ->subDays(6)
                    ->startOfDay();
                break;

            case 'term':
                $startDate = $termStart->copy();
                break;

            case 'month':
            default:
                $startDate = $endDate
                    ->copy()
                    ->subDays(29)
                    ->startOfDay();
                break;
        }

        if ($startDate->lessThan($termStart)) {
            $startDate = $termStart->copy();
        }

        $studentIds = $this->periodStudentIds(
            $studentClass->id,
            $academicYear->id,
            $term->id
        );

        $assignmentIds = StudentClassAssignment::query()
            ->where(
                'student_class_id',
                $studentClass->id
            )
            ->where(
                'academic_year_id',
                $academicYear->id
            )
            ->where(
                'term_id',
                $term->id
            )
            ->whereIn('status', [
                'active',
                'repeated',
                'pending',
                'completed',
            ])
            ->pluck('id');

        $sessions = AttendanceSession::query()
            ->where(
                'student_class_id',
                $studentClass->id
            )
            ->whereBetween(
                'attendance_date',
                [$startDate, $endDate]
            )
            ->where(function ($query) use (
                $academicYear,
                $term
            ) {
                $query->where(function ($q) use (
                    $academicYear,
                    $term
                ) {
                    $q->where(
                        'academic_year_id',
                        $academicYear->id
                    )
                        ->where(
                            'term_id',
                            $term->id
                        );
                })
                    ->orWhere(function ($q) {
                        $q->whereNull(
                            'academic_year_id'
                        )
                            ->whereNull(
                                'term_id'
                            );
                    });
            })
            ->orderBy('attendance_date')
            ->get();

        $sessionIds = $sessions
            ->pluck('id')
            ->values();

        $records = $sessionIds->isNotEmpty()
            ? \App\Models\Attendance::query()
                ->whereIn(
                    'attendance_session_id',
                    $sessionIds
                )
                ->when(
                    $assignmentIds->isNotEmpty(),
                    function ($query) use (
                        $assignmentIds
                    ) {
                        $query->whereIn(
                            'student_class_assignment_id',
                            $assignmentIds
                        );
                    },
                    function ($query) {
                        $query->whereRaw(
                            '1 = 0'
                        );
                    }
                )
                ->get()
            : collect();

        $recordsBySession = $records
            ->groupBy('attendance_session_id');

        $dailyData = [];

        foreach ($sessions as $session) {
            $date = Carbon::parse(
                $session->attendance_date
            )->format('M d');

            $sessionRecords =
                $recordsBySession->get(
                    $session->id,
                    collect()
                );

            $marked = $sessionRecords
                ->whereIn('status', [
                    'present',
                    'absent',
                    'late',
                    'excused',
                ])
                ->count();

            $attended = $sessionRecords
                ->whereIn('status', [
                    'present',
                    'late',
                ])
                ->count();

            $dailyData[$date] = $marked > 0
                ? round(
                    ($attended / $marked) * 100,
                    1
                )
                : 0;
        }

        $totalPresent = $records
            ->where('status', 'present')
            ->count();

        $totalAbsent = $records
            ->where('status', 'absent')
            ->count();

        $totalLate = $records
            ->where('status', 'late')
            ->count();

        $totalExcused = $records
            ->where('status', 'excused')
            ->count();

        $totalMarked =
            $totalPresent +
            $totalAbsent +
            $totalLate +
            $totalExcused;

        $overallRate = $totalMarked > 0
            ? round(
                (
                    (
                        $totalPresent +
                        $totalLate
                    ) / $totalMarked
                ) * 100,
                1
            )
            : 0;

        return response()->json([
            'success' => true,
            'labels' => array_keys($dailyData),
            'data' => array_values($dailyData),
            'termAvg' => count($dailyData) > 0
                ? round(
                    array_sum($dailyData) /
                    count($dailyData),
                    1
                )
                : 0,
            'totalStudents' => $studentIds->count(),
            'stats' => [
                'total_sessions' => $sessions->count(),
                'overall_rate' => $overallRate,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'total_excused' => $totalExcused,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE DATA FOR CLASS ATTENDANCE TAB
    |--------------------------------------------------------------------------
    */
    public function attendanceData(
        Request $request,
        $id
    ) {
        $studentClass = StudentClass::findOrFail($id);

        $user = auth()->user();

        abort_unless(
            $this->authorization->canAccessClass(
                $user,
                $studentClass->id
            ),
            403
        );

        $settings = SystemSetting::instance();

        $academicYear = $settings->currentAcademicYear;
        $term = $settings->currentTerm;

        if (!$academicYear || !$term) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Academic Year and Term are not configured.',
            ], 422);
        }

        [$termStart, $termEnd] =
            $this->attendancePeriodBounds($term);

        if ($termEnd->lessThan($termStart)) {
            return response()->json([
                'success' => true,
                'today_rate' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
                'total_sessions' => 0,
                'sessions' => [],
                'students' => [],
                'chart' => [
                    'labels' => [],
                    'data' => [],
                ],
            ]);
        }

        $fromDate = $request->filled('from_date')
            ? Carbon::parse(
                $request->from_date
            )->startOfDay()
            : $termStart->copy();

        $toDate = $request->filled('to_date')
            ? Carbon::parse(
                $request->to_date
            )->endOfDay()
            : $termEnd->copy();

        if ($fromDate->lessThan($termStart)) {
            $fromDate = $termStart->copy();
        }

        if ($toDate->greaterThan($termEnd)) {
            $toDate = $termEnd->copy();
        }

        if ($toDate->lessThan($fromDate)) {
            $toDate = $fromDate
                ->copy()
                ->endOfDay();
        }

        $assignmentIds =
            StudentClassAssignment::query()
                ->where(
                    'student_class_id',
                    $studentClass->id
                )
                ->where(
                    'academic_year_id',
                    $academicYear->id
                )
                ->where(
                    'term_id',
                    $term->id
                )
                ->whereIn('status', [
                    'active',
                    'repeated',
                    'pending',
                    'completed',
                ])
                ->pluck('id');

        $studentIds = $this->periodStudentIds(
            $studentClass->id,
            $academicYear->id,
            $term->id
        );

        $sessions = AttendanceSession::query()
            ->where(
                'student_class_id',
                $studentClass->id
            )
            ->whereBetween(
                'attendance_date',
                [$fromDate, $toDate]
            )
            ->where(function ($query) use (
                $academicYear,
                $term
            ) {
                $query->where(function ($q) use (
                    $academicYear,
                    $term
                ) {
                    $q->where(
                        'academic_year_id',
                        $academicYear->id
                    )
                        ->where(
                            'term_id',
                            $term->id
                        );
                })
                    ->orWhere(function ($q) {
                        $q->whereNull(
                            'academic_year_id'
                        )
                            ->whereNull(
                                'term_id'
                            );
                    });
            })
            ->with('takenBy')
            ->latest('attendance_date')
            ->get();

        $sessionIds = $sessions
            ->pluck('id')
            ->values();

        $records = $sessionIds->isNotEmpty()
            ? \App\Models\Attendance::query()
                ->whereIn(
                    'attendance_session_id',
                    $sessionIds
                )
                ->when(
                    $assignmentIds->isNotEmpty(),
                    function ($query) use (
                        $assignmentIds
                    ) {
                        $query->whereIn(
                            'student_class_assignment_id',
                            $assignmentIds
                        );
                    },
                    function ($query) {
                        $query->whereRaw(
                            '1 = 0'
                        );
                    }
                )
                ->with('student')
                ->get()
            : collect();

        $recordsBySession = $records
            ->groupBy('attendance_session_id');

        $todaySession = $sessions
            ->filter(function ($session) {
                return Carbon::parse(
                    $session->attendance_date
                )->isToday();
            })
            ->sortByDesc('attendance_date')
            ->first();

        $todayRecords = $todaySession
            ? $recordsBySession->get(
                $todaySession->id,
                collect()
            )
            : collect();

        $presentCount = $todayRecords
            ->where('status', 'present')
            ->count();

        $absentCount = $todayRecords
            ->where('status', 'absent')
            ->count();

        $lateCount = $todayRecords
            ->where('status', 'late')
            ->count();

        $excusedCount = $todayRecords
            ->where('status', 'excused')
            ->count();

        $todayMarked =
            $presentCount +
            $absentCount +
            $lateCount +
            $excusedCount;

        $todayRate = $todayMarked > 0
            ? round(
                (
                    (
                        $presentCount +
                        $lateCount
                    ) / $todayMarked
                ) * 100
            )
            : 0;

        $formattedSessions = $sessions
            ->map(
                function ($session) use (
                    $recordsBySession
                ) {
                    $sessionRecords =
                        $recordsBySession->get(
                            $session->id,
                            collect()
                        );

                    $present = $sessionRecords
                        ->where(
                            'status',
                            'present'
                        )
                        ->count();

                    $absent = $sessionRecords
                        ->where(
                            'status',
                            'absent'
                        )
                        ->count();

                    $late = $sessionRecords
                        ->where(
                            'status',
                            'late'
                        )
                        ->count();

                    $excused = $sessionRecords
                        ->where(
                            'status',
                            'excused'
                        )
                        ->count();

                    $marked =
                        $present +
                        $absent +
                        $late +
                        $excused;

                    return [
                        'id' => $session->id,
                        'date' => Carbon::parse(
                            $session->attendance_date
                        )->format('d M Y'),
                        'present' => $present,
                        'absent' => $absent,
                        'late' => $late,
                        'excused' => $excused,
                        'rate' => $marked > 0
                            ? round(
                                (
                                    (
                                        $present +
                                        $late
                                    ) / $marked
                                ) * 100
                            )
                            : 0,
                        'taken_by' =>
                            optional(
                                $session->takenBy
                            )->name ?? 'N/A',
                    ];
                }
            )
            ->values();

        $studentModels = $studentIds->isNotEmpty()
            ? \App\Models\Student::whereIn(
                'id',
                $studentIds
            )
                ->get()
                ->keyBy('id')
            : collect();

        $recordsByStudent = $records
            ->whereIn(
                'student_id',
                $studentIds
            )
            ->groupBy('student_id');

        $students = [];

        foreach ($studentIds as $studentId) {
            $student = $studentModels->get(
                $studentId
            );

            if (!$student) {
                continue;
            }

            $studentRecords =
                $recordsByStudent->get(
                    $studentId,
                    collect()
                );

            $present = $studentRecords
                ->where('status', 'present')
                ->count();

            $absent = $studentRecords
                ->where('status', 'absent')
                ->count();

            $late = $studentRecords
                ->where('status', 'late')
                ->count();

            $excused = $studentRecords
                ->where('status', 'excused')
                ->count();

            $marked =
                $present +
                $absent +
                $late +
                $excused;

            $students[] = [
                'id' => $student->id,
                'name' => trim(
                    ($student->first_name ?? '') .
                    ' ' .
                    ($student->middle_name ?? '') .
                    ' ' .
                    ($student->last_name ?? '')
                ),
                'student_id' =>
                    $student->student_id,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'rate' => $marked > 0
                    ? round(
                        (
                            (
                                $present +
                                $late
                            ) / $marked
                        ) * 100
                    )
                    : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'today_rate' => $todayRate,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'excused' => $excusedCount,
            'total_sessions' => $sessions->count(),
            'sessions' => $formattedSessions,
            'students' => $students,
            'chart' => [
                'labels' => $formattedSessions
                    ->pluck('date')
                    ->values()
                    ->all(),
                'data' => $formattedSessions
                    ->pluck('rate')
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PERIOD STUDENT IDS
    |--------------------------------------------------------------------------
    */
    private function periodStudentIds(
        int $classId,
        ?int $academicYearId,
        ?int $termId
    ) {
        if (!$academicYearId || !$termId) {
            return collect();
        }

        return StudentClassAssignment::query()
            ->where(
                'student_class_id',
                $classId
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'term_id',
                $termId
            )
            ->whereIn('status', [
                'active',
                'repeated',
                'pending',
                'completed',
            ])
            ->whereNotNull('student_id')
            ->distinct()
            ->pluck('student_id')
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE PERIOD BOUNDS
    |--------------------------------------------------------------------------
    */
    private function attendancePeriodBounds(
        $term
    ): array {
        $termStart = $term->start_date
            ? Carbon::parse(
                $term->start_date
            )->startOfDay()
            : Carbon::now()->startOfDay();

        $termEnd = $term->end_date
            ? Carbon::parse(
                $term->end_date
            )->endOfDay()
            : Carbon::now()->endOfDay();

        if ($termEnd->greaterThan(Carbon::now())) {
            $termEnd = Carbon::now()->endOfDay();
        }

        return [
            $termStart,
            $termEnd,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SELECTED-PERIOD ATTENDANCE RATE
    |--------------------------------------------------------------------------
    */
    private function calculateAttendanceRate(
        int $classId,
        ?int $academicYearId,
        ?int $termId,
        $assignmentIds
    ): float {
        if (
            !$academicYearId ||
            !$termId ||
            $assignmentIds->isEmpty()
        ) {
            return 0.0;
        }

        $term = \App\Models\Term::find(
            $termId
        );

        if (!$term) {
            return 0.0;
        }

        [$termStart, $termEnd] =
            $this->attendancePeriodBounds(
                $term
            );

        if ($termEnd->lessThan($termStart)) {
            return 0.0;
        }

        $sessionIds = AttendanceSession::query()
            ->where(
                'student_class_id',
                $classId
            )
            ->whereBetween(
                'attendance_date',
                [$termStart, $termEnd]
            )
            ->where(function ($query) use (
                $academicYearId,
                $termId
            ) {
                $query->where(function ($q) use (
                    $academicYearId,
                    $termId
                ) {
                    $q->where(
                        'academic_year_id',
                        $academicYearId
                    )
                        ->where(
                            'term_id',
                            $termId
                        );
                })
                    ->orWhere(function ($q) {
                        $q->whereNull(
                            'academic_year_id'
                        )
                            ->whereNull(
                                'term_id'
                            );
                    });
            })
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return 0.0;
        }

        $attendance =
            \App\Models\Attendance::query()
                ->whereIn(
                    'attendance_session_id',
                    $sessionIds
                )
                ->whereIn(
                    'student_class_assignment_id',
                    $assignmentIds
                )
                ->selectRaw("
                    COUNT(*) as total_marked,
                    SUM(
                        CASE
                            WHEN LOWER(status)
                                IN ('present', 'late')
                            THEN 1
                            ELSE 0
                        END
                    ) as attended_count
                ")
                ->first();

        $totalMarked = (int) (
            $attendance->total_marked ?? 0
        );

        $attendedCount = (int) (
            $attendance->attended_count ?? 0
        );

        return $totalMarked > 0
            ? round(
                (
                    $attendedCount /
                    $totalMarked
                ) * 100,
                1
            )
            : 0.0;
    }

    /*
    |--------------------------------------------------------------------------
    | BILL TOTAL
    |--------------------------------------------------------------------------
    */
    private function billTotal(
        $bill
    ): float {
        return (float) (
            $bill->net_amount ??
            $bill->total_amount ??
            0
        );
    }
}