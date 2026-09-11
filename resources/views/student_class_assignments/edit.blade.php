@extends('layouts.master')

@section('title', 'Edit Student Assignment')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-user-edit me-2 text-primary"></i>
                Edit Student Assignment
            </h4>

            <p class="text-muted mb-0">
                Update the student's class and academic period.
            </p>
        </div>

        <a href="{{ route('student-class-assignments.show', $assignment->id) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Assignment
        </a>
    </div>


    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Please correct the following:
            </div>

            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Student Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-user-graduate me-2 text-primary"></i>
                Student Information
            </h6>
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">
                    <small class="text-muted d-block">
                        Student ID
                    </small>

                    <div class="fw-semibold">
                        {{ $assignment->student->student_id }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">
                        Student Name
                    </small>

                    <div class="fw-semibold">
                        {{ $assignment->student->first_name }}
                        {{ $assignment->student->middle_name ?? '' }}
                        {{ $assignment->student->last_name }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">
                        Current Status
                    </small>

                    @if($assignment->status === 'active')
                        <span class="badge bg-success">
                            Active
                        </span>
                    @elseif($assignment->status === 'repeated')
                        <span class="badge bg-warning text-dark">
                            Repeated
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            {{ ucfirst($assignment->status) }}
                        </span>
                    @endif
                </div>

            </div>

        </div>

    </div>


    {{-- Edit Form --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>
                Assignment Details
            </h6>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('student-class-assignments.update', $assignment->id) }}">

                @csrf
                @method('PUT')

                <div class="row g-4">

                    {{-- Class --}}
                    <div class="col-md-6">

                        <label for="student_class_id"
                               class="form-label fw-semibold">

                            Class
                            <span class="text-danger">*</span>

                        </label>

                        <select name="student_class_id"
                                id="student_class_id"
                                class="form-select @error('student_class_id') is-invalid @enderror"
                                required>

                            <option value="">
                                Select class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}"
                                    {{ old('student_class_id', $assignment->student_class_id) == $class->id ? 'selected' : '' }}>

                                    {{ $class->name }}

                                    @if(!empty($class->stream))
                                        — {{ $class->stream }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @error('student_class_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Academic Year --}}
                    <div class="col-md-6">

                        <label for="academic_year_id"
                               class="form-label fw-semibold">

                            Academic Year
                            <span class="text-danger">*</span>

                        </label>

                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-select @error('academic_year_id') is-invalid @enderror"
                                required>

                            <option value="">
                                Select academic year
                            </option>

                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}"
                                    {{ old('academic_year_id', $assignment->academic_year_id) == $year->id ? 'selected' : '' }}>

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                        @error('academic_year_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Term --}}
                    <div class="col-md-6">

                        <label for="term_id"
                               class="form-label fw-semibold">

                            Term
                            <span class="text-danger">*</span>

                        </label>

                        <select name="term_id"
                                id="term_id"
                                class="form-select @error('term_id') is-invalid @enderror"
                                required>

                            <option value="">
                                Select term
                            </option>

                            @foreach($terms as $term)

                                <option value="{{ $term->id }}"
                                    {{ old('term_id', $assignment->term_id) == $term->id ? 'selected' : '' }}>

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>

                        @error('term_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <small class="text-muted">
                            Terms are restricted to the selected academic year.
                        </small>

                    </div>


                    {{-- Status --}}
                    <div class="col-md-6">

                        <label for="status"
                               class="form-label fw-semibold">

                            Assignment Status

                        </label>

                        <select name="status"
                                id="status"
                                class="form-select">

                            <option value="active"
                                {{ old('status', $assignment->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="repeated"
                                {{ old('status', $assignment->status) === 'repeated' ? 'selected' : '' }}>
                                Repeated
                            </option>

                        </select>

                    </div>


                    {{-- Assigned Date --}}
                    <div class="col-md-6">

                        <label for="assigned_date"
                               class="form-label fw-semibold">

                            Assigned Date

                        </label>

                        <input type="date"
                               name="assigned_date"
                               id="assigned_date"
                               class="form-control @error('assigned_date') is-invalid @enderror"
                               value="{{ old(
                                   'assigned_date',
                                   optional($assignment->assigned_date)->format('Y-m-d')
                               ) }}">

                        @error('assigned_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>


                {{-- Historical Warning --}}
                <div class="alert alert-warning mt-4 mb-0">

                    <div class="d-flex">

                        <i class="fas fa-history mt-1 me-2"></i>

                        <div>

                            <strong>Historical record</strong>

                            <div class="small mt-1">
                                Changing the academic year or term changes
                                this assignment's academic period. Existing
                                historical assignments are not deleted.
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Buttons --}}
                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a href="{{ route('student-class-assignments.show', $assignment->id) }}"
                       class="btn btn-light border">

                        <i class="fas fa-times me-1"></i>
                        Cancel

                    </a>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>
                        Update Assignment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    .card {
        border-radius: 12px;
    }

    .form-label {
        color: #344054;
    }

    .form-select,
    .form-control {
        min-height: 44px;
        border-radius: 8px;
    }

    .form-select:focus,
    .form-control:focus {
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .10);
    }

</style>

@endpush


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const academicYear = document.getElementById('academic_year_id');
    const term = document.getElementById('term_id');

    if (!academicYear || !term) {
        return;
    }

    academicYear.addEventListener('change', function () {

        const yearId = this.value;

        term.innerHTML =
            '<option value="">Loading terms...</option>';

        term.disabled = true;

        if (!yearId) {

            term.innerHTML =
                '<option value="">Select academic year first</option>';

            term.disabled = false;

            return;
        }

        const url =
            "{{ route('settings.academic-years.terms', ['academicYear' => '__YEAR__']) }}"
                .replace('__YEAR__', yearId);

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        .then(response => {

            if (!response.ok) {
                throw new Error('Unable to load terms.');
            }

            return response.json();

        })

        .then(terms => {

            term.innerHTML =
                '<option value="">Select term</option>';

            terms.forEach(function (item) {

                const option =
                    document.createElement('option');

                option.value = item.id;
                option.textContent = item.name;

                term.appendChild(option);

            });

            term.disabled = false;

        })

        .catch(error => {

            console.error(error);

            term.innerHTML =
                '<option value="">Unable to load terms</option>';

            term.disabled = false;

        });

    });

});

</script>

@endpush