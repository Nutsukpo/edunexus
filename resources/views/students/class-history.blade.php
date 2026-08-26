@extends('students.layouts.app')

@section('title', 'Class History - EduNexus')

@section('content')
<style>
    /* Header */
    .class-history-header {
        background: linear-gradient(135deg, #1a0000 0%, #4a0000 30%, #6b0000 60%, #8b0000 100%);
        color: white;
        border-radius: 16px;
        padding: 30px 35px;
        margin-bottom: 30px;
        box-shadow: 0 8px 32px rgba(139, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }

    .class-history-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .class-history-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 50%;
        pointer-events: none;
    }

    .class-history-header h2 {
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
        position: relative;
        z-index: 1;
    }

    .class-history-header h2 i {
        color: #fca5a5;
        margin-right: 12px;
    }

    .class-history-header .sub-info {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
        position: relative;
        z-index: 1;
    }

    .class-history-header .sub-info i {
        margin-right: 6px;
        color: #fca5a5;
    }

    .class-history-header .header-badge {
        background: rgba(255, 255, 255, 0.12);
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .class-history-header .header-badge i {
        color: #fca5a5;
    }

    /* Summary Cards - Dark Red Gradient Theme */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #eef2f6;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #dc2626, #b91c1c, #7f1d1d);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 30px rgba(139, 0, 0, 0.12);
        border-color: rgba(220, 38, 38, 0.15);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 12px;
    }

    .stat-card .stat-icon.primary {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(139, 0, 0, 0.1));
        color: #dc2626;
    }

    .stat-card .stat-icon.success {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(22, 163, 74, 0.1));
        color: #22c55e;
    }

    .stat-card .stat-icon.info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
        color: #3b82f6;
    }

    .stat-card .stat-icon.warning {
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.1), rgba(202, 138, 4, 0.1));
        color: #eab308;
    }

    .stat-card .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }

    .stat-card .stat-label {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
    }

    .stat-card .stat-change {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 8px;
    }

    .stat-card .stat-change.up {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }

    .stat-card .stat-change.down {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .stat-card .stat-change.neutral {
        background: rgba(234, 179, 8, 0.1);
        color: #eab308;
    }

    /* Timeline */
    .timeline-wrapper {
        background: white;
        border-radius: 16px;
        border: 1px solid #eef2f6;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    .timeline-header {
        padding: 18px 24px;
        background: #fafafa;
        border-bottom: 1px solid #eef2f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .timeline-header h5 {
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .timeline-header h5 i {
        color: #dc2626;
        margin-right: 10px;
    }

    .timeline-body {
        padding: 0;
        max-height: 600px;
        overflow-y: auto;
    }

    .timeline-body::-webkit-scrollbar {
        width: 6px;
    }

    .timeline-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .timeline-body::-webkit-scrollbar-thumb {
        background: rgba(220, 38, 38, 0.3);
        border-radius: 10px;
    }

    .timeline-body::-webkit-scrollbar-thumb:hover {
        background: rgba(220, 38, 38, 0.5);
    }

    /* Timeline Item */
    .timeline-item {
        display: flex;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
        position: relative;
    }

    .timeline-item:hover {
        background: #fafafa;
    }

    .timeline-item:last-child {
        border-bottom: none;
    }

    .timeline-item .timeline-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        margin-right: 16px;
    }

    .timeline-item .timeline-badge.current {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .timeline-item .timeline-badge.completed {
        background: #f1f5f9;
        color: #64748b;
    }

    .timeline-item .timeline-badge.graduated {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
    }

    .timeline-item .timeline-content {
        flex: 1;
        min-width: 0;
    }

    .timeline-item .timeline-content .class-name {
        font-weight: 600;
        font-size: 1rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .timeline-item .timeline-content .class-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .timeline-item .timeline-content .class-meta .meta-item {
        font-size: 0.8rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .timeline-item .timeline-content .class-meta .meta-item i {
        font-size: 0.7rem;
    }

    .timeline-item .timeline-content .class-status {
        margin-top: 6px;
    }

    .status-badge {
        padding: 3px 14px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .status-badge.current {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .status-badge.completed {
        background: rgba(100, 116, 139, 0.1);
        color: #64748b;
    }

    .status-badge.graduated {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }

    .status-badge.promoted {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .status-badge.repeated {
        background: rgba(234, 179, 8, 0.1);
        color: #eab308;
    }

    .timeline-item .timeline-date {
        font-size: 0.75rem;
        color: #94a3b8;
        text-align: right;
        flex-shrink: 0;
        margin-left: 16px;
    }

    .timeline-item .timeline-date .date-label {
        display: block;
        font-weight: 500;
        color: #64748b;
    }

    .timeline-item .timeline-date .date-value {
        display: block;
        font-size: 0.7rem;
    }

    /* Progress line */
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 44px;
        top: 60px;
        bottom: 0;
        width: 2px;
        background: #eef2f6;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-item.current::before {
        background: linear-gradient(180deg, #dc2626, #eef2f6);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #e2e8f0;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #1e293b;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #94a3b8;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Export Button */
    .btn-edunexus {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-edunexus:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        color: white;
    }

    .btn-outline-edunexus {
        border: 2px solid #dc2626;
        color: #dc2626;
        background: transparent;
        padding: 8px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-outline-edunexus:hover {
        background: #dc2626;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.25);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .class-history-header {
            padding: 20px;
        }

        .class-history-header h2 {
            font-size: 1.4rem;
        }

        .stat-card .stat-number {
            font-size: 1.6rem;
        }

        .timeline-item {
            padding: 14px 16px;
            flex-wrap: wrap;
        }

        .timeline-item .timeline-date {
            width: 100%;
            text-align: left;
            margin-left: 60px;
            margin-top: 4px;
        }

        .timeline-item::before {
            left: 36px;
            top: 52px;
        }

        .timeline-item .timeline-badge {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }

        .timeline-header {
            padding: 14px 16px;
            flex-direction: column;
            align-items: stretch;
        }

        .timeline-header .d-flex {
            justify-content: stretch;
        }

        .timeline-header .btn-edunexus,
        .timeline-header .btn-outline-edunexus {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .stat-card {
            padding: 16px;
        }

        .stat-card .stat-number {
            font-size: 1.3rem;
        }

        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .timeline-item .timeline-content .class-name {
            font-size: 0.9rem;
        }

        .timeline-item .timeline-content .class-meta .meta-item {
            font-size: 0.7rem;
        }
    }

    /* Print Styles */
    @media print {
        .class-history-header {
            background: #1a0000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .stat-card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        .timeline-wrapper {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        .btn-edunexus,
        .btn-outline-edunexus {
            display: none !important;
        }
    }
</style>

<div class="class-history-container">
    <!-- Header -->
    <div class="class-history-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2><i class="fas fa-history"></i> My Class History</h2>
                <div class="sub-info">
                    <i class="fas fa-user-graduate"></i>
                    <span>{{ $student->full_name ?? 'Student' }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-graduation-cap"></i>
                    <span>{{ $student->class->name ?? 'Not Assigned' }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-calendar-alt"></i>
                    <span>Academic Journey</span>
                </div>
            </div>
            <div class="header-badge mt-2 mt-sm-0">
                <i class="fas fa-clock"></i>
                <span>{{ now()->format('l, F d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-school"></i>
                </div>
                <div class="stat-number">{{ $summary['total_classes'] ?? 0 }}</div>
                <div class="stat-label">Total Classes</div>
                <div>
                    <span class="stat-change neutral">
                        <i class="fas fa-minus-circle me-1"></i> All Time
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number">{{ $summary['current_class'] ?? 0 }}</div>
                <div class="stat-label">Current Class</div>
                <div>
                    <span class="stat-change up">
                        <i class="fas fa-arrow-up me-1"></i> Active
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <div class="stat-number">{{ $summary['completed_classes'] ?? 0 }}</div>
                <div class="stat-label">Completed</div>
                <div>
                    <span class="stat-change neutral">
                        <i class="fas fa-check-circle me-1"></i> Done
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number">{{ $summary['total_years'] ?? 0 }}</div>
                <div class="stat-label">Academic Years</div>
                <div>
                    <span class="stat-change neutral">
                        <i class="fas fa-calendar-check me-1"></i> Total
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline / Class History -->
    <div class="timeline-wrapper">
        <div class="timeline-header">
            <h5><i class="fas fa-timeline"></i> Academic Journey Timeline</h5>
            <div class="d-flex gap-2">
                <button class="btn-outline-edunexus" id="printClassHistory">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn-edunexus" id="exportClassHistory">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <div class="timeline-body">
            @if(isset($classHistory) && $classHistory->count() > 0)
                @foreach($classHistory as $assignment)
                    @php
                        $status = 'completed';
                        $statusLabel = 'Completed';
                        $icon = 'fa-check-circle';
                        $badgeClass = 'completed';
                        
                        if($assignment->is_current) {
                            $status = 'current';
                            $statusLabel = 'Current';
                            $icon = 'fa-star';
                            $badgeClass = 'current';
                        } elseif(isset($assignment->status) && $assignment->status == 'graduated') {
                            $status = 'graduated';
                            $statusLabel = 'Graduated';
                            $icon = 'fa-graduation-cap';
                            $badgeClass = 'graduated';
                        } elseif(isset($assignment->status) && $assignment->status == 'promoted') {
                            $status = 'promoted';
                            $statusLabel = 'Promoted';
                            $icon = 'fa-arrow-up';
                            $badgeClass = 'promoted';
                        } elseif(isset($assignment->status) && $assignment->status == 'repeated') {
                            $status = 'repeated';
                            $statusLabel = 'Repeated';
                            $icon = 'fa-redo';
                            $badgeClass = 'repeated';
                        }
                    @endphp
                    <div class="timeline-item {{ $status }}">
                        <div class="timeline-badge {{ $status }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="class-name">
                                {{ $assignment->studentClass->name ?? 'N/A' }}
                                <span class="status-badge {{ $badgeClass }}">
                                    <i class="fas {{ $icon }}"></i>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="class-meta">
                                <span class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $assignment->academicYear->name ?? 'N/A' }}
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-user"></i>
                                    {{ $assignment->studentClass->teacher->name ?? 'N/A' }}
                                </span>
                                @if(isset($assignment->studentClass->students_count))
                                    <span class="meta-item">
                                        <i class="fas fa-users"></i>
                                        {{ $assignment->studentClass->students_count }} Students
                                    </span>
                                @endif
                            </div>
                            <div class="class-status">
                                @if($assignment->is_current)
                                    <span class="text-success small">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                        Currently Enrolled
                                    </span>
                                @elseif(isset($assignment->end_date) && $assignment->end_date)
                                    <span class="text-muted small">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        Completed: {{ $assignment->end_date->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-date">
                            <span class="date-label">
                                @if($assignment->start_date)
                                    {{ $assignment->start_date->format('M d, Y') }}
                                @else
                                    {{ $assignment->created_at->format('M d, Y') }}
                                @endif
                            </span>
                            @if($assignment->start_date && $assignment->end_date)
                                <span class="date-value">
                                    {{ $assignment->start_date->diffInDays($assignment->end_date) }} days
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>No Class History Available</h4>
                    <p>You haven't been assigned to any classes yet. Your academic journey will appear here once you're enrolled.</p>
                    <button class="btn-edunexus mt-3" onclick="window.location.href='{{ route('students.dashboard') }}'">
                        <i class="fas fa-home"></i> Go to Dashboard
                    </button>
                </div>
            @endif
        </div>

        @if(isset($classHistory) && $classHistory->hasPages())
            <div class="p-3 border-top">
                {{ $classHistory->links() }}
            </div>
        @endif
    </div>

    <!-- Additional Info -->
    @if(isset($classHistory) && $classHistory->count() > 0)
    <div class="row mt-4 g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="fas fa-chart-pie text-danger me-2"></i> Class Distribution</h6>
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <span class="badge bg-danger" style="font-size: 1rem; padding: 8px 16px;">
                                {{ $summary['current_class'] ?? 0 }}
                            </span>
                            <div class="small text-muted mt-1">Current</div>
                        </div>
                        <div>
                            <span class="badge bg-secondary" style="font-size: 1rem; padding: 8px 16px;">
                                {{ $summary['completed_classes'] ?? 0 }}
                            </span>
                            <div class="small text-muted mt-1">Completed</div>
                        </div>
                        <div>
                            <span class="badge bg-success" style="font-size: 1rem; padding: 8px 16px;">
                                {{ $summary['total_classes'] ?? 0 }}
                            </span>
                            <div class="small text-muted mt-1">Total</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="fas fa-lightbulb text-danger me-2"></i> Quick Tips</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span class="small text-muted">Keep track of your academic journey</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            <span class="small text-muted">Each class builds upon the previous one</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-check text-warning me-2"></i>
                            <span class="small text-muted">Your current class determines your timetable</span>
                        </li>
                        <li>
                            <i class="fas fa-download text-info me-2"></i>
                            <span class="small text-muted">Export your history for records</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Print class history
    document.getElementById('printClassHistory')?.addEventListener('click', function() {
        window.print();
    });

    // Export class history
    document.getElementById('exportClassHistory')?.addEventListener('click', function() {
        // In production, this would call an export endpoint
        // For now, we'll use the print dialog with "Save as PDF"
        if (typeof toastr !== 'undefined') {
            toastr.info('Select "Save as PDF" in the print dialog to export', 'Export');
        }
        window.print();
    });

    // Animate stat cards on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.stat-card').forEach(function(card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
});

// Toastr configuration (if using toastr)
if (typeof toastr !== 'undefined') {
    toastr.options = {
        positionClass: 'toast-top-right',
        progressBar: true,
        closeButton: true,
        timeOut: 3000
    };
}
</script>

@endsection