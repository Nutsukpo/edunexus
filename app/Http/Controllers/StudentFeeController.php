<?php
// app/Http/Controllers/StudentFeeController.php

namespace App\Http\Controllers;

use App\Models\StudentFeeAllocation;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\FeeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentFeeController extends Controller
{
    public function index()
    {
        
        
        $feeAllocations = StudentFeeAllocation::with(['feeStructure', 'academicYear', 'term'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalBalance = $feeAllocations->sum('balance');
        $totalPaid = $feeAllocations->sum('paid_amount');
        $totalAmount = $feeAllocations->sum('total_amount');
        
        return view('admin.payments.index', compact(
            'feeAllocations', 'totalBalance', 'totalPaid', 'totalAmount'
        ));
    }

    public function show($id)
    {
        
        
        $allocation = StudentFeeAllocation::with(['feeStructure.feeItems', 'payments' => function($query) {
            $query->where('status', 'completed');
        }])
        ->where('student_id', $student->id)
        ->findOrFail($id);
        
        return view('admin.payments.show', compact('allocation'));
    }

    public function makePayment(Request $request, $id)
    {
        $student = Auth::guard('student')->user();
        
        $allocation = StudentFeeAllocation::with(['feeStructure.feeItems'])
            ->where('student_id', $student->id)
            ->findOrFail($id);
        
        if ($allocation->balance <= 0) {
            return back()->with('error', 'This fee has been fully paid.');
        }
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:' . $allocation->balance,
            'payment_method' => 'required|in:cash,bank_transfer,card,online,cheque,other',
            'reference_number' => 'nullable|string|max:255',
            'payment_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'pay_full' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $amount = $request->pay_full ? $allocation->balance : $request->amount;
            
            // Create payment
            $payment = Payment::create([
                'student_id' => $student->id,
                'student_fee_allocation_id' => $allocation->id,
                'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(uniqid()),
                'amount' => $amount,
                'paid_amount' => $amount,
                'balance' => $allocation->balance - $amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'payment_details' => $request->payment_details,
                'status' => 'completed',
                'payment_date' => now(),
                'received_by' => null, // Student self-payment
                'notes' => $request->notes,
            ]);

            // Create payment items (optional - can be used for detailed breakdown)
            // This is a simplified version
            
            // Update allocation
            $allocation->paid_amount += $amount;
            $allocation->save();
            $allocation->updateStatus();

            DB::commit();

            // Generate receipt
            $this->generateReceipt($payment);

            return redirect()->route('student.fees.show', $allocation->id)
                ->with('success', 'Payment of ₦' . number_format($amount, 2) . ' completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function paymentHistory()
    {
        
        
        $payments = Payment::with(['studentFeeAllocation.feeStructure'])
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->paginate(15);
        
        return view('admin.payments.payments', compact('payments'));
    }

    public function downloadReceipt($id)
    {
        $student = Auth::guard('student')->user();
        
        $payment = Payment::with(['student', 'studentFeeAllocation.feeStructure'])
            ->where('student_id', $student->id)
            ->findOrFail($id);
        
        // Generate PDF receipt
        $pdf = PDF::loadView('admin.payments.receipt', compact('payment'));
        
        return $pdf->download('receipt-' . $payment->invoice_number . '.pdf');
    }

    private function generateReceipt($payment)
    {
        // Optional: Generate receipt file or send email
        // This can be implemented based on requirements
    }
}