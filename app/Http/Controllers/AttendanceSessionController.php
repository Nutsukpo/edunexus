<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DISPLAY ALL ATTENDANCE SESSIONS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $sessions = AttendanceSession::with([
            'studentClass',
            'takenBy'
        ])
        ->latest()
        ->paginate(20);

        return view(
            'attendance.index',
            compact('sessions')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $classes = StudentClass::orderBy('name')
            ->get();

        return view(
            'attendance.create',
            compact('classes')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'student_class_id' => 'required|exists:student_classes,id',
            'attendance_date'  => 'required|date',
            'attendance'       => 'required|array',
        ]);

        /*
        |--------------------------------------------------------------------------
        | PREVENT DUPLICATE ATTENDANCE
        |--------------------------------------------------------------------------
        */
        $exists = AttendanceSession::where(
            'student_class_id',
            $request->student_class_id
        )
        ->where(
            'attendance_date',
            $request->attendance_date
        )
        ->exists();

        if ($exists) {

            return back()->with(
                'error',
                'Attendance already taken for this class and date.'
            );
        }

        DB::transaction(function () use ($request) {

            /*
            |--------------------------------------------------------------------------
            | CREATE SESSION
            |--------------------------------------------------------------------------
            */
            $session = AttendanceSession::create([
                'student_class_id' => $request->student_class_id,
                'attendance_date'  => $request->attendance_date,
                'taken_by'         => auth()->id(),
                'status'           => 'completed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | SAVE STUDENT ATTENDANCE
            |--------------------------------------------------------------------------
            */
            foreach ($request->attendance as $assignmentId => $status) {

                $assignment = StudentClassAssignment::with('student')
                    ->find($assignmentId);

                if (!$assignment) {
                    continue;
                }

                Attendance::create([

                    'attendance_session_id'
                        => $session->id,

                    'student_class_assignment_id'
                        => $assignment->id,

                    'student_id'
                        => $assignment->student_id,

                    'status'
                        => $status,
                ]);
            }
        });

        return redirect()
            ->route('student-classes.show',
            $request->student_class_id)
            ->with(
                'success',
                'Attendance submitted successfully.'
            );
    }
        


    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE SESSION
    |--------------------------------------------------------------------------
    */
    public function show(AttendanceSession $attendanceSession)
    {
        $attendanceSession->load([
            'studentClass',
            'attendances.student',
            'takenBy'
        ]);

        return view(
            'attendance.show',
            compact('attendanceSession')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX LOAD CLASS STUDENTS
    |--------------------------------------------------------------------------
    */
    public function getStudents($classId)
    {
        $students = StudentClassAssignment::with('student')
            ->where('student_class_id', $classId)
            ->where('is_current', true)
            ->orderBy('id')
            ->get();

        return response()->json($students);
    }


    public function createForClass($studentClassId)
    {
        $studentClass = StudentClass::findOrFail($studentClassId);
        
        // Get active assignments (students currently enrolled in this class)
        $assignments = StudentClassAssignment::with('student')
            ->where('student_class_id', $studentClassId)
            ->where('is_current', true)
            ->get();
        
        return view('attendance.take', compact('studentClass', 'assignments'));
    }

        /**
 * Check if attendance already exists for a class and date
 */
    public function checkExists(Request $request)
    {
        $classId = $request->get('class_id');
        $date = $request->get('date');
        
        $session = AttendanceSession::where('student_class_id', $classId)
            ->where('attendance_date', $date)
            ->first();
        
        if ($session) {
            // Load existing attendance data
            $attendances = Attendance::where('attendance_session_id', $session->id)
                ->get()
                ->pluck('status', 'student_class_assignment_id');
            
            return response()->json([
                'exists' => true,
                'session_id' => $session->id,
                'attendance' => $attendances
            ]);
        }
        
        return response()->json([
            'exists' => false
        ]);
    }


    
    
}