<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index()
    {
        $subjects = Subject::with('staff')
            ->latest()
            ->paginate(10);

        return view('subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $staffs = Staff::orderBy('first_name')->get();

        return view('subjects.create', compact('staffs'));
    }

    /**
     * Store a newly created subject.
     */
    public function store(Request $request)
    {
        $request->validate([

            'name'              => 'required|string|max:255',

            'code'              => 'nullable|string|max:50|unique:subjects,code',

            'description'       => 'nullable|string',

            'education_level'   => 'required|in:Early Childhood,Primary,JHS,SHS',

            'category'          => 'required|in:Core,Elective,Vocational,Technical',

            'staff_id'          => 'nullable|exists:staff,id',

            'is_active'         => 'nullable|boolean',

        ]);

        Subject::create([

            'name'              => $request->name,

            'code'              => $request->code,

            'description'       => $request->description,

            'education_level'   => $request->education_level,

            'category'          => $request->category,

            'staff_id'          => $request->staff_id,

            'is_active'         => $request->boolean('is_active'),

        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $subject->load('staff');

        return view('subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        $staffs = Staff::orderBy('first_name')->get();

        return view('subjects.edit', compact(
            'subject',
            'staffs'
        ));
    }

    /**
     * Update the specified subject.
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([

            'name'              => 'required|string|max:255',

            'code'              => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,

            'description'       => 'nullable|string',

            'education_level'   => 'required|in:Early Childhood,Primary,JHS,SHS',

            'category'          => 'required|in:Core,Elective,Vocational,Technical',

            'staff_id'          => 'nullable|exists:staff,id',

            'is_active'         => 'nullable|boolean',

        ]);

        $subject->update([

            'name'              => $request->name,

            'code'              => $request->code,

            'description'       => $request->description,

            'education_level'   => $request->education_level,

            'category'          => $request->category,

            'staff_id'          => $request->staff_id,

            'is_active'         => $request->boolean('is_active'),

        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}