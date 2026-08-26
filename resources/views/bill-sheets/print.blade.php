<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Sheet - {{ $billSheet->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background: #fff;
        }
        .print-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        .info-table .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 30%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
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
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .grand-total {
            font-size: 18px;
            color: #28a745;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            color: #fff;
            font-size: 12px;
        }
        .status-draft { background-color: #6c757d; }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-approved { background-color: #28a745; }
        .status-rejected { background-color: #dc3545; }
        .status-published { background-color: #17a2b8; }
        .status-archived { background-color: #6c757d; }
        
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            .print-container { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Print Button -->
        <div class="no-print" style="margin-bottom:20px; text-align:right;">
            <button onclick="window.print()" style="padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer;">
                🖨️ Print
            </button>
            <button onclick="window.close()" style="padding:10px 20px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer; margin-left:10px;">
                Close
            </button>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>Bill Sheet</h1>
            <p><strong>{{ $billSheet->name }}</strong></p>
            <p>Generated: {{ $billSheet->generated_date->format('d-m-Y') }}</p>
            @if($billSheet->due_date)
                <p>Due Date: {{ $billSheet->due_date->format('d-m-Y') }}</p>
            @endif
        </div>

        <!-- Information -->
        <table class="info-table">
            <tr>
                <td class="label">Class</td>
                <td>{{ $billSheet->studentClass->name ?? 'N/A' }}</td>
                <td class="label">Academic Year</td>
                <td>{{ $billSheet->academicYear->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Term</td>
                <td>{{ $billSheet->term->name ?? 'N/A' }}</td>
                <td class="label">Status</td>
                <td>
                    <span class="status-badge status-{{ $billSheet->status }}">
                        {{ ucfirst($billSheet->status) }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Items -->
        <h3 style="margin-top: 20px;">Bill Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Fee Category</th>
                    <th class="text-end">Amount (GHS)</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total (GHS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billSheet->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->feeCategory->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-end">Sub Total:</td>
                    <td class="text-end">{{ number_format($billSheet->total_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-end">Discount:</td>
                    <td class="text-end" style="color:#dc3545;">- {{ number_format($billSheet->discount_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-end">Tax:</td>
                    <td class="text-end" style="color:#ffc107;">+ {{ number_format($billSheet->tax_amount, 2) }}</td>
                </tr>
                <tr class="total-row grand-total">
                    <td colspan="5" class="text-end" style="font-size:16px;">Grand Total:</td>
                    <td class="text-end" style="font-size:18px; color:#28a745;">
                        GHS {{ number_format($billSheet->net_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Description -->
        @if($billSheet->description)
            <div style="margin-top:20px;">
                <strong>Description:</strong>
                <p style="margin-top:5px; padding:10px; background:#f8f9fa; border-radius:4px;">
                    {{ $billSheet->description }}
                </p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Generated on: {{ now()->format('d-m-Y H:i:s') }}</p>
            <p>This is a computer-generated document. No signature is required.</p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            // Uncomment the line below if you want auto-print
            // window.print();
        }
    </script>
</body>
</html>