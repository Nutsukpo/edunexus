<?php
// app/Http/Controllers/FeeStructureController.php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\FeeCategory;
use App\Models\FeeItem;
use App\Models\StudentFeeAllocation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FeeStructureController extends Controller
{
    /**
     * Display a listing of fee structures.
     */
    public function index()
    {
        try {
            $feeStructures = FeeStructure::with(['studentClass', 'academicYear', 'term', 'feeItems'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            
            return view('admin.fees.structures.index', compact('feeStructures'));
        } catch (\Exception $e) {
            Log::error('Error loading fee structures: ' . $e->getMessage());
            $feeStructures = collect();
            return view('admin.fees.structures.index', compact('feeStructures'));
        }
    }

    /**
     * Show the form for creating a new fee structure.
     */
    public function create()
    {
        $classes = StudentClass::where('is_active', true)->get();
        $academicYears = AcademicYear::where('is_active', true)->get();
        $terms = Term::all();
        $feeCategories = FeeCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('admin.fees.structures.create', compact('classes', 'academicYears', 'terms', 'feeCategories'));
    }

    /**
     * Store a newly created fee structure.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:one-time,termly,monthly',
            'is_active' => 'boolean',
            'fee_items' => 'required|array|min:1',
            'fee_items.*.fee_category_id' => 'nullable|exists:fee_categories,id',
            'fee_items.*.name' => 'required|string|max:255',
            'fee_items.*.amount' => 'required|numeric|min:0',
            'fee_items.*.is_optional' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if fee structure already exists
        $exists = FeeStructure::where([
            'student_class_id' => $request->student_class_id,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
        ])->exists();

        if ($exists) {
            return back()->with('error', 'A fee structure already exists for this class, academic year, and term.')
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            $feeStructure = FeeStructure::create([
                'student_class_id' => $request->student_class_id,
                'academic_year_id' => $request->academic_year_id,
                'term_id' => $request->term_id,
                'name' => $request->name,
                'description' => $request->description,
                'total_amount' => $request->total_amount,
                'payment_frequency' => $request->payment_frequency,
                'is_active' => $request->has('is_active'),
            ]);

            // Create fee items
            foreach ($request->fee_items as $itemData) {
                FeeItem::create([
                    'fee_structure_id' => $feeStructure->id,
                    'fee_category_id' => $itemData['fee_category_id'] ?? null,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                    'amount' => $itemData['amount'],
                    'is_optional' => isset($itemData['is_optional']),
                    'sort_order' => $itemData['sort_order'] ?? 0,
                ]);
            }

            // Allocate fees to students in this class
            $this->allocateFeesToStudents($feeStructure);

            DB::commit();

            return redirect()->route('fee-structures.index')
                ->with('success', 'Fee structure created and allocated to students successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating fee structure: ' . $e->getMessage());
            return back()->with('error', 'Failed to create fee structure: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Allocate fees to all students in a class.
     */
    private function allocateFeesToStudents($feeStructure)
    {
        // Get students from the class directly
        $students = DB::table('students')
            ->join('student_class_assignments', 'students.id', '=', 'student_class_assignments.student_id')
            ->where('student_class_assignments.student_class_id', $feeStructure->student_class_id)
            ->where('student_class_assignments.academic_year_id', $feeStructure->academic_year_id)
            ->where('students.is_active', 1)
            ->select('students.*')
            ->distinct()
            ->get();

        if ($students->isEmpty()) {
            $students = DB::table('students')
                ->join('student_class_assignments', 'students.id', '=', 'student_class_assignments.student_id')
                ->where('student_class_assignments.student_class_id', $feeStructure->student_class_id)
                ->where('students.is_active', 1)
                ->select('students.*')
                ->distinct()
                ->get();
        }

        if ($students->isEmpty()) {
            $students = DB::table('students')
                ->where('is_active', 1)
                ->limit(50)
                ->get();
        }

        foreach ($students as $studentData) {
            $student = Student::find($studentData->id);
            if ($student) {
                $this->createFeeAllocation($student, $feeStructure);
            }
        }
    }

    /**
     * Create a fee allocation for a student.
     */
    private function createFeeAllocation($student, $feeStructure)
    {
        $existingAllocation = StudentFeeAllocation::where([
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'academic_year_id' => $feeStructure->academic_year_id,
            'term_id' => $feeStructure->term_id,
        ])->first();

        if (!$existingAllocation) {
            StudentFeeAllocation::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'academic_year_id' => $feeStructure->academic_year_id,
                'term_id' => $feeStructure->term_id,
                'total_amount' => $feeStructure->total_amount,
                'paid_amount' => 0,
                'status' => 'pending',
                'due_date' => now()->addDays(30),
            ]);
        }
    }

    /**
     * Display the specified fee structure.
     */
    public function show($id)
    {
        $feeStructure = FeeStructure::with(['studentClass', 'academicYear', 'term', 'feeItems.feeCategory'])
            ->findOrFail($id);
        
        $allocationCount = $feeStructure->studentAllocations()->count();
        $paidCount = $feeStructure->studentAllocations()->where('status', 'paid')->count();
        $partialCount = $feeStructure->studentAllocations()->where('status', 'partial')->count();
        $totalPaid = $feeStructure->studentAllocations()->sum('paid_amount');
        
        return view('admin.fees.structures.show', compact(
            'feeStructure', 'allocationCount', 'paidCount', 'partialCount', 'totalPaid'
        ));
    }

    /**
     * Show the form for editing the specified fee structure.
     */
    public function edit($id)
    {
        $feeStructure = FeeStructure::with('feeItems')->findOrFail($id);
        $classes = StudentClass::where('is_active', true)->get();
        $academicYears = AcademicYear::where('is_active', true)->get();
        $terms = Term::all();
        $feeCategories = FeeCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('admin.fees.structures.edit', compact(
            'feeStructure', 'classes', 'academicYears', 'terms', 'feeCategories'
        ));
    }

    /**
     * Update the specified fee structure.
     */
    public function update(Request $request, $id)
    {
        $feeStructure = FeeStructure::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:one-time,termly,monthly',
            'is_active' => 'boolean',
            'fee_items' => 'required|array|min:1',
            'fee_items.*.fee_category_id' => 'nullable|exists:fee_categories,id',
            'fee_items.*.name' => 'required|string|max:255',
            'fee_items.*.amount' => 'required|numeric|min:0',
            'fee_items.*.is_optional' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $exists = FeeStructure::where([
            'student_class_id' => $request->student_class_id,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
        ])->where('id', '!=', $id)->exists();

        if ($exists) {
            return back()->with('error', 'A fee structure already exists for this class, academic year, and term.')
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            $feeStructure->update([
                'student_class_id' => $request->student_class_id,
                'academic_year_id' => $request->academic_year_id,
                'term_id' => $request->term_id,
                'name' => $request->name,
                'description' => $request->description,
                'total_amount' => $request->total_amount,
                'payment_frequency' => $request->payment_frequency,
                'is_active' => $request->has('is_active'),
            ]);

            $existingItemIds = $feeStructure->feeItems->pluck('id')->toArray();
            $updatedItemIds = [];
            
            foreach ($request->fee_items as $itemData) {
                if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                    $item = FeeItem::find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'fee_category_id' => $itemData['fee_category_id'] ?? null,
                            'name' => $itemData['name'],
                            'description' => $itemData['description'] ?? null,
                            'amount' => $itemData['amount'],
                            'is_optional' => isset($itemData['is_optional']),
                            'sort_order' => $itemData['sort_order'] ?? 0,
                        ]);
                        $updatedItemIds[] = $item->id;
                    }
                } else {
                    $item = FeeItem::create([
                        'fee_structure_id' => $feeStructure->id,
                        'fee_category_id' => $itemData['fee_category_id'] ?? null,
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'amount' => $itemData['amount'],
                        'is_optional' => isset($itemData['is_optional']),
                        'sort_order' => $itemData['sort_order'] ?? 0,
                    ]);
                    $updatedItemIds[] = $item->id;
                }
            }
            
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            FeeItem::destroy($itemsToDelete);

            DB::commit();

            return redirect()->route('fee-structures.index')
                ->with('success', 'Fee structure updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating fee structure: ' . $e->getMessage());
            return back()->with('error', 'Failed to update fee structure: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified fee structure.
     */
    public function destroy($id)
    {
        $feeStructure = FeeStructure::findOrFail($id);
        
        $hasPayments = $feeStructure->studentAllocations()
            ->whereHas('payments', function($query) {
                $query->where('status', 'completed');
            })
            ->exists();
        
        if ($hasPayments) {
            return back()->with('error', 'Cannot delete fee structure with associated payments.');
        }
        
        $feeStructure->studentAllocations()->delete();
        $feeStructure->feeItems()->delete();
        $feeStructure->delete();
        
        return redirect()->route('fee-structures.index')
            ->with('success', 'Fee structure deleted successfully.');
    }

    /**
     * Toggle fee structure status.
     */
    public function toggleStatus($id)
    {
        $feeStructure = FeeStructure::findOrFail($id);
        $feeStructure->is_active = !$feeStructure->is_active;
        $feeStructure->save();

        $status = $feeStructure->is_active ? 'activated' : 'deactivated';

        return redirect()->route('fee-structures.index')
            ->with('success', "Fee structure '{$feeStructure->name}' {$status} successfully.");
    }

    /**
     * Get fee structure details for AJAX.
     */
    public function getDetails($id)
    {
        $feeStructure = FeeStructure::with(['studentClass', 'academicYear', 'term', 'feeItems'])
            ->findOrFail($id);
        
        return response()->json($feeStructure);
    }

    /**
     * Get fee structures for bill sheet creation.
     * This is the method called from the bill sheet create page.
     */
    public function getFeeStructures(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Build the query
        $query = FeeStructure::where('is_active', true)
            ->where('student_class_id', $request->student_class_id)
            ->where('academic_year_id', $request->academic_year_id);

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Eager load fee items - SIMPLE STRING APPROACH
        $feeStructures = $query->with('feeItems')
            ->orderBy('name', 'asc')
            ->get();

        // Transform data for response
        $transformedData = [];

        foreach ($feeStructures as $structure) {
            // Add the structure itself as an item
            $transformedData[] = [
                'id' => $structure->id,
                'name' => $structure->name,
                'description' => $structure->description,
                'amount' => $structure->total_amount ?? 0,
                'is_optional' => false,
                'is_active' => $structure->is_active ?? true,
                'fee_category_id' => null,
                'fee_category' => null,
                'type' => 'structure',
                'fee_structure_id' => $structure->id,
                'fee_structure_item_id' => null,
            ];

            // Add fee items
            if ($structure->feeItems && $structure->feeItems->count() > 0) {
                foreach ($structure->feeItems as $item) {
                    $transformedData[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description ?? '',
                        'amount' => $item->amount ?? 0,
                        'is_optional' => $item->is_optional ?? false,
                        'is_active' => $item->is_active ?? true,
                        'fee_category_id' => $item->fee_category_id ?? null,
                        'fee_category' => null,
                        'type' => 'item',
                        'fee_structure_id' => $structure->id,
                        'fee_structure_item_id' => $item->id,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'count' => count($transformedData),
            'total_structures' => $feeStructures->count()
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching fee structures: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to load fee structures: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Get student fee allocation details.
     */
    public function getStudentAllocations($studentId)
    {
        $allocations = StudentFeeAllocation::with(['feeStructure', 'academicYear', 'term'])
            ->where('student_id', $studentId)
            ->get()
            ->map(function($allocation) {
                return [
                    'id' => $allocation->id,
                    'fee_structure_name' => $allocation->feeStructure->name ?? 'N/A',
                    'total_amount' => $allocation->total_amount,
                    'paid_amount' => $allocation->paid_amount,
                    'balance' => $allocation->balance,
                    'status' => $allocation->status,
                    'due_date' => $allocation->due_date ? $allocation->due_date->format('Y-m-d') : null,
                    'percentage' => $allocation->payment_percentage,
                ];
            });
        
        return response()->json($allocations);
    }
}