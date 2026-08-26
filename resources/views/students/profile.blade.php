@extends('students.layouts.app')

@section('title', 'My Profile - EduNexus')

@section('content')
<style>
    /* ================= BLUE LIGHT PALETTE ================= */
    :root {
        --blue-50: #eff6ff;
        --blue-100: #dbeafe;
        --blue-200: #bfdbfe;
        --blue-300: #93c5fd;
        --blue-400: #60a5fa;
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        --blue-700: #1d4ed8;
        --blue-800: #1e40af;
        --blue-900: #1e3a8a;
    }

    /* ================= HEADER ================= */
    .profile-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 40%, #3b82f6 70%, #60a5fa 100%);
        color: white;
        border-radius: 16px;
        padding: 25px 30px;
        margin-bottom: 25px;
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.25);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .profile-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .profile-header h2 {
        font-weight: 700;
        font-size: 1.6rem;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
        position: relative;
        z-index: 1;
    }

    .profile-header h2 i {
        color: #93c5fd;
        margin-right: 12px;
    }

    .profile-header .sub-info {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.85rem;
        position: relative;
        z-index: 1;
    }

    .profile-header .sub-info i {
        margin-right: 6px;
        color: #93c5fd;
    }

    .profile-header .header-badge {
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .profile-header .header-badge i {
        color: #93c5fd;
    }

    /* ================= PROFILE CARD ================= */
    .profile-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #dbeafe;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(59, 130, 246, 0.08);
    }

    .profile-card .card-body {
        padding: 25px 30px;
    }

    /* ================= PHOTO SECTION ================= */
    .photo-section {
        text-align: center;
        padding: 10px 0;
    }

    .photo-wrapper {
        position: relative;
        display: inline-block;
    }

    .photo-wrapper .profile-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #3b82f6;
        box-shadow: 0 8px 30px rgba(59, 130, 246, 0.25);
        transition: all 0.3s ease;
    }

    .photo-wrapper .profile-photo:hover {
        transform: scale(1.03);
        box-shadow: 0 12px 40px rgba(59, 130, 246, 0.35);
    }

    .photo-wrapper .photo-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #3b82f6;
        box-shadow: 0 8px 30px rgba(59, 130, 246, 0.15);
    }

    .photo-wrapper .photo-placeholder i {
        font-size: 3.5rem;
        color: #60a5fa;
    }

    .photo-wrapper .status-dot {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .photo-wrapper .status-dot.active {
        background: #22c55e;
    }

    .photo-wrapper .status-dot.inactive {
        background: #ef4444;
    }

    .photo-wrapper .status-dot.graduated {
        background: #8b5cf6;
    }

    .student-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 12px;
        margin-bottom: 2px;
    }

    .student-id {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .student-id i {
        margin-right: 6px;
        color: #3b82f6;
    }

    /* ================= STATUS BADGES ================= */
    .status-badge-main {
        display: inline-block;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 6px;
    }

    .status-badge-main.active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }

    .status-badge-main.inactive {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .status-badge-main.graduated {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }

    /* ================= CLASS STATUS CARD ================= */
    .class-status-card {
        border-radius: 12px;
        padding: 12px 16px;
        margin-top: 12px;
        border: 1px solid #dbeafe;
        transition: all 0.3s ease;
        text-align: left;
        background: #eff6ff;
    }

    .class-status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .class-status-card.current {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-color: #3b82f6;
    }

    .class-status-card.graduated {
        background: linear-gradient(135deg, #f5f3ff, #ede9fe);
        border-color: #8b5cf6;
    }

    .class-status-card.no-class {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border-color: #eab308;
    }

    .class-status-card .status-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .class-status-card .status-icon.current {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .class-status-card .status-icon.graduated {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }

    .class-status-card .status-icon.no-class {
        background: rgba(234, 179, 8, 0.15);
        color: #eab308;
    }

    .class-status-card .status-content .status-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .class-status-card .status-content .status-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
    }

    .class-status-card .status-content .status-detail {
        font-size: 0.7rem;
        color: #94a3b8;
        margin-top: 1px;
    }

    /* ================= INFO SECTIONS ================= */
    .info-section {
        margin-bottom: 20px;
    }

    .info-section:last-child {
        margin-bottom: 0;
    }

    .info-section .section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #eff6ff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-section .section-title i {
        color: #3b82f6;
        width: 20px;
        text-align: center;
        font-size: 0.9rem;
    }

    .info-section .section-title .badge {
        font-size: 0.55rem;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        margin-left: auto;
    }

    .badge.bg-primary-custom {
        background: #3b82f6;
        color: white;
    }

    .badge.bg-success-custom {
        background: #22c55e;
        color: white;
    }

    .badge.bg-info-custom {
        background: #0ea5e9;
        color: white;
    }

    .badge.bg-warning-custom {
        background: #eab308;
        color: white;
    }

    .badge.bg-secondary-custom {
        background: #94a3b8;
        color: white;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 6px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        padding: 5px 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .info-item:hover {
        background: #eff6ff;
    }

    .info-item .label {
        font-size: 0.6rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item .value {
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e293b;
        margin-top: 1px;
    }

    .info-item .value .text-muted {
        color: #94a3b8;
        font-weight: 400;
    }

    .info-item .value .text-success {
        color: #22c55e;
    }

    .info-item .value .text-warning {
        color: #eab308;
    }

    .info-item .value .text-primary {
        color: #3b82f6;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 992px) {
        .profile-card .card-body {
            padding: 20px;
        }

        .photo-wrapper .profile-photo,
        .photo-wrapper .photo-placeholder {
            width: 130px;
            height: 130px;
        }

        .photo-wrapper .photo-placeholder i {
            font-size: 3rem;
        }
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 18px 20px;
        }

        .profile-header h2 {
            font-size: 1.3rem;
        }

        .profile-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 8px;
        }

        .profile-card .card-body {
            padding: 16px;
        }

        .photo-wrapper .profile-photo,
        .photo-wrapper .photo-placeholder {
            width: 110px;
            height: 110px;
        }

        .photo-wrapper .photo-placeholder i {
            font-size: 2.5rem;
        }

        .student-name {
            font-size: 1rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .info-item {
            padding: 4px 8px;
        }

        .class-status-card {
            padding: 10px 12px;
        }

        .col-md-3.text-center {
            margin-bottom: 16px;
        }
    }

    @media (max-width: 576px) {
        .photo-wrapper .profile-photo,
        .photo-wrapper .photo-placeholder {
            width: 90px;
            height: 90px;
            border-width: 3px;
        }

        .photo-wrapper .status-dot {
            width: 14px;
            height: 14px;
            border-width: 2px;
            bottom: 4px;
            right: 4px;
        }

        .status-badge-main {
            font-size: 0.65rem;
            padding: 3px 12px;
        }

        .info-section .section-title {
            font-size: 0.75rem;
        }
    }

    /* ================= PRINT ================= */
    @media print {
        .profile-header {
            background: #1e3a8a !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .profile-card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        .photo-wrapper .profile-photo {
            border-color: #3b82f6 !important;
        }

        .class-status-card {
            border: 1px solid #ddd !important;
        }

        .info-item:hover {
            background: transparent !important;
        }

        .btn-edunexus,
        .btn-outline-edunexus,
        .nav-item-custom {
            display: none !important;
        }
    }
</style>

<div class="container-fluid">
    <!-- ================= HEADER ================= -->
    <div class="profile-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2><i class="fas fa-user-circle"></i> My Profile</h2>
                <div class="sub-info">
                    <i class="fas fa-user-graduate"></i>
                    <span>{{ $student->full_name }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-id-card"></i>
                    <span>{{ $student->student_id }}</span>
                </div>
            </div>
            <div class="header-badge mt-2 mt-sm-0">
                <i class="fas fa-clock"></i>
                <span>{{ now()->format('l, F d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- ================= PROFILE CARD ================= -->
    <div class="profile-card">
        <div class="card-body">
            <div class="row g-4">
                <!-- ================= LEFT COLUMN - PHOTO & STATUS ================= -->
                <div class="col-lg-3 col-md-4">
                    <div class="photo-section">
                        <div class="photo-wrapper">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}"
                                    class="profile-photo"
                                    alt="{{ $student->full_name }}">
                            @else
                                <div class="photo-placeholder">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            @endif

                            @php
                                $hasGraduated = $student->classAssignments
                                    ->where('status', 'graduated')
                                    ->where('is_current', false)
                                    ->isNotEmpty();
                                
                                $currentAssignment = $student->classAssignments
                                    ->where('is_current', true)
                                    ->first();
                            @endphp

                            <span class="status-dot {{ $hasGraduated ? 'graduated' : ($student->is_active ? 'active' : 'inactive') }}"></span>
                        </div>

                        <div class="student-name">{{ $student->full_name }}</div>
                        <div class="student-id">
                            <i class="fas fa-id-badge"></i> {{ $student->student_id }}
                        </div>

                        @if($hasGraduated)
                            <span class="status-badge-main graduated">
                                <i class="fas fa-graduation-cap me-1"></i> Graduated
                            </span>
                        @else
                            <span class="status-badge-main {{ $student->is_active ? 'active' : 'inactive' }}">
                                <i class="fas fa-circle me-1" style="font-size: 0.4rem;"></i>
                                {{ $student->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @endif

                        <!-- ================= CLASS STATUS ================= -->
                        @if($hasGraduated)
                            @php
                                $graduatedAssignment = $student->classAssignments
                                    ->where('status', 'graduated')
                                    ->last();
                            @endphp
                            <div class="class-status-card graduated">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="status-icon graduated">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="status-content text-start">
                                        <div class="status-label">Status</div>
                                        <div class="status-value">
                                            <span class="text-success">
                                                <i class="fas fa-check-circle me-1"></i> Graduated
                                            </span>
                                        </div>
                                        <div class="status-detail">
                                            From: <strong>{{ $graduatedAssignment->studentClass->name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="status-detail">
                                            Graduated: <strong>{{ $graduatedAssignment->updated_at ? \Carbon\Carbon::parse($graduatedAssignment->updated_at)->format('d M Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($currentAssignment && $currentAssignment->studentClass)
                            <div class="class-status-card current">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="status-icon current">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="status-content text-start">
                                        <div class="status-label">Current Class</div>
                                        <div class="status-value">
                                            <span class="text-primary">{{ $currentAssignment->studentClass->name }}</span>
                                        </div>
                                        <div class="status-detail">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Since: <strong>{{ $currentAssignment->created_at ? \Carbon\Carbon::parse($currentAssignment->created_at)->format('d M Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="class-status-card no-class">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="status-icon no-class">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="status-content text-start">
                                        <div class="status-label">Status</div>
                                        <div class="status-value">
                                            <span class="text-warning">No Class Assigned</span>
                                        </div>
                                        <div class="status-detail">
                                            Please contact administration
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ================= RIGHT COLUMN - DETAILS ================= -->
                <div class="col-lg-9 col-md-8">
                    <!-- ================= PERSONAL INFORMATION ================= -->
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i> Personal Information
                            <span class="badge bg-primary-custom">Profile</span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">First Name</span>
                                <span class="value">{{ $student->first_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Middle Name</span>
                                <span class="value">{{ $student->middle_name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Last Name</span>
                                <span class="value">{{ $student->last_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Gender</span>
                                <span class="value">{{ $student->gender }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Date of Birth</span>
                                <span class="value">
                                    {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '-' }}
                                    @if($student->date_of_birth)
                                        <span class="text-muted">
                                            ({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} yrs)
                                        </span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="label">Nationality</span>
                                <span class="value">{{ $student->nationality ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Religion</span>
                                <span class="value">{{ $student->religion ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Address</span>
                                <span class="value">{{ $student->address ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ================= DISABILITY INFORMATION ================= -->
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-wheelchair"></i> Disability Information
                            <span class="badge bg-secondary-custom">Optional</span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Has Disability</span>
                                <span class="value">
                                    @if($student->has_disability)
                                        <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Yes</span>
                                    @else
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> No</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="label">Disability Type</span>
                                <span class="value">{{ $student->disability_type ?? 'None' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ================= FATHER INFORMATION ================= -->
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-male"></i> Father Information
                            <span class="badge bg-info-custom">Parent</span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Name</span>
                                <span class="value">{{ $student->father_name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Phone</span>
                                <span class="value">{{ $student->father_phone ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Email</span>
                                <span class="value">{{ $student->father_email ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Occupation</span>
                                <span class="value">{{ $student->father_occupation ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ================= MOTHER INFORMATION ================= -->
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-female"></i> Mother Information
                            <span class="badge bg-info-custom">Parent</span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Name</span>
                                <span class="value">{{ $student->mother_name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Phone</span>
                                <span class="value">{{ $student->mother_phone ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Email</span>
                                <span class="value">{{ $student->mother_email ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Occupation</span>
                                <span class="value">{{ $student->mother_occupation ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ================= GUARDIAN INFORMATION ================= -->
                    @if($student->guardian_name || $student->guardian_phone || $student->guardian_email)
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-user-shield"></i> Guardian Information
                            <span class="badge bg-warning-custom">Emergency</span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Name</span>
                                <span class="value">{{ $student->guardian_name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Phone</span>
                                <span class="value">{{ $student->guardian_phone ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Email</span>
                                <span class="value">{{ $student->guardian_email ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Relationship</span>
                                <span class="value">{{ $student->guardian_relationship ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- ================= SCHOOL INFORMATION ================= -->
                    <div class="info-section">
                        <div class="section-title">
                            <i class="fas fa-school"></i> School Information
                            <span class="badge bg-primary-custom">Academic</span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Student ID</span>
                                <span class="value"><strong class="text-primary">{{ $student->student_id }}</strong></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Admission Date</span>
                                <span class="value">
                                    {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : now()->format('d M Y') }}
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="label">Current Class</span>
                                <span class="value">
                                    @if($currentAssignment && $currentAssignment->studentClass)
                                        <span class="text-primary">{{ $currentAssignment->studentClass->name }}</span>
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="label">Academic Year</span>
                                <span class="value">
                                    @if($currentAssignment && $currentAssignment->academicYear)
                                        {{ $currentAssignment->academicYear->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </span>
                            </div>
                            @if($hasGraduated)
                                @php
                                    $graduatedAssignment = $student->classAssignments
                                        ->where('status', 'graduated')
                                        ->last();
                                @endphp
                                <div class="info-item">
                                    <span class="label">Graduated From</span>
                                    <span class="value">{{ $graduatedAssignment->studentClass->name ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Graduation Date</span>
                                    <span class="value">{{ $graduatedAssignment->updated_at ? \Carbon\Carbon::parse($graduatedAssignment->updated_at)->format('d M Y') : 'N/A' }}</span>
                                </div>
                            @endif
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
    /* ================= ADDITIONAL BLUE LIGHT STYLES ================= */
    .nav-item-custom {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: #eff6ff;
        border-radius: 8px;
        text-decoration: none;
        color: #1e293b;
        margin-top: 10px;
        transition: all 0.3s ease;
        width: 100%;
        border: 1px solid #dbeafe;
    }
    .nav-item-custom:hover {
        background: #dbeafe;
        color: #2563eb;
        border-color: #3b82f6;
    }
    .nav-item-custom button {
        margin-left: auto;
        background: #3b82f6;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .nav-item-custom button:hover {
        background: #2563eb;
    }
    
    /* Badge Styles */
    .badge.bg-primary-custom {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: white !important;
    }
    
    .badge.bg-success-custom {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
        color: white !important;
    }
    
    .badge.bg-info-custom {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
        color: white !important;
    }
    
    .badge.bg-warning-custom {
        background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important;
        color: white !important;
    }
    
    .badge.bg-secondary-custom {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%) !important;
        color: white !important;
    }

    .badge.bg-danger-custom {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: white !important;
    }
    
    /* Card Styles */
    .bg-primary.bg-opacity-10 {
        background-color: rgba(59, 130, 246, 0.08) !important;
    }
    
    .bg-success.bg-opacity-10 {
        background-color: rgba(34, 197, 94, 0.08) !important;
    }
    
    .bg-warning.bg-opacity-10 {
        background-color: rgba(234, 179, 8, 0.08) !important;
    }
    
    /* Summary Cards */
    .bg-white.rounded.shadow-sm {
        transition: all 0.3s ease;
    }
    .bg-white.rounded.shadow-sm:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(59, 130, 246, 0.15) !important;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
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
            background: #1e3a8a !important;
            color: white !important;
        }
        .table thead th {
            background: #f0f0f0 !important;
        }
    }
</style>
@endpush