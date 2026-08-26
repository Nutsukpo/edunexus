@extends('layouts.master')

@section('title', 'Generate Report Card')

@section('content')

<div class="container-fluid">
    
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-white text-dark text-white">
            <h4 class="mb-0 bg-white text-dark">
                <i class="fas fa-file-alt me-2 text-primary"></i> Generate Student Report Card
            </h4>
        </div>
        <div class="card-body">
            
            <form method="POST" action="{{ route('report-cards.show') }}" id="reportCardForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                            <option value="">-- Select Academic Year --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Term <span class="text-danger">*</span></label>
                        <select name="term_id" id="term_id" class="form-select" required>
                            <option value="">-- Select Term --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                        <select name="student_class_id" id="student_class_id" class="form-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-select" required disabled>
                            <option value="">-- Select Student --</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-download me-2"></i> Generate Report Card
                        </button>
                        <button type="reset" class="btn btn-secondary btn-lg px-5">
                            <i class="fas fa-undo me-2"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    
</div>

@endsection

@push('scripts')
<script>
    // Load students based on selected class
    document.getElementById('student_class_id').addEventListener('change', function() {
        const classId = this.value;
        const studentSelect = document.getElementById('student_id');
        
        if (classId) {
            // Enable the select
            studentSelect.disabled = false;
            studentSelect.innerHTML = '<option value="">Loading students...</option>';
            
            // Fetch students for this class
            fetch('/get-students-by-class?class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let options = '<option value="">-- Select Student --</option>';
                        data.forEach(student => {
                            options += `<option value="${student.id}">${student.first_name} ${student.last_name} (${student.student_id || 'N/A'})</option>`;
                        });
                        studentSelect.innerHTML = options;
                    } else {
                        studentSelect.innerHTML = '<option value="">No students found in this class</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    studentSelect.innerHTML = '<option value="">Error loading students</option>';
                });
        } else {
            studentSelect.disabled = true;
            studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
        }
    });
</script>
@endpush