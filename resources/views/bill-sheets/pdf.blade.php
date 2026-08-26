<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Sheet - {{ $billSheet->name }}</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #333;
            font-size: 12px;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Header Styles */
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .header .meta-info {
            margin-top: 10px;
            font-size: 13px;
            color: #555;
        }
        
        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 12px;
        }
        
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        
        .info-table .label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 15%;
        }
        
        .info-table .value {
            width: 35%;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        
        .items-table th {
            background-color: #34495e;
            color: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        
        .items-table .text-end {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .items-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Totals Section */
        .totals-table {
            width: 100%;
            max-width: 400px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .totals-table td {
            padding: 6px 12px;
            border: 1px solid #ddd;
        }
        
        .totals-table .label {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .totals-table .total-row {
            background-color: #e8f5e9;
        }
        
        .totals-table .grand-total {
            background-color: #2e7d32;
            color: #fff;
        }
        
        .totals-table .grand-total .label {
            background-color: #2e7d32;
            color: #fff;
            font-size: 16px;
        }
        
        .totals-table .grand-total .value {
            font-size: 18px;
            font-weight: bold;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #95a5a6; }
        .status-pending { background-color: #f39c12; }
        .status-approved { background-color: #27ae60; }
        .status-rejected { background-color: #e74c3c; }
        .status-published { background-color: #2980b9; }
        .status-archived { background-color: #7f8c8d; }
        
        /* Description */
        .description-box {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        
        .description-box strong {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .description-box p {
            margin: 0;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #2c3e50;
            text-align: center;
            color: #7f8c8d;
            font-size: 11px;
        }
        
        .footer .footer-line {
            margin: 5px 0;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
        
        /* Print Styles */
        @media print {
            body {
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Watermark -->
        @if($billSheet->status == 'draft')
            <div class="watermark">DRAFT</div>
        @elseif($billSheet->status == 'approved')
            <div class="watermark">APPROVED</div>
        @elseif($billSheet->status == 'rejected')
            <div class="watermark">REJECTED</div>
        @endif

        <!-- Header -->
        <div class="header">
            <h1>Bill Sheet</h1>
            <div class="subtitle">{{ $billSheet->name }}</div>
            <div class="meta-info">
                <span>Generated: {{ $billSheet->generated_date->format('d-m-Y') }}</span>
                @if($billSheet->due_date)
                    <span> | Due Date: {{ $billSheet->due_date->format('d-m-Y') }}</span>
                @endif
                <span> | Status: <span class="status-badge status-{{ $billSheet->status }}">{{ ucfirst($billSheet->status) }}</span></span>
            </div>
        </div>

        <!-- Information Table -->
        <table class="info-table">
            <tr>
                <td class="label">Class</td>
                <td class="value">{{ $billSheet->studentClass->name ?? 'N/A' }}</td>
                <td class="label">Academic Year</td>
                <td class="value">{{ $billSheet->academicYear->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Term</td>
                <td class="value">{{ $billSheet->term->name ?? 'N/A' }}</td>
                <td class="label">Generated By</td>
                <td class="value">{{ $billSheet->generatedBy->name ?? 'N/A' }}</td>
            </tr>
            @if($billSheet->approved_at)
                <tr>
                    <td class="label">Approved At</td>
                    <td class="value">{{ $billSheet->approved_at->format('d-m-Y H:i') }}</td>
                    <td class="label">Approved By</td>
                    <td class="value">{{ $billSheet->approvedBy->name ?? 'N/A' }}</td>
                </tr>
            @endif
        </table>

        <!-- Bill Items -->
        <h3 style="color: #2c3e50; margin-top: 20px;">Bill Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Item Name</th>
                    <th style="width: 25%;">Fee Category</th>
                    <th style="width: 12%;" class="text-end">Amount (GHS)</th>
                    <th style="width: 8%;" class="text-center">Qty</th>
                    <th style="width: 15%;" class="text-end">Total (GHS)</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach($billSheet->items as $item)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->feeCategory->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td class="label" style="width: 70%;">Sub Total:</td>
                <td class="value text-end" style="width: 30%;">GHS {{ number_format($billSheet->total_amount, 2) }}</td>
            </tr>
            @if($billSheet->discount_amount > 0)
                <tr>
                    <td class="label">Discount:</td>
                    <td class="value text-end" style="color: #e74c3c;">- GHS {{ number_format($billSheet->discount_amount, 2) }}</td>
                </tr>
            @endif
            @if($billSheet->tax_amount > 0)
                <tr>
                    <td class="label">Tax:</td>
                    <td class="value text-end" style="color: #2980b9;">+ GHS {{ number_format($billSheet->tax_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td class="label" style="font-size: 16px; font-weight: bold;">Grand Total:</td>
                <td class="value text-end" style="font-size: 18px; font-weight: bold;">
                    GHS {{ number_format($billSheet->net_amount, 2) }}
                </td>
            </tr>
        </table>

        <!-- Description -->
        @if($billSheet->description)
            <div class="description-box">
                <strong><i class="fas fa-info-circle"></i> Description:</strong>
                <p>{{ $billSheet->description }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-line">
                <strong>{{ config('app.name', 'School Management System') }}</strong>
            </div>
            <div class="footer-line">
                Generated on: {{ now()->format('d-m-Y H:i:s') }}
            </div>
            <div class="footer-line" style="font-size: 10px; color: #95a5a6;">
                This is a computer-generated document. No signature is required.
            </div>
            <div class="footer-line" style="font-size: 10px; color: #95a5a6;">
                Document #: BS-{{ str_pad($billSheet->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>
    </div>
</body>
</html>