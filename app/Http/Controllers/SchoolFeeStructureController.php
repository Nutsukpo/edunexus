<?php
// app/Http/Controllers/SchoolFeeStructureController.php

namespace App\Http\Controllers;

use App\Models\SchoolFeeStructure;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SchoolFeeStructureController extends Controller
{
    public function index()
    {
        try {
            // Check if table exists
            if (!Schema::hasTable('school_fee_structures')) {
                return view('admin.school-fee-structures.index', [
                    'schoolFeeStructures' => collect([]),
                    'tableMissing' => true
                ]);
            }

            $schoolFeeStructures = SchoolFeeStructure::with([
                    'studentClass',
                    'academicYear',
                    'term',
                    'feeCategory'
                ])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15);

            return view('admin.school-fee-structures.index', compact('schoolFeeStructures'));
            
        } catch (\Exception $e) {
            \Log::error('Error loading school fee structures: ' . $e->getMessage());
            return back()->with('error', 'Unable to load fee structures. Please run migrations.');
        }
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

        $terms = Term::orderBy('name')
            ->get();

        return view('admin.school-fee-structures.create', compact(
            'classes', 'academicYears', 'categories', 'terms'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:school_fee_structures,code',
            'student_class_id' => 'nullable|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:tuition,registration,exam,library,sports,transport,other',
            'payment_frequency' => 'required|in:one-time,termly,monthly,quarterly',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle boolean fields
        $validated['is_optional'] = $request->has('is_optional');
        $validated['is_mandatory'] = $request->has('is_mandatory') || !$request->has('is_optional');
        $validated['is_active'] = $request->has('is_active');

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = SchoolFeeStructure::generateUniqueCode();
        }

        try {
            $schoolFeeStructure = SchoolFeeStructure::create($validated);

            return redirect()
                ->route('school-fee-structures.index')
                ->with('success', "School fee structure '{$schoolFeeStructure->name}' created successfully.");

        } catch (\Exception $e) {
            \Log::error('Error creating fee structure: ' . $e->getMessage());
            return back()
                ->with('error', 'Failed to create fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(SchoolFeeStructure $schoolFeeStructure)
    {
        $schoolFeeStructure->load([
            'studentClass',
            'academicYear',
            'term',
            'feeCategory',
            'studentFeeAllocations.student'
        ]);

        return view('admin.school-fee-structures.show', compact('schoolFeeStructure'));
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

        $terms = Term::orderBy('name')
            ->get();

        return view('admin.school-fee-structures.edit', compact(
            'schoolFeeStructure',
            'classes',
            'academicYears',
            'categories',
            'terms'
        ));
    }

    public function update(Request $request, SchoolFeeStructure $schoolFeeStructure)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:school_fee_structures,code,' . $schoolFeeStructure->id,
            'student_class_id' => 'nullable|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:tuition,registration,exam,library,sports,transport,other',
            'payment_frequency' => 'required|in:one-time,termly,monthly,quarterly',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle boolean fields
        $validated['is_optional'] = $request->has('is_optional');
        $validated['is_mandatory'] = $request->has('is_mandatory') || !$request->has('is_optional');
        $validated['is_active'] = $request->has('is_active');

        try {
            $schoolFeeStructure->update($validated);

            return redirect()
                ->route('school-fee-structures.index')
                ->with('success', "School fee structure '{$schoolFeeStructure->name}' updated successfully.");

        } catch (\Exception $e) {
            \Log::error('Error updating fee structure: ' . $e->getMessage());
            return back()
                ->with('error', 'Failed to update fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(SchoolFeeStructure $schoolFeeStructure)
    {
        try {
            // Check if there are any student allocations
            if ($schoolFeeStructure->studentFeeAllocations()->count() > 0) {
                return back()->with('error', 
                    "Cannot delete '{$schoolFeeStructure->name}' because it has student allocations.");
            }

            $schoolFeeStructure->delete();

            return redirect()
                ->route('school-fee-structures.index')
                ->with('success', "School fee structure '{$schoolFeeStructure->name}' deleted successfully.");

        } catch (\Exception $e) {
            \Log::error('Error deleting fee structure: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete fee structure.');
        }
    }

    public function toggleStatus($id)
    {
        $schoolFeeStructure = SchoolFeeStructure::findOrFail($id);
        $schoolFeeStructure->is_active = !$schoolFeeStructure->is_active;
        $schoolFeeStructure->save();

        $status = $schoolFeeStructure->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', "School fee structure '{$schoolFeeStructure->name}' {$status} successfully.");
    }

    public function toggleOptional($id)
    {
        $schoolFeeStructure = SchoolFeeStructure::findOrFail($id);
        $schoolFeeStructure->is_optional = !$schoolFeeStructure->is_optional;
        $schoolFeeStructure->is_mandatory = !$schoolFeeStructure->is_optional;
        $schoolFeeStructure->save();

        $status = $schoolFeeStructure->is_optional ? 'made optional' : 'made mandatory';

        return redirect()
            ->route('school-fee-structures.index')
            ->with('success', "School fee structure '{$schoolFeeStructure->name}' {$status} successfully.");
    }
}