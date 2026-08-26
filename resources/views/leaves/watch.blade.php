@extends('layouts.master')

@section('title', 'Leave Details')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-light: #e8f0fe;
            --primary-dark: #1557b0;
            --primary-gradient: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            --secondary-color: #4285f4;
            --accent-color: #34a853;
            --warning-color: #fbbc04;
            --danger-color: #ea4335;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --bg-light: #f5f9ff;
            --shadow-color: rgba(26, 115, 232, 0.15);
            --border-color: #d4e4ff;
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 30px var(--shadow-color);
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 6px 40px rgba(26, 115, 232, 0.2);
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1.5rem 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }
        
        .card-header h4, .card-header p {
            position: relative;
            z-index: 1;
        }
        
        .card-header h4 i {
            margin-right: 0.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .detail-section {
            margin-bottom: 2rem;
        }
        
        .detail-section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-light);
            position: relative;
        }
        
        .detail-section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px;
        }
        
        .detail-section-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .detail-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f4ff;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            width: 200px;
            flex-shrink: 0;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .detail-value {
            flex: 1;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .detail-value .badge {
            font-size: 0.8rem;
            padding: 0.4rem 1rem;
        }
        
        .status-badge-approved {
            background: linear-gradient(135deg, #34a853, #2d8f47);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-badge-pending {
            background: linear-gradient(135deg, #fbbc04, #f9a825);
            color: #1a1a1a;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-badge-rejected {
            background: linear-gradient(135deg, #ea4335, #d33426);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-badge-draft {
            background: linear-gradient(135deg, #9aa0a6, #80868b);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-badge-cancelled {
            background: linear-gradient(135deg, #9aa0a6, #80868b);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .signature-box {
            background: var(--bg-light);
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .signature-box:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .signature-box img {
            max-height: 80px;
            max-width: 100%;
        }
        
        .btn-action {
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary-action {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-primary-action:hover {
            box-shadow: 0 4px 20px rgba(26, 115, 232, 0.35);
            color: white;
        }
        
        .btn-success-action {
            background: linear-gradient(135deg, #34a853, #2d8f47);
            color: white;
        }
        
        .btn-success-action:hover {
            box-shadow: 0 4px 20px rgba(52, 168, 83, 0.35);
            color: white;
        }
        
        .btn-danger-action {
            background: linear-gradient(135deg, #ea4335, #d33426);
            color: white;
        }
        
        .btn-danger-action:hover {
            box-shadow: 0 4px 20px rgba(234, 67, 53, 0.35);
            color: white;
        }
        
        .btn-warning-action {
            background: linear-gradient(135deg, #fbbc04, #f9a825);
            color: #1a1a1a;
        }
        
        .btn-warning-action:hover {
            box-shadow: 0 4px 20px rgba(251, 188, 4, 0.35);
            color: #1a1a1a;
        }
        
        .btn-secondary-action {
            background: var(--bg-light);
            color: var(--text-secondary);
            border: 1.5px solid var(--border-color);
        }
        
        .btn-secondary-action:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .info-box {
            background: var(--primary-light);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .info-box i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            padding-bottom: 1.5rem;
            border-left: 2px solid var(--border-color);
        }
        
        .timeline-item:last-child {
            border-left: none;
            padding-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-color);
        }
        
        .timeline-item.completed::before {
            background: var(--accent-color);
        }
        
        .timeline-item .timeline-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .timeline-item .timeline-date {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        
        .timeline-item .timeline-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        
        .official-box {
            background: linear-gradient(135deg, #f8faff 0%, #eef6ff 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .official-box .title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.25rem;
            }
            
            .detail-row {
                flex-direction: column;
                padding: 0.5rem 0;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 0.25rem;
                font-size: 0.8rem;
            }
            
            .card-header {
                padding: 1.25rem;
            }
            
            .card-header h4 {
                font-size: 1.2rem;
            }
            
            .btn-action {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .d-flex.gap-2 {
                flex-direction: column;
            }
            
            .signature-box {
                min-height: 80px;
            }
        }
        
        @media print {
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .btn-action, .btn {
                display: none !important;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="fas fa-file-alt me-2"></i>Leave Application Details
                            </h4>
                            <p class="mb-0 mt-1 opacity-75">Reference: #LEAVE-{{ str_pad($leave->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <span class="status-badge-{{ $leave->status }}">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Status Info -->
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <strong>Status:</strong> 
                            <span class="badge status-badge-{{ $leave->status }} ms-2">
                                {{ ucfirst($leave->status) }}
                            </span>
                            @if($leave->status === 'approved')
                                <span class="ms-3">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Approved on {{ \Carbon\Carbon::parse($leave->approved_at ?? $leave->updated_at)->format('d M, Y') }}
                                </span>
                            @elseif($leave->status === 'rejected')
                                <span class="ms-3">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    Rejected on {{ \Carbon\Carbon::parse($leave->updated_at)->format('d M, Y') }}
                                </span>
                            @elseif($leave->status === 'pending')
                                <span class="ms-3">
                                    <i class="fas fa-clock text-warning"></i>
                                    Awaiting approval
                                </span>
                            @endif
                        </div>

                        <!-- Two Column Layout -->
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-7">
                                <!-- Personal Information -->
                                <div class="detail-section">
                                    <h6 class="detail-section-title">
                                        <i class="fas fa-user-circle"></i>Personal Information
                                    </h6>
                                    <div class="detail-row">
                                        <span class="detail-label">Full Name</span>
                                        <span class="detail-value">{{ $leave->full_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Designation</span>
                                        <span class="detail-value">{{ $leave->designation ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Contact Number</span>
                                        <span class="detail-value">+233 {{ $leave->contact_number ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- Leave Details -->
                                <div class="detail-section">
                                    <h6 class="detail-section-title">
                                        <i class="fas fa-calendar-alt"></i>Leave Details
                                    </h6>
                                    <div class="detail-row">
                                        <span class="detail-label">Leave Type</span>
                                        <span class="detail-value">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $leave->leave_type ?? 'N/A' }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Date of Commencement</span>
                                        <span class="detail-value">
                                            <i class="fas fa-calendar-day me-1 text-primary"></i>
                                            {{ \Carbon\Carbon::parse($leave->date_commencement)->format('d M, Y') }}
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Date of Resumption</span>
                                        <span class="detail-value">
                                            <i class="fas fa-calendar-check me-1 text-success"></i>
                                            {{ \Carbon\Carbon::parse($leave->date_resumption)->format('d M, Y') }}
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Days Applied For</span>
                                        <span class="detail-value">
                                            <strong>{{ $leave->days_applied_for ?? 0 }}</strong> day(s)
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Date of Application</span>
                                        <span class="detail-value">
                                            {{ \Carbon\Carbon::parse($leave->date_of_application)->format('d M, Y') }}
                                        </span>
                                    </div>
                                    @if($leave->reason)
                                    <div class="detail-row">
                                        <span class="detail-label">Reason</span>
                                        <span class="detail-value">{{ $leave->reason }}</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Previous Leave Information -->
                                @if($leave->date_last_leave || $leave->days_entitled || $leave->days_already_utilized)
                                <div class="detail-section">
                                    <h6 class="detail-section-title">
                                        <i class="fas fa-history"></i>Previous Leave Information
                                    </h6>
                                    @if($leave->date_last_leave)
                                    <div class="detail-row">
                                        <span class="detail-label">Date of Last Leave</span>
                                        <span class="detail-value">
                                            {{ \Carbon\Carbon::parse($leave->date_last_leave)->format('d M, Y') }}
                                        </span>
                                    </div>
                                    @endif
                                    @if($leave->days_entitled)
                                    <div class="detail-row">
                                        <span class="detail-label">Days Entitled</span>
                                        <span class="detail-value">{{ $leave->days_entitled }} day(s)</span>
                                    </div>
                                    @endif
                                    @if($leave->days_already_utilized)
                                    <div class="detail-row">
                                        <span class="detail-label">Days Already Utilized</span>
                                        <span class="detail-value">{{ $leave->days_already_utilized }} day(s)</span>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-5">
                                <!-- Signature -->
                                <div class="detail-section">
                                    <h6 class="detail-section-title">
                                        <i class="fas fa-pen"></i>Signature
                                    </h6>
                                    <div class="signature-box">
                                        @if($leave->signature)
                                            <img src="{{ $leave->signature }}" alt="Signature" class="img-fluid">
                                        @else
                                            <div class="text-muted">
                                                <i class="fas fa-pen fa-2x mb-2 d-block"></i>
                                                <span>No signature provided</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-clock me-1"></i>
                                        Signed on {{ \Carbon\Carbon::parse($leave->created_at)->format('d M, Y h:i A') }}
                                    </div>
                                </div>

                                <!-- Official Use -->
                                @if($leave->status !== 'draft')
                                <div class="detail-section">
                                    <h6 class="detail-section-title">
                                        <i class="fas fa-lock"></i>Official Use Only
                                    </h6>
                                    <div class="official-box">
                                        <div class="mb-2">
                                            <span class="text-muted small">Recommendation</span>
                                            <div class="fw-bold">
                                                @if($leave->recommendation)
                                                    <span class="badge bg-success">Recommended</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Recommended</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted small">Days Granted</span>
                                            <div class="fw-bold">{{ $leave->days_granted ?? 'N/A' }} day(s)</div>
                                        </div>
                                        @if($leave->administrator_name)
                                        <div class="mb-2">
                                            <span class="text-muted small">Administrator</span>
                                            <div class="fw-bold">{{ $leave->administrator_name }}</div>
                                        </div>
                                        @endif
                                        @if($leave->zonal_coordinator_name)
                                        <div>
                                            <span class="text-muted small">Zonal Coordinator</span>
                                            <div class="fw-bold">{{ $leave->zonal_coordinator_name }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Timeline -->
                                <div class="detail-section">
                                    <h6 class="detail-section-title">
                                        <i class="fas fa-clock"></i>Timeline
                                    </h6>
                                    <div class="timeline-item">
                                        <div class="timeline-title">Application Submitted</div>
                                        <div class="timeline-date">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            {{ \Carbon\Carbon::parse($leave->created_at)->format('d M, Y h:i A') }}
                                        </div>
                                    </div>
                                    
                                    @if($leave->status === 'approved' || $leave->status === 'rejected')
                                    <div class="timeline-item completed">
                                        <div class="timeline-title">
                                            @if($leave->status === 'approved')
                                                <i class="fas fa-check-circle text-success me-1"></i>Approved
                                            @else
                                                <i class="fas fa-times-circle text-danger me-1"></i>Rejected
                                                <h4 class="mt-2">Reason: {{ $leave->recommendation}} </h4> 
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            {{ \Carbon\Carbon::parse($leave->updated_at)->format('d M, Y h:i A') }}
                                        </div>
                                        <div>
                                            
                                        </div>
                                        @if($leave->approval_comment)
                                        <div class="timeline-description">
                                            <i class="fas fa-comment me-1"></i>
                                            {{ $leave->approval_comment }}
                                        </div>
                                        @endif
                                    </div>
                                    @elseif($leave->status === 'pending')
                                    <div class="timeline-item">
                                        <div class="timeline-title">
                                            <i class="fas fa-clock text-warning me-1"></i>Pending Approval
                                        </div>
                                        <div class="timeline-date">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            Awaiting review by administrator
                                        </div>
                                    </div>
                                    @endif
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 pt-3 border-top no-print">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('leaves.index') }}" class="btn btn-secondary-action btn-action">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                
                                @if($leave->status === 'draft' || $leave->status === 'pending')
                                    <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-warning-action btn-action">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                @endif

                                @if($leave->status === 'pending' && auth()->user()->hasRole('admin'))
                                    <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success-action btn-action" onclick="return confirm('Approve this leave application?')">
                                            <i class="fas fa-check me-2"></i>Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('leaves.reject', $leave->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger-action btn-action" onclick="return confirm('Reject this leave application?')">
                                            <i class="fas fa-times me-2"></i>Reject
                                        </button>
                                    </form>
                                @endif

                                @if($leave->status === 'draft')
                                    <form
                                        action="{{ url('/leaves/' . $leave->id . '/submit') }}"
                                        method="POST"
                                        class="d-inline"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-primary-action btn-action"
                                            onclick="return confirm('Submit this leave application for approval?')"
                                        >
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Submit for Approval
                                        </button>
                                    </form>
                                @endif

                                <button onclick="window.print()" class="btn btn-secondary-action btn-action">
                                    <i class="fas fa-print me-2"></i>Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
@endsection