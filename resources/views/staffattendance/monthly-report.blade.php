@extends('layouts.master')

@section('title', 'Monthly Attendance Report')

@section('content')
<div class="container-fluid">
    
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 mt-3">
        <div>
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                Monthly Attendance Report - {{ $summary['month_name'] }}
            </h6>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('staffattendance.export-monthly', ['month_year' => sprintf('%04d-%02d', $year, $month)]) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            <button onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="fas fa-print me-1"></i> Print
            </button>
            
        </div>
    </div>
    
    {{-- MONTH SELECTOR --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form action="{{ route('staffattendance.monthly-report') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <!-- <label class="form-label small fw-bold mb-0">Select Month</label> -->
                    <input type="month" name="month_year" class="form-control form-control-sm" 
                           value="{{ sprintf('%04d-%02d', $year, $month) }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Generate
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('staffattendance.monthly-report') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-sync-alt me-1"></i> Current
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    {{-- SUMMARY STATISTICS --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Total Staff</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_staff'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Total Present</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_present'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Total Late</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_late'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white text-dark">
                <div class="card-body py-2 px-3">
                    <h6 class="card-subtitle mb-0 text-dark-50 small">Total Absent</h6>
                    <h4 class="card-title mb-0">{{ $summary['total_absent'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MONTHLY ATTENDANCE TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle table-sm" id="monthlyTable">
                    <thead class="table-primary text-dark">
                        <tr>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 120px; position: sticky; left: 0; background: #e7f1ff; z-index: 12; font-size: 11px;">
                                <i class="fas fa-user me-1"></i> Staff
                            </th>
                            @foreach($days as $day)
                                <th class="text-center p-1" style="min-width: 32px; font-size: 10px; {{ $day->isWeekend() ? 'background-color: #f8f9fa;' : '' }}">
                                    <div>{{ $day->format('d') }}</div>
                                    <small class="text-muted" style="font-size: 8px;">{{ $day->format('D') }}</small>
                                </th>
                            @endforeach
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 11px; min-width: 20px;">P</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 11px; min-width: 20px;">L</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 11px; min-width: 20px;">A</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 11px; min-width: 30px;">Total</th>
                            <th rowspan="2" class="align-middle text-center p-1" style="background-color: #e7f1ff; font-size: 11px; min-width: 45px;">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyData as $data)
                            <tr>
                                <td style="position: sticky; left: 0; background: white; z-index: 1; font-size: 11px; padding: 4px 8px;">
                                    <strong>{{ $data['staff']->first_name }} {{ $data['staff']->last_name }}</strong>
                                </td>
                                
                                @foreach($days as $day)
                                    @php
                                        $dateString = $day->toDateString();
                                        $attendance = $data['attendance'][$dateString] ?? ['status' => 'absent'];
                                        $status = $attendance['status'];
                                        
                                        $color = match($status) {
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'light',
                                            default => 'light'
                                        };
                                        $icon = match($status) {
                                            'present' => '✓',
                                            'late' => '⏰',
                                            'absent' => '✗',
                                            default => '—'
                                        };
                                        $textColor = match($status) {
                                            'present' => 'text-success',
                                            'late' => 'text-warning',
                                            'absent' => 'text-muted',
                                            default => 'text-muted'
                                        };
                                    @endphp
                                    <td class="text-center p-0 {{ $day->isWeekend() ? 'bg-light' : '' }}" style="font-size: 11px;">
                                        <span class="badge bg-{{ $color }} p-1 {{ $textColor }}" 
                                              style="font-size: 10px; min-width: 24px; display: inline-block;"
                                              title="{{ ucfirst($status) }} - {{ $day->format('F d, Y') }}">
                                            {{ $icon }}
                                        </span>
                                    </td>
                                @endforeach
                                
                                <td class="text-center fw-bold text-success" style="font-size: 11px; padding: 2px 4px;">{{ $data['present'] }}</td>
                                <td class="text-center fw-bold text-warning" style="font-size: 11px; padding: 2px 4px;">{{ $data['late'] }}</td>
                                <td class="text-center fw-bold text-muted" style="font-size: 11px; padding: 2px 4px;">{{ $data['absent'] }}</td>
                                <td class="text-center fw-bold" style="font-size: 11px; padding: 2px 4px;">{{ $data['total'] }}</td>
                                <td class="text-center" style="padding: 2px 4px;">
                                    @php
                                        $rate = $data['attendance_rate'];
                                        $badgeColor = $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} p-1" style="font-size: 10px; min-width: 40px; display: inline-block;">
                                        {{ $rate }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($days) + 7 }}" class="text-center py-3 text-muted" style="font-size: 12px;">
                                    <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                    No attendance records found for this month.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- Summary Row --}}
                    @if(count($monthlyData) > 0)
                    <tfoot class="table-secondary">
                        <tr>
                            <th style="position: sticky; left: 0; background: #e9ecef; z-index: 12; font-size: 10px; padding: 2px 8px;">
                                <strong>Summary</strong>
                            </th>
                            @foreach($days as $day)
                                @php
                                    $dayPresent = 0;
                                    $dayLate = 0;
                                    $dayAbsent = 0;
                                    foreach($monthlyData as $data) {
                                        $dateString = $day->toDateString();
                                        $att = $data['attendance'][$dateString] ?? ['status' => 'absent'];
                                        if ($att['status'] === 'present') $dayPresent++;
                                        elseif ($att['status'] === 'late') $dayLate++;
                                        else $dayAbsent++;
                                    }
                                @endphp
                                <th class="text-center p-0 {{ $day->isWeekend() ? 'bg-light' : '' }}" style="font-size: 8px;">
                                    <div>P: {{ $dayPresent }}</div>
                                    <div>L: {{ $dayLate }}</div>
                                    <div>A: {{ $dayAbsent }}</div>
                                </th>
                            @endforeach
                            <th class="text-center fw-bold p-1" style="font-size: 10px;">{{ $summary['total_present'] }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 10px;">{{ $summary['total_late'] }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 10px;">{{ $summary['total_absent'] }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 10px;">{{ count($monthlyData) * count($days) }}</th>
                            <th class="text-center fw-bold p-1" style="font-size: 10px;">—</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    {{-- LEGEND --}}
    <div class="card shadow-sm mt-2">
        <div class="card-body py-2 px-3">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="fw-bold mb-1 small"><i class="fas fa-info-circle me-2"></i> Legend</h6>
                    <div class="d-flex flex-wrap gap-3" style="font-size: 11px;">
                        <div>
                            <span class="badge bg-success p-1 me-1" style="font-size: 10px;">✓</span>
                            <span>Present</span>
                        </div>
                        <div>
                            <span class="badge bg-warning p-1 me-1" style="font-size: 10px;">⏰</span>
                            <span>Late</span>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark p-1 me-1" style="font-size: 10px;">✗</span>
                            <span>Absent</span>
                        </div>
                        <div>
                            <span class="badge bg-secondary p-1 me-1" style="font-size: 10px;">—</span>
                            <span>No Record</span>
                        </div>
                        <div class="border-start ps-3">
                            <span class="badge bg-info p-1 me-1" style="font-size: 10px;">P</span>
                            <span>Present</span>
                        </div>
                        <div>
                            <span class="badge bg-warning p-1 me-1" style="font-size: 10px;">L</span>
                            <span>Late</span>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark p-1 me-1" style="font-size: 10px;">A</span>
                            <span>Absent</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Compact table styles */
.table-sm th,
.table-sm td {
    padding: 2px 4px !important;
    font-size: 10px !important;
}

.table-sm .badge {
    font-size: 9px !important;
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
        margin-bottom: 5px !important;
    }
    .table {
        font-size: 7px !important;
    }
    .table th, .table td {
        padding: 1px 2px !important;
    }
    .badge {
        border: 1px solid #ddd !important;
        font-size: 7px !important;
        padding: 1px 2px !important;
    }
    .table-responsive {
        max-height: none !important;
        overflow: visible !important;
    }
    .container-fluid {
        padding: 5px !important;
    }
    .card-body {
        padding: 5px !important;
    }
    .card {
        box-shadow: none !important;
    }
    .bg-primary, .bg-success, .bg-warning, .bg-danger {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    .bg-primary .card-title,
    .bg-success .card-title,
    .bg-warning .card-title,
    .bg-danger .card-title {
        color: #000 !important;
    }
    .bg-primary .card-subtitle,
    .bg-success .card-subtitle,
    .bg-warning .card-subtitle,
    .bg-danger .card-subtitle {
        color: #666 !important;
    }
    .text-white {
        color: #000 !important;
    }
    .text-white-50 {
        color: #666 !important;
    }
}

/* Table responsiveness */
.table-responsive {
    max-height: 550px;
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

tbody td:first-child {
    position: sticky;
    left: 0;
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

/* Card styles */
.card {
    border-radius: 8px;
}

.card-body {
    padding: 10px 15px;
}

.badge {
    border-radius: 4px;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.03);
}

/* Summary cards */
.card .card-body {
    padding: 8px 12px;
}

.card .card-title {
    font-size: 20px;
    font-weight: bold;
}

.card .card-subtitle {
    font-size: 11px;
}

/* Form controls */
.form-control-sm {
    font-size: 12px;
    padding: 4px 8px;
}

.btn-sm {
    font-size: 12px;
    padding: 4px 10px;
}

/* Scrollbar styling */
.table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
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
        font-size: 8px !important;
    }
    
    .table-sm .badge {
        font-size: 7px !important;
        padding: 1px 2px !important;
        min-width: 18px !important;
    }
    
    .card .card-title {
        font-size: 16px;
    }
    
    .card .card-subtitle {
        font-size: 9px;
    }
    
    .container-fluid {
        padding: 5px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit month selector on change
    const monthInput = document.querySelector('input[name="month_year"]');
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