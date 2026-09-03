@extends('students.layouts.app')

@section('title', 'Student Dashboard')

@section('content')

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden rounded-4" 
             style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 40%, #90caf9 70%, #64b5f6 100%);">
            
            <div class="card-body p-0 position-relative">
                <!-- Decorative Elements -->
                <div class="position-absolute top-0 end-0 opacity-10" style="font-size: 8rem; transform: translate(20%, -20%);">
                    <i class="fas fa-graduation-cap text-primary"></i>
                </div>
                <div class="position-absolute bottom-0 start-0 opacity-5" style="font-size: 6rem; transform: translate(-20%, 20%);">
                    <i class="fas fa-star text-primary"></i>
                </div>
                
                <div class="p-3 p-md-4 p-xl-5 position-relative">
                    <div class="row align-items-center g-3">
                        <!-- Left Content -->
                        <div class="col-md-8 text-primary-dark">
                            <!-- Greeting & Badges -->
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <span class="badge bg-white bg-opacity-30 text-primary px-3 py-1 rounded-pill" 
                                      style="backdrop-filter: blur(10px); color: #0d47a1 !important;">
                                    <i class="fas fa-calendar-alt me-1" style="font-size: 0.7rem;"></i>
                                    <small>{{ \Carbon\Carbon::now()->format('l, M j, Y') }}</small>
                                </span>
                                <span class="badge bg-white bg-opacity-30 text-primary px-3 py-1 rounded-pill"
                                      style="backdrop-filter: blur(10px); color: #0d47a1 !important;">
                                    <i class="fas fa-circle me-1 text-success" style="font-size: 0.5rem;"></i>
                                    <small>{{ ucfirst($student->status ?? 'Active') }}</small>
                                </span>
                            </div>

                            <!-- Welcome Message -->
                            <h5 class="fw-bold mb-1" style="color: #0d47a1; font-size: 1.2rem;">
                                <i class="fas fa-hand-wave me-2" style="color: #fbbf24;"></i>
                                Hello, {{ $student->full_name }}! 
                                <span style="font-weight: 400; font-size: 0.85rem; color: #1565c0;">
                                    Welcome back to your dashboard
                                </span>
                            </h5>
                            
                            <!-- Quick Info -->
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                <span class="badge bg-white bg-opacity-30 text-primary px-3 py-1 rounded-pill"
                                      style="backdrop-filter: blur(10px); font-weight: 400; color: #0d47a1 !important;">
                                    <i class="fas fa-users me-1" style="font-size: 0.7rem;"></i>
                                    <small>{{ $assignment->studentClass->name ?? 'No Class Assigned' }}</small>
                                </span>
                                <span class="badge bg-white bg-opacity-30 text-primary px-3 py-1 rounded-pill"
                                      style="backdrop-filter: blur(10px); font-weight: 400; color: #0d47a1 !important;">
                                    <i class="fas fa-id-card me-1" style="font-size: 0.7rem;"></i>
                                    <small>ID: {{ $student->student_id }}</small>
                                </span>
                            </div>
                        </div>

                        <!-- Right Content - Avatar -->
                        <div class="col-md-4 text-center text-md-end">
                            <div class="position-relative d-inline-block">
                                <!-- Avatar -->
                                <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('images/default-avatar.png') }}" 
                                     alt="{{ $student->full_name }}"
                                     class="rounded-circle border border-3 border-white shadow"
                                     width="100" height="100"
                                     style="object-fit: cover; transition: transform 0.3s ease; box-shadow: 0 4px 20px rgba(13, 71, 161, 0.2);"
                                     onmouseover="this.style.transform='scale(1.05)'"
                                     onmouseout="this.style.transform='scale(1)'">
                                
                                <!-- Online Status Dot -->
                                <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                                      style="width: 16px; height: 16px;">
                                    <span class="position-absolute top-50 start-50 translate-middle" 
                                          style="width: 6px; height: 6px; background: #22c55e; border-radius: 50%;"></span>
                                </span>
                                
                                <!-- Ring Animation -->
                                <span class="position-absolute top-50 start-50 translate-middle rounded-circle border border-primary border-opacity-25 animate-ping"
                                      style="width: 120px; height: 120px; animation-duration: 3s; border-color: #1976d2 !important;"></span>
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-white bg-opacity-30 text-primary px-3 py-1 rounded-pill"
                                      style="font-size: 0.65rem; backdrop-filter: blur(10px); color: #0d47a1 !important;">
                                    <i class="fas fa-circle me-1 text-success" style="font-size: 0.4rem;"></i>
                                    Online
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Smooth Pulse Animation */
    @keyframes pulse-ring {
        0% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 0.8;
        }
        50% {
            transform: translate(-50%, -50%) scale(1.15);
            opacity: 0.3;
        }
        100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 0.8;
        }
    }
    
    .animate-ping {
        animation: pulse-ring 3s ease-in-out infinite;
    }

    .text-primary-dark {
        color: #0d47a1;
    }

    /* Hover Effects */
    .badge {
        transition: all 0.2s ease;
    }
    
    .badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 71, 161, 0.15);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body .p-3 {
            padding: 1rem !important;
        }
        
        h5.fw-bold {
            font-size: 1rem !important;
        }
        
        img.rounded-circle {
            width: 70px !important;
            height: 70px !important;
        }
        
        .badge {
            font-size: 0.7rem !important;
            padding: 4px 12px !important;
        }
        
        .animate-ping {
            width: 90px !important;
            height: 90px !important;
        }
    }

    @media (max-width: 576px) {
        .col-md-8 {
            text-align: center !important;
        }
        
        .col-md-4 {
            text-align: center !important;
        }
        
        .d-flex.align-items-center {
            justify-content: center !important;
        }
        
        h5.fw-bold {
            font-size: 0.95rem !important;
        }
        
        img.rounded-circle {
            width: 60px !important;
            height: 60px !important;
        }
    }
