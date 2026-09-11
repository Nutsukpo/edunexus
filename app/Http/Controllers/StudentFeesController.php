<?php

namespace App\Http\Controllers;

use App\Models\BillSheet;
use App\Models\FeePayment;
use App\Models\StudentClassAssignment;
use App\Models\StudentFeeAccount;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StudentFeesController extends Controller
{
    /**
     * Display the student's fees for the administrator-selected
     * operational academic year and term.
     *
     * Bill Sheet amounts are displayed independently of payments.
     */
    public function index()
    {
        $student = $this->student();
        [$academicYear, $term] = $this->operationalPeriod();

        $assignment = $this->currentAssignment($student, $academicYear, $term);

        if (!$assignment) {
            return view(
                'students.fees.index',
                $this->emptyPageData($student, $academicYear, $term)
            );
        }

        $billSheets = $this->currentBillSheets(
            $assignment,
            $academicYear,
            $term
        );

        $billTotals = $billSheets->mapWithKeys(function ($bill) {
            return [
                $bill->id => $this->billTotal($bill),
            ];
        });

        $totalFees = round((float) $billTotals->sum(), 2);

        $paidByBill = $this->paymentsByBill(
            $student->id,
            $assignment->id,
            $billSheets
        );

        $amountPaid = round((float) $paidByBill->sum(), 2);
        $balance = max(0, round($totalFees - $amountPaid, 2));

        $feeAccount = StudentFeeAccount::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_id', $student->id)
            ->where('student_class_assignment_id', $assignment->id)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $feeItems = $billSheets
            ->flatMap(function ($bill) {
                return $bill->items->map(function ($item) use ($bill) {
                    return (object) [
                        'id' => $item->id,
                        'bill_sheet_id' => $bill->id,
                        'fee_name' => $item->name,
                        'description' => null,
                        'fee_type' => $item->is_optional
                            ? 'Optional'
                            : 'Mandatory',
                        'amount' => (float) $item->total_amount,
                        'paid_amount' => 0,
                        'due_date' => $bill->due_date,
                        'status' => 'pending',
                    ];
                });
            })
            ->values();

        $billSummary = $billSheets->map(function ($bill) use ($paidByBill) {
            $total = $this->billTotal($bill);
            $paid = round(
                (float) $paidByBill->get($bill->id, 0),
                2
            );

            return (object) [
                'id' => $bill->id,
                'name' => $bill->name,
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, round($total - $paid, 2)),
                'status' => $this->billStatus($total, $paid),
                'due_date' => $bill->due_date,
            ];
        });

        $payments = FeePayment::with([
            'receipt',
            'billSheet',
            'studentClassAssignment.studentClass',
        ])
            ->where('student_id', $student->id)
            ->where('student_class_assignment_id', $assignment->id)
            ->whereIn('bill_sheet_id', $billSheets->pluck('id'))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15);

        return view('students.fees.index', compact(
            'student',
            'assignment',
            'feeAccount',
            'feeItems',
            'payments',
            'totalFees',
            'amountPaid',
            'balance',
            'academicYear',
            'term',
            'billSheets',
            'billSummary'
        ));
    }

    /**
     * Display the payment page.
     */
    public function payment()
    {
        $student = $this->student();
        [$academicYear, $term] = $this->operationalPeriod();

        $assignment = $this->currentAssignment(
            $student,
            $academicYear,
            $term
        );

        if (!$assignment) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'You are not currently assigned to a class for the school\'s operational period.'
                );
        }

        $billSheets = $this->currentBillSheets(
            $assignment,
            $academicYear,
            $term
        );

        if ($billSheets->isEmpty()) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'No approved or published Bill Sheet is available for the current school term yet.'
                );
        }

        $paidByBill = $this->paymentsByBill(
            $student->id,
            $assignment->id,
            $billSheets
        );

        $totalFees = round(
            (float) $billSheets->sum(
                fn ($bill) => $this->billTotal($bill)
            ),
            2
        );

        $amountPaid = round((float) $paidByBill->sum(), 2);
        $balance = max(0, round($totalFees - $amountPaid, 2));

        if ($balance <= 0) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'success',
                    'Your school fees for the current school term have already been fully paid.'
                );
        }

        /*
         * Resolve the first bill with an outstanding amount.
         * The payment form can therefore work without requiring
         * bill_sheet_id from the browser.
         */
        $selectedBillSheet = $this->billWithOutstandingBalance(
            $billSheets,
            $paidByBill
        );

        return view('students.fees.payment', compact(
            'student',
            'assignment',
            'academicYear',
            'term',
            'billSheets',
            'selectedBillSheet',
            'totalFees',
            'amountPaid',
            'balance'
        ));
    }

    /**
     * Initiate a student payment through Paystack.
     *
     * The Bill Sheet is resolved server-side. If the form supplies
     * bill_sheet_id, it is validated against the student's current
     * operational Bill Sheets. Otherwise, the first outstanding
     * Bill Sheet is selected automatically.
     */
    public function initiatePayment(Request $request)
    {
        $student = $this->student();
        [$academicYear, $term] = $this->operationalPeriod();

        $assignment = $this->currentAssignment(
            $student,
            $academicYear,
            $term
        );

        if (!$assignment) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'You do not have a current class assignment for the operational school term.'
                );
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'bill_sheet_id' => ['nullable', 'integer'],
            'phone' => ['nullable', 'string', 'max:20'],
            'network' => ['nullable', 'in:mtn,vodafone,airteltigo'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $billSheets = $this->currentBillSheets(
            $assignment,
            $academicYear,
            $term
        );

        if ($billSheets->isEmpty()) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No approved or published Bill Sheet is available for the current school term.'
                );
        }

        $paidByBill = $this->paymentsByBill(
            $student->id,
            $assignment->id,
            $billSheets
        );

        /*
         * Resolve the Bill Sheet entirely from the trusted server-side
         * collection. Never access a missing array key directly.
         */
        $billSheetId = $validated['bill_sheet_id'] ?? null;

        if ($billSheetId !== null) {
            $billSheet = $billSheets->firstWhere(
                'id',
                (int) $billSheetId
            );

            if (!$billSheet) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'The selected Bill Sheet does not belong to your current school term.'
                    );
            }

            $billTotal = $this->billTotal($billSheet);
            $paid = round(
                (float) $paidByBill->get($billSheet->id, 0),
                2
            );

            if ($billTotal <= $paid) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'The selected Bill Sheet has already been fully paid.'
                    );
            }
        } else {
            $billSheet = $this->billWithOutstandingBalance(
                $billSheets,
                $paidByBill
            );

            if (!$billSheet) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'There is no outstanding balance on the current Bill Sheets.'
                    );
            }

            $billTotal = $this->billTotal($billSheet);
            $paid = round(
                (float) $paidByBill->get($billSheet->id, 0),
                2
            );
        }

        $billBalance = max(
            0,
            round($billTotal - $paid, 2)
        );

        $amount = round(
            (float) $validated['amount'],
            2
        );

        if ($amount <= 0) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount must be greater than zero.');
        }

        if ($amount > $billBalance) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Payment cannot exceed the current Bill Sheet balance of GHS '
                    . number_format($billBalance, 2)
                    . '.'
                );
        }

        /*
         * Prevent duplicate pending Paystack transactions for the
         * same student, assignment, Bill Sheet and amount.
         */
        $existingPending = FeePayment::query()
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where('bill_sheet_id', $billSheet->id)
            ->where('status', 'pending')
            ->where('payment_method', 'paystack')
            ->where('net_amount', $amount)
            ->latest('id')
            ->first();

        if ($existingPending) {
            $url = data_get(
                $existingPending->metadata,
                'paystack_authorization_url'
            );

            if ($url) {
                return redirect()->away($url);
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    'You already have a pending Paystack payment for this amount.'
                );
        }

        $reference = 'EDUNEXUS-'
            . now()->format('YmdHis')
            . '-'
            . strtoupper(Str::random(8));

        $receiptNumber = 'RCP-'
            . now()->format('Y')
            . '-'
            . str_pad(
                ((int) FeePayment::max('id')) + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

        $account = StudentFeeAccount::query()
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $payment = FeePayment::create([
            'student_id' => $student->id,
            'student_class_assignment_id' => $assignment->id,
            'student_fee_account_id' => $account?->id,
            'bill_sheet_id' => $billSheet->id,
            'bill_sheet_item_id' => null,
            'receipt_number' => $receiptNumber,
            'amount' => $amount,
            'penalty_amount' => 0,
            'discount_amount' => 0,
            'net_amount' => $amount,
            'payment_method' => 'paystack',
            'payment_date' => now()->toDateString(),
            'status' => 'pending',
            'payment_type' => $amount >= $billBalance
                ? 'full'
                : 'partial',
            'reference_number' => $reference,
            'transaction_id' => null,
            'notes' => $validated['notes']
                ?? 'Student portal Paystack payment',
            'recorded_by' => 'student',
            'metadata' => [
                'student_id' => $student->id,
                'student_class_assignment_id' => $assignment->id,
                'student_class_id' => $assignment->student_class_id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $term->id,
                'bill_sheet_id' => $billSheet->id,
                'bill_total' => $billTotal,
                'bill_paid_before_payment' => $paid,
                'bill_balance_before_payment' => $billBalance,
                'phone' => $validated['phone'] ?? null,
                'network' => $validated['network'] ?? null,
                'initiated_from' => 'student_portal',
                'gateway' => 'paystack',
                'paystack_reference' => $reference,
            ],
        ]);

        $response = Http::withToken(
            config('services.paystack.secret_key')
        )
            ->acceptJson()
            ->post(
                'https://api.paystack.co/transaction/initialize',
                [
                    'email' => $this->studentPaymentEmail($student),
                    'amount' => (int) round($amount * 100),
                    'currency' => 'GHS',
                    'reference' => $reference,
                    'channels' => [
                        'card',
                        'mobile_money',
                        'bank',
                        'ussd',
                        'qr',
                    ],
                    'callback_url' => route(
                        'students.fees.payment.callback'
                    ),
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'student_id' => $student->id,
                        'student_fee_account_id' => $account?->id,
                        'student_class_assignment_id' => $assignment->id,
                        'bill_sheet_id' => $billSheet->id,
                        'academic_year_id' => $academicYear->id,
                        'term_id' => $term->id,
                        'receipt_number' => $receiptNumber,
                        'student_reference' => $student->student_id,
                    ],
                ]
            );

        if (!$response->successful() || !$response->json('status')) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'paystack_error' => $response->json('message')
                            ?? 'Unable to initialize Paystack transaction.',
                        'paystack_response' => $response->json(),
                    ]
                ),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $response->json('message')
                    ?? 'Unable to initialize payment with Paystack.'
                );
        }

        $payment->update([
            'metadata' => array_merge(
                $payment->metadata ?? [],
                [
                    'paystack_access_code' =>
                        $response->json('data.access_code'),
                    'paystack_authorization_url' =>
                        $response->json('data.authorization_url'),
                    'paystack_initialized_at' =>
                        now()->toDateTimeString(),
                ]
            ),
        ]);

        return redirect()->away(
            $response->json('data.authorization_url')
        );
    }

    /**
     * Paystack callback/verification.
     */
    public function paymentCallback(Request $request)
    {
        $student = $this->student();

        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'No Paystack transaction reference was received.'
                );
        }

        $payment = FeePayment::query()
            ->where('reference_number', $reference)
            ->where('student_id', $student->id)
            ->first();

        if (!$payment) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'Payment transaction could not be found.'
                );
        }

        if ($payment->status === 'completed') {
            return redirect()
                ->route('students.fees')
                ->with(
                    'success',
                    'Your payment has already been confirmed.'
                );
        }

        $response = Http::withToken(
            config('services.paystack.secret_key')
        )
            ->acceptJson()
            ->get(
                'https://api.paystack.co/transaction/verify/'
                . urlencode($reference)
            );

        if (!$response->successful() || !$response->json('status')) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'Unable to verify your Paystack payment.'
                );
        }

        $transaction = $response->json('data');

        $expectedAmount = (int) round(
            ((float) $payment->net_amount) * 100
        );

        $paidAmount = (int) ($transaction['amount'] ?? 0);

        if ($paidAmount !== $expectedAmount) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'verification_error' =>
                            'Paystack amount mismatch',
                        'expected_amount' => $expectedAmount,
                        'received_amount' => $paidAmount,
                        'paystack_response' => $transaction,
                    ]
                ),
            ]);

            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'The payment amount could not be verified.'
                );
        }

        if (($transaction['status'] ?? null) === 'success') {
            $payment->update([
                'status' => 'completed',
                'transaction_id' =>
                    (string) (
                        $transaction['id']
                        ?? $payment->transaction_id
                    ),
                'reference_number' =>
                    $transaction['reference'] ?? $reference,
                'payment_method' => 'paystack',
                'payment_date' => isset($transaction['paid_at'])
                    ? \Carbon\Carbon::parse(
                        $transaction['paid_at']
                    )->toDateString()
                    : now()->toDateString(),
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'paystack_status' =>
                            $transaction['status'],
                        'paystack_channel' =>
                            $transaction['channel'] ?? null,
                        'gateway_response' =>
                            $transaction['gateway_response'] ?? null,
                        'paid_at' =>
                            $transaction['paid_at'] ?? null,
                        'verified_at' =>
                            now()->toDateTimeString(),
                    ]
                ),
            ]);

            return redirect()
                ->route('students.fees')
                ->with(
                    'success',
                    'Payment successful. Your fee account has been updated.'
                );
        }

        $payment->update([
            'status' => 'failed',
            'metadata' => array_merge(
                $payment->metadata ?? [],
                [
                    'paystack_status' =>
                        $transaction['status'] ?? null,
                    'gateway_response' =>
                        $transaction['gateway_response'] ?? null,
                ]
            ),
        ]);

        return redirect()
            ->route('students.fees')
            ->with(
                'error',
                'Payment was not completed. Paystack status: '
                . ($transaction['status'] ?? 'unknown')
            );
    }

    /**
     * Display receipt.
     */
    public function receipt($id)
    {
        $student = $this->student();

        $payment = FeePayment::with([
            'receipt',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'studentClassAssignment.term',
            'billSheet',
            'billSheet.items',
            'billSheetItem',
        ])
            ->where('student_id', $student->id)
            ->whereKey($id)
            ->firstOrFail();

        return view(
            'students.fees.receipt',
            compact('student', 'payment')
        );
    }

    /**
     * Download completed receipt as PDF.
     */
    public function receiptPdf($id)
    {
        $student = $this->student();

        $payment = FeePayment::with([
            'receipt',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
            'studentClassAssignment.term',
            'billSheet',
            'billSheet.items',
            'billSheetItem',
        ])
            ->where('student_id', $student->id)
            ->whereKey($id)
            ->where('status', 'completed')
            ->firstOrFail();

        $filename = 'Receipt-'
            . ($payment->receipt_number ?? $payment->id)
            . '.pdf';

        $pdf = Pdf::loadView(
            'students.fees.receipt',
            [
                'student' => $student,
                'payment' => $payment,
                'pdfMode' => true,
            ]
        )
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isPhpEnabled' => false,
            ]);

        return $pdf->download($filename);
    }

    /**
     * Get authenticated student.
     */
    private function student()
    {
        $student = Auth::guard('student')->user();

        abort_unless(
            $student,
            401,
            'Student authentication is required.'
        );

        return $student;
    }

    /**
     * Resolve the administrator-selected operational period.
     */
    private function operationalPeriod(): array
    {
        $settings = SystemSetting::instance()->load([
            'operationalAcademicYear',
            'operationalTerm',
        ]);

        $academicYear = $settings->operationalAcademicYear;
        $term = $settings->operationalTerm;

        if (!$academicYear || !$term) {
            abort(
                503,
                'The school operational academic year and term have not been configured by the administrator.'
            );
        }

        if (
            (int) $term->academic_year_id
            !== (int) $academicYear->id
        ) {
            abort(
                503,
                'The school operational term does not belong to the operational academic year.'
            );
        }

        return [$academicYear, $term];
    }

    /**
     * Get the student's current assignment for the operational period.
     */
    private function currentAssignment(
        $student,
        $academicYear,
        $term
    ) {
        return StudentClassAssignment::with([
            'studentClass',
            'academicYear',
            'term',
        ])
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_current', true)
            ->whereIn('status', ['active', 'repeated'])
            ->latest('id')
            ->first();
    }

    /**
     * Get all active approved/published Bill Sheets belonging to
     * the student's exact current assignment and operational period.
     */
    private function currentBillSheets(
        $assignment,
        $academicYear,
        $term
    ) {
        return BillSheet::with('items')
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->whereIn('status', ['approved', 'published'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Calculate a Bill Sheet's charge before considering payments.
     */
    private function billTotal($bill): float
    {
        $net = (float) ($bill->net_amount ?? 0);

        if ($net > 0) {
            return round($net, 2);
        }

        return round(
            max(
                0,
                (float) ($bill->total_amount ?? 0)
                - (float) ($bill->discount_amount ?? 0)
                + (float) ($bill->tax_amount ?? 0)
            ),
            2
        );
    }

    /**
     * Sum completed payments per Bill Sheet.
     */
    private function paymentsByBill(
        int $studentId,
        int $assignmentId,
        $billSheets
    ) {
        if ($billSheets->isEmpty()) {
            return collect();
        }

        return FeePayment::query()
            ->where('student_id', $studentId)
            ->where(
                'student_class_assignment_id',
                $assignmentId
            )
            ->whereIn(
                'bill_sheet_id',
                $billSheets->pluck('id')
            )
            ->where('status', 'completed')
            ->selectRaw(
                'bill_sheet_id, SUM(net_amount) as paid'
            )
            ->groupBy('bill_sheet_id')
            ->pluck('paid', 'bill_sheet_id');
    }

    /**
     * Get completed payments against one Bill Sheet.
     */
    private function billPaid(
        int $assignmentId,
        int $billSheetId
    ): float {
        return round(
            (float) FeePayment::query()
                ->where(
                    'student_class_assignment_id',
                    $assignmentId
                )
                ->where('bill_sheet_id', $billSheetId)
                ->where('status', 'completed')
                ->sum('net_amount'),
            2
        );
    }

    /**
     * Resolve the first Bill Sheet with an outstanding balance.
     */
    private function billWithOutstandingBalance(
        $billSheets,
        $paidByBill
    ) {
        return $billSheets->first(function ($bill) use ($paidByBill) {
            $total = $this->billTotal($bill);
            $paid = round(
                (float) $paidByBill->get($bill->id, 0),
                2
            );

            return $total > $paid;
        });
    }

    /**
     * Determine Bill Sheet payment status.
     */
    private function billStatus(
        float $total,
        float $paid
    ): string {
        if ($total > 0 && $paid >= $total) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return 'pending';
    }

    /**
     * Determine the student's Paystack email.
     */
    private function studentPaymentEmail($student): string
    {
        return $student->father_email
            ?? $student->mother_email
            ?? $student->guardian_email
            ?? 'student' . $student->id . '@edunexus.local';
    }

    /**
     * Empty fee page state.
     */
    private function emptyPageData(
        $student,
        $academicYear = null,
        $term = null
    ): array {
        return [
            'student' => $student,
            'assignment' => null,
            'feeAccount' => null,
            'feeItems' => collect(),
            'payments' => collect(),
            'totalFees' => 0,
            'amountPaid' => 0,
            'balance' => 0,
            'academicYear' => $academicYear,
            'term' => $term,
            'billSheets' => collect(),
            'billSummary' => collect(),
            'currentBillSheet' => null,
        ];
    }
}
