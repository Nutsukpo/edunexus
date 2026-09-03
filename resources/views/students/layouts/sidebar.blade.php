<!-- Sidebar Toggle Button (Mobile) -->
<button id="sidebarToggle" class="btn btn-primary d-md-none position-fixed" 
        style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50%; width: 50px; height: 50px; box-shadow: 0 4px 15px rgba(13, 71, 161, 0.3);">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="d-flex flex-column flex-shrink-0 p-3 text-white position-fixed sidebar"
     style="width:260px;height:100vh;background:linear-gradient(180deg, #0d47a1, #1976d2, #42a5f5); z-index: 1040; transition: all 0.3s ease;">

    <!-- Logo/Brand Section -->
    <div class="text-center mb-4">
        <a href="{{ route('students.dashboard') }}" class="text-white text-decoration-none">
            <div class="brand-icon mb-2">
                <i class="fas fa-graduation-cap fa-2x"></i>
            </div>
            <h4 class="mb-0 font-weight-bold" style="letter-spacing: 1px;">
                Talha USMS
            </h4>
            <small class="text-light" style="font-size: 0.7rem; opacity: 0.8;">Student Portal</small>
        </a>
    </div>

    <hr class="border-light" style="opacity: 0.3;">

    <!-- Navigation Menu -->
    <ul class="nav nav-pills flex-column mb-auto" style="overflow-y: auto; max-height: calc(100vh - 250px);">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('students.dashboard') }}" 
               class="nav-link text-white {{ request()->routeIs('students.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home me-3"></i>
                <span>Dashboard</span>
                @if(request()->routeIs('students.dashboard'))
                    <span class="badge bg-light text-primary ms-2">Active</span>
                @endif
            </a>
        </li>

        <!-- My Profile -->
        <li class="nav-item">
            <a href="/student/profile" 
               class="nav-link text-white {{ request()->is('student/profile*') ? 'active' : '' }}">
                <i class="fas fa-user me-3"></i>
                <span>My Profile</span>
                @if(!request()->is('student/profile*'))
                    <span class="badge bg-light text-secondary ms-2">View</span>
                @endif
            </a>
        </li>

        <!-- Attendance -->
        <li class="nav-item">
            <a href="#" 
               class="nav-link text-white">
                <i class="fas fa-calendar-check me-3"></i>
                <span>Attendance</span>
                <span class="badge bg-warning text-dark ms-2">New</span>
            </a>
        </li>

        <!-- Results -->
        <li class="nav-item">
            <a href="/student/results" 
               class="nav-link text-white {{ request()->is('student/results*') ? 'active' : '' }}">
                <i class="fas fa-chart-line me-3"></i>
                <span>Results</span>
                @if(!request()->is('student/results'))
                    <span class="badge bg-light text-secondary ms-2">Check</span>
                @endif
            </a>
        </li>

        <!-- Academic History -->
        <li class="nav-item">
            <a href="/student/class-history" 
               class="nav-link text-white {{ request()->is('class-history*') ? 'active' : '' }}">
                <i class="fas fa-file-alt me-3"></i>
                <span>Academic History</span>
            </a>
        </li>

        <!-- Timetable -->
        <li class="nav-item">
            <a href="/student/timetable" 
               class="nav-link text-white {{ request()->is('student/timetable*') ? 'active' : '' }}">
                <i class="fas fa-calendar me-3"></i>
                <span>Timetable</span>
            </a>
        </li>

        <!-- School Fees -->
        <li class="nav-item">
            <a href="/student/fees" 
               class="nav-link text-white">
                <i class="fas fa-money-bill-wave me-3"></i>
                <span>School Fees</span>
                <span class="badge bg-danger ms-2">Due</span>
            </a>
        </li>

        <!-- Announcements -->
        <li class="nav-item">
            <a href="#" 
               class="nav-link text-white">
                <i class="fas fa-bullhorn me-3"></i>
                <span>Announcements</span>
                <span class="badge bg-info ms-2">3</span>
            </a>
        </li>

        <!-- student-grievance -->
        <li class="nav-item">
            <a href="/student-grievance" 
               class="nav-link text-white">
                <i class="fas fa-bullhorn me-3"></i>
                <span>Grievance</span>
                <span class="badge bg-info ms-2">3</span>
            </a>
        </li>
    </ul>

    <hr class="border-light" style="opacity: 0.3;">

    <!-- User Info & Logout -->
    <div class="mt-auto">
        <div class="d-flex align-items-center text-white mb-3 p-2 rounded" 
             style="background: rgba(255,255,255,0.1);">
            <div class="user-avatar me-2">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div class="user-info">
                <small class="d-block font-weight-bold" style="font-size: 0.9rem;">
                    {{ Auth::user()->name ?? 'Student' }}
                </small>
                <small style="font-size: 0.7rem; opacity: 0.8;">
                    <i class="fas fa-circle text-success" style="font-size: 0.5rem;"></i>
                    Online
                </small>
            </div>
        </div>
        
        <a href="{{ route('logout') }}" 
           class="btn btn-outline-light btn-sm w-100" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay d-md-none position-fixed" 
     style="display: none; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1039;">
</div>