@extends('layouts.master')

@section('title', 'Student Progressions')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-arrow-up me-2 text-primary"></i>
                Student Promotion & Graduation
            </h5>
            <p class="text-muted mb-0">Manage student promotions, repetitions, and graduations</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-primary alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Selection Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>
                Select Options
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student-progressions.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Select Class <span class="text-primary">*</span></label>
                        <select name="class_id" class="form-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Academic Year <span class="text-primary">*</span></label>
                        <select name="academic_year_id" class="form-select" required>
                            <option value="">-- Select Academic Year --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Load Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    @if(request('class_id') && request('academic_year_id'))
        @if(isset($students) && $students->count() > 0)
            <form method="POST" action="{{ route('student-progressions.process') }}" id="progressionForm">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-users me-2 text-primary"></i>
                                Students in {{ $selectedClass->name ?? 'Selected Class' }}
                            </h5>
                            <div class="d-flex gap-2">
                                <button type="button" onclick="selectAll()" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-check-double me-1"></i> Select All
                                </button>
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-save me-1"></i> Save Progressions
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                                        </th>
                                        <th width="50">#</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Current Class</th>
                                        <th width="150">Action</th>
                                        <th width="200">Next Class</th>
                                        <th width="200">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                    @php
                                        // Get the current class name safely
                                        $currentClassName = 'N/A';
                                        if($student->studentClass) {
                                            $currentClassName = $student->studentClass->name;
                                        } elseif($student->classAssignments->where('is_current', true)->first()) {
                                            $currentClassName = $student->classAssignments->where('is_current', true)->first()->studentClass->name ?? 'N/A';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" 
                                                   name="students[{{ $student->id }}][selected]" 
                                                   class="student-checkbox" 
                                                   value="1">
                                        </td>
                                        <td class="text-muted">{{ $index + 1 }}</td>
                                        <td><code>{{ $student->student_id }}</code></td>
                                        <td>
                                            <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-white text-dark">{{ $currentClassName }}</span>
                                        </td>
                                        <td>
                                            <select name="students[{{ $student->id }}][action]" 
                                                    class="form-select form-select-sm action-select" 
                                                    data-student-id="{{ $student->id }}">
                                                <option value="promoted">🎓 Promote</option>
                                                <option value="repeated">🔄 Repeat</option>
                                                <option value="graduated">🎉 Graduate</option>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                Auto From Progression Rules
                                            </span>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="students[{{ $student->id }}][remarks]" 
                                                   class="form-control form-control-sm"
                                                   placeholder="Optional remarks">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" onclick="selectAll()" class="btn btn-outline-primary">
                                <i class="fas fa-check-double me-1"></i> Select All
                            </button>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save me-1"></i> Process Selected Students
                            </button>
                        </div>
                    </div> -->
                </div>
            </form>
        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No students found in this class. Please add students to this class first.
            </div>
        @endif
    @endif
</div>

<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}



// Form validation
document.getElementById('progressionForm')?.addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Please select at least one student to process.');
        return false;
    }
    
    let hasError = false;
    checkedBoxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const actionSelect = row.querySelector('.action-select');
        if (!actionSelect.value) {
            alert('Please select an action.');
            hasError = true;
        }
    });
    
    if (hasError) {
        e.preventDefault();
    }
});
</script>

<style>
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
    }
    
    .table th {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    
    .card {
        border-radius: 12px;
    }
    
    select:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
</style>

@endsection