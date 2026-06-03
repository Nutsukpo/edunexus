@extends('layouts.master')

@section('title', 'View Staff - ' . ($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''))

@section('content')

<div class="container-fluid py-4">

    <!-- MAIN PROFILE CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- TOP SECTION -->
        <div class="card-body p-4 p-lg-5 border-bottom">

            <div class="row align-items-center g-4">

                <!-- STAFF IMAGE -->
                <div class="col-lg-3 text-center">

                    @if(isset($staff->photo) && $staff->photo && file_exists(public_path('uploads/staff/'.$staff->photo)))

                        <img src="{{ asset('uploads/staff/'.$staff->photo) }}"
                             alt="{{ $staff->first_name }}"
                             class="img-fluid rounded-circle border border-4 shadow"
                             style="width: 220px; height: 220px; object-fit: cover;">

                    @else

                        <div class="rounded-circle border border-4 d-flex align-items-center justify-content-center mx-auto shadow"
                             style="width: 220px; height: 220px; font-size: 60px; font-weight: 700; background: #fff; color: #000;">

                            {{ strtoupper(substr($staff->first_name ?? 'S', 0, 1)) }}
                            {{ strtoupper(substr($staff->last_name ?? 'T', 0, 1)) }}

                        </div>

                    @endif

                </div>

                <!-- STAFF DETAILS -->
                <div class="col-lg-6">

                    <h2 class="fw-bold text-dark mb-2">

                        {{ $staff->first_name ?? '' }}
                        {{ $staff->last_name ?? '' }}

                    </h2>

                    <div class="mb-3">

                        <span class="badge bg-dark rounded-pill px-3 py-2">
                            {{ $staff->position ?? 'Staff Member' }}
                        </span>

                        <span class="badge border text-dark rounded-pill px-3 py-2">
                            {{ $staff->department ?? 'No Department' }}
                        </span>

                    </div>

                    <div class="mb-2">
                        <strong class="text-dark">Staff ID:</strong>
                        <span class="text-muted">
                            {{ $staff->staff_id ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <strong class="text-dark">Email:</strong>

                        @if($staff->email)
                            <a href="mailto:{{ $staff->email }}"
                               class="text-decoration-none text-muted">
                                {{ $staff->email }}
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>

                    <div>
                        <strong class="text-dark">Phone:</strong>

                        @if($staff->phone)
                            <a href="tel:{{ $staff->phone }}"
                               class="text-decoration-none text-muted">
                                {{ $staff->phone }}
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>

                    <div class="border rounded-4 p-3 bg-light">

                        <small class="text-muted d-block mb-1">
                            Staff Type
                        </small>

                        <div class="fw-semibold text-dark">
                            {{ $staff->staff_type ?? 'General Staff' }}
                        </div>
                     </div>

                </div>              
            </div>

        </div>

        <!-- INFORMATION SECTION -->
        <div class="card-body p-4">

            <div class="row g-4">

                <!-- PERSONAL INFO -->
                <div class="col-lg-6">

                    <div class="card border rounded-4 h-100">

                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-user-circle me-2"></i>
                                Personal Information
                            </h5>
                        </div>

                        <div class="card-body p-0">

                            <table class="table table-bordered table-striped mb-0 align-middle">

                                <tbody>

                                    <tr>
                                        <th width="35%" class="bg-light">First Name</th>
                                        <td>{{ $staff->first_name ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Last Name</th>
                                        <td>{{ $staff->last_name ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Other Name</th>
                                        <td>{{ $staff->other_name ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Gender</th>
                                        <td>{{ $staff->gender ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Date of Birth</th>

                                        <td>
                                            @if($staff->date_of_birth)
                                                {{ \Carbon\Carbon::parse($staff->date_of_birth)->format('F d, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Address</th>
                                        <td>{{ $staff->address ?? 'N/A' }}</td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- EMPLOYMENT INFO -->
                <div class="col-lg-6">

                    <div class="card border rounded-4 h-100">

                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-briefcase me-2"></i>
                                Employment Information
                            </h5>
                        </div>

                        <div class="card-body p-0">

                            <table class="table table-bordered table-striped mb-0 align-middle">

                                <tbody>

                                    <tr>
                                        <th width="35%" class="bg-light">
                                            Department
                                        </th>

                                        <td>
                                            {{ $staff->department ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">
                                            Position
                                        </th>

                                        <td>
                                            {{ $staff->position ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">
                                            Staff Type
                                        </th>

                                        <td>
                                            {{ $staff->staff_type ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">
                                            Salary
                                        </th>

                                        <td class="fw-bold text-dark">

                                            @if($staff->salary)
                                                GHS {{ number_format($staff->salary, 2) }}
                                            @else
                                                N/A
                                            @endif

                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">
                                            Employment Date
                                        </th>

                                        <td>

                                            @if($staff->date_employed)
                                                {{ \Carbon\Carbon::parse($staff->date_employed)->format('F d, Y') }}
                                            @else
                                                N/A
                                            @endif

                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">
                                            Status
                                        </th>

                                        <td>

                                            @if(($staff->status ?? '') == 'Active')

                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    Active
                                                </span>

                                            @else

                                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                                    {{ $staff->status ?? 'Inactive' }}
                                                </span>

                                            @endif

                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="card-footer bg-white border-top py-3 text-center">

            <small class="text-muted">

                <strong>Record ID:</strong>
                #{{ $staff->id }}

                @if(isset($staff->created_at))
                    |
                    <strong>Created:</strong>
                    {{ \Carbon\Carbon::parse($staff->created_at)->format('M d, Y h:i A') }}
                @endif

                @if(isset($staff->updated_at))
                    |
                    <strong>Updated:</strong>
                    {{ \Carbon\Carbon::parse($staff->updated_at)->format('M d, Y h:i A') }}
                @endif

            </small>

        </div>

    </div>

</div>

@endsection