<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST ALL PAYMENTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $payments = Payment::with([
                'student',
                'studentFee',
                'academicYear',
                'term'
            ])
            ->latest()
            ->get();

        return view('payments.index', compact('payments'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $students = Student::with('studentFees')
            ->orderBy('first_name')
            ->get();

        return view('payments.create', compact('students'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount_paid'    => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | GET STUDENT FEE RECORD
        |--------------------------------------------------------------------------
        */
        $studentFee = StudentFee::findOrFail($request->student_fee_id);

        /*
        |--------------------------------------------------------------------------
        | PREVENT OVERPAYMENT
        |--------------------------------------------------------------------------
        */
        if ($request->amount_paid > $studentFee->balance) {
            return back()
                ->with('error', 'Payment exceeds remaining balance.')
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE PAYMENT RECORD
        |--------------------------------------------------------------------------
        */
        $payment = Payment::create([
            'student_fee_id'   => $studentFee->id,
            'student_id'       => $studentFee->student_id,
            'academic_year_id' => $studentFee->academic_year_id,
            'term_id'          => $studentFee->term_id,
            'amount_paid'      => $request->amount_paid,
            'payment_method'   => $request->payment_method,
            'receipt_number'   => 'RCPT-' . strtoupper(Str::random(8)),
            'reference_number' => $request->reference_number ?? null,
            'received_by'      => auth()->id(),
            'payment_date'     => $request->payment_date,
            'notes'            => $request->notes,
        ]);

        /*
        |--------------------------------------------------------------------------
        | RECALCULATE TOTAL PAID
        |--------------------------------------------------------------------------
        */
        $totalPaid = $studentFee->payments()->sum('amount_paid');

        /*
        |--------------------------------------------------------------------------
        | RECALCULATE BALANCE
        |--------------------------------------------------------------------------
        */
        $balance = $studentFee->total_fee - $totalPaid;

        /*
        |--------------------------------------------------------------------------
        | PAYMENT STATUS
        |--------------------------------------------------------------------------
        */
        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($totalPaid < $studentFee->total_fee) {
            $status = 'partial';
        } else {
            $status = 'paid';
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE STUDENT FEE
        |--------------------------------------------------------------------------
        */
        $studentFee->update([
            'amount_paid'    => $totalPaid,
            'balance'        => $balance,
            'payment_status' => $status,
        ]);

        /*
        |--------------------------------------------------------------------------
        | REDIRECT SUCCESS
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW SINGLE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit(Payment $payment)
    {
        // Load relationships
        $payment->load(['student', 'studentFee.feeStructure']);
        
        // Get students for dropdown (if needed for editing)
        $students = Student::orderBy('first_name')->get();
        
        // Get fee structures (avoid 'name' column issue)
        $feeStructures = FeeStructure::all();
        
        return view('payments.edit', compact('payment', 'students', 'feeStructures'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Payment $payment)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'amount_paid'    => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | GET ASSOCIATED STUDENT FEE RECORD
        |--------------------------------------------------------------------------
        */
        $studentFee = $payment->studentFee;

        /*
        |--------------------------------------------------------------------------
        | CALCULATE NEW TOTALS
        |--------------------------------------------------------------------------
        */
        // Get sum of all other payments (excluding this one)
        $otherPaymentsTotal = $studentFee->payments()
            ->where('id', '!=', $payment->id)
            ->sum('amount_paid');
        
        // New total paid after update
        $newTotalPaid = $otherPaymentsTotal + $request->amount_paid;

        /*
        |--------------------------------------------------------------------------
        | PREVENT OVERPAYMENT
        |--------------------------------------------------------------------------
        */
        if ($newTotalPaid > $studentFee->total_fee) {
            return back()
                ->with('error', 'Payment amount would exceed total fee.')
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYMENT RECORD
        |--------------------------------------------------------------------------
        */
        $payment->update([
            'amount_paid'    => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'payment_date'   => $request->payment_date,
            'notes'          => $request->notes,
        ]);

        /*
        |--------------------------------------------------------------------------
        | RECALCULATE STUDENT FEE TOTALS
        |--------------------------------------------------------------------------
        */
        $totalPaid = $studentFee->payments()->sum('amount_paid');
        $balance = $studentFee->total_fee - $totalPaid;

        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYMENT STATUS
        |--------------------------------------------------------------------------
        */
        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($totalPaid < $studentFee->total_fee) {
            $status = 'partial';
        } else {
            $status = 'paid';
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE STUDENT FEE
        |--------------------------------------------------------------------------
        */
        $studentFee->update([
            'amount_paid'    => $totalPaid,
            'balance'        => $balance,
            'payment_status' => $status,
        ]);

        /*
        |--------------------------------------------------------------------------
        | REDIRECT SUCCESS
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function destroy(Payment $payment)
    {
        $studentFee = $payment->studentFee;

        $payment->delete();

        $totalPaid = $studentFee->payments()->sum('amount_paid');
        $balance = $studentFee->total_fee - $totalPaid;

        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($totalPaid < $studentFee->total_fee) {
            $status = 'partial';
        } else {
            $status = 'paid';
        }

        $studentFee->update([
            'amount_paid'    => $totalPaid,
            'balance'        => $balance,
            'payment_status' => $status,
        ]);

        return back()->with('success', 'Payment deleted successfully.');
    }
}