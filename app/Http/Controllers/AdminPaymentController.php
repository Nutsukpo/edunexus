<?php
// app/Http/Controllers/AdminPaymentController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentFeeAllocation;
use App\Models\BillSheet;
use App\Models\BillSheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['student', 'studentFeeAllocation.feeStructure', 'receivedBy'])
            ->orderBy('payment_date', 'desc');

        // Filters
        if ($request->has('student_id') && $request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->paginate(20);
        $students = Student::all();
        $statuses = ['pending', 'completed', 'failed', 'refunded', 'partial'];

        // Summary statistics
        $totalPayments = Payment::where('status', 'completed')->sum('paid_amount');
        $todayPayments = Payment::where('status', 'completed')
            ->whereDate('payment_date', today())
            ->sum('paid_amount');
        $pendingPayments = Payment::where('status', 'pending')->count();
        $totalStudents = Student::count();
        $totalWithBalance = StudentFeeAllocation::where('balance', '>', 0)
            ->distinct('student_id')
            ->count();

        return view('admin.payments.index', compact(
            'payments', 'students', 'statuses',
            'totalPayments', 'todayPayments', 'pendingPayments',
            'totalStudents', 'totalWithBalance'
        ));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $studentId = $request->student_id;
        $allocationId = $request->allocation_id;
        $billId = $request->bill_id;
        
        $students = Student::all();
        
        $selectedAllocation = null;
        $selectedBill = null;

        // Get allocations for the student if selected
        $allocations = collect();
        if ($studentId) {
            $allocations = StudentFeeAllocation::with(['feeStructure', 'academicYear', 'term'])
                ->where('student_id', $studentId)
                ->where('balance', '>', 0)
                ->get()
                ->map(function($allocation) {
                    return [
                        'id' => $allocation->id,
                        'label' => ($allocation->feeStructure->name ?? 'Fee Item') . ' - Balance: ₦' . number_format($allocation->balance, 2),
                        'balance' => $allocation->balance,
                        'total_amount' => $allocation->total_amount,
                        'paid_amount' => $allocation->paid_amount,
                    ];
                });
        }

        if ($allocationId) {
            $selectedAllocation = StudentFeeAllocation::with(['feeStructure', 'student'])
                ->find($allocationId);
        }

        if ($billId) {
            $selectedBill = BillSheet::with(['student', 'academicYear', 'term'])
                ->where('student_id', $studentId)
                ->where('balance', '>', 0)
                ->find($billId);
        }

        return view('admin.payments.create', compact(
            'students', 'allocations', 'selectedAllocation', 
            'studentId', 'selectedBill', 'billId'
        ));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'fee_allocation_id' => 'nullable|exists:student_fee_allocations,id',
            'bill_sheet_id' => 'nullable|exists:bill_sheets,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,card,online,cheque,other,mobile_money,pos',
            'payment_channel' => 'nullable|in:bank,online,mobile,pos,cash',
            'reference_number' => 'nullable|string|max:255',
            'payment_details' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate that we have either fee_allocation_id or bill_sheet_id
        if (!$request->fee_allocation_id && !$request->bill_sheet_id) {
            return back()->with('error', 'Please select either a fee allocation or a bill to pay.')->withInput();
        }

        DB::beginTransaction();

        try {
            $allocation = null;
            $bill = null;
            $maxAmount = 0;

            if ($request->fee_allocation_id) {
                $allocation = StudentFeeAllocation::find($request->fee_allocation_id);
                if (!$allocation) {
                    throw new \Exception('Fee allocation not found.');
                }
                if ($allocation->student_id != $request->student_id) {
                    throw new \Exception('Fee allocation does not belong to the selected student.');
                }
                if ($request->amount > $allocation->balance) {
                    throw new \Exception('Payment amount cannot exceed the balance of ₦' . number_format($allocation->balance, 2));
                }
                $maxAmount = $allocation->balance;
            }

            if ($request->bill_sheet_id) {
                $bill = BillSheet::find($request->bill_sheet_id);
                if (!$bill) {
                    throw new \Exception('Bill sheet not found.');
                }
                if ($bill->student_id != $request->student_id) {
                    throw new \Exception('Bill sheet does not belong to the selected student.');
                }
                if ($request->amount > $bill->balance) {
                    throw new \Exception('Payment amount cannot exceed the bill balance of ₦' . number_format($bill->balance, 2));
                }
                $maxAmount = $bill->balance;
            }

            // Create payment
            $payment = Payment::create([
                'student_id' => $request->student_id,
                'student_fee_allocation_id' => $request->fee_allocation_id,
                'bill_sheet_id' => $request->bill_sheet_id,
                'invoice_number' => Payment::generateInvoiceNumber(),
                'amount' => $request->amount,
                'paid_amount' => $request->amount,
                'balance' => $maxAmount - $request->amount,
                'payment_method' => $request->payment_method,
                'payment_channel' => $request->payment_channel,
                'reference_number' => $request->reference_number,
                'payment_details' => $request->payment_details,
                'status' => 'completed',
                'payment_date' => $request->payment_date,
                'received_by' => Auth::id(),
                'notes' => $request->notes,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'transaction_id' => $request->transaction_id,
            ]);

            // Update allocation balance
            if ($allocation) {
                $allocation->paid_amount += $request->amount;
                $allocation->balance = $allocation->total_amount - $allocation->paid_amount;
                $allocation->save();
                
                // Update allocation status
                if (method_exists($allocation, 'updateStatus')) {
                    $allocation->updateStatus();
                }
            }

            // Update bill balance
            if ($bill) {
                $bill->paid_amount = ($bill->paid_amount ?? 0) + $request->amount;
                $bill->balance = $bill->net_amount - $bill->paid_amount;
                $bill->save();
                
                // Update bill status
                if (method_exists($bill, 'updateStatus')) {
                    $bill->updateStatus();
                } else {
                    // Manual status update
                    if ($bill->balance <= 0) {
                        $bill->status = 'paid';
                        $bill->save();
                    } elseif ($bill->paid_amount > 0) {
                        $bill->status = 'partial';
                        $bill->save();
                    }
                }
            }

            DB::commit();

            // Generate receipt
            $this->generateReceipt($payment);

            return redirect()->route('admin.payments.show', $payment->id)
                ->with('success', "Payment recorded successfully. Invoice: {$payment->invoice_number}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with([
            'student',
            'studentFeeAllocation.feeStructure',
            'billSheet',
            'receivedBy'
        ])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be edited.');
        }

        $students = Student::all();
        $paymentMethods = ['cash', 'bank_transfer', 'card', 'online', 'cheque', 'other', 'mobile_money', 'pos'];
        $statuses = ['pending', 'completed', 'failed', 'refunded'];

        return view('admin.payments.edit', compact('payment', 'students', 'paymentMethods', 'statuses'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be updated.');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,card,online,cheque,other,mobile_money,pos',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $oldAmount = $payment->paid_amount;
            $newAmount = $request->amount;
            $diff = $newAmount - $oldAmount;

            // Update payment
            $payment->update([
                'amount' => $request->amount,
                'paid_amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            // Update allocation balance if status is completed
            if ($request->status === 'completed' && $payment->studentFeeAllocation) {
                $allocation = $payment->studentFeeAllocation;
                $allocation->paid_amount += $diff;
                $allocation->balance = $allocation->total_amount - $allocation->paid_amount;
                $allocation->save();
                
                if (method_exists($allocation, 'updateStatus')) {
                    $allocation->updateStatus();
                }
            }

            // Update bill balance if status is completed
            if ($request->status === 'completed' && $payment->billSheet) {
                $bill = $payment->billSheet;
                $bill->paid_amount += $diff;
                $bill->balance = $bill->net_amount - $bill->paid_amount;
                $bill->save();
                
                if (method_exists($bill, 'updateStatus')) {
                    $bill->updateStatus();
                } else {
                    if ($bill->balance <= 0) {
                        $bill->status = 'paid';
                        $bill->save();
                    } elseif ($bill->paid_amount > 0) {
                        $bill->status = 'partial';
                        $bill->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.payments.show', $payment->id)
                ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment.
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status === 'completed') {
            return back()->with('error', 'Completed payments cannot be deleted.');
        }

        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Generate receipt for payment.
     */
    private function generateReceipt($payment)
    {
        try {
            $pdf = Pdf::loadView('admin.payments.receipt', compact('payment'));
            $filename = 'receipt-' . $payment->invoice_number . '.pdf';
            $path = storage_path('app/public/receipts/');
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $pdf->save($path . $filename);
            
            $payment->receipt_path = 'receipts/' . $filename;
            $payment->save();
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate receipt: ' . $e->getMessage());
        }
    }

    /**
     * Download payment receipt.
     */
    public function downloadReceipt($id)
    {
        $payment = Payment::findOrFail($id);
        
        if (!$payment->receipt_path || !file_exists(storage_path('app/public/' . $payment->receipt_path))) {
            $this->generateReceipt($payment);
            $payment->refresh();
        }
        
        return response()->download(storage_path('app/public/' . $payment->receipt_path));
    }

    /**
     * Get fee allocations for a student (AJAX).
     */
    public function getAllocations(Request $request)
    {
        $studentId = $request->student_id;
        
        if (!$studentId) {
            return response()->json([]);
        }
        
        try {
            $allocations = StudentFeeAllocation::with(['feeStructure'])
                ->where('student_id', $studentId)
                ->where('balance', '>', 0)
                ->get();
            
            $result = $allocations->map(function($allocation) {
                $feeStructureName = $allocation->feeStructure ? $allocation->feeStructure->name : 'Fee Item';
                return [
                    'id' => $allocation->id,
                    'label' => $feeStructureName . ' - Balance: ₦' . number_format($allocation->balance, 2),
                    'balance' => $allocation->balance,
                    'total_amount' => $allocation->total_amount,
                    'paid_amount' => $allocation->paid_amount,
                ];
            });
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching allocations: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get bills for a student (AJAX).
     */
    public function getBills(Request $request)
    {
        $studentId = $request->student_id;
        
        if (!$studentId) {
            return response()->json([]);
        }
        
        try {
            $bills = BillSheet::where('student_id', $studentId)
                ->where('balance', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->get()
                ->map(function($bill) {
                    return [
                        'id' => $bill->id,
                        'invoice_number' => $bill->bill_number,
                        'bill_number' => $bill->bill_number,
                        'label' => $bill->bill_number . ' - Balance: ₦' . number_format($bill->balance, 2),
                        'balance' => $bill->balance,
                        'total_amount' => $bill->total_amount,
                        'net_amount' => $bill->net_amount,
                        'paid_amount' => $bill->paid_amount,
                        'status' => $bill->status,
                        'bill_date' => $bill->bill_date ? $bill->bill_date->format('Y-m-d') : null,
                        'due_date' => $bill->due_date ? $bill->due_date->format('Y-m-d') : null,
                    ];
                });
            
            \Log::info('Bills found for student ' . $studentId . ': ' . $bills->count());
            
            return response()->json($bills);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching bills: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch bills: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get student details (AJAX).
     */
    public function getStudentDetails($id)
    {
        try {
            $student = Student::findOrFail($id);
            
            // Get class from student_class_assignments
            $className = 'N/A';
            try {
                $classAssignment = \App\Models\StudentClassAssignment::with('studentClass')
                    ->where('student_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->first();
                
                if ($classAssignment && $classAssignment->studentClass) {
                    $className = $classAssignment->studentClass->name;
                    if ($classAssignment->studentClass->section) {
                        $className .= ' - ' . $classAssignment->studentClass->section;
                    }
                }
            } catch (\Exception $e) {
                // Fallback to student's class field
                $className = $student->class ?? $student->current_class ?? 'N/A';
            }
            
            return response()->json([
                'id' => $student->id,
                'name' => ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''),
                'admission_number' => $student->admission_number ?? 'N/A',
                'class' => $className,
                'class_name' => $className,
                'email' => $student->email ?? 'N/A',
                'phone' => $student->phone ?? 'N/A',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching student details: ' . $e->getMessage());
            return response()->json(['error' => 'Student not found'], 404);
        }
    }

    /**
     * Get student balance (AJAX).
     */
    public function getStudentBalance($studentId)
    {
        try {
            $feeBalance = StudentFeeAllocation::where('student_id', $studentId)
                ->where('balance', '>', 0)
                ->sum('balance');
            
            $billBalance = BillSheet::where('student_id', $studentId)
                ->where('balance', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->sum('balance');
            
            $totalBalance = $feeBalance + $billBalance;
            
            return response()->json([
                'fee_balance' => $feeBalance,
                'bill_balance' => $billBalance,
                'total_balance' => $totalBalance
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching student balance: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch balance'], 500);
        }
    }
}