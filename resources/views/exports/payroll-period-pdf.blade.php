<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Period Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 20px;
            color: #2d3436;
            background: #fff;
            font-size: 14px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1565c0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            color: #0d47a1;
            font-size: 28px;
            font-weight: 700;
        }
        .header .subtitle {
            color: #636e72;
            font-size: 16px;
            margin-top: 5px;
        }
        .header p {
            margin: 5px 0;
            color: #636e72;
        }
        .badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-draft { background: #dfe6e9; color: #2d3436; }
        .badge-processing { background: #ffeaa7; color: #6c5200; }
        .badge-approved { background: #74b9ff; color: #004080; }
        .badge-paid { background: #55efc4; color: #004d40; }
        .badge-cancelled { background: #ff7675; color: #7f0000; }
        
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 10px 15px;
            border: 1px solid #dfe6e9;
        }
        .info-table .label {
            font-weight: 600;
            background: #f8f9fa;
            width: 140px;
            color: #2d3436;
        }
        .info-table .value {
            color: #0d47a1;
        }
        
        .section-title {
            color: #0d47a1;
            font-size: 18px;
            margin: 20px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e3f2fd;
        }
        
        .staff-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .staff-table th {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            color: #0d47a1;
            font-size: 13px;
        }
        .staff-table td {
            border: 1px solid #dfe6e9;
            padding: 8px 12px;
            font-size: 13px;
        }
        .staff-table .text-end {
            text-align: right;
        }
        .staff-table .text-center {
            text-align: center;
        }
        .staff-table .total-row {
            background: #e3f2fd;
            font-weight: 700;
            color: #0d47a1;
        }
        .staff-table .total-row td {
            border-top: 2px solid #1565c0;
        }
        .staff-table tr:nth-child(even) {
            background: #f8fbff;
        }
        .staff-table tr:hover {
            background: #e3f2fd;
        }
        
        .summary-box {
            margin-top: 25px;
            padding: 20px;
            background: #f8fbff;
            border-radius: 8px;
            border-left: 4px solid #1565c0;
        }
        .summary-box .row {
            display: flex;
            justify-content: space-around;
        }
        .summary-box .item {
            text-align: center;
        }
        .summary-box .item .number {
            font-size: 24px;
            font-weight: 700;
            color: #0d47a1;
        }
        .summary-box .item .label {
            font-size: 13px;
            color: #636e72;
            margin-top: 4px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #636e72;
            font-size: 12px;
            border-top: 1px solid #dfe6e9;
            padding-top: 20px;
        }
        .footer p {
            margin: 3px 0;
        }
        
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header mb-0">
        <h1>Payroll Period Report</h1>
        <div class="subtitle">{{ config('app.name', 'EduNexus') }} - Payroll Management</div>
        <p>
            <strong>Period:</strong> {{ $payrollPeriod->name }} &nbsp;|&nbsp;
            <strong>Code:</strong> {{ $payrollPeriod->period_code }} &nbsp;|&nbsp;
            <strong>Month:</strong> {{ \Carbon\Carbon::create()->month($payrollPeriod->month)->format('F') }} {{ $payrollPeriod->year }}
        </p>
    </div>

    <table class="info-table mb-0">
        <tr>
            <td class="label"><i class="fas fa-calendar-plus"></i> Start Date</td>
            <td class="value">{{ \Carbon\Carbon::parse($payrollPeriod->start_date)->format('l, d F, Y') }}</td>
            <td class="label"><i class="fas fa-calendar-minus"></i> End Date</td>
            <td class="value">{{ \Carbon\Carbon::parse($payrollPeriod->end_date)->format('l, d F, Y') }}</td>
        </tr>
        <tr>
            <td class="label"><i class="fas fa-users"></i> Staff Count</td>
            <td class="value">{{ $payrollPeriod->staff->count() }}</td>
            <td class="label"><i class="fas fa-user"></i> Created By</td>
            <td class="value">{{ $payrollPeriod->createdBy->name ?? 'N/A' }}</td>
        </tr>
        @if($payrollPeriod->approved_by)
        <tr>
            <td class="label"><i class="fas fa-check-circle"></i> Approved By</td>
            <td class="value">{{ $payrollPeriod->approvedBy->name ?? 'N/A' }}</td>
            <td class="label"><i class="fas fa-clock"></i> Approved At</td>
            <td class="value">{{ $payrollPeriod->approved_at ? \Carbon\Carbon::parse($payrollPeriod->approved_at)->format('l, d F, Y H:i') : 'N/A' }}</td>
        </tr>
        @endif
        @if($payrollPeriod->payment_date)
        <tr>
            <td class="label"><i class="fas fa-money-bill-wave"></i> Payment Date</td>
            <td class="value" colspan="3">{{ \Carbon\Carbon::parse($payrollPeriod->payment_date)->format('l, d F, Y') }}</td>
        </tr>
        @endif
    </table>

    <!-- Summary Box - Horizontal Table Layout
    <div class="summary-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: center; padding: 12px 15px; border-right: 1px solid #e0e0e0; background: #f8f9fa;">
                    <div style="font-size: 20px; font-weight: bold; color: #1a3c5e;">₵{{ number_format($totalGross ?? 0, 2) }}</div>
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Gross Salary</div>
                </td>
                <td style="text-align: center; padding: 12px 15px; border-right: 1px solid #e0e0e0; background: #f8f9fa;">
                    <div style="font-size: 20px; font-weight: bold; color: #dc3545;">₵{{ number_format($totalDeductions ?? 0, 2) }}</div>
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Deductions</div>
                </td>
                <td style="text-align: center; padding: 12px 15px; border-right: 1px solid #e0e0e0; background: #f8f9fa;">
                    <div style="font-size: 20px; font-weight: bold; color: #28a745;">₵{{ number_format($totalNet ?? 0, 2) }}</div>
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Net Salary</div>
                </td>
                <td style="text-align: center; padding: 12px 15px; background: #f8f9fa;">
                    <div style="font-size: 20px; font-weight: bold; color: #1a3c5e;">{{ $payrollPeriod->staff->count() }}</div>
                    <div style="font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Staff</div>
                </td>
            </tr>
        </table>
    </div> -->
    <h3 class="section-title">
        <!-- <i class="fas fa-list"></i> Staff Payroll Details -->
    </h3>
    
    <table class="staff-table">
        <thead>
            <tr>
                <th width="40">#</th>
                <th>Staff Name</th>
                <th>Staff ID</th>
                <th>Department</th>
                <th>Position</th>
                <th class="text-end">Gross Salary</th>
                <th class="text-end">Deductions</th>
                <th class="text-end">Net Salary</th>
            </tr>
        </thead>
        <tbody class="mt-0">
            @forelse($payrollPeriod->staff as $index => $staffMember)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $staffMember->first_name ?? '' }} {{ $staffMember->last_name ?? '' }}</td>
                    <td>{{ $staffMember->staff_id ?? 'N/A' }}</td>
                    <td>{{ $staffMember->department ?? 'N/A' }}</td>
                    <td>{{ $staffMember->position ?? 'N/A' }}</td>
                    <td class="text-end">GHC {{ number_format($staffMember->pivot->gross_salary ?? 0, 2) }}</td>
                    <td class="text-end">GHC {{ number_format($staffMember->pivot->total_deduction ?? 0, 2) }}</td>
                    <td class="text-end">GHC {{ number_format($staffMember->pivot->net_salary ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px 0; color: #636e72;">
                        <i class="fas fa-users" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        No staff assigned to this payroll period.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($payrollPeriod->staff->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end">GHC {{ number_format($totalGross ?? 0, 2) }}</td>
                    <td class="text-end">GHC {{ number_format($totalDeductions ?? 0, 2) }}</td>
                    <td class="text-end">GHC {{ number_format($totalNet ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <p><strong>{{ config('app.name', 'EduNexus') }}</strong> - Payroll Management System</p>
        <p>Generated on {{ now()->format('l, d F, Y h:i:s A') }}</p>
        <p style="font-size: 11px; color: #999;">This report is system-generated and does not require a signature.</p>
        <p style="font-size: 11px; color: #999;">© {{ date('Y') }} {{ config('app.name', 'EduNexus') }}. All rights reserved.</p>
    </div>

</body>
</html>