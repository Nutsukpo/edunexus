@extends('layouts.master')

@section('title', 'Attendance Details')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-clipboard-list me-2"></i>
                Attendance Details
            </h3>

            <p class="text-muted mb-0">
                View full attendance record
            </p>
        </div>

        <a href="{{ route('student-classes.show', $attendanceSession->studentClass->id) }}"
           class="btn btn-light text-dark">

            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>

    </div>

    {{-- SESSION CARD --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <strong>Class:</strong><br>
                    {{ $attendanceSession->studentClass->name ?? 'N/A' }}
                </div>

                <div class="col-md-4">
                    <strong>Date:</strong><br>
                    {{ \Carbon\Carbon::parse($attendanceSession->attendance_date)->format('M d, Y') }}
                </div>

                <div class="col-md-4">
                    <strong>Taken By:</strong><br>
                    {{ $attendanceSession->takenBy->name ?? 'System' }}
                </div>

            </div>

        </div>

    </div>

    {{-- ATTENDANCE TABLE --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Student Attendance List
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Admission No.</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($attendanceSession->attendances as $attendance)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                {{-- STUDENT NAME --}}
                                <td>
                                    {{ $attendance->student->first_name ?? '' }}
                                    {{ $attendance->student->last_name ?? '' }}
                                </td>

                                {{-- ADMISSION NO --}}
                                <td>
                                    {{ $attendance->student->student_id ?? 'N/A' }}
                                </td>

                                {{-- STATUS BADGE --}}
                                <td>

                                    @if($attendance->status == 'present')
                                        <span class="badge bg-success">Present</span>

                                    @elseif($attendance->status == 'absent')
                                        <span class="badge bg-danger">Absent</span>

                                    @elseif($attendance->status == 'late')
                                        <span class="badge bg-warning text-dark">Late</span>

                                    @elseif($attendance->status == 'excused')
                                        <span class="badge bg-info text-dark">Excused</span>

                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    No attendance records found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection