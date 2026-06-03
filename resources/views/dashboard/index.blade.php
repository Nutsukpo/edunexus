
@extends('layouts.master')


@section('title', 'Dashboard - Kabore USMS')

@section('content')
<!-- MAIN DASHBOARD CONTENT -->
<div class="container-fluid py-4 px-4">
    <!-- ROW 1 – KEY METRICS -->
    <div class="row g-4 mb-4">

    {{-- TOTAL STUDENTS --}}
    <div class="col-md-6 col-xl-3">

    <div class="card stats-card shadow-sm h-100 border-0">

        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-start mb-3">

                <div>

                    <small class="text-uppercase fw-bold text-muted">
                        <i class="fas fa-chalkboard-user me-1 text-success"></i>
                        Total Students
                    </small>

                    <h1 class="fw-bold display-5 mt-2 mb-1">
                        {{ number_format($totalStudents) }}
                    </h1>

                </div>

                <div class="bg-primary bg-opacity-10 rounded-circle p-3">

                    <i class="fas fa-user-graduate text-info fs-3"></i>

                </div>

            </div>

            <div class="d-flex gap-2 flex-wrap mt-3">

                <span class="badge bg-light text-dark px-3 py-2">

                    <i class="fas fa-male me-1"></i>
                    Male:
                    {{ $maleCount }}

                </span>

                <span class="badge bg-light text-dark px-3 py-2">

                    <i class="fas fa-female me-1"></i>
                    Female:
                    {{ $femaleCount }}

                </span>

            </div>

            <div class="mt-3">

                <small class="text-success fw-semibold">

                    <i class="fas fa-arrow-up me-1"></i>

                    {{ $studentsThisMonth }} new this month

                </small>

            </div>

        </div>

    </div>

</div>


    {{-- TOTAL STAFF --}}
<div class="col-md-6 col-xl-3">

<div class="card stats-card border-0 shadow-sm h-100 overflow-hidden">

    <div class="card-body p-4 position-relative">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-start">

            <div>

                <div class="analytics-header text-uppercase fw-bold small text-muted">

                    <i class="fas fa-chalkboard-user me-1 text-success"></i>
                    Total Staff

                </div>

                <h1 class="fw-bold display-4 mt-3 mb-1 stat-number">
                    {{ number_format($totalStaff) }}
                </h1>

                <small class="trend-up fs-6 fw-semibold text-success">

                    <i class="fas fa-arrow-up me-1"></i>
                    +{{ $staffThisMonth ?? 0 }} new this month

                </small>

            </div>

            {{-- ICON AVATAR --}}
            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:70px; height:70px;">

                <i class="fas fa-user-tie text-success fs-2"></i>

            </div>

        </div>

    </div>

</div>

</div>


{{-- ACTIVE CLASSES --}}
<div class="col-md-6 col-xl-3">

<div class="card stats-card border-0 shadow-sm h-100 overflow-hidden">

    <div class="card-body p-4 position-relative">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-start">

            <div>

                <div class="analytics-header text-uppercase fw-bold small text-muted">

                    <i class="fas fa-chalkboard me-1 text-warning"></i>
                    Active Classes

                </div>

                <h1 class="fw-bold display-4 mt-3 mb-1 stat-number">
                    {{ number_format($activeClasses) }}
                </h1>

                <small class="trend-neutral fs-6 fw-semibold text-dark">

                    <i class="fas fa-calendar-week me-1"></i>
                    Current Academic Term

                </small>

            </div>

            {{-- ICON AVATAR --}}
            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:70px; height:70px;">

                <i class="fas fa-school text-warning fs-2"></i>

            </div>

        </div>

    </div>

</div>

</div>


{{-- ATTENDANCE RATE --}}
<div class="col-md-6 col-xl-3">

<div class="card stats-card border-0 shadow-sm h-100 overflow-hidden">

    <div class="card-body p-4 position-relative">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-start">

            <div>

                <div class="analytics-header text-uppercase fw-bold small text-muted">

                    <i class="fas fa-percent me-1 text-danger"></i>
                    Attendance Rate

                </div>

                <h1 class="fw-bold display-4 mt-3 mb-1 stat-number">
                    {{ $attendanceRate ?? 90 }}%
                </h1>

                <small class="trend-up fs-6 fw-semibold text-dark">

                    <i class="fas fa-chart-line me-1"></i>
                    Student Attendance Performance

                </small>

            </div>

            {{-- ICON AVATAR --}}
            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:70px; height:70px;">

                <i class="fas fa-calendar-check text-danger fs-2"></i>

            </div>

        </div>

    </div>

