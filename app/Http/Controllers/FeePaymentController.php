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

        $terms = Term::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $paymentMethods = $this->paymentMethods();

        $paymentTypes = [
            'full'        => 'Full Payment',
            'partial'     => 'Partial Payment',
            'installment' => 'Installment',
            'advance'     => 'Advance Payment',
        ];

        $recordedBy = [
            'cashier'   => 'Cashier',
            'accountant' => 'Accountant',
            'admin'     => 'Administrator',
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
                $request->academic_year_id
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

            if ($amount > $billBalance) {
                throw new \RuntimeException(
                    'The payment amount cannot exceed the outstanding Bill Sheet balance of GHS '
                    . number_format($billBalance, 2) . '.'
                );
            }

            if ($discount > ($amount + $penalty)) {
                throw new \RuntimeException(
                    'The discount cannot be greater than the payment plus penalty.'
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
                $request->academic_year_id
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

            if ($request->status === 'completed' && $amount > $outstanding) {
                throw new \RuntimeException(
                    'The payment amount cannot exceed the outstanding Bill Sheet balance of GHS '
                    . number_format($outstanding, 2) . '.'
                );
            }

            if ($discount > ($amount + $penalty)) {
                throw new \RuntimeException(
                    'The discount cannot be greater than the payment plus penalty.'
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
            'term_id' => 'nullable|exists:terms,id',
        ]);

        $assignments = StudentClassAssignment::query()
            ->with(['student', 'studentClass', 'academicYear'])
            ->where('student_class_id', $validated['class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereIn(DB::raw('LOWER(status)'), ['active']);
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
                    'class_name' => $assignment->studentClass?->name,
                    'academic_year' => $assignment->academicYear?->name,
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

        try {
            $assignment = $this->resolveAssignment(
                $studentId,
                $request->student_class_assignment_id,
                $request->academic_year_id
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
            'student_id' => 'required|exists:students,id',
            'student_class_assignment_id' => 'required|exists:student_class_assignments,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $assignment = $this->resolveAssignment(
            $request->student_id,
            $request->student_class_assignment_id,
            $request->academic_year_id
        );

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student class assignment.',
            ], 422);
        }

        $bills = BillSheet::query()
            ->where('student_class_assignment_id', $assignment->id)
            ->where('academic_year_id', $assignment->academic_year_id)
            ->where('term_id', $request->term_id)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($bill) use ($assignment) {
                $total = $this->billTotal($bill);
                $paid = $this->billPaid($assignment->id, $bill->id);
                $balance = max(0, round($total - $paid, 2));

                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'total_amount' => $total,
                    'paid' => $paid,
                    'balance' => $balance,
                    'formatted_total' => $this->money($total),
                    'formatted_paid' => $this->money($paid),
                    'formatted_balance' => $this->money($balance),
                    'status' => $balance <= 0
                        ? 'paid'
                        : ($paid > 0 ? 'partial' : 'pending'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'bill_sheets' => $bills,
            'count' => $bills->count(),
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

    private function resolveAssignment($studentId, $assignmentId, $academicYearId)
    {
        return StudentClassAssignment::query()
            ->with([
                'student',
                'studentClass',
                'academicYear',
            ])
            ->whereKey($assignmentId)
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_current', true)
            ->where('status', 'active')
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
        return round(
            (float) (
                $billSheet->net_amount
                ?? $billSheet->total_amount
                ?? 0
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
