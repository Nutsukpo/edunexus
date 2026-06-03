<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\InvoiceItem;
use App\Models\SchoolFeeStructure;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    // LIST INVOICES
    public function index()
    {
        $invoices = StudentInvoice::with('student')
            ->latest()
            ->get();

        return view('billing.index', compact('invoices'));
    }

    // CREATE FORM
    public function create()
    {
        return view('billing.create', [
            'students' => Student::all(),
            'academicYears' => AcademicYear::all(),
            'terms' => Term::all(),
        ]);
    }

    // GENERATE INVOICE
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required',
            'term_id' => 'required',
        ]);

        $student = Student::findOrFail($request->student_id);

        $fees = SchoolFeeStructure::where('student_class_id', $student->student_class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('term_id', $request->term_id)
            ->where('is_active', true)
            ->get();

        $invoice = StudentInvoice::create([
            'student_id' => $student->id,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
            'student_class_id' => $student->student_class_id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(6)),
        ]);

        $total = 0;

        foreach ($fees as $fee) {
            InvoiceItem::create([
                'student_invoice_id' => $invoice->id,
                'fee_category_id' => $fee->fee_category_id,
                'description' => $fee->name,
                'amount' => $fee->amount,
            ]);

            $total += $fee->amount;
        }

        $invoice->update([
            'total_amount' => $total,
            'balance' => $total,
        ]);

        return redirect()
            ->route('billing.index')
            ->with('success', 'Invoice generated successfully.');
    }
}