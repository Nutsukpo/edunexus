<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\SystemSetting;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StaffAttendanceController extends Controller
{
    public function dashboard(Request $request)
    {
        [$yearId, $termId] = $this->period($request);
        $query = StaffAttendance::query();
        $this->filterPeriod($query, $yearId, $termId);

        $today = now()->toDateString();
        $todayQuery = (clone $query)->whereDate('date', $today);

        return view('staffattendance.dashboard', [
            'totalStaff' => Staff::count(),
            'presentToday' => (clone $todayQuery)->where('status', 'present')->count(),
            'lateToday' => (clone $todayQuery)->where('status', 'late')->count(),
            'absentToday' => (clone $todayQuery)->where('status', 'absent')->count(),
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function index(Request $request)
    {
        [$yearId, $termId] = $this->period($request);

        $query = StaffAttendance::with(['staff', 'academicYear', 'term']);
        $this->filterPeriod($query, $yearId, $termId);

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->integer('staff_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->input('to_date'));
        }

        $attendances = $query
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('staffattendance.index', [
            'attendances' => $attendances,
            'staff' => Staff::orderBy('first_name')->orderBy('last_name')->get(),
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function create(Request $request)
    {
        [$yearId, $termId] = $this->period($request);

        return view('staffattendance.create', [
            'staff' => Staff::orderBy('first_name')->orderBy('last_name')->get(),
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function store(Request $request)
    {
        $this->validateAttendance($request, false);

        $this->ensureDateInTerm(
            $request->input('date'),
            (int) $request->input('academic_year_id'),
            (int) $request->input('term_id')
        );

        $exists = StaffAttendance::where('staff_id', $request->input('staff_id'))
            ->whereDate('date', $request->input('date'))
            ->exists();

        if ($exists) {
            return back()
                ->with('error', 'Attendance already exists for this staff member on this date.')
                ->withInput();
        }

        StaffAttendance::create([
            'staff_id' => $request->input('staff_id'),
            'academic_year_id' => $request->input('academic_year_id'),
            'term_id' => $request->input('term_id'),
            'date' => $request->input('date'),
            'status' => $request->input('status'),
            'clock_in_time' => $request->input('clock_in_time'),
            'clock_out_time' => $request->input('clock_out_time'),
            'remarks' => $request->input('remarks'),
        ]);

        return redirect()->route('staffattendance.index', [
            'academic_year_id' => $request->input('academic_year_id'),
            'term_id' => $request->input('term_id'),
        ])->with('success', 'Attendance recorded successfully.');
    }

    public function clockIn(Request $request)
    {
        $this->validateGps($request);
        $this->ensureDateInTerm(
            $request->input('date'),
            (int) $request->input('academic_year_id'),
            (int) $request->input('term_id')
        );

        $setting = AttendanceSetting::first();
        if (!$setting) {
            return back()->with('error', 'Attendance settings not configured.');
        }

        $now = Carbon::now();

        if ($request->input('date') !== $now->toDateString()) {
            return back()->with('error', 'Clock-in can only be performed for today.');
        }

        if (!$setting->gps_enabled) {
            return back()->with('error', 'GPS attendance is currently disabled.');
        }

        $this->ensureWithinGpsRadius($request, $setting);

        $currentTime = $now->format('H:i:s');
        $isLate = false;

        if ($setting->clock_in_start &&
            $currentTime < Carbon::parse($setting->clock_in_start)->format('H:i:s')) {
            return back()->with(
                'error',
                'Clock-in opens at ' . Carbon::parse($setting->clock_in_start)->format('h:i A') . '.'
            );
        }

        if ($setting->clock_in_end &&
            $currentTime > Carbon::parse($setting->clock_in_end)->format('H:i:s')) {
            $isLate = true;
        }

        $attendance = StaffAttendance::where('staff_id', $request->input('staff_id'))
            ->whereDate('date', $request->input('date'))
            ->first();

        if ($attendance?->clock_in_time) {
            return back()->with(
                'info',
                'Staff already clocked in today at ' .
                Carbon::parse($attendance->clock_in_time)->format('h:i A')
            );
        }

        StaffAttendance::updateOrCreate(
            [
                'staff_id' => $request->input('staff_id'),
                'date' => $request->input('date'),
            ],
            [
                'academic_year_id' => $request->input('academic_year_id'),
                'term_id' => $request->input('term_id'),
                'clock_in_time' => $currentTime,
                'clock_in_latitude' => $request->input('latitude'),
                'clock_in_longitude' => $request->input('longitude'),
                'status' => $isLate ? 'late' : 'present',
            ]
        );

        return redirect()->route('staff-attendance.index', [
            'academic_year_id' => $request->input('academic_year_id'),
            'term_id' => $request->input('term_id'),
        ])->with(
            'success',
            'Clock-in successful at ' . $now->format('h:i A') .
            ($isLate ? '. Status: LATE.' : '. Status: PRESENT.')
        );
    }

    public function gpsClockIn(Request $request)
    {
        return $this->clockIn($request);
    }

    public function clockOut(Request $request)
    {
        $this->validateGps($request);
        $this->ensureDateInTerm(
            $request->input('date'),
            (int) $request->input('academic_year_id'),
            (int) $request->input('term_id')
        );

        $setting = AttendanceSetting::first();
        if (!$setting) {
            return back()->with('error', 'Attendance settings not configured.');
        }

        $now = Carbon::now();

        if ($request->input('date') !== $now->toDateString()) {
            return back()->with('error', 'Clock-out can only be performed for today.');
        }

        if (!$setting->gps_enabled) {
            return back()->with('error', 'GPS attendance is currently disabled.');
        }

        $this->ensureWithinGpsRadius($request, $setting);

        $attendance = StaffAttendance::where('staff_id', $request->input('staff_id'))
            ->whereDate('date', $request->input('date'))
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No clock-in record found for today. Please clock in first.');
        }

        if ((int) $attendance->academic_year_id !== (int) $request->input('academic_year_id') ||
            (int) $attendance->term_id !== (int) $request->input('term_id')) {
            return back()->with('error', 'Selected Academic Year and Term do not match the attendance record.');
        }

        if ($attendance->clock_out_time) {
            return back()->with(
                'info',
                'Already clocked out today at ' .
                Carbon::parse($attendance->clock_out_time)->format('h:i A')
            );
        }

        $currentTime = $now->format('H:i:s');

        if ($setting->clock_out_start &&
            $currentTime < Carbon::parse($setting->clock_out_start)->format('H:i:s')) {
            return back()->with(
                'error',
                'Clock-out opens at ' . Carbon::parse($setting->clock_out_start)->format('h:i A') . '.'
            );
        }

        if ($setting->clock_out_end &&
            $currentTime > Carbon::parse($setting->clock_out_end)->format('H:i:s')) {
            return back()->with(
                'error',
                'Clock-out deadline was ' . Carbon::parse($setting->clock_out_end)->format('h:i A') . '.'
            );
        }

        $attendance->update([
            'clock_out_time' => $currentTime,
            'clock_out_latitude' => $request->input('latitude'),
            'clock_out_longitude' => $request->input('longitude'),
        ]);

        return redirect()->route('staff-attendance.index', [
            'academic_year_id' => $request->input('academic_year_id'),
            'term_id' => $request->input('term_id'),
        ])->with('success', 'Clock-out successful at ' . $now->format('h:i A') . '.');
    }

    public function gpsClockOut(Request $request)
    {
        return $this->clockOut($request);
    }

    public function show($id)
    {
        $attendance = StaffAttendance::with([
            'staff', 'academicYear', 'term'
        ])->findOrFail($id);

        return view('staffattendance.show', compact('attendance'));
    }

    public function edit($id)
    {
        $attendance = StaffAttendance::with([
            'staff', 'academicYear', 'term'
        ])->findOrFail($id);

        return view('staffattendance.edit', [
            'attendance' => $attendance,
            'academicYears' => $this->academicYears(),
            'terms' => $this->terms($attendance->academic_year_id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $attendance = StaffAttendance::findOrFail($id);

        $this->validateAttendance($request, true);

        $this->ensureDateInTerm(
            $request->input('date'),
            (int) $request->input('academic_year_id'),
            (int) $request->input('term_id')
        );

        $duplicate = StaffAttendance::where('staff_id', $attendance->staff_id)
            ->whereDate('date', $request->input('date'))
            ->whereKeyNot($attendance->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->with('error', 'Another attendance record already exists for that staff member and date.')
                ->withInput();
        }

        $attendance->update([
            'academic_year_id' => $request->input('academic_year_id'),
            'term_id' => $request->input('term_id'),
            'date' => $request->input('date'),
            'status' => $request->input('status'),
            'clock_in_time' => $request->input('clock_in_time'),
            'clock_out_time' => $request->input('clock_out_time'),
            'remarks' => $request->input('remarks'),
        ]);

        return redirect()->route('staff-attendance.index', [
            'academic_year_id' => $request->input('academic_year_id'),
            'term_id' => $request->input('term_id'),
        ])->with('success', 'Attendance updated successfully.');
    }

    public function destroy($id)
    {
        $attendance = StaffAttendance::findOrFail($id);
        $yearId = $attendance->academic_year_id;
        $termId = $attendance->term_id;
        $attendance->delete();

        return redirect()->route('staff-attendance.index', array_filter([
            'academic_year_id' => $yearId,
            'term_id' => $termId,
        ], fn ($v) => $v !== null))
            ->with('success', 'Attendance record deleted successfully.');
    }

    public function history($staffId, Request $request)
    {
        $staff = Staff::findOrFail($staffId);
        [$yearId, $termId] = $this->period($request);

        $query = StaffAttendance::with(['academicYear', 'term'])
            ->where('staff_id', $staffId);

        $this->filterPeriod($query, $yearId, $termId);

        $history = $query->latest('date')
            ->paginate(30)
            ->withQueryString();

        return view('staffattendance.history', [
            'staff' => $staff,
            'history' => $history,
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function report(Request $request)
    {
        [$yearId, $termId] = $this->period($request);

        $query = StaffAttendance::query();
        $this->filterPeriod($query, $yearId, $termId);

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        $report = $query->select(
            'staff_id',
            DB::raw('COUNT(*) as total_days'),
            DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days"),
            DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days"),
            DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days")
        )->groupBy('staff_id')->with('staff')->get();

        return view('staffattendance.report', [
            'report' => $report,
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function liveMap(Request $request)
    {
        [$yearId, $termId] = $this->period($request);
        $setting = AttendanceSetting::first();

        $query = StaffAttendance::with(['staff', 'academicYear', 'term'])
            ->whereDate('date', today())
            ->whereNotNull('clock_in_latitude')
            ->whereNotNull('clock_in_longitude');

        $this->filterPeriod($query, $yearId, $termId);
        $attendances = $query->get();

        $locations = $attendances->map(function ($a) {
            $staff = $a->staff;

            return [
                'id' => $a->id,
                'staff' => [
                    'id' => $staff->id ?? null,
                    'first_name' => $staff->first_name ?? 'Unknown',
                    'last_name' => $staff->last_name ?? '',
                    'full_name' => trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')),
                ],
                'academic_year' => $a->academicYear->name ?? null,
                'term' => $a->term->name ?? null,
                'clock_in_time' => $a->clock_in_time,
                'clock_out_time' => $a->clock_out_time,
                'clock_in_latitude' => $a->clock_in_latitude,
                'clock_in_longitude' => $a->clock_in_longitude,
                'clock_out_latitude' => $a->clock_out_latitude,
                'clock_out_longitude' => $a->clock_out_longitude,
                'status' => $a->status,
                'is_late' => $a->status === 'late',
            ];
        });

        return view('staffattendance.live-map', [
            'locations' => $locations,
            'attendances' => $attendances,
            'setting' => $setting,
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function liveLocations(Request $request)
    {
        [$yearId, $termId] = $this->period($request);

        $query = StaffAttendance::with(['staff', 'academicYear', 'term'])
            ->whereDate('date', today())
            ->whereNotNull('clock_in_latitude')
            ->whereNotNull('clock_in_longitude');

        $this->filterPeriod($query, $yearId, $termId);

        $locations = $query->get()->map(function ($a) {
            $staff = $a->staff;

            return [
                'staff' => [
                    'id' => $staff->id ?? null,
                    'first_name' => $staff->first_name ?? '',
                    'last_name' => $staff->last_name ?? '',
                    'full_name' => trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')),
                ],
                'academic_year_id' => $a->academic_year_id,
                'term_id' => $a->term_id,
                'academic_year' => $a->academicYear->name ?? null,
                'term' => $a->term->name ?? null,
                'clock_in' => $a->clock_in_time,
                'clock_out' => $a->clock_out_time,
                'clock_in_latitude' => $a->clock_in_latitude,
                'clock_in_longitude' => $a->clock_in_longitude,
                'clock_out_latitude' => $a->clock_out_latitude,
                'clock_out_longitude' => $a->clock_out_longitude,
                'is_late' => $a->status === 'late',
                'status' => $a->status,
                'remarks' => $a->remarks ?? 'No remarks',
            ];
        });

        return response()->json([
            'success' => true,
            'academic_year_id' => $yearId,
            'term_id' => $termId,
            'locations' => $locations,
        ]);
    }

    public function getSettings()
    {
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance settings not configured',
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
            ],
        ]);
    }

    public function monthlyReport(Request $request)
    {
        [$yearId, $termId] = $this->period($request);
        [$year, $month] = $this->month($request);

        $staffMembers = Staff::orderBy('first_name')->orderBy('last_name')->get();

        $query = StaffAttendance::whereBetween('date', [
            Carbon::create($year, $month, 1)->startOfDay(),
            Carbon::create($year, $month, 1)->endOfMonth()->endOfDay(),
        ]);

        $this->filterPeriod($query, $yearId, $termId);
        $attendances = $query->get()->groupBy('staff_id');

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $days = collect(range(1, $daysInMonth))
            ->map(fn ($d) => Carbon::create($year, $month, $d));

        $monthlyData = [];

        foreach ($staffMembers as $staff) {
            $records = $attendances->get($staff->id, collect());
            $attendanceData = [];

            foreach ($days as $day) {
                $date = $day->toDateString();
                $record = $records->first(
                    fn ($a) => Carbon::parse($a->date)->toDateString() === $date
                );

                $attendanceData[$date] = [
                    'status' => $record?->status ?? 'absent',
                    'clock_in' => $record?->clock_in_time,
                    'clock_out' => $record?->clock_out_time,
                    'record_id' => $record?->id,
                ];
            }

            $present = collect($attendanceData)->where('status', 'present')->count();
            $late = collect($attendanceData)->where('status', 'late')->count();
            $absent = collect($attendanceData)->where('status', 'absent')->count();

            $monthlyData[] = [
                'staff' => $staff,
                'attendance' => $attendanceData,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'total' => $daysInMonth,
                'attendance_rate' => $daysInMonth
                    ? round((($present + $late) / $daysInMonth) * 100, 2)
                    : 0,
            ];
        }

        $summary = [
            'total_staff' => $staffMembers->count(),
            'total_present' => collect($monthlyData)->sum('present'),
            'total_late' => collect($monthlyData)->sum('late'),
            'total_absent' => collect($monthlyData)->sum('absent'),
            'month_name' => Carbon::create($year, $month, 1)->format('F Y'),
        ];

        return view('staffattendance.monthly-report', [
            'monthlyData' => $monthlyData,
            'days' => $days,
            'month' => $month,
            'year' => $year,
            'summary' => $summary,
            'staffMembers' => $staffMembers,
            'academicYears' => $this->academicYears(),
            'selectedAcademicYear' => $yearId,
            'selectedTerm' => $termId,
            'terms' => $this->terms($yearId),
        ]);
    }

    public function exportMonthlyReport(Request $request)
    {
        [$yearId, $termId] = $this->period($request);
        [$year, $month] = $this->month($request);

        $staffMembers = Staff::orderBy('first_name')->orderBy('last_name')->get();

        $query = StaffAttendance::whereBetween('date', [
            Carbon::create($year, $month, 1)->startOfDay(),
            Carbon::create($year, $month, 1)->endOfMonth()->endOfDay(),
        ]);

        $this->filterPeriod($query, $yearId, $termId);
        $attendances = $query->get()->groupBy('staff_id');

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $filename = 'staff_attendance_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use (
            $staffMembers, $attendances, $year, $month, $daysInMonth
        ) {
            $out = fopen('php://output', 'w');

            $header = ['Staff Name'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $header[] = sprintf('%02d', $d);
            }
            array_push($header, 'Present', 'Late', 'Absent', 'Total', 'Rate %');
            fputcsv($out, $header);

            foreach ($staffMembers as $staff) {
                $records = $attendances->get($staff->id, collect());
                $row = [trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''))];
                $present = $late = $absent = 0;

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = Carbon::create($year, $month, $d)->toDateString();
                    $record = $records->first(
                        fn ($a) => Carbon::parse($a->date)->toDateString() === $date
                    );

                    if (!$record) {
                        $row[] = 'Absent';
                        $absent++;
                        continue;
                    }

                    $row[] = ucfirst($record->status);
                    if ($record->status === 'present') $present++;
                    elseif ($record->status === 'late') $late++;
                    else $absent++;
                }

                $row[] = $present;
                $row[] = $late;
                $row[] = $absent;
                $row[] = $daysInMonth;
                $row[] = $daysInMonth
                    ? round((($present + $late) / $daysInMonth) * 100, 2)
                    : 0;

                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validateAttendance(Request $request, bool $update): void
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')->where(
                    fn ($q) => $q->where('academic_year_id', $request->input('academic_year_id'))
                ),
            ],
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,late'],
            'clock_in_time' => ['nullable', 'date_format:H:i'],
            'clock_out_time' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function validateGps(Request $request): void
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')->where(
                    fn ($q) => $q->where('academic_year_id', $request->input('academic_year_id'))
                ),
            ],
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
    }

    private function ensureWithinGpsRadius(Request $request, AttendanceSetting $setting): void
    {
        $distance = $this->calculateDistance(
            $request->input('latitude'),
            $request->input('longitude'),
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > (float) $setting->radius) {
            abort(422,
                'You are outside the allowed attendance radius. Current distance: ' .
                round($distance, 2) . ' meters. Maximum allowed: ' .
                $setting->radius . ' meters.'
            );
        }
    }

    private function ensureDateInTerm(string $date, int $yearId, int $termId): void
    {
        $term = Term::whereKey($termId)
            ->where('academic_year_id', $yearId)
            ->first();

        if (!$term) {
            abort(422, 'The selected Term does not belong to the selected Academic Year.');
        }

        $day = Carbon::parse($date);

        if ($term->start_date && $day->lt(Carbon::parse($term->start_date)->startOfDay())) {
            abort(422, 'The attendance date is before the selected Term.');
        }

        if ($term->end_date && $day->gt(Carbon::parse($term->end_date)->endOfDay())) {
            abort(422, 'The attendance date is after the selected Term.');
        }
    }

    private function period(Request $request): array
    {
        $settings = SystemSetting::instance();

        $yearId = $request->has('academic_year_id')
            ? $request->input('academic_year_id')
            : $settings->current_academic_year_id;

        $termId = $request->has('term_id')
            ? $request->input('term_id')
            : $settings->current_term_id;

        $yearId = $yearId !== null && $yearId !== '' ? (int) $yearId : null;
        $termId = $termId !== null && $termId !== '' ? (int) $termId : null;

        if ($yearId !== null && $termId !== null &&
            !Term::whereKey($termId)->where('academic_year_id', $yearId)->exists()) {
            abort(422, 'The selected Term does not belong to the selected Academic Year.');
        }

        return [$yearId, $termId];
    }

    private function filterPeriod($query, ?int $yearId, ?int $termId): void
    {
        if ($yearId !== null) {
            $query->where('academic_year_id', $yearId);
        }
        if ($termId !== null) {
            $query->where('term_id', $termId);
        }
    }

    private function academicYears()
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }

    private function terms(?int $yearId)
    {
        return $yearId === null
            ? collect()
            : Term::where('academic_year_id', $yearId)
                ->orderBy('start_date')
                ->orderBy('id')
                ->get();
    }

    private function month(Request $request): array
    {
        if (!$request->filled('month_year')) {
            return [now()->year, now()->month];
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $request->input('month_year'))) {
            abort(422, 'Invalid month selection.');
        }

        [$year, $month] = array_map('intval', explode('-', $request->input('month_year')));

        if ($month < 1 || $month > 12) {
            abort(422, 'Invalid month selection.');
        }

        return [$year, $month];
    }

    private function calculateDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo
    ): float {
        $earthRadius = 6371000;

        $latFrom = deg2rad((float) $latitudeFrom);
        $lonFrom = deg2rad((float) $longitudeFrom);
        $latTo = deg2rad((float) $latitudeTo);
        $lonTo = deg2rad((float) $longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }
}
