<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\SchoolFeeStructure;
use App\Models\StudentClass;
use App\Models\Term;
use Illuminate\Http\Request;

class SchoolFeeStructureController extends Controller
{
    public function index()
    {
        $structures = SchoolFeeStructure::with([
            'academicYear',
            'term',
            'studentClass',
            'feeCategory'
        ])->latest()->paginate(20);

        return view(
            'school-fee-structures.index',
            compact('structures')
        );
    }

    public function create()
    {
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        $classes = StudentClass::all();
        $categories = FeeCategory::all();

        return view(
            'school-fee-structures.create',
            compact(
                'academicYears',
                'terms',
                'classes',
                'categories'
            )
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable'
        ]);

        $validated['is_active'] = $request->has('is_active');

        SchoolFeeStructure::create($validated);

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', 'Fee structure created successfully.');
    }

    public function show(SchoolFeeStructure $schoolFeeStructure)
    {
        $schoolFeeStructure->load([
            'academicYear',
            'term',
            'studentClass',
            'feeCategory'
        ]);

        return view(
            'school-fee-structures.show',
            compact('schoolFeeStructure')
        );
    }

    public function edit(SchoolFeeStructure $schoolFeeStructure)
    {
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        $classes = StudentClass::all();
        $categories = FeeCategory::all();

        return view(
            'school-fee-structures.edit',
            compact(
                'schoolFeeStructure',
                'academicYears',
                'terms',
                'classes',
                'categories'
            )
        );
    }

    public function update(
        Request $request,
        SchoolFeeStructure $schoolFeeStructure
    ) {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $schoolFeeStructure->update($validated);

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', 'Fee structure updated successfully.');
    }

    public function destroy(SchoolFeeStructure $schoolFeeStructure)
    {
        $schoolFeeStructure->delete();

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', 'Fee structure deleted successfully.');
    }
}