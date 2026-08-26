<?php

namespace App\Services;

use App\Models\Student;
use App\Models\BillSheet;
use App\Models\StudentFeePayment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Generate student fee payments from bill sheet
     */
    public function generateStudentPayments(BillSheet $billSheet)
    {
        $students = $billSheet->studentClass->students;
        $payments = [];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // Check if payment already exists
                $existing = StudentFeePayment::where([
                    'student_id' => $student->id,
                    'bill_sheet_id' => $billSheet->id,
                ])->first();

                if (!$existing) {
                    $payment = StudentFeePayment::create([
                        'student_id' => $student->id,
                        'bill_sheet_id' => $billSheet->id,
                        'academic_year_id' => $billSheet->academic_year_id,
                        'term_id' => $billSheet->term_id,
                        'amount_due' => $billSheet->net_amount,
                        'amount_paid' => 0,
                        'balance' => $billSheet->net_amount,
                        'discount_applied' => 0,
                        'payment_status' => 'pending',
                        'due_date' => $billSheet->due_date ?? now()->addDays(30),
                    ]);
                    
                    $payments[] = $payment;
                }
            }
            DB::commit();
            return $payments;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate student payments: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process student payment
     */
    public function processPayment(StudentFeePayment $payment, $amount, $paymentMethod, $userId)
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero.');
        }

        if ($amount > $payment->balance) {
            throw new \Exception('Payment amount exceeds balance.');
        }

        return $payment->applyPayment($amount, $paymentMethod, $userId);
    }

    /**
     * Bulk payment processing
     */
    public function processBulkPayment($studentIds, $amount, $paymentMethod, $userId)
    {
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                $payments = StudentFeePayment::where('student_id', $studentId)
                    ->where('payment_status', '!=', 'paid')
                    ->get();

                foreach ($payments as $payment) {
                    $remaining = $payment->balance;
                    $toPay = min($amount, $remaining);
                    
                    if ($toPay > 0) {
                        $this->processPayment($payment, $toPay, $paymentMethod, $userId);
                        $amount -= $toPay;
                        $results[] = [
                            'student_id' => $studentId,
                            'payment_id' => $payment->id,
                            'amount' => $toPay,
                            'status' => 'success'
                        ];
                    }

                    if ($amount <= 0) break;
                }
                
                if ($amount <= 0) break;
            }
            
            DB::commit();
            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate payment report
     */
    public function generatePaymentReport($academicYearId = null, $termId = null, $classId = null)
    {
        $query = StudentFeePayment::with(['student', 'student.studentClass']);
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        if ($termId) {
            $query->where('term_id', $termId);
        }
        
        if ($classId) {
            $query->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        return $query->get();
    }

    /**
     * Send payment reminders
     */
    public function sendPaymentReminders($daysBeforeDue = 7)
    {
        $dateThreshold = now()->addDays($daysBeforeDue);
        
        $payments = StudentFeePayment::where('payment_status', 'pending')
            ->whereDate('due_date', '<=', $dateThreshold)
            ->where('is_active', true)
            ->get();

        $sent = [];
        
        foreach ($payments as $payment) {
            // Send reminder logic (email, SMS, etc.)
            $this->sendReminder($payment);
            $sent[] = $payment->id;
        }
        
        return $sent;
    }

    /**
     * Send individual reminder
     */
    protected function sendReminder(StudentFeePayment $payment)
    {
        // Implement your notification logic here
        // Example: Email, SMS, WhatsApp, etc.
        
        // Create reminder record
        $payment->reminders()->create([
            'sent_by' => auth()->id() ?? 1,
            'reminder_type' => 'email',
            'message' => $this->generateReminderMessage($payment),
            'sent_at' => now(),
        ]);
    }

    /**
     * Generate reminder message
     */
    protected function generateReminderMessage(StudentFeePayment $payment)
    {
        return "Dear Parent/Guardian,\n\n" .
               "This is a reminder that the school fees for {$payment->student->name} " .
               "amounting to GHS {$payment->balance} is due on {$payment->due_date->format('d-m-Y')}.\n\n" .
               "Please make payment to avoid late fees.\n\n" .
               "Regards,\n" .
               "School Administration";
    }

    /**
     * Calculate late fees
     */
    public function calculateLateFees(StudentFeePayment $payment)
    {
        if (!$payment->isOverdue()) {
            return 0;
        }

        $daysOverdue = now()->diffInDays($payment->due_date);
        $lateFeeRate = 0.01; // 1% per day
        $lateFee = $payment->balance * $lateFeeRate * min($daysOverdue, 30); // Max 30 days

        return min($lateFee, $payment->balance * 0.1); // Max 10% of balance
    }

    /**
     * Apply late fees to overdue payments
     */
    public function applyLateFees()
    {
        $overduePayments = StudentFeePayment::overdue()
            ->where('payment_status', '!=', 'paid')
            ->get();

        foreach ($overduePayments as $payment) {
            $lateFee = $this->calculateLateFees($payment);
            if ($lateFee > 0) {
                $payment->update([
                    'late_fee' => $payment->late_fee + $lateFee,
                    'amount_due' => $payment->amount_due + $lateFee,
                    'balance' => $payment->balance + $lateFee,
                ]);
            }
        }

        return $overduePayments->count();
    }
}