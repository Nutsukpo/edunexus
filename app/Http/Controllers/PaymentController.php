<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\StudentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['student', 'invoice', 'receiver'])
            ->latest()
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $invoices = StudentInvoice::with('student')
            ->where('balance', '>', 0)
            ->get();

        return view('payments.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'student_invoice_id' => 'required|exists:student_invoices,id',
            'student_id' => 'required|exists:students,id',
            'receipt_number' => 'required|string|unique:payments,receipt_number',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank Transfer,Mobile Money,Cheque,POS',
            'reference_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'received_by' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            // Get the invoice
            $invoice = StudentInvoice::findOrFail($request->student_invoice_id);

            // Check if amount exceeds balance
            if ($request->amount > $invoice->balance) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Payment amount (GH₵ ' . number_format($request->amount, 2) . 
                           ') cannot exceed invoice balance (GH₵ ' . number_format($invoice->balance, 2) . ').');
            }

            // Create the payment
            $payment = Payment::create([
                'student_invoice_id' => $invoice->id,
                'student_id' => $request->student_id,
                'receipt_number' => $request->receipt_number,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'remarks' => $request->remarks,
                'received_by' => $request->received_by,
            ]);

            // Update invoice amounts - FIXED: Use 'amount_paid' not 'paid_amount'
            $invoice->amount_paid = ($invoice->amount_paid ?? 0) + $payment->amount;
            $invoice->balance = $invoice->total_amount - $invoice->amount_paid;

            // Update invoice status based on new balance
            if ($invoice->balance <= 0) {
                $invoice->status = 'Paid';
            } elseif ($invoice->amount_paid > 0) {
                $invoice->status = 'Partially Paid';
            }
            // If amount_paid is 0, keep existing status (usually 'Unpaid')

            $invoice->save();

            DB::commit();

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Payment received successfully. Receipt #: ' . $request->receipt_number);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function show(Payment $payment)
    {
        $payment->load(['student', 'invoice', 'receiver']);
        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        DB::beginTransaction();

        try {
            $invoice = $payment->invoice;
            
            // Reverse the payment - FIXED: Use 'amount_paid' not 'paid_amount'
            $invoice->amount_paid -= $payment->amount;
            $invoice->balance = $invoice->total_amount - $invoice->amount_paid;

            // Update invoice status based on new balance
            if ($invoice->amount_paid <= 0) {
                $invoice->status = 'Unpaid';
            } elseif ($invoice->balance > 0 && $invoice->amount_paid > 0) {
                $invoice->status = 'Partially Paid';
            } elseif ($invoice->balance <= 0) {
                $invoice->status = 'Paid';
            }

            $invoice->save();
            
            // Delete the payment record
            $payment->delete();

            DB::commit();

            return redirect()
                ->route('payments.index')
                ->with('success', 'Payment reversed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment reversal error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error reversing payment: ' . $e->getMessage());
        }
    }

    // Optional: Add method to view payment receipt
    public function receipt(Payment $payment)
    {
        $payment->load(['student', 'invoice', 'receiver']);
        return view('payments.receipt', compact('payment'));
    }

    // Optional: Add method to get student payment history
    public function studentPayments($studentId)
    {
        $payments = Payment::with(['invoice'])
            ->where('student_id', $studentId)
            ->latest()
            ->paginate(20);
            
        return view('payments.student-history', compact('payments', 'studentId'));
    }
}