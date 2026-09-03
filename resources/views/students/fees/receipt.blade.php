<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Receipt - {{ $payment->receipt_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            background: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
        }

        .receipt-wrapper {
            max-width: 850px;
            margin: 0 auto;
        }

        .receipt {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .receipt-header {
            background: #1456a0;
            color: #ffffff;
            padding: 30px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .school-name {
            font-size: 25px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .school-subtitle {
            font-size: 13px;
            opacity: 0.9;
        }

        .receipt-title {
            text-align: right;
        }

        .receipt-title h1 {
            margin: 0;
            font-size: 25px;
        }

        .receipt-title span {
            font-size: 13px;
            opacity: 0.9;
        }

        .receipt-body {
            padding: 35px;
        }

        .success-box {
            background: #ecfdf3;
            border: 1px solid #b7ebc6;
            color: #18794e;
            padding: 15px 18px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1456a0;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin: 25px 0 15px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .detail {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail:nth-child(odd) {
            border-right: 1px solid #e5e7eb;
        }

        .detail-label {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 15px;
            font-weight: 600;
        }

        .amount-box {
            margin-top: 25px;
            padding: 22px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .amount-label {
            font-size: 15px;
            color: #4b5563;
        }

        .amount {
            font-size: 28px;
            font-weight: 800;
            color: #1456a0;
        }

        .footer {
            border-top: 1px solid #e5e7eb;
            padding: 22px 35px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 11px 20px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-print {
            background: #1456a0;
            color: white;
        }

        .btn-back {
            background: #e5e7eb;
            color: #374151;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
            }

            .actions {
                display: none;
            }
        }

        @media (max-width: 650px) {
            .receipt-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .receipt-title {
                text-align: center;
            }

            .details {
                grid-template-columns: 1fr;
            }

            .detail:nth-child(odd) {
                border-right: none;
            }

            .amount-box {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="receipt-wrapper">

    <div class="receipt">

        {{-- HEADER --}}
        <div class="receipt-header">

            <div>
                <div class="school-name">
                    TALHA USMS
                </div>

                <div class="school-subtitle">
                    EDUNEXUS School Management System
                </div>
            </div>

            <div class="receipt-title">
                <h1>PAYMENT RECEIPT</h1>
                <span>Official Student Fee Receipt</span>
            </div>

        </div>


        <div class="receipt-body">

            {{-- PAYMENT STATUS --}}
            <div class="success-box">
                ✓ Payment Successfully Completed
            </div>


            {{-- STUDENT INFORMATION --}}
            <div class="section-title">
                Student Information
            </div>

            <div class="details">

                <div class="detail">
                    <span class="detail-label">Student ID</span>
                    <span class="detail-value">
                        {{ $student->student_id ?? 'N/A' }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Student Name</span>
                    <span class="detail-value">
                        {{ trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                    </span>
                </div>

            </div>


            {{-- PAYMENT INFORMATION --}}
            <div class="section-title">
                Payment Information
            </div>

            <div class="details">

                <div class="detail">
                    <span class="detail-label">Receipt Number</span>
                    <span class="detail-value">
                        {{ $payment->receipt_number }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Payment Date</span>
                    <span class="detail-value">
                        {{ $payment->payment_date
                            ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y')
                            : 'N/A' }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value">
                        {{ $payment->transaction_id ?? 'N/A' }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Reference Number</span>
                    <span class="detail-value">
                        {{ $payment->reference_number ?? 'N/A' }}
                    </span>
                </div>

            </div>


            {{-- AMOUNT --}}
            <div class="amount-box">

                <div class="amount-label">
                    Amount Paid
                </div>

                <div class="amount">
                    GHS {{ number_format((float) $payment->net_amount, 2) }}
                </div>

            </div>


            {{-- RECEIPT RECORD --}}
            
        @php
            $feeReceipt = $payment->receipt;
        @endphp

        @if($feeReceipt)

            <div class="section-title">
                Receipt Record
            </div>

            <div class="details">

                <div class="detail">
                    <span class="detail-label">Receipt ID</span>
                    <span class="detail-value">
                        {{ $feeReceipt->id }}
                    </span>
                </div>

                <div class="detail">
                    <span class="detail-label">Issued Date</span>
                    <span class="detail-value">
                        {{ $feeReceipt->created_at
                            ? $feeReceipt->created_at->format('d M Y H:i')
                            : 'N/A' }}
                    </span>
                </div>

            </div>

        @endif
        </div>


        {{-- FOOTER --}}
        <div class="footer">

            <strong>EDUNEXUS School Management System</strong>
            <br>

            This receipt confirms that the above payment has been recorded
            in the student's fee account.

            <div class="actions">

            <a
                href="{{ route('students.fees.receipt.pdf', $payment->id) }}"
                class="btn btn-print"
            >
                <i class="fas fa-file-pdf me-1"></i>
                Download PDF Receipt
            </a>
                <a
                    href="{{ route('students.fees') }}"
                    class="btn btn-back"
                >
                    Back to Fees
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>