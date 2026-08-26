@extends('layouts.master')

@section('title', 'Take Attendance - ' . $studentClass->name)

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h5 class="fw-bold mb-1 mt-3">
                {{ $studentClass->name }}
            </h5>

            <p class="text-muted mb-0">
                Manage daily student attendance records
            </p>
        </div>

        <a href="{{ route('student-classes.show', $studentClass->id) }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Back to Class

        </a>

    </div>

    {{-- EMPTY STATE --}}
    @if($assignments->count() == 0)

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fas fa-user-slash fs-1 text-warning mb-3 d-block"></i>

                <h4 class="fw-bold">
                    No Students Assigned
                </h4>

                <p class="text-muted">
                    Please enroll students before taking attendance.
                </p>

                <a href="{{ route('student-classes.show', $studentClass->id) }}"
                   class="btn btn-primary">

                    <i class="fas fa-user-plus me-1"></i>
                    Enroll Students

                </a>

            </div>

        </div>

    @else

        <form action="{{ route('attendance-sessions.store') }}"
              method="POST"
              id="attendanceForm">

            @csrf

            <input type="hidden"
                   name="student_class_id"
                   value="{{ $studentClass->id }}">

            {{-- TOP CARDS --}}
            <div class="row mb-4">

                {{-- DATE --}}
                <div class="col-md-4 mb-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <label class="form-label fw-semibold">

                                <i class="fas fa-calendar-alt me-1 text-dark"></i>
                                Attendance Date

                            </label>

                            <input type="date"
                                   name="attendance_date"
                                   id="attendance_date"
                                   class="form-control @error('attendance_date') is-invalid @enderror"
                                   value="{{ old('attendance_date', now()->format('Y-m-d')) }}"
                                   required>

                            @error('attendance_date')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>

                </div>

                {{-- STATUS --}}
                <div class="col-md-4 mb-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <label class="form-label fw-semibold">

                                <i class="fas fa-info-circle me-1 text-success"></i>
                                Attendance Status

                            </label>

                            <div>

                                <span id="dateStatus"
                                      class="badge bg-success p-2">

                                    <i class="fas fa-check-circle me-1"></i>
                                    Taking attendance for today

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- TOTAL --}}
                <div class="col-md-4 mb-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <label class="form-label fw-semibold">

                                <i class="fas fa-users me-1 text-dark"></i>
                                Students

                            </label>

                            <div>

                                <span class="badge bg-dark p-2 fs-6">

                                    <i class="fas fa-user-graduate me-1 text-dark"></i>

                                    {{ $assignments->count() }} Students

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- WARNING --}}
            <div id="existingAttendanceAlert"
                 class="alert alert-warning d-none">

                <i class="fas fa-exclamation-triangle me-2"></i>

                Attendance already exists for this date.
                Submitting again will overwrite previous records.

            </div>

            {{-- ACTION BUTTONS --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body d-flex flex-wrap gap-2">

                    <button type="button"
                            class="btn btn-success"
                            onclick="markAll('present')">

                        <i class="fas fa-user-check me-1"></i>
                        Mark All Present

                    </button>

                    <button type="button"
                            class="btn btn-warning"
                            onclick="markAll('late')">

                        <i class="fas fa-clock me-1"></i>
                        Mark All Late

                    </button>

                    <button type="button"
                            class="btn btn-primary"
                            onclick="markAll('absent')">

                        <i class="fas fa-user-times me-1"></i>
                        Mark All Absent

                    </button>

                    <button type="button"
                            class="btn btn-secondary"
                            onclick="markAll('excused')">

                        <i class="fas fa-shield-alt me-1"></i>
                        Mark All Excused

                    </button>

                    <button type="button"
                            class="btn btn-dark"
                            onclick="clearAttendance()">

                        <i class="fas fa-eraser me-1"></i>
                        Clear Selection

                    </button>

                </div>

            </div>

            {{-- ATTENDANCE TABLE --}}
            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th width="50">#</th>
                                    <th>Student</th>
                                    <th>Student ID</th>
                                    <th width="500">Attendance Status</th>
                                    <th>Notes</th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($assignments as $assignment)

                                    @php
                                        $student = $assignment->student;
                                    @endphp

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        {{-- STUDENT --}}
                                        <td>

                                            <div class="d-flex align-items-center">

                                                <div>

                                                    <div class="fw-semibold">

                                                        {{ $student->first_name }}
                                                        {{ $student->last_name }}

                                                    </div>

                                                    <small class="text-muted">

                                                        {{ $student->gender ?? 'N/A' }}

                                                    </small>

                                                </div>

                                            </div>

                                        </td>

                                        {{-- STUDENT ID --}}
                                        <td>

                                            <span class="badge bg-light text-dark">

                                                {{ $student->student_id ?? 'N/A' }}

                                            </span>

                                        </td>

                                        {{-- STATUS --}}
                                        <td>

                                            <div class="btn-group btn-group-sm flex-wrap"
                                                 role="group">

                                                {{-- PRESENT --}}
                                                <input type="radio"
                                                       class="btn-check"
                                                       name="attendance[{{ $assignment->id }}]"
                                                       value="present"
                                                       id="present_{{ $assignment->id }}"
                                                       autocomplete="off">

                                                <label class="btn btn-outline-success"
                                                       for="present_{{ $assignment->id }}">

                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Present

                                                </label>

                                                {{-- LATE --}}
                                                <input type="radio"
                                                       class="btn-check"
                                                       name="attendance[{{ $assignment->id }}]"
                                                       value="late"
                                                       id="late_{{ $assignment->id }}"
                                                       autocomplete="off">

                                                <label class="btn btn-outline-warning"
                                                       for="late_{{ $assignment->id }}">

                                                    <i class="fas fa-clock me-1"></i>
                                                    Late

                                                </label>

                                                {{-- ABSENT --}}
                                                <input type="radio"
                                                       class="btn-check"
                                                       name="attendance[{{ $assignment->id }}]"
                                                       value="absent"
                                                       id="absent_{{ $assignment->id }}"
                                                       autocomplete="off">

                                                <label class="btn btn-outline-primary"
                                                       for="absent_{{ $assignment->id }}">

                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Absent

                                                </label>

                                                {{-- EXCUSED --}}
                                                <input type="radio"
                                                       class="btn-check"
                                                       name="attendance[{{ $assignment->id }}]"
                                                       value="excused"
                                                       id="excused_{{ $assignment->id }}"
                                                       autocomplete="off">

                                                <label class="btn btn-outline-primary"
                                                       for="excused_{{ $assignment->id }}">

                                                    <i class="fas fa-shield-alt me-1"></i>
                                                    Excused

                                                </label>

                                            </div>

                                        </td>

                                        {{-- NOTES --}}
                                        <td>

                                            <input type="text"
                                                   name="notes[{{ $assignment->id }}]"
                                                   class="form-control form-control-sm"
                                                   placeholder="Optional note...">

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            {{-- FOOTER --}}
            <div class="d-flex justify-content-end mt-4">

                <button type="submit"
                        class="btn btn-white text-dark px-5">

                    <i class="fas fa-save me-1"></i>
                    Save Attendance

                </button>

            </div>

        </form>

    @endif

</div>

{{-- STYLES --}}
<style>

    .avatar-circle{
        width:45px;
        height:45px;
        border-radius:50%;
        background:#0d6efd;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        font-size:16px;
    }

    .btn-check:checked + .btn-outline-success{
        background:#198754;
        color:#fff;
    }

    .btn-check:checked + .btn-outline-warning{
        background:#ffc107;
        color:#000;
    }

    .btn-check:checked + .btn-outline-primary{
        background:#dc3545;
        color:#fff;
    }

    .btn-check:checked + .btn-outline-primary{
        background:#0d6efd;
        color:#fff;
    }

</style>

{{-- SCRIPTS --}}
<script>

    /*
    |--------------------------------------------------------------------------
    | MARK ALL
    |--------------------------------------------------------------------------
    */

    function markAll(status)
    {
        document.querySelectorAll(`input[value="${status}"]`)
            .forEach(input => {
                input.checked = true;
            });
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAR ALL
    |--------------------------------------------------------------------------
    */

    function clearAttendance()
    {
        document.querySelectorAll('input[type="radio"]')
            .forEach(input => {
                input.checked = false;
            });
    }

    /*
    |--------------------------------------------------------------------------
    | DOM READY
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const dateInput = document.getElementById('attendance_date');

        const statusBadge = document.getElementById('dateStatus');

        const alertBox = document.getElementById('existingAttendanceAlert');

        const classId = {{ $studentClass->id }};

        /*
        |--------------------------------------------------------------------------
        | CHECK EXISTING ATTENDANCE
        |--------------------------------------------------------------------------
        */

        function checkAttendance()
        {
            const selectedDate = dateInput.value;

            if(!selectedDate){
                return;
            }

            const today = new Date()
                .toISOString()
                .split('T')[0];

            /*
            |--------------------------------------------------------------------------
            | DATE BADGE
            |--------------------------------------------------------------------------
            */

            if(selectedDate === today){

                statusBadge.className = 'badge bg-success p-2';

                statusBadge.innerHTML =
                    '<i class="fas fa-check-circle me-1"></i> Taking attendance for today';

            }else if(selectedDate < today){

                statusBadge.className = 'badge bg-warning text-dark p-2';

                statusBadge.innerHTML =
                    '<i class="fas fa-history me-1"></i> Taking attendance for past date';

            }else{

                statusBadge.className = 'badge bg-info p-2';

                statusBadge.innerHTML =
                    '<i class="fas fa-calendar-alt me-1"></i> Future attendance session';

            }

            /*
            |--------------------------------------------------------------------------
            | AJAX CHECK
            |--------------------------------------------------------------------------
            */

            fetch(`/attendance/check-exists?class_id=${classId}&date=${selectedDate}`)

                .then(response => response.json())

                .then(data => {

                    if(data.exists){

                        alertBox.classList.remove('d-none');

                        /*
                        |--------------------------------------------------------------------------
                        | LOAD EXISTING DATA
                        |--------------------------------------------------------------------------
                        */

                        if(data.attendance){

                            Object.entries(data.attendance)
                                .forEach(([assignmentId, status]) => {

                                    const radio = document.querySelector(
                                        `input[name="attendance[${assignmentId}]"][value="${status}"]`
                                    );

                                    if(radio){
                                        radio.checked = true;
                                    }

                                });

                        }

                    }else{

                        alertBox.classList.add('d-none');

                    }

                })

                .catch(error => {

                    console.error(error);

                });

        }

        dateInput.addEventListener('change', checkAttendance);

        checkAttendance();

        /*
        |--------------------------------------------------------------------------
        | SUBMIT CONFIRM
        |--------------------------------------------------------------------------
        */

        document.getElementById('attendanceForm')
            .addEventListener('submit', function(e){

                if(!alertBox.classList.contains('d-none')){

                    const confirmOverwrite = confirm(
                        'Attendance already exists for this date. Continue and overwrite records?'
                    );

                    if(!confirmOverwrite){

                        e.preventDefault();

                    }

                }

            });

    });

</script>

@endsection