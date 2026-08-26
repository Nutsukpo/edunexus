<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Period Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #fdcb6e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #2d3436;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #f8f9fa;
            border: 1px solid #dfe6e9;
            padding: 8px 12px;
            text-align: left;
        }
        td {
            border: 1px solid #dfe6e9;
            padding: 8px 12px;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        .info-table td {
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payroll Period Report</h1>
        <p><strong>Period:</strong> {{ $payrollPeriod->name }}</p>
        <p><strong>Code:</strong> {{ $payrollPeriod->period_code }} | 
           <strong>Month:</strong> {{ $payrollPeriod->month }} {{ $payrollPeriod->year }}</p>
        <p><strong>Status:</strong> {{ $payrollPeriod->status }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($payrollPeriod->start_date)->format('d M, Y') }}</td>
            <td><strong>End Date:</strong> {{ \Carbon\Carbon::parse($payrollPeriod->end_date)->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Staff Count:</strong> {{ $payrollPeriod->staff->count() }}</td>
            <td><strong>Created By:</strong> {{ $payrollPeriod->user->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <h3>Staff Payroll Details</h3>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Staff Name</th>
                <th>Staff Code</th>
                <th>Department</th>
                <th>Position</th>
                <th class="text-end">Gross Salary</th>
                <th class="text-end">Deductions</th>
                <th class="text-end">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $index => $staffMember)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $staffMember->first_name ?? '' }} {{ $staffMember->last_name ?? '' }}</td>
                    <td>{{ $staffMember->staff_code ?? 'N/A' }}</td>
                    <td>{{ $staffMember->department ?? 'N/A' }}</td>
                    <td>{{ $staffMember->position ?? 'N/A' }}</td>
                    <td class="text-end">₦{{ number_format($staffMember->pivot->gross_salary ?? 0, 2) }}</td>
                    <td class="text-end">₦{{ number_format($staffMember->pivot->total_deduction ?? 0, 2) }}</td>
                    <td class="text-end">₦{{ number_format($staffMember->pivot->net_salary ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No staff assigned to this payroll period.</td>
                </tr>
            @endforelse
        </tbody>
        @if($staff->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end">₦{{ number_format($totalGross ?? 0, 2) }}</td>
                    <td class="text-end">₦{{ number_format($totalDeductions ?? 0, 2) }}</td>
                    <td class="text-end">₦{{ number_format($totalNet ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>