<link rel="icon" type="image/png" href="{{ asset('img/Talha.jpeg') }}">
<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <title>Invoice - {{ $bill->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            padding: 20px;
            background: #fff;
        }
        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1565c0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-left h1 {
            color: #1565c0;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header-left .subtitle {
            color: #666;
            font-size: 14px;
        }
        .header-right {
            text-align: right;
        }
        .header-right .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .header-right .invoice-number {
            font-size: 16px;
            color: #666;
        }
        .company-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fbff;
            border-radius: 4px;
            border-left: 4px solid #1565c0;
        }
        .company-info .name {
            font-size: 18px;
            font-weight: bold;
            color: #1565c0;
        }
        .bill-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .info-item .label {
            font-weight: bold;
            color: #555;
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-item .value {
            font-size: 15px;
            color: #333;
            margin-top: 2px;
        }
        .info-item .value.highlight {
            color: #28a745;
            font-weight: bold;
        }
        .info-item .value.danger {
            color: #dc3545;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background: #1565c0;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .totals {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
            max-width: 400px;
            float: right;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .totals-row .label {
            font-weight: 500;
            color: #555;
        }
        .totals-row .value {
            font-weight: bold;
        }
        .totals-row.total {
            border-top: 2px solid #1565c0;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 18px;
        }
        .totals-row.total .value {
            color: #28a745;
        }
        .totals-row.danger .value {
            color: #dc3545;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .terms {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #888;
            font-size: 13px;
            clear: both;
        }
        .footer .note {
            margin-top: 10px;
            font-size: 12px;
            color: #999;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #cce5ff;
            color: #004085;
        }
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            clear: both;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin-top: 30px;
        }
        .signature-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .watermark {
            position: relative;
        }
        .watermark::after {
            content: '{{ $bill->status == 'paid' ? 'PAID' : ($bill->status == 'overdue' ? 'OVERDUE' : '') }}';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100px;
            color: rgba(40, 167, 69, 0.08);
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            body { padding: 0; }
            .invoice-container { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container {{ $bill->status == 'paid' ? 'watermark' : '' }}">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>TALHA PREMIER INTERNATIONAL ACADEMY </h1>
                <div class="subtitle">School Management System</div>
                <div style="margin-top: 10px; font-size: 13px; color: #666;">
                    {{ config('app.address', '123 School Street, City') }}<br>
                    {{ config('app.phone', '+123 456 7890') }} | {{ config('app.email', 'info@edunexus.com') }}
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">BILLSHEET</div>
                <div class="invoice-number">#{{ $bill->invoice_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $bill->status }}">
                        {{ $bill->status_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bill Info -->
        <div class="bill-info">
            <div>
                <div class="info-item">
                    <span class="label">Bill To: <span class="value" style="font-weight: bold;">
                        {{ $bill->student->full_name ?? 'N/A' }}
                    </span></span>  
                </div>
                <div class="info-item">
                    <span class="label">Admission Number: <span class="value">{{ $bill->student->student_id ?? 'N/A' }}</span></span>
                    
                </div>
                <div class="info-item">
                    <span class="label">Class: <span class="value">{{ $bill->student->currentAssignment->studentClass->name  ?? 'N/A' }}</span></span>                 
                  
                </div>
                <div class="info-item">
                    <span class="label">Billing Type: <span class="value">School Fees</span></span>                    
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="label">Invoice Date: <span class="value">{{ $bill->bill_date->format('l, F d, Y') }}</span></span>            
                </div>
                <div class="info-item">
                    <span class="label">Due Date:  <span class="value {{ $bill->isOverdue() ? 'danger' : '' }}">
                        {{ $bill->due_date ? $bill->due_date->format('l, F d, Y') : 'N/A' }}
                        @if($bill->isOverdue())
                            <span style="color: #dc3545;">(Overdue)</span>
                        @endif
                    </span></span>              
                </div>
                @if($bill->billing_period_start && $bill->billing_period_end)
                <!-- <div class="info-item">
                    <span class="label">Billing Period: <span class="value">
                        {{ $bill->billing_period_start->format('d/m/Y') }} - 
                        {{ $bill->billing_period_end->format('d/m/Y') }}
                    </span></span>                   
                </div> -->
                @endif
                @if($bill->is_recurring)
                <div class="info-item">
                    <span class="label">Recurring</span>
                    <span class="value">
                        {{ $bill->recurring_frequency_label }}
                        @if($bill->recurring_until)
                            (Until {{ $bill->recurring_until->format('d/m/Y') }})
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Invoice Items -->
        <table>
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th width="50%">Description</th>
                    
                    <th width="120">Unit Price</th>
                    <th width="120">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->billItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->description }}</td>
                   
                    <td>GHC {{ number_format($item->unit_price, 2) }}</td>
                    <td>GHC {{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
        
            @if($bill->discount_amount > 0)
            <div class="totals-row">
                <span class="label">Discount ({{ $bill->discount_percentage }}%)</span>
                <span class="value" style="color: #dc3545;">-GHC{{ number_format($bill->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($bill->tax_amount > 0)
            <div class="totals-row">
                <span class="label">Tax ({{ $bill->tax_rate }}%)</span>
                <span class="value">+GHC{{ number_format($bill->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span class="label">Total Amount</span>
                <span class="value">GHC {{ number_format($bill->total_amount, 2) }}</span>
            </div>
            @if($bill->paid_amount > 0)
            <div class="totals-row">
                <span class="label">Paid Amount</span>
                <span class="value" style="color: #28a745;">GHC{{ number_format($bill->paid_amount, 2) }}</span>
            </div>
            <div class="totals-row {{ $bill->balance > 0 ? 'danger' : '' }}">
                <span class="label">Balance</span>
                <span class="value" style="color: {{ $bill->balance > 0 ? '#dc3545' : '#28a745' }};">
                    GHC {{ number_format($bill->balance, 2) }}
                </span>
            </div>
            @endif
        </div>

        <!-- Notes -->
        @if($bill->notes)
        <div class="notes">
            <strong>Notes:</strong>
            <p style="margin-top: 5px;">{{ $bill->notes }}</p>
        </div>
        @endif

        <!-- Terms -->
        @if($bill->terms)
        <div class="terms">
            <strong>Terms & Conditions:</strong>
            <p style="margin-top: 5px;">{{ $bill->terms }}</p>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signature">
            <div>
                <div class="signature-line"></div>
                <div class="signature-label">Authorized Signature</div>
            </div>
            <div>
                <div class="signature-line"></div>
                <div class="signature-label">Date</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a system-generated invoice. Please pay by the due date.</p>
            <p class="note">
                Generated on {{ now()->format('l, F d, Y h:i A') }} | 
                TALHA PREMIER INTERNATIONAL ACADEMY
            </p>
            @if($bill->status == 'paid')
                <p class="note" style="color: #28a745; font-weight: bold;">
                    <i class="fas fa-check-circle"></i> Invoice Paid
                </p>
            @elseif($bill->isOverdue())
                <p class="note" style="color: #dc3545; font-weight: bold;">
                    <i class="fas fa-exclamation-circle"></i> Invoice Overdue
                </p>
            @endif
        </div>
    </div>
</body>
</html>