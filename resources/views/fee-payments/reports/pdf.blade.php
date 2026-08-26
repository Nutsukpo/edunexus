{{-- resources/views/fee-payments/reports/pdf.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .badge-paid {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
        }
        .badge-partial {
            background: #ffc107;
            color: #333;
            padding: 2px 8px;
            border-radius: 3px;
        }
        .badge-pending {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
        }
        .summary-box {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #7f8c8d;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .totals {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fee Payment Report</h1>
        <p>Generated on: {{ $generated_at->format('F d, Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <!-- <strong>Filters Applied:</strong> -->
        <ul>
            @if($filters['academic_year_id'] ?? false)
                <li>Academic Year: {{ \App\Models\AcademicYear::find($filters['academic_year_id'])->name ?? 'N/A' }}</li>
            @endif
            @if($filters['class_id'] ?? false)
                <li>Class: {{ \App\Models\StudentClass::find($filters['class_id'])->name ?? 'N/A' }}</li>
            @endif
            @if($filters['status'] ?? false)
                <li>Status: {{ ucfirst($filters['status']) }}</li>
            @endif
            @if(($filters['date_from'] ?? false) || ($filters['date_to'] ?? false))
                <li>Date Range: {{ $filters['date_from'] ?? 'Start' }} to {{ $filters['date_to'] ?? 'End' }}</li>
            @endif
        </ul>
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <table style="width:100%;">
            <tr>
                <td><strong>Total Students:</strong> {{ $data->count() }}</td>
                <td><strong>Total Fees:</strong> Ghc {{ number_format($data->sum('total_fees'), 2) }}</td>
                <td><strong>Total Paid:</strong> Ghc {{ number_format($data->sum('amount_paid'), 2) }}</td>
                <td><strong>Total Balance:</strong> Ghc {{ number_format($data->sum('balance'), 2) }}</td>
            </tr>
            <tr>
                <td><strong>Collection Rate:</strong> 
                    @php
                        $totalFees = $data->sum('total_fees');
                        $totalPaid = $data->sum('amount_paid');
                        $rate = $totalFees > 0 ? ($totalPaid / $totalFees) * 100 : 0;
                    @endphp
                    {{ number_format($rate, 2) }}%
                </td>
                <td><strong>Paid:</strong> {{ $data->where('status', 'paid')->count() }}</td>
                <td><strong>Partial:</strong> {{ $data->where('status', 'partial')->count() }}</td>
                <td><strong>Pending:</strong> {{ $data->where('status', 'pending')->count() }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Class</th>
                <th>Academic Year</th>
                <th>Total Fees</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Discount</th>
                <th>Waiver</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->student->full_name ?? 'N/A' }}</td>
                    <td>{{ $item->studentClass->name ?? 'N/A' }}</td>
                    <td>{{ $item->academicYear->name ?? 'N/A' }}</td>
                    <td class="text-right">Ghc {{ number_format($item->total_fees, 2) }}</td>
                    <td class="text-right">Ghc {{ number_format($item->amount_paid, 2) }}</td>
                    <td class="text-right">Ghc {{ number_format($item->balance, 2) }}</td>
                    <td class="text-right">Ghc {{ number_format($item->discount_applied, 2) }}</td>
                    <td class="text-right">Ghc {{ number_format($item->waiver_amount, 2) }}</td>
                    <td class="text-center">
                        @if($item->status == 'paid')
                            <span class="badge-paid">Paid</span>
                        @elseif($item->status == 'partial')
                            <span class="badge-partial">Partial</span>
                        @else
                            <span class="badge-pending">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No records found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="4" class="text-right">Totals:</td>
                <td class="text-right">Ghc {{ number_format($data->sum('total_fees'), 2) }}</td>
                <td class="text-right">Ghc {{ number_format($data->sum('amount_paid'), 2) }}</td>
                <td class="text-right">Ghc {{ number_format($data->sum('balance'), 2) }}</td>
                <td class="text-right">Ghc {{ number_format($data->sum('discount_applied'), 2) }}</td>
                <td class="text-right">Ghc {{ number_format($data->sum('waiver_amount'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is a system-generated report. For any discrepancies, please contact the accounts department.</p>
        <p>&copy; {{ date('Y') }} Fee Management System. All rights reserved.</p>
    </div>
</body>
</html>