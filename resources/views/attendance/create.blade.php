@extends('layouts.master')

@section('title', 'Take Attendance')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-user-check me-2"></i>
                Take Attendance
            </h3>

            <p class="text-muted mb-0">
                Record daily student attendance
            </p>
        </div>

    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('attendance-sessions.store') }}"
          method="POST">

        @csrf

        {{-- CLASS + DATE --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="row">

                    {{-- CLASS --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Class
                        </label>

                        <select name="student_class_id"
                                id="student_class_id"
                                class="form-select"
                                required>

                            <option value="">
                                Select Class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}">
                                    {{ $class->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- DATE --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Attendance Date
                        </label>

                        <input type="date"
                               name="attendance_date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}"
                               required>

                    </div>

                </div>

            </div>

        </div>

        {{-- STUDENTS TABLE --}}
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Students Attendance
                </h5>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th width="5%">#</th>
                                <th>Student</th>
                                <th>Admission No.</th>
                                <th width="25%">Status</th>

                            </tr>

                        </thead>

                        <tbody id="students-table-body">

                            <tr>

                                <td colspan="4"
                                    class="text-center text-muted py-5">

                                    Select a class to load students

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="card-footer bg-white text-end">

                <button type="submit"
                        class="btn btn-white text-dark">

                    <i class="fas fa-save me-1"></i>

                    Submit Attendance

                </button>

            </div>

        </div>

    </form>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const classSelect = document.getElementById('student_class_id');

    const tableBody = document.getElementById('students-table-body');

    classSelect.addEventListener('change', function () {

        let classId = this.value;

        /*
        |--------------------------------------------------------------------------
        | EMPTY STATE
        |--------------------------------------------------------------------------
        */
        if (!classId) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4"
                        class="text-center text-muted py-5">

                        Select a class to load students

                    </td>
                </tr>
            `;

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | LOADING STATE
        |--------------------------------------------------------------------------
        */
        tableBody.innerHTML = `
            <tr>
                <td colspan="4"
                    class="text-center py-5">

                    <div class="spinner-border text-primary"
                         role="status">
                    </div>

                    <div class="mt-2">
                        Loading students...
                    </div>

                </td>
            </tr>
        `;

        /*
        |--------------------------------------------------------------------------
        | FETCH STUDENTS
        |--------------------------------------------------------------------------
        */
        fetch(`/attendance/class/${classId}/students`)

            .then(response => response.json())

            .then(data => {

                tableBody.innerHTML = '';

                /*
                |--------------------------------------------------------------------------
                | NO STUDENTS
                |--------------------------------------------------------------------------
                */
                if (data.length === 0) {

                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4"
                                class="text-center text-muted py-5">

                                No students found in this class

                            </td>
                        </tr>
                    `;

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | LOAD STUDENTS
                |--------------------------------------------------------------------------
                */
                data.forEach((assignment, index) => {

                    let student = assignment.student;

                    tableBody.innerHTML += `

                        <tr>

                            <td>
                                ${index + 1}
                            </td>

                            <td>

                                ${student?.first_name ?? ''}

                                ${student?.last_name ?? ''}

                            </td>

                            <td>

                                ${student?.student_id ?? 'N/A'}

                            </td>

                            <td>

                                <select
                                    name="attendance[${assignment.id}]"
                                    class="form-select">

                                    <option value="present">
                                        Present
                                    </option>

                                    <option value="absent">
                                        Absent
                                    </option>

                                    <option value="late">
                                        Late
                                    </option>

                                    <option value="excused">
                                        Excused
                                    </option>

                                </select>

                            </td>

                        </tr>

                    `;
                });

            })

            .catch(error => {

                console.error(error);

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4"
                            class="text-center text-danger py-5">

                            Failed to load students

                        </td>
                    </tr>
                `;
            });

    });

});

</script>

@endpush