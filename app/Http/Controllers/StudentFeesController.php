<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\StudentClassAssignment;
use App\Models\StudentFeeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentFeesController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STUDENT FEES DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $student = Auth::guard('student')->user();

        /*
        |--------------------------------------------------------------------------
        | Get student's CURRENT class assignment
        |--------------------------------------------------------------------------
        */

        $assignment = StudentClassAssignment::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_id', $student->id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | No current class
        |--------------------------------------------------------------------------
        */

        if (!$assignment) {
            return view('students.fees.index', [
                'student' => $student,
                'assignment' => null,
                'feeAccount' => null,
                'feeItems' => collect(),
                'payments' => collect(),
                'totalFees' => 0,
                'amountPaid' => 0,
                'balance' => 0,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get fee account belonging to CURRENT assignment
        |--------------------------------------------------------------------------
        */

        $feeAccount = StudentFeeAccount::with([
            'studentClass',
            'academicYear',
            'feeItems',
        ])
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where('is_active', true)
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | No fee account
        |--------------------------------------------------------------------------
        |
        | Do NOT create an empty account automatically.
        | The school must create/allocate the student's fees.
        |
        */

        if (!$feeAccount) {
            return view('students.fees.index', [
                'student' => $student,
                'assignment' => $assignment,
                'feeAccount' => null,
                'feeItems' => collect(),
                'payments' => collect(),
                'totalFees' => 0,
                'amountPaid' => 0,
                'balance' => 0,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate REAL completed payments
        |--------------------------------------------------------------------------
        */

        $amountPaid = (float) FeePayment::query()
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where(
                'student_fee_account_id',
                $feeAccount->id
            )
            ->where('status', 'completed')
            ->sum('net_amount');

        /*
        |--------------------------------------------------------------------------
        | Total fees
        |--------------------------------------------------------------------------
        */

        $totalFees = (float) (
            $feeAccount->total_fees ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Outstanding balance
        |--------------------------------------------------------------------------
        */

        $balance = max(
            0,
            $totalFees - $amountPaid
        );

        /*
        |--------------------------------------------------------------------------
        | Synchronize account
        |--------------------------------------------------------------------------
        */

        $feeAccount->amount_paid = $amountPaid;
        $feeAccount->balance = $balance;

        $feeAccount->status = match (true) {

            $totalFees <= 0 =>
                'pending',

            $balance <= 0 =>
                'paid',

            $amountPaid > 0 =>
                'partial',

            default =>
                'pending',
        };

        $feeAccount->save();

        /*
        |--------------------------------------------------------------------------
        | Fee items
        |--------------------------------------------------------------------------
        */

        $feeItems = $feeAccount->feeItems()
            ->orderBy('fee_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Payment history
        |--------------------------------------------------------------------------
        */

        $payments = FeePayment::with([
            'receipt',
            'studentClassAssignment.studentClass',
        ])
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where(
                'student_fee_account_id',
                $feeAccount->id
            )
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15);

        return view(
            'students.fees.index',
            compact(
                'student',
                'assignment',
                'feeAccount',
                'feeItems',
                'payments',
                'totalFees',
                'amountPaid',
                'balance'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT PAGE
    |--------------------------------------------------------------------------
    */

    public function payment()
    {
        $student = Auth::guard('student')->user();

        /*
        |--------------------------------------------------------------------------
        | Current assignment
        |--------------------------------------------------------------------------
        */

        $assignment = StudentClassAssignment::with([
            'studentClass',
            'academicYear',
        ])
            ->where('student_id', $student->id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'You are not currently assigned to a class.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Current fee account
        |--------------------------------------------------------------------------
        */

        $feeAccount = StudentFeeAccount::with([
            'feeItems',
        ])
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (!$feeAccount) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'Your fee account has not yet been created.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate current payment position
        |--------------------------------------------------------------------------
        */

        $amountPaid = (float) FeePayment::query()
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where(
                'student_fee_account_id',
                $feeAccount->id
            )
            ->where('status', 'completed')
            ->sum('net_amount');

        $totalFees = (float) (
            $feeAccount->total_fees ?? 0
        );

        $balance = max(
            0,
            $totalFees - $amountPaid
        );

        /*
        |--------------------------------------------------------------------------
        | Already fully paid
        |--------------------------------------------------------------------------
        */

        if ($balance <= 0) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'success',
                    'Your school fees have already been fully paid.'
                );
        }

        return view(
            'students.fees.payment',
            compact(
                'student',
                'assignment',
                'feeAccount',
                'totalFees',
                'amountPaid',
                'balance'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INITIATE MOBILE MONEY PAYMENT
    |--------------------------------------------------------------------------
    */

    public function initiatePayment(Request $request)
    {
        $student = Auth::guard('student')->user();
    
        /*
        |--------------------------------------------------------------------------
        | Validate request
        |--------------------------------------------------------------------------
        */
    
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],
    
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
    
            'network' => [
                'nullable',
                'in:mtn,vodafone,airteltigo',
            ],
    
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);
    
        /*
        |--------------------------------------------------------------------------
        | Current assignment
        |--------------------------------------------------------------------------
        */
    
        $assignment = StudentClassAssignment::where(
            'student_id',
            $student->id
        )
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();
    
        if (!$assignment) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'You do not have a current class assignment.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Current fee account
        |--------------------------------------------------------------------------
        */
    
        $account = StudentFeeAccount::where(
            'student_id',
            $student->id
        )
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where('is_active', true)
            ->latest('id')
            ->first();
    
        if (!$account) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Your fee account could not be found.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Calculate completed payments
        |--------------------------------------------------------------------------
        */
    
        $paid = (float) FeePayment::query()
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where(
                'student_fee_account_id',
                $account->id
            )
            ->where('status', 'completed')
            ->sum('net_amount');
    
        $total = (float) (
            $account->total_fees ?? 0
        );
    
        $balance = max(
            0,
            $total - $paid
        );
    
        $amount = round(
            (float) $validated['amount'],
            2
        );
    
        /*
        |--------------------------------------------------------------------------
        | Validate payment amount
        |--------------------------------------------------------------------------
        */
    
        if ($amount <= 0) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please enter a valid payment amount.'
                );
        }
    
        if ($amount > $balance) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Payment cannot exceed your outstanding balance of GHS '
                    . number_format($balance, 2)
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate pending Paystack transactions
        |--------------------------------------------------------------------------
        */
    
        $existingPending = FeePayment::query()
            ->where('student_id', $student->id)
            ->where(
                'student_class_assignment_id',
                $assignment->id
            )
            ->where(
                'student_fee_account_id',
                $account->id
            )
            ->where('status', 'pending')
            ->where('payment_method', 'paystack')
            ->where('amount', $amount)
            ->latest('id')
            ->first();
    
        if ($existingPending) {
            return back()
                ->with(
                    'error',
                    'You already have a pending Paystack payment for this amount.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Generate unique Paystack reference
        |--------------------------------------------------------------------------
        */
    
        $reference = 'EDUNEXUS-' .
            now()->format('YmdHis') .
            '-' .
            strtoupper(Str::random(8));
    
        /*
        |--------------------------------------------------------------------------
        | Generate local receipt number
        |--------------------------------------------------------------------------
        */
    
        $nextId = ((int) FeePayment::max('id')) + 1;
    
        $receiptNumber =
            'RCP-' .
            now()->format('Y') .
            '-' .
            str_pad(
                $nextId,
                6,
                '0',
                STR_PAD_LEFT
            );
    
        /*
        |--------------------------------------------------------------------------
        | Create pending payment
        |--------------------------------------------------------------------------
        */
    
        $payment = FeePayment::create([
            'student_id' =>
                $student->id,
    
            'student_class_assignment_id' =>
                $assignment->id,
    
            'student_fee_account_id' =>
                $account->id,
    
            'amount' =>
                $amount,
    
            'penalty_amount' =>
                0,
    
            'discount_amount' =>
                0,
    
            'net_amount' =>
                $amount,
    
            'payment_method' =>
                'paystack',
    
            'payment_date' =>
                now()->toDateString(),
    
            'status' =>
                'pending',
    
            'payment_type' =>
                $amount >= $balance
                    ? 'full'
                    : 'partial',
    
            'reference_number' =>
                $reference,
    
            'transaction_id' =>
                null,
    
            'notes' =>
                $validated['notes']
                    ?? 'Student portal Paystack payment',
    
            'recorded_by' =>
                'student',
    
            'metadata' =>
                [
                    'student_id' =>
                        $student->id,
    
                    'student_class_assignment_id' =>
                        $assignment->id,
    
                    'student_fee_account_id' =>
                        $account->id,
    
                    'phone' =>
                        $validated['phone'] ?? null,
    
                    'network' =>
                        $validated['network'] ?? null,
    
                    'initiated_from' =>
                        'student_portal',
    
                    'gateway' =>
                        'paystack',
    
                    'paystack_reference' =>
                        $reference,
    
                    'initiated_at' =>
                        now()->toDateTimeString(),
                ],
    
            'receipt_number' =>
                $receiptNumber,
        ]);
    
        /*
        |--------------------------------------------------------------------------
        | Initialize Paystack transaction
        |--------------------------------------------------------------------------
        |
        | Paystack expects GHS in pesewas.
        | Example:
        |
        | GHS 1,000.00 = 100000
        |
        */
    
        $response = Http::withToken(
            config('services.paystack.secret_key')
        )
            ->acceptJson()
            ->post(
                'https://api.paystack.co/transaction/initialize',
                [
                    'email' =>
                        $student->father_email
                        ?? $student->mother_email
                        ?? $student->guardian_email
                        ?? 'student' . $student->id . '@edunexus.local',
    
                    'amount' =>
                        (int) round($amount * 100),
    
                    'currency' =>
                        'GHS',
    
                    'reference' =>
                        $reference,
    
                    'channels' =>
                        [
                            'card',
                            'mobile_money',
                            'bank',
                            'ussd',
                            'qr',
                        ],
    
                    'callback_url' =>
                        route('students.fees.payment.callback'),
    
                    'metadata' =>
                        [
                            'payment_id' =>
                                $payment->id,
    
                            'student_id' =>
                                $student->id,
    
                            'student_fee_account_id' =>
                                $account->id,
    
                            'receipt_number' =>
                                $receiptNumber,
    
                            'student_reference' =>
                                $student->student_id,
                        ],
                ]
            );
    
        /*
        |--------------------------------------------------------------------------
        | Handle Paystack initialization failure
        |--------------------------------------------------------------------------
        */
    
        if (!$response->successful() || !$response->json('status')) {
    
            $payment->update([
                'status' => 'failed',
    
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'paystack_error' =>
                            $response->json('message')
                            ?? 'Unable to initialize Paystack transaction.',
    
                        'paystack_response' =>
                            $response->json(),
    
                        'failed_at' =>
                            now()->toDateTimeString(),
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
    
        /*
        |--------------------------------------------------------------------------
        | Save Paystack transaction information
        |--------------------------------------------------------------------------
        */
    
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
    
        /*
        |--------------------------------------------------------------------------
        | Redirect student to Paystack Checkout
        |--------------------------------------------------------------------------
        */
    
        return redirect()->away(
            $response->json('data.authorization_url')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    */

    public function receipt($id)
    {
        $student = Auth::guard('student')->user();

        /*
        |--------------------------------------------------------------------------
        | Only allow this student to access their own payment
        |--------------------------------------------------------------------------
        */

        $payment = FeePayment::with([
            'receipt',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
        ])
            ->where('student_id', $student->id)
            ->where('id', $id)
            ->firstOrFail();

        return view(
            'students.fees.receipt',
            compact(
                'student',
                'payment'
            )
        );
    }

    public function paymentCallback(Request $request)
    {
        $student = Auth::guard('student')->user();
    
        $reference = $request->query('reference');
    
        if (!$reference) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'No Paystack transaction reference was received.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Find our pending payment
        |--------------------------------------------------------------------------
        */
    
        $payment = FeePayment::where(
            'reference_number',
            $reference
        )
            ->where(
                'student_id',
                $student->id
            )
            ->first();
    
        if (!$payment) {
            return redirect()
                ->route('students.fees')
                ->with(
                    'error',
                    'Payment transaction could not be found.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Prevent double processing
        |--------------------------------------------------------------------------
        */
    
        if ($payment->status === 'completed') {
            return redirect()
                ->route('students.fees')
                ->with(
                    'success',
                    'Your payment has already been confirmed.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Verify transaction with Paystack
        |--------------------------------------------------------------------------
        */
    
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
    
        /*
        |--------------------------------------------------------------------------
        | Verify amount
        |--------------------------------------------------------------------------
        */
    
        $expectedAmount = (int) round(
            ((float) $payment->net_amount) * 100
        );
    
        $paidAmount = (int) (
            $transaction['amount'] ?? 0
        );
    
        if ($paidAmount !== $expectedAmount) {
    
            $payment->update([
                'status' => 'failed',
    
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'verification_error' =>
                            'Paystack amount mismatch',
    
                        'expected_amount' =>
                            $expectedAmount,
    
                        'received_amount' =>
                            $paidAmount,
    
                        'paystack_response' =>
                            $transaction,
    
                        'verified_at' =>
                            now()->toDateTimeString(),
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
    
        /*
        |--------------------------------------------------------------------------
        | Successful payment
        |--------------------------------------------------------------------------
        */
    
        if (($transaction['status'] ?? null) === 'success') {
    
            $payment->update([
                'status' =>
                    'completed',
    
                'transaction_id' =>
                    (string) (
                        $transaction['id']
                        ?? $payment->transaction_id
                    ),
    
                'reference_number' =>
                    $transaction['reference']
                    ?? $reference,
    
                'payment_method' =>
                    'paystack',
    
                'payment_date' =>
                    isset($transaction['paid_at'])
                        ? \Carbon\Carbon::parse(
                            $transaction['paid_at']
                        )->toDateString()
                        : now()->toDateString(),
    
                'metadata' =>
                    array_merge(
                        $payment->metadata ?? [],
                        [
                            'paystack_status' =>
                                $transaction['status'],
    
                            'paystack_channel' =>
                                $transaction['channel'] ?? null,
    
                            'gateway_response' =>
                                $transaction['gateway_response']
                                ?? null,
    
                            'paid_at' =>
                                $transaction['paid_at']
                                ?? null,
    
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
    
        /*
        |--------------------------------------------------------------------------
        | Payment not successful
        |--------------------------------------------------------------------------
        */
    
        $payment->update([
            'status' => 'failed',
    
            'metadata' => array_merge(
                $payment->metadata ?? [],
                [
                    'paystack_status' =>
                        $transaction['status'] ?? null,
    
                    'gateway_response' =>
                        $transaction['gateway_response']
                        ?? null,
    
                    'verified_at' =>
                        now()->toDateTimeString(),
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

        /*
|--------------------------------------------------------------------------
| STUDENT RECEIPT PDF
|--------------------------------------------------------------------------
*/

    public function receiptPdf($id)
    {
        $student = Auth::guard('student')->user();

        $payment = FeePayment::with([
            'receipt',
            'studentFeeAccount',
            'studentClassAssignment.studentClass',
            'studentClassAssignment.academicYear',
        ])
            ->where('student_id', $student->id)
            ->where('id', $id)
            ->where('status', 'completed')
            ->firstOrFail();

        $filename = 'Receipt-' .
            ($payment->receipt_number ?? $payment->id) .
            '.pdf';

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
}