<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class StaffController extends Controller
{
    /**
     * Display all staff
     */
    public function index()
    {
        $staff = Staff::orderBy('created_at', 'desc')->get();
        return view('staff.index', compact('staff'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('staff.create');
    }

    /**
     * Store new staff
     */
    // In StaffController.php

public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'gender' => 'nullable|string',
        'date_employed' => 'required|date',
        'status' => 'required|string',
        'staff_type' => 'nullable|string',
        'email' => 'nullable|email|unique:staff,email',
        'phone' => 'nullable|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Generate staff ID if not provided
    $staffId = $request->staff_id ?? $this->generateStaffId();

    // Handle photo upload
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('uploads/staff', 'public');
    }

    Staff::create([
        'staff_id' => $staffId,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'other_name' => $request->other_name,
        'gender' => $request->gender,
        'date_of_birth' => $request->date_of_birth,
        'phone' => $request->phone,
        'email' => $request->email,
        'department' => $request->department,
        'position' => $request->position,
        'date_employed' => $request->date_employed,
        'salary' => $request->salary,
        'address' => $request->address,
        'staff_type' => $request->staff_type,
        'status' => $request->status,
        'photo' => $photoPath,
    ]);

    return redirect()->route('staff.index')->with('success', 'Staff added successfully');
}

    /**
     * Show single staff
     */
    public function show(Staff $staff)
    {
        return view('staff.show', compact('staff'));
    }

    /**
     * Show edit form
     */
    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    /**
     * Update staff
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([

            'staff_id'      => 'required|unique:staff,staff_id,' . $staff->id,

            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'other_name'    => 'nullable|string|max:255',
            'gender'        => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'phone'         => 'nullable|string|max:20',

            // Ignore current record
            'email'         => 'nullable|email|unique:staff,email,' . $staff->id,

            'department'    => 'nullable|string|max:255',
            'position'      => 'nullable|string|max:255',
            'date_employed' => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'address'       => 'nullable|string',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'staff_type'    => 'nullable|string|max:255',
            'status'        => 'required|string|max:50',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Photo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('photo')) {

            // Delete old photo

            if (
                $staff->photo &&
                File::exists(public_path('uploads/staff/' . $staff->photo))
            ) {

                File::delete(public_path('uploads/staff/' . $staff->photo));
            }

            $photo = $request->file('photo');

            $photoName = time() . '.' . $photo->getClientOriginalExtension();

            $photo->move(public_path('uploads/staff'), $photoName);

            $validated['photo'] = $photoName;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Staff
        |--------------------------------------------------------------------------
        */

        $staff->update($validated);

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff updated successfully.');
    }

    /**
     * Soft Delete Staff
     */
    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff moved to trash successfully.');
    }

    /**
     * Trash List
     */
    public function trash()
    {
        $staff = Staff::onlyTrashed()
            ->latest()
            ->get();

        return view('staff.trash', compact('staff'));
    }

    /**
     * Restore Staff
     */
    public function restore($id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);

        $staff->restore();

        return redirect()
            ->route('staff.trash')
            ->with('success', 'Staff restored successfully.');
    }

    /**
     * Permanently Delete Staff
     */
    public function forceDelete($id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Delete Photo Permanently
        |--------------------------------------------------------------------------
        */

        if (
            $staff->photo &&
            File::exists(public_path('uploads/staff/' . $staff->photo))
        ) {

            File::delete(public_path('uploads/staff/' . $staff->photo));
        }

        $staff->forceDelete();

        return redirect()
            ->route('staff.trash')
            ->with('success', 'Staff permanently deleted successfully.');
    }
}