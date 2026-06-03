<?php

namespace App\Http\Controllers;

use App\Models\SchoolFeeStructure;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Term;
use Illuminate\Http\Request;

class SchoolFeeStructureController extends Controller
{
    public function index()
    {
        $schoolFeeStructures = SchoolFeeStructure::with([
                'studentClass',
                'academicYear',
                'feeCategory'
            ])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view(
            'school-fee-structures.index',
            compact('schoolFeeStructures')
        );
    }

    public function create()
    {
        $classes = StudentClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')
            ->get();

        $categories = FeeCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $terms = Term::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'school-fee-structures.create',
            compact('classes', 'academicYears', 'categories', 'terms')
        );
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:school_fee_structures,code',
        'student_class_id' => 'nullable|exists:student_classes,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'term_id' => 'required|exists:terms,id',
        'fee_category_id' => 'required|exists:fee_categories,id',
        'amount' => 'required|numeric|min:0',
        'fee_type' => 'required|in:tuition,registration,exam,library,sports,transport,other',
        'payment_frequency' => 'required|in:one-time,termly,monthly,quarterly',
        'description' => 'nullable|string',
        'due_date' => 'nullable|date',
    ]);

    $validated['is_mandatory'] = $request->has('is_mandatory');
    $validated['is_active'] = $request->has('is_active');

    SchoolFeeStructure::create($validated);

    return redirect()
        ->route('school-fee-structures.index')
        ->with('success', 'School fee structure created successfully.');
}

    public function show(SchoolFeeStructure $schoolFeeStructure)
    {
        $schoolFeeStructure->load([
            'studentClass',
            'academicYear',
            'term',
            'feeCategory'
        ]);

        return view(
            'school-fee-structures.show',
            compact('schoolFeeStructure')
        );
    }

    public function edit(SchoolFeeStructure $schoolFeeStructure)
    {
        $classes = StudentClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')
            ->get();

        $categories = FeeCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $terms = Term::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'school-fee-structures.edit',
            compact(
                'schoolFeeStructure',
                'classes',
                'academicYears',
                'categories',
                'terms'
            )
        );
    }

    public function update(Request $request, SchoolFeeStructure $schoolFeeStructure)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:school_fee_structures,code,' . $schoolFeeStructure->id,
            'class_id' => 'nullable|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:tuition,registration,exam,library,sports,transport,other',
            'payment_frequency' => 'required|in:one-time,termly,monthly,quarterly',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_mandatory' => 'nullable|boolean',
        ]);

        $validated['is_mandatory'] = $request->has('is_mandatory');

        $schoolFeeStructure->update($validated);

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', 'School fee structure updated successfully.');
    }

    public function destroy(SchoolFeeStructure $schoolFeeStructure)
    {
        $schoolFeeStructure->delete();

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', 'School fee structure deleted successfully.');
    }
}