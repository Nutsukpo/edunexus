<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BillSheet;
use App\Models\BillSheetItem;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\StudentFeeAccount;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class FeePaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = FeePayment::query()
            ->with([
                'student',
                'studentFeeAccount',
                'studentClassAssignment.studentClass',
                'studentClassAssignment.academicYear',
                'billSheet',
                'billSheetItem',
            ])
            ->latest('id');

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('studentClassAssignment', function ($q) use ($request) {
                $q->where('student_class_id', $request->class_id);
            });
        }

        if ($request->filled('academic_year_id')) {
            $query->whereHas('studentClassAssignment', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('term_id')) {
            $query->whereHas('billSheet', function ($q) use ($request) {
                $q->where('term_id', $request->term_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery
                            ->where('student_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        $students = Student::query()
            ->orderBy('first_name')
            ->get();

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->withCount([
                'studentClassAssignments as students_count' => function ($q) {
                    $q->where('is_current', true)
                        ->where('status', 'active');
                },
            ])
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('name')
            ->get();

        $terms = Term::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $paymentMethods = $this->paymentMethods();

        $statuses = [
            'pending'   => 'Pending',
            'completed' => 'Completed',
            'failed'    => 'Failed',
            'refunded'  => 'Refunded',
            'reversed'  => 'Reversed',
        ];

        return view('fee-payments.index', compact(
            'payments',
            'students',
            'classes',
            'academicYears',
            'terms',
            'paymentMethods',
            'statuses'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $academicYears = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('name')
            ->get();

        $classes = StudentClass::query()
            ->where('is_active', true)
            ->withCount([
                'studentClassAssignments as students_count' => function ($q) {
                    $q->where('is_current', true)
                        ->where('status', 'active');
                },
            ])
            ->orderBy('name')
            ->get();

        // Terms are loaded dynamically after the academic year is selected.
        $terms = collect();

        $paymentMethods = $this->paymentMethods();

        $paymentTypes = [
            'full'        => 'Full Payment',
            'partial'     => 'Partial Payment',
            'installment' => 'Installment',
            'advance'     => 'Advance Payment',
        ];

        $recordedBy = [
            'cashier'    => 'Cashier',
            'accountant' => 'Accountant',
            'admin'      => 'Administrator',
        ];

        return view('fee-payments.create', compact(
            'academicYears',
            'classes',
            'terms',
            'paymentMethods',
            'paymentTypes',
            'recordedBy'
        ));
    }

            /*
|--------------------------------------------------------------------------
| GET TERMS BY ACADEMIC YEAR
|--------------------------------------------------------------------------
|
| Returns ONLY active terms belonging to the selected academic year.
| This prevents terms from another academic year appearing in the
| Fee Payment form.
|
*/

    public function getTerms(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $terms = Term::query()
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('is_active', true)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get([
                'id',
                'academic_year_id',
                'name',
                'start_date',
                'end_date',
                'is_active',
            ]);

        return response()->json([
            'success' => true,
            'terms' => $terms,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'student_class_assignment_id' => 'required|exists:student_class_assignments,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'bill_sheet_id' => 'required|exists:bill_sheets,id',
            'bill_sheet_item_id' => 'nullable|exists:bill_sheet_items,id',
            'amount' => 'required|numeric|min:0.01',
            'penalty_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,card,cheque,online,other',
            'payment_type' => 'required|in:full,partial,installment,advance',
            'recorded_by' => 'nullable|in:cashier,accountant,admin',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $assignment = $this->resolveAssignment(
                $request->student_id,
                $request->student_class_assignment_id,
                $request->academic_year_id,
                $request->term_id
            );

            if (!$assignment) {
                throw new \RuntimeException(
                    'The selected student class assignment is invalid for this student and academic year.'
                );
            }

            $student = $assignment->student;

            $billSheet = $this->resolveBillSheet(
                $request->bill_sheet_id,
                $assignment,
                $request->term_id
            );

            if (!$billSheet) {
                throw new \RuntimeException(
                    'The selected Bill Sheet does not belong to this student, assignment, academic year and term, or is not available for payment.'
                );
            }

            // HARD TERM BOUNDARY:
            // The selected term must belong to the selected academic year.
            $this->validateTermForAcademicYear(
                (int) $request->term_id,
                (int) $request->academic_year_id
            );

            // FINANCIAL CLEARANCE RULE:
            // A student must clear the immediately preceding term
            // before a completed payment can be recorded for this term.
            $this->enforcePreviousTermCleared(
                $assignment,
                (int) $request->term_id,
                (int) $request->academic_year_id
            );

            $billTotal = $this->billTotal($billSheet);

            $alreadyPaid = $this->billPaid(
                $assignment->id,
                $billSheet->id
            );

            $billBalance = max(0, round($billTotal - $alreadyPaid, 2));

            if ($billBalance <= 0) {
                throw new \RuntimeException(
                    'This Bill Sheet has already been fully paid.'
                );
            }

            $billSheetItem = null;

            if ($request->filled('bill_sheet_item_id')) {
                $billSheetItem = $billSheet->items()
                    ->whereKey($request->bill_sheet_item_id)
                    ->first();

                if (!$billSheetItem) {
                    throw new \RuntimeException(
                        'The selected Bill Sheet item does not belong to this Bill Sheet.'
                    );
                }
            }

            $amount = round((float) $request->amount, 2);
            $penalty = round((float) ($request->penalty_amount ?? 0), 2);
            $discount = round((float) ($request->discount_amount ?? 0), 2);
            $netAmount = round($amount + $penalty - $discount, 2);

            if ($discount > ($amount + $penalty)) {
                throw new \RuntimeException(
                    'The discount cannot be greater than the payment plus penalty.'
                );
            }

            if ($netAmount > $billBalance) {
                throw new \RuntimeException(
                    'The net payment amount cannot exceed the outstanding Bill Sheet balance of GHS '
                    . number_format($billBalance, 2) . '.'
                );
            }

            if ($netAmount <= 0) {
                throw new \RuntimeException(
                    'The net payment amount must be greater than zero.'
                );
            }

            $feeAccount = $this->getOrCreateFeeAccount($assignment);

            /*
             * IMPORTANT:
             * receipt_number is NOT generated after INSERT.
             * MySQL requires it before INSERT, so it is explicitly supplied here.
             */
            $receiptNumber = $this->generateReceiptNumber();

            $payment = FeePayment::create([
                'student_id' => $student->id,
                'student_class_assignment_id' => $assignment->id,
                'student_fee_account_id' => $feeAccount->id,
                'bill_sheet_id' => $billSheet->id,
                'bill_sheet_item_id' => $billSheetItem?->id,
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'penalty_amount' => $penalty,
                'discount_amount' => $discount,
                'net_amount' => $netAmount,
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'recorded_by' => $request->recorded_by ?? 'cashier',
                'payment_date' => $request->payment_date,
                'transaction_id' => $request->transaction_id,
                'bank_name' => $request->bank_name,
                'cheque_number' => $request->cheque_number,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'status' => 'completed',
                'metadata' => [
                    'student_id' => $student->id,
                    'student_class_assignment_id' => $assignment->id,
                    'student_class_id' => $assignment->student_class_id,
                    'academic_year_id' => $assignment->academic_year_id,
                    'term_id' => $request->term_id,
                    'bill_sheet_id' => $billSheet->id,
                    'bill_sheet_item_id' => $billSheetItem?->id,
                    'bill_total' => $billTotal,
                    'bill_paid_before_payment' => $alreadyPaid,
                    'bill_balance_before_payment' => $billBalance,
                    'bill_balance_after_payment' => max(0, round($billBalance - $netAmount, 2)),
                ],
            ]);

            $this->synchronizeFeeAccount($feeAccount, $assignment);

            DB::commit();

            return redirect()
                ->route('fee-payments.show', $payment->id)
                ->with(
                    'success',
                    'Payment recorded successfully. Receipt #: ' . $payment->receipt_number
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Fee payment creation failed', [
                'message' => $e->getMessage(),
                'student_id' => $request->student_id,
                'assignment_id' => $request->student_class_assignment_id,
                'bill_sheet_id' => $request->bill_sheet_id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $payment = FeePayment::with([
            'student',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'billSheet',
            'billSheet.items',
            'billSheetItem',
        ])->findOrFail($id);

        return view('fee-payments.show', compact('payment'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $payment = FeePayment::with([
            'student',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'billSheet',
            'billSheetItem',
        ])->findOrFail($id);

        $academicYears = AcademicYear::where('is_active', true)
            ->orderByDesc('name')
            ->get();

        $classes = StudentClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $terms = Term::where('is_active', true)
            ->orderBy('name')
            ->get();

        $paymentMethods = $this->paymentMethods();

        $paymentTypes = [
            'full'        => 'Full Payment',
            'partial'     => 'Partial Payment',
            'installment' => 'Installment',
            'advance'     => 'Advance Payment',
        ];

        return view('fee-payments.edit', compact(
            'payment',
            'academicYears',
            'classes',
            'terms',
            'paymentMethods',
            'paymentTypes'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $payment = FeePayment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'student_class_assignment_id' => 'required|exists:student_class_assignments,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'bill_sheet_id' => 'required|exists:bill_sheets,id',
            'bill_sheet_item_id' => 'nullable|exists:bill_sheet_items,id',
            'amount' => 'required|numeric|min:0.01',
            'penalty_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,card,cheque,online,other',
            'payment_type' => 'required|in:full,partial,installment,advance',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,refunded,reversed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $oldAssignment = $payment->studentClassAssignment;
            $oldAccount = $payment->studentFeeAccount;

            $assignment = $this->resolveAssignment(
                $request->student_id,
                $request->student_class_assignment_id,
                $request->academic_year_id,
                $request->term_id
            );

            if (!$assignment) {
                throw new \RuntimeException(
                    'The selected student class assignment is invalid.'
                );
            }

            $billSheet = $this->resolveBillSheet(
                $request->bill_sheet_id,
                $assignment,
                $request->term_id
            );

            if (!$billSheet) {
                throw new \RuntimeException(
                    'The selected Bill Sheet is invalid or is not available for payment.'
                );
            }

            // HARD TERM BOUNDARY.
            $this->validateTermForAcademicYear(
                (int) $request->term_id,
                (int) $request->academic_year_id
            );

            // Only a payment being recorded/completed must pass
            // the previous-term clearance rule.
            if ($request->status === 'completed') {
                $this->enforcePreviousTermCleared(
                    $assignment,
                    (int) $request->term_id,
                    (int) $request->academic_year_id
                );
            }

            $billSheetItem = null;

            if ($request->filled('bill_sheet_item_id')) {
                $billSheetItem = $billSheet->items()
                    ->whereKey($request->bill_sheet_item_id)
                    ->first();

                if (!$billSheetItem) {
                    throw new \RuntimeException(
                        'The selected Bill Sheet item does not belong to this Bill Sheet.'
                    );
                }
            }

            $billTotal = $this->billTotal($billSheet);

            $otherPaid = FeePayment::query()
                ->where('student_class_assignment_id', $assignment->id)
                ->where('bill_sheet_id', $billSheet->id)
                ->where('status', 'completed')
                ->whereKeyNot($payment->id)
                ->sum('net_amount');

            $outstanding = max(
                0,
                round($billTotal - (float) $otherPaid, 2)
            );

            $amount = round((float) $request->amount, 2);
            $penalty = round((float) ($request->penalty_amount ?? 0), 2);
            $discount = round((float) ($request->discount_amount ?? 0), 2);
            $netAmount = round($amount + $penalty - $discount, 2);

            if ($discount > ($amount + $penalty)) {
                throw new \RuntimeException(
                    'The discount cannot be greater than the payment plus penalty.'
                );
            }

            if ($request->status === 'completed' && $netAmount > $outstanding) {
                throw new \RuntimeException(
                    'The net payment amount cannot exceed the outstanding Bill Sheet balance of GHS '
                    . number_format($outstanding, 2) . '.'
                );
            }

            if ($request->status === 'completed' && $netAmount <= 0) {
                throw new \RuntimeException(
                    'The net payment amount must be greater than zero.'
                );
            }

            $newAccount = $this->getOrCreateFeeAccount($assignment);

            $payment->update([
                'student_id' => $assignment->student_id,
                'student_class_assignment_id' => $assignment->id,
                'student_fee_account_id' => $newAccount->id,
                'bill_sheet_id' => $billSheet->id,
                'bill_sheet_item_id' => $billSheetItem?->id,
                'amount' => $amount,
                'penalty_amount' => $penalty,
                'discount_amount' => $discount,
                'net_amount' => $netAmount,
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'payment_date' => $request->payment_date,
                'transaction_id' => $request->transaction_id,
                'bank_name' => $request->bank_name,
                'cheque_number' => $request->cheque_number,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'status' => $request->status,
                'metadata' => array_merge(
                    is_array($payment->metadata) ? $payment->metadata : [],
                    [
                        'updated_bill_sheet_id' => $billSheet->id,
                        'updated_at' => now()->toDateTimeString(),
                    ]
                ),
            ]);

            if ($oldAccount && $oldAssignment) {
                $this->synchronizeFeeAccount($oldAccount, $oldAssignment);
            }

            $this->synchronizeFeeAccount($newAccount, $assignment);

            DB::commit();

            return redirect()
                ->route('fee-payments.show', $payment->id)
                ->with('success', 'Payment updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Fee payment update failed', [
                'payment_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded,reversed',
        ]);

        DB::beginTransaction();

        try {
            $payment = FeePayment::with([
                'studentFeeAccount',
                'studentClassAssignment',
            ])->findOrFail($id);

            $oldStatus = $payment->status;
            $newStatus = $request->status;

            /*
             * If a payment is being changed to COMPLETED, re-check
             * the previous-term clearance rule on the server.
             *
             * This prevents someone from creating a pending payment
             * first and later bypassing the clearance rule by simply
             * changing its status to completed.
             */
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $payment->load([
                    'studentClassAssignment',
                    'billSheet',
                ]);

                if (
                    $payment->studentClassAssignment
                    && $payment->billSheet
                ) {
                    $this->enforcePreviousTermCleared(
                        $payment->studentClassAssignment,
                        (int) $payment->billSheet->term_id,
                        (int) $payment->billSheet->academic_year_id
                    );
                }
            }

            $payment->update([
                'status' => $newStatus,
            ]);

            $assignment = $payment->studentClassAssignment;
            $account = $payment->studentFeeAccount;

            if ($assignment && $account) {
                $this->synchronizeFeeAccount($account, $assignment);
            }

            DB::commit();

            return back()->with(
                'success',
                "Payment status changed from {$oldStatus} to {$newStatus}."
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Fee payment status update failed', [
                'payment_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $payment = FeePayment::with([
                'studentFeeAccount',
                'studentClassAssignment',
            ])->findOrFail($id);

            $feeAccount = $payment->studentFeeAccount;
            $assignment = $payment->studentClassAssignment;

            $payment->delete();

            if ($feeAccount && $assignment) {
                $this->synchronizeFeeAccount($feeAccount, $assignment);
            }

            DB::commit();

            return redirect()
                ->route('fee-payments.index')
                ->with('success', 'Payment deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Fee payment deletion failed', [
                'payment_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Unable to delete payment: ' . $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENTS BY CLASS
    |--------------------------------------------------------------------------
    */

    public function getStudentsByClass(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        if (!empty($validated['term_id'])) {
            $this->validateTermForAcademicYear(
                (int) $validated['term_id'],
                (int) $validated['academic_year_id']
            );
        }

        /*
         * A student can have multiple assignments in one academic year,
         * one for each term. The payment form must therefore resolve the
         * assignment for the SELECTED TERM, not simply the student's
         * current assignment.
         */
        $assignments = StudentClassAssignment::query()
            ->with(['student', 'studentClass', 'academicYear', 'term'])
            ->where('student_class_id', $validated['class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('term_id', $validated['term_id'])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['active']);
            })
            ->orderBy('id')
            ->get();

        $students = $assignments
            ->filter(fn ($assignment) => $assignment->student !== null)
            ->unique('student_id')
            ->map(function ($assignment) {
                $student = $assignment->student;

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'full_name' => $this->studentName($student),
                    'assignment_id' => $assignment->id,
                    'student_class_id' => $assignment->student_class_id,
                    'academic_year_id' => $assignment->academic_year_id,
                    'term_id' => $assignment->term_id,
                    'class_name' => $assignment->studentClass?->name,
                    'academic_year' => $assignment->academicYear?->name,
                    'term_name' => $assignment->term?->name,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'students' => $students,
            'count' => $students->count(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT DETAILS / BILL SHEETS
    |--------------------------------------------------------------------------
    |
    | This endpoint is intended for the payment create form.
    | Once the cashier selects a student, it returns the student's
    | assignment, Bill Sheet total, paid amount, outstanding balance,
    | and Bill Sheet items.
    |--------------------------------------------------------------------------
    */

    public function getStudentDetails(Request $request, $studentId)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'student_class_assignment_id' => 'required|exists:student_class_assignments,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $this->validateTermForAcademicYear(
            (int) $request->term_id,
            (int) $request->academic_year_id
        );

        try {
            $assignment = $this->resolveAssignment(
                $studentId,
                $request->student_class_assignment_id,
                $request->academic_year_id,
                $request->term_id
            );

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected student class assignment is invalid.',
                ], 422);
            }

            $student = $assignment->student;

            $billSheets = BillSheet::query()
                ->with('items')
                ->where('student_class_assignment_id', $assignment->id)
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('term_id', $request->term_id)
                ->where('is_active', true)
                ->whereIn('status', ['approved', 'published'])
                ->orderByDesc('id')
                ->get();

            $billData = $billSheets->map(function ($billSheet) use ($assignment) {
                $billTotal = $this->billTotal($billSheet);

                $paid = $this->billPaid(
                    $assignment->id,
                    $billSheet->id
                );

                $balance = max(
                    0,
                    round($billTotal - $paid, 2)
                );

                return [
                    'id' => $billSheet->id,
                    'name' => $billSheet->name,
                    'total_amount' => $billTotal,
                    'paid' => $paid,
                    'balance' => $balance,
                    'formatted_total' => $this->money($billTotal),
                    'formatted_paid' => $this->money($paid),
                    'formatted_balance' => $this->money($balance),
                    'generated_date' => $billSheet->generated_date?->format('Y-m-d'),
                    'due_date' => $billSheet->due_date?->format('Y-m-d'),
                    'status' => $balance <= 0
                        ? 'paid'
                        : ($paid > 0 ? 'partial' : 'pending'),
                    'bill_sheet_status' => $billSheet->status,
                    'items' => $billSheet->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'amount' => (float) $item->amount,
                            'quantity' => (int) ($item->quantity ?? 1),
                            'total_amount' => (float) $item->total_amount,
                            'is_optional' => (bool) ($item->is_optional ?? false),
                            'is_mandatory' => (bool) ($item->is_mandatory ?? true),
                        ];
                    })->values(),
                ];
            })->values();

            $totalFees = (float) $billData->sum('total_amount');
            $totalPaid = (float) $billData->sum('paid');
            $totalBalance = max(
                0,
                round($totalFees - $totalPaid, 2)
            );

            $feeAccount = $this->getOrCreateFeeAccount($assignment);
            $this->synchronizeFeeAccount($feeAccount, $assignment);

            return response()->json([
                'success' => true,

                'student' => [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'full_name' => $this->studentName($student),
                ],

                'assignment' => [
                    'id' => $assignment->id,
                    'student_class_id' => $assignment->student_class_id,
                    'class_name' => $assignment->studentClass?->name,
                    'academic_year' => $assignment->academicYear?->name,
                    'academic_year_id' => $assignment->academic_year_id,
                    'term_id' => $assignment->term_id,
                    'term_name' => $assignment->term?->name,
                ],

                'bill_sheets' => $billData,

                'selected_bill_sheet_id' => $billData
                    ->where('balance', '>', 0)
                    ->first()['id'] ?? null,

                'total_fees' => $totalFees,
                'total_paid' => $totalPaid,
                'balance' => $totalBalance,

                /*
                 * The create Blade can use this value to automatically
                 * display the outstanding Bill Sheet amount.
                 */
                'suggested_amount' => $totalBalance,

                'formatted_total_fees' => $this->money($totalFees),
                'formatted_total_paid' => $this->money($totalPaid),
                'formatted_balance' => $this->money($totalBalance),

                'has_bill_sheet' => $billData->isNotEmpty(),
                'has_outstanding_bill' => $billData->contains(
                    fn ($bill) => $bill['balance'] > 0
                ),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error loading student fee details', [
                'student_id' => $studentId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load student Bill Sheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT BILL SHEETS
    |--------------------------------------------------------------------------
    */

    public function getStudentBillSheets(Request $request)
{
    $request->validate([
        'student_id' => [
            'required',
            'exists:students,id',
        ],

        'student_class_assignment_id' => [
            'required',
            'exists:student_class_assignments,id',
        ],

        'academic_year_id' => [
            'required',
            'exists:academic_years,id',
        ],

        'term_id' => [
            'required',
            'exists:terms,id',
        ],
    ]);

    $this->validateTermForAcademicYear(
        (int) $request->term_id,
        (int) $request->academic_year_id
    );


    $assignment = $this->resolveAssignment(
        $request->student_id,
        $request->student_class_assignment_id,
        $request->academic_year_id,
        $request->term_id
    );


    if (!$assignment) {

        return response()->json([
            'success' => false,
            'message' => 'Invalid student class assignment.',
        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | FIND APPROVED / PUBLISHED BILL SHEETS
    |--------------------------------------------------------------------------
    */

    $bills = BillSheet::query()
        ->with('items')
        ->where(
            'student_class_assignment_id',
            $assignment->id
        )
        ->where(
            'academic_year_id',
            $assignment->academic_year_id
        )
        ->where(
            'term_id',
            $request->term_id
        )
        ->where(
            'is_active',
            true
        )
        ->whereIn(
            'status',
            [
                'approved',
                'published',
            ]
        )
        ->orderByDesc('id')
        ->get()
        ->map(function ($bill) use ($assignment) {

            /*
            |--------------------------------------------------------------------------
            | BILL TOTAL
            |--------------------------------------------------------------------------
            */

            $total =
                $this->billTotal($bill);


            /*
            |--------------------------------------------------------------------------
            | AMOUNT PAID
            |--------------------------------------------------------------------------
            */

            $paid =
                $this->billPaid(
                    $assignment->id,
                    $bill->id
                );


            /*
            |--------------------------------------------------------------------------
            | OUTSTANDING BALANCE
            |--------------------------------------------------------------------------
            */

            $balance =
                max(
                    0,
                    round(
                        $total - $paid,
                        2
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | PAYMENT STATUS
            |--------------------------------------------------------------------------
            */

            $paymentStatus =
                $balance <= 0
                    ? 'paid'
                    : (
                        $paid > 0
                            ? 'partial'
                            : 'pending'
                    );


            return [

                /*
                |--------------------------------------------------------------------------
                | BILL IDENTITY
                |--------------------------------------------------------------------------
                */

                'id' =>
                    $bill->id,

                'name' =>
                    $bill->name,


                /*
                |--------------------------------------------------------------------------
                | FINANCIAL VALUES
                |--------------------------------------------------------------------------
                */

                'total_amount' =>
                    $total,

                'paid' =>
                    $paid,

                'balance' =>
                    $balance,


                /*
                |--------------------------------------------------------------------------
                | FORMATTED VALUES
                |--------------------------------------------------------------------------
                */

                'formatted_total' =>
                    $this->money($total),

                'formatted_paid' =>
                    $this->money($paid),

                'formatted_balance' =>
                    $this->money($balance),


                /*
                |--------------------------------------------------------------------------
                | DATES
                |--------------------------------------------------------------------------
                */

                'generated_date' =>
                    $bill->generated_date
                        ? $bill->generated_date->format('Y-m-d')
                        : null,

                'due_date' =>
                    $bill->due_date
                        ? $bill->due_date->format('Y-m-d')
                        : null,


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT STATUS SEPARATION
                |--------------------------------------------------------------------------
                */

                'bill_sheet_status' =>
                    $bill->status,

                'payment_status' =>
                    $paymentStatus,


                /*
                |--------------------------------------------------------------------------
                | BILL ITEMS
                |--------------------------------------------------------------------------
                */

                'items' =>
                    $bill->items
                        ->map(function ($item) {

                            return [

                                'id' =>
                                    $item->id,

                                'name' =>
                                    $item->name,

                                'amount' =>
                                    (float) $item->amount,

                                'quantity' =>
                                    (int) (
                                        $item->quantity ?? 1
                                    ),

                                'total_amount' =>
                                    (float) $item->total_amount,

                                'is_optional' =>
                                    (bool) (
                                        $item->is_optional ?? false
                                    ),

                                'is_mandatory' =>
                                    (bool) (
                                        $item->is_mandatory ?? true
                                    ),

                            ];

                        })
                        ->values(),

            ];

        })
        ->values();


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    /*
     * Keep Draft/Pending Bill Sheets non-payable, but return their status
     * so the create form can explain why no approved sheet is available.
     */
    $nonPayableBillSheets = BillSheet::query()
        ->where('student_class_assignment_id', $assignment->id)
        ->where('academic_year_id', $assignment->academic_year_id)
        ->where('term_id', $request->term_id)
        ->where('is_active', true)
        ->whereNotIn('status', ['approved', 'published'])
        ->orderByDesc('id')
        ->get(['id', 'name', 'status']);

    return response()->json([

        'success' =>
            true,

        'bill_sheets' =>
            $bills,

        'count' =>
            $bills->count(),

        'non_payable_bill_sheets' =>
            $nonPayableBillSheets->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'status' => $bill->status,
                ];
            })->values(),

        'total_amount' =>
            round(
                (float) $bills->sum('total_amount'),
                2
            ),

        'total_paid' =>
            round(
                (float) $bills->sum('paid'),
                2
            ),

        'total_balance' =>
            round(
                (float) $bills->sum('balance'),
                2
            ),

        'assignment_id' =>
            $assignment->id,

    ]);
}

    /*
    |--------------------------------------------------------------------------
    | BILL SHEET ITEMS
    |--------------------------------------------------------------------------
    */

    public function getBillSheetItems(Request $request)
    {
        $request->validate([
            'bill_sheet_id' => 'required|exists:bill_sheets,id',
        ]);

        $billSheet = BillSheet::query()
            ->with('items')
            ->whereKey($request->bill_sheet_id)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->first();

        if (!$billSheet) {
            return response()->json([
                'success' => false,
                'message' => 'Bill Sheet is not available for payment.',
            ], 404);
        }

        $total = $this->billTotal($billSheet);

        return response()->json([
            'success' => true,

            'bill_sheet' => [
                'id' => $billSheet->id,
                'name' => $billSheet->name,
                'total_amount' => $total,
                'formatted_total' => $this->money($total),
                'status' => $billSheet->status,
            ],

            'items' => $billSheet->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'amount' => (float) $item->amount,
                    'quantity' => (int) ($item->quantity ?? 1),
                    'total_amount' => (float) $item->total_amount,
                    'is_optional' => (bool) ($item->is_optional ?? false),
                    'is_mandatory' => (bool) ($item->is_mandatory ?? true),
                ];
            })->values(),
        ]);
    }
    
    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | TERM / ACADEMIC YEAR VALIDATION
    |--------------------------------------------------------------------------
    |
    | A Term is never allowed to cross academic-year boundaries.
    |
    | Example:
    |   2026/2027 First Term belongs to 2026/2027.
    |
    | It must never be accepted together with a 2025/2026 academic year.
    |
    |--------------------------------------------------------------------------
    */

    private function validateTermForAcademicYear(
        int $termId,
        int $academicYearId
    ): Term {
        $term = Term::query()
            ->whereKey($termId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if (!$term) {
            throw new \RuntimeException(
                'The selected term does not belong to the selected academic year.'
            );
        }

        return $term;
    }


    /*
    |--------------------------------------------------------------------------
    | PREVIOUS TERM CLEARANCE
    |--------------------------------------------------------------------------
    |
    | BUSINESS RULE:
    |
    | First Term:
    |   No previous term exists, therefore payment is allowed.
    |
    | Second Term:
    |   First Term must have a zero balance.
    |
    | Third Term:
    |   Second Term must have a zero balance.
    |
    | IMPORTANT:
    |   The previous term is searched ONLY inside the same academic year.
    |
    |   We calculate the previous term using start_date, not global
    |   database ID ordering. This prevents a term from another academic
    |   year from being accidentally selected.
    |
    |--------------------------------------------------------------------------
    */

    private function enforcePreviousTermCleared(
        StudentClassAssignment $assignment,
        int $termId,
        int $academicYearId
    ): void {
        $currentTerm = $this->validateTermForAcademicYear(
            $termId,
            $academicYearId
        );

        /*
         * Find the immediately preceding term in the SAME academic year.
         *
         * If start_date is available, it is the safest ordering field.
         * The fallback uses the term ID only when dates are unavailable.
         */
        $previousTermQuery = Term::query()
            ->where('academic_year_id', $academicYearId)
            ->where('id', '!=', $currentTerm->id);

        if ($currentTerm->start_date) {
            $previousTerm = (clone $previousTermQuery)
                ->whereNotNull('start_date')
                ->where('start_date', '<', $currentTerm->start_date)
                ->orderByDesc('start_date')
                ->first();
        } else {
            $previousTerm = (clone $previousTermQuery)
                ->where('id', '<', $currentTerm->id)
                ->orderByDesc('id')
                ->first();
        }

        /*
         * First Term has no previous term.
         */
        if (!$previousTerm) {
            return;
        }

        /*
         * Find the previous term's active approved/published Bill Sheet
         * for THIS exact student assignment and academic year.
         */
        $previousBill = BillSheet::query()
            ->where('student_class_assignment_id', $assignment->id)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $previousTerm->id)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->orderByDesc('id')
            ->first();

        /*
         * If the school has not generated a previous-term bill, we do
         * not invent an outstanding balance.
         *
         * This is important for students whose billing has not yet been
         * created for that term.
         */
        if (!$previousBill) {
            return;
        }

        $previousTotal = $this->billTotal($previousBill);

        /*
         * IMPORTANT:
         *
         * We ONLY count payments attached to the previous Bill Sheet.
         *
         * We do NOT use StudentFeeAccount.balance here.
         */
        $previousPaid = $this->billPaid(
            $assignment->id,
            $previousBill->id
        );

        $previousBalance = max(
            0,
            round($previousTotal - $previousPaid, 2)
        );

        if ($previousBalance > 0) {
            throw new \RuntimeException(
                'Payment blocked. '
                . $previousTerm->name
                . ' fees must be fully settled before '
                . $currentTerm->name
                . ' payment can be recorded. '
                . 'Outstanding '
                . $previousTerm->name
                . ' balance: GHS '
                . number_format($previousBalance, 2)
                . '.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TERM FINANCIAL SUMMARY
    |--------------------------------------------------------------------------
    |
    | Returns the financial position of ONE exact Bill Sheet.
    |
    | This is deliberately Bill Sheet based and therefore cannot mix
    | First Term, Second Term and Third Term money.
    |
    |--------------------------------------------------------------------------
    */

    private function billFinancialSummary(
        StudentClassAssignment $assignment,
        BillSheet $billSheet
    ): array {
        $total = $this->billTotal($billSheet);

        $paid = $this->billPaid(
            $assignment->id,
            $billSheet->id
        );

        $balance = max(
            0,
            round($total - $paid, 2)
        );

        return [
            'bill_sheet_id' => $billSheet->id,
            'academic_year_id' => $billSheet->academic_year_id,
            'term_id' => $billSheet->term_id,
            'total' => $total,
            'paid' => $paid,
            'balance' => $balance,
            'status' => $balance <= 0
                ? 'paid'
                : ($paid > 0 ? 'partial' : 'pending'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT CLEARANCE INFORMATION
    |--------------------------------------------------------------------------
    |
    | Used by AJAX responses so the payment form can display the reason
    | a later term is locked.
    |
    |--------------------------------------------------------------------------
    */

    private function previousTermClearance(
        StudentClassAssignment $assignment,
        int $termId,
        int $academicYearId
    ): array {
        $currentTerm = $this->validateTermForAcademicYear(
            $termId,
            $academicYearId
        );

        $previousTermQuery = Term::query()
            ->where('academic_year_id', $academicYearId)
            ->where('id', '!=', $currentTerm->id);

        if ($currentTerm->start_date) {
            $previousTerm = (clone $previousTermQuery)
                ->whereNotNull('start_date')
                ->where('start_date', '<', $currentTerm->start_date)
                ->orderByDesc('start_date')
                ->first();
        } else {
            $previousTerm = (clone $previousTermQuery)
                ->where('id', '<', $currentTerm->id)
                ->orderByDesc('id')
                ->first();
        }

        if (!$previousTerm) {
            return [
                'cleared' => true,
                'has_previous_term' => false,
                'previous_term_id' => null,
                'previous_term_name' => null,
                'previous_term_balance' => 0,
            ];
        }

        $previousBill = BillSheet::query()
            ->where('student_class_assignment_id', $assignment->id)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $previousTerm->id)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->orderByDesc('id')
            ->first();

        if (!$previousBill) {
            return [
                'cleared' => true,
                'has_previous_term' => true,
                'previous_term_id' => $previousTerm->id,
                'previous_term_name' => $previousTerm->name,
                'previous_term_balance' => 0,
                'previous_bill_exists' => false,
            ];
        }

        $total = $this->billTotal($previousBill);

        $paid = $this->billPaid(
            $assignment->id,
            $previousBill->id
        );

        $balance = max(
            0,
            round($total - $paid, 2)
        );

        return [
            'cleared' => $balance <= 0,
            'has_previous_term' => true,
            'previous_term_id' => $previousTerm->id,
            'previous_term_name' => $previousTerm->name,
            'previous_bill_exists' => true,
            'previous_bill_sheet_id' => $previousBill->id,
            'previous_term_total' => $total,
            'previous_term_paid' => $paid,
            'previous_term_balance' => $balance,
        ];
    }


    private function resolveAssignment(
        $studentId,
        $assignmentId,
        $academicYearId,
        $termId
    ) {
        /*
         * IMPORTANT:
         * is_current represents the student's CURRENT assignment.
         * Historical First Term assignments can correctly have
         * is_current = false after the student moves to another term.
         *
         * Payments must resolve the assignment for the selected
         * Academic Year + Term, so is_current must NOT be required here.
         */
        return StudentClassAssignment::query()
            ->with([
                'student',
                'studentClass',
                'academicYear',
                'term',
            ])
            ->whereKey($assignmentId)
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['active']);
            })
            ->first();
    }

    private function resolveBillSheet(
        $billSheetId,
        StudentClassAssignment $assignment,
        $termId
    ) {
        return BillSheet::query()
            ->with('items')
            ->whereKey($billSheetId)
            ->where('student_class_assignment_id', $assignment->id)
            ->where('academic_year_id', $assignment->academic_year_id)
            ->where('term_id', $termId)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->first();
    }

    private function billTotal(BillSheet $billSheet): float
{
    /*
    |--------------------------------------------------------------------------
    | BILL TOTAL
    |--------------------------------------------------------------------------
    |
    | net_amount is the final payable amount when it has been properly
    | calculated.
    |
    | However, older/generated Bill Sheets may have:
    |
    |     total_amount > 0
    |     net_amount = 0
    |
    | In that situation, using:
    |
    |     net_amount ?? total_amount
    |
    | incorrectly returns ZERO because 0 is not NULL.
    |
    | Therefore:
    |
    | 1. Use net_amount when it is greater than zero.
    | 2. Otherwise calculate the payable amount from total_amount,
    |    discount and tax.
    |
    |--------------------------------------------------------------------------
    */

    $totalAmount = (float) ($billSheet->total_amount ?? 0);

    $discountAmount = (float) ($billSheet->discount_amount ?? 0);

    $taxAmount = (float) ($billSheet->tax_amount ?? 0);

    $netAmount = (float) ($billSheet->net_amount ?? 0);


    /*
    |--------------------------------------------------------------------------
    | Properly stored net amount
    |--------------------------------------------------------------------------
    */

    if ($netAmount > 0) {
        return round($netAmount, 2);
    }


    /*
    |--------------------------------------------------------------------------
    | Fallback for Bill Sheets whose net_amount is zero/null
    |--------------------------------------------------------------------------
    */

    return round(
        max(
            0,
            $totalAmount
            - $discountAmount
            + $taxAmount
        ),
        2
    );
}

    private function billPaid($assignmentId, $billSheetId): float
    {
        return round(
            (float) FeePayment::query()
                ->where('student_class_assignment_id', $assignmentId)
                ->where('bill_sheet_id', $billSheetId)
                ->where('status', 'completed')
                ->sum('net_amount'),
            2
        );
    }

    private function getOrCreateFeeAccount(
        StudentClassAssignment $assignment
    ): StudentFeeAccount {
        $account = StudentFeeAccount::firstOrCreate(
            [
                'student_id' => $assignment->student_id,
                'academic_year_id' => $assignment->academic_year_id,
            ],
            [
                'student_class_id' => $assignment->student_class_id,
                'student_class_assignment_id' => $assignment->id,
                'total_fees' => 0,
                'amount_paid' => 0,
                'balance' => 0,
                'discount_applied' => 0,
                'waiver_amount' => 0,
                'status' => 'pending',
                'is_active' => true,
            ]
        );

        $account->student_class_id = $assignment->student_class_id;
        $account->student_class_assignment_id = $assignment->id;

        $account->save();

        return $account->fresh();
    }

    private function synchronizeFeeAccount(
        StudentFeeAccount $feeAccount,
        StudentClassAssignment $assignment
    ): void {
        $totalFees = (float) BillSheet::query()
            ->where('student_class_assignment_id', $assignment->id)
            ->where('academic_year_id', $assignment->academic_year_id)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->get()
            ->sum(fn ($bill) => $this->billTotal($bill));

        $totalPaid = (float) FeePayment::query()
            ->where('student_class_assignment_id', $assignment->id)
            ->where('status', 'completed')
            ->sum('net_amount');

        $discount = (float) ($feeAccount->discount_applied ?? 0);
        $waiver = (float) ($feeAccount->waiver_amount ?? 0);

        $balance = max(
            0,
            round($totalFees - $totalPaid - $discount - $waiver, 2)
        );

        $feeAccount->student_class_id = $assignment->student_class_id;
        $feeAccount->student_class_assignment_id = $assignment->id;
        $feeAccount->total_fees = round($totalFees, 2);
        $feeAccount->amount_paid = round($totalPaid, 2);
        $feeAccount->balance = $balance;
        $feeAccount->status = $this->feeAccountStatus(
            $totalPaid,
            $balance
        );

        $feeAccount->save();
    }

    /*
     * Generate the receipt BEFORE FeePayment::create() performs INSERT.
     *
     * This fixes:
     * SQLSTATE[HY000]: 1364 Field 'receipt_number' doesn't have a default value
     */
    private function generateReceiptNumber(): string
    {
        $nextId = ((int) FeePayment::withTrashed()->max('id')) + 1;

        $receipt = 'RCP-' .
            now()->format('Y') .
            '-' .
            str_pad($nextId, 6, '0', STR_PAD_LEFT);

        if (!FeePayment::withTrashed()
            ->where('receipt_number', $receipt)
            ->exists()) {
            return $receipt;
        }

        /*
         * Extremely unlikely fallback in case the calculated receipt
         * already exists because of a concurrent transaction.
         */
        return $receipt . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));
    }

    private function feeAccountStatus(float $paid, float $balance): string
    {
        if ($balance <= 0) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return 'pending';
    }

    private function studentName($student): string
    {
        if (!empty($student->full_name)) {
            return $student->full_name;
        }

        return trim(collect([
            $student->first_name ?? null,
            $student->middle_name ?? null,
            $student->last_name ?? null,
        ])->filter()->implode(' '));
    }

    private function money(float $amount): string
    {
        return 'GHS ' . number_format($amount, 2);
    }

    private function paymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'card' => 'Card Payment',
            'cheque' => 'Cheque',
            'online' => 'Online Payment',
            'other' => 'Other',
        ];
    }
               /**
     * Display the payment receipt as a normal HTML page.
     * This is useful for the "View Receipt" button.
     */
    public function printReceipt($id)
    {
        $payment = $this->loadPaymentForReceipt($id);

        return view('fee-payments.receipt', compact('payment'));
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIPT PDF (Stream in Browser)
    |--------------------------------------------------------------------------
    */

    /**
     * Open the receipt directly as a PDF in the browser.
     * This is useful for the "View PDF" button.
     */
    public function receiptPdf($id)
    {
        $payment = $this->loadPaymentForReceipt($id);

        $filename = $this->receiptFilename($payment);

        $pdf = Pdf::loadView('fee-payments.receipt', [
            'payment' => $payment,
            'pdfMode' => true,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isPhpEnabled' => false,
            ]);

        return $pdf->stream($filename);
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD RECEIPT (PDF Download)
    |--------------------------------------------------------------------------
    */

    /**
     * Download the receipt as a PDF file.
     * This is the method your "Download Receipt" button should call.
     */
    public function downloadReceipt($id)
    {
        $payment = $this->loadPaymentForReceipt($id);

        $filename = $this->receiptFilename($payment);

        $pdf = Pdf::loadView('fee-payments.receipt', [
            'payment' => $payment,
            'pdfMode' => true,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isPhpEnabled' => false,
            ]);

        return $pdf->download($filename);
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD HTML RECEIPT (Alternative)
    |--------------------------------------------------------------------------
    */

    /**
     * Download the receipt as an HTML file.
     * Useful if you want to save the HTML version.
     */
    public function downloadHtmlReceipt($id)
    {
        $payment = $this->loadPaymentForReceipt($id);

        $html = view('fee-payments.receipt', [
            'payment' => $payment,
            'pdfMode' => false,
        ])->render();

        $filename = 'Receipt-' . ($payment->receipt_number ?? 'payment-' . $payment->id) . '.html';

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /*
    |--------------------------------------------------------------------------
    | BACKWARD COMPATIBLE ALIASES
    |--------------------------------------------------------------------------
    */

    /**
     * Backward-compatible alias for routes using @pdf.
     */
    public function pdf($id)
    {
        return $this->downloadReceipt($id);
    }

    /**
     * Backward-compatible alias for routes using @download.
     */
    public function download($id)
    {
        return $this->downloadReceipt($id);
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIPT HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Load everything required by the receipt.
     */
    private function loadPaymentForReceipt($id): FeePayment
    {
        return FeePayment::with([
            'student',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'billSheet',
            'billSheet.items',
            'billSheetItem',
        ])->findOrFail($id);
    }

    /**
     * Generate a safe receipt filename.
     */
    private function receiptFilename(FeePayment $payment): string
    {
        $receiptNumber = $payment->receipt_number ?: ('PAYMENT-' . $payment->id);

        $safeReceipt = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '-',
            $receiptNumber
        );

        $date = now()->format('Ymd');

        return 'Receipt-' . trim($safeReceipt, '-') . '-' . $date . '.pdf';
    }
}
