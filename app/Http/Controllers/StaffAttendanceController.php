<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $today = now()->toDateString();

        return view('staffattendance.dashboard', [
            'totalStaff' => Staff::count(),
            'presentToday' => StaffAttendance::whereDate('date', $today)->where('status', 'present')->count(),
            'lateToday' => StaffAttendance::whereDate('date', $today)->where('status', 'late')->count(),
            'absentToday' => StaffAttendance::whereDate('date', $today)->where('status', 'absent')->count(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX (LIST ALL ATTENDANCE RECORDS)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $attendances = StaffAttendance::with('staff')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('staffattendance.index', compact('attendances'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $staff = Staff::orderBy('first_name')->orderBy('last_name')->get();
        return view('staffattendance.create', compact('staff'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE MANUAL ATTENDANCE (ADMIN OVERRIDE - No GPS Required)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
        ]);

        // Check if attendance already exists for this staff on this date
        $existing = StaffAttendance::where('staff_id', $request->staff_id)
            ->where('date', $request->date)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Attendance already recorded for this staff on ' . $request->date)
                ->withInput();
        }

        $attendance = StaffAttendance::create([
            'staff_id' => $request->staff_id,
            'date' => $request->date,
            'status' => $request->status,
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
        ]);

        return redirect()->route('staffattendance.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | CLOCK IN (GPS BASED) - FULLY OBEYS ATTENDANCE SETTINGS
    |--------------------------------------------------------------------------
    */
    public function clockIn(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // GET ATTENDANCE SETTINGS
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return redirect()->back()
                ->with('error', 'Attendance settings not configured. Please contact administrator.');
        }

        $now = Carbon::now();
        $currentDate = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        // FIX: Check if the requested date matches today's date
        if ($request->date != $currentDate) {
            return redirect()->back()
                ->with('error', 'Clock-in can only be done for today\'s date.');
        }

        // CHECK IF GPS IS ENABLED IN SETTINGS
        if (!$setting->gps_enabled) {
            return redirect()->back()
                ->with('error', 'GPS attendance is currently disabled. Manual attendance entry is available.');
        }

        // GPS VALIDATION - Calculate distance from allowed location
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->radius) {
            return redirect()->back()
                ->with('error', 'You are outside the allowed attendance radius. Current distance: ' . round($distance, 2) . ' meters. Maximum allowed: ' . $setting->radius . ' meters.');
        }

        // CHECK CLOCK-IN TIME WINDOW
        $isLate = false;
        $canClockIn = true;
        $timeMessage = '';

        // Check if clock-in start time is set and current time is before it
        if ($setting->clock_in_start) {
            $startTime = Carbon::parse($setting->clock_in_start);
            $start = $startTime->format('H:i:s');
            
            if ($currentTime < $start) {
                $canClockIn = false;
                $timeMessage = 'Clock-in not open yet. Starts at ' . $startTime->format('h:i A') . '. Current time: ' . $now->format('h:i A');
            }
        }

        // Check if clock-in end time is set (late cutoff)
        if ($canClockIn && $setting->clock_in_end) {
            $endTime = Carbon::parse($setting->clock_in_end);
            $end = $endTime->format('H:i:s');
            
            if ($currentTime > $end) {
                $isLate = true;
                $timeMessage = 'Late clock-in. You clocked in after ' . $endTime->format('h:i A');
            }
        }

        // Return error if clock-in is outside allowed window
        if (!$canClockIn) {
            return redirect()->back()->with('error', $timeMessage);
        }

        // CHECK IF ALREADY CLOCKED IN TODAY
        $attendance = StaffAttendance::where('staff_id', $request->staff_id)
            ->where('date', $request->date)
            ->first();

        if ($attendance && $attendance->clock_in_time) {
            return redirect()->back()
                ->with('info', 'Staff already clocked in today at ' . Carbon::parse($attendance->clock_in_time)->format('h:i A'));
        }

        // DETERMINE STATUS BASED ON LATE CHECK
        $status = $isLate ? 'late' : 'present';

        // SAVE CLOCK IN
        $attendance = StaffAttendance::updateOrCreate(
            [
                'staff_id' => $request->staff_id,
                'date' => $request->date,
            ],
            [
                'clock_in_time' => $currentTime,
                'clock_in_latitude' => $request->latitude,
                'clock_in_longitude' => $request->longitude,
                'status' => $status,
            ]
        );

        $message = '✓ Clock-in successful at ' . $now->format('h:i A') . '. ';
        $message .= $isLate ? 'Status: LATE' : 'Status: PRESENT (on time)';
        
        if ($timeMessage && $isLate) {
            $message .= ' (' . $timeMessage . ')';
        }

        return redirect()->route('staff-attendance.index')
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | CLOCK OUT (GPS BASED) - FULLY OBEYS ATTENDANCE SETTINGS
    |--------------------------------------------------------------------------
    */
    public function clockOut(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // GET ATTENDANCE SETTINGS
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return redirect()->back()
                ->with('error', 'Attendance settings not configured. Please contact administrator.');
        }

        $now = Carbon::now();
        $currentDate = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        // FIX: Check if the requested date matches today's date
        if ($request->date != $currentDate) {
            return redirect()->back()
                ->with('error', 'Clock-out can only be done for today\'s date.');
        }

        // CHECK IF GPS IS ENABLED IN SETTINGS
        if (!$setting->gps_enabled) {
            return redirect()->back()
                ->with('error', 'GPS attendance is currently disabled. Manual attendance entry is available.');
        }

        // GPS VALIDATION
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->radius) {
            return redirect()->back()
                ->with('error', 'You are outside the allowed attendance radius. Current distance: ' . round($distance, 2) . ' meters. Maximum allowed: ' . $setting->radius . ' meters.');
        }

        // FIND CLOCK-IN RECORD
        $attendance = StaffAttendance::where('staff_id', $request->staff_id)
            ->where('date', $request->date)
            ->first();

        if (!$attendance) {
            return redirect()->back()
                ->with('error', 'No clock-in record found for today. Please clock in first.');
        }

        // CHECK IF ALREADY CLOCKED OUT
        if ($attendance->clock_out_time) {
            return redirect()->back()
                ->with('info', 'Already clocked out today at ' . Carbon::parse($attendance->clock_out_time)->format('h:i A'));
        }

        // CHECK CLOCK-OUT TIME WINDOW
        $canClockOut = true;
        $timeMessage = '';

        // Check if clock-out start time is set (earliest time they can clock out)
        if ($setting->clock_out_start) {
            $startTime = Carbon::parse($setting->clock_out_start);
            $start = $startTime->format('H:i:s');
            
            if ($currentTime < $start) {
                $canClockOut = false;
                $timeMessage = 'Clock-out not allowed yet. You can clock out after ' . $startTime->format('h:i A') . '. Current time: ' . $now->format('h:i A');
            }
        }

        // Check if clock-out end time is set (deadline for clock out)
        if ($canClockOut && $setting->clock_out_end) {
            $endTime = Carbon::parse($setting->clock_out_end);
            $end = $endTime->format('H:i:s');
            
            if ($currentTime > $end) {
                $canClockOut = false;
                $timeMessage = 'Clock-out time has passed. Deadline was ' . $endTime->format('h:i A') . '. Current time: ' . $now->format('h:i A');
            }
        }

        // Return error if clock-out is outside allowed window
        if (!$canClockOut) {
            return redirect()->back()->with('error', $timeMessage);
        }

        // SAVE CLOCK OUT
        $attendance->update([
            'clock_out_time' => $currentTime,
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
        ]);

        return redirect()->route('staffattendance.index')
            ->with('success', '✓ Clock-out successful at ' . $now->format('h:i A'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE ATTENDANCE RECORD
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $attendance = StaffAttendance::with('staff')->findOrFail($id);
        return view('staffattendance.show', compact('attendance'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $attendance = StaffAttendance::with('staff')->findOrFail($id);
        return view('staffattendance.edit', compact('attendance'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ATTENDANCE RECORD
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late',
            'clock_in_time' => 'nullable',
            'clock_out_time' => 'nullable',
        ]);

        $attendance = StaffAttendance::findOrFail($id);

        $attendance->update([
            'status' => $request->status,
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
        ]);

        return redirect()->route('staffattendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ATTENDANCE RECORD
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $attendance = StaffAttendance::findOrFail($id);
        $attendance->delete();

        return redirect()->route('staffattendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF ATTENDANCE HISTORY
    |--------------------------------------------------------------------------
    */
    public function history($staffId)
    {
        $staff = Staff::findOrFail($staffId);
        $history = StaffAttendance::where('staff_id', $staffId)
            ->latest('date')
            ->paginate(30);

        return view('staffattendance.history', compact('staff', 'history'));
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE REPORT
    |--------------------------------------------------------------------------
    */
    public function report(Request $request)
    {
        $query = StaffAttendance::select(
                'staff_id',
                DB::raw('COUNT(*) as total_days'),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days")
            )
            ->groupBy('staff_id')
            ->with('staff');

        // Optional date filters
        if ($request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $report = $query->get();

        return view('staffattendance.report', compact('report'));
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE MAP VIEW
    |--------------------------------------------------------------------------
    */
    public function liveMap()
    {
        $setting = AttendanceSetting::first();
        
        $attendances = StaffAttendance::with('staff')
            ->whereDate('date', today())
            ->whereNotNull('clock_in_latitude')
            ->whereNotNull('clock_in_longitude')
            ->get();
        
        $locations = $attendances->map(function($attendance) {
            return [
                'id' => $attendance->id,
                'staff' => [
                    'id' => $attendance->staff->id ?? null,
                    'first_name' => $attendance->staff->first_name ?? 'Unknown',
                    'last_name' => $attendance->staff->last_name ?? '',
                    'full_name' => ($attendance->staff->first_name ?? '') . ' ' . ($attendance->staff->last_name ?? ''),
                ],
                'clock_in_time' => $attendance->clock_in_time,
                'clock_out_time' => $attendance->clock_out_time,
                'clock_in_latitude' => $attendance->clock_in_latitude,
                'clock_in_longitude' => $attendance->clock_in_longitude,
                'clock_out_latitude' => $attendance->clock_out_latitude,
                'clock_out_longitude' => $attendance->clock_out_longitude,
                'status' => $attendance->status,
                'is_late' => $attendance->status === 'late',
            ];
        });
        
        return view('staffattendance.live-map', compact('locations', 'attendances', 'setting'));
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE LOCATIONS API (AJAX)
    |--------------------------------------------------------------------------
    */
    public function liveLocations()
    {
        $locations = StaffAttendance::with('staff')
            ->whereDate('date', today())
            ->whereNotNull('clock_in_latitude')
            ->whereNotNull('clock_in_longitude')
            ->get()
            ->map(function ($attendance) {
                return [
                    'staff' => [
                        'id' => $attendance->staff->id ?? null,
                        'first_name' => $attendance->staff->first_name ?? '',
                        'last_name' => $attendance->staff->last_name ?? '',
                        'full_name' => ($attendance->staff->first_name ?? '') . ' ' . ($attendance->staff->last_name ?? ''),
                    ],
                    'clock_in' => $attendance->clock_in_time,
                    'clock_out' => $attendance->clock_out_time,
                    'clock_in_latitude' => $attendance->clock_in_latitude,
                    'clock_in_longitude' => $attendance->clock_in_longitude,
                    'clock_out_latitude' => $attendance->clock_out_latitude,
                    'clock_out_longitude' => $attendance->clock_out_longitude,
                    'is_late' => $attendance->status === 'late',
                    'status' => $attendance->status,
                    'remarks' => $attendance->remarks ?? 'No remarks',
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GPS CLOCK IN (ALTERNATIVE METHOD)
    |--------------------------------------------------------------------------
    */
    public function gpsClockIn(Request $request)
    {
        return $this->clockIn($request);
    }

    /*
    |--------------------------------------------------------------------------
    | GPS CLOCK OUT (ALTERNATIVE METHOD)
    |--------------------------------------------------------------------------
    */
    public function gpsClockOut(Request $request)
    {
        return $this->clockOut($request);
    }

    /*
    |--------------------------------------------------------------------------
    | GET CURRENT ATTENDANCE SETTINGS (API)
    |--------------------------------------------------------------------------
    */
    public function getSettings()
    {
        $setting = AttendanceSetting::first();
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance settings not configured'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'settings' => [
                'gps_enabled' => (bool) $setting->gps_enabled,
                'latitude' => $setting->latitude,
                'longitude' => $setting->longitude,
                'radius' => $setting->radius,
                'clock_in_start' => $setting->clock_in_start,
                'clock_in_end' => $setting->clock_in_end,
                'clock_out_start' => $setting->clock_out_start,
                'clock_out_end' => $setting->clock_out_end,
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE DISTANCE BETWEEN TWO GPS POINTS (METERS)
    |--------------------------------------------------------------------------
    */
    private function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(
            sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($latFrom) *
                cos($latTo) *
                pow(sin($lonDelta / 2), 2)
            )
        );

        return $angle * $earthRadius;
    }
}