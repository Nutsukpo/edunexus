@extends('layouts.app')

@section('title', 'Student Assignment History')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-history me-2"></i>
                Student Assignment History
            </h4>
            <p class="text-muted mb-0">
                View the complete academic class history of a student.
            </p>
        </div>

        <a href="{{ route('student-class-assignments.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Assignments
        </a>
    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- Student Information --}}
    @if(isset($student))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">

                    {{-- Photo --}}
                    <div class="col-auto">
                        @if(!empty($student->photo))
                            <img src="{{ asset('storage/' . $student->photo) }}"
                                 alt="{{ $student->first_name }}"
                                 class="rounded-circle"
                                 style="width:80px;height:80px;object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:80px;height:80px;font-size:28px;">
                                {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="col">
                        <h5 class="fw-bold mb-1">
                            {{ $student->first_name }}
                            {{ $student->middle_name ?? '' }}
                            {{ $student->last_name }}
                        </h5>

                        <div class="text-muted">
                            <span class="me-3">
                                <i class="fas fa-id-card me-1"></i>
                                {{ $student->student_id }}
                            </span>

                            @if(isset($student->gender))
                                <span class="me-3">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $student->gender }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Current Assignment --}}
                    @php
                        $currentAssignment = $assignments->first(function ($assignment) {
                            return (bool) $assignment->is_current;
                        });
                    @endphp

                    <div class="col-auto text-end">
                        <small class="text-muted d-block mb-1">
                            Current Class
                        </small>

                        @if($currentAssignment)
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                {{ $currentAssignment->studentClass->name ?? 'N/A' }}
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                No Current Assignment
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif


    {{-- Current System Academic Period --}}
    @if(isset($settings))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                    <div>
                        <small class="text-uppercase text-muted fw-semibold">
                            Current System Academic Period
                        </small>

                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">

                            @if($settings->currentAcademicYear)
                                <span class="badge bg-dark px-3 py-2">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $settings->currentAcademicYear->name }}
                                </span>
                            @endif

                            @if($settings->currentTerm)
                                <span class="badge bg-info text-dark px-3 py-2">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $settings->currentTerm->name }}
                                </span>
                            @endif

                        </div>
                    </div>

                    <a href="{{ route('settings.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-cog me-1"></i>
                        System Settings
                    </a>

                </div>

            </div>
        </div>
    @endif


    {{-- Assignment History --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h5 class="fw-bold mb-1">
                        Academic Assignment History
                    </h5>

                    <small class="text-muted">
                        Every class assignment remains preserved for historical tracking.
                    </small>
                </div>

                <span class="badge bg-light text-dark border px-3 py-2">
                    {{ $assignments->count() }}
                    {{ Str::plural('Assignment', $assignments->count()) }}
                </span>

            </div>
        </div>


        <div class="card-body p-0">

            @if($assignments->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th class="ps-4">#</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Current</th>
                                <th>Assigned</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($assignments as $index => $assignment)

                                @php
                                    $status = strtolower($assignment->status ?? 'active');

                                    $statusClass = match($status) {
                                        'active' => 'bg-success',
                                        'completed' => 'bg-primary',
                                        'inactive' => 'bg-secondary',
                                        'pending' => 'bg-warning text-dark',
                                        'graduated' => 'bg-dark',
                                        'repeated' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };

                                    $isCurrentPeriod =
                                        isset($settings) &&
                                        $settings->current_academic_year_id == $assignment->academic_year_id &&
                                        $settings->current_term_id == $assignment->term_id;
                                @endphp

                                <tr>

                                    {{-- Number --}}
                                    <td class="ps-4 text-muted">
                                        {{ $index + 1 }}
                                    </td>


                                    {{-- Academic Year --}}
                                    <td>

                                        <div class="fw-semibold">
                                            {{ $assignment->academicYear->name ?? 'N/A' }}
                                        </div>

                                        @if($isCurrentPeriod)
                                            <small class="text-success fw-semibold">
                                                <i class="fas fa-circle me-1"
                                                   style="font-size:7px;"></i>
                                                Current Period
                                            </small>
                                        @endif

                                    </td>


                                    {{-- Term --}}
                                    <td>

                                        @if($assignment->term)

                                            <span class="badge bg-light text-dark border">
                                                {{ $assignment->term->name }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                Not Set
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Class --}}
                                    <td>

                                        <div class="fw-semibold">
                                            {{ $assignment->studentClass->name ?? 'N/A' }}
                                        </div>

                                        @if(!empty($assignment->studentClass->stream))
                                            <small class="text-muted">
                                                {{ $assignment->studentClass->stream }}
                                            </small>
                                        @endif

                                    </td>


                                    {{-- Status --}}
                                    <td>

                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($status) }}
                                        </span>

                                    </td>


                                    {{-- Current --}}
                                    <td>

                                        @if($assignment->is_current)

                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                Yes
                                            </span>

                                        @else

                                            <span class="badge bg-light text-muted border">
                                                No
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Assigned Date --}}
                                    <td>

                                        @if($assignment->assigned_date)

                                            <div>
                                                {{ \Carbon\Carbon::parse($assignment->assigned_date)->format('d M Y') }}
                                            </div>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Action --}}
                                    <td class="text-end pe-4">

                                        <a href="{{ route('student-class-assignments.show', $assignment->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Assignment">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                {{-- Empty State --}}
                <div class="text-center py-5">

                    <div class="mb-3">
                        <i class="fas fa-history text-muted"
                           style="font-size:48px;"></i>
                    </div>

                    <h5 class="fw-semibold">
                        No Assignment History
                    </h5>

                    <p class="text-muted mb-4">
                        This student does not have any class assignment records yet.
                    </p>

                    <a href="{{ route('student-class-assignments.create') }}"
                       class="btn btn-primary">

                        <i class="fas fa-plus me-1"></i>
                        Create Assignment

                    </a>

                </div>

            @endif

        </div>
    </div>

</div>
@endsection