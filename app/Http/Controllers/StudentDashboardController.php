<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentFeeAccount;
use App\Models\StudentResult;
use App\Models\SystemSetting;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\TimetablePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StudentDashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STUDENT DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $student = $this->student();

        /*
        |--------------------------------------------------------------------------
        | ADMIN-CONTROLLED OPERATIONAL PERIOD
        |--------------------------------------------------------------------------
        |
        | The student portal MUST inherit the academic period selected by
        | administrators in System Settings.
        |
        | We do not independently select the first active year/term.
        |--------------------------------------------------------------------------
        */
        $settings = $this->systemSettings();

        $academicYear = $this->operationalAcademicYear($settings);
        $term = $this->operationalTerm($settings, $academicYear);

        /*
        |--------------------------------------------------------------------------
        | CURRENT ASSIGNMENT
        |--------------------------------------------------------------------------
        |
        | The student's current assignment must belong to the same operational
        | academic year and term configured by the administrator.
        |--------------------------------------------------------------------------
        */
        $assignment = $this->currentAssignment(
            $student,
            $academicYear,
            $term
        );

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $attendanceQuery = Attendance::query()
            ->where('student_id', $student->id);

        /*
        |--------------------------------------------------------------------------
        | Restrict attendance to current assignment where possible.
        |--------------------------------------------------------------------------
        */
        if ($assignment) {

            $attendanceQuery->where(function ($query) use ($assignment) {

                $query
                    ->where(
                        'student_class_assignment_id',
                        $assignment->id
                    )
                    ->orWhereNull(
                        'student_class_assignment_id'
                    );

            });
        }

        $total = (clone $attendanceQuery)->count();

        $present = (clone $attendanceQuery)
            ->where('status', 'present')
            ->count();

        $absent = (clone $attendanceQuery)
            ->where('status', 'absent')
            ->count();

        $late = (clone $attendanceQuery)
            ->where('status', 'late')
            ->count();

        $excused = (clone $attendanceQuery)
            ->where('status', 'excused')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Attendance percentage
        |--------------------------------------------------------------------------
        |
        | Present + Late = attended.
        | Excused is excluded from the denominator.
        |--------------------------------------------------------------------------
        */
        $attendanceBase =
            $present +
            $absent +
            $late;

        $attendancePercentage =
            $attendanceBase > 0
                ? round(
                    (
                        ($present + $late)
                        / $attendanceBase
                    ) * 100,
                    1
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | FEE ACCOUNT
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Fee calculations remain assignment-specific.
        |
        | Nothing is created, updated, deleted or recalculated here.
        |--------------------------------------------------------------------------
        */

        $feeAccount = null;

        $totalFees = 0;
        $amountPaid = 0;
        $feeBalance = 0;

        if ($assignment) {

            $feeAccount = StudentFeeAccount::query()
                ->where(
                    'student_id',
                    $student->id
                )
                ->where(
                    'student_class_assignment_id',
                    $assignment->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->latest('id')
                ->first();

            if ($feeAccount) {

                $totalFees = (float) (
                    $feeAccount->total_fees ?? 0
                );

                $amountPaid = (float) FeePayment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'student_class_assignment_id',
                        $assignment->id
                    )
                    ->where(
                        'student_fee_account_id',
                        $feeAccount->id
                    )
                    ->where(
                        'status',
                        'completed'
                    )
                    ->sum('net_amount');

                $feeBalance = max(
                    0,
                    $totalFees - $amountPaid
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT COUNT
        |--------------------------------------------------------------------------
        */

        $subjects = 0;

        if (
            $assignment &&
            $assignment->studentClass
        ) {

            $subjects = $assignment
                ->studentClass
                ->subjects()
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | RECENT RESULTS
        |--------------------------------------------------------------------------
        */

        $recentResults = StudentResult::query()
            ->where(
                'student_id',
                $student->id
            )
            ->latest('id')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ANNOUNCEMENTS
        |--------------------------------------------------------------------------
        */

        $announcements = Announcement::published()
            ->where(function ($query) {

                $query
                    ->where(
                        'audience',
                        'all'
                    )
                    ->orWhere(
                        'audience',
                        'students'
                    );

            })
            ->latest('created_at')
            ->take(4)
            ->get();

        $featuredAnnouncement =
            Announcement::published()
                ->featured()
                ->where(function ($query) {

                    $query
                        ->where(
                            'audience',
                            'all'
                        )
                        ->orWhere(
                            'audience',
                            'students'
                        );

                })
                ->first();

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view(
            'students.dashboard',
            compact(
                'student',
                'settings',
                'assignment',
                'academicYear',
                'term',

                // Attendance
                'total',
                'present',
                'absent',
                'late',
                'excused',
                'attendancePercentage',

                // Fees
                'feeAccount',
                'totalFees',
                'amountPaid',
                'feeBalance',

                // Academic
                'subjects',
                'recentResults',

                // Announcements
                'announcements',
                'featuredAnnouncement'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function attendance(Request $request)
    {
        $student = $this->student();

        /*
        |--------------------------------------------------------------------------
        | Base attendance query
        |--------------------------------------------------------------------------
        */

        $baseQuery = Attendance::query()
            ->where(
                'student_id',
                $student->id
            )
            ->whereHas(
                'session',
                function ($query) {
                    $query->where(
                        'status',
                        'completed'
                    );
                }
            )
            ->with([
                'session.studentClass',
                'classAssignment.studentClass',
            ]);

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE CLASSES
        |--------------------------------------------------------------------------
        */

        $classes = StudentClass::query()
            ->whereIn(
                'id',
                Attendance::where(
                    'student_id',
                    $student->id
                )
                    ->whereHas(
                        'session',
                        function ($query) {
                            $query->where(
                                'status',
                                'completed'
                            );
                        }
                    )
                    ->with('session')
                    ->get()
                    ->pluck(
                        'session.student_class_id'
                    )
                    ->filter()
                    ->unique()
                    ->values()
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE MONTHS
        |--------------------------------------------------------------------------
        */

        $months = Attendance::where(
            'student_id',
            $student->id
        )
            ->whereHas(
                'session',
                function ($query) {
                    $query->where(
                        'status',
                        'completed'
                    );
                }
            )
            ->with(
                'session:id,attendance_date'
            )
            ->get()
            ->map(function ($record) {

                if (
                    !$record->session ||
                    !$record->session->attendance_date
                ) {
                    return null;
                }

                return \Carbon\Carbon::parse(
                    $record->session->attendance_date
                )->format('Y-m');
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | FILTER: STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {

            $baseQuery->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER: MONTH
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('month') &&
            preg_match(
                '/^\d{4}-\d{2}$/',
                $request->month
            )
        ) {

            $month = $request->month;

            $baseQuery->whereHas(
                'session',
                function ($query) use ($month) {

                    $query->whereRaw(
                        "DATE_FORMAT(attendance_date, '%Y-%m') = ?",
                        [$month]
                    );

                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER: CLASS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('class_id')) {

            $classId = $request->class_id;

            $baseQuery->whereHas(
                'session',
                function ($query) use ($classId) {

                    $query->where(
                        'student_class_id',
                        $classId
                    );

                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER: FROM DATE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {

            $baseQuery->whereHas(
                'session',
                function ($query) use ($request) {

                    $query->whereDate(
                        'attendance_date',
                        '>=',
                        $request->date_from
                    );

                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER: TO DATE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_to')) {

            $baseQuery->whereHas(
                'session',
                function ($query) use ($request) {

                    $query->whereDate(
                        'attendance_date',
                        '<=',
                        $request->date_to
                    );

                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance records
        |--------------------------------------------------------------------------
        */

        $attendance = $baseQuery
            ->get()
            ->sortByDesc(function ($record) {

                return optional(
                    $record->session
                )->attendance_date;

            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall summary
        |--------------------------------------------------------------------------
        */

        $summaryQuery = Attendance::query()
            ->where(
                'student_id',
                $student->id
            )
            ->whereHas(
                'session',
                function ($query) {
                    $query->where(
                        'status',
                        'completed'
                    );
                }
            );

        $total = (clone $summaryQuery)->count();

        $present = (clone $summaryQuery)
            ->where(
                'status',
                'present'
            )
            ->count();

        $absent = (clone $summaryQuery)
            ->where(
                'status',
                'absent'
            )
            ->count();

        $late = (clone $summaryQuery)
            ->where(
                'status',
                'late'
            )
            ->count();

        $excused = (clone $summaryQuery)
            ->where(
                'status',
                'excused'
            )
            ->count();

        $attendancePercentage =
            ($present + $absent + $late) > 0
                ? round(
                    (
                        ($present + $late)
                        / ($present + $absent + $late)
                    ) * 100,
                    1
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Filtered summary
        |--------------------------------------------------------------------------
        */

        $filteredTotal =
            $attendance->count();

        $filteredPresent =
            $attendance
                ->where('status', 'present')
                ->count();

        $filteredAbsent =
            $attendance
                ->where('status', 'absent')
                ->count();

        $filteredLate =
            $attendance
                ->where('status', 'late')
                ->count();

        $filteredExcused =
            $attendance
                ->where('status', 'excused')
                ->count();

        return view(
            'students.attendance',
            compact(
                'student',
                'attendance',
                'classes',
                'months',

                'total',
                'present',
                'absent',
                'late',
                'excused',
                'attendancePercentage',

                'filteredTotal',
                'filteredPresent',
                'filteredAbsent',
                'filteredLate',
                'filteredExcused'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        $student = $this->student();

        $settings = $this->systemSettings();

        $assignment = $this->currentAssignment(
            $student,
            $this->operationalAcademicYear($settings),
            $this->operationalTerm(
                $settings,
                $this->operationalAcademicYear($settings)
            )
        );

        return view(
            'students.profile',
            compact(
                'student',
                'assignment'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESULTS
    |--------------------------------------------------------------------------
    */

    public function results()
    {
        $student = $this->student();

        $results = StudentResult::where(
            'student_id',
            $student->id
        )
            ->latest()
            ->get();

        return view(
            'students.results',
            compact(
                'student',
                'results'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC HISTORY
    |--------------------------------------------------------------------------
    */

    public function academicHistory()
    {
        $student = $this->student();

        $history = StudentClassAssignment::with([
            'studentClass',
            'academicYear',
            'term',
        ])
            ->where(
                'student_id',
                $student->id
            )
            ->orderByDesc('created_at')
            ->get();

        return view(
            'students.academic-history',
            compact(
                'student',
                'history'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CLASS HISTORY
    |--------------------------------------------------------------------------
    */

    public function classHistory()
    {
        $student = $this->student();

        $classHistory =
            StudentClassAssignment::with([
                'studentClass',
                'academicYear',
                'term',
            ])
                ->where(
                    'student_id',
                    $student->id
                )
                ->orderByDesc('is_current')
                ->orderByDesc('created_at')
                ->get();

        $assignmentQuery =
            StudentClassAssignment::where(
                'student_id',
                $student->id
            );

        $totalClasses =
            (clone $assignmentQuery)->count();

        $currentClasses =
            (clone $assignmentQuery)
                ->where(
                    'is_current',
                    true
                )
                ->count();

        $completedClasses =
            (clone $assignmentQuery)
                ->where(function ($query) {

                    $query
                        ->where(
                            'is_current',
                            false
                        )
                        ->orWhereNull(
                            'is_current'
                        );

                })
                ->count();

        $totalYears =
            (clone $assignmentQuery)
                ->whereNotNull(
                    'academic_year_id'
                )
                ->distinct()
                ->count(
                    'academic_year_id'
                );

        $currentAssignment =
            $classHistory->first(
                function ($assignment) {
                    return (bool)
                        $assignment->is_current;
                }
            );

        $summary = [
            'total_classes' =>
                $totalClasses,

            'current_class' =>
                $currentClasses,

            'completed_classes' =>
                $completedClasses,

            'total_years' =>
                $totalYears,
        ];

        return view(
            'students.class-history',
            [
                'student' =>
                    $student,

                'classHistory' =>
                    $classHistory,

                'summary' =>
                    $summary,

                'currentAssignment' =>
                    $currentAssignment,

                'totalClasses' =>
                    $totalClasses,

                'currentClasses' =>
                    $currentClasses,

                'completedClasses' =>
                    $completedClasses,

                'totalYears' =>
                    $totalYears,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CLASS HISTORY API
    |--------------------------------------------------------------------------
    */

    public function getClassHistoryApi()
    {
        $student = $this->student();

        $history =
            StudentClassAssignment::with([
                'studentClass',
                'academicYear',
                'term',
            ])
                ->where(
                    'student_id',
                    $student->id
                )
                ->orderByDesc('is_current')
                ->orderByDesc('created_at')
                ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CLASS PERFORMANCE API
    |--------------------------------------------------------------------------
    */

    public function getClassPerformanceApi()
    {
        $student = $this->student();

        $results =
            StudentResult::where(
                'student_id',
                $student->id
            )
                ->latest()
                ->get();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TIMETABLE
    |--------------------------------------------------------------------------
    */

    public function timetable()
    {
        $student = $this->student();

        $settings = $this->systemSettings();

        $academicYear =
            $this->operationalAcademicYear(
                $settings
            );

        $term =
            $this->operationalTerm(
                $settings,
                $academicYear
            );

        $assignment =
            $this->currentAssignment(
                $student,
                $academicYear,
                $term
            );

        $currentClass =
            $assignment
                ? $assignment->studentClass
                : null;

        $classId =
            $currentClass
                ? $currentClass->id
                : null;

        $timetable = null;

        $availableTimetables = collect();

        /*
        |--------------------------------------------------------------------------
        | CURRENT TIMETABLE
        |--------------------------------------------------------------------------
        */

        if ($classId) {

            try {

                $query =
                    Timetable::where(
                        'student_class_id',
                        $classId
                    );

                if (
                    Schema::hasColumn(
                        'timetables',
                        'is_active'
                    )
                ) {

                    $query->where(
                        'is_active',
                        true
                    );
                }

                if (
                    $academicYear &&
                    Schema::hasColumn(
                        'timetables',
                        'academic_year_id'
                    )
                ) {

                    $query->where(
                        'academic_year_id',
                        $academicYear->id
                    );
                }

                if (
                    $term &&
                    Schema::hasColumn(
                        'timetables',
                        'term_id'
                    )
                ) {

                    $query->where(
                        'term_id',
                        $term->id
                    );
                }

                $timetable =
                    $query
                        ->latest()
                        ->first();

            } catch (\Throwable $e) {

                $timetable = null;
            }


            /*
            |--------------------------------------------------------------------------
            | Fallback PDF
            |--------------------------------------------------------------------------
            */

            if (!$timetable) {

                try {

                    $query =
                        TimetablePdf::where(
                            'class_id',
                            $classId
                        );

                    if (
                        Schema::hasColumn(
                            'timetable_pdfs',
                            'is_active'
                        )
                    ) {

                        $query->where(
                            'is_active',
                            true
                        );
                    }

                    if (
                        $academicYear &&
                        Schema::hasColumn(
                            'timetable_pdfs',
                            'academic_year_id'
                        )
                    ) {

                        $query->where(
                            'academic_year_id',
                            $academicYear->id
                        );
                    }

                    if (
                        $term &&
                        Schema::hasColumn(
                            'timetable_pdfs',
                            'term_id'
                        )
                    ) {

                        $query->where(
                            'term_id',
                            $term->id
                        );
                    }

                    $timetable =
                        $query
                            ->latest()
                            ->first();

                } catch (\Throwable $e) {

                    $timetable = null;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | AVAILABLE TIMETABLES
            |--------------------------------------------------------------------------
            */

            try {

                $query =
                    Timetable::where(
                        'student_class_id',
                        $classId
                    );

                if (
                    Schema::hasColumn(
                        'timetables',
                        'is_active'
                    )
                ) {

                    $query->where(
                        'is_active',
                        true
                    );
                }

                if (
                    $academicYear &&
                    Schema::hasColumn(
                        'timetables',
                        'academic_year_id'
                    )
                ) {

                    $query->where(
                        'academic_year_id',
                        $academicYear->id
                    );
                }

                if (
                    $term &&
                    Schema::hasColumn(
                        'timetables',
                        'term_id'
                    )
                ) {

                    $query->where(
                        'term_id',
                        $term->id
                    );
                }

                $availableTimetables =
                    $query
                        ->latest()
                        ->get();

            } catch (\Throwable $e) {

                $availableTimetables =
                    collect();
            }
        }

        return view(
            'students.timetable',
            compact(
                'student',
                'assignment',
                'currentClass',
                'classId',
                'academicYear',
                'term',
                'timetable',
                'availableTimetables'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VIEW TIMETABLE
    |--------------------------------------------------------------------------
    */

    public function viewTimetable($id)
    {
        $student = $this->student();

        $currentClassId =
            $this->getStudentCurrentClassId(
                $student
            );

        if (!$currentClassId) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You are not currently assigned to a class.',
            ], 403);
        }

        $timetable =
            Timetable::with([
                'studentClass',
                'academicYear',
            ])->findOrFail($id);

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You do not have permission to view this timetable.',
            ], 403);
        }

        $url = null;

        if ($timetable->file_path) {

            $url =
                Storage::disk('public')
                    ->url(
                        $timetable->file_path
                    );
        }

        return response()->json([
            'success' => true,
            'timetable' => $timetable,
            'url' => $url,
            'file_name' => $timetable->file_name,
            'file_type' => $timetable->file_type,
            'file_size' => $timetable->file_size,
            'status' => $timetable->status,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD TIMETABLE
    |--------------------------------------------------------------------------
    */

    public function downloadTimetable($id)
    {
        $student = $this->student();

        $currentClassId =
            $this->getStudentCurrentClassId(
                $student
            );

        if (!$currentClassId) {
            abort(
                403,
                'You are not currently assigned to a class.'
            );
        }

        $timetable =
            Timetable::findOrFail($id);

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {

            abort(
                403,
                'You do not have permission to download this timetable.'
            );
        }

        if (!$timetable->file_path) {
            abort(
                404,
                'Timetable file path is missing.'
            );
        }

        $disk =
            Storage::disk('public');

        if (
            !$disk->exists(
                $timetable->file_path
            )
        ) {

            abort(
                404,
                'Timetable file not found.'
            );
        }

        return $disk->download(
            $timetable->file_path,
            $timetable->file_name
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STREAM TIMETABLE
    |--------------------------------------------------------------------------
    */

    public function streamTimetable($id)
    {
        $student = $this->student();

        $currentClassId =
            $this->getStudentCurrentClassId(
                $student
            );

        if (!$currentClassId) {
            abort(
                403,
                'You are not currently assigned to a class.'
            );
        }

        $timetable =
            Timetable::findOrFail($id);

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {

            abort(
                403,
                'You do not have permission to view this timetable.'
            );
        }

        if (!$timetable->file_path) {
            abort(
                404,
                'Timetable file path is missing.'
            );
        }

        $disk =
            Storage::disk('public');

        if (
            !$disk->exists(
                $timetable->file_path
            )
        ) {

            abort(
                404,
                'Timetable file not found.'
            );
        }

        $path =
            $disk->path(
                $timetable->file_path
            );

        $extension =
            strtolower(
                $timetable->file_type
                ?: pathinfo(
                    $timetable->file_path,
                    PATHINFO_EXTENSION
                )
            );

        $mimeType = match ($extension) {

            'pdf' =>
                'application/pdf',

            'jpg',
            'jpeg' =>
                'image/jpeg',

            'png' =>
                'image/png',

            default =>
                'application/octet-stream',
        };

        return response()->file(
            $path,
            [
                'Content-Type' =>
                    $mimeType,

                'Content-Disposition' =>
                    'inline; filename="' .
                    (
                        $timetable->file_name
                        ?? 'timetable'
                    ) .
                    '"',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TIMETABLE INFORMATION
    |--------------------------------------------------------------------------
    */

    public function getTimetableInfo($id)
    {
        try {

            $student = $this->student();

            $currentClassId =
                $this->getStudentCurrentClassId(
                    $student
                );

            if (!$currentClassId) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'No current class assignment.',
                ], 403);
            }

            $timetable =
                Timetable::with([
                    'studentClass',
                    'academicYear',
                ])->findOrFail($id);

            if (
                (int) $timetable->student_class_id !==
                (int) $currentClassId
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Unauthorized timetable.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'timetable' => $timetable,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to load timetable information.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SWITCH TIMETABLE
    |--------------------------------------------------------------------------
    */

    public function switchTimetable(Request $request)
    {
        $student = $this->student();

        $currentClassId =
            $this->getStudentCurrentClassId(
                $student
            );

        if (!$currentClassId) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You are not currently assigned to a class.',
            ], 403);
        }

        $request->validate([
            'timetable_id' => [
                'required',
                'integer',
            ],
        ]);

        $timetable =
            Timetable::findOrFail(
                $request->timetable_id
            );

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You cannot access a timetable for another class.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Timetable selected successfully.',
            'timetable' => $timetable,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    public function settings()
    {
        $student = $this->student();

        $settings =
            $this->systemSettings();

        $academicYear =
            $this->operationalAcademicYear(
                $settings
            );

        $term =
            $this->operationalTerm(
                $settings,
                $academicYear
            );

        return view(
            'students.settings',
            compact(
                'student',
                'academicYear',
                'term'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT STUDENT
    |--------------------------------------------------------------------------
    */

    private function student()
    {
        $student =
            Auth::guard('student')->user();

        abort_unless(
            $student,
            401,
            'Student authentication is required.'
        );

        return $student;
    }


    /*
    |--------------------------------------------------------------------------
    | SYSTEM SETTINGS
    |--------------------------------------------------------------------------
    |
    | SINGLE SOURCE OF TRUTH
    |
    | All student-facing academic-period information comes from the same
    | SystemSetting record used by the administration.
    |--------------------------------------------------------------------------
    */

    private function systemSettings()
    {
        return SystemSetting::instance()->load([
            'currentAcademicYear',
            'currentTerm',
            'operationalAcademicYear',
            'operationalTerm',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | OPERATIONAL ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    private function operationalAcademicYear(
        $settings
    ) {
        return $settings->operationalAcademicYear;
    }


    /*
    |--------------------------------------------------------------------------
    | OPERATIONAL TERM
    |--------------------------------------------------------------------------
    */

    private function operationalTerm(
        $settings,
        $academicYear = null
    ) {
        $term =
            $settings->operationalTerm;

        /*
        |--------------------------------------------------------------------------
        | Safety validation
        |--------------------------------------------------------------------------
        |
        | Never allow a term belonging to another academic year to appear
        | on the student portal.
        |--------------------------------------------------------------------------
        */

        if (
            $term &&
            $academicYear &&
            (int) $term->academic_year_id !==
            (int) $academicYear->id
        ) {
            return null;
        }

        return $term;
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | The student's current assignment must match the administrator's
    | operational academic year and term.
    |--------------------------------------------------------------------------
    */

    private function currentAssignment(
        $student,
        $academicYear = null,
        $term = null
    ) {

        $query =
            StudentClassAssignment::with([
                'studentClass',
                'academicYear',
                'term',
            ])
                ->where(
                    'student_id',
                    $student->id
                )
                ->where(
                    'is_current',
                    true
                )
                ->where(
                    'status',
                    'active'
                );

        if ($academicYear) {

            $query->where(
                'academic_year_id',
                $academicYear->id
            );
        }

        if ($term) {

            $query->where(
                'term_id',
                $term->id
            );
        }

        return $query
            ->latest('id')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT CLASS ID
    |--------------------------------------------------------------------------
    */

    private function getStudentCurrentClassId(
        $student
    ) {

        $settings =
            $this->systemSettings();

        $academicYear =
            $this->operationalAcademicYear(
                $settings
            );

        $term =
            $this->operationalTerm(
                $settings,
                $academicYear
            );

        $assignment =
            $this->currentAssignment(
                $student,
                $academicYear,
                $term
            );

        if ($assignment) {

            return $assignment->student_class_id;
        }

        /*
        |--------------------------------------------------------------------------
        | Legacy fallback
        |--------------------------------------------------------------------------
        |
        | Only use the direct student class column when the assignment-based
        | current class cannot be found.
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'students',
                'student_class_id'
            )
        ) {

            return $student->student_class_id
                ?? null;
        }

        return $student->class_id
            ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | FEE BALANCE
    |--------------------------------------------------------------------------
    |
    | Kept assignment-specific.
    |
    | This helper does not modify the fee system.
    |--------------------------------------------------------------------------
    */

    private function calculateFeeBalance(
        $student,
        $assignment
    ) {

        if (!$assignment) {
            return 0;
        }

        try {

            $account =
                StudentFeeAccount::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'student_class_assignment_id',
                        $assignment->id
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->latest('id')
                    ->first();

            if (!$account) {
                return 0;
            }

            $totalFees =
                (float) (
                    $account->total_fees ?? 0
                );

            $paid =
                (float) FeePayment::query()
                    ->where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'student_class_assignment_id',
                        $assignment->id
                    )
                    ->where(
                        'student_fee_account_id',
                        $account->id
                    )
                    ->where(
                        'status',
                        'completed'
                    )
                    ->sum('net_amount');

            return max(
                0,
                $totalFees - $paid
            );

        } catch (\Throwable $e) {

            return 0;
        }
    }
}