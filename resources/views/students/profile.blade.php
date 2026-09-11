@extends('students.layouts.app')

@section('title', 'My Profile - EduNexus')

@section('content')

<style>
    :root {
        --edx-primary: #2563eb;
        --edx-primary-dark: #1d4ed8;
        --edx-primary-soft: #eff6ff;
        --edx-border: #e5e7eb;
        --edx-text: #172033;
        --edx-muted: #64748b;
        --edx-success: #16a34a;
        --edx-warning: #d97706;
        --edx-danger: #dc2626;
        --edx-purple: #7c3aed;
        --edx-bg: #f8fafc;
    }

    .profile-page {
        padding: 4px 0 30px;
    }

    /* =========================================================
       TOP HEADER
    ========================================================= */

    .profile-top {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 30px;
        margin-bottom: 22px;
        background:
            radial-gradient(circle at 90% 15%, rgba(255,255,255,.18), transparent 28%),
            radial-gradient(circle at 10% 100%, rgba(255,255,255,.10), transparent 30%),
            linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #3b82f6 100%);
        color: #fff;
        box-shadow: 0 12px 35px rgba(37, 99, 235, .18);
    }

    .profile-top::before,
    .profile-top::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
        pointer-events: none;
    }

    .profile-top::before {
        width: 240px;
        height: 240px;
        right: -90px;
        top: -120px;
    }

    .profile-top::after {
        width: 180px;
        height: 180px;
        left: 25%;
        bottom: -130px;
    }

    .profile-top-content {
        position: relative;
        z-index: 2;
    }

    .profile-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: rgba(255,255,255,.72);
        margin-bottom: 12px;
    }

    .profile-breadcrumb i {
        font-size: 10px;
    }

    .profile-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .profile-heading h1 {
        margin: 0;
        font-size: 26px;
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: -.4px;
    }

    .profile-heading p {
        margin: 7px 0 0;
        font-size: 13px;
        color: rgba(255,255,255,.78);
    }

    .profile-date {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 10px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.13);
        font-size: 12px;
        white-space: nowrap;
        backdrop-filter: blur(8px);
    }

    /* =========================================================
       MAIN PROFILE CARD
    ========================================================= */

    .profile-main-card {
        background: #fff;
        border: 1px solid var(--edx-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 5px 22px rgba(15, 23, 42, .05);
    }

    /* =========================================================
       IDENTITY PANEL
    ========================================================= */

    .identity-panel {
        position: relative;
        padding: 30px 25px;
        text-align: center;
        border-right: 1px solid var(--edx-border);
        height: 100%;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .avatar-container {
        position: relative;
        display: inline-block;
        margin-bottom: 13px;
    }

    .student-avatar,
    .student-avatar-placeholder {
        width: 138px;
        height: 138px;
        border-radius: 50%;
        display: block;
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow:
            0 0 0 2px #bfdbfe,
            0 12px 28px rgba(37,99,235,.16);
    }

    .student-avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        color: #3b82f6;
        font-size: 48px;
    }

    .avatar-status {
        position: absolute;
        width: 19px;
        height: 19px;
        right: 7px;
        bottom: 8px;
        border: 4px solid #fff;
        border-radius: 50%;
        box-shadow: 0 2px 7px rgba(0,0,0,.16);
    }

    .avatar-status.active {
        background: #22c55e;
    }

    .avatar-status.inactive {
        background: #ef4444;
    }

    .avatar-status.graduated {
        background: #8b5cf6;
    }

    .student-full-name {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        color: var(--edx-text);
    }

    .student-number {
        margin-top: 5px;
        font-size: 12px;
        color: var(--edx-muted);
    }

    .student-number i {
        color: var(--edx-primary);
        margin-right: 4px;
    }

    /* =========================================================
       STATUS
    ========================================================= */

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 11px;
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-pill.active {
        color: #15803d;
        background: #dcfce7;
    }

    .status-pill.inactive {
        color: #b91c1c;
        background: #fee2e2;
    }

    .status-pill.graduated {
        color: #6d28d9;
        background: #ede9fe;
    }

    /* =========================================================
       CLASS CARD
    ========================================================= */

    .class-card {
        margin-top: 22px;
        padding: 15px;
        text-align: left;
        border: 1px solid #dbeafe;
        border-radius: 13px;
        background: #eff6ff;
    }

    .class-card.graduated {
        border-color: #ddd6fe;
        background: #f5f3ff;
    }

    .class-card.warning {
        border-color: #fde68a;
        background: #fffbeb;
    }

    .class-card-top {
        display: flex;
        align-items: flex-start;
        gap: 11px;
    }

    .class-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #dbeafe;
        color: var(--edx-primary);
    }

    .class-card.graduated .class-icon {
        background: #ede9fe;
        color: var(--edx-purple);
    }

    .class-card.warning .class-icon {
        background: #fef3c7;
        color: var(--edx-warning);
    }

    .class-label {
        color: var(--edx-muted);
        font-size: 9px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: .7px;
        margin-bottom: 3px;
    }

    .class-name {
        color: var(--edx-text);
        font-size: 14px;
        font-weight: 700;
    }

    .class-detail {
        margin-top: 5px;
        color: var(--edx-muted);
        font-size: 10px;
        line-height: 1.5;
    }

    /* =========================================================
       DETAILS PANEL
    ========================================================= */

    .details-panel {
        padding: 30px;
    }

    .section-block {
        margin-bottom: 28px;
    }

    .section-block:last-child {
        margin-bottom: 0;
    }

    .section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 11px;
        margin-bottom: 10px;
        border-bottom: 1px solid #eef2f7;
    }

    .section-heading-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-heading-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: var(--edx-primary-soft);
        color: var(--edx-primary);
        font-size: 13px;
    }

    .section-heading h3 {
        margin: 0;
        color: var(--edx-text);
        font-size: 14px;
        font-weight: 700;
    }

    .section-heading p {
        margin: 2px 0 0;
        color: #94a3b8;
        font-size: 10px;
    }

    .section-tag {
        padding: 4px 9px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 700;
        color: var(--edx-primary);
        background: var(--edx-primary-soft);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 2px 8px;
    }

    .info-item {
        padding: 10px 12px;
        border-radius: 9px;
        transition: background .2s ease;
    }

    .info-item:hover {
        background: #f8fafc;
    }

    .info-label {
        display: block;
        margin-bottom: 3px;
        color: #94a3b8;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .info-value {
        display: block;
        color: var(--edx-text);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.45;
        word-break: break-word;
    }

    .empty-value {
        color: #a8b2c1;
        font-weight: 400;
    }

    .value-primary {
        color: var(--edx-primary);
    }

    .value-success {
        color: var(--edx-success);
    }

    .value-warning {
        color: var(--edx-warning);
    }

    /* =========================================================
       PARENT / GUARDIAN
    ========================================================= */

    .contact-card {
        border: 1px solid #eef2f7;
        border-radius: 12px;
        overflow: hidden;
    }

    .contact-card-header {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 11px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }

    .contact-card-header i {
        color: var(--edx-primary);
        font-size: 12px;
    }

    .contact-card-header span {
        font-size: 11px;
        font-weight: 700;
        color: var(--edx-text);
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .contact-grid .info-item {
        border-bottom: 1px solid #f1f5f9;
    }

    /* =========================================================
       SPECIAL ACADEMIC STATUS
    ========================================================= */

    .academic-highlight {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .academic-mini-card {
        padding: 13px 14px;
        border-radius: 11px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
    }

    .academic-mini-card .mini-label {
        color: #94a3b8;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
        margin-bottom: 4px;
    }

    .academic-mini-card .mini-value {
        color: var(--edx-text);
        font-size: 12px;
        font-weight: 700;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 991px) {
        .identity-panel {
            border-right: 0;
            border-bottom: 1px solid var(--edx-border);
        }

        .profile-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .profile-date {
            align-self: flex-start;
        }
    }

    @media (max-width: 767px) {
        .profile-page {
            padding: 0 0 20px;
        }

        .profile-top {
            padding: 21px 18px;
            border-radius: 15px;
        }

        .profile-heading h1 {
            font-size: 21px;
        }

        .profile-heading p {
            font-size: 11px;
        }

        .profile-date {
            font-size: 10px;
        }

        .identity-panel {
            padding: 25px 18px;
        }

        .details-panel {
            padding: 22px 17px;
        }

        .student-avatar,
        .student-avatar-placeholder {
            width: 118px;
            height: 118px;
        }

        .student-avatar-placeholder {
            font-size: 40px;
        }

        .info-grid,
        .contact-grid,
        .academic-highlight {
            grid-template-columns: 1fr;
        }

        .section-heading {
            align-items: flex-start;
        }

        .section-tag {
            display: none;
        }
    }

    /* =========================================================
       PRINT
    ========================================================= */

    @media print {
        body {
            background: #fff !important;
        }

        .profile-top {
            background: #1e3a8a !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            box-shadow: none !important;
        }

        .profile-main-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .info-item:hover {
            background: transparent !important;
        }
    }
</style>


@php
    /*
    |--------------------------------------------------------------------------
    | Student Status / Assignment Data
    |--------------------------------------------------------------------------
    */

    $currentAssignment = $student->classAssignments
        ->where('is_current', true)
        ->first();

    $graduatedAssignment = $student->classAssignments
        ->where('status', 'graduated')
        ->where('is_current', false)
        ->sortByDesc('updated_at')
        ->first();

    $hasGraduated = !is_null($graduatedAssignment);

    $studentStatus = $hasGraduated
        ? 'graduated'
        : ($student->is_active ? 'active' : 'inactive');

    $statusLabel = $hasGraduated
        ? 'Graduated'
        : ($student->is_active ? 'Active Student' : 'Inactive');

    $statusIcon = $hasGraduated
        ? 'fa-graduation-cap'
        : ($student->is_active ? 'fa-check' : 'fa-pause');

    $formatDate = function ($date, $fallback = '-') {
        return $date
            ? \Carbon\Carbon::parse($date)->format('d M Y')
            : $fallback;
    };
@endphp


<div class="container-fluid profile-page">

    {{-- ======================================================
         PAGE HEADER
    ======================================================= --}}
    <div class="profile-top">

        <div class="profile-top-content">

            <div class="profile-breadcrumb">
                <span>Student Portal</span>
                <i class="fas fa-chevron-right"></i>
                <span>My Profile</span>
            </div>

            <div class="profile-heading">

                <div>
                    <h1>
                        <i class="fas fa-user-circle me-2"></i>
                        My Profile
                    </h1>

                    <p>
                        View your personal, contact and academic information.
                    </p>
                </div>

                <div class="profile-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ now()->format('l, d M Y') }}
                </div>

            </div>

        </div>

    </div>


    {{-- ======================================================
         MAIN PROFILE
    ======================================================= --}}
    <div class="profile-main-card">

        <div class="row g-0">

            {{-- ==================================================
                 LEFT — IDENTITY
            =================================================== --}}
            <div class="col-lg-3 col-md-4">

                <div class="identity-panel">

                    <div class="avatar-container">

                        @if($student->photo)

                            <img
                                src="{{ asset('storage/' . $student->photo) }}"
                                class="student-avatar"
                                alt="{{ $student->full_name }}"
                            >

                        @else

                            <div class="student-avatar-placeholder">
                                <i class="fas fa-user-graduate"></i>
                            </div>

                        @endif

                        <span class="avatar-status {{ $studentStatus }}"></span>

                    </div>


                    <h2 class="student-full-name">
                        {{ $student->full_name }}
                    </h2>

                    <div class="student-number">
                        <i class="fas fa-id-card"></i>
                        {{ $student->student_id }}
                    </div>


                    <div class="status-pill {{ $studentStatus }}">
                        <i class="fas {{ $statusIcon }}"></i>
                        {{ $statusLabel }}
                    </div>


                    {{-- CURRENT CLASS --}}
                    @if($currentAssignment && $currentAssignment->studentClass)

                        <div class="class-card">

                            <div class="class-card-top">

                                <div class="class-icon">
                                    <i class="fas fa-school"></i>
                                </div>

                                <div>

                                    <div class="class-label">
                                        Current Class
                                    </div>

                                    <div class="class-name">
                                        {{ $currentAssignment->studentClass->name }}
                                    </div>

                                    <div class="class-detail">
                                        <i class="fas fa-calendar-check me-1"></i>

                                        Since
                                        {{ $formatDate($currentAssignment->created_at) }}
                                    </div>

                                </div>

                            </div>

                        </div>


                    {{-- GRADUATED --}}
                    @elseif($hasGraduated)

                        <div class="class-card graduated">

                            <div class="class-card-top">

                                <div class="class-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>

                                <div>

                                    <div class="class-label">
                                        Academic Status
                                    </div>

                                    <div class="class-name">
                                        Graduated
                                    </div>

                                    <div class="class-detail">
                                        From:
                                        <strong>
                                            {{ $graduatedAssignment->studentClass->name ?? 'N/A' }}
                                        </strong>
                                    </div>

                                    <div class="class-detail">
                                        <i class="fas fa-calendar me-1"></i>

                                        {{ $formatDate($graduatedAssignment->updated_at) }}
                                    </div>

                                </div>

                            </div>

                        </div>


                    {{-- NO CLASS --}}
                    @else

                        <div class="class-card warning">

                            <div class="class-card-top">

                                <div class="class-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>

                                <div>

                                    <div class="class-label">
                                        Academic Status
                                    </div>

                                    <div class="class-name">
                                        No Class Assigned
                                    </div>

                                    <div class="class-detail">
                                        Please contact the school administration.
                                    </div>

                                </div>

                            </div>

                        </div>

                    @endif

                </div>

            </div>


            {{-- ==================================================
                 RIGHT — INFORMATION
            =================================================== --}}
            <div class="col-lg-9 col-md-8">

                <div class="details-panel">


                    {{-- ==================================================
                         PERSONAL INFORMATION
                    =================================================== --}}
                    <div class="section-block">

                        <div class="section-heading">

                            <div class="section-heading-left">

                                <div class="section-heading-icon">
                                    <i class="fas fa-user"></i>
                                </div>

                                <div>
                                    <h3>Personal Information</h3>
                                    <p>Your basic personal details</p>
                                </div>

                            </div>

                            <span class="section-tag">
                                PROFILE
                            </span>

                        </div>


                        <div class="info-grid">

                            <div class="info-item">
                                <span class="info-label">First Name</span>
                                <span class="info-value">
                                    {{ $student->first_name ?: '-' }}
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Middle Name</span>
                                <span class="info-value {{ !$student->middle_name ? 'empty-value' : '' }}">
                                    {{ $student->middle_name ?: 'Not provided' }}
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Last Name</span>
                                <span class="info-value">
                                    {{ $student->last_name ?: '-' }}
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Gender</span>
                                <span class="info-value">
                                    {{ $student->gender ?: 'Not provided' }}
                                </span>
                            </div>

                            <div class="info-item">

                                <span class="info-label">
                                    Date of Birth
                                </span>

                                <span class="info-value">

                                    @if($student->date_of_birth)

                                        {{ $formatDate($student->date_of_birth) }}

                                        <span class="empty-value">
                                            ({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years)
                                        </span>

                                    @else

                                        Not provided

                                    @endif

                                </span>

                            </div>

                            <div class="info-item">
                                <span class="info-label">Nationality</span>
                                <span class="info-value">
                                    {{ $student->nationality ?: 'Not provided' }}
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Religion</span>
                                <span class="info-value">
                                    {{ $student->religion ?: 'Not provided' }}
                                </span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Residential Address</span>
                                <span class="info-value">
                                    {{ $student->address ?: 'Not provided' }}
                                </span>
                            </div>

                        </div>

                    </div>


                    {{-- ==================================================
                         ACADEMIC INFORMATION
                    =================================================== --}}
                    <div class="section-block">

                        <div class="section-heading">

                            <div class="section-heading-left">

                                <div class="section-heading-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>

                                <div>
                                    <h3>Academic Information</h3>
                                    <p>Your school and enrollment details</p>
                                </div>

                            </div>

                            <span class="section-tag">
                                ACADEMIC
                            </span>

                        </div>


                        <div class="academic-highlight">

                            <div class="academic-mini-card">

                                <div class="mini-label">
                                    Student ID
                                </div>

                                <div class="mini-value value-primary">
                                    {{ $student->student_id }}
                                </div>

                            </div>


                            <div class="academic-mini-card">

                                <div class="mini-label">
                                    Admission Date
                                </div>

                                <div class="mini-value">
                                    {{ $formatDate($student->admission_date) }}
                                </div>

                            </div>


                            <div class="academic-mini-card">

                                <div class="mini-label">
                                    Current Class
                                </div>

                                <div class="mini-value">

                                    @if($currentAssignment && $currentAssignment->studentClass)

                                        <span class="value-primary">
                                            {{ $currentAssignment->studentClass->name }}
                                        </span>

                                    @else

                                        <span class="empty-value">
                                            Not Assigned
                                        </span>

                                    @endif

                                </div>

                            </div>


                            <div class="academic-mini-card">

                                <div class="mini-label">
                                    Academic Year
                                </div>

                                <div class="mini-value">

                                    @if($currentAssignment && $currentAssignment->academicYear)

                                        {{ $currentAssignment->academicYear->name }}

                                    @else

                                        <span class="empty-value">
                                            Not Available
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        @if($hasGraduated)

                            <div class="info-grid mt-2">

                                <div class="info-item">

                                    <span class="info-label">
                                        Graduated From
                                    </span>

                                    <span class="info-value">
                                        {{ $graduatedAssignment->studentClass->name ?? 'N/A' }}
                                    </span>

                                </div>

                                <div class="info-item">

                                    <span class="info-label">
                                        Graduation Date
                                    </span>

                                    <span class="info-value value-success">
                                        {{ $formatDate($graduatedAssignment->updated_at) }}
                                    </span>

                                </div>

                            </div>

                        @endif

                    </div>


                    {{-- ==================================================
                         DISABILITY INFORMATION
                    =================================================== --}}
                    <div class="section-block">

                        <div class="section-heading">

                            <div class="section-heading-left">

                                <div class="section-heading-icon">
                                    <i class="fas fa-universal-access"></i>
                                </div>

                                <div>
                                    <h3>Accessibility Information</h3>
                                    <p>Additional support information</p>
                                </div>

                            </div>

                            <span class="section-tag">
                                OPTIONAL
                            </span>

                        </div>


                        <div class="info-grid">

                            <div class="info-item">

                                <span class="info-label">
                                    Disability Status
                                </span>

                                <span class="info-value">

                                    @if($student->has_disability)

                                        <span class="value-warning">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Yes
                                        </span>

                                    @else

                                        <span class="value-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            No
                                        </span>

                                    @endif

                                </span>

                            </div>


                            <div class="info-item">

                                <span class="info-label">
                                    Disability Type
                                </span>

                                <span class="info-value">

                                    {{ $student->disability_type ?: 'None recorded' }}

                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- ==================================================
                         FATHER
                    =================================================== --}}
                    <div class="section-block">

                        <div class="section-heading">

                            <div class="section-heading-left">

                                <div class="section-heading-icon">
                                    <i class="fas fa-male"></i>
                                </div>

                                <div>
                                    <h3>Father / Parent Information</h3>
                                    <p>Registered father or parent contact</p>
                                </div>

                            </div>

                            <span class="section-tag">
                                PARENT
                            </span>

                        </div>


                        <div class="contact-card">

                            <div class="contact-card-header">
                                <i class="fas fa-user"></i>
                                <span>Father / Parent</span>
                            </div>

                            <div class="contact-grid">

                                <div class="info-item">
                                    <span class="info-label">Name</span>
                                    <span class="info-value">
                                        {{ $student->father_name ?: 'Not provided' }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value">
                                        {{ $student->father_phone ?: 'Not provided' }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value">
                                        {{ $student->father_email ?: 'Not provided' }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Occupation</span>
                                    <span class="info-value">
                                        {{ $student->father_occupation ?: 'Not provided' }}
                                    </span>
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ==================================================
                         MOTHER
                    =================================================== --}}
                    <div class="section-block">

                        <div class="section-heading">

                            <div class="section-heading-left">

                                <div class="section-heading-icon">
                                    <i class="fas fa-female"></i>
                                </div>

                                <div>
                                    <h3>Mother / Parent Information</h3>
                                    <p>Registered mother or parent contact</p>
                                </div>

                            </div>

                            <span class="section-tag">
                                PARENT
                            </span>

                        </div>


                        <div class="contact-card">

                            <div class="contact-card-header">
                                <i class="fas fa-user"></i>
                                <span>Mother / Parent</span>
                            </div>

                            <div class="contact-grid">

                                <div class="info-item">
                                    <span class="info-label">Name</span>
                                    <span class="info-value">
                                        {{ $student->mother_name ?: 'Not provided' }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value">
                                        {{ $student->mother_phone ?: 'Not provided' }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value">
                                        {{ $student->mother_email ?: 'Not provided' }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Occupation</span>
                                    <span class="info-value">
                                        {{ $student->mother_occupation ?: 'Not provided' }}
                                    </span>
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ==================================================
                         GUARDIAN
                    =================================================== --}}
                    @if(
                        $student->guardian_name ||
                        $student->guardian_phone ||
                        $student->guardian_email ||
                        $student->guardian_relationship
                    )

                        <div class="section-block">

                            <div class="section-heading">

                                <div class="section-heading-left">

                                    <div class="section-heading-icon">
                                        <i class="fas fa-user-shield"></i>
                                    </div>

                                    <div>
                                        <h3>Guardian Information</h3>
                                        <p>Emergency or alternative contact</p>
                                    </div>

                                </div>

                                <span class="section-tag">
                                    GUARDIAN
                                </span>

                            </div>


                            <div class="contact-card">

                                <div class="contact-card-header">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Registered Guardian</span>
                                </div>

                                <div class="contact-grid">

                                    <div class="info-item">
                                        <span class="info-label">Name</span>
                                        <span class="info-value">
                                            {{ $student->guardian_name ?: 'Not provided' }}
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">Relationship</span>
                                        <span class="info-value">
                                            {{ $student->guardian_relationship ?: 'Not provided' }}
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">Phone</span>
                                        <span class="info-value">
                                            {{ $student->guardian_phone ?: 'Not provided' }}
                                        </span>
                                    </div>

                                    <div class="info-item">
                                        <span class="info-label">Email</span>
                                        <span class="info-value">
                                            {{ $student->guardian_email ?: 'Not provided' }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </div>

                    @endif


                </div>

            </div>

        </div>

    </div>

</div>

@endsection