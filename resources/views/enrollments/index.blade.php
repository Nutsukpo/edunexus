@extends('layouts.master')

@section('title', 'Student Enrollments')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Student Enrollments</h4>

        <a href="{{ route('enrollments.create') }}" class="btn btn-light text-dark">
            <i class="fas fa-plus bg-light text-dark"></i> New Enrollment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-light text-dark">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Academic Year</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($enrollments as $enrollment)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $enrollment->student->student_id ?? '-' }}
                            </td>

                            <td>
                                {{ $enrollment->student->full_name ?? '-' }}
                            </td>

                            <td>
                                {{ $enrollment->studentClass->name ?? '-' }}
                            </td>

                            <td>
                                {{ $enrollment->academicYear->name ?? '-' }}
                            </td>

                            <td>
                                {{ $enrollment->enrollment_date ?? '-' }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $enrollment->is_active ? 'success' : 'danger' }}">
                                    {{ $enrollment->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>

                                <a href="{{ route('enrollments.show', $enrollment->id) }}"
                                   class="btn btn-light btn-sm">
                                    View
                                </a>

                                <a href="{{ route('enrollments.edit', $enrollment->id) }}"
                                   class="btn btn-light btn-sm">
                                    Edit
                                </a>

                                <form action="{{ route('enrollments.destroy', $enrollment->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-light btn-sm"
                                            onclick="return confirm('Delete enrollment?')">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center">
                                No enrollments found
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection