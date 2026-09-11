<?php

namespace App\Http\Controllers;

use App\Models\StudentClassAssignment;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    /**
     * Display all terms.
     */
    public function index()
    {
        $terms = Term::query()
            ->with('academicYear')
            ->orderByDesc('academic_year_id')
            ->orderBy('start_date')
            ->orderBy('name')
            ->get();

        return view('terms.index', compact('terms'));
    }


    /**
     * Show form for creating a term.
     */
    public function create()
    {
        $academicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();

        return view('terms.create', compact('academicYears'));
    }


    /**
     * Store a new term.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'name' => [
                'required',
                'string',
                Rule::in([
                    'First Term',
                    'Second Term',
                    'Third Term',
                ]),
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate term within the same academic year
        |--------------------------------------------------------------------------
        */
        $exists = Term::query()
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->whereRaw(
                'LOWER(name) = ?',
                [strtolower($validated['name'])]
            )
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' =>
                        'This term already exists for the selected academic year.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Create term
        |--------------------------------------------------------------------------
        */
        Term::create([
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);


        return redirect()
            ->route('terms.index')
            ->with(
                'success',
                'Term created successfully.'
            );
    }


    /**
     * Display a single term.
     */
    public function show(Term $term)
    {
        $term->load('academicYear');

        return view('terms.show', compact('term'));
    }


    /**
     * Show form for editing a term.
     */
    public function edit(Term $term)
    {
        $academicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();

        return view(
            'terms.edit',
            compact(
                'term',
                'academicYears'
            )
        );
    }


    /**
     * Update an existing term.
     */
    public function update(Request $request, Term $term)
    {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'name' => [
                'required',
                'string',
                Rule::in([
                    'First Term',
                    'Second Term',
                    'Third Term',
                ]),
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate term within the academic year
        |--------------------------------------------------------------------------
        */
        $exists = Term::query()
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->whereRaw(
                'LOWER(name) = ?',
                [strtolower($validated['name'])]
            )
            ->where('id', '!=', $term->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' =>
                        'This term already exists for the selected academic year.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Update term
        |--------------------------------------------------------------------------
        */
        $term->update([
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Keep System Settings safe
        |--------------------------------------------------------------------------
        |
        | If this term is currently selected as the system term and the
        | academic year changes, the SystemSetting relationship could
        | become invalid. We therefore clear the current term selection.
        |
        */
        $settings = \App\Models\SystemSetting::instance();

        if (
            $settings->current_term_id === $term->id &&
            (
                $settings->current_academic_year_id !=
                $term->academic_year_id
            )
        ) {
            $settings->update([
                'current_term_id' => null,
            ]);
        }


        return redirect()
            ->route('terms.index')
            ->with(
                'success',
                'Term updated successfully.'
            );
    }


    /**
     * Delete a term.
     */
    public function destroy(Term $term)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deleting a term currently used by System Settings
        |--------------------------------------------------------------------------
        */
        $settings = \App\Models\SystemSetting::instance();

        if ($settings->current_term_id === $term->id) {
            return redirect()
                ->route('terms.index')
                ->with(
                    'error',
                    'This term cannot be deleted because it is currently selected as the system term. Change System Settings first.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent deleting a term already used by student assignments
        |--------------------------------------------------------------------------
        */
        if (
            StudentClassAssignment::query()
                ->where('term_id', $term->id)
                ->exists()
        ) {
            return redirect()
                ->route('terms.index')
                ->with(
                    'error',
                    'This term cannot be deleted because student assignments already reference it.'
                );
        }


        $term->delete();

        return redirect()
            ->route('terms.index')
            ->with(
                'success',
                'Term deleted successfully.'
            );
    }
}