</div>

</div>
</div>

    <!-- DAILY ATTENDANCE CHART - ENHANCED WITH 5 CHART TYPES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                        <div>
                            <h2 class="fw-light mb-1 fs-3">
                                <i class="fas fa-chart-line text-danger me-2"></i> Daily Attendance Analysis
                            </h2>
                            <small class="text-secondary">Students present per day | <strong class="text-dark">Feb 9 – May 4</strong></small>
                        </div>
                        <div class="d-flex gap-2 mt-1 mt-lg-0 flex-wrap">
                            <!-- Period Selector -->
                            <select class="form-select" id="periodSelect" style="width: auto;">
                                <option value="90">Last 90 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="7">Last 7 Days</option>
                            </select>
                            
                            <!-- Chart Type Selector with ALL options: Line, Bar, Spline, Area, SplineArea -->
                            <select class="form-select" id="chartTypeSelect" style="width: auto;">
                                <option value="line">📈 Line Chart</option>
                                <option value="bar">📊 Bar Chart</option>
                                <option value="spline">〰️ Spline Chart</option>
                                <option value="area">📉 Area Chart</option>
                                <option value="splineArea">🌀 Spline Area Chart</option>
                            </select>
                            
                            <!-- Color Picker -->
                            <div class="color-picker-group">
                                <label><i class="fas fa-palette me-1"></i> Color:</label>
                                <input type="color" id="lineColorPicker" value="#e11d48" title="Chart Color">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="custom-legend">
                            <div><span class="legend-dot-red" id="legendColorDot" style="background:#e11d48;"></span> <span class="fw-semibold">Daily attendance (students)</span></div>
                            <div><span class="legend-dot-blue"></span> <span class="fw-semibold">Current term benchmark</span> <span class="text-muted small">(term average)</span></div>
                        </div>
                        <div class="attendance-insight mt-2 mt-sm-0">
                            <i class="fas fa-chart-simple me-1 text-info"></i> Peak attendance: 1,145 students (Apr 20)
                        </div>
                    </div>

                    <!-- CHART CONTAINER -->
                    <div class="chart-container mt-3" style="height: 500px;">
                        <canvas id="attendanceChartCanvas"></canvas>
                    </div>

                    <div class="text-muted small mt-3 text-end border-top pt-2">
                        <i class="fas fa-info-circle me-1"></i> Y-axis: number of students present | 
                        <span id="chartTypeIndicator">Line Chart</span> | 
                        Interactive chart with smooth animations
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM FINANCIAL & SCHOOL STATS -->
    <div class="row g-4">
        <div class="col-md-6 col-xl-3 bottom-card">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header"><i class="fas fa-coins me-1 text-success"></i> TOTAL FEES PAID</div>
                    <h1 class="fw-bold display-5 mt-2 stat-number">GH₵ 10,284,000</h1>
                    <small class="trend-up fs-6">
                        <i class="fas fa-arrow-up me-1"></i> +12% this month
                    </small>
                    <div class="mt-2 small text-secondary">↑ collection rate improved</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 bottom-card">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header"><i class="fas fa-exclamation-triangle me-1 text-warning"></i> OUTSTANDING FEES</div>
                    <h1 class="fw-bold display-5 mt-2 stat-number">GH₵ 3,500,297</h1>
                    <small class="trend-neutral fs-6">
                        <i class="fas fa-clock me-1"></i> Pending payments (25% of total)
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 bottom-card">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header"><i class="fas fa-user-friends me-1 text-info"></i> TOTAL PARENTS</div>
                    <h1 class="fw-bold display-4 mt-2 stat-number">700</h1>
                    <small class="trend-up fs-6">
                        <i class="fas fa-arrow-up me-1"></i> +2.1% from last semester
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 bottom-card">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="analytics-header"><i class="fas fa-chalkboard me-1 text-secondary"></i> STUDENT/TEACHER RATIO</div>
                    <h1 class="fw-bold display-4 mt-2 stat-number">53:1</h1>
                    <small class="text-primary fs-6">
                        <i class="fas fa-graduation-cap me-1"></i> 24 teachers, 1,284 students
                    </small>
                    <div class="mt-2 small text-muted">Optimal target: 40:1</div>
                </div>
            </div>
        </div>
    </div>

    <!-- insight row -->
    <div class="row mt-1 g-2">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-sm rounded-4 insight-card">
                <div class="card-body py-3 px-4">
                    <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between">
                        <div><i class="fas fa-chart-pie text-primary me-2"></i> <strong>Fee collection rate:</strong> <span class="fw-bold text-success">74.6%</span> paid</div>
                        <div><i class="fas fa-arrow-trend-up text-success"></i> Outstanding decreased by 3% vs last term</div>
                        <div><small class="text-muted"><i class="far fa-calendar-alt"></i> Data as of {{ \Carbon\Carbon::now()->format('d-M-Y | H:i') }}</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Additional dashboard-specific styles */
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
    .legend-dot-red {
        width: 12px;
        height: 12px;
        background: #e11d48;
        border-radius: 20px;
        display: inline-block;
        margin-right: 8px;
    }
    .legend-dot-blue {
        width: 12px;
        height: 12px;
        background: #3b82f6;
        border-radius: 20px;
        display: inline-block;
        margin-right: 8px;
    }
    .attendance-insight {
        background: #fefaf5;
        border-radius: 40px;
        padding: 5px 18px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #7c3aed;
        border: 1px solid #f3e8ff;
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
        background: #e11d48;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .chart-container {
        position: relative;
        width: 100%;
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
    // Dynamic greeting
    function updateGreeting() {
        const hour = new Date().getHours();
        let greeting = '';
        if(hour < 12) greeting = 'Good Morning';
        else if(hour < 18) greeting = 'Good Afternoon';
        else greeting = 'Good Evening';
        const greetingEl = document.getElementById('greetingMessage');
        if(greetingEl) {
            greetingEl.style.opacity = '0';
            setTimeout(() => {
                greetingEl.innerHTML = `${greeting}, Kabore USMS 👋`;
                greetingEl.style.transition = 'opacity 0.3s';
                greetingEl.style.opacity = '1';
            }, 80);
        }
    }
    updateGreeting();

    // Attendance data
    const labels = [
        'Feb 9', 'Feb 16', 'Feb 23', 'Mar 2', 'Mar 9', 'Mar 16',
        'Mar 23', 'Mar 30', 'Apr 6', 'Apr 13', 'Apr 20', 'Apr 27', 'May 4'
    ];
    const attendanceData = [890, 912, 945, 968, 1002, 988, 1024, 1051, 1078, 1103, 1145, 1120, 1093];
    const termAvg = attendanceData.reduce((a,b) => a+b,0) / attendanceData.length;
    const benchmarkData = Array(labels.length).fill(Math.floor(termAvg));

    let currentChart = null;
    let currentChartType = 'line';
    let currentColor = '#e11d48';

    // Function to get tension based on chart type (spline effect)
    function getChartTension(chartType) {
        switch(chartType) {
            case 'spline':
            case 'splineArea':
                return 0.4;  // High tension for smooth spline curve
            default:
                return 0.2;  // Standard tension for line/area
        }
    }

    // Function to determine if fill is enabled
    function shouldFill(chartType) {
        return chartType === 'area' || chartType === 'splineArea';
    }

    // Function to get chart type for Chart.js (line or bar)
    function getBaseChartType(chartType) {
        if (chartType === 'bar') return 'bar';
        return 'line'; // line, spline, area, splineArea all use line type with different styling
    }

    function updateLegendDot(color) {
        const dot = document.getElementById('legendColorDot');
        if(dot) dot.style.backgroundColor = color;
    }

    // Update chart type indicator text
    function updateChartTypeIndicator(chartType) {
        const indicator = document.getElementById('chartTypeIndicator');
        if(indicator) {
            const names = {
                'line': 'Line Chart',
                'bar': 'Bar Chart',
                'spline': 'Spline Chart (Smooth Curve)',
                'area': 'Area Chart',
                'splineArea': 'Spline Area Chart'
            };
            indicator.textContent = names[chartType] || 'Line Chart';
        }
    }

    function renderAttendanceChart(chartType = currentChartType, lineColor = currentColor) {
        const canvas = document.getElementById('attendanceChartCanvas');
        if(!canvas) return;
        const ctx = canvas.getContext('2d');
        if(currentChart) currentChart.destroy();

        const isBar = chartType === 'bar';
        const fillArea = shouldFill(chartType);
        const tension = getChartTension(chartType);
        const baseType = getBaseChartType(chartType);
        
        // Update indicator
        updateChartTypeIndicator(chartType);

        const datasets = [
            {
                label: 'Daily Attendance (students present)',
                data: attendanceData,
                borderColor: lineColor,
                backgroundColor: fillArea ? `${lineColor}30` : (isBar ? lineColor : `${lineColor}08`),
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
                categoryPercentage: isBar ? 0.8 : 0.8,
                animation: { duration: 700, easing: 'easeOutQuart' }
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
                type: 'line',
                animation: { duration: 700 }
            }
        ];

        currentChart = new Chart(ctx, {
            type: baseType,
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let val = context.raw;
                                if(context.dataset.label.includes('Attendance')) {
                                    return `${label}: ${val.toLocaleString()} students (${Math.round((val/1284)*100)}% of enrollment)`;
                                }
                                return `${label}: ${val.toLocaleString()} students`;
                            }
                        }
                    },
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 700,
                        max: 1250,
                        ticks: { 
                            stepSize: 100, 
                            callback: val => val.toLocaleString() + ' students', 
                            color: '#6c7283' 
                        },
                        title: { 
                            display: true, 
                            text: 'Number of Students Present', 
                            color: '#4b5563', 
                            font: { weight: '500', size: 11 } 
                        },
                        grid: { color: '#eef2f6' }
                    },
                    x: { 
                        ticks: { color: '#6c7283', font: { size: 11 }, maxRotation: 35 }, 
                        grid: { display: false } 
                    }
                }
            }
        });
    }

    // Initialize chart when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        renderAttendanceChart('line', '#e11d48');

        const chartTypeSelect = document.getElementById('chartTypeSelect');
        const lineColorPicker = document.getElementById('lineColorPicker');
        const periodSelect = document.getElementById('periodSelect');

        if(chartTypeSelect) {
            chartTypeSelect.addEventListener('change', (e) => {
                currentChartType = e.target.value;
                renderAttendanceChart(currentChartType, currentColor);
            });
        }

        if(lineColorPicker) {
            lineColorPicker.addEventListener('input', (e) => {
                currentColor = e.target.value;
                updateLegendDot(currentColor);
                renderAttendanceChart(currentChartType, currentColor);
            });
        }

        if(periodSelect) {
            periodSelect.addEventListener('change', (e) => filterChartByPeriod(e.target.value));
        }
    });

    function filterChartByPeriod(period) {
        let filteredLabels = [...labels];
        let filteredAttendance = [...attendanceData];
        let filteredBenchmark = [...benchmarkData];
        if(period === '30') { 
            filteredLabels = labels.slice(-5); 
            filteredAttendance = attendanceData.slice(-5); 
            filteredBenchmark = benchmarkData.slice(-5); 
        } else if(period === '7') { 
            filteredLabels = labels.slice(-2); 
            filteredAttendance = attendanceData.slice(-2); 
            filteredBenchmark = benchmarkData.slice(-2); 
        }
        
        if(currentChart) currentChart.destroy();
        const ctx = document.getElementById('attendanceChartCanvas').getContext('2d');
        const isBar = currentChartType === 'bar';
        const fillArea = shouldFill(currentChartType);
        const tension = getChartTension(currentChartType);
        const baseType = getBaseChartType(currentChartType);
        
        currentChart = new Chart(ctx, {
            type: baseType,
            data: {
                labels: filteredLabels,
                datasets: [
                    { 
                        label: 'Daily Attendance (students present)', 
                        data: filteredAttendance, 
                        borderColor: currentColor, 
                        backgroundColor: fillArea ? `${currentColor}30` : (isBar ? currentColor : `${currentColor}08`),
                        pointBackgroundColor: currentColor, 
                        pointBorderColor: '#fff', 
                        pointRadius: isBar ? 0 : 4,
                        borderWidth: isBar ? 0 : 2.8, 
                        fill: fillArea, 
                        tension: tension,
                        barPercentage: isBar ? 0.7 : 0.65,
                        animation: { duration: 500 } 
                    },
                    { 
                        label: 'Current term benchmark (avg)', 
                        data: filteredBenchmark, 
                        borderColor: '#3b82f6', 
                        backgroundColor: 'transparent', 
                        borderWidth: 2, 
                        borderDash: [8,5], 
                        pointRadius: 3, 
                        pointBackgroundColor: '#3b82f6', 
                        fill: false, 
                        type: 'line' 
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { tooltip: { backgroundColor: '#1e293b' }, legend: { display: false } }, 
                scales: { 
                    y: { min: 700, max: 1250, ticks: { stepSize: 100, callback: val => val.toLocaleString() + ' students' } }, 
                    x: { ticks: { maxRotation: 35 } } 
                } 
            }
        });
    }
    
    updateLegendDot('#e11d48');
</script>
@endpush
