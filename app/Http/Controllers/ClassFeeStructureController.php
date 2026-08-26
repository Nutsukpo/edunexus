<?php

namespace App\Http\Controllers;

use App\Models\ClassFeeStructure;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClassFeeStructureController extends Controller
{
    /**
     * Display a listing of fee structures.
     */
    public function index(Request $request)
    {
        $query = ClassFeeStructure::with(['studentClass', 'academicYear', 'creator']);

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->where('student_class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('is_required')) {
            $query->where('is_required', $request->is_required);
        }

        $feeStructures = $query->orderBy('created_at', 'desc')->paginate(15);
        $academicYears = AcademicYear::where('is_active', true)->get();
        $classes = StudentClass::where('is_active', true)->get();

        return view('class-fee-structures.index', compact('feeStructures', 'academicYears', 'classes'));
    }

    /**
     * Show the form for creating a new fee structure.
     */
    public function create()
    {
        $classes = StudentClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('name', 'desc')->get();

        return view('class-fee-structures.create', compact('classes', 'academicYears'));
    }

    /**
     * Store a newly created fee structure.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_name' => 'required|string|max:255',
            'fee_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'due_date' => 'nullable|date',
            'metadata' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $feeStructure = ClassFeeStructure::create([
                'student_class_id' => $request->student_class_id,
                'academic_year_id' => $request->academic_year_id,
                'fee_name' => $request->fee_name,
                'fee_type' => $request->fee_type,
                'amount' => $request->amount,
                'description' => $request->description,
                'is_required' => $request->has('is_required'),
                'is_active' => $request->has('is_active'),
                'due_date' => $request->due_date,
                'metadata' => $request->metadata ? json_decode($request->metadata, true) : null,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('class-fee-structures.show', $feeStructure->id)
                ->with('success', 'Fee structure created successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create fee structure: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create fee structure: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified fee structure.
     */
    public function show($id)
    {
        $feeStructure = ClassFeeStructure::with(['studentClass', 'academicYear', 'creator'])
            ->findOrFail($id);

        // Get statistics
        $totalStudents = \App\Models\StudentClassAssignment::where('student_class_id', $feeStructure->student_class_id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->count();

        $totalPaid = \App\Models\FeePayment::whereHas('studentClassAssignment', function($query) use ($feeStructure) {
            $query->where('student_class_id', $feeStructure->student_class_id);
        })->where('status', 'completed')->sum('net_amount');

        return view('class-fee-structures.show', compact('feeStructure', 'totalStudents', 'totalPaid'));
    }

    /**
     * Show the form for editing the specified fee structure.
     */
    public function edit($id)
    {
        $feeStructure = ClassFeeStructure::findOrFail($id);
        $classes = StudentClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('name', 'desc')->get();

        return view('class-fee-structures.edit', compact('feeStructure', 'classes', 'academicYears'));
    }

    /**
     * Update the specified fee structure.
     */
    public function update(Request $request, $id)
    {
        $feeStructure = ClassFeeStructure::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_name' => 'required|string|max:255',
            'fee_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'due_date' => 'nullable|date',
            'metadata' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $feeStructure->update([
                'student_class_id' => $request->student_class_id,
                'academic_year_id' => $request->academic_year_id,
                'fee_name' => $request->fee_name,
                'fee_type' => $request->fee_type,
                'amount' => $request->amount,
                'description' => $request->description,
                'is_required' => $request->has('is_required'),
                'is_active' => $request->has('is_active'),
                'due_date' => $request->due_date,
                'metadata' => $request->metadata ? json_decode($request->metadata, true) : null,
            ]);

            return redirect()
                ->route('class-fee-structures.show', $feeStructure->id)
                ->with('success', 'Fee structure updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update fee structure: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update fee structure: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified fee structure.
     */
    public function destroy($id)
    {
        try {
            $feeStructure = ClassFeeStructure::findOrFail($id);
            
            // Check if there are any associated records
            $hasPayments = \App\Models\FeePayment::whereHas('studentFeeAccount', function($query) use ($feeStructure) {
                $query->whereHas('feeItems', function($q) use ($feeStructure) {
                    $q->where('class_fee_structure_id', $feeStructure->id);
                });
            })->exists();

            if ($hasPayments) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete this fee structure because it has associated payments.');
            }

            $feeStructure->delete();

            return redirect()
                ->route('class-fee-structures.index')
                ->with('success', 'Fee structure deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete fee structure: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to delete fee structure: ' . $e->getMessage());
        }
    }
}