</style>

<!-- Quick Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3 hover-lift" 
             style="border-radius: 16px; border-left: 4px solid #1976d2;">
            <div class="card-body">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-calendar-check text-primary fa-2x"></i>
                </div>
                <h6 class="fw-bold mb-0" style="color: #0d47a1;">Attendance</h6>
                <small class="text-muted" style="color: #1565c0 !important;">{{ $attendanceRate ?? 0 }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3 hover-lift"
             style="border-radius: 16px; border-left: 4px solid #388e3c;">
            <div class="card-body">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-book text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold mb-0" style="color: #1b5e20;">Subjects</h6>
                <small class="text-muted" style="color: #2e7d32 !important;">{{ $subjects ?? 0 }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3 hover-lift"
             style="border-radius: 16px; border-left: 4px solid #f9a825;">
            <div class="card-body">
                <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-chart-line text-warning fa-2x"></i>
                </div>
                <h6 class="fw-bold mb-0" style="color: #f57f17;">Results</h6>
                <small class="text-muted" style="color: #f9a825 !important;">{{ $recentResults->count() ?? 0 }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center py-3 hover-lift"
             style="border-radius: 16px; border-left: 4px solid #0288d1;">
            <div class="card-body">
                <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                    <i class="fas fa-money-bill-wave text-info fa-2x"></i>
                </div>
                <h6 class="fw-bold mb-0" style="color: #01579b;">Fee Balance</h6>
                <small class="text-muted" style="color: #0288d1 !important;">GHS {{ number_format($feeBalance ?? 0, 2) }}</small>
            </div>
        </div>
    </div>
</div>

<!-- ANNOUNCEMENTS SECTION -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3" style="border-radius: 16px;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" 
                 style="border-radius: 16px 16px 0 0;">
                <h6 class="fw-bold mb-0" style="color: #0d47a1;">
                    <i class="fas fa-bullhorn text-primary me-2"></i>
                    Announcements & Notices
                    @if(isset($announcements) && $announcements->count() > 0)
                        <span class="badge bg-danger ms-2">{{ $announcements->count() }}</span>
                    @endif
                </h6>
                @if(Route::has('announcements.public'))
                    <a href="{{ route('announcements.public') }}" class="btn btn-sm btn-outline-primary" 
                       style="border-radius: 20px; padding: 0.3rem 1.2rem;">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                @else
                    <span class="text-muted small">All announcements shown</span>
                @endif
            </div>
            <div class="card-body">
                @if(isset($announcements) && $announcements->count() > 0)
                    <div class="row g-3">
                        @if(isset($featuredAnnouncement) && $featuredAnnouncement)
                            <div class="col-12">
                                <div class="card border-0 rounded-3" 
                                     style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="bg-primary rounded-circle p-2" style="background: #1976d2 !important;">
                                                <i class="fas fa-star text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                    <div>
                                                        <span class="badge bg-primary text-white mb-1" style="background: #1976d2 !important;">
                                                            <i class="fas fa-star me-1"></i> Featured
                                                        </span>
                                                        <h6 class="fw-bold mb-1" style="color: #0d47a1;">{{ $featuredAnnouncement->title }}</h6>
                                                    </div>
                                                    <small class="text-muted" style="color: #1565c0 !important;">{{ $featuredAnnouncement->time_ago }}</small>
                                                </div>
                                                <p class="mb-2 text-muted small">
                                                    {{ Str::limit($featuredAnnouncement->content, 150) }}
                                                </p>
                                                @if(Route::has('announcements.show'))
                                                    <a href="{{ route('announcements.show', $featuredAnnouncement) }}" 
                                                       class="btn btn-sm btn-primary" style="background: #1976d2; border-radius: 20px; border: none;">
                                                        Read More <i class="fas fa-arrow-right ms-1"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @foreach($announcements as $announcement)
                            @if(!isset($featuredAnnouncement) || $announcement->id != $featuredAnnouncement->id)
                                @if($loop->index < 3)
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 hover-lift" style="border-radius: 12px;">
                                            @if($announcement->image)
                                                <img src="{{ asset('storage/' . $announcement->image) }}" 
                                                     class="card-img-top" alt="{{ $announcement->title }}"
                                                     style="height: 160px; object-fit: cover; border-radius: 12px 12px 0 0;">
                                            @endif
                                            <div class="card-body">
                                                <div class="d-flex gap-1 flex-wrap mb-2">
                                                    <span class="badge bg-{{ $announcement->type_badge ?? 'primary' }}" 
                                                          style="background: #1976d2 !important;">
                                                        {{ ucfirst($announcement->type ?? 'General') }}
                                                    </span>
                                                    @if(isset($announcement->priority) && ($announcement->priority == 'urgent' || $announcement->priority == 'high'))
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            {{ ucfirst($announcement->priority) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h6 class="fw-bold mb-1" style="color: #0d47a1;">{{ Str::limit($announcement->title, 40) }}</h6>
                                                <p class="text-muted small mb-2">{{ Str::limit($announcement->content, 80) }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted" style="color: #1565c0 !important;">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $announcement->time_ago ?? 'Just now' }}
                                                    </small>
                                                    @if(Route::has('announcements.public'))
                                                        <a href="{{ route('announcements.public', $announcement) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           style="border-radius: 20px; border-color: #1976d2; color: #1976d2;">
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>

                    @if($announcements->count() > 3)
                        <div class="text-center mt-3">
                            @if(Route::has('announcements.public'))
                                <a href="{{ route('announcements.public') }}" class="btn btn-outline-primary" 
                                   style="border-radius: 25px; padding: 0.5rem 2rem; border-color: #1976d2; color: #1976d2;">
                                    View All Announcements <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            @else
                                <span class="text-muted small">{{ $announcements->count() - 3 }} more announcements available</span>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3 d-block" style="color: #90caf9 !important;"></i>
                        <h6 class="text-muted" style="color: #1565c0 !important;">No announcements available</h6>
                        <p class="text-muted small" style="color: #64b5f6 !important;">Check back later for updates and notices.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Session Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3" style="border-radius: 16px;">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold mb-0" style="color: #0d47a1;">
                        <i class="fas fa-sign-out-alt text-danger me-2"></i>
                        Session Management
                    </h6>
                    <small class="text-muted" style="color: #1565c0 !important;">You are currently logged in as a student</small>
                </div>
                <!-- The logout button is in the navbar now -->
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(13, 71, 161, 0.12) !important;
    }
    .hover-lift .rounded-circle {
        transition: transform 0.3s ease;
    }
    .hover-lift:hover .rounded-circle {
        transform: scale(1.1);
    }
    
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .bg-opacity-15 { --bs-bg-opacity: 0.15; }
    .bg-opacity-20 { --bs-bg-opacity: 0.2; }
    .bg-opacity-30 { --bs-bg-opacity: 0.3; }
    .bg-opacity-75 { --bs-bg-opacity: 0.75; }
    
    @media (max-width: 768px) {
        .text-md-end { text-align: center !important; }
    }
    
    /* Modal Animation */
    .modal.fade .modal-dialog {
        transform: scale(0.9) translateY(-20px);
        transition: all 0.3s ease;
    }
    .modal.show .modal-dialog {
        transform: scale(1) translateY(0);
    }

    /* Card hover effects */
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 8px 30px rgba(13, 71, 161, 0.08) !important;
    }

    /* Badge styles */
    .badge {
        font-weight: 500;
    }
</style>
@endsection