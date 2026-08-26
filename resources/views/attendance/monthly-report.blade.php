@extends('layouts.master')

@section('title', 'Monthly Student Attendance Report')

@section('content')
<div class="container-fluid">
    
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                Monthly Attendance Report
                @if(!empty($summary) && isset($summary['class_name']))
                    - {{ $summary['class_name'] }}
                @endif
                @if(!empty($summary) && isset($summary['month_name']))
                    ({{ $summary['month_name'] }})
                @endif
            </h6>
        </div>
        
        <div class="d-flex gap-2">
            @if($selectedClass)
            <a href="{{ route('attendance.export-monthly', ['class_id' => $selectedClassId, 'month_year' => sprintf('%04d-%02d', $year, $month)]) }}" 
               class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endif
            <button onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
    
    {{-- FILTERS --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form action="{{ route('attendance.monthly-report') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <!-- <label class="form-label small fw-bold mb-0">Select Class</label> -->
                    <select name="class_id" class="form-select form-select-sm" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <!-- <label class="form-label small fw-bold mb-0">Select Month</label> -->
                    <input type="month" name="month_year" class="form-control form-control-sm" 
                           value="{{ sprintf('%04d-%02d', $year, $month) }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-20">
                        <i class="fas fa-search me-1"></i> 
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('attendance.monthly-report') }}" class="btn btn-sm btn-outline-secondary w-20">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    {{-- SUMMARY STATISTICS --}}
    <!-- @if(!empty($summary) && isset($summary['total_students']))
    <div class="row g-2 mb-3">
        <div class="col-md-2">
            <div class="card shadow-sm border-0 bg-light text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark small">Total Students</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_students'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 bg-light text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Present</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_present'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 bg-light text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Late</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_late'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 bg-light text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Absent</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_absent'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 bg-info text-white">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Excused</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_excused'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
    @endif -->
    
    {{-- ATTENDANCE TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                @if(!empty($monthlyData) && count($monthlyData) > 0)
                <table class="table table-hover table-bordered mb-0 align-middle table-sm" id="monthlyTable">
                    <thead class="table-primary text-dark">
                        <tr>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 35px; position: sticky; left: 0; background: #e7f1ff; z-index: 12; font-size: 10px;">#</th>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 150px; position: sticky; left: 35px; background: #e7f1ff; z-index: 12; font-size: 10px;">
                                <i class="fas fa-user me-1"></i> Student Name
                            </th>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 80px; position: sticky; left: 185px; background: #e7f1ff; z-index: 12; font-size: 10px;">
                                Admission No.
                            </th>
                            @foreach($days as $day)
                                <th class="text-center p-1" style="min-width: 30px; font-size: 9px; {{ $day->isWeekend() ? 'background-color: #f8f9fa;' : '' }}">
                                    <div>{{ $day->format('d') }}</div>
                                    <!-- <small class="text-muted" style="font-size: 7px;">{{ $day->format('D') }}</small> -->
                                </th>
                            @endforeach
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 10px; min-width: 20px;">P</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 10px; min-width: 20px;">L</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 10px; min-width: 20px;">A</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 10px; min-width: 20px;">E</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 10px; min-width: 30px;">Total</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 10px; min-width: 40px;">Rate</th>
                        </tr>
                        <tr>
                            @foreach($days as $day)
                                <th class="text-center" style="{{ $day->isWeekend() ? 'background-color: #f8f9fa;' : '' }}">
                                    <small class="text-muted" style="font-size: 7px;">{{ $day->format('D') }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyData as $data)
                            <tr>
                                <td style="position: sticky; left: 0; background: white; z-index: 1; font-size: 10px; padding: 2px 4px; text-align: center;">
                                    {{ $loop->iteration }}
                                </td>
                                <td style="position: sticky; left: 35px; background: white; z-index: 1; font-size: 10px; padding: 2px 8px;">
                                    <strong>{{ $data['student']->first_name }} {{ $data['student']->last_name }}</strong>
                                </td>
                                <td style="position: sticky; left: 185px; background: white; z-index: 1; font-size: 10px; padding: 2px 8px;">
                                    {{ $data['student']->student_id ?? 'N/A' }}
                                </td>
                                
                                @foreach($days as $day)
                                    @php
                                        $dateString = $day->toDateString();
                                        $attendance = $data['attendance'][$dateString] ?? ['status' => 'absent'];
                                        $status = $attendance['status'];
                                        
                                        $color = match($status) {
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'excused' => 'info',
                                            'absent' => 'light',
                                            default => 'light'
                                        };
                                        $icon = match($status) {
                                            'present' => '✓',
                                            'late' => '⏰',
                                            'excused' => 'ℹ',
                                            'absent' => '✗',
                                            default => '—'
                                        };
                                        $textColor = match($status) {
                                            'present' => 'text-success',
                                            'late' => 'text-warning',
                                            'excused' => 'text-info',
                                            'absent' => 'text-muted',
                                            default => 'text-muted'
                                        };
                                    @endphp
                                    <td class="text-center p-0 {{ $day->isWeekend() ? 'bg-light' : '' }}" style="font-size: 10px;">
                                        <span class="badge bg-{{ $color }} p-1 {{ $textColor }}" 
                                              style="font-size: 9px; min-width: 22px; display: inline-block;"
                                              title="{{ ucfirst($status) }} - {{ $day->format('F d, Y') }}">
                                            {{ $icon }}
                                        </span>
                                    </td>
                                @endforeach
                                
                                <td class="text-center fw-bold text-success" style="font-size: 10px; padding: 2px 4px;">{{ $data['present'] }}</td>
                                <td class="text-center fw-bold text-warning" style="font-size: 10px; padding: 2px 4px;">{{ $data['late'] }}</td>
                                <td class="text-center fw-bold text-muted" style="font-size: 10px; padding: 2px 4px;">{{ $data['absent'] }}</td>
                                <td class="text-center fw-bold text-info" style="font-size: 10px; padding: 2px 4px;">{{ $data['excused'] }}</td>
                                <td class="text-center fw-bold" style="font-size: 10px; padding: 2px 4px;">{{ $data['total'] }}</td>
                                <td class="text-center" style="padding: 2px 4px;">
                                    @php
                                        $rate = $data['attendance_rate'];
                                        $badgeColor = $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} p-1" style="font-size: 9px; min-width: 35px; display: inline-block;">
                                        {{ $rate }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    
                    {{-- Summary Row --}}
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="3" style="position: sticky; left: 0; background: #e9ecef; z-index: 12; font-size: 10px; padding: 2px 8px;">
                                <strong>Daily Summary</strong>
                            </th>
                            @foreach($days as $day)
                                @php
                                    $dayPresent = 0;
                                    $dayLate = 0;
                                    $dayAbsent = 0;
                                    $dayExcused = 0;
                                    foreach($monthlyData as $data) {
                                        $dateString = $day->toDateString();
                                        $att = $data['attendance'][$dateString] ?? ['status' => 'absent'];
                                        if ($att['status'] === 'present') $dayPresent++;
                                        elseif ($att['status'] === 'late') $dayLate++;
                                        elseif ($att['status'] === 'excused') $dayExcused++;
                                        else $dayAbsent++;
                                    }
                                @endphp
                                <th class="text-center p-0 {{ $day->isWeekend() ? 'bg-light' : '' }}" style="font-size: 7px;">
                                    <div>P: {{ $dayPresent }}</div>
                                    <div>L: {{ $dayLate }}</div>
                                    <div>A: {{ $dayAbsent }}</div>
                                    <div>E: {{ $dayExcused }}</div>
                                </th>
                            @endforeach
                            <th class="text-center fw-bold p-1" style="font-size: 9px;">{{ $summary['total_present'] ?? 0 }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 9px;">{{ $summary['total_late'] ?? 0 }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 9px;">{{ $summary['total_absent'] ?? 0 }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 9px;">{{ $summary['total_excused'] ?? 0 }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 9px;">{{ count($monthlyData) * count($days) }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 9px;">—</th>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                    <p>Please select a class to view the monthly attendance report.</p>
                    @if(!empty($summary) && isset($summary['class_name']))
                    <p class="small">No attendance records found for {{ $summary['class_name'] }} in {{ $summary['month_name'] }}.</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- LEGEND --}}
    @if(!empty($monthlyData) && count($monthlyData) > 0)
    <div class="card shadow-sm mt-2">
        <div class="card-body py-2 px-3">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="fw-bold mb-1 small"><i class="fas fa-info-circle me-2"></i> Legend</h6>
                    <div class="d-flex flex-wrap gap-3" style="font-size: 11px;">
                        <div>
                            <span class="badge bg-success p-1 me-1" style="font-size: 9px;">✓</span>
                            <span>Present</span>
                        </div>
                        <div>
                            <span class="badge bg-warning p-1 me-1" style="font-size: 9px;">⏰</span>
                            <span>Late</span>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark p-1 me-1" style="font-size: 9px;">✗</span>
                            <span>Absent</span>
                        </div>
                        <div>
                            <span class="badge bg-info p-1 me-1" style="font-size: 9px;">ℹ</span>
                            <span>Excused</span>
                        </div>
                        <div>
                            <span class="badge bg-secondary p-1 me-1" style="font-size: 9px;">—</span>
                            <span>No Record</span>
                        </div>
                        <div class="border-start ps-3">
                            <span class="badge bg-success p-1 me-1" style="font-size: 9px;">P</span>
                            <span>Present</span>
                        </div>
                        <div>
                            <span class="badge bg-warning p-1 me-1" style="font-size: 9px;">L</span>
                            <span>Late</span>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark p-1 me-1" style="font-size: 9px;">A</span>
                            <span>Absent</span>
                        </div>
                        <div>
                            <span class="badge bg-info p-1 me-1" style="font-size: 9px;">E</span>
                            <span>Excused</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
/* Compact table styles */
.table-sm th,
.table-sm td {
    padding: 2px 4px !important;
    font-size: 10px !important;
}

.table-sm .badge {
    font-size: 8px !important;
    padding: 2px 4px !important;
}

/* Print styles */
@media print {
    .btn, .card-footer, form, .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 3px !important;
    }
    .table {
        font-size: 6px !important;
    }
    .table th, .table td {
        padding: 1px 2px !important;
    }
    .badge {
        border: 1px solid #ddd !important;
        font-size: 6px !important;
        padding: 1px 2px !important;
    }
    .table-responsive {
        max-height: none !important;
        overflow: visible !important;
    }
    .container-fluid {
        padding: 3px !important;
    }
    .card-body {
        padding: 3px !important;
    }
    .card {
        box-shadow: none !important;
    }
    .bg-primary, .bg-success, .bg-warning, .bg-danger, .bg-info {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    .bg-primary .card-title,
    .bg-success .card-title,
    .bg-warning .card-title,
    .bg-danger .card-title,
    .bg-info .card-title {
        color: #000 !important;
    }
    .bg-primary .card-subtitle,
    .bg-success .card-subtitle,
    .bg-warning .card-subtitle,
    .bg-danger .card-subtitle,
    .bg-info .card-subtitle {
        color: #666 !important;
    }
    .text-white {
        color: #000 !important;
    }
    .text-white-50 {
        color: #666 !important;
    }
    .table-primary {
        background-color: #e9ecef !important;
    }
    .table-primary th {
        background-color: #e9ecef !important;
    }
}

/* Table responsiveness */
.table-responsive {
    max-height: 500px;
    overflow-y: auto;
}

/* Sticky headers */
thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #e7f1ff;
}

thead th:first-child {
    z-index: 12;
}
thead th:nth-child(2) {
    z-index: 12;
}
thead th:nth-child(3) {
    z-index: 12;
}

tbody td:first-child {
    position: sticky;
    left: 0;
    background: white;
    z-index: 1;
}
tbody td:nth-child(2) {
    position: sticky;
    left: 35px;
    background: white;
    z-index: 1;
}
tbody td:nth-child(3) {
    position: sticky;
    left: 185px;
    background: white;
    z-index: 1;
}

tfoot th {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #e9ecef;
}

tfoot th:first-child {
    z-index: 12;
}
tfoot th:nth-child(2) {
    z-index: 12;
}
tfoot th:nth-child(3) {
    z-index: 12;
}

/* Card styles */
.card {
    border-radius: 8px;
}

.card-body {
    padding: 10px 15px;
}

.badge {
    border-radius: 3px;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.03);
}

/* Summary cards */
.card .card-body {
    padding: 6px 10px;
}

.card .card-title {
    font-size: 18px;
    font-weight: bold;
}

.card .card-subtitle {
    font-size: 10px;
}

/* Form controls */
.form-control-sm {
    font-size: 11px;
    padding: 4px 8px;
}

.btn-sm {
    font-size: 11px;
    padding: 4px 10px;
}

/* Scrollbar styling */
.table-responsive::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-sm th,
    .table-sm td {
        padding: 1px 2px !important;
        font-size: 7px !important;
    }
    
    .table-sm .badge {
        font-size: 6px !important;
        padding: 1px 2px !important;
        min-width: 16px !important;
    }
    
    .card .card-title {
        font-size: 14px;
    }
    
    .card .card-subtitle {
        font-size: 8px;
    }
    
    .container-fluid {
        padding: 3px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit on change
    const classSelect = document.querySelector('select[name="class_id"]');
    const monthInput = document.querySelector('input[name="month_year"]');
    
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }
    
    if (monthInput) {
        monthInput.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }
});

function printReport() {
    window.print();
}
</script>

@endsection