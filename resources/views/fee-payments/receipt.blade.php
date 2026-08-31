<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    @php
        $pdfMode = $pdfMode ?? false;

        $student = $payment->student;
        $assignment = $payment->studentClassAssignment;
        $billSheet = $payment->billSheet;

        /*
        |--------------------------------------------------------------------------
        | STUDENT NAME
        |--------------------------------------------------------------------------
        */

        $studentName = trim(collect([
            $student->first_name ?? null,
            $student->middle_name ?? null,
            $student->last_name ?? null,
        ])->filter()->implode(' '));

        if (!$studentName && !empty($student->full_name)) {
            $studentName = $student->full_name;
        }

        if (!$studentName && !empty($student->name)) {
            $studentName = $student->name;
        }

        if (!$studentName && !empty($student->student_name)) {
            $studentName = $student->student_name;
        }

        $studentName = $studentName ?: 'N/A';

        /*
        |--------------------------------------------------------------------------
        | CLASS & ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $className = optional(optional($assignment)->studentClass)->name ?? 'N/A';
        $academicYear = optional(optional($assignment)->academicYear)->name ?? 'N/A';

        /*
        |--------------------------------------------------------------------------
        | AMOUNTS
        |--------------------------------------------------------------------------
        */

        $amount = (float) ($payment->amount ?? 0);
        $penalty = (float) ($payment->penalty_amount ?? 0);
        $discount = (float) ($payment->discount_amount ?? 0);
        $netAmount = (float) ($payment->net_amount ?? ($amount + $penalty - $discount));

        $billTotal = (float) (
            optional($billSheet)->net_amount
            ?? optional($billSheet)->total_amount
            ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | DATES
        |--------------------------------------------------------------------------
        */

        $paymentDate = $payment->payment_date
            ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y')
            : optional($payment->created_at)->format('d M Y');

        $paymentTime = optional($payment->created_at)->format('h:i A');

        /*
        |--------------------------------------------------------------------------
        | PAYMENT DETAILS
        |--------------------------------------------------------------------------
        */

        $paymentMethod = ucwords(str_replace('_', ' ', $payment->payment_method ?? 'N/A'));
        $paymentType = ucwords(str_replace('_', ' ', $payment->payment_type ?? 'N/A'));
        $currency = 'GHS';

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status = strtolower($payment->status ?? '');
        $statusClass = match ($status) {
            'paid' => 'status-paid',
            'pending' => 'status-pending',
            'failed' => 'status-failed',
            'cancelled' => 'status-cancelled',
            'refunded' => 'status-refunded',
            default => '',
        };
    @endphp

    <title>Payment Receipt - {{ $payment->receipt_number ?? 'N/A' }}</title>

    <style>
        @page {
            margin: 16mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            color: #212529;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .receipt {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
        }

        .header {
            border-bottom: 2px solid #212529;
            padding: 8px 0 14px;
            margin-bottom: 18px;
        }

        .receipt-number {
            float: right;
            text-align: right;
            line-height: 1.5;
        }

        .school-name {
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .document-title {
            font-size: 17px;
            font-weight: 700;
            margin-top: 6px;
        }

        .clearfix {
            clear: both;
        }

        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #f1f1f1;
            border: 1px solid #d8d8d8;
            padding: 7px 9px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td,
        .items th,
        .items td {
            border: 1px solid #d5d5d5;
            padding: 8px;
            vertical-align: top;
        }

        .details .label {
            width: 17%;
            font-weight: 700;
            background: #fafafa;
        }

        .items th {
            background: #f1f1f1;
            font-weight: 700;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total-row td {
            font-weight: 700;
        }

        .amount-box {
            margin-top: 14px;
            border: 2px solid #212529;
            padding: 13px;
            text-align: center;
            background: #fafafa;
        }

        .amount-label {
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .amount {
            font-size: 23px;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            border: 1px solid #333;
            padding: 4px 12px;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 10px;
            border-radius: 4px;
        }

        .status-paid {
            border-color: #28a745;
            color: #28a745;
            background: #d4edda;
        }

        .status-pending {
            border-color: #ffc107;
            color: #856404;
            background: #fff3cd;
        }

        .status-failed {
            border-color: #dc3545;
            color: #dc3545;
            background: #f8d7da;
        }

        .status-cancelled {
            border-color: #6c757d;
            color: #6c757d;
            background: #e2e3e5;
        }

        .status-refunded {
            border-color: #17a2b8;
            color: #17a2b8;
            background: #d1ecf1;
        }

        .signature {
            margin-top: 34px;
        }

        .signature-line {
            width: 220px;
            border-top: 1px solid #212529;
            padding-top: 5px;
            text-align: center;
        }

        .footer {
            margin-top: 28px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
<div class="receipt">

    {{-- ============================================================= --}}
    {{-- HEADER --}}
    {{-- ============================================================= --}}

    <div class="header">
        <div class="receipt-number">
            <strong>Receipt No.</strong><br>
            {{ $payment->receipt_number ?? 'N/A' }}
        </div>

        <div class="school-name">
            TALHA PREMIER INTERNATIONAL ACADEMY
        </div>

        <div class="document-title">
            OFFICIAL FEE PAYMENT RECEIPT
        </div>

        <div class="clearfix"></div>
    </div>

    {{-- ============================================================= --}}
    {{-- STUDENT INFORMATION --}}
    {{-- ============================================================= --}}

    <div class="section">
        <div class="section-title">Student Information</div>

        <table class="details">
            <tr>
                <td class="label">Student ID</td>
                <td>{{ $student->student_id ?? 'N/A' }}</td>

                <td class="label">Student Name</td>
                <td><strong>{{ $studentName }}</strong></td>
            </tr>

            <tr>
                <td class="label">Class</td>
                <td>{{ $className }}</td>

                <td class="label">Academic Year</td>
                <td>{{ $academicYear }}</td>
            </tr>

            <tr>
                <td class="label">Receipt Date</td>
                <td>{{ $paymentDate }}</td>

                <td class="label">Payment Time</td>
                <td>{{ $paymentTime ?: 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- ============================================================= --}}
    {{-- PAYMENT INFORMATION --}}
    {{-- ============================================================= --}}

    <div class="section">
        <div class="section-title">Payment Information</div>

        <table class="details">
            <tr>
                <td class="label">Payment Method</td>
                <td>{{ $paymentMethod }}</td>

                <td class="label">Payment Type</td>
                <td>{{ $paymentType }}</td>
            </tr>

            <tr>
                <td class="label">Transaction ID</td>
                <td>{{ $payment->transaction_id ?: 'N/A' }}</td>

                <td class="label">Reference</td>
                <td>{{ $payment->transaction_id ?: 'N/A' }}</td>
            </tr>

            @if($payment->bank_name || $payment->cheque_number)
                <tr>
                    <td class="label">Bank</td>
                    <td>{{ $payment->bank_name ?: 'N/A' }}</td>

                    <td class="label">Cheque No.</td>
                    <td>{{ $payment->cheque_number ?: 'N/A' }}</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- ============================================================= --}}
    {{-- PAYMENT BREAKDOWN --}}
    {{-- ============================================================= --}}

    <div class="section">
        <div class="section-title">Payment Breakdown</div>

        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right">Amount ({{ $currency }})</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Payment Amount</td>
                    <td class="right">{{ number_format($amount, 2) }}</td>
                </tr>

                @if($penalty > 0)
                    <tr>
                        <td>Penalty</td>
                        <td class="right">{{ number_format($penalty, 2) }}</td>
                    </tr>
                @endif

                @if($discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td class="right">-{{ number_format($discount, 2) }}</td>
                    </tr>
                @endif

                <tr class="total-row">
                    <td>NET PAYMENT</td>
                    <td class="right">{{ number_format($netAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="amount-box">
            <div class="amount-label">Amount Received</div>
            <div class="amount">
                {{ $currency }} {{ number_format($netAmount, 2) }}
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- BILL SHEET ITEMS --}}
    {{-- The original commented section remains commented. --}}
    {{-- ============================================================= --}}

    <!--
    @if($billSheet && $billTotal > 0)
        <div class="section">
            <div class="section-title">Bill Sheet Items</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="right">Amount ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billSheet->items ?? [] as $item)
                        <tr>
                            <td>{{ $item->name ?? 'Fee Item' }}</td>
                            <td class="right">
                                {{ number_format((float) ($item->total_amount ?? $item->amount ?? 0), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="center">No Bill Sheet items recorded.</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td>Bill Sheet Total</td>
                        <td class="right">{{ number_format($billTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
    -->

    {{-- ============================================================= --}}
    {{-- PAYMENT STATUS --}}
    {{-- The original commented section remains commented. --}}
    {{-- ============================================================= --}}

    <!--
    <div class="section">
        <div class="section-title">Payment Status</div>

        <p>
            Status:
            <span class="status {{ $statusClass }}">
                {{ strtoupper($payment->status ?? 'N/A') }}
            </span>
        </p>

        @if($payment->notes)
            <p>
                <strong>Notes:</strong><br>
                {{ $payment->notes }}
            </p>
        @endif
    </div>
    -->

    {{-- ============================================================= --}}
    {{-- SIGNATURE --}}
    {{-- ============================================================= --}}

    <div class="signature">
        <div class="signature-line">
            Authorized Officer
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- FOOTER --}}
    {{-- ============================================================= --}}

    <div class="footer">
        This is an electronically generated payment receipt.<br>
        Receipt No: {{ $payment->receipt_number ?? 'N/A' }}
    </div>

</div>
</body>
</html>
