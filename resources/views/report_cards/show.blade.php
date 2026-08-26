@extends('layouts.master')

@section('title', 'Student Report Card')

@section('content')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    @media print {
        body {
            background: white;
            margin: 0;
            padding: 0;
        }
        .no-print {
            display: none !important;
        }
        .report-card {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
    
    .report-card-wrapper {
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .report-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    
    .school-header {
        background: #1e3c72;
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .school-header h3 {
        font-size: 18px;
        margin-bottom: 5px;
    }
    
    .school-header p {
        font-size: 12px;
        margin-bottom: 0;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 15px;
        background: #f8f9fa;
        margin: 15px;
        border-radius: 8px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 10px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 13px;
        font-weight: 700;
        color: #2d3748;
        margin-top: 3px;
    }
    
    .performance-bar-container {
        width: 100%;
        background: #e2e8f0;
        border-radius: 4px;
        height: 6px;
        margin: 5px 0;
    }
    
    .performance-bar {
        height: 6px;
        border-radius: 4px;
    }
    
    .bg-excellent { background: #48bb78; }
    .bg-good { background: #4299e1; }
    .bg-average { background: #ed8936; }
    .bg-poor { background: #f56565; }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 0 15px 15px 15px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px;
        text-align: center;
        border-radius: 8px;
    }
    
    .stat-value {
        font-size: 20px;
        font-weight: bold;
    }
    
    .stat-label {
        font-size: 10px;
        text-transform: uppercase;
    }
    
    .score-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin: 0 15px 15px 15px;
        width: calc(100% - 30px);
    }
    
    .score-table th {
        background:#1e3c72 ;
        color: white;
        padding: 8px;
        text-align: center;
        border: 1px solid #4a5568;
    }
    
    .score-table td {
        padding: 6px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    
    .score-table .text-start {
        text-align: left;
    }
    
    .grade-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .remarks-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        padding: 0 15px 15px 15px;
    }
    
    .remarks-card {
        border-left: 3px solid;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .remarks-teacher { border-left-color: #4299e1; }
    .remarks-head { border-left-color: #48bb78; }
    
    .remarks-title {
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .remarks-text {
        font-size: 11px;
        font-style: italic;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    
    .remarks-footer {
        font-size: 10px;
        border-top: 1px solid #dee2e6;
        padding-top: 5px;
        margin-top: 5px;
    }
    
    .footer {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        font-size: 10px;
        border-top: 1px solid #e2e8f0;
    }
    
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .remarks-grid {
            grid-template-columns: 1fr;
        }
        .score-table {
            font-size: 10px;
        }
    }
</style>

<div class="report-card-wrapper no-print">
    <div class="report-card" id="reportCard">
        
        <!-- School Header -->
        <div class="school-header">
            @if(file_exists(public_path('img/Talha.jpeg')))
                <img src="{{ asset('img/Talha.jpeg') }}" alt="Logo" style="height: 50px; margin-bottom: 8px;">
            @endif
            <h3>TALHA PREMIER INTERNATIOANL ACADEMY</h3>
            <p>{{ $schoolMotto ?? 'Excellence in Education' }}</p>
            <div style="margin-top: 8px;">
                <span style="background: rgba(255,255,255,0.2); padding: 3px 12px; border-radius: 20px; font-size: 12px;">TERMINAL REPORT CARD</span>
            </div>
        </div>
        
        <!-- Student Information -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Student Name</span>
                <span class="info-value">{{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Admission No.</span>
                <span class="info-value">{{ $student->student_id ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Class</span>
                <span class="info-value">{{ $class->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Academic Year</span>
                <span class="info-value">{{ $academicYear->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Term</span>
                <span class="info-value">{{ $term->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Class Position</span>
                <span class="info-value">
                    @if($classPosition)
                        {{ $classPosition }}{{ $classPosition == 1 ? 'st' : ($classPosition == 2 ? 'nd' : ($classPosition == 3 ? 'rd' : 'th')) }} / {{ $totalStudents ?? 0 }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Overall Performance</span>
                <div class="info-value">
                    <div class="performance-bar-container">
                        @php
                            $overallPercent = $average;
                            $barClass = $overallPercent >= 80 ? 'bg-excellent' : ($overallPercent >= 60 ? 'bg-good' : ($overallPercent >= 50 ? 'bg-average' : 'bg-poor'));
                        @endphp
                        <div class="performance-bar {{ $barClass }}" style="width: {{ $overallPercent }}%"></div>
                    </div>
                    <small>{{ number_format($overallPercent, 1) }}%</small>
                </div>
            </div>
            <div class="info-item">
                <span class="info-label">Subjects Taken</span>
                <span class="info-value">{{ $results->count() }}</span>
            </div>
        </div>
        <!-- Results Table -->
        <table class="score-table">
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th>Subject</th>
                    <th width="60">Class</th>
                    <th width="60">Exam</th>
                    <th width="60">Total</th>
                    <th width="60">Grade</th>
                    <th width="60">Remarks</th>
                    <th width="50">Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $index => $result)
                    @php
                        if($result->total_score >= 80) { $gradeLetter = '1'; $gradeClass = 'grade-1'; }
                        elseif($result->total_score >= 75) { $gradeLetter = '2'; $gradeClass = 'grade-2'; }
                        elseif($result->total_score >= 70) { $gradeLetter = '3'; $gradeClass = 'grade-3'; }
                        elseif($result->total_score >= 60) { $gradeLetter = '4'; $gradeClass = 'grade-4'; }
                        elseif($result->total_score >= 55) { $gradeLetter = '4'; $gradeClass = 'grade-4'; }
                        elseif($result->total_score >= 50) { $gradeLetter = '4'; $gradeClass = 'grade-5'; }
                        else { $gradeLetter = '6'; $gradeClass = 'grade-6'; }
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td class="text-start">{{ $result->subject->name ?? 'N/A' }}</td>
                        <td style="text-align: center;">{{ $result->class_score ?? 0 }}</td>
                        <td style="text-align: center;">{{ $result->exam_score ?? 0 }}</td>
                        <td style="text-align: center;"><strong>{{ $result->total_score ?? 0 }}</strong></td>
                        <td style="text-align: center;">
                            <span class="grade-badge bg-white text-dark">{{ $gradeLetter }}</span>
                        </td>
                        <td class="text-start">
                            <small>
                                <!-- <i class="fas fa-comment-dots text-muted me-1"></i> -->
                                {{ $result->remark ?? ($result->total_score >= 80 ? 'Excellent performance!' : ($result->total_score >= 75 ? 'Very good!' : ($result->total_score >= 70 ? 'Good!' : ($result->total_score >= 50 ? 'Pass!' : 'Needs improvement!')))) }}
                            </small>
                        </td>
                        <td style="text-align: center;">
                            @if($result->subject_position && $result->subject_position <= 3)
                                @if($result->subject_position == 1) 1
                                @elseif($result->subject_position == 2) 2
                                @else 3 @endif
                            @else
                                {{ $result->subject_position ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px;">
                            <strong>No Results Found</strong><br>
                            <small>No results recorded for this student in the selected term.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Remarks Section -->
        <div class="remarks-grid">
    <div class="remarks-card remarks-teacher">
        <div class="remarks-title">
            <i class="fas fa-chalkboard-teacher"></i> Teacher's Remarks
        </div>
        <div class="remarks-text">
            "{{ $teacherRemarks ?? 'Good performance. Keep it up!' }}"
        </div>
        <div class="remarks-footer">
            <div style="display: flex; justify-content: space-between;">
                <span>Teacher: {{ $classTeacher->first_name ?? '______' }} {{ $classTeacher->last_name ?? '' }}</span>
                <span>Date: {{ date('d/m/Y') }}</span>
            </div>
        </div>
    </div>
    <div class="remarks-card remarks-head">
        <div class="remarks-title">
            <i class="fas fa-gavel"></i> Headmaster's Remarks
        </div>
        <div class="remarks-text">
            "{{ $headRemarks }}"
        </div>
        <div class="remarks-footer">
            <div style="display: flex; justify-content: space-between;">
                <span>Headmaster: DR. J. K. MENSAH</span>
                <span>
                    @php
                        $termName = strtolower($term->name ?? '');
                        $isThirdTerm = (str_contains($termName, 'third') || str_contains($termName, '3rd'));
                    @endphp
                    
                    @if($isThirdTerm)
                        @if($average >= 60)
                            <span style="color: #48bb78;">✓ Promoted</span>
                        @elseif($average >= 50)
                            <span style="color: #ed8936;">⚠ Conditional</span>
                        @else
                            <span style="color: #f56565;">✗ Repeated</span>
                        @endif
                    @else
                        <span style="color: #4299e1;">📋 In Progress</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
        
        <!-- Footer -->
        <div class="footer">
            Generated on {{ now()->format('d/m/Y h:i A') }}
        </div>
        
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-3 no-print" style="max-width: 1100px; margin: 20px auto;">
    <div class="col-12 text-center">
        <button onclick="exportToPDF()" class="btn btn-danger btn-sm mx-1">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </button>
        <a href="{{ route('report-cards.index') }}" class="btn btn-secondary btn-sm mx-1">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function exportToPDF() {
        const element = document.getElementById('reportCard');
        
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        const opt = {
            margin: [0.3, 0.3, 0.3, 0.3],
            filename: 'Report_Card_{{ $student->student_id ?? 'student' }}_{{ date('Y-m-d') }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, letterRendering: true, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            Swal.close();
            Swal.fire({ icon: 'success', title: 'PDF Generated!', timer: 1500, showConfirmButton: false });
        }).catch(() => {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Error generating PDF' });
        });
    }
</script>
@endpush