@extends('layouts.master')

@section('title', 'Assign Student To Class')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4 mt-3">
        <h3 class="fw-bold mb-1">
            <i class="fas fa-user-plus me-2 text-primary"></i>
            Assign Student To Class
        </h3>
        <p class="text-muted mb-0">
            Create new student class assignment
        </p>
    </div>

    {{-- ERRORS --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('student-class-assignments.store') }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">

                    {{-- STUDENT --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-1 text-primary"></i>
                            Student <span class="text-danger">*</span>
                        </label>

                        <select name="student_id"
                                id="student-select"
                                class="form-select @error('student_id') is-invalid @enderror"
                                required>
                            <option value="">
                                -- Select Student --
                            </option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" 
                                        {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    [{{ $student->student_id ?? 'No ID' }}] - {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </select>

                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CLASS --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-chalkboard me-1 text-success"></i>
                            Class <span class="text-danger">*</span>
                        </label>

                        <select name="student_class_id"
                                class="form-select @error('student_class_id') is-invalid @enderror"
                                required>
                            <option value="">
                                Select Class
                            </option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" 
                                        {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('student_class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-1 text-info"></i>
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                class="form-select @error('academic_year_id') is-invalid @enderror">
                            <option value="">
                                Select Academic Year
                            </option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" 
                                        {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-circle me-1" style="color: #28a745;"></i>
                            Status
                        </label>

                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="repeated" {{ old('status') == 'repeated' ? 'selected' : '' }}>
                                Repeated
                            </option>
                        </select>

                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ASSIGNED DATE --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-day me-1 text-secondary"></i>
                            Assigned Date
                        </label>

                        <input type="date"
                               name="assigned_date"
                               class="form-control @error('assigned_date') is-invalid @enderror"
                               value="{{ old('assigned_date', date('Y-m-d')) }}">

                        @error('assigned_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white text-end border-0">
                <a href="{{ route('student-class-assignments.index') }}" 
                   class="btn btn-secondary me-2">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Save Assignment
                </button>
            </div>

        </div>
    </form>

</div>
@endsection

{{-- Add scripts at the bottom --}}
@section('scripts')
<!-- jQuery (if not already loaded) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>

<script>
    $(document).ready(function() {
        console.log('Document ready - Initializing Select2');
        
        // Check if select element exists
        if ($('#student-select').length === 0) {
            console.error('Student select element not found!');
            return;
        }
        
        console.log('Student select found, initializing Select2...');
        
        try {
            $('#student-select').select2({
                placeholder: 'Search by name or ID...',
                allowClear: true,
                width: '100%'
            });
            console.log('Select2 initialized successfully!');
        } catch(e) {
            console.error('Error initializing Select2:', e);
            alert('Error loading Select2. Please check console for details.');
        }
    });
</script>
@endsection