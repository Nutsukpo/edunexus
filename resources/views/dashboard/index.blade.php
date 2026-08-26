@extends('layouts.master')

@section('title', 'Dashboard - Kabore USMS')

@section('content')
<div class="container-fluid py-4 px-4">
    {{-- TOP METRICS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card stats-card shadow-sm h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <small class="text-uppercase fw-bold text-muted">
                                <i class="fas fa-chalkboard-user me-1 text-dark"></i>
                                Total Students
                            </small>
                            <h1 class="fw-bold display-5 mt-2 mb-1">
                                {{ number_format($totalStudents) }}
                            </h1>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-user-graduate text-dark fs-3"></i>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-male me-1"></i>
                            Male: {{ $maleCount }}
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-female me-1"></i>
                            Female: {{ $femaleCount }}
                        </span>
                    </div>

                    <div class="mt-3">
                        <small class="text-primary fw-semibold">
                            <i class="fas fa-arrow-up me-1"></i>
                            +{{ $studentsThisYear }} new this Academic Year
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase fw-bold small text-muted">
                                <i class="fas fa-chalkboard-user me-1 text-primary"></i>
                                Total Staff
                            </div>
                            <h1 class="fw-bold display-4 mt-3 mb-1">
                                {{ number_format($totalStaff) }}
                            </h1>
                            <small class="fw-semibold text-primary">
                                <i class="fas fa-arrow-up me-1"></i>
                                +{{ $staffThisYear }} new this Academic Year
                            </small>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                             style="width:70px; height:70px;">
                            <i class="fas fa-user-tie text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase fw-bold small text-muted">
                                <i class="fas fa-chalkboard me-1 text-info"></i>
                                Active Classes
                            </div>
                            <h1 class="fw-bold display-4 mt-3 mb-1">
                                {{ number_format($activeClasses) }}
                            </h1>
                            <small class="fw-semibold text-dark">
                                <i class="fas fa-calendar-week me-1"></i>
                                Current Academic Term
                            </small>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                             style="width:70px; height:70px;">
                            <i class="fas fa-school text-info fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase fw-bold small text-muted">
                                <i class="fas fa-percent me-1 text-danger"></i>
                                Attendance Rate
                            </div>
                            <h1 class="fw-bold display-4 mt-3 mb-1">
                                {{ $studentAttendanceRate ?? 0 }}%
                            </h1>
                            <small class="fw-semibold text-dark">
                                <i class="fas fa-chart-line me-1"></i>
                                Student Attendance Performance
                            </small>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                             style="width:70px; height:70px;">
                            <i class="fas fa-calendar-check text-danger fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DAILY ATTENDANCE CHART --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div>
                            <h2 class="fw-light mb-1 fs-3">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                Daily Attendance Analysis
                            </h2>
                            <small class="text-secondary">
                                Students present per day from database
                            </small>
                        </div>

                        <div class="d-flex gap-2 mt-2 mt-lg-0 flex-wrap">
                            <select class="form-select" id="periodSelect" style="width:auto;">
                                <option value="90">Last 90 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="7">Last 7 Days</option>
                            </select>

                            <select class="form-select" id="chartTypeSelect" style="width:auto;">
                                <option value="line">📈 Line Chart</option>
                                <option value="bar">📊 Bar Chart</option>
                                <option value="spline">〰️ Spline Chart</option>
                                <option value="area">📉 Area Chart</option>
                                <option value="splineArea">🌀 Spline Area Chart</option>
                            </select>

                            <div class="color-picker-group">
                                <label><i class="fas fa-palette me-1"></i> Color:</label>
                                <input type="color" id="lineColorPicker" value="#1a4b8c" title="Chart Color">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="custom-legend">
                            <div>
                                <span class="legend-dot-blue" id="legendColorDot" style="background:#1a4b8c;"></span>
                                <span class="fw-semibold">Daily attendance (students)</span>
                            </div>
                            <div>
                                <span class="legend-dot-lightblue"></span>
                                <span class="fw-semibold">Current term benchmark</span>
                                <span class="text-muted small">(average)</span>
                            </div>
                        </div>

                        <div class="attendance-insight mt-2 mt-sm-0" id="attendanceInsight">
                            <i class="fas fa-chart-simple me-1 text-info"></i>
                            Loading attendance insight...
                        </div>
                    </div>

                    <div class="chart-container mt-3" style="height: 500px;">
                        <canvas id="attendanceChartCanvas"></canvas>
                    </div>

                    <div class="text-muted small mt-3 text-end border-top pt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Y-axis: number of students present |
                        <span id="chartTypeIndicator">Line Chart</span> |
                        Live attendance data from database
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTTOM STATS CARDS --}}
    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header">
                        <i class="fas fa-coins me-1 text-success"></i> TOTAL FEES PAID
                    </div>
                    <h1 class="fw-bold display-5 mt-2">GH₵ 10,284,000</h1>
                    <small class="text-success">
                        <i class="fas fa-arrow-up me-1"></i> +12% this month
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header">
                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i> OUTSTANDING FEES
                    </div>
                    <h1 class="fw-bold display-5 mt-2">GH₵ 3,500,297</h1>
                    <small class="text-dark">
                        <i class="fas fa-clock me-1"></i> Pending payments
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header">
                        <i class="fas fa-user-friends me-1 text-info"></i> STAFF ATTENDANCE
                    </div>
                    <h1 class="fw-bold display-4 mt-2">{{$staffAttendanceRate ?? 0 }}%</h1>
                    <small class="text-dark">
                        <i class="fas fa-arrow-up me-1"></i> Staff Attendance Performance
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header">
                        <i class="fas fa-chalkboard me-1 text-secondary"></i> STUDENT/TEACHER RATIO
                    </div>
                    <h1 class="fw-bold display-4 mt-2">
                        {{ $totalStaff > 0 ? round($totalStudents / $totalStaff) : 0 }}:1
                    </h1>
                    <small class="text-primary fs-6">
                        <i class="fas fa-graduation-cap me-1"></i>
                        {{ $totalStaff }} teachers, {{ $totalStudents }} students
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- CLASS ATTENDANCE TABLE --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        Daily Attendance by Class
                        <span class="badge bg-light text-dark ms-2" id="attendanceDateDisplay">
                            {{ \Carbon\Carbon::now()->format('d M Y') }}
                        </span>
                    </h5>
                    <div class="d-flex gap-2">
                        <input type="date" id="attendanceDateFilter" class="form-control form-control-sm" 
                               style="width: auto;" value="{{ date('Y-m-d') }}">
                        <button class="btn btn-sm btn-primary" id="loadClassAttendanceBtn">
                            <i class="fas fa-sync me-1"></i> Load
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportClassAttendance()">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="classAttendanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Class Name</th>
                                    <th class="text-center">Total Students</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">Excused</th>
                                    <th class="text-center">Attendance Rate</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="classAttendanceBody">
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="spinner-border text-primary me-2" role="status"></div>
                                        Loading attendance data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-muted small" id="attendanceSummaryFooter">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing attendance for <span id="totalClassesCount">0</span> classes | 
                        Last updated: <span id="lastUpdatedTime">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER INSIGHT --}}
    <div class="row mt-4 g-2">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-sm rounded-4 insight-card">
                <div class="card-body py-3 px-4">
                    <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-chart-pie text-primary me-2"></i>
                            <strong>Attendance dashboard:</strong>
                            <span class="fw-bold text-primary">Live data enabled</span>
                        </div>
                        <div>
                            <i class="fas fa-database text-info me-1"></i>
                            <span class="text-muted">Real-time class attendance</span>
                        </div>
                        <div>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt"></i>
                                Data as of {{ \Carbon\Carbon::now()->format('d-M-Y | H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .display-4 {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .display-5 {
        font-size: 2rem;
        font-weight: 700;
    }

    .custom-legend {
        display: flex;
        gap: 28px;
        align-items: center;
        margin-top: 6px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .legend-dot-blue,
    .legend-dot-lightblue {
        width: 12px;
        height: 12px;
        border-radius: 20px;
        display: inline-block;
        margin-right: 8px;
    }

    .legend-dot-blue {
        background: #1a4b8c;
    }

    .legend-dot-lightblue {
        background: #3b82f6;
    }

    .attendance-insight {
        background: #eff6ff;
        border-radius: 40px;
        padding: 5px 18px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #1a4b8c;
        border: 1px solid #bfdbfe;
    }

    .color-picker-group {
        background: #f8fafc;
        padding: 4px 14px;
        border-radius: 36px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #eef2ff;
    }

    .color-picker-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    #lineColorPicker {
        width: 34px;
        height: 34px;
        border: 2px solid white;
        border-radius: 12px;
        cursor: pointer;
        background: #1a4b8c;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }

    .chart-container {
        position: relative;
        width: 100%;
    }

    #classAttendanceTable th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    #classAttendanceTable td {
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .attendance-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    @media (max-width: 768px) {
        .display-4 { font-size: 1.8rem; }
        .display-5 { font-size: 1.5rem; }
        .custom-legend { justify-content: flex-start; }
    }
</style>
@endpush

@push('scripts')
<script>
    // ========================================
    // CHART CONFIGURATION
    // ========================================
    let currentChart = null;
    let currentChartType = 'line';
    let currentColor = '#1a4b8c';
    let chartDataFromServer = {
        labels: [],
        attendance: [],
        benchmark: [],
        peak: 0,
        peak_day: 'N/A',
        average: 0,
        total: 0
    };

    function getChartTension(chartType) {
        return (chartType === 'spline' || chartType === 'splineArea') ? 0.4 : 0.2;
    }

    function shouldFill(chartType) {
        return chartType === 'area' || chartType === 'splineArea';
    }

    function getBaseChartType(chartType) {
        return chartType === 'bar' ? 'bar' : 'line';
    }

    function updateLegendDot(color) {
        const dot = document.getElementById('legendColorDot');
        if (dot) dot.style.backgroundColor = color;
    }

    function updateChartTypeIndicator(chartType) {
        const indicator = document.getElementById('chartTypeIndicator');
        if (!indicator) return;

        const names = {
            line: 'Line Chart',
            bar: 'Bar Chart',
            spline: 'Spline Chart (Smooth Curve)',
            area: 'Area Chart',
            splineArea: 'Spline Area Chart'
        };

        indicator.textContent = names[chartType] || 'Line Chart';
    }

    function updateAttendanceInsight(data) {
        const insight = document.getElementById('attendanceInsight');
        if (!insight) return;

        if (!data.labels.length) {
            insight.innerHTML = `
                <i class="fas fa-exclamation-circle me-1 text-danger"></i>
                No attendance data found for this period
            `;
            return;
        }

        insight.innerHTML = `
            <i class="fas fa-chart-simple me-1 text-info"></i>
            Peak attendance: ${data.peak.toLocaleString()} students (${data.peak_day})
        `;
    }

    // ========================================
    // LOAD ATTENDANCE DATA FOR CHART
    // ========================================
    async function loadAttendanceData(days = 30) {
        const insight = document.getElementById('attendanceInsight');
        if (insight) {
            insight.innerHTML = `
                <i class="fas fa-spinner fa-spin me-1 text-info"></i>
                Loading attendance data...
            `;
        }

        try {
            const response = await fetch(`{{ route('dashboard.attendance-data') }}?days=${days}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            chartDataFromServer = {
                labels: data.labels || [],
                attendance: data.attendance || [],
                benchmark: data.benchmark || [],
                peak: data.peak || 0,
                peak_day: data.peak_day || 'N/A',
                average: data.average || 0,
                total: data.total || 0
            };

            updateAttendanceInsight(chartDataFromServer);
            renderAttendanceChart(currentChartType, currentColor);
        } catch (error) {
            console.error('Error loading attendance data:', error);

            if (insight) {
                insight.innerHTML = `
                    <i class="fas fa-exclamation-circle me-1 text-danger"></i>
                    Failed to load attendance data
                `;
            }
        }
    }

    // ========================================
    // RENDER ATTENDANCE CHART
    // ========================================
    function renderAttendanceChart(chartType = currentChartType, lineColor = currentColor) {
        const canvas = document.getElementById('attendanceChartCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (currentChart) currentChart.destroy();

        const labels = chartDataFromServer.labels;
        const attendanceData = chartDataFromServer.attendance;
        const benchmarkData = chartDataFromServer.benchmark;

        updateChartTypeIndicator(chartType);

        if (!labels.length || !attendanceData.length) {
            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['No data'],
                    datasets: [{
                        label: 'Daily Attendance',
                        data: [0],
                        borderColor: lineColor,
                        backgroundColor: `${lineColor}20`
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true },
                        x: { display: true }
                    }
                }
            });
            return;
        }

        const isBar = chartType === 'bar';
        const fillArea = shouldFill(chartType);
        const tension = getChartTension(chartType);
        const baseType = getBaseChartType(chartType);

        currentChart = new Chart(ctx, {
            type: baseType,
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Daily Attendance (students present)',
                        data: attendanceData,
                        borderColor: lineColor,
                        backgroundColor: fillArea
                            ? `${lineColor}30`
                            : (isBar ? lineColor : `${lineColor}10`),
                        pointBackgroundColor: lineColor,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: isBar ? 0 : 4,
                        pointHoverRadius: isBar ? 4 : 7,
                        fill: fillArea,
                        tension: tension,
                        borderWidth: isBar ? 0 : 2.8,
                        borderRadius: isBar ? 8 : 0,
                        barPercentage: isBar ? 0.7 : 0.65,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Current term benchmark (avg)',
                        data: benchmarkData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'transparent',
                        borderWidth: 2.2,
                        borderDash: [8, 5],
                        pointRadius: 3,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        fill: false,
                        tension: 0.1,
                        type: 'line'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value.toLocaleString()} students`;
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#6c7283',
                            callback: value => value.toLocaleString() + ' students'
                        },
                        grid: {
                            color: '#eef2f6'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c7283',
                            font: { size: 11 },
                            maxRotation: 35
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // ========================================
    // LOAD CLASS ATTENDANCE TABLE
    // ========================================
    function loadClassAttendance(date = null) {
        if (!date) {
            date = document.getElementById('attendanceDateFilter').value;
        }

        const tbody = document.getElementById('classAttendanceBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border text-primary me-2" role="status"></div>
                    Loading class attendance data...
                </td>
            </tr>
        `;

        fetch(`/dashboard/class-attendance?date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.classes.length > 0) {
                    renderClassAttendanceTable(data);
                    document.getElementById('attendanceDateDisplay').textContent = 
                        new Date(date).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fs-3 d-block mb-2"></i>
                                No attendance records found for this date
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading class attendance:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-circle fs-3 d-block mb-2"></i>
                            Error loading attendance data
                        </td>
                    </tr>
                `;
            });
    }

    // ========================================
    // RENDER CLASS ATTENDANCE TABLE
    // ========================================
    function renderClassAttendanceTable(data) {
        const tbody = document.getElementById('classAttendanceBody');
        let html = '';

        data.classes.forEach((classData, index) => {
            const rate = classData.rate || 0;
            let statusBadge = '';
            let statusColor = '';

            if (rate >= 80) {
                statusBadge = 'Excellent';
                statusColor = 'success';
            } else if (rate >= 60) {
                statusBadge = 'Good';
                statusColor = 'info';
            } else if (rate >= 40) {
                statusBadge = 'Average';
                statusColor = 'warning';
            } else {
                statusBadge = 'Poor';
                statusColor = 'danger';
            }

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td class="fw-semibold">
                       
                        ${classData.class_name}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">${classData.total_students}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">${classData.present}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">${classData.absent}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">${classData.late}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">${classData.excused}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="progress" style="width: 70px; height: 8px;">
                                <div class="progress-bar bg-${statusColor}" role="progressbar" 
                                     style="width: ${rate}%;" 
                                     aria-valuenow="${rate}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <span class="ms-2 fw-bold">${rate}%</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-${statusColor} attendance-badge">
                            <i class="fas fa-${rate >= 80 ? 'check-circle' : rate >= 60 ? 'info-circle' : rate >= 40 ? 'exclamation-triangle' : 'times-circle'} me-1"></i>
                            ${statusBadge}
                        </span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Update summary
        document.getElementById('totalClassesCount').textContent = data.classes.length;
        document.getElementById('lastUpdatedTime').textContent = new Date().toLocaleTimeString();
    }

    // ========================================
    // EXPORT CLASS ATTENDANCE TO EXCEL
    // ========================================
    function exportClassAttendance() {
        const table = document.getElementById('classAttendanceTable');
        if (!table) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'Please load attendance records first'
            });
            return;
        }

        try {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            ws['!cols'] = [
                { wch: 5 }, { wch: 25 }, { wch: 15 }, { wch: 15 }, 
                { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 20 }, { wch: 15 }
            ];
            XLSX.utils.book_append_sheet(wb, ws, 'Class Attendance');
            XLSX.writeFile(wb, `Class_Attendance_${new Date().toISOString().slice(0,10)}.xlsx`);
            
            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                timer: 1500,
                showConfirmButton: false
            });
        } catch(error) {
            Swal.fire({
                icon: 'error',
                title: 'Export Failed',
                text: error.message
            });
        }
    }

    // ========================================
    // INITIALIZATION
    // ========================================
    document.addEventListener('DOMContentLoaded', function () {
        // Chart initialization
        updateLegendDot(currentColor);
        loadAttendanceData(30);

        const chartTypeSelect = document.getElementById('chartTypeSelect');
        const lineColorPicker = document.getElementById('lineColorPicker');
        const periodSelect = document.getElementById('periodSelect');

        if (chartTypeSelect) {
            chartTypeSelect.addEventListener('change', function (e) {
                currentChartType = e.target.value;
                renderAttendanceChart(currentChartType, currentColor);
            });
        }

        if (lineColorPicker) {
            lineColorPicker.addEventListener('input', function (e) {
                currentColor = e.target.value;
                updateLegendDot(currentColor);
                renderAttendanceChart(currentChartType, currentColor);
            });
        }

        if (periodSelect) {
            periodSelect.addEventListener('change', function (e) {
                loadAttendanceData(e.target.value);
            });
        }

        // Class attendance table initialization
        const loadBtn = document.getElementById('loadClassAttendanceBtn');
        if (loadBtn) {
            loadBtn.addEventListener('click', function () {
                const date = document.getElementById('attendanceDateFilter').value;
                loadClassAttendance(date);
            });
        }

        // Auto-load class attendance on page load
        loadClassAttendance(document.getElementById('attendanceDateFilter').value);

        // Auto-refresh class attendance every 60 seconds
        setInterval(() => {
            const date = document.getElementById('attendanceDateFilter').value;
            loadClassAttendance(date);
        }, 60000);
    });
</script>
@endpush