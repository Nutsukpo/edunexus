<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\StudentClassAssignment;
use App\Models\StudentInvoice;
use App\Models\StudentResult;
use App\Models\Term;
use App\Models\Timetable;
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
        $student = Auth::guard('student')->user();

        /*
        |--------------------------------------------------------------------------
        | Current Assignment
        |--------------------------------------------------------------------------
        */

        $assignment = $this->getCurrentAssignment($student);

        /*
        |--------------------------------------------------------------------------
        | Academic Year
        |--------------------------------------------------------------------------
        */

        $academicYear = $this->getCurrentAcademicYear();

        /*
        |--------------------------------------------------------------------------
        | Current Term
        |--------------------------------------------------------------------------
        */

        $term = $this->getCurrentTerm();

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        $attendanceQuery = AttendanceRecord::where(
            'student_id',
            $student->id
        );

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

        $totalAttendance = (clone $attendanceQuery)->count();

        $attendanceRate = $totalAttendance > 0
            ? round(($present / $totalAttendance) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Fees
        |--------------------------------------------------------------------------
        */

        $feeBalance = StudentInvoice::where(
            'student_id',
            $student->id
        )->sum('balance');

        /*
        |--------------------------------------------------------------------------
        | Subjects
        |--------------------------------------------------------------------------
        */

        $subjects = 0;

        if ($assignment && $assignment->studentClass) {
            $subjects = $assignment->studentClass
                ->subjects()
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Results
        |--------------------------------------------------------------------------
        */

        $recentResults = StudentResult::where(
            'student_id',
            $student->id
        )
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */

        $announcements = Announcement::published()
            ->where(function ($query) {
                $query->where('audience', 'all')
                    ->orWhere('audience', 'students');
            })
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        $featuredAnnouncement = Announcement::published()
            ->featured()
            ->where(function ($query) {
                $query->where('audience', 'all')
                    ->orWhere('audience', 'students');
            })
            ->first();

        return view('students.dashboard', compact(
            'student',
            'assignment',
            'academicYear',
            'term',
            'attendanceRate',
            'feeBalance',
            'subjects',
            'recentResults',
            'announcements',
            'featuredAnnouncement',
            'present',
            'absent',
            'late',
            'excused',
            'totalAttendance'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT TIMETABLE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | The timetable is determined ONLY from the student's CURRENT class.
    |
    | Student
    |     ↓
    | StudentClassAssignment
    |     ↓
    | is_current = true
    |     ↓
    | student_class_id
    |     ↓
    | Timetable.student_class_id
    |
    |--------------------------------------------------------------------------
    */

    public function timetable()
    {
        $student = Auth::guard('student')->user();

        /*
        |--------------------------------------------------------------------------
        | Get Current Assignment
        |--------------------------------------------------------------------------
        */

        $assignment = $this->getCurrentAssignment($student);

        /*
        |--------------------------------------------------------------------------
        | Student Has No Current Class
        |--------------------------------------------------------------------------
        */

        if (!$assignment || !$assignment->studentClass) {
            return view('students.timetable', [
                'student' => $student,
                'assignment' => null,
                'currentClass' => null,
                'classId' => null,
                'academicYear' => null,
                'timetable' => null,
                'availableTimetables' => collect(),
                'message' => 'You are not currently assigned to a class. Please contact the school administrator.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Current Class
        |--------------------------------------------------------------------------
        */

        $currentClass = $assignment->studentClass;

        $classId = (int) $assignment->student_class_id;

        /*
        |--------------------------------------------------------------------------
        | Academic Year
        |--------------------------------------------------------------------------
        |
        | Prefer the academic year attached to the student's current
        | assignment.
        |
        |--------------------------------------------------------------------------
        */

        $academicYear = $assignment->academicYear;

        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        /*
        |--------------------------------------------------------------------------
        | Find Current Class Timetable
        |--------------------------------------------------------------------------
        |
        | Priority:
        |
        | 1. Current class + current academic year + active
        | 2. Current class + active
        |
        |--------------------------------------------------------------------------
        */

        $timetable = null;

        $baseQuery = Timetable::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_class_id', $classId)
            ->where('status', 'active');

        /*
        |--------------------------------------------------------------------------
        | Priority 1:
        | Current Class + Assignment Academic Year
        |--------------------------------------------------------------------------
        */

        if ($academicYear) {
            $timetable = (clone $baseQuery)
                ->where(
                    'academic_year_id',
                    $academicYear->id
                )
                ->latest('created_at')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Priority 2:
        | Any Active Timetable For Current Class
        |--------------------------------------------------------------------------
        */

        if (!$timetable) {
            $timetable = (clone $baseQuery)
                ->latest('created_at')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Available Timetable Versions
        |--------------------------------------------------------------------------
        |
        | These belong ONLY to the student's current class.
        |
        |--------------------------------------------------------------------------
        */

        $availableTimetables = Timetable::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_class_id', $classId)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('students.timetable', [
            'student' => $student,
            'assignment' => $assignment,
            'currentClass' => $currentClass,
            'classId' => $classId,
            'academicYear' => $academicYear,
            'timetable' => $timetable,
            'availableTimetables' => $availableTimetables,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | VIEW TIMETABLE
    |--------------------------------------------------------------------------
    */

    public function viewTimetable($id)
    {
        $student = Auth::guard('student')->user();

        $currentClassId = $this->getStudentCurrentClassId($student);

        if (!$currentClassId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently assigned to a class.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Timetable
        |--------------------------------------------------------------------------
        */

        $timetable = Timetable::with([
            'studentClass',
            'academicYear',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        |
        | A student can ONLY view a timetable belonging to their
        | current class.
        |
        |--------------------------------------------------------------------------
        */

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this timetable.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | File URL
        |--------------------------------------------------------------------------
        */

        $url = null;

        if ($timetable->file_path) {
            $url = Storage::disk('public')
                ->url($timetable->file_path);
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

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
        $student = Auth::guard('student')->user();

        $currentClassId = $this->getStudentCurrentClassId($student);

        if (!$currentClassId) {
            abort(
                403,
                'You are not currently assigned to a class.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Timetable
        |--------------------------------------------------------------------------
        */

        $timetable = Timetable::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {
            abort(
                403,
                'You do not have permission to download this timetable.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | File
        |--------------------------------------------------------------------------
        */

        if (!$timetable->file_path) {
            abort(
                404,
                'Timetable file path is missing.'
            );
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($timetable->file_path)) {
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
        $student = Auth::guard('student')->user();

        $currentClassId = $this->getStudentCurrentClassId($student);

        if (!$currentClassId) {
            abort(
                403,
                'You are not currently assigned to a class.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Timetable
        |--------------------------------------------------------------------------
        */

        $timetable = Timetable::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {
            abort(
                403,
                'You do not have permission to view this timetable.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | File
        |--------------------------------------------------------------------------
        */

        if (!$timetable->file_path) {
            abort(
                404,
                'Timetable file path is missing.'
            );
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($timetable->file_path)) {
            abort(
                404,
                'Timetable file not found.'
            );
        }

        $path = $disk->path(
            $timetable->file_path
        );

        /*
        |--------------------------------------------------------------------------
        | MIME Type
        |--------------------------------------------------------------------------
        */

        $extension = strtolower(
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

            'xls' =>
                'application/vnd.ms-excel',

            'xlsx' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            default =>
                mime_content_type($path)
                    ?: 'application/octet-stream',
        };

        /*
        |--------------------------------------------------------------------------
        | Stream
        |--------------------------------------------------------------------------
        */

        return response()->file(
            $path,
            [
                'Content-Type' => $mimeType,

                'Content-Disposition' =>
                    'inline; filename="' .
                    addslashes(
                        $timetable->file_name
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
        $student = Auth::guard('student')->user();

        $currentClassId = $this->getStudentCurrentClassId($student);

        if (!$currentClassId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently assigned to a class.',
            ], 403);
        }

        $timetable = Timetable::with([
            'studentClass',
            'academicYear',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized timetable.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | File URL
        |--------------------------------------------------------------------------
        */

        $url = null;

        if ($timetable->file_path) {
            $url = Storage::disk('public')
                ->url($timetable->file_path);
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
    | SWITCH TIMETABLE
    |--------------------------------------------------------------------------
    |
    | This does not change the student's class.
    |
    | It only verifies that the selected timetable belongs to the
    | student's current class.
    |
    |--------------------------------------------------------------------------
    */

    public function switchTimetable(Request $request)
    {
        $student = Auth::guard('student')->user();

        $currentClassId = $this->getStudentCurrentClassId($student);

        if (!$currentClassId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently assigned to a class.',
            ], 403);
        }

        $validated = $request->validate([
            'timetable_id' => [
                'required',
                'integer',
                'exists:timetables,id',
            ],
        ]);

        $timetable = Timetable::with([
            'studentClass',
            'academicYear',
        ])->findOrFail(
            $validated['timetable_id']
        );

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        if (
            (int) $timetable->student_class_id !==
            (int) $currentClassId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot access a timetable for another class.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Only Active Timetables
        |--------------------------------------------------------------------------
        */

        if ($timetable->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This timetable is no longer active.',
            ], 404);
        }

        $url = Storage::disk('public')
            ->url($timetable->file_path);

        return response()->json([
            'success' => true,

            'message' => 'Timetable selected successfully.',

            'timetable' => $timetable,

            'url' => $url,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CLASS HISTORY
    |--------------------------------------------------------------------------
    */

    public function classHistory()
    {
        $student = Auth::guard('student')->user();

        /*
        |--------------------------------------------------------------------------
        | All Class Assignments
        |--------------------------------------------------------------------------
        */

        $classHistory = StudentClassAssignment::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_id', $student->id)
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->paginate(15);

        /*
        |--------------------------------------------------------------------------
        | Summary Query
        |--------------------------------------------------------------------------
        */

        $assignmentQuery = StudentClassAssignment::where(
            'student_id',
            $student->id
        );

        $totalClasses = (clone $assignmentQuery)
            ->count();

        $currentClasses = (clone $assignmentQuery)
            ->where('is_current', true)
            ->count();

        $completedClasses = (clone $assignmentQuery)
            ->where(function ($query) {
                $query->where('is_current', false)
                    ->orWhereNull('is_current');
            })
            ->count();

        $totalYears = (clone $assignmentQuery)
            ->whereNotNull('academic_year_id')
            ->distinct()
            ->count('academic_year_id');

        /*
        |--------------------------------------------------------------------------
        | Current Assignment
        |--------------------------------------------------------------------------
        */

        $currentAssignment = $this->getCurrentAssignment(
            $student
        );

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_classes' => $totalClasses,

            'current_class' => $currentClasses,

            'completed_classes' => $completedClasses,

            'total_years' => $totalYears,
        ];

        return view(
            'students.class-history',
            compact(
                'student',
                'classHistory',
                'summary',
                'currentAssignment'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CLASS HISTORY API
    |--------------------------------------------------------------------------
    */

    public function getClassHistoryApi()
    {
        $student = Auth::guard('student')->user();

        $history = StudentClassAssignment::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_id', $student->id)
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
        $student = Auth::guard('student')->user();

        $results = StudentResult::where(
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
    | PROFILE
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        $student = Auth::guard('student')->user();

        $assignment = $this->getCurrentAssignment($student);

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
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function attendance()
    {
        $student = Auth::guard('student')->user();

        $attendance = AttendanceRecord::where(
            'student_id',
            $student->id
        )
            ->latest()
            ->paginate(20);

        return view(
            'students.attendance',
            compact(
                'student',
                'attendance'
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
        $student = Auth::guard('student')->user();

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
        $student = Auth::guard('student')->user();

        $results = StudentResult::where(
            'student_id',
            $student->id
        )
            ->latest()
            ->get();

        return view(
            'students.academic-history',
            compact(
                'student',
                'results'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FEES
    |--------------------------------------------------------------------------
    */

    public function fees()
    {
        $student = Auth::guard('student')->user();

        $invoices = StudentInvoice::where(
            'student_id',
            $student->id
        )
            ->latest()
            ->paginate(15);

        $balance = StudentInvoice::where(
            'student_id',
            $student->id
        )->sum('balance');

        return view(
            'students.fees',
            compact(
                'student',
                'invoices',
                'balance'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    public function settings()
    {
        $student = Auth::guard('student')->user();

        return view(
            'students.settings',
            compact('student')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: CURRENT ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | This is the single source of truth for the student's current class.
    |
    |--------------------------------------------------------------------------
    */

    private function getCurrentAssignment($student)
    {
        return StudentClassAssignment::with([
            'studentClass',
            'academicYear',
        ])
            ->where(
                'student_id',
                $student->id
            )
            ->where(
                'is_current',
                true
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: CURRENT CLASS ID
    |--------------------------------------------------------------------------
    */

    private function getStudentCurrentClassId($student): ?int
    {
        $assignment = $this->getCurrentAssignment($student);

        if (!$assignment) {
            return null;
        }

        return $assignment->student_class_id
            ? (int) $assignment->student_class_id
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: CURRENT ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    private function getCurrentAcademicYear()
    {
        $query = AcademicYear::query();

        /*
        |--------------------------------------------------------------------------
        | Prefer Active Academic Year
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'academic_years',
                'is_active'
            )
        ) {
            $active = (clone $query)
                ->where('is_active', true)
                ->latest('created_at')
                ->first();

            if ($active) {
                return $active;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        return $query
            ->latest('created_at')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: CURRENT TERM
    |--------------------------------------------------------------------------
    */

    private function getCurrentTerm()
    {
        $query = Term::query();

        /*
        |--------------------------------------------------------------------------
        | Prefer Current Term
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'terms',
                'is_current'
            )
        ) {
            $current = (clone $query)
                ->where('is_current', true)
                ->latest('created_at')
                ->first();

            if ($current) {
                return $current;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback: Active Term
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'terms',
                'is_active'
            )
        ) {
            $active = (clone $query)
                ->where('is_active', true)
                ->latest('created_at')
                ->first();

            if ($active) {
                return $active;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Final Fallback
        |--------------------------------------------------------------------------
        */

        return $query
            ->latest('created_at')
            ->first();
    }
}