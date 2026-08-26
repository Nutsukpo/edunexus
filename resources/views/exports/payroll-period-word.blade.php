<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Period Report</title>
    <style>
        body {
            font-family: 'Times New Roman', Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.6;
            color: #1a1a1a;
        }
        
        /* School Header */
        .school-header {
            text-align: center;
            border-bottom: 3px solid #1a3c5e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-header .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a3c5e;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
            padding: 0;
        }
        .school-header .school-motto {
            font-size: 14px;
            color: #4a4a4a;
            font-style: italic;
            margin: 2px 0;
        }
        .school-header .school-address {
            font-size: 11px;
            color: #555;
            margin: 2px 0;
        }
        .school-header .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #1a3c5e;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .school-header .report-subtitle {
            font-size: 13px;
            color: #4a4a4a;
            margin: 2px 0;
        }
        
        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #d1d1d1;
            background: #f9f9f9;
        }
        .info-table .label {
            font-weight: bold;
            background: #e8ecef;
            width: 120px;
            color: #1a3c5e;
        }
        .info-table .value {
            background: #ffffff;
        }
        
        /* Staff Table */
        .staff-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }
        .staff-table th {
            background: #1a3c5e;
            color: #ffffff;
            border: 1px solid #1a3c5e;
            padding: 8px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .staff-table td {
            border: 1px solid #d1d1d1;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .staff-table tbody tr:nth-child(even) {
            background: #f5f7fa;
        }
        .staff-table tbody tr:hover {
            background: #e8f0fe;
        }
        .staff-table .text-end {
            text-align: right;
        }
        .staff-table .text-center {
            text-align: center;
        }
        .staff-table .total-row {
            background: #e8ecef !important;
            font-weight: bold;
            border-top: 2px solid #1a3c5e;
        }
        .staff-table .total-row td {
            padding: 8px 12px;
            background: #e8ecef;
        }
        .staff-table .total-label {
            font-weight: bold;
            color: #1a3c5e;
        }
        
        /* Currency Styling */
        .currency {
            font-weight: 500;
        }
        .currency-ghs {
            font-weight: bold;
            color: #1a3c5e;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #d1d1d1;
            padding-top: 15px;
        }
        .footer .generated-by {
            font-weight: bold;
            color: #555;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status-draft {
            background: #e8ecef;
            color: #555;
        }
        .status-processing {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-paid {
            background: #cce5ff;
            color: #004085;
        }
        
        /* Summary Section */
        .summary-box {
            margin: 15px 0;
            padding: 0;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 8px 15px;
            border: 1px solid #d1d1d1;
            text-align: center;
            font-size: 12px;
            background: #f9f9f9;
        }
        .summary-box .summary-label {
            font-weight: bold;
            color: #1a3c5e;
            background: #e8ecef;
        }
        .summary-box .summary-value {
            font-weight: bold;
            font-size: 14px;
            color: #1a3c5e;
            background: #ffffff;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(26, 60, 94, 0.05);
            font-weight: bold;
            pointer-events: none;
            z-index: 999;
        }
        
        @media print {
            .watermark {
                display: none;
            }
            .staff-table tbody tr:hover {
                background: none !important;
            }
        }
    </style>
</head>
<body>
    {{-- Watermark for official document --}}
    <div class="watermark">OFFICIAL</div>

    {{-- School Header --}}
    <div class="school-header">
        <h1 class="school-name">LAGOS INTERNATIONAL SCHOOL</h1>
        <p class="school-motto">"Excellence in Education"</p>
        <p class="school-address">
            123 Education Drive, Victoria Island, Lagos, Nigeria<br>
            Tel: +234 800 123 4567 | Email: info@lagosintlschool.edu.ng
        </p>
        <div style="border-top: 2px solid #1a3c5e; width: 200px; margin: 10px auto;"></div>
        <h2 class="report-title">Payroll Period Report</h2>
        <p class="report-subtitle">
            {{ $payrollPeriod->name }} | Period Code: {{ $payrollPeriod->period_code }}
        </p>
    </div>

    {{-- Period Information --}}
    <table class="info-table">
        <tr>
            <td class="label">Period</td>
            <td class="value">{{ $payrollPeriod->name }}</td>
            <td class="label">Period Code</td>
            <td class="value">{{ $payrollPeriod->period_code }}</td>
        </tr>
        <tr>
            <td class="label">Month/Year</td>
            <td class="value">{{ $payrollPeriod->month }} - {{ $payrollPeriod->year }}</td>
            <td class="label">Status</td>
            <td class="value">
                <span class="status-badge status-{{ strtolower($payrollPeriod->status) }}">
                    {{ $payrollPeriod->status }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Start Date</td>
            <td class="value">{{ \Carbon\Carbon::parse($payrollPeriod->start_date)->format('d M, Y') }}</td>
            <td class="label">End Date</td>
            <td class="value">{{ \Carbon\Carbon::parse($payrollPeriod->end_date)->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Staff Count</td>
            <td class="value">{{ $payrollPeriod->staff->count() }}</td>
            <td class="label">Created By</td>
            <td class="value">{{ $payrollPeriod->user->name ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- Summary Box --}}
    <div class="summary-box">
        <table>
            <tr>
                <td class="summary-label">Total Gross Salary</td>
                <td class="summary-value">₵{{ number_format($totalGross ?? 0, 2) }}</td>
                <td class="summary-label">Total Deductions</td>
                <td class="summary-value">₵{{ number_format($totalDeductions ?? 0, 2) }}</td>
                <td class="summary-label">Total Net Salary</td>
                <td class="summary-value">₵{{ number_format($totalNet ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Staff Payroll Details --}}
    <h3 style="color: #1a3c5e; border-bottom: 2px solid #1a3c5e; padding-bottom: 5px; margin-top: 20px;">
        Staff Payroll Details
    </h3>
    
    <table class="staff-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 20%;">Staff Name</th>
                <th style="width: 12%;">Staff Code</th>
                <th style="width: 15%;">Department</th>
                <th style="width: 15%;">Position</th>
                <th style="width: 11%;" class="text-end">Gross Salary</th>
                <th style="width: 11%;" class="text-end">Deductions</th>
                <th style="width: 11%;" class="text-end">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $staffMember)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $staffMember->first_name ?? '' }} {{ $staffMember->last_name ?? '' }}</strong>
                        @if(isset($staffMember->middle_name))
                            <br><small style="color: #666;">{{ $staffMember->middle_name }}</small>
                        @endif
                    </td>
                    <td>{{ $staffMember->staff_code ?? 'N/A' }}</td>
                    <td>{{ $staffMember->department ?? 'N/A' }}</td>
                    <td>{{ $staffMember->position ?? 'N/A' }}</td>
                    <td class="text-end currency">₵{{ number_format($staffMember->pivot->gross_salary ?? 0, 2) }}</td>
                    <td class="text-end currency">₵{{ number_format($staffMember->pivot->total_deduction ?? 0, 2) }}</td>
                    <td class="text-end currency-ghs">₵{{ number_format($staffMember->pivot->net_salary ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 40px 20px; color: #888;">
                        <strong>No staff assigned to this payroll period.</strong>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($staff->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-end total-label">TOTAL</td>
                    <td class="text-end">₵{{ number_format($totalGross ?? 0, 2) }}</td>
                    <td class="text-end">₵{{ number_format($totalDeductions ?? 0, 2) }}</td>
                    <td class="text-end">₵{{ number_format($totalNet ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row" style="background: #d4edda !important;">
                    <td colspan="5" class="text-end total-label" style="color: #155724;">NET PAYABLE</td>
                    <td colspan="3" class="text-end" style="font-size: 14px; color: #155724;">
                        ₵{{ number_format($totalNet ?? 0, 2) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Signatures Section --}}
    <table style="width: 100%; margin-top: 30px; border: none; font-size: 11px;">
        <tr>
            <td style="width: 33%; text-align: center; border: none; padding-top: 20px;">
                <div style="border-top: 1px solid #1a3c5e; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Prepared By</strong>
                </div>
                <div style="margin-top: 5px; color: #666;">{{ auth()->user()->name ?? '________________' }}</div>
                <div style="color: #999; font-size: 10px;">Date: {{ now()->format('d M, Y') }}</div>
            </td>
            <td style="width: 33%; text-align: center; border: none; padding-top: 20px;">
                <div style="border-top: 1px solid #1a3c5e; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Checked By</strong>
                </div>
                <div style="margin-top: 5px; color: #666;">________________</div>
                <div style="color: #999; font-size: 10px;">Date: ________________</div>
            </td>
            <td style="width: 33%; text-align: center; border: none; padding-top: 20px;">
                <div style="border-top: 1px solid #1a3c5e; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Approved By</strong>
                </div>
                <div style="margin-top: 5px; color: #666;">________________</div>
                <div style="color: #999; font-size: 10px;">Date: ________________</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>
            <span class="generated-by">LAGOS INTERNATIONAL SCHOOL</span> | 
            Payroll System Report Generated on {{ now()->format('d M, Y H:i:s') }}
        </p>
        <p style="margin-top: 2px; font-size: 9px; color: #aaa;">
            This is a computer-generated document. No signature is required.
        </p>
        <p style="margin-top: 2px; font-size: 9px; color: #aaa;">
            Page {{ $page ?? 1 }} of {{ $totalPages ?? 1 }}
        </p>
    </div>
</body>
</html>