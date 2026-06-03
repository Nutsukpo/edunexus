<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('hod')->latest()->get();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        // Get all active staff for HOD selection
        $staff = Staff::where('status', 'Active')
                      ->orderBy('first_name')
                      ->get();

        return view('departments.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:50|unique:departments,code',
            'description'        => 'nullable|string',
            'head_of_department' => 'nullable|exists:staff,id',
            'status'             => 'required|in:active,inactive',
        ]);

        Department::create([
            'name'               => $request->name,
            'code'               => $request->code,
            'description'        => $request->description,
            'head_of_department' => $request->head_of_department,
            'status'             => $request->status,
        ]);

        return redirect()
                ->route('departments.index')
                ->with('success', 'Department created successfully');
    }

    public function show(Department $department)
    {
        $department->load('hod', 'staff');
        
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $staff = Staff::where('status', 'Active')
                      ->orderBy('first_name')
                      ->get();

        return view('departments.edit', compact('department', 'staff'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description'        => 'nullable|string',
            'head_of_department' => 'nullable|exists:staff,id',
            'status'             => 'required|in:active,inactive',
        ]);

        $department->update([
            'name'               => $request->name,
            'code'               => $request->code,
            'description'        => $request->description,
            'head_of_department' => $request->head_of_department,
            'status'             => $request->status,
        ]);

        return redirect()
                ->route('departments.index')
                ->with('success', 'Department updated successfully');
    }

    public function destroy(Department $department)
    {
        // Check if department has staff members
        if ($department->staff()->count() > 0) {
            return redirect()
                    ->route('departments.index')
                    ->with('error', 'Cannot delete department with assigned staff members. Please reassign staff first.');
        }
        
        $department->delete();

        return redirect()
                ->route('departments.index')
                ->with('success', 'Department deleted successfully');
    }
}