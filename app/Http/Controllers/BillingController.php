<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\InvoiceItem;
use App\Models\SchoolFeeStructure;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    public function index()
    {
        $invoices = StudentInvoice::with('student')->latest()->get();
        return view('billing.index', compact('invoices'));
    }

    public function create()
    {
        return view('billing.create', [
            'students' => Student::all(),
            'academicYears' => AcademicYear::all(),
            'terms' => Term::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        try {
            DB::beginTransaction();

            $student = Student::findOrFail($request->student_id);

            $classAssignment = StudentClassAssignment::where('student_id', $student->id)
                ->where('is_current', true)
                ->first();

            if (!$classAssignment) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Student has no active class assignment.')->withInput();
            }

            $classId = $classAssignment->student_class_id;

            $fees = SchoolFeeStructure::where('student_class_id', $classId)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('term_id', $request->term_id)
                ->where('is_active', true)
                ->get();

            if ($fees->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No fee structure found for this class.')->withInput();
            }

            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            
            $invoice = StudentInvoice::create([
                'student_id' => $student->id,
                'academic_year_id' => $request->academic_year_id,
                'term_id' => $request->term_id,
                'student_class_id' => $classId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'total_amount' => 0,
                'balance' => 0,
                'amount_paid' => 0,
            ]);

            $total = 0;

            foreach ($fees as $fee) {
                InvoiceItem::create([
                    'student_invoice_id' => $invoice->id,
                    'fee_category_id' => $fee->fee_category_id,
                    'description' => $fee->description ?? $fee->name ?? 'School Fee',
                    'amount' => $fee->amount,
                ]);
                $total += $fee->amount;
            }

            $invoice->update([
                'total_amount' => $total,
                'balance' => $total,
            ]);

            DB::commit();

            return redirect()
                ->route('billing.index')
                ->with('success', 'Invoice generated successfully. Invoice #: ' . $invoiceNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Invoice creation failed: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error creating invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(StudentInvoice $billing)
    {
        $billing->load('student', 'items');
        return view('billing.show', compact('billing'));
    }

    public function edit(StudentInvoice $billing)
    {
        return view('billing.edit', compact('billing'));
    }

    public function update(Request $request, StudentInvoice $billing)
    {
        $request->validate([
            'status' => 'required|string',
            'total_amount' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'balance' => 'required|numeric',
        ]);

        $billing->update([
            'status' => $request->status,
            'total_amount' => $request->total_amount,
            'amount_paid' => $request->amount_paid,
            'balance' => $request->balance,
        ]);

        return redirect()
            ->route('billing.index')
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(StudentInvoice $billing)
    {
        InvoiceItem::where('student_invoice_id', $billing->id)->delete();
        $billing->delete();

        return redirect()
            ->route('billing.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    // PDF DOWNLOAD - FIXED VERSION
    public function pdf(StudentInvoice $billing)
    {
        try {
            // Load relationships
            $billing->load(['student', 'items']);
            
            // Load academic year and term
            $academicYear = AcademicYear::find($billing->academic_year_id);
            $term = Term::find($billing->term_id);
            
            // Prepare data for PDF
            $data = [
                'invoice' => $billing,
                'billing' => $billing,
                'student' => $billing->student,
                'items' => $billing->items,
                'academicYear' => $academicYear,
                'term' => $term,
                'company' => [
                    'name' => 'KABORE SCHOOL',
                    'address' => '123 Education Street, Accra, Ghana',
                    'phone' => '+233 123 456 789',
                    'email' => 'info@kaboreschool.com',
                ]
            ];
            
            // Generate PDF
            $pdf = Pdf::loadView('billing.pdf', $data);
            
            // Set paper size
            $pdf->setPaper('A4', 'portrait');
            
            // Download PDF
            return $pdf->download('invoice_' . $billing->invoice_number . '_' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }
    
    // View PDF in browser (stream)
    public function viewPdf(StudentInvoice $billing)
    {
        try {
            $billing->load(['student', 'items']);
            
            $academicYear = AcademicYear::find($billing->academic_year_id);
            $term = Term::find($billing->term_id);
            
            $data = [
                'invoice' => $billing,
                'billing' => $billing,
                'student' => $billing->student,
                'items' => $billing->items,
                'academicYear' => $academicYear,
                'term' => $term,
                'company' => [
                    'name' => 'KABORE SCHOOL',
                    'address' => '123 Education Street, Accra, Ghana',
                    'phone' => '+233 123 456 789',
                    'email' => 'info@kaboreschool.com',
                ]
            ];
            
            $pdf = Pdf::loadView('billing.pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->stream('invoice_' . $billing->invoice_number . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }
}