@extends('layouts.master')

@section('title', 'Invoice Details')

@section('content')

<style>

.invoice-wrapper{
    max-width: 950px;
    margin: auto;
}

.invoice-card{
    border-radius: 12px;
    overflow: hidden;
}

.invoice-header{
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 25px;
}

.summary-box{
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    height: 100%;
}

.summary-title{
    font-size: 12px;
    text-transform: uppercase;
    color: #6c757d;
    font-weight: 600;
}

.summary-value{
    font-size: 20px;
    font-weight: 700;
}

.invoice-table th{
    background: #f8f9fa;
}

.signature-box{
    margin-top: 60px;
}

@media print {

    .no-print{
        display:none !important;
    }

    body{
        background:#fff !important;
    }

    .card{
        border:none !important;
        box-shadow:none !important;
    }

    .invoice-card{
        box-shadow:none !important;
    }
}

</style>

<div class="container py-4">
{{-- ACTION BUTTONS --}}
<div class="invoice-wrapper mb-3 no-print">

    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h4 class="fw-bold mb-1">
                Student Billing Invoice
            </h4>

            <small class="text-muted">
                
            </small>
        </div>

        <div class="btn-group">

            <button id="downloadPdfBtn"
                    class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i>
                 PDF
            </button>

            <a href="{{ route('billing.index') }}"
               class="btn btn-secondary">
                Back
            </a>

        </div>

    </div>

</div>

{{-- PDF LOADER --}}
<div id="pdfLoading"
     style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;justify-content:center;align-items:center;">

    <div class="bg-white p-4 rounded text-center">

        <i class="fas fa-spinner fa-spin fa-3x text-danger mb-3"></i>

        <h5>Generating PDF...</h5>

        <p class="mb-0">Please wait</p>

    </div>

</div>

<div class="invoice-wrapper">

    <div id="invoiceContent"
         class="card shadow invoice-card">

        {{-- HEADER --}}
        <div class="invoice-header">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <h2 class="fw-bold text-success mb-1">
                        KABORE SCHOOL
                    </h2>

                    <p class="text-muted mb-0">
                        Student Billing Invoice
                    </p>

                    <small class="text-muted">
                        Ho, Volta Region, Ghana
                    </small>

                </div>

                <div class="col-md-6 text-md-end">

                    <h4 class="fw-bold">
                        INVOICE
                    </h4>

                    <p class="mb-1">
                        <strong>No:</strong>
                        {{ $billing->invoice_number }}
                    </p>

                    <p class="mb-1">
                        <strong>Date:</strong>
                        {{ $billing->created_at->format('d M Y') }}
                    </p>

                    <p class="mb-0">
                        <strong>Due:</strong>
                        {{ $billing->created_at->addDays(30)->format('d M Y') }}
                    </p>

                </div>

            </div>

        </div>

        <div class="card-body p-4">

            {{-- SUMMARY --}}
            <div class="row g-3 mb-4">

                <div class="col-md-4">

                    <div class="summary-box">

                        <div class="summary-title">
                            Student
                        </div>

                        <strong>
                            {{ $billing->student?->full_name }}
                        </strong>

                        <br>

                        <small class="text-muted">
                            {{ $billing->student?->student_id }}
                        </small>

                        <br>

                        <small class="text-muted">
                            Class:
                            {{ $billing->studentClass?->name ?? 'N/A' }}
                        </small>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="summary-box">

                        <div class="summary-title">
                            Total Amount
                        </div>

                        <div class="summary-value text-primary">
                            GH₵ {{ number_format($billing->total_amount,2) }}
                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="summary-box">

                        <div class="summary-title">
                            Outstanding Balance
                        </div>

                        <div class="summary-value text-danger">
                            GH₵ {{ number_format($billing->balance,2) }}
                        </div>

                    </div>

                </div>

            </div>

            {{-- STATUS --}}
            <div class="mb-4">

                @if($billing->status == 'Paid')

                    <span class="badge bg-success px-3 py-2">
                        Paid
                    </span>

                @elseif($billing->status == 'Partially Paid')

                    <span class="badge bg-warning text-dark px-3 py-2">
                        Partially Paid
                    </span>

                @else

                    <span class="badge bg-danger px-3 py-2">
                        Unpaid
                    </span>

                @endif

            </div>

            {{-- ACADEMIC INFO --}}
            <div class="row mb-4">

                <div class="col-md-6">

                    <strong>Academic Year:</strong>
                    {{ $billing->academicYear?->name ?? 'N/A' }}

                </div>

                <div class="col-md-6">

                    <strong>Term:</strong>
                    {{ $billing->term?->name ?? 'N/A' }}

                </div>

            </div>

            {{-- ITEMS --}}
            <table class="table table-bordered invoice-table">

                <thead>

                    <tr>
                        <th width="5%">#</th>
                        <th>Description</th>
                        <th width="20%" class="text-end">
                            Amount
                        </th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($billing->items as $key => $item)

                        <tr>

                            <td>{{ $key + 1 }}</td>

                            <td>{{ $item->description }}</td>

                            <td class="text-end">
                                GH₵ {{ number_format($item->amount,2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="3"
                                class="text-center text-muted">
                                No invoice items found
                            </td>

                        </tr>

                    @endforelse

                </tbody>

                <tfoot>

                    <tr>

                        <th colspan="2"
                            class="text-end">
                            Total
                        </th>

                        <th class="text-end">
                            GH₵ {{ number_format($billing->total_amount,2) }}
                        </th>

                    </tr>

                    <tr>

                        <th colspan="2"
                            class="text-end">
                            Amount Paid
                        </th>

                        <th class="text-end text-success">
                            GH₵ {{ number_format($billing->amount_paid ?? 0,2) }}
                        </th>

                    </tr>

                    <tr>

                        <th colspan="2"
                            class="text-end">
                            Balance Due
                        </th>

                        <th class="text-end text-danger">
                            GH₵ {{ number_format($billing->balance,2) }}
                        </th>

                    </tr>

                </tfoot>

            </table>

            {{-- SIGNATURES --}}
            <div class="row signature-box">

                <div class="col-6">

                    _______________________

                    <br>

                    <strong>Finance Officer</strong>

                </div>

                <div class="col-6 text-end">

                    _______________________

                    <br>

                    <strong>Parent / Guardian</strong>

                </div>

            </div>

            {{-- FOOTER --}}
            <div class="text-center mt-5 text-muted">

                This invoice was generated automatically by

                <strong>EDUNEXUS School ERP</strong>

            </div>

        </div>

    </div>

    {{-- PAYMENT NOTICE --}}
    <div class="alert alert-info mt-3 no-print">

        <strong>Payment Instructions</strong>

        <hr>

        Bank:
        Consolidated Bank Ghana (CBG)

        <br>

        Account Name:
        Kabore School

        <br>

        Account Number:
        1234567890

        <br>

        Reference:
        {{ $billing->invoice_number }}

    </div>

</div>
```

</div>

@endsection

@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>

document.getElementById('downloadPdfBtn').addEventListener('click', function () {

    document.getElementById('pdfLoading').style.display = 'flex';

    const element = document.getElementById('invoiceContent');

    html2pdf()
        .set({
            margin: 0.4,
            filename: 'Invoice-{{ $billing->invoice_number }}.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        })
        .from(element)
        .save()
        .then(() => {
            document.getElementById('pdfLoading').style.display = 'none';
        });

});

</script>

@endpush
