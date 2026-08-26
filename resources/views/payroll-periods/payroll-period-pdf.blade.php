<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Period Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #2d3436;
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
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #636e72;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #dfe6e9;
        }
        .info-table .label {
            font-weight: bold;
            background: #f8f9fa;
            width: 120px;
        }
        .staff-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .staff-table th {
            background: #f8f9fa;
            border: 1px solid #dfe6e9;
            padding: 10px 12px;
            text-align: left;
            font-weight: bold;
            color: #2d3436;
        }
        .staff-table td {
            border: 1px solid #dfe6e9;
            padding: 8px 12px;
        }
        .staff-table .text-end {
            text-align: right;
        }
        .staff-table .text-center {
            text-align: center;
        }
        .staff-table .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #636e72;
            font-size: 12px;
            border-top: 1px solid #dfe6e9;
            padding-top: 15px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-draft { background: #dfe6e9; color: #2d3436; }
        .badge-processing { background: #ffeaa7; color: #6c5200; }
        .badge-approved { background: #dfe6e9; color: #2d3436; }
        .badge-paid { background: #55efc4; color: #004d40; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payroll Period Report</h1>
        <p><strong>Period:</strong> {{ $payrollPeriod->name }}</p>
        <p><strong>Code:</strong> {{ $payrollPeriod->period_code }} | 
           <strong>Month:</strong> {{ $payrollPeriod->month }} {{ $payrollPeriod->year }}</p>
        <p>
            <strong>Status:</strong> 
            <span class="badge badge-{{ strtolower($payrollPeriod->status) }}">
                {{ $payrollPeriod->status }}
            </span>
        </p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Start Date</td>
            <td>{{ \Carbon\Carbon::parse($payrollPeriod->start_date)->format('d M, Y') }}</td>
            <td class="label">End Date</td>
            <td>{{ \Carbon\Carbon::parse($payrollPeriod->end_date)->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Staff Count</td>
            <td>{{ $payrollPeriod->staff->count() }}</td>
            <td class="label">Created By</td>
            <td>{{ $payrollPeriod->user->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <h3 style="color: #2d3436;">Staff Payroll Details</h3>
    
    <table class="staff-table">
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

    <div class="footer">
        <p>Generated on {{ now()->format('d M, Y H:i:s') }}</p>
        <p>© {{ date('Y') }} Payroll System. All rights reserved.</p>
    </div>
</body>
</html>