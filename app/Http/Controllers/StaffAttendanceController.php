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

            'presentToday' => StaffAttendance::whereDate('date', $today)
                ->where('status', 'present')
                ->count(),

            'lateToday' => StaffAttendance::whereDate('date', $today)
                ->where('status', 'late')
                ->count(),

            'absentToday' => StaffAttendance::whereDate('date', $today)
                ->where('status', 'absent')
                ->count(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    // In StaffAttendanceController.php

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
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $staff = Staff::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('staffattendance.create', compact('staff'));
    }

    /*
    |--------------------------------------------------------------------------
    | CLOCK IN
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

        $setting = AttendanceSetting::first();

        if (!$setting) {
            return back()->with('error', 'Attendance settings not configured.');
        }

        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | GPS VALIDATION
        |--------------------------------------------------------------------------
        */

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->radius) {

            return back()->with(
                'error',
                'You are outside the allowed attendance radius. '
                . 'Current distance: '
                . round($distance, 2)
                . ' meters.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CLOCK IN START TIME
        |--------------------------------------------------------------------------
        */

        if ($setting->clock_in_start) {

            $start = Carbon::parse($setting->clock_in_start);

            if ($now->lt($start)) {

                return back()->with(
                    'warning',
                    'Clock-in not open yet. Starts at '
                    . $start->format('h:i A')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CLOCK IN END TIME
        |--------------------------------------------------------------------------
        */

        if ($setting->clock_in_end) {

            $end = Carbon::parse($setting->clock_in_end);

            if ($now->gt($end)) {

                return back()->with(
                    'error',
                    'Clock-in closed at '
                    . $end->format('h:i A')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */

        $attendance = StaffAttendance::firstOrCreate([
            'staff_id' => $request->staff_id,
            'date' => $request->date,
        ]);

        if ($attendance->clock_in_time) {

            return back()->with(
                'info',
                'Staff already clocked in today.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status = 'present';

        if ($setting->clock_in_end) {

            $end = Carbon::parse($setting->clock_in_end);

            if ($now->gt($end)) {
                $status = 'late';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE CLOCK IN
        |--------------------------------------------------------------------------
        */

        $attendance->update([
            'clock_in_time' => now()->format('H:i:s'),
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'status' => $status,
        ]);

        return redirect()
            ->route('staffattendance.index')
            ->with('success', 'Clock-in successful.');
    }

    /*
    |--------------------------------------------------------------------------
    | CLOCK OUT
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

        $setting = AttendanceSetting::first();

        if (!$setting) {
            return back()->with('error', 'Attendance settings not configured.');
        }

        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | GPS VALIDATION
        |--------------------------------------------------------------------------
        */

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->radius) {

            return back()->with(
                'error',
                'You are outside the allowed attendance radius. '
                . 'Current distance: '
                . round($distance, 2)
                . ' meters.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CLOCK OUT START
        |--------------------------------------------------------------------------
        */

        if ($setting->clock_out_start) {

            $start = Carbon::parse($setting->clock_out_start);

            if ($now->lt($start)) {

                return back()->with(
                    'warning',
                    'Clock-out allowed after '
                    . $start->format('h:i A')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FIND RECORD
        |--------------------------------------------------------------------------
        */

        $attendance = StaffAttendance::where('staff_id', $request->staff_id)
            ->where('date', $request->date)
            ->first();

        if (!$attendance) {

            return back()->with(
                'error',
                'No clock-in record found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CLOCK OUT
        |--------------------------------------------------------------------------
        */

        if ($attendance->clock_out_time) {

            return back()->with(
                'info',
                'Staff already clocked out today.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE CLOCK OUT
        |--------------------------------------------------------------------------
        */

        $attendance->update([
            'clock_out_time' => now()->format('H:i:s'),
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
        ]);

        return redirect()
            ->route('staffattendance.index')
            ->with('success', 'Clock-out successful.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $attendance = StaffAttendance::with('staff')
            ->findOrFail($id);

        return view('staffattendance.show', compact('attendance'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $attendance = StaffAttendance::with('staff')
            ->findOrFail($id);

        return view('staffattendance.edit', compact('attendance'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $attendance = StaffAttendance::findOrFail($id);

        $attendance->update([
            'clock_out_time' => now()->format('H:i:s'),
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('staffattendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | HISTORY
    |--------------------------------------------------------------------------
    */
    public function history($staffId)
    {
        $staff = Staff::findOrFail($staffId);

        $history = StaffAttendance::where('staff_id', $staffId)
            ->latest('date')
            ->get();

        return view('staffattendance.history', compact(
            'staff',
            'history'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT
    |--------------------------------------------------------------------------
    */
    public function report()
    {
        $report = StaffAttendance::select(
                'staff_id',
                DB::raw('COUNT(*) as total_days'),
                DB::raw("SUM(status = 'present') as present_days"),
                DB::raw("SUM(status = 'late') as late_days"),
                DB::raw("SUM(status = 'absent') as absent_days")
            )
            ->groupBy('staff_id')
            ->with('staff')
            ->get();

        return view('staffattendance.report', compact('report'));
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE MAP
    |--------------------------------------------------------------------------
    */
    // In StaffAttendanceController.php

public function liveMap()
{
    // Get today's attendance records with staff relationship
    $attendances = StaffAttendance::with('staff')
        ->whereDate('date', today())
        ->get();
    
    // Prepare the data for JavaScript
    $locations = $attendances->map(function($attendance) {
        return [
            'id' => $attendance->id,
            'staff' => [
                'id' => $attendance->staff->id ?? null,
                'first_name' => $attendance->staff->first_name ?? 'Unknown',
                'last_name' => $attendance->staff->last_name ?? '',
            ],
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'clock_in_latitude' => $attendance->clock_in_latitude,
            'clock_in_longitude' => $attendance->clock_in_longitude,
            'clock_out_latitude' => $attendance->clock_out_latitude,
            'clock_out_longitude' => $attendance->clock_out_longitude,
            'status' => $attendance->status,
        ];
    });
    
    return view('staffattendance.live-map', compact('locations', 'attendances'));
}

    /*
    |--------------------------------------------------------------------------
    | LIVE LOCATIONS API
    |--------------------------------------------------------------------------
    */
    public function liveLocations()
    {
        $locations = StaffAttendance::with('staff')
            ->whereDate('date', today())
            ->get()
            ->map(function ($a) {

                return [

                    'staff' => [
                        'id' => $a->staff->id ?? null,
                        'first_name' => $a->staff->first_name ?? '',
                        'last_name' => $a->staff->last_name ?? '',
                    ],

                    'clock_in' => $a->clock_in_time,
                    'clock_out' => $a->clock_out_time,

                    'clock_in_latitude' => $a->clock_in_latitude,
                    'clock_in_longitude' => $a->clock_in_longitude,

                    'is_late' => $a->status === 'late',
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE DISTANCE (METERS)
    |--------------------------------------------------------------------------
    */
    private function calculateDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo
    ) {

        $earthRadius = 6371000;

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