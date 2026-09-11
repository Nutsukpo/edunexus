<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassFeeStructure;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentFeeAllocationController extends Controller
{
    /**
     * Display student fee accounts.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();

        $query = StudentFeeAccount::query()
            ->with([
                'student',
                'studentClass',
                'academicYear',
                'studentClassAssignment',
                'feeItems',
            ])
            ->where('is_active', true);

        if ($request->filled('academic_year_id')) {
            $query->where(
                'academic_year_id',
                $request->academic_year_id
            );
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where(function ($q) use ($search) {
                    $q->where(
                        'student_id',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'first_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'middle_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'last_name',
                        'like',
                        "%{$search}%"
                    );
                });
            });
        }

        $accounts = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'student_fee_allocations.index',
            compact(
                'accounts',
                'academicYears'
            )
        );
    }


    /**
     * Show the fee allocation form.
     */
    public function create(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();

        $student = null;
        $assignment = null;
        $feeAccount = null;
        $classFeeStructures = collect();
        $feeItems = collect();

        /*
         * No student selected yet.
         */
        if (!$request->filled('student_id')) {
            return view(
                'student_fee_allocations.create',
                compact(
                    'academicYears',
                    'student',
                    'assignment',
                    'feeAccount',
                    'classFeeStructures',
                    'feeItems'
                )
            );
        }

        /*
         * Load student.
         */
        $student = Student::find($request->student_id);

        if (!$student) {
            return redirect()
                ->route('student-fee-allocations.create')
                ->with('error', 'Student not found.');
        }

        /*
         * Load the student's current class assignment.
         */
        $assignment = StudentClassAssignment::query()
            ->with([
                'studentClass',
                'academicYear',
            ])
            ->where('student_id', $student->id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return redirect()
                ->route('student-fee-allocations.create')
                ->with(
                    'error',
                    'This student does not have an active current class assignment.'
                );
        }

        /*
         * Find or create the student's fee account
         * for the academic year attached to the
         * current class assignment.
         */
        $feeAccount = StudentFeeAccount::firstOrCreate(
            [
                'student_id' => $student->id,
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

        /*
         * Keep the account connected to the student's
         * current class assignment.
         */
        $feeAccount->update([
            'student_class_id' => $assignment->student_class_id,
            'student_class_assignment_id' => $assignment->id,
            'is_active' => true,
        ]);

        /*
         * Load all active fee structures belonging
         * to the student's current class and year.
         */
        $classFeeStructures = ClassFeeStructure::query()
            ->where(
                'student_class_id',
                $assignment->student_class_id
            )
            ->where(
                'academic_year_id',
                $assignment->academic_year_id
            )
            ->where('is_active', true)
            ->orderByDesc('is_required')
            ->orderBy('fee_name')
            ->get();

        /*
         * Existing account items.
         */
        $feeItems = $feeAccount->feeItems()
            ->orderBy('fee_name')
            ->get();

        /*
         * IMPORTANT:
         * Explicitly expose the numeric account total
         * to the Blade. This prevents the view from
         * trying to dereference a potentially null model.
         */
        $accountTotal = (float) ($feeAccount->total_fees ?? 0);

        $accountBalance = (float) ($feeAccount->balance ?? 0);

        return view(
            'student_fee_allocations.create',
            compact(
                'academicYears',
                'student',
                'assignment',
                'feeAccount',
                'classFeeStructures',
                'feeItems',
                'accountTotal',
                'accountBalance'
            )
        );
    }


    /**
     * Search students for fee allocation.
     */
    public function students(Request $request)
    {
        $search = trim(
            $request->input('q', '')
        );

        if ($search === '') {
            return response()->json([]);
        }

        $students = Student::query()
            ->where(function ($query) use ($search) {
                $query->where(
                    'student_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'first_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'middle_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'last_name',
                    'like',
                    "%{$search}%"
                );
            })
            ->orderBy('first_name')
            ->limit(20)
            ->get([
                'id',
                'student_id',
                'first_name',
                'middle_name',
                'last_name',
            ]);

        return response()->json(
            $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'name' => trim(
                        $student->first_name . ' ' .
                        ($student->middle_name ?? '') . ' ' .
                        $student->last_name
                    ),
                ];
            })
        );
    }


    /**
     * Save student fee allocation.
     *
     * The server calculates standard fee amounts directly
     * from ClassFeeStructure. Client-side amounts are never
     * trusted.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],

            'student_fee_account_id' => [
                'required',
                'integer',
                'exists:student_fee_accounts,id',
            ],

            'items' => [
                'nullable',
                'array',
            ],

            'items.*.class_fee_structure_id' => [
                'required',
                'integer',
                'exists:class_fee_structures,id',
            ],

            'custom_items' => [
                'nullable',
                'array',
            ],

            'custom_items.*.fee_name' => [
                'required',
                'string',
                'max:255',
            ],

            'custom_items.*.fee_type' => [
                'required',
                'string',
                'max:255',
            ],

            'custom_items.*.amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'custom_items.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'custom_items.*.is_required' => [
                'nullable',
                'boolean',
            ],

            'custom_items.*.due_date' => [
                'nullable',
                'date',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            /*
             * Lock the account while modifying it.
             */
            $account = StudentFeeAccount::query()
                ->lockForUpdate()
                ->findOrFail(
                    $validated['student_fee_account_id']
                );

            /*
             * Make sure the account belongs to the
             * selected student.
             */
            if (
                (int) $account->student_id !==
                (int) $validated['student_id']
            ) {
                throw ValidationException::withMessages([
                    'student_id' =>
                        'The selected student does not match the fee account.',
                ]);
            }

            /*
             * Confirm the student's current assignment
             * still matches the account.
             */
            $assignment = StudentClassAssignment::query()
                ->where('id', $account->student_class_assignment_id)
                ->where('student_id', $account->student_id)
                ->where('is_current', true)
                ->where('status', 'active')
                ->first();

            if (!$assignment) {
                throw ValidationException::withMessages([
                    'student_id' =>
                        'The student does not have a valid current class assignment.',
                ]);
            }

            /*
             * Remove only unpaid allocation items.
             *
             * Completed/paid history remains intact.
             */
            $account->feeItems()
                ->whereIn(
                    'status',
                    [
                        'pending',
                        'partial',
                        'overdue',
                    ]
                )
                ->delete();

            /*
             * Add selected standard class fees.
             */
            foreach ($validated['items'] ?? [] as $item) {

                $structure = ClassFeeStructure::query()
                    ->where(
                        'id',
                        $item['class_fee_structure_id']
                    )
                    ->where(
                        'student_class_id',
                        $account->student_class_id
                    )
                    ->where(
                        'academic_year_id',
                        $account->academic_year_id
                    )
                    ->where('is_active', true)
                    ->first();

                if (!$structure) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'One of the selected fee structures is invalid for this student.',
                    ]);
                }

                $amount = round(
                    (float) $structure->amount,
                    2
                );

                if ($amount <= 0) {
                    continue;
                }

                $account->feeItems()->create([
                    'class_fee_structure_id' => $structure->id,
                    'fee_type' => $structure->fee_type,
                    'fee_name' => $structure->fee_name,
                    'amount' => $amount,
                    'description' => $structure->description,
                    'is_required' => (bool) $structure->is_required,
                    'due_date' => $structure->due_date,
                    'status' => 'pending',
                    'paid_amount' => 0,
                    'remaining_amount' => $amount,
                    'metadata' => [
                        'source' => 'class_fee_structure',
                        'class_fee_structure_id' => $structure->id,
                    ],
                ]);
            }

            /*
             * Add custom fees.
             */
            foreach ($validated['custom_items'] ?? [] as $item) {

                $amount = round(
                    (float) $item['amount'],
                    2
                );

                if ($amount <= 0) {
                    continue;
                }

                $account->feeItems()->create([
                    'class_fee_structure_id' => null,
                    'fee_type' => $item['fee_type'],
                    'fee_name' => $item['fee_name'],
                    'amount' => $amount,
                    'description' => $item['description'] ?? null,
                    'is_required' =>
                        (bool) ($item['is_required'] ?? false),
                    'due_date' => $item['due_date'] ?? null,
                    'status' => 'pending',
                    'paid_amount' => 0,
                    'remaining_amount' => $amount,
                    'metadata' => [
                        'source' => 'custom',
                    ],
                ]);
            }

            /*
             * Recalculate the account from its items.
             */
            $this->recalculateAccount($account);

            /*
             * Reconcile all completed payments against
             * the new fee items.
             */
            $this->reconcilePayments($account);
        });

        return redirect()
            ->route('student-fee-allocations.index')
            ->with(
                'success',
                'Student fee allocation saved successfully.'
            );
    }


    /**
     * Display a student's fee allocation.
     */
    public function show(StudentFeeAccount $studentFeeAccount)
    {
        $studentFeeAccount->load([
            'student',
            'studentClass',
            'academicYear',
            'studentClassAssignment',
            'feeItems.classFeeStructure',
            'payments' => function ($query) {
                $query
                    ->where('status', 'completed')
                    ->latest();
            },
        ]);

        return view(
            'student_fee_allocations.show',
            compact('studentFeeAccount')
        );
    }


    /**
     * Delete an unpaid fee item.
     */
    public function destroyItem(
        StudentFeeItem $studentFeeItem
    ) {
        if (
            in_array(
                $studentFeeItem->status,
                ['paid', 'partial']
            )
        ) {
            return back()->with(
                'error',
                'A paid or partially paid fee item cannot be deleted.'
            );
        }

        DB::transaction(function () use ($studentFeeItem) {

            $account = $studentFeeItem->studentFeeAccount;

            if (!$account) {
                throw ValidationException::withMessages([
                    'student_fee_item' =>
                        'The fee account associated with this item could not be found.',
                ]);
            }

            $studentFeeItem->delete();

            $this->recalculateAccount($account);

            $this->reconcilePayments($account);
        });

        return back()->with(
            'success',
            'Fee item removed successfully.'
        );
    }


    /**
     * Recalculate account totals.
     */
    private function recalculateAccount(
        StudentFeeAccount $account
    ): void {
        $total = round(
            (float) $account->feeItems()->sum('amount'),
            2
        );

        $paid = round(
            (float) FeePayment::query()
                ->where(
                    'student_fee_account_id',
                    $account->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->sum('net_amount'),
            2
        );

        $account->total_fees = $total;
        $account->amount_paid = $paid;

        $account->balance = max(
            0,
            round(
                $total - $paid,
                2
            )
        );

        if ($total <= 0) {
            $account->status = 'pending';
        } elseif ($paid >= $total) {
            $account->status = 'paid';
        } elseif ($paid > 0) {
            $account->status = 'partial';
        } else {
            $account->status = 'pending';
        }

        $account->save();
    }


    /**
     * Reconcile completed payments against fee items.
     */
    private function reconcilePayments(
        StudentFeeAccount $account
    ): void {
        $items = $account->feeItems()
            ->orderBy('id')
            ->get();

        /*
         * Reset item payment allocations.
         */
        foreach ($items as $item) {

            $item->paid_amount = 0;

            $item->remaining_amount =
                round(
                    (float) $item->amount,
                    2
                );

            $item->status = 'pending';

            $item->save();
        }

        /*
         * Get completed payments.
         */
        $payments = FeePayment::query()
            ->where(
                'student_fee_account_id',
                $account->id
            )
            ->where(
                'status',
                'completed'
            )
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {

            $remainingPayment = round(
                (float) $payment->net_amount,
                2
            );

            if ($remainingPayment <= 0) {
                continue;
            }

            foreach ($items as $item) {

                $remainingItem = max(
                    0,
                    round(
                        (float) $item->amount -
                        (float) $item->paid_amount,
                        2
                    )
                );

                if (
                    $remainingItem <= 0 ||
                    $remainingPayment <= 0
                ) {
                    continue;
                }

                $allocation = min(
                    $remainingPayment,
                    $remainingItem
                );

                $item->paid_amount = round(
                    (float) $item->paid_amount +
                    $allocation,
                    2
                );

                $item->remaining_amount = round(
                    (float) $item->amount -
                    (float) $item->paid_amount,
                    2
                );

                if ($item->remaining_amount <= 0) {

                    $item->status = 'paid';

                } elseif ($item->paid_amount > 0) {

                    $item->status = 'partial';

                } else {

                    $item->status = 'pending';
                }

                $item->save();

                $remainingPayment = round(
                    $remainingPayment - $allocation,
                    2
                );

                if ($remainingPayment <= 0) {
                    break;
                }
            }
        }

        /*
         * Recalculate account one final time after
         * payment reconciliation.
         */
        $this->recalculateAccount($account);
    }
}
