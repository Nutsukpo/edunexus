<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BillSheet;
use App\Models\BillSheetItem;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class BillSheetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | Display all student Bill Sheets.
    |
    | IMPORTANT:
    | We do NOT use bill_sheets.student_class_id because that column
    | does not exist in your current architecture.
    |
    | Class and student are obtained through:
    |
    | BillSheet
    |    -> StudentClassAssignment
    |        -> Student
    |        -> StudentClass
    |
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = BillSheet::query()
            ->with([
                'studentClassAssignment.student',
                'studentClassAssignment.studentClass',
                'studentClassAssignment.academicYear',
                'academicYear',
                'term',
                'items.feeCategory',
                'generatedBy',
                'approvedBy',
            ])
            ->orderByDesc('id');

        /*
        |--------------------------------------------------------------------------
        | FILTER BY CLASS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('class_id')) {
            $query->whereHas(
                'studentClassAssignment',
                function ($q) use ($request) {
                    $q->where(
                        'student_class_id',
                        $request->class_id
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        if ($request->filled('academic_year_id')) {
            $query->where(
                'academic_year_id',
                $request->academic_year_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY TERM
        |--------------------------------------------------------------------------
        */

        if ($request->filled('term_id')) {
            $query->where(
                'term_id',
                $request->term_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH STUDENT
        |--------------------------------------------------------------------------
        */

        if ($request->filled('student')) {
            $search = trim($request->student);

            $query->whereHas(
                'studentClassAssignment.student',
                function ($q) use ($search) {
                    $q->where(function ($studentQuery) use ($search) {
                        $studentQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
                }
            );
        }

        $billSheets = $query
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | FILTER DATA
        |--------------------------------------------------------------------------
        */

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $terms = Term::query()
            ->orderBy('id')
            ->get();

        return view(
            'bill-sheets.index',
            compact(
                'billSheets',
                'classes',
                'academicYears',
                'terms'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    |
    | Show batch Bill Sheet generation form.
    |
    */

    public function create()
    {
        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $terms = Term::query()
            ->orderBy('id')
            ->get();

        $feeCategories = FeeCategory::query()
            ->orderBy('name')
            ->get();

        return view(
            'bill-sheets.create',
            compact(
                'classes',
                'academicYears',
                'terms',
                'feeCategories'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | Generate one Bill Sheet for EVERY eligible StudentClassAssignment.
    |
    | Admin selects:
    |
    |   Class
    |   Academic Year
    |   Term
    |
    | EDUNEXUS finds:
    |
    |   active + current assignments
    |
    | and generates:
    |
    |   ONE BILL SHEET PER ASSIGNMENT
    |
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'generated_date' => [
                'required',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:generated_date',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.fee_category_id' => [
                'nullable',
                'integer',
                'exists:fee_categories,id',
            ],

            'items.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.is_optional' => [
                'nullable',
                'boolean',
            ],
        ]);

        $studentClass = StudentClass::findOrFail(
            $validated['student_class_id']
        );

        /*
        |--------------------------------------------------------------------------
        | FIND ELIGIBLE ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignments = StudentClassAssignment::query()
            ->with([
                'student',
                'studentClass',
                'academicYear',
            ])
            ->where(
                'student_class_id',
                $validated['student_class_id']
            )
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->where(
                'status',
                'active'
            )
            ->where(
                'is_current',
                true
            )
            ->get();

        if ($assignments->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_class_id' =>
                        'There are no active students currently assigned to the selected class for the selected academic year.'
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PREPARE BILL ITEMS
        |--------------------------------------------------------------------------
        */

        $preparedItems = [];
        $subtotal = 0;

        foreach ($validated['items'] as $item) {

            $amount = (float) $item['amount'];
            $quantity = (int) $item['quantity'];

            $totalAmount = $amount * $quantity;

            $subtotal += $totalAmount;

            $preparedItems[] = [
                'fee_category_id' =>
                    $item['fee_category_id'] ?? null,

                'name' =>
                    $item['name'],

                'amount' =>
                    $amount,

                'quantity' =>
                    $quantity,

                'total_amount' =>
                    $totalAmount,

                'is_optional' =>
                    !empty($item['is_optional']),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULATE TOTALS
        |--------------------------------------------------------------------------
        */

        $discountAmount =
            (float) ($validated['discount_amount'] ?? 0);

        $taxAmount =
            (float) ($validated['tax_amount'] ?? 0);

        $netAmount = max(
            0,
            $subtotal - $discountAmount + $taxAmount
        );

        /*
        |--------------------------------------------------------------------------
        | GENERATE
        |--------------------------------------------------------------------------
        */

        $createdCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {

            foreach ($assignments as $assignment) {

                /*
                |--------------------------------------------------------------------------
                | CHECK EXISTING BILL
                |--------------------------------------------------------------------------
                */

                $existingBill = BillSheet::query()
                    ->where(
                        'student_class_assignment_id',
                        $assignment->id
                    )
                    ->where(
                        'academic_year_id',
                        $validated['academic_year_id']
                    )
                    ->where(
                        'term_id',
                        $validated['term_id']
                    )
                    ->first();

                if ($existingBill) {
                    $skippedCount++;
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | STUDENT NAME
                |--------------------------------------------------------------------------
                */

                $student = $assignment->student;

                $studentName = $student
                    ? trim(
                        collect([
                            $student->first_name ?? null,
                            $student->middle_name ?? null,
                            $student->last_name ?? null,
                        ])
                            ->filter()
                            ->implode(' ')
                    )
                    : 'Student';

                /*
                |--------------------------------------------------------------------------
                | BILL NAME
                |--------------------------------------------------------------------------
                */

                $billName =
                    $validated['name']
                    . ' - '
                    . $studentName;

                /*
                |--------------------------------------------------------------------------
                | CREATE BILL
                |--------------------------------------------------------------------------
                */

                $billSheet = BillSheet::create([
                    'name' =>
                        $billName,

                    'student_class_assignment_id' =>
                        $assignment->id,

                    'academic_year_id' =>
                        $validated['academic_year_id'],

                    'term_id' =>
                        $validated['term_id'],

                    'generated_date' =>
                        $validated['generated_date'],

                    'due_date' =>
                        $validated['due_date'] ?? null,

                    'description' =>
                        $validated['description'] ?? null,

                    'total_amount' =>
                        $subtotal,

                    'discount_amount' =>
                        $discountAmount,

                    'tax_amount' =>
                        $taxAmount,

                    'net_amount' =>
                        $netAmount,

                    'status' =>
                        'draft',

                    'generated_by' =>
                        Auth::id(),

                    'is_active' =>
                        true,

                    'metadata' => [
                        'generated_as_batch' => true,

                        'batch_class_id' =>
                            $validated['student_class_id'],

                        'batch_academic_year_id' =>
                            $validated['academic_year_id'],

                        'batch_term_id' =>
                            $validated['term_id'],

                        'student_assignment_id' =>
                            $assignment->id,

                        'student_id' =>
                            $assignment->student_id,

                        'student_name' =>
                            $studentName,

                        'generated_at' =>
                            now()->toDateTimeString(),
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | CREATE ITEMS
                |--------------------------------------------------------------------------
                */

                foreach ($preparedItems as $item) {

                    BillSheetItem::create([
                        'bill_sheet_id' =>
                            $billSheet->id,

                        'fee_category_id' =>
                            $item['fee_category_id'],

                        'name' =>
                            $item['name'],

                        'amount' =>
                            $item['amount'],

                        'quantity' =>
                            $item['quantity'],

                        'total_amount' =>
                            $item['total_amount'],

                        'is_optional' =>
                            $item['is_optional'],
                    ]);
                }

                $createdCount++;
            }

            DB::commit();

            $message =
                "{$createdCount} Bill Sheet"
                . ($createdCount === 1 ? '' : 's')
                . " generated successfully for {$studentClass->name}.";

            if ($skippedCount > 0) {
                $message .=
                    " {$skippedCount} existing Bill Sheet"
                    . ($skippedCount === 1 ? '' : 's')
                    . " were skipped.";
            }

            return redirect()
                ->route('bill-sheets.index', [
                    'class_id' =>
                        $validated['student_class_id'],

                    'academic_year_id' =>
                        $validated['academic_year_id'],

                    'term_id' =>
                        $validated['term_id'],
                ])
                ->with(
                    'success',
                    $message
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Bill Sheet generation failed. No Bill Sheets were created.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(BillSheet $billSheet)
    {
        $billSheet->load([
            'studentClassAssignment.student',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'academicYear',
            'term',
            'items.feeCategory',
            'generatedBy',
            'approvedBy',
        ]);

        return view(
            'bill-sheets.show',
            compact('billSheet')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(BillSheet $billSheet)
    {
        if (!$billSheet->isDraft()) {
            return redirect()
                ->route(
                    'bill-sheets.show',
                    $billSheet
                )
                ->with(
                    'error',
                    'Only draft Bill Sheets can be edited.'
                );
        }

        $billSheet->load([
            'studentClassAssignment.student',
            'studentClassAssignment.studentClass',
            'academicYear',
            'term',
            'items',
        ]);

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $terms = Term::query()
            ->orderBy('id')
            ->get();

        $feeCategories = FeeCategory::query()
            ->orderBy('name')
            ->get();

        return view(
            'bill-sheets.edit',
            compact(
                'billSheet',
                'classes',
                'academicYears',
                'terms',
                'feeCategories'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        BillSheet $billSheet
    ) {
        if (!$billSheet->isDraft()) {
            return redirect()
                ->route(
                    'bill-sheets.show',
                    $billSheet
                )
                ->with(
                    'error',
                    'Only draft Bill Sheets can be edited.'
                );
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'student_class_assignment_id' => [
                'required',
                'integer',
                'exists:student_class_assignments,id',
            ],

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],

            'generated_date' => [
                'required',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:generated_date',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.fee_category_id' => [
                'nullable',
                'integer',
                'exists:fee_categories,id',
            ],

            'items.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.is_optional' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | VERIFY ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        $assignment = StudentClassAssignment::findOrFail(
            $validated['student_class_assignment_id']
        );

        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE
        |--------------------------------------------------------------------------
        */

        $duplicateExists = BillSheet::query()
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->where(
                'term_id',
                $validated['term_id']
            )
            ->where(
                'id',
                '!=',
                $billSheet->id
            )
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_class_assignment_id' =>
                        'This student already has a Bill Sheet for the selected academic year and term.'
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULATE TOTALS
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $subtotal +=
                ((float) $item['amount'])
                *
                ((int) $item['quantity']);
        }

        $discountAmount =
            (float) ($validated['discount_amount'] ?? 0);

        $taxAmount =
            (float) ($validated['tax_amount'] ?? 0);

        $netAmount = max(
            0,
            $subtotal - $discountAmount + $taxAmount
        );

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | UPDATE BILL
            |--------------------------------------------------------------------------
            */

            $billSheet->update([
                'name' =>
                    $validated['name'],

                'student_class_assignment_id' =>
                    $assignment->id,

                'academic_year_id' =>
                    $validated['academic_year_id'],

                'term_id' =>
                    $validated['term_id'],

                'generated_date' =>
                    $validated['generated_date'],

                'due_date' =>
                    $validated['due_date'] ?? null,

                'description' =>
                    $validated['description'] ?? null,

                'total_amount' =>
                    $subtotal,

                'discount_amount' =>
                    $discountAmount,

                'tax_amount' =>
                    $taxAmount,

                'net_amount' =>
                    $netAmount,
            ]);

            /*
            |--------------------------------------------------------------------------
            | REPLACE ITEMS
            |--------------------------------------------------------------------------
            */

            $billSheet->items()->delete();

            foreach ($validated['items'] as $item) {

                $amount =
                    (float) $item['amount'];

                $quantity =
                    (int) $item['quantity'];

                BillSheetItem::create([
                    'bill_sheet_id' =>
                        $billSheet->id,

                    'fee_category_id' =>
                        $item['fee_category_id'] ?? null,

                    'name' =>
                        $item['name'],

                    'amount' =>
                        $amount,

                    'quantity' =>
                        $quantity,

                    'total_amount' =>
                        $amount * $quantity,

                    'is_optional' =>
                        !empty($item['is_optional']),
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'bill-sheets.show',
                    $billSheet
                )
                ->with(
                    'success',
                    'Bill Sheet updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the Bill Sheet.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(BillSheet $billSheet)
    {
        if (!$billSheet->isDraft()) {
            return back()->with(
                'error',
                'Only draft Bill Sheets can be deleted.'
            );
        }

        if (
            FeePayment::where(
                'bill_sheet_id',
                $billSheet->id
            )->exists()
        ) {
            return back()->with(
                'error',
                'This Bill Sheet cannot be deleted because payments already exist against it.'
            );
        }

        try {

            $billSheet->items()->delete();

            $billSheet->delete();

            return redirect()
                ->route('bill-sheets.index')
                ->with(
                    'success',
                    'Bill Sheet deleted successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Unable to delete the Bill Sheet.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SUBMIT FOR APPROVAL
    |--------------------------------------------------------------------------
    */

    public function submitForApproval(BillSheet $billSheet)
    {
        if (!$billSheet->isDraft()) {
            return back()->with(
                'error',
                'Only draft Bill Sheets can be submitted for approval.'
            );
        }

        if ($billSheet->items()->count() === 0) {
            return back()->with(
                'error',
                'The Bill Sheet must contain at least one fee item.'
            );
        }

        $billSheet->update([
            'status' => 'pending',
        ]);

        return back()->with(
            'success',
            'Bill Sheet submitted for approval successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

    public function approve(BillSheet $billSheet)
    {
        if (!$billSheet->isPending()) {
            return back()->with(
                'error',
                'Only pending Bill Sheets can be approved.'
            );
        }

        $billSheet->update([
            'status' =>
                'approved',

            'approved_by' =>
                Auth::id(),

            'approved_at' =>
                now(),
        ]);

        return back()->with(
            'success',
            'Bill Sheet approved successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        BillSheet $billSheet
    ) {
        if (!$billSheet->isPending()) {
            return back()->with(
                'error',
                'Only pending Bill Sheets can be rejected.'
            );
        }

        $validated = $request->validate([
            'rejection_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $metadata = $billSheet->metadata ?? [];

        $metadata['rejection_reason'] =
            $validated['rejection_reason'] ?? null;

        $metadata['rejected_by'] =
            Auth::id();

        $metadata['rejected_at'] =
            now()->toDateTimeString();

        $billSheet->update([
            'status' =>
                'rejected',

            'metadata' =>
                $metadata,
        ]);

        return back()->with(
            'success',
            'Bill Sheet rejected successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PUBLISH
    |--------------------------------------------------------------------------
    */

    public function publish(BillSheet $billSheet)
    {
        if (!$billSheet->isApproved()) {
            return back()->with(
                'error',
                'Only approved Bill Sheets can be published.'
            );
        }

        $billSheet->update([
            'status' =>
                'published',

            'is_active' =>
                true,
        ]);

        return back()->with(
            'success',
            'Bill Sheet published successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE STATUS
    |--------------------------------------------------------------------------
    |
    | This route is useful for activating/deactivating a Bill Sheet.
    |
    */

    public function toggleStatus($id)
    {
        $billSheet = BillSheet::findOrFail($id);

        $billSheet->update([
            'is_active' =>
                !$billSheet->is_active,
        ]);

        return back()->with(
            'success',
            $billSheet->is_active
                ? 'Bill Sheet activated successfully.'
                : 'Bill Sheet deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REGENERATE
    |--------------------------------------------------------------------------
    |
    | This is the important class-wide correction mechanism.
    |
    | Example:
    |
    | Primary 5 has 30 students.
    |
    | Admin corrects ONE draft Bill Sheet.
    |
    | Clicking Regenerate:
    |
    |   1. Reads the corrected Bill Sheet.
    |   2. Finds its class through StudentClassAssignment.
    |   3. Finds every active/current assignment in that class.
    |   4. Updates their draft Bill Sheets.
    |   5. Creates missing Bill Sheets.
    |   6. NEVER modifies a Bill Sheet with payments.
    |   7. NEVER modifies approved/published bills.
    |
    |--------------------------------------------------------------------------
    */

    public function regenerate(
        Request $request,
        BillSheet $billSheet
    ) {
        $billSheet->load([
            'studentClassAssignment.student',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'term',
            'items',
        ]);

        if (!$billSheet->isDraft()) {
            return back()->with(
                'error',
                'Only a draft Bill Sheet can be used as the regeneration template.'
            );
        }

        $assignment =
            $billSheet->studentClassAssignment;

        if (!$assignment) {
            return back()->with(
                'error',
                'This Bill Sheet is not linked to a Student Class Assignment.'
            );
        }

        $classId =
            (int) $assignment->student_class_id;

        $academicYearId =
            (int) $billSheet->academic_year_id;

        $termId =
            (int) $billSheet->term_id;

        /*
        |--------------------------------------------------------------------------
        | TEMPLATE ITEMS
        |--------------------------------------------------------------------------
        */

        $templateItems =
            $billSheet->items
                ->map(function ($item) {

                    return [
                        'fee_category_id' =>
                            $item->fee_category_id,

                        'name' =>
                            $item->name,

                        'amount' =>
                            (float) $item->amount,

                        'quantity' =>
                            (int) $item->quantity,

                        'total_amount' =>
                            (float) $item->total_amount,

                        'is_optional' =>
                            (bool) $item->is_optional,
                    ];
                })
                ->values()
                ->all();

        if (empty($templateItems)) {
            return back()->with(
                'error',
                'The template Bill Sheet has no fee items.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ELIGIBLE ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignments = StudentClassAssignment::query()
            ->with([
                'student',
                'studentClass',
                'academicYear',
            ])
            ->where(
                'student_class_id',
                $classId
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'status',
                'active'
            )
            ->where(
                'is_current',
                true
            )
            ->get();

        if ($assignments->isEmpty()) {
            return back()->with(
                'error',
                'There are no active students assigned to this class for the selected academic year.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TOTALS
        |--------------------------------------------------------------------------
        */

        $subtotal = collect($templateItems)
            ->sum(function ($item) {
                return
                    (float) $item['amount']
                    *
                    (int) $item['quantity'];
            });

        $discountAmount =
            (float) $billSheet->discount_amount;

        $taxAmount =
            (float) $billSheet->tax_amount;

        $netAmount = max(
            0,
            $subtotal - $discountAmount + $taxAmount
        );

        $updatedCount = 0;
        $createdCount = 0;
        $skippedPaidCount = 0;
        $skippedStatusCount = 0;

        DB::beginTransaction();

        try {

            foreach ($assignments as $studentAssignment) {

                /*
                |--------------------------------------------------------------------------
                | FIND EXISTING STUDENT BILL
                |--------------------------------------------------------------------------
                */

                $studentBill = BillSheet::query()
                    ->where(
                        'student_class_assignment_id',
                        $studentAssignment->id
                    )
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'term_id',
                        $termId
                    )
                    ->first();

                $student =
                    $studentAssignment->student;

                $studentName = $student
                    ? trim(
                        collect([
                            $student->first_name ?? null,
                            $student->middle_name ?? null,
                            $student->last_name ?? null,
                        ])
                            ->filter()
                            ->implode(' ')
                    )
                    : 'Student';

                $className =
                    $studentAssignment
                        ->studentClass
                        ?->name
                    ?? 'Class';

                $termName =
                    $billSheet->term?->name
                    ?? 'Term';

                $billName =
                    $className
                    . ' - '
                    . $studentName
                    . ' - '
                    . $termName;

                /*
                |--------------------------------------------------------------------------
                | CREATE MISSING BILL
                |--------------------------------------------------------------------------
                */

                if (!$studentBill) {

                    $studentBill = BillSheet::create([
                        'name' =>
                            $billName,

                        'student_class_assignment_id' =>
                            $studentAssignment->id,

                        'academic_year_id' =>
                            $academicYearId,

                        'term_id' =>
                            $termId,

                        'generated_date' =>
                            $billSheet->generated_date,

                        'due_date' =>
                            $billSheet->due_date,

                        'description' =>
                            $billSheet->description,

                        'total_amount' =>
                            $subtotal,

                        'discount_amount' =>
                            $discountAmount,

                        'tax_amount' =>
                            $taxAmount,

                        'net_amount' =>
                            $netAmount,

                        'status' =>
                            'draft',

                        'generated_by' =>
                            Auth::id(),

                        'is_active' =>
                            true,

                        'metadata' => [
                            'generated_as_batch' =>
                                true,

                            'regenerated_from_bill_sheet_id' =>
                                $billSheet->id,

                            'batch_class_id' =>
                                $classId,

                            'batch_academic_year_id' =>
                                $academicYearId,

                            'batch_term_id' =>
                                $termId,

                            'student_assignment_id' =>
                                $studentAssignment->id,

                            'student_id' =>
                                $studentAssignment->student_id,

                            'student_name' =>
                                $studentName,

                            'regenerated_at' =>
                                now()->toDateTimeString(),
                        ],
                    ]);

                    foreach ($templateItems as $item) {

                        BillSheetItem::create([
                            'bill_sheet_id' =>
                                $studentBill->id,

                            'fee_category_id' =>
                                $item['fee_category_id'],

                            'name' =>
                                $item['name'],

                            'amount' =>
                                $item['amount'],

                            'quantity' =>
                                $item['quantity'],

                            'total_amount' =>
                                $item['amount']
                                * $item['quantity'],

                            'is_optional' =>
                                $item['is_optional'],
                        ]);
                    }

                    $createdCount++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DO NOT TOUCH BILLS WITH PAYMENTS
                |--------------------------------------------------------------------------
                */

                $hasPayments =
                    FeePayment::query()
                        ->where(
                            'bill_sheet_id',
                            $studentBill->id
                        )
                        ->exists();

                if ($hasPayments) {

                    $skippedPaidCount++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DO NOT TOUCH NON-DRAFT BILLS
                |--------------------------------------------------------------------------
                */

                if (!$studentBill->isDraft()) {

                    $skippedStatusCount++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE DRAFT BILL
                |--------------------------------------------------------------------------
                */

                $metadata =
                    $studentBill->metadata ?? [];

                $metadata[
                    'regenerated_from_bill_sheet_id'
                ] = $billSheet->id;

                $metadata[
                    'regenerated_at'
                ] = now()->toDateTimeString();

                $metadata[
                    'student_assignment_id'
                ] = $studentAssignment->id;

                $studentBill->update([
                    'name' =>
                        $billName,

                    'student_class_assignment_id' =>
                        $studentAssignment->id,

                    'academic_year_id' =>
                        $academicYearId,

                    'term_id' =>
                        $termId,

                    'generated_date' =>
                        $billSheet->generated_date,

                    'due_date' =>
                        $billSheet->due_date,

                    'description' =>
                        $billSheet->description,

                    'total_amount' =>
                        $subtotal,

                    'discount_amount' =>
                        $discountAmount,

                    'tax_amount' =>
                        $taxAmount,

                    'net_amount' =>
                        $netAmount,

                    'metadata' =>
                        $metadata,
                ]);

                /*
                |--------------------------------------------------------------------------
                | REPLACE ITEMS
                |--------------------------------------------------------------------------
                */

                $studentBill->items()->delete();

                foreach ($templateItems as $item) {

                    BillSheetItem::create([
                        'bill_sheet_id' =>
                            $studentBill->id,

                        'fee_category_id' =>
                            $item['fee_category_id'],

                        'name' =>
                            $item['name'],

                        'amount' =>
                            $item['amount'],

                        'quantity' =>
                            $item['quantity'],

                        'total_amount' =>
                            $item['amount']
                            * $item['quantity'],

                        'is_optional' =>
                            $item['is_optional'],
                    ]);
                }

                $updatedCount++;
            }

            DB::commit();

            $className =
                $assignment
                    ->studentClass
                    ?->name
                ?? 'selected class';

            $message =
                "Bill Sheets regenerated successfully for {$className}. "
                . "{$updatedCount} updated and "
                . "{$createdCount} created.";

            if ($skippedPaidCount > 0) {
                $message .=
                    " {$skippedPaidCount} skipped because payments already exist.";
            }

            if ($skippedStatusCount > 0) {
                $message .=
                    " {$skippedStatusCount} skipped because they are not draft Bill Sheets.";
            }

            return redirect()
                ->route(
                    'bill-sheets.index',
                    [
                        'class_id' =>
                            $classId,

                        'academic_year_id' =>
                            $academicYearId,

                        'term_id' =>
                            $termId,
                    ]
                )
                ->with(
                    'success',
                    $message
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Bill Sheet regeneration failed. No changes were committed.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GET STUDENT COUNT
    |--------------------------------------------------------------------------
    |
    | Used by create.blade.php.
    |
    */

    public function getStudentCount(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'exists:terms,id',
            ],
        ]);

        $assignmentQuery =
            StudentClassAssignment::query()
                ->where(
                    'student_class_id',
                    $validated['student_class_id']
                )
                ->where(
                    'academic_year_id',
                    $validated['academic_year_id']
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(
                    'is_current',
                    true
                );

        $studentCount =
            $assignmentQuery->count();

        $existingCount = 0;

        if (!empty($validated['term_id'])) {

            $assignmentIds =
                $assignmentQuery
                    ->pluck('id');

            $existingCount =
                BillSheet::query()
                    ->whereIn(
                        'student_class_assignment_id',
                        $assignmentIds
                    )
                    ->where(
                        'academic_year_id',
                        $validated['academic_year_id']
                    )
                    ->where(
                        'term_id',
                        $validated['term_id']
                    )
                    ->count();
        }

        return response()->json([
            'success' =>
                true,

            'count' =>
                $studentCount,

            'student_count' =>
                $studentCount,

            'existing_count' =>
                $existingCount,

            'new_count' =>
                max(
                    0,
                    $studentCount - $existingCount
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ASSIGNMENTS
    |--------------------------------------------------------------------------
    |
    | Returns active assignments for AJAX.
    |
    */

    public function assignments(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
        ]);

        $assignments =
            StudentClassAssignment::query()
                ->with('student')
                ->where(
                    'student_class_id',
                    $validated['student_class_id']
                )
                ->where(
                    'academic_year_id',
                    $validated['academic_year_id']
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(
                    'is_current',
                    true
                )
                ->get();

        return response()->json([
            'success' =>
                true,

            'count' =>
                $assignments->count(),

            'students' =>
                $assignments->map(
                    function ($assignment) {

                        $student =
                            $assignment->student;

                        $name = $student
                            ? trim(
                                collect([
                                    $student->first_name ?? null,
                                    $student->middle_name ?? null,
                                    $student->last_name ?? null,
                                ])
                                    ->filter()
                                    ->implode(' ')
                            )
                            : 'Student';

                        return [
                            'assignment_id' =>
                                $assignment->id,

                            'student_id' =>
                                $assignment->student_id,

                            'name' =>
                                $name,
                        ];
                    }
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf($id)
    {
        $billSheet =
            BillSheet::with([
                'studentClassAssignment.student',
                'studentClassAssignment.studentClass',
                'studentClassAssignment.academicYear',
                'academicYear',
                'term',
                'items.feeCategory',
                'generatedBy',
                'approvedBy',
            ])->findOrFail($id);

        $pdf =
            Pdf::loadView(
                'bill-sheets.pdf',
                compact('billSheet')
            );

        $pdf->setPaper(
            'A4',
            'portrait'
        );

        $studentName =
            $billSheet->student_name;

        $safeName =
            preg_replace(
                '/[^A-Za-z0-9\-]/',
                '-',
                $studentName
            );

        $filename =
            'Bill-Sheet-'
            . $safeName
            . '-'
            . $billSheet->id
            . '.pdf';

        return $pdf->stream(
            $filename
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print($id)
    {
        $billSheet =
            BillSheet::with([
                'studentClassAssignment.student',
                'studentClassAssignment.studentClass',
                'studentClassAssignment.academicYear',
                'academicYear',
                'term',
                'items.feeCategory',
                'generatedBy',
                'approvedBy',
            ])->findOrFail($id);

        return view(
            'bill-sheets.print',
            compact('billSheet')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    |
    | Export one Bill Sheet to CSV.
    |
    | This avoids requiring another package.
    |
    */

    public function export($id)
    {
        $billSheet =
            BillSheet::with([
                'studentClassAssignment.student',
                'studentClassAssignment.studentClass',
                'academicYear',
                'term',
                'items.feeCategory',
            ])->findOrFail($id);

        $filename =
            'bill-sheet-'
            . $billSheet->id
            . '.csv';

        $headers = [
            'Content-Type' =>
                'text/csv',

            'Content-Disposition' =>
                'attachment; filename="' . $filename . '"',
        ];

        $callback =
            function () use ($billSheet) {

                $file =
                    fopen(
                        'php://output',
                        'w'
                    );

                fputcsv(
                    $file,
                    [
                        'Bill Sheet ID',
                        'Student',
                        'Student ID',
                        'Class',
                        'Academic Year',
                        'Term',
                        'Fee Item',
                        'Fee Category',
                        'Amount',
                        'Quantity',
                        'Total',
                    ]
                );

                $student =
                    $billSheet
                        ->studentClassAssignment
                        ?->student;

                foreach (
                    $billSheet->items
                    as $item
                ) {

                    fputcsv(
                        $file,
                        [
                            $billSheet->id,

                            $billSheet->student_name,

                            $student?->student_id
                                ?? 'N/A',

                            $billSheet->class_name,

                            $billSheet->academic_year_name,

                            $billSheet->term_name,

                            $item->name,

                            $item->feeCategory?->name
                                ?? 'N/A',

                            number_format(
                                (float) $item->amount,
                                2,
                                '.',
                                ''
                            ),

                            $item->quantity,

                            number_format(
                                (float) $item->total_amount,
                                2,
                                '.',
                                ''
                            ),
                        ]
                    );
                }

                fputcsv(
                    $file,
                    []
                );

                fputcsv(
                    $file,
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        'Subtotal',
                        '',
                        '',
                        '',
                        '',
                        number_format(
                            (float) $billSheet->total_amount,
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $file,
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        'Discount',
                        '',
                        '',
                        '',
                        '',
                        number_format(
                            (float) $billSheet->discount_amount,
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $file,
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        'Tax',
                        '',
                        '',
                        '',
                        '',
                        number_format(
                            (float) $billSheet->tax_amount,
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $file,
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        'Net Amount',
                        '',
                        '',
                        '',
                        '',
                        number_format(
                            (float) $billSheet->net_amount,
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fclose($file);
            };

        return Response::stream(
            $callback,
            200,
            $headers
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DUPLICATE
    |--------------------------------------------------------------------------
    |
    | Because every Bill Sheet belongs to exactly one assignment, blindly
    | duplicating the same Bill Sheet for the same student would create
    | a duplicate billing record.
    |
    | Therefore this method finds the next eligible assignment in the
    | same class/year/term that does not already have a Bill Sheet and
    | copies the selected Bill Sheet to that student.
    |
    | If no such student exists, it tells the administrator to use
    | Regenerate instead.
    |
    |--------------------------------------------------------------------------
    */

    public function duplicate($id)
    {
        $source =
            BillSheet::with([
                'studentClassAssignment.studentClass',
                'academicYear',
                'term',
                'items',
            ])->findOrFail($id);

        if (!$source->studentClassAssignment) {
            return back()->with(
                'error',
                'The selected Bill Sheet is not linked to a Student Class Assignment.'
            );
        }

        $classId =
            $source
                ->studentClassAssignment
                ->student_class_id;

        $academicYearId =
            $source->academic_year_id;

        $termId =
            $source->term_id;

        /*
        |--------------------------------------------------------------------------
        | FIND ELIGIBLE ASSIGNMENTS WITHOUT BILLS
        |--------------------------------------------------------------------------
        */

        $assignment =
            StudentClassAssignment::query()
                ->with('student')
                ->where(
                    'student_class_id',
                    $classId
                )
                ->where(
                    'academic_year_id',
                    $academicYearId
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(
                    'is_current',
                    true
                )
                ->whereNotIn(
                    'id',
                    BillSheet::query()
                        ->where(
                            'academic_year_id',
                            $academicYearId
                        )
                        ->where(
                            'term_id',
                            $termId
                        )
                        ->pluck(
                            'student_class_assignment_id'
                        )
                )
                ->first();

        if (!$assignment) {
            return back()->with(
                'error',
                'There is no eligible student without a Bill Sheet in this class for the selected academic year and term. Use Regenerate if you need to correct the class bills.'
            );
        }

        DB::beginTransaction();

        try {

            $student =
                $assignment->student;

            $studentName =
                $student
                    ? trim(
                        collect([
                            $student->first_name ?? null,
                            $student->middle_name ?? null,
                            $student->last_name ?? null,
                        ])
                            ->filter()
                            ->implode(' ')
                    )
                    : 'Student';

            $className =
                $assignment
                    ->studentClass
                    ?->name
                ?? 'Class';

            $termName =
                $source->term?->name
                ?? 'Term';

            $newBill =
                BillSheet::create([
                    'name' =>
                        $className
                        . ' - '
                        . $studentName
                        . ' - '
                        . $termName,

                    'student_class_assignment_id' =>
                        $assignment->id,

                    'academic_year_id' =>
                        $source->academic_year_id,

                    'term_id' =>
                        $source->term_id,

                    'generated_date' =>
                        $source->generated_date,

                    'due_date' =>
                        $source->due_date,

                    'description' =>
                        $source->description,

                    'total_amount' =>
                        $source->total_amount,

                    'discount_amount' =>
                        $source->discount_amount,

                    'tax_amount' =>
                        $source->tax_amount,

                    'net_amount' =>
                        $source->net_amount,

                    'status' =>
                        'draft',

                    'generated_by' =>
                        Auth::id(),

                    'is_active' =>
                        true,

                    'metadata' => [
                        'duplicated_from_bill_sheet_id' =>
                            $source->id,

                        'student_assignment_id' =>
                            $assignment->id,

                        'student_id' =>
                            $assignment->student_id,

                        'duplicated_at' =>
                            now()->toDateTimeString(),
                    ],
                ]);

            foreach ($source->items as $item) {

                BillSheetItem::create([
                    'bill_sheet_id' =>
                        $newBill->id,

                    'fee_category_id' =>
                        $item->fee_category_id,

                    'name' =>
                        $item->name,

                    'amount' =>
                        $item->amount,

                    'quantity' =>
                        $item->quantity,

                    'total_amount' =>
                        $item->total_amount,

                    'is_optional' =>
                        $item->is_optional,
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'bill-sheets.show',
                    $newBill
                )
                ->with(
                    'success',
                    "Bill Sheet copied successfully for {$studentName}."
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Unable to duplicate the Bill Sheet.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS CHANGE
    |--------------------------------------------------------------------------
    |
    | Allows controlled status changes from the UI.
    |
    */

    public function changeStatus(
        Request $request,
        BillSheet $billSheet
    ) {
        $validated =
            $request->validate([
                'status' => [
                    'required',
                    Rule::in([
                        'draft',
                        'pending',
                        'approved',
                        'rejected',
                        'published',
                        'archived',
                    ]),
                ],
            ]);

        $newStatus =
            $validated['status'];

        /*
        |--------------------------------------------------------------------------
        | PREVENT UNAUTHORISED PAYMENT-BILL CHANGES
        |--------------------------------------------------------------------------
        */

        if (
            $newStatus === 'draft'
            &&
            FeePayment::where(
                'bill_sheet_id',
                $billSheet->id
            )->exists()
        ) {
            return back()->with(
                'error',
                'A Bill Sheet with payments cannot be returned to draft.'
            );
        }

        $update = [
            'status' =>
                $newStatus,
        ];

        if ($newStatus === 'approved') {

            $update['approved_by'] =
                Auth::id();

            $update['approved_at'] =
                now();
        }

        if ($newStatus === 'published') {

            $update['is_active'] =
                true;
        }

        $billSheet->update(
            $update
        );

        return back()->with(
            'success',
            'Bill Sheet status updated successfully.'
        );
    }
}