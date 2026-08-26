@extends('layouts.master')

@section('title', 'Student Profile')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row">

                {{-- ================= PHOTO ================= --}}
                <div class="col-md-3 text-center">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}"
                            class="img-fluid rounded shadow-sm mb-3"
                            style="max-height:180px; object-fit:cover;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3"
                            style="height:180px;">
                            <span class="text-muted">No Photo</span>
                        </div>
                    @endif

                    {{-- STUDENT NAME --}}
                    <h5 class="fw-bold mb-1">{{ $student->full_name }}</h5>

                    {{-- STUDENT ID --}}
                    <p class="text-muted mb-2">
                        {{ $student->student_id }}
                    </p>

                    {{-- STATUS --}}
                    @php
                        // Check if student has graduated
                        $hasGraduated = $student->classAssignments
                            ->where('status', 'graduated')
                            ->where('is_current', false)
                            ->isNotEmpty();
                        
                        $currentAssignment = $student->classAssignments
                            ->where('is_current', true)
                            ->first();
                    @endphp

                    @if($hasGraduated)
                        <span class="badge bg-success" style="font-size: 14px; padding: 8px 16px;">
                            <i class="fas fa-graduation-cap me-1"></i> Graduated
                        </span>
                    @else
                        <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}" style="font-size: 14px; padding: 8px 16px;">
                            {{ $student->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    @endif

                    {{-- CURRENT CLASS OR GRADUATED STATUS --}}
                    <div class="mt-3 bg-white">
                        @if($hasGraduated)
                            <div class="card border-0 bg-success bg-opacity-10 shadow-sm">
                                <div class="card-body py-3">
                                    <small class="text-success d-block">
                                        <i class="fas fa-check-circle me-1"></i> Status
                                    </small>
                                    <strong class="text-success">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        Graduated
                                    </strong>
                                    <div class="mt-2">
                                        <small class="text-muted d-block">
                                            Graduated from:
                                            <strong>
                                                {{ $student->classAssignments->where('status', 'graduated')->last()->studentClass->name ?? 'N/A' }}
                                            </strong>
                                        </small>
                                        <small class="text-muted d-block">
                                            Graduated on:
                                            <strong>
                                                {{ $student->classAssignments->where('status', 'graduated')->last()->updated_at ? \Carbon\Carbon::parse($student->classAssignments->where('status', 'graduated')->last()->updated_at)->format('d M Y') : 'N/A' }}
                                            </strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @elseif($currentAssignment && $currentAssignment->studentClass)
                            <div class="card border-0 bg-light shadow-sm">
                                <div class="card-body py-2">
                                    <small class="text-muted d-block">
                                        Current Class
                                    </small>
                                    <strong>
                                        {{ $currentAssignment->studentClass->name }}
                                    </strong>
                                </div>
                            </div>
                        @else
                            <div class="card border-0 bg-warning bg-opacity-10 shadow-sm">
                                <div class="card-body py-2">
                                    <small class="text-warning d-block">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Status
                                    </small>
                                    <strong class="text-warning">
                                        No class assigned
                                    </strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ================= DETAILS ================= --}}
                <div class="col-md-9">

                    {{-- PERSONAL INFO --}}
                    <h5 class="text-dark">Personal Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>First Name:</strong> {{ $student->first_name }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Middle Name:</strong> {{ $student->middle_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Last Name:</strong> {{ $student->last_name }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Gender:</strong> {{ $student->gender }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Date of Birth:</strong> 
                                {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '-' }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Nationality:</strong> {{ $student->nationality ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Religion:</strong> {{ $student->religion ?? '-' }}</p>
                        </div>

                        <div class="col-md-12">
                            <p><strong>Address:</strong> {{ $student->address ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- DISABILITY --}}
                    <h5 class="text-dark mt-3">Disability Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Has Disability:</strong>
                                {{ $student->has_disability ? 'Yes' : 'No' }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Disability Type:</strong>
                                {{ $student->disability_type ?? 'None' }}
                            </p>
                        </div>

                    </div>

                    {{-- FATHER --}}
                    <h5 class="text-dark mt-3">Father Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $student->father_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $student->father_phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $student->father_email ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Occupation:</strong> {{ $student->father_occupation ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- MOTHER --}}
                    <h5 class="text-dark mt-3">Mother Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $student->mother_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $student->mother_phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $student->mother_email ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Occupation:</strong> {{ $student->mother_occupation ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- GUARDIAN --}}
                    <h5 class="text-dark mt-3">Guardian Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $student->guardian_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $student->guardian_phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $student->guardian_email ?? '-' }}</p>
                        </div>

                    </div>

                    {{-- SCHOOL INFO --}}
                    <h5 class="text-dark mt-3">School Information</h5>
                    <hr>

                    <div class="row">

                        <div class="col-md-6">
                            <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Admission Date:</strong> 
                                {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : now()->format('d M Y') }}
                            </p>
                        </div>

                        @if($hasGraduated)
                            @php
                                $graduatedAssignment = $student->classAssignments->where('status', 'graduated')->last();
                            @endphp
                            <div class="col-md-6">
                                <p><strong>Graduated From:</strong> 
                                    {{ $graduatedAssignment->studentClass->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Graduation Date:</strong> 
                                    {{ $graduatedAssignment->updated_at ? \Carbon\Carbon::parse($graduatedAssignment->updated_at)->format('d M Y') : 'N/A' }}
                                </p>
                            </div>
                        @endif

                    </div>

                    {{-- ================= ACADEMIC RESULTS (REPORT CARD STYLE) ================= --}}
                    <h5 class="text-dark mt-4">Academic Results</h5>
                    <hr>

                    @php
                        // Fetch results from student_results table
                        $results = $student->studentResults ?? collect();
                        
                        // Get current class from student's assignment
                        $currentAssignment = $student->classAssignments->where('is_current', true)->first();
                        $currentClassName = $currentAssignment->studentClass->name ?? 'No Class Assigned';
                        
                        if($results->count() > 0) {
                            // Group by academic year and term, include class name
                            $groupedResults = $results->groupBy(function($item) use ($currentClassName) {
                                $academicYear = $item->academicYear->name ?? 'Unknown Year';
                                $term = $item->term->name ?? 'Unknown Term';
                                
                                // Use the student's current class
                                $className = $currentClassName;
                                
                                return $academicYear . '|' . $term . '|' . $className;
                            });
                        } else {
                            $groupedResults = collect();
                        }
                    @endphp

                    @if($groupedResults->count() > 0)
                        @foreach($groupedResults as $key => $termResults)
                            @php
                                // Split the key into parts
                                $parts = explode('|', $key);
                                $year = $parts[0] ?? 'Unknown Year';
                                $term = $parts[1] ?? 'Unknown Term';
                                $class = $parts[2] ?? 'Unknown Class';
                                
                                // Calculate totals for this term
                                $totalClassScore = 0;
                                $totalExamScore = 0;
                                $totalOverall = 0;
                                $subjectCount = $termResults->count();
                                
                                foreach($termResults as $result) {
                                    $totalClassScore += $result->class_score ?? 0;
                                    $totalExamScore += $result->exam_score ?? 0;
                                    $totalOverall += $result->overall_score ?? 0;
                                }
                                
                                $averageOverall = $subjectCount > 0 ? $totalOverall / $subjectCount : 0;
                            @endphp
                            
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header" style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-white">
                                                <i class="fas fa-calendar-alt me-2"></i>
                                                {{ $year }} - {{ $term }} - {{ $class }}
                                            </h6>
                                        </div>
                                        <div class="mt-1 mt-md-0">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" style="font-size: 14px;">
                                            <thead style="background: #f5f5f5;">
                                                <tr>
                                                    <th style="width: 5%;">#</th>
                                                    <th style="width: 30%;">Subject</th>
                                                    <th style="width: 15%;">Class</th>
                                                    <th style="width: 15%;">Exam</th>
                                                    <th style="width: 15%;">Total</th>
                                                    <th style="width: 10%;">Grade</th>
                                                    <th style="width: 10%;">Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $counter = 1;
                                                    $totalAllClass = 0;
                                                    $totalAllExam = 0;
                                                    $totalAllOverall = 0;
                                                @endphp
                                                
                                                @foreach($termResults->sortByDesc('overall_score') as $result)
                                                    @php
                                                        $classScore = $result->class_score ?? 0;
                                                        $examScore = $result->exam_score ?? 0;
                                                        $overall = $result->total_score ?? 0;
                                                        
                                                        $totalAllClass += $classScore;
                                                        $totalAllExam += $examScore;
                                                        $totalAllOverall += $overall;
                                                        
                                                        // Get subject name
                                                        $subjectName = 'N/A';
                                                        if(isset($result->subject) && $result->subject) {
                                                            $subjectName = $result->subject->name ?? 'N/A';
                                                        } elseif(isset($result->subject_name)) {
                                                            $subjectName = $result->subject_name;
                                                        } elseif(isset($result->subject_id)) {
                                                            $subjectName = 'Subject ID: ' . $result->subject_id;
                                                        }
                                                        
                                                        // Determine grade
                                                        $grade = $result->grade ?? 'F';
                                                        $gradeClass = 'danger';
                                                        $remarks = 'Poor';
                                                        
                                                        if($grade == '1' || $grade == '1') {
                                                            $gradeClass = 'success';
                                                            $remarks = 'Excellent';
                                                        } elseif(in_array($grade, ['2', '3', '4'])) {
                                                            $gradeClass = 'info';
                                                            $remarks = 'Good';
                                                        } elseif(in_array($grade, ['5', '6', 'C7'])) {
                                                            $gradeClass = 'warning';
                                                            $remarks = 'Average';
                                                        } elseif(in_array($grade, ['8', '9', '10'])) {
                                                            $gradeClass = 'danger';
                                                            $remarks = 'Below Average';
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $counter++ }}</td>
                                                        <td><strong>{{ $subjectName }}</strong></td>
                                                        <td class="text-center">{{ number_format($classScore, 2) }}</td>
                                                        <td class="text-center">{{ number_format($examScore, 2) }}</td>
                                                        <td class="text-center"><strong>{{ number_format($overall, 2) }}</strong></td>
                                                        <td class="text-center">
                                                            <span class="text-dark" style="font-size: 13px; padding: 5px 12px;">
                                                                {{ $grade }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">{{ $remarks }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot style="background: #e3f2fd; font-weight: bold;">
                                                <tr>
                                                    <td colspan="2" class="text-end">Total</td>
                                                    <td class="text-center">{{ number_format($totalAllClass, 2) }}</td>
                                                    <td class="text-center">{{ number_format($totalAllExam, 2) }}</td>
                                                    <td class="text-center">{{ number_format($totalAllOverall, 2) }}</td>
                                                    <td colspan="2" class="text-center">
                                                        <span class="text-dark">Average: {{ number_format($totalAllOverall / ($subjectCount > 0 ? $subjectCount : 1), 2) }}%</span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No academic results available for this student.
                        </div>
                    @endif

                    {{-- ================= ACADEMIC HISTORY ================= --}}
                    @if($student->classAssignments->count() > 0)
                        <h5 class="text-dark mt-4">Academic History</h5>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Academic Year</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->classAssignments->sortByDesc('created_at') as $index => $assignment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $assignment->studentClass->name ?? 'N/A' }}</td>
                                            <td>{{ $assignment->academicYear->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($assignment->status == 'graduated')
                                                    <span class="badge bg-success">Graduated</span>
                                                @elseif($assignment->is_current)
                                                    <span class="badge bg-primary">Current</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($assignment->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->created_at ? $assignment->created_at->format('d M Y') : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .nav-item-custom {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        margin-top: 10px;
        transition: all 0.3s ease;
        width: 100%;
    }
    .nav-item-custom:hover {
        background: #e9ecef;
        color: #0d6efd;
    }
    .nav-item-custom button {
        margin-left: auto;
        background: #0d6efd;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
    }
    .nav-item-custom button:hover {
        background: #0b5ed7;
    }
    
    /* Badge Styles */
    .badge.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }
    
    /* Card Styles */
    .bg-success.bg-opacity-10 {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }
    .bg-warning.bg-opacity-10 {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    .bg-primary.bg-opacity-10 {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    
    /* Table Styles */
    .table {
        font-size: 14px;
    }
    .table-sm td, .table-sm th {
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    .table tfoot td {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    
    /* Grade Colors */
    .text-success {
        color: #28a745 !important;
    }
    .text-warning {
        color: #ffc107 !important;
    }
    .text-danger {
        color: #dc3545 !important;
    }
    .text-info {
        color: #0dcaf0 !important;
    }
    
    /* Card Header */
    .card-header {
        padding: 12px 20px;
    }
    
    /* Summary Cards */
    .bg-white.rounded.shadow-sm {
        transition: all 0.3s ease;
    }
    .bg-white.rounded.shadow-sm:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Remarks Cards */
    .card-header.bg-success {
        background: linear-gradient(135deg, #43a047 0%, #66bb6a 100%) !important;
    }
    .card-header.bg-primary {
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%) !important;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .col-md-3.text-center {
            margin-bottom: 20px;
        }
        .table-responsive {
            font-size: 12px;
        }
        .table-sm td, .table-sm th {
            padding: 0.3rem;
        }
        .card-header h6 {
            font-size: 14px;
        }
        .card-header .badge {
            font-size: 11px;
        }
        .summary-cards .col-md-3 {
            margin-bottom: 10px;
        }
    }
    
    /* Print Styles */
    @media print {
        .nav-item-custom {
            display: none !important;
        }
        .card.shadow-sm {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        .badge {
            border: 1px solid #333 !important;
        }
        .bg-success.bg-opacity-10 {
            background-color: #f0f0f0 !important;
        }
        .btn {
            display: none !important;
        }
        .card-header {
            background: #333 !important;
            color: white !important;
        }
        .table thead th {
            background: #f0f0f0 !important;
        }
    }
</style>
@endpush