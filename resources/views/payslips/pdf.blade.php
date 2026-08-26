<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="right" style="font-size: 10px; color: #999999; padding-bottom: 10px;">
                            Generated: {{ date('d M, Y h:i A') }}
                        </td>
                    </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 3px solid #1a73e8; padding-bottom: 15px; margin-bottom: 20px;">
                    <tr>
                        <td align="left" style="width: 50%;">
                            <div style="font-size: 22px; font-weight: bold; color: #1a73e8;">PAYSLIP</div>
                            <div style="font-size: 13px; color: #666666;">Employee Salary Statement</div>
                        </td>
                        <td align="right" style="width: 50%; font-size: 11px; color: #666666;">
                            <!-- <strong>{{ config('app.name', 'Company Name') }}</strong><br> -->
                            <!-- {{ config('app.address', 'Company Address') }} -->TALHA PREMIER INTERNATIONAL ACADEMY
                        </td>
                    </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
                    <tr>
                        <!-- Staff Information -->
                        <td valign="top" style="width: 50%; padding-right: 15px;">
                            <div style="font-size: 14px; font-weight: bold; color: #1a73e8; border-bottom: 2px solid #e8f0fe; padding-bottom: 5px; margin-bottom: 10px;">
                                Staff Information
                            </div>
                            <table width="100%" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Name</td>
                                    <td style="padding: 3px 0;">
                                        <strong>{{ $payslip->staff->first_name ?? '' }} {{ $payslip->staff->last_name ?? '' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Position</td>
                                    <td style="padding: 3px 0;">{{ $payslip->staff->position ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Department</td>
                                    <td style="padding: 3px 0;">{{ $payslip->staff->department ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Staff Code</td>
                                    <td style="padding: 3px 0;">{{ $payslip->staff->staff_id ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </td>
                        
                        <!-- Payslip Information -->
                        <td valign="top" style="width: 50%; padding-left: 15px;">
                            <div style="font-size: 14px; font-weight: bold; color: #1a73e8; border-bottom: 2px solid #e8f0fe; padding-bottom: 5px; margin-bottom: 10px;">
                                Payslip Information
                            </div>
                            <table width="100%" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Period</td>
                                    <td style="padding: 3px 0;">
                                        <strong>{{ $payslip->month_name ?? '' }} {{ $payslip->year ?? '' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Generated</td>
                                    <td style="padding: 3px 0;">
                                        {{ isset($payslip->created_at) ? $payslip->created_at->format('d M, Y') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Status</td>
                                    <td style="padding: 3px 0;">
                                        @php
                                            $status = $payslip->status ?? 'generated';
                                            $statusColors = [
                                                'generated' => 'background: #e8f5e9; color: #2e7d32;',
                                                'cancelled' => 'background: #fbe9e7; color: #c62828;',
                                                'paid' => 'background: #e3f2fd; color: #0d47a1;'
                                            ];
                                            $color = $statusColors[$status] ?? 'background: #e8f5e9; color: #2e7d32;';
                                        @endphp
                                        <span style="display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; {{ $color }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #555555; width: 100px; padding: 3px 0;">Generated By</td>
                                    <td style="padding: 3px 0;">{{ isset($payslip->creator) ? $payslip->creator->name : 'N/A' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- Salary Breakdown -->
                <div style="font-size: 14px; font-weight: bold; color: #1a73e8; border-bottom: 2px solid #e8f0fe; padding-bottom: 5px; margin-bottom: 10px;">
                    Salary Breakdown
                </div>
                
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 12px; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th align="left" style="padding: 8px 10px; border-bottom: 2px solid #dddddd; font-weight: 600; width: 70%;">Description</th>
                            <th align="right" style="padding: 8px 10px; border-bottom: 2px solid #dddddd; font-weight: 600; width: 30%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Basic Salary -->
                        <tr>
                            <td style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">Basic Salary</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format((float)($payslip->basic_salary ?? 0), 2) }}</td>
                        </tr>
                        
                        <!-- Allowances -->
                        <tr>
                            <td style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">Allowances</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format((float)($payslip->allowances ?? 0), 2) }}</td>
                        </tr>
                        
                        <!-- Bonus -->
                        @php $bonus = (float)($payslip->bonus ?? 0); @endphp
                        @if($bonus > 0)
                        <tr>
                            <td style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">Bonus</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($bonus, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Overtime -->
                        @php $overtime = (float)($payslip->overtime ?? 0); @endphp
                        @if($overtime > 0)
                        <tr>
                            <td style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">Overtime</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($overtime, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Total Earnings -->
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td style="padding: 8px 10px; border-bottom: 1px solid #dddddd;">Total Earnings</td>
                            <td align="right" style="padding: 8px 10px; border-bottom: 1px solid #dddddd; color: #2e7d32;">
                                ${{ number_format((float)($payslip->total_earnings ?? 0), 2) }}
                            </td>
                        </tr>
                        
                        <!-- Spacer -->
                        <tr>
                            <td colspan="2" style="padding: 5px 10px;"></td>
                        </tr>
                        
                        <!-- Deductions Header -->
                        <tr style="background: #fff3f3; font-weight: bold; color: #d32f2f;">
                            <td style="padding: 8px 10px; border-bottom: 2px solid #dddddd;">Deductions</td>
                            <td align="right" style="padding: 8px 10px; border-bottom: 2px solid #dddddd;"></td>
                        </tr>
                        
                        <!-- Tax -->
                        @php $tax = (float)($payslip->tax ?? 0); @endphp
                        @if($tax > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Tax (PAYE)</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($tax, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Pension -->
                        @php $pension = (float)($payslip->pension ?? 0); @endphp
                        @if($pension > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Pension (SSNIT)</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($pension, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Tier 2 -->
                        @php $tier2 = (float)($payslip->tier2 ?? 0); @endphp
                        @if($tier2 > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Tier 2</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($tier2, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Tier 3 -->
                        @php $tier3 = (float)($payslip->tier3 ?? 0); @endphp
                        @if($tier3 > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Tier 3</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($tier3, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Insurance -->
                        @php $insurance = (float)($payslip->insurance ?? 0); @endphp
                        @if($insurance > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Insurance</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($insurance, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Loans -->
                        @php $loans = (float)($payslip->loans ?? 0); @endphp
                        @if($loans > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Loans</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($loans, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Other Deductions -->
                        @php $otherDeductions = (float)($payslip->other_deductions ?? 0); @endphp
                        @if($otherDeductions > 0)
                        <tr>
                            <td style="padding: 6px 10px 6px 25px; border-bottom: 1px solid #eeeeee;">Other Deductions</td>
                            <td align="right" style="padding: 6px 10px; border-bottom: 1px solid #eeeeee;">${{ number_format($otherDeductions, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- Total Deductions -->
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td style="padding: 8px 10px; border-bottom: 1px solid #dddddd;">Total Deductions</td>
                            <td align="right" style="padding: 8px 10px; border-bottom: 1px solid #dddddd; color: #d32f2f;">
                                ${{ number_format((float)($payslip->total_deductions ?? 0), 2) }}
                            </td>
                        </tr>
                        
                        <!-- Net Pay -->
                        <tr style="background: #e8f5e9; font-weight: bold; font-size: 15px;">
                            <td style="padding: 10px 10px; border-top: 2px solid #2e7d32;">NET PAY</td>
                            <td align="right" style="padding: 10px 10px; border-top: 2px solid #2e7d32; color: #2e7d32;">
                                ${{ number_format((float)($payslip->net_pay ?? 0), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Breakdown -->
                @php
                    $breakdown = $payslip->breakdown ?? null;
                    $hasBreakdown = !empty($breakdown) && is_array($breakdown) && count($breakdown) > 0;
                @endphp
                @if($hasBreakdown)
                <div style="margin-top: 20px;">
                    <div style="font-size: 14px; font-weight: bold; color: #1a73e8; border-bottom: 2px solid #e8f0fe; padding-bottom: 5px; margin-bottom: 10px;">
                        Breakdown Details
                    </div>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th align="left" style="padding: 5px 8px; border-bottom: 1px solid #dddddd; font-weight: 600; width: 70%;">Item</th>
                                <th align="right" style="padding: 5px 8px; border-bottom: 1px solid #dddddd; font-weight: 600; width: 30%;">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($breakdown as $key => $value)
                                @if(!is_array($value) && !is_object($value) && !is_null($value))
                                <tr>
                                    <td style="padding: 4px 8px; border-bottom: 1px solid #f0f0f0;">
                                        {{ ucfirst(str_replace('_', ' ', (string)$key)) }}
                                    </td>
                                    <td align="right" style="padding: 4px 8px; border-bottom: 1px solid #f0f0f0;">
                                        @if(is_numeric($value))
                                            ${{ number_format((float)$value, 2) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                
                <!-- Notes -->
                @php $notes = $payslip->notes ?? ''; @endphp
                @if(!empty(trim($notes)))
                <div style="margin-top: 20px;">
                    <div style="font-size: 14px; font-weight: bold; color: #1a73e8; border-bottom: 2px solid #e8f0fe; padding-bottom: 5px; margin-bottom: 10px;">
                        Notes
                    </div>
                    <div style="padding: 10px 0; font-size: 12px; color: #555555;">
                        {{ $notes }}
                    </div>
                </div>
                @endif
                
                <!-- Footer -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #dddddd;">
                    <tr>
                        <td align="center" style="font-size: 10px; color: #999999;">
                            This is a computer-generated payslip. No signature is required.<br>
                            For any queries, please contact the HR/Payroll department.
                        </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    
</body>
</html>