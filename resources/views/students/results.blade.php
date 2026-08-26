@extends('students.layouts.app')

@section('title', 'My Results')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    My Academic Results
                </h5>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-info bg-opacity-10 border-0">
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-info">{{ number_format($summary['average_score'] ?? 0, 2) }}</h3>
                                <small class="text-muted">Average Score</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success bg-opacity-10 border-0">
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-success">{{ $summary['highest_score'] ?? 0 }}</h3>
                                <small class="text-muted">Highest Score</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger bg-opacity-10 border-0">
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-danger">{{ $summary['lowest_score'] ?? 0 }}</h3>
                                <small class="text-muted">Lowest Score</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary bg-opacity-10 border-0">
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-primary">{{ $summary['total_subjects'] ?? 0 }}</h3>
                                <small class="text-muted">Total Subjects</small>
                            </div>
                        </div>
                    </div>
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
        </div>
    </div>
</div>
@endsection