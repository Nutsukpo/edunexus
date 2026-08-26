@extends('layouts.master')

@section('title', 'Leave Approvals')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fas fa-user-check text-primary me-2"></i>
                Leave Approvals
            </h4>

            <p class="text-muted mb-0">
                Review and process staff leave applications.
            </p>
        </div>

    </div>


    {{-- FLASH MESSAGES --}}

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif


    {{-- SUMMARY CARDS --}}

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>
                            <small class="text-muted">
                                Pending
                            </small>

                            <h3 class="fw-bold text-warning mb-0">
                                {{ $pendingCount }}
                            </h3>
                        </div>

                        <div class="text-warning fs-2">
                            <i class="fas fa-clock"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>
                            <small class="text-muted">
                                Approved
                            </small>

                            <h3 class="fw-bold text-success mb-0">
                                {{ $approvedCount }}
                            </h3>
                        </div>

                        <div class="text-success fs-2">
                            <i class="fas fa-check-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>
                            <small class="text-muted">
                                Rejected
                            </small>

                            <h3 class="fw-bold text-danger mb-0">
                                {{ $rejectedCount }}
                            </h3>
                        </div>

                        <div class="text-danger fs-2">
                            <i class="fas fa-times-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- FILTER CARD --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('leave-approvals.index') }}"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-lg-3">

                        <label class="form-label fw-semibold">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Name, designation, leave type..."
                        >

                    </div>


                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Statuses
                            </option>

                            <option
                                value="pending"
                                @selected(request('status') === 'pending')
                            >
                                Pending
                            </option>

                            <option
                                value="approved"
                                @selected(request('status') === 'approved')
                            >
                                Approved
                            </option>

                            <option
                                value="rejected"
                                @selected(request('status') === 'rejected')
                            >
                                Rejected
                            </option>

                        </select>

                    </div>


                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            Leave Type
                        </label>

                        <select
                            name="leave_type"
                            class="form-select"
                        >

                            <option value="">
                                All Types
                            </option>

                            @foreach($leaveTypes as $type)

                                <option
                                    value="{{ $type }}"
                                    @selected(request('leave_type') === $type)
                                >
                                    {{ $type }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            From
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}"
                        >

                    </div>


                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            To
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}"
                        >

                    </div>


                    <div class="col-lg-1">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="fas fa-filter"></i>
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- LEAVE TABLE --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">
                    Leave Applications
                </h5>

                <span class="badge bg-light text-dark">
                    {{ $leaves->total() }} record(s)
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            @if($leaves->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-3">
                                    #
                                </th>

                                <th>
                                    Employee
                                </th>

                                <th>
                                    Leave Type
                                </th>

                                <th>
                                    Commencement
                                </th>

                                <th>
                                    Resumption
                                </th>

                                <th class="text-center">
                                    Applied
                                </th>

                                <th class="text-center">
                                    Granted
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="text-end px-3">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($leaves as $index => $leave)

                                <tr>

                                    <td class="px-3">
                                        {{ $leaves->firstItem() + $index }}
                                    </td>


                                    <td>

                                        <div class="fw-semibold">
                                            {{ $leave->full_name }}
                                        </div>

                                        @if($leave->designation)
                                            <small class="text-muted">
                                                {{ $leave->designation }}
                                            </small>
                                        @endif

                                    </td>


                                    <td>
                                        {{ $leave->leave_type ?? 'N/A' }}
                                    </td>


                                    <td>
                                        {{ optional($leave->date_commencement)->format('d M Y') }}
                                    </td>


                                    <td>
                                        {{ optional($leave->date_resumption)->format('d M Y') }}
                                    </td>


                                    <td class="text-center">

                                        <span class="badge bg-light text-dark">
                                            {{ $leave->days_applied_for ?? 0 }}
                                        </span>

                                    </td>


                                    <td class="text-center">

                                        @if($leave->days_granted !== null)

                                            <span class="badge bg-success">
                                                {{ $leave->days_granted }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        @if($leave->status === 'pending')

                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>

                                        @elseif($leave->status === 'approved')

                                            <span class="badge bg-success">
                                                Approved
                                            </span>

                                        @elseif($leave->status === 'rejected')

                                            <span class="badge bg-danger">
                                                Rejected
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                {{ ucfirst($leave->status) }}
                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-end px-3">

                                        <a
                                            href="{{ route('leave-approvals.show', $leave) }}"
                                            class="btn btn-sm btn-primary"
                                        >

                                            <i class="fas fa-eye me-1"></i>

                                            Review

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- PAGINATION --}}

                <div class="p-3 border-top">

                    {{ $leaves->links() }}

                </div>

            @else

                <div class="text-center py-5">

                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>

                    <h5 class="fw-bold">
                        No Leave Applications Found
                    </h5>

                    <p class="text-muted mb-0">
                        There are no leave applications matching your filters.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection