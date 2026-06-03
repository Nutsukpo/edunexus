<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSetting;
use Illuminate\Http\Request;

class AttendanceSettingController extends Controller
{
    /**
     * Display attendance settings
     */
    public function index()
    {
        $setting = AttendanceSetting::first();

        return view('attendance-settings.index', compact('setting'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $setting = AttendanceSetting::first();

        // Prevent multiple settings records
        if ($setting) {

            return redirect()
                ->route('attendance-settings.edit', $setting->id)
                ->with('info', 'Attendance settings already exist.');
        }

        return view('attendance-settings.create');
    }

    /**
     * Store attendance settings
     */
    public function store(Request $request)
    {
        $request->validate([

            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            'radius' => 'required|integer|min:1',

            'clock_in_start' => 'nullable',
            'clock_in_end' => 'nullable',

            'clock_out_start' => 'nullable',
            'clock_out_end' => 'nullable',

            'gps_enabled' => 'nullable|boolean',
        ]);

        AttendanceSetting::create([

            'latitude' => $request->latitude,
            'longitude' => $request->longitude,

            'radius' => $request->radius,

            'clock_in_start' => $request->clock_in_start,
            'clock_in_end' => $request->clock_in_end,

            'clock_out_start' => $request->clock_out_start,
            'clock_out_end' => $request->clock_out_end,

            'gps_enabled' => $request->has('gps_enabled'),
        ]);

        return redirect()
            ->route('attendance-settings.index')
            ->with('success', 'Attendance settings created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $setting = AttendanceSetting::findOrFail($id);

        return view('attendance-settings.edit', compact('setting'));
    }

    /**
     * Update attendance settings
     */
    public function update(Request $request, $id)
    {
        $request->validate([

            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            'radius' => 'required|integer|min:1',

            'clock_in_start' => 'nullable',
            'clock_in_end' => 'nullable',

            'clock_out_start' => 'nullable',
            'clock_out_end' => 'nullable',

            'gps_enabled' => 'nullable|boolean',
        ]);

        $setting = AttendanceSetting::findOrFail($id);

        $setting->update([

            'latitude' => $request->latitude,
            'longitude' => $request->longitude,

            'radius' => $request->radius,

            'clock_in_start' => $request->clock_in_start,
            'clock_in_end' => $request->clock_in_end,

            'clock_out_start' => $request->clock_out_start,
            'clock_out_end' => $request->clock_out_end,

            'gps_enabled' => $request->has('gps_enabled'),
        ]);

        return redirect()
            ->route('attendance-settings.index')
            ->with('success', 'Attendance settings updated successfully.');
    }

    /**
     * Delete attendance settings
     */
    public function destroy($id)
    {
        $setting = AttendanceSetting::findOrFail($id);

        $setting->delete();

        return redirect()
            ->route('attendance-settings.index')
            ->with('success', 'Attendance settings deleted successfully.');
    }
}