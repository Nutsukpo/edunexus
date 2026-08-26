<?php
// app/Http/Controllers/FeePaymentReportController.php

namespace App\Http\Controllers;

use App\Models\StudentFeeAccount;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FeePaymentReportController extends Controller
{
    /**
     * Display fee payment reports index with filters
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get filter parameters with defaults
        $filters = $this->getFilterParameters($request);
        
        // Get filter data for dropdowns
        $filterOptions = $this->getFilterOptions();
        
        // Build query with filters
        $query = $this->buildReportQuery($filters);
        
        // Get summary statistics
        $summary = $this->calculateSummary($query);
        
        // Get report data based on report type
        $reportData = $this->getReportData($query, $filters['report_type']);
        
        // Get payment summary
        $paymentSummary = $this->getPaymentSummary($filters);
        
        // Prepare view data
        $viewData = array_merge(
            $filters,
            $filterOptions,
            [
                'reports' => $reportData,
                'summary' => $summary,
                'paymentSummary' => $paymentSummary,
            ]
        );
        
        return view('fee-payments.reports.index', $viewData);
    }

    /**
     * Display detailed view of a specific fee account
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $feeAccount = StudentFeeAccount::with([
            'student',
            'studentClass',
            'academicYear',
            'payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            },
            'feeItems'
        ])->findOrFail($id);
        
        $paymentHistory = $feeAccount->payments;
        $totalPaid = $paymentHistory->sum('amount');
        $balance = $feeAccount->balance;
        
        return view('fee-payments.reports.show', compact(
            'feeAccount',
            'paymentHistory',
            'totalPaid',
            'balance'
        ));
    }

    /**
     * Export report as PDF
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        // Validate export request
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'class_id' => 'nullable|exists:student_classes,id',
            'status' => 'nullable|in:paid,partial,pending',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get filters
        $filters = $this->getFilterParameters($request);
        
        // Get data for export
        $data = $this->getExportData($filters);
        
        // Generate summary for PDF
        $summary = $this->calculateSummaryForExport($data);
        
        // Load PDF view
        $pdf = PDF::loadView('fee-payments.reports.pdf', [
            'data' => $data,
            'filters' => $filters,
            'summary' => $summary,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'System',
        ]);

        // Configure PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        // Generate filename
        $filename = 'fee-payment-report-' . date('Y-m-d-H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export report as Excel
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportExcel(Request $request)
    {
        // Get filters
        $filters = $this->getFilterParameters($request);
        
        // Get data for export
        $data = $this->getExportData($filters);
        
        // For now, return a CSV download
        return $this->exportCsv($data, $filters);
    }

    /**
     * Export as CSV
     * 
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @param array $filters
     * @return \Illuminate\Http\Response
     */
    private function exportCsv($data, $filters)
    {
        $filename = 'fee-payment-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($handle, [
                'Student',
                'Class',
                'Academic Year',
                'Total Fees',
                'Amount Paid',
                'Balance',
                'Discount',
                'Waiver',
                'Status',
                'Payment Date'
            ]);
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->student->full_name ?? $row->student->first_name . ' ' . $row->student->last_name ?? 'N/A',
                    $row->studentClass->class_name ?? 'N/A',
                    $row->academicYear->year_name ?? 'N/A',
                    number_format($row->total_fees, 2),
                    number_format($row->amount_paid, 2),
                    number_format($row->balance, 2),
                    number_format($row->discount_applied, 2),
                    number_format($row->waiver_amount, 2),
                    ucfirst($row->status),
                    $row->created_at ? $row->created_at->format('Y-m-d') : 'N/A'
                ]);
            }
            
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display payment history with filters
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function paymentHistory(Request $request)
    {
        // Build payment query with filters
        $query = FeePayment::with([
            'student',
            'studentFeeAccount.studentClass',
            'studentFeeAccount.academicYear'
        ]);

        // Apply filters
        $query = $this->applyPaymentFilters($query, $request);
        
        // Order by payment date
        $query->orderBy('payment_date', 'desc');
        
        // Paginate results
        $payments = $query->paginate(50);
        
        // Get filter options
        $students = Student::orderBy('id')->get()->pluck('full_name', 'id');
        $paymentMethods = FeePayment::distinct()->pluck('payment_method');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get()->pluck('year_name', 'id');
        
        // Calculate summary
        $summary = [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_students' => $query->distinct('student_id')->count('student_id'),
            'by_method' => (clone $query)->select('payment_method', DB::raw('COUNT(*) as count, SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get(),
        ];

        return view('fee-payments.reports.payment-history', compact(
            'payments',
            'students',
            'paymentMethods',
            'academicYears',
            'summary',
            'request'
        ));
    }

    /**
     * Get summary statistics for dashboard
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummaryStats(Request $request)
    {
        $filters = $this->getFilterParameters($request);
        $query = $this->buildReportQuery($filters);
        $summary = $this->calculateSummary($query);
        
        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get chart data for visualizations
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        $filters = $this->getFilterParameters($request);
        
        // Get payment trends
        $trends = FeePayment::where('status', 'completed')
            ->when($filters['date_from'], function($query) use ($filters) {
                return $query->whereDate('payment_date', '>=', $filters['date_from']);
            })
            ->when($filters['date_to'], function($query) use ($filters) {
                return $query->whereDate('payment_date', '<=', $filters['date_to']);
            })
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Get status distribution
        $statusDistribution = StudentFeeAccount::select('status', DB::raw('COUNT(*) as count'))
            ->when($filters['academic_year_id'], function($query) use ($filters) {
                return $query->where('academic_year_id', $filters['academic_year_id']);
            })
            ->when($filters['class_id'], function($query) use ($filters) {
                return $query->where('student_class_id', $filters['class_id']);
            })
            ->groupBy('status')
            ->get();

        // Get class performance
        $classPerformance = StudentFeeAccount::with('studentClass')
            ->select(
                'student_class_id',
                DB::raw('SUM(total_fees) as total_fees'),
                DB::raw('SUM(amount_paid) as total_paid'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->when($filters['academic_year_id'], function($query) use ($filters) {
                return $query->where('academic_year_id', $filters['academic_year_id']);
            })
            ->groupBy('student_class_id')
            ->having('student_class_id', '>', 0)
            ->get()
            ->map(function($item) {
                $item->class_name = $item->studentClass->class_name ?? 'Unknown';
                $item->collection_rate = $item->total_fees > 0 
                    ? round(($item->total_paid / $item->total_fees) * 100, 2) 
                    : 0;
                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'status_distribution' => $statusDistribution,
                'class_performance' => $classPerformance,
            ]
        ]);
    }

    /**
     * Get filter parameters from request
     * 
     * @param Request $request
     * @return array
     */
    private function getFilterParameters($request)
    {
        return [
            'academic_year_id' => $request->get('academic_year_id'),
            'class_id' => $request->get('class_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'student_id' => $request->get('student_id'),
            'report_type' => $request->get('report_type', 'summary'),
            'payment_method' => $request->get('payment_method'),
        ];
    }

    /**
     * Get filter options for dropdowns
     * 
     * @return array
     */
    private function getFilterOptions()
    {
        // Get academic years - adjust column name as per your schema
        $academicYears = AcademicYear::orderBy('id', 'desc')->get()->pluck('year_name', 'id');
        
        // Get classes - adjust column name as per your schema
        $classes = StudentClass::where('is_active', true)->orderBy('id')->get()->pluck('class_name', 'id');
        
        // Get students - adjust column name as per your schema
        // If your Student model has 'first_name' and 'last_name', concatenate them
        $students = Student::orderBy('id')->get()->mapWithKeys(function($student) {
            $name = $student->full_name ?? $student->first_name . ' ' . $student->last_name ?? 'Student #' . $student->id;
            return [$student->id => $name];
        });
        
        // Get payment methods
        $paymentMethods = FeePayment::distinct()->pluck('payment_method');
        
        return [
            'academicYears' => $academicYears,
            'classes' => $classes,
            'students' => $students,
            'paymentMethods' => $paymentMethods,
            'statusOptions' => ['paid' => 'Paid', 'partial' => 'Partial', 'pending' => 'Pending'],
        ];
    }

    /**
     * Build report query with filters
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildReportQuery($filters)
    {
        return StudentFeeAccount::with(['student', 'studentClass', 'academicYear', 'payments'])
            ->when(!empty($filters['academic_year_id']), function($query) use ($filters) {
                return $query->where('academic_year_id', $filters['academic_year_id']);
            })
            ->when(!empty($filters['class_id']), function($query) use ($filters) {
                return $query->where('student_class_id', $filters['class_id']);
            })
            ->when(!empty($filters['student_id']), function($query) use ($filters) {
                return $query->where('student_id', $filters['student_id']);
            })
            ->when(!empty($filters['status']), function($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(!empty($filters['date_from']), function($query) use ($filters) {
                return $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function($query) use ($filters) {
                return $query->whereDate('created_at', '<=', $filters['date_to']);
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get report data based on report type
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $reportType
     * @return mixed
     */
    private function getReportData($query, $reportType)
    {
        switch ($reportType) {
            case 'detailed':
                return $query->with(['payments' => function($q) {
                    $q->orderBy('created_at', 'desc');
                }])->paginate(50);
                
            case 'student':
                return $query->with(['student', 'payments'])
                    ->get()
                    ->groupBy('student_id')
                    ->map(function($items) {
                        $student = $items->first()->student;
                        return (object) [
                            'student' => $student,
                            'accounts' => $items,
                            'total_fees' => $items->sum('total_fees'),
                            'total_paid' => $items->sum('amount_paid'),
                            'total_balance' => $items->sum('balance'),
                            'status' => $items->sum('balance') <= 0 ? 'paid' : 
                                       ($items->sum('amount_paid') > 0 ? 'partial' : 'pending')
                        ];
                    });
                
            case 'class':
                return $query->with(['studentClass'])
                    ->get()
                    ->groupBy('student_class_id')
                    ->map(function($items) {
                        $class = $items->first()->studentClass;
                        return (object) [
                            'class' => $class,
                            'accounts' => $items,
                            'total_students' => $items->count(),
                            'total_fees' => $items->sum('total_fees'),
                            'total_paid' => $items->sum('amount_paid'),
                            'total_balance' => $items->sum('balance'),
                            'collection_rate' => $items->sum('total_fees') > 0 
                                ? round(($items->sum('amount_paid') / $items->sum('total_fees')) * 100, 2)
                                : 0
                        ];
                    });
                
            case 'payment_summary':
                return $this->getPaymentSummaryByPeriod($query);
                
            default: // summary
                return $query->paginate(50);
        }
    }

    /**
     * Calculate summary statistics
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    private function calculateSummary($query)
    {
        $baseQuery = clone $query;
        
        $totals = [
            'total_students' => $baseQuery->count(),
            'total_fees' => $baseQuery->sum('total_fees'),
            'total_paid' => $baseQuery->sum('amount_paid'),
            'total_balance' => $baseQuery->sum('balance'),
            'total_discount' => $baseQuery->sum('discount_applied'),
            'total_waiver' => $baseQuery->sum('waiver_amount'),
        ];
        
        $statusCounts = [
            'paid_count' => (clone $baseQuery)->where('status', 'paid')->count(),
            'partial_count' => (clone $baseQuery)->where('status', 'partial')->count(),
            'pending_count' => (clone $baseQuery)->where('status', 'pending')->count(),
        ];
        
        $totals['collection_rate'] = $this->calculateCollectionRate($baseQuery);
        
        return array_merge($totals, $statusCounts);
    }

    /**
     * Calculate collection rate
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return float
     */
    private function calculateCollectionRate($query)
    {
        $totalFees = $query->sum('total_fees');
        $totalPaid = $query->sum('amount_paid');
        
        if ($totalFees <= 0) {
            return 0;
        }
        
        return round(($totalPaid / $totalFees) * 100, 2);
    }

    /**
     * Calculate summary for export
     * 
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @return array
     */
    private function calculateSummaryForExport($data)
    {
        return [
            'total_students' => $data->count(),
            'total_fees' => $data->sum('total_fees'),
            'total_paid' => $data->sum('amount_paid'),
            'total_balance' => $data->sum('balance'),
            'total_discount' => $data->sum('discount_applied'),
            'total_waiver' => $data->sum('waiver_amount'),
            'collection_rate' => $this->calculateCollectionRateForExport($data),
            'paid_count' => $data->where('status', 'paid')->count(),
            'partial_count' => $data->where('status', 'partial')->count(),
            'pending_count' => $data->where('status', 'pending')->count(),
        ];
    }

    /**
     * Calculate collection rate for export
     * 
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @return float
     */
    private function calculateCollectionRateForExport($data)
    {
        $totalFees = $data->sum('total_fees');
        $totalPaid = $data->sum('amount_paid');
        
        if ($totalFees <= 0) {
            return 0;
        }
        
        return round(($totalPaid / $totalFees) * 100, 2);
    }

    /**
     * Get payment summary by period
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPaymentSummaryByPeriod($query)
    {
        return $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_fees) as total_fees'),
            DB::raw('SUM(amount_paid) as total_paid'),
            DB::raw('SUM(balance) as total_balance')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();
    }

    /**
     * Get payment summary
     * 
     * @param array $filters
     * @return array
     */
    private function getPaymentSummary($filters)
    {
        $query = FeePayment::with(['student', 'studentFeeAccount.studentClass'])
            ->where('status', 'completed')
            ->when(!empty($filters['date_from']), function($q) use ($filters) {
                return $q->whereDate('payment_date', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function($q) use ($filters) {
                return $q->whereDate('payment_date', '<=', $filters['date_to']);
            });

        return [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_method' => $query->select('payment_method', DB::raw('COUNT(*) as count, SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get(),
            'daily_summary' => $query->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get(),
        ];
    }

    /**
     * Apply payment filters
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyPaymentFilters($query, $request)
    {
        return $query
            ->when($request->get('student_id'), function($query) use ($request) {
                return $query->where('student_id', $request->get('student_id'));
            })
            ->when($request->get('academic_year_id'), function($query) use ($request) {
                return $query->whereHas('studentFeeAccount', function($q) use ($request) {
                    $q->where('academic_year_id', $request->get('academic_year_id'));
                });
            })
            ->when($request->get('class_id'), function($query) use ($request) {
                return $query->whereHas('studentFeeAccount', function($q) use ($request) {
                    $q->where('student_class_id', $request->get('class_id'));
                });
            })
            ->when($request->get('date_from'), function($query) use ($request) {
                return $query->whereDate('payment_date', '>=', $request->get('date_from'));
            })
            ->when($request->get('date_to'), function($query) use ($request) {
                return $query->whereDate('payment_date', '<=', $request->get('date_to'));
            })
            ->when($request->get('payment_method'), function($query) use ($request) {
                return $query->where('payment_method', $request->get('payment_method'));
            })
            ->when($request->get('status'), function($query) use ($request) {
                return $query->where('status', $request->get('status'));
            });
    }

    /**
     * Get export data
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getExportData($filters)
    {
        return StudentFeeAccount::with(['student', 'studentClass', 'academicYear', 'payments'])
            ->when(!empty($filters['academic_year_id']), function($query) use ($filters) {
                return $query->where('academic_year_id', $filters['academic_year_id']);
            })
            ->when(!empty($filters['class_id']), function($query) use ($filters) {
                return $query->where('student_class_id', $filters['class_id']);
            })
            ->when(!empty($filters['status']), function($query) use ($filters) {
                return $query->where('status', $filters['status']);
            })
            ->when(!empty($filters['student_id']), function($query) use ($filters) {
                return $query->where('student_id', $filters['student_id']);
            })
            ->when(!empty($filters['date_from']), function($query) use ($filters) {
                return $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function($query) use ($filters) {
                return $query->whereDate('created_at', '<=', $filters['date_to']);
            })
            ->get();
    }

    /**
     * Generate receipt for a payment
     * 
     * @param int $paymentId
     * @return \Illuminate\Http\Response
     */
    public function generateReceipt($paymentId)
    {
        $payment = FeePayment::with([
            'student',
            'studentFeeAccount.studentClass',
            'studentFeeAccount.academicYear'
        ])->findOrFail($paymentId);
        
        $pdf = PDF::loadView('fee-payments.receipt', [
            'payment' => $payment,
            'generated_at' => now(),
        ]);
        
        return $pdf->download('receipt-' . $payment->invoice_number . '.pdf');
    }

    /**
     * Get outstanding fees report
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function outstandingFees(Request $request)
    {
        $query = StudentFeeAccount::with(['student', 'studentClass', 'academicYear'])
            ->where('balance', '>', 0)
            ->where('status', '!=', 'paid')
            ->orderBy('balance', 'desc');
        
        // Apply filters
        if ($request->get('academic_year_id')) {
            $query->where('academic_year_id', $request->get('academic_year_id'));
        }
        
        if ($request->get('class_id')) {
            $query->where('student_class_id', $request->get('class_id'));
        }
        
        $outstandingFees = $query->paginate(50);
        
        $totalOutstanding = $query->sum('balance');
        $totalStudents = $query->distinct('student_id')->count('student_id');
        
        $academicYears = AcademicYear::orderBy('id', 'desc')->get()->pluck('year_name', 'id');
        $classes = StudentClass::where('is_active', true)->orderBy('id')->get()->pluck('class_name', 'id');
        
        return view('fee-payments.reports.outstanding', compact(
            'outstandingFees',
            'totalOutstanding',
            'totalStudents',
            'academicYears',
            'classes'
        ));
    }

    /**
     * Add accessor to Student model for full_name
     * If you don't have this in your model, add it temporarily
     */
    // Note: Add this method to your Student model if it doesn't exist
    // public function getFullNameAttribute()
    // {
    //     return $this->first_name . ' ' . $this->last_name;
    // }
}