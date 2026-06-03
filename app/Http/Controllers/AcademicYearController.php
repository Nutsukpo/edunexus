<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display all academic years
     */
    public function index()
    {
        $academicYears = AcademicYear::latest()->get();

        return view('academic_years.index', compact('academicYears'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('academic_years.create');
    }

    /**
     * Store academic year
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        AcademicYear::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->has('is_active'),
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic Year created successfully.');
    }

    /**
     * Show single academic year
     */
    public function show(AcademicYear $academicYear)
    {
        return view('academic_years.show', compact('academicYear'));
    }

    /**
     * Show edit form
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('academic_years.edit', compact('academicYear'));
    }

    /**
     * Update academic year
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name'       => 'required|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $academicYear->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->has('is_active'),
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic Year updated successfully.');
    }

    /**
     * Delete academic year
     */
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic Year deleted successfully.');
    }
}