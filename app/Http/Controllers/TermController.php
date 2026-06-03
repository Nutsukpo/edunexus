<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TermController extends Controller
{
    /**
     * Display all terms
     */
    public function index()
    {
        $terms = Term::with('academicYear')
                    ->latest()
                    ->get();

        return view('terms.index', compact('terms'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $academicYears = AcademicYear::all();

        return view('terms.create', compact('academicYears'));
    }

    /**
     * Store new term
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:255',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after:start_date',
        ]);

        Term::create([
            'academic_year_id' => $request->academic_year_id,
            'name'             => $request->name,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'is_active'        => $request->has('is_active'),
        ]);

        return redirect()
                ->route('terms.index')
                ->with('success', 'Term created successfully.');
    }

    /**
     * Display single term
     */
    public function show(Term $term)
    {
        return view('terms.show', compact('term'));
    }

    /**
     * Show edit form
     */
    public function edit(Term $term)
    {
        $academicYears = AcademicYear::all();

        return view('terms.edit', compact('term', 'academicYears'));
    }

    /**
     * Update term
     */
    public function update(Request $request, Term $term)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name'             => 'required|string|max:255',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after:start_date',
        ]);

        $term->update([
            'academic_year_id' => $request->academic_year_id,
            'name'             => $request->name,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'is_active'        => $request->has('is_active'),
        ]);

        return redirect()
                ->route('terms.index')
                ->with('success', 'Term updated successfully.');
    }

    /**
     * Delete term
     */
    public function destroy(Term $term)
    {
        $term->delete();

        return redirect()
                ->route('terms.index')
                ->with('success', 'Term deleted successfully.');
    }
}