<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SchoolFeeStructure;
use App\Models\StudentInvoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceService
{
    public function generate($studentId, $academicYearId, $termId)
    {
        $student = Student::findOrFail($studentId);

        // Prevent duplicate invoice (important ERP rule)
        $existing = StudentInvoice::where([
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'term_id' => $termId,
        ])->first();

        if ($existing) {
            return $existing;
        }

        // Create invoice header
        $invoice = StudentInvoice::create([
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'term_id' => $termId,
            'student_class_id' => $student->student_class_id,
            'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance' => 0,
            'status' => 'pending',
            'generated_at' => Carbon::now(),
        ]);

        // Fetch fee structures for class + term + year
        $structures = SchoolFeeStructure::where([
            'academic_year_id' => $academicYearId,
            'term_id' => $termId,
            'student_class_id' => $student->student_class_id,
            'is_active' => true,
        ])->get();

        $total = 0;

        // Create invoice breakdown items
        foreach ($structures as $structure) {

            InvoiceItem::create([
                'student_invoice_id' => $invoice->id,
                'fee_category_id' => $structure->fee_category_id,
                'description' => null,
                'amount' => $structure->amount,
            ]);

            $total += $structure->amount;
        }

        // Update totals
        $invoice->update([
            'total_amount' => $total,
            'balance' => $total,
            'status' => $total > 0 ? 'pending' : 'paid',
        ]);

        return $invoice;
    }
}