@extends('layouts.master')

@section('title', 'Review Bill Sheets')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-start mb-4">

        <div>

            @php

                $student = $assignment->student;

                $studentName = $student
                    ? trim(
                        collect([
                            $student->first_name ?? '',
                            $student->middle_name ?? '',
                            $student->last_name ?? '',
                        ])->filter()->implode(' ')
                    )
                    : 'N/A';

            @endphp


            <h4 class="fw-bold mb-1">

                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>

                Bill Sheet Approval

            </h4>


            <div class="text-muted">

                <strong>{{ $studentName }}</strong>

                &nbsp; • &nbsp;

                {{ $assignment->studentClass?->name ?? 'N/A' }}

                &nbsp; • &nbsp;

                {{ $assignment->academicYear?->name ?? 'N/A' }}

            </div>

        </div>


        <a href="{{ route('bill-sheets.approval.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left me-1"></i>

            Back

        </a>

    </div>


    {{-- FLASH MESSAGES --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle me-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- SUMMARY --}}

    <div class="row g-3 mb-4">

        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        Total Bill Sheets
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $billSheets->count() }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        Pending
                    </small>

                    <h3 class="fw-bold text-warning mb-0">
                        {{ $pendingCount }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        Approved
                    </small>

                    <h3 class="fw-bold text-success mb-0">
                        {{ $approvedCount }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        Rejected
                    </small>

                    <h3 class="fw-bold text-danger mb-0">
                        {{ $rejectedCount }}
                    </h3>

                </div>

            </div>

        </div>

    </div>


    {{-- BULK ACTIONS --}}

    @if($pendingCount > 0)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">
                            Bulk Approval
                        </h5>

                        <p class="text-muted mb-0">

                            There are
                            <strong>{{ $pendingCount }}</strong>
                            pending Bill Sheets for this student.

                        </p>

                    </div>


                    <div class="d-flex gap-2">

                        {{-- APPROVE ALL --}}

                        <form action="{{ route(
                            'bill-sheets.approval.approve-all',
                            $assignment
                        ) }}"
                              method="POST"
                              class="approve-all-form">

                            @csrf

                            <button type="submit"
                                    class="btn btn-success">

                                <i class="fas fa-check-double me-1"></i>

                                Approve All Pending

                            </button>

                        </form>


                        {{-- REJECT ALL --}}

                        <button type="button"
                                class="btn btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal">

                            <i class="fas fa-times-circle me-1"></i>

                            Reject All Pending

                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif


    {{-- BILL SHEETS --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                Student Bill Sheets
            </h5>

        </div>


        <div class="card-body p-0">

            @if($billSheets->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>#</th>

                                <th>Bill Sheet</th>

                                <th>Term</th>

                                <th>Generated Date</th>

                                <th class="text-end">
                                    Amount
                                </th>

                                <th class="text-end">
                                    Net Amount
                                </th>

                                <th class="text-center">
                                    Status
                                </th>

                                <th class="text-end">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($billSheets as $billSheet)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>


                                    <td>

                                        <div class="fw-semibold">

                                            {{ $billSheet->name }}

                                        </div>

                                        <small class="text-muted">

                                            #{{ $billSheet->id }}

                                        </small>

                                    </td>


                                    <td>

                                        {{ $billSheet->term?->name ?? 'N/A' }}

                                    </td>


                                    <td>

                                        {{ optional(
                                            $billSheet->generated_date
                                        )->format('d M Y') }}

                                    </td>


                                    <td class="text-end">

                                        GHS
                                        {{ number_format(
                                            (float) $billSheet->total_amount,
                                            2
                                        ) }}

                                    </td>


                                    <td class="text-end fw-semibold">

                                        GHS
                                        {{ number_format(
                                            (float) $billSheet->net_amount,
                                            2
                                        ) }}

                                    </td>


                                    <td class="text-center">

                                        @switch($billSheet->status)

                                            @case('pending')

                                                <span class="badge bg-warning text-dark">
                                                    Pending
                                                </span>

                                                @break

                                            @case('approved')

                                                <span class="badge bg-success">
                                                    Approved
                                                </span>

                                                @break

                                            @case('rejected')

                                                <span class="badge bg-danger">
                                                    Rejected
                                                </span>

                                                @break

                                            @case('published')

                                                <span class="badge bg-primary">
                                                    Published
                                                </span>

                                                @break

                                            @default

                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($billSheet->status) }}
                                                </span>

                                        @endswitch

                                    </td>


                                    <td class="text-end">

                                        <a href="{{ route(
                                            'bill-sheets.show',
                                            $billSheet
                                        ) }}"
                                           class="btn btn-sm btn-outline-primary">

                                            <i class="fas fa-eye"></i>

                                            View

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5">

                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>

                    <h5>
                        No Bill Sheets Found
                    </h5>

                </div>

            @endif

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- REJECTION MODAL --}}
{{-- ========================================================= --}}

@if($pendingCount > 0)

<div class="modal fade"
     id="rejectModal"
     tabindex="-1"
     aria-labelledby="rejectModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form action="{{ route(
                'bill-sheets.approval.reject-all',
                $assignment
            ) }}"
                  method="POST">

                @csrf


                <div class="modal-header">

                    <h5 class="modal-title fw-bold"
                        id="rejectModalLabel">

                        <i class="fas fa-times-circle text-danger me-2"></i>

                        Reject All Pending Bill Sheets

                    </h5>


                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body">

                    <div class="alert alert-warning">

                        You are about to reject

                        <strong>
                            {{ $pendingCount }}
                        </strong>

                        pending Bill Sheet(s) belonging to:

                        <strong>
                            {{ $studentName }}
                        </strong>.

                    </div>


                    <div class="mb-3">

                        <label for="rejection_reason"
                               class="form-label fw-semibold">

                            Rejection Reason

                            <span class="text-danger">*</span>

                        </label>


                        <textarea name="rejection_reason"
                                  id="rejection_reason"
                                  rows="4"
                                  class="form-control"
                                  placeholder="Enter the reason for rejecting these Bill Sheets..."
                                  required></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>


                    <button type="submit"
                            class="btn btn-danger">

                        <i class="fas fa-times-circle me-1"></i>

                        Reject All

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endif


@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const approveForm =
        document.querySelector('.approve-all-form');

    if (approveForm) {

        approveForm.addEventListener('submit', function (event) {

            const confirmed = confirm(
                'Are you sure you want to approve all pending Bill Sheets for this student?'
            );

            if (!confirmed) {

                event.preventDefault();

            }

        });

    }

});

</script>

@endpush