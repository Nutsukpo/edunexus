<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kabore USMS')</title>

    <!-- Bootstrap 5 + Icons + Fonts -->
    <!-- IMPORTANT: Bootstrap 5.3.3 is loaded ONCE. Do not load Bootstrap 4/5 again in child views. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
 
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <link rel="icon" type="image/png" href="{{ asset('img/Talha.jpeg') }}">
    @yield('styles')
    
    
    <style>
        /* ---------- PRELOADER ---------- */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s ease;
            opacity: 1;
            visibility: visible;
        }
        
        body.dark-mode #preloader {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            transform: scale(1);
            animation: loaderFadeIn 0.4s ease-out;
        }
        
        @keyframes loaderFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .loader {
            width: 64px;
            height: 64px;
            border: 5px solid rgba(26, 75, 140, 0.15);
            border-top: 5px solid #1a4b8c;
            border-right: 5px solid #2b6bb0;
            border-bottom: 5px solid #1a4b8c;
            border-radius: 50%;
            animation: rotation 0.8s linear infinite;
            box-shadow: 0 4px 15px rgba(26, 75, 140, 0.2);
        }
        
        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loader-dots {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 8px;
        }
        
        .dot {
            width: 8px;
            height: 8px;
            background: #1a4b8c;
            border-radius: 50%;
            animation: pulse 1.4s ease-in-out infinite;
        }
        
        .dot:nth-child(1) { animation-delay: 0s; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }
        
        .loader-text {
            color: #1a4b8c;
            font-weight: 600;
            text-align: center;
            font-size: 1.1rem;
            letter-spacing: 1px;
            background: rgba(26, 75, 140, 0.08);
            padding: 8px 24px;
            border-radius: 40px;
        }
        
        .loader-subtext {
            color: #6c757d;
            font-size: 0.75rem;
            margin-top: -8px;
        }
        
        body.dark-mode .loader-subtext {
            color: #94a3b8;
        }
        
        .preloader-hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        
        body.preloader-active .main-content,
        body.preloader-active .navbar,
        body.preloader-active .footer {
            opacity: 0;
        }
        
        body.preloader-done .main-content,
        body.preloader-done .navbar,
        body.preloader-done .footer {
            animation: contentReveal 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        }
        
        @keyframes contentReveal {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* ---------- SAFE GLOBAL RESETS ---------- */
        html {
            min-height: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f7ff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.3s ease, color 0.2s ease;
        }

        .main-content {
            flex: 1;
            padding: 0;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulseSoft {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.02); }
        }

        /* ========== EDUNEXUS SIDEBAR - BLUE GRADIENT ========== */
        .offcanvas {
            background: linear-gradient(180deg, #0a2f66 0%, #1a4b8c 40%, #2b6bb0 70%, #1a4b8c 100%) !important;
            border-right: none !important;
            box-shadow: 4px 0 30px rgba(26, 75, 140, 0.4);
            display: flex !important;
            flex-direction: column !important;
        }

        .offcanvas-start {
            width: 280px !important;
        }
        
        /* Sidebar scrollbar - subtle blue */
        .offcanvas-body::-webkit-scrollbar {
            width: 4px;
        }
        
        .offcanvas-body::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        .offcanvas-body::-webkit-scrollbar-thumb {
            background: rgba(43, 107, 176, 0.4);
            border-radius: 10px;
        }
        
        .offcanvas-body::-webkit-scrollbar-thumb:hover {
            background: rgba(43, 107, 176, 0.6);
        }

        /* MAKE SIDEBAR CONTENT SCROLLABLE */
        .offcanvas-body {
            flex: 1 !important;
            overflow-y: auto !important;
            padding: 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        
        /* Sidebar Header - with subtle blue glow */
        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 0;
            flex-shrink: 0;
            background: rgba(0, 0, 0, 0.15);
            position: relative;
        }
        
        .sidebar-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3f87d0, transparent);
            opacity: 0.3;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2b6bb0, #1a4b8c);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            box-shadow: 0 6px 16px rgba(43, 107, 176, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .sidebar-logo-icon:hover {
            transform: scale(1.05) rotate(-5deg);
            box-shadow: 0 8px 24px rgba(43, 107, 176, 0.5);
        }
        
        .sidebar-logo-text a {
            text-decoration: none;
        }
        
        .sidebar-logo-text h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
            letter-spacing: -0.3px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .sidebar-logo-text h4 span {
            color: #6ba3e0;
        }

        .sidebar-logo-text p {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.6);
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* Sidebar Navigation Container - Scrollable */
        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem 0.75rem 1rem 0.75rem;
        }
        
        .sidebar-nav-container::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar-nav-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
        }
        
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(43, 107, 176, 0.3);
            border-radius: 10px;
        }

        .sidebar-nav {
            padding: 0;
        }

        /* Section Title - subtle and elegant */
        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.35);
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.25rem;
        }

        /* Nav Items - Clean white text with hover effects */
        .nav-item-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.7rem 1rem;
            margin-bottom: 2px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.8) !important;
            text-decoration: none;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 0.85rem;
            position: relative;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .nav-item-custom i {
            width: 22px;
            font-size: 1rem;
            text-align: center;
            transition: all 0.2s;
            color: rgba(255, 255, 255, 0.6);
        }

        .nav-item-custom:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff !important;
            transform: translateX(4px);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .nav-item-custom:hover i {
            color: #ffffff;
        }

        .nav-item-custom.active {
            background: linear-gradient(95deg, rgba(43, 107, 176, 0.4), rgba(26, 75, 140, 0.6));
            color: #ffffff !important;
            border-left: 3px solid #3f87d0;
            box-shadow: 0 4px 15px rgba(43, 107, 176, 0.15);
        }
        
        .nav-item-custom.active i {
            color: #6ba3e0;
        }
        
        .nav-item-custom.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: linear-gradient(180deg, #3f87d0, #6ba3e0);
            border-radius: 0 4px 4px 0;
        }

        .nav-badge {
            margin-left: auto;
            background: rgba(43, 107, 176, 0.25);
            color: #6ba3e0;
            font-size: 0.6rem;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        /* Collapse submenu styles - with indentation */
        .collapse.nav-collapse {
            padding-left: 1.75rem;
        }
        
        .collapse.nav-collapse .nav-item-custom {
            padding: 0.55rem 1rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.65) !important;
            border-left: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 0 12px 12px 0;
        }
        
        .collapse.nav-collapse .nav-item-custom i {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
        }
        
        .collapse.nav-collapse .nav-item-custom:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .collapse.nav-collapse .nav-item-custom:hover i {
            color: #ffffff;
        }
        
        .collapse.nav-collapse .nav-item-custom.active {
            background: rgba(43, 107, 176, 0.2);
            border-left: 2px solid #3f87d0;
            color: #ffffff !important;
        }
        
        /* Sidebar footer - subtle blue gradient */
        .sidebar-footer {
            flex-shrink: 0;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            margin-top: auto;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
        }
        
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.5rem;
            border-radius: 14px;
            transition: all 0.2s;
        }
        
        .sidebar-user:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #2b6bb0, #1a4b8c);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: white;
            box-shadow: 0 4px 12px rgba(43, 107, 176, 0.3);
        }
        
        .sidebar-user-info .name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }
        
        .sidebar-user-info .role {
            font-size: 0.6rem;
            color: rgba(255, 255, 255, 0.5);
            margin: 0;
        }
        
        /* Dark mode - sidebar stays blue gradient */
        body.dark-mode .offcanvas {
            background: linear-gradient(180deg, #0a2f66 0%, #1a4b8c 40%, #2b6bb0 70%, #1a4b8c 100%) !important;
        }
        
        body.dark-mode .sidebar-footer {
            background: rgba(0, 0, 0, 0.25);
        }
        
        body.dark-mode {
            background: #0f172a;
        }

        body.dark-mode .navbar {
            background: #1e293b !important;
            border-bottom-color: #334155;
        }

        body.dark-mode .navbar-brand,
        body.dark-mode .greeting-text,
        body.dark-mode .icon-btn {
            color: #e2e8f0;
        }

        body.dark-mode .dropdown-menu {
            background: #1e293b;
            border-color: #334155;
        }

        body.dark-mode .dropdown-item {
            color: #cbd5e1;
        }
        
        body.dark-mode .dropdown-item:hover {
            background: #334155;
            color: white;
        }

        body.dark-mode .footer {
            background: #1e293b;
            border-top-color: #334155;
            color: #94a3b8;
        }

        body.dark-mode .darkmode-toggle {
            background: #334155;
            color: #f1f5f9;
        }
        
        body.dark-mode .greeting-wrapper {
            background: #334155;
        }
        
        body.dark-mode .date-display {
            background: #334155;
            color: #cbd5e1;
        }

        /* ---------- BOOTSTRAP 5 SAFE APPLICATION LAYER ---------- */
        /*
         * Bootstrap 5.3.3 is loaded once in this layout.
         * Page-specific styles should be placed in @push('styles').
         * Do not redefine Bootstrap modal, dropdown, button, form, table,
         * navbar or collapse rules globally.
         */
        .app-content {
            width: 100%;
            min-width: 0;
        }

        .navbar .dropdown-menu {
            z-index: 1080;
        }

        .modal {
            z-index: 1055;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        #preloader.preloader-hidden {
            pointer-events: none !important;
        }

        /* Navbar & misc */
        .navbar {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #edf2f7;
            transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
            padding: 0.75rem 1.5rem;
            animation: slideDown 0.4s ease-out;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2b6bb0, #1a4b8c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(43, 107, 176, 0.2);
        }

        .user-avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 20px rgba(43, 107, 176, 0.4);
        }

        .dropdown-menu {
            border-radius: 16px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #eef2f6;
            min-width: 220px;
        }

        .dropdown-item {
            padding: 0.6rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .dropdown-item i {
            width: 22px;
            margin-right: 8px;
        }

        .notification-dropdown {
            width: 340px;
            padding: 0;
            border-radius: 20px;
        }
        
        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid #eef2f6;
            font-weight: 600;
        }
        
        .notification-item {
            padding: 0.85rem 1rem;
            transition: all 0.2s;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .notification-item:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 20px;
            animation: pulseSoft 2s infinite;
        }
        
        .darkmode-toggle {
            background: #f1f5f9;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .icon-btn {
            background: transparent;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.25s;
        }
        
        .icon-btn:hover {
            background: #f1f5f9;
        }
        
        .footer {
            background: white;
            border-top: 1px solid #edf2f7;
            padding: 1.2rem 2rem;
            margin-top: auto;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .date-display {
            font-size: 0.75rem;
            font-weight: 500;
            background: #f1f5f9;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        
        .greeting-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 6px 16px;
            border-radius: 40px;
            transition: all 0.2s;
        }
        
        .menu-btn {
            transition: all 0.2s;
            background: linear-gradient(135deg, #1a4b8c, #2b6bb0) !important;
            border: none !important;
        }
        
        .menu-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(43, 107, 176, 0.4) !important;
            background: linear-gradient(135deg, #0a2f66, #1a4b8c) !important;
        }
        
        .logout-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 1000000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 20px;
            backdrop-filter: blur(8px);
        }
        
        .logout-spinner {
            width: 55px;
            height: 55px;
            border: 4px solid rgba(255,255,255,0.2);
            border-top: 4px solid #2b6bb0;
            border-right: 4px solid #1a4b8c;
            border-radius: 50%;
            animation: rotation 0.7s linear infinite;
        }
        
        .logout-text {
            color: white;
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .greeting-text { display: none; }
            .date-display { display: none; }
            .offcanvas-start {
                width: 260px !important;
            }
        }
        
        /* Rotate icon for collapsed items */
        .nav-item-custom[data-bs-toggle="collapse"] i.fa-chevron-down,
        .nav-item-custom[data-bs-toggle="collapse"] i.fa-chevron-right {
            margin-left: auto;
            transition: transform 0.3s ease;
        }
        
        .nav-item-custom[data-bs-toggle="collapse"]:not(.collapsed) i.fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
    @stack('styles')
</head>
<body class="preloader-active">
    <!-- PRELOADER -->
    <div id="preloader">
        <div class="loader-container">
            <div class="loader"></div>
            <div class="loader-dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            <div class="loader-text">Talha Prem USMS</div>
            <div class="loader-subtext">Loading at {{ now()->format('h:i:s A') }} ...</div>
        </div>
    </div>

    <!-- SIDEBAR (Offcanvas) - EDUNEXUS BLUE GRADIENT -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="sidebar-logo-text">
                    <h4>Talha<span>Premier</span></h4>
                    <p>Universal School Management System</p>
                </div>
            </div>
        </div>
        
        <!-- SCROLLABLE SIDEBAR CONTENT -->
        <div class="offcanvas-body">
            <div class="sidebar-nav-container">
                <div class="sidebar-nav">
                    
                    <!-- DASHBOARD -->
                    <div class="nav-section">
                        <div class="nav-section-title">DASHBOARD</div>
                        <a href="/dashboard" class="nav-item-custom active">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <!-- STUDENT MANAGEMENT -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#studentMenu" aria-expanded="false">
                            <i class="fas fa-user-graduate"></i>
                            <span>Students</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="studentMenu">
                            <a href="/students" class="nav-item-custom">
                                <i class="fas fa-list"></i>
                                <span>Admissions</span>
                            </a>
                            
                            <a href="/student-class-assignments" class="nav-item-custom">
                                <i class="fas fa-building"></i>
                                <span>Students Class Enrolled</span>
                            </a>
                            <a href="/student-progressions" class="nav-item-custom">
                                <i class="fas fa-level-up-alt"></i>
                                <span>Promotions</span>
                            </a>
                            <a href="/graduated-students" class="nav-item-custom">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Graduated Students</span>
                            </a>
                        </div>
                    </div>

                    <!-- ACADEMICS -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#academicMenu">
                            <i class="fas fa-book-open"></i>
                            <span>Academics</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="academicMenu">
                            <a href="/student-classes" class="nav-item-custom">
                                    <i class="fas fa-building"></i>
                                    <span>Class/Form</span>
                                </a>    
                            <a href="/lesson-notes" class="nav-item-custom">
                                <i class="fas fa-sticky-note"></i>
                                <span>Lesson Notes</span>
                            </a>
                            <a href="/academic-years" class="nav-item-custom">
                                <i class="fas fa-user-shield"></i>
                                <span>Academic Years</span>
                            </a>
                            <a href="/terms" class="nav-item-custom">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Terms</span>
                            </a>
                            <a href="/subjects" class="nav-item-custom">
                                <i class="fas fa-book"></i>
                                <span>Subjects</span>
                            </a>
                            <a href="/timetables" class="nav-item-custom">
                                <i class="fas fa-clock"></i>
                                <span>Timetable</span>
                            </a>
                            
                        </div>
                    </div>

                    <!-- ASSESSMENT -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#examMenu">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Assessment</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="examMenu">
                            <a href="/scores" class="nav-item-custom">
                                <i class="fas fa-chart-line"></i>
                                <span>Subject Scores</span>
                            </a>
                            <a href="/assessment-forms" class="nav-item-custom">
                                <i class="fas fa-upload"></i>
                                <span>Assessment Form</span>
                            </a>
                          
                        </div>
                    </div>

                      <!-- RESULTS -->
                      <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#resultMenu">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Results</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="resultMenu">
                            <a href="/broadsheet" class="nav-item-custom">
                                <i class="fas fa-registered"></i>
                                <span>Class Results</span>
                            </a>
                            <a href="/subject-results" class="nav-item-custom">
                                <i class="fas fa-chart-line"></i>
                                <span>Subject Results</span>
                            </a>
                            <a href="/report-cards" class="nav-item-custom">
                                <i class="fas fa-id-card"></i>
                                <span>Report Cards</span>
                            </a>
                        </div>
                    </div>

                    <!-- ATTENDANCE -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#attendanceMenu">
                            <i class="fas fa-calendar-check"></i>
                            <span>Attendance</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="attendanceMenu">
                            <a href="/attendance-sessions" class="nav-item-custom">
                                <i class="fas fa-user-check"></i>
                                <span>Student Attendance</span>
                            </a>
                            <a href="/staff-attendance" class="nav-item-custom">
                                <i class="fas fa-users"></i>
                                <span>Staff Attendance</span>
                            </a>
                            <a href="/staffattendance-live-map" class="nav-item-custom">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Attendance Map</span>
                            </a>
                        </div>
                    </div>

                     <!-- APPROVALS -->
                     <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#approvalMenu">
                            <i class="fas fa-calendar-check"></i>
                            <span>Approvals</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="approvalMenu">
                            <a href="/payroll-period-approvals" class="nav-item-custom">
                                <i class="fas fa-paypal"></i>
                                <span>Payroll</span>
                            </a>
                            <a href="/leave-approvals" class="nav-item-custom">
                                <i class="fas fa-clock"></i>
                                <span>Leaves</span>
                            </a>
                            <a href="/approvals" class="nav-item-custom">
                                <i class="fas fa-sticky-note"></i>
                                <span>Lesson Note</span>
                            </a>
                            <a href="/bill-sheet-approvals" class="nav-item-custom">
                                <i class="fas fa-sticky-note"></i>
                                <span>Bill Sheets</span>
                            </a>
                        </div>
                    </div>

                    <!-- FINANCE -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#financeMenu">
                            <i class="fas fa-wallet"></i>
                            <span>Fees & Payments</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="financeMenu">
                            <!-- <a href="/fee-categories" class="nav-item-custom">
                                <i class="fas fa-credit-card"></i>
                                <span>Fee Categories</span>
                            </a> -->
                            <!-- <a href="/class-fee-structures" class="nav-item-custom">
                                <i class="fas fa-refresh"></i>
                                <span>Fee Structure</span>
                            </a> -->
                            <a href="/bill-sheets" class="nav-item-custom">
                                <i class="fas fa-file-invoice"></i>
                                <span>BillSheet</span>
                            </a>  
                            <a href="/fee-payments" class="nav-item-custom">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Payments</span>
                            </a> 
                            <a href="/payroll-periods" class="nav-item-custom">
                                <i class="fas fa-paypal"></i>
                                <span>Payroll</span>
                            </a>
                            <a href="/payslips" class="nav-item-custom">
                                <i class="fas fa-paypal"></i>
                                <span>PaySlip</span>
                            </a> 
                            <a href="/salary-structures" class="nav-item-custom">
                                <i class="fas fa-paypal"></i>
                                <span>salary-structures</span>
                            </a>                    
                        </div>
                    </div>
                            <!-- ASSET MANAGEMENT -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#assetMenu">
                            <i class="fas fa-hand"></i>
                            <span>Asset Manager</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="assetMenu">
                            <a href="assets" class="nav-item-custom">
                                <i class="fas fa-archive"></i>
                                <span>Store Records</span>
                            </a>
                            <a href="#" class="nav-item-custom">
                                <i class="fas fa-upload"></i>
                                <span>Upload Docs</span>
                            </a>
                          
                        </div>
                    </div>

                    <!-- COMMUNICATION -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#communicationMenu">
                            <i class="fas fa-comments"></i>
                            <span>Communication</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="communicationMenu">
                            <a href="/discussions" class="nav-item-custom">
                                <i class="fas fa-envelope"></i>
                                <span>Messages</span>
                            </a>
                            <a href="/announcements" class="nav-item-custom">
                                <i class="fas fa-bullhorn"></i>
                                <span>Announcements</span>
                            </a>
                            <a href="/grievance" class="nav-item-custom">
                                <i class="fas fa-calendar"></i>
                                <span>Staff Gravience</span>
                            </a>
                            <!-- <a href="/student-grievance" class="nav-item-custom">
                                <i class="fas fa-calendar"></i>
                                <span>Student Gravience</span>
                            </a> -->
                        </div>
                    </div>

                    <!-- ADMINISTRATION -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#administrationMenu">
                            <i class="fas fa-users-cog"></i>
                            <span>Administration</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="administrationMenu">
                            <a href="/staff" class="nav-item-custom">
                                <i class="fas fa-users"></i>
                                <span>Staff</span>
                            </a>
                            <a href="/departments" class="nav-item-custom">
                                <i class="fas fa-building"></i>
                                <span>Departments</span>
                            </a>
                            <a href="/leaves" class="nav-item-custom">
                                <i class="fas fa-clock"></i>
                                <span>Leave</span>
                            </a>
                            <a href="/staff-appraisals" class="nav-item-custom">
                                <i class="fas fa-list-check"></i>
                                <span>Appraisals</span>
                            </a>
                        </div>
                    </div>

                     <!-- REPORTS -->
                     <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#reportMenu">
                            <i class="fas fa-hand-lizard"></i>
                            <span>Reports</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="reportMenu">
                            <a href="/staffattendance/monthly-report" class="nav-item-custom">
                                <i class="fas fa-archive"></i>
                                <span>Staff-Attendance</span>
                            </a>
                            <a href="/attendance/monthly-report" class="nav-item-custom">
                                <i class="fas fa-upload"></i>
                                <span>Student-Attendance</span>
                            </a>
                            <a href="/fee-payment-reports" class="nav-item-custom">
                                <i class="fas fa-upload"></i>
                                <span>Student-Fee Payment</span>
                            </a>
                          
                        </div>
                    </div>

                    <!-- SYSTEM -->
                    <div class="nav-section">
                        <a class="nav-item-custom collapsed" data-bs-toggle="collapse" href="#settingsMenu">
                            <i class="fas fa-cogs"></i>
                            <span>Settings</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse nav-collapse" id="settingsMenu">
                            <a href="/users" class="nav-item-custom">
                                <i class="fas fa-users-cog"></i>
                                <span>User Management</span>
                            </a>
                          
                            <a href="/roles-permissions" class="nav-item-custom">
                                <i class="fas fa-user-shield"></i>
                                <span>Roles & Permissions</span>
                            </a>
                            <a href="/attendance-settings" class="nav-item-custom">
                                <i class="fas fa-database"></i>
                                <span>Attendance Settings</span>
                            </a>
                            <a href="/settings" class="nav-item-custom">
                                <i class="fas fa-tools"></i>
                                <span>System Settings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Footer - User Info (Fixed at bottom) -->
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar" id="sidebarUserInitials">AD</div>
                    <div class="sidebar-user-info">
                        <p class="name" id="sidebarUserName">Administrator</p>
                        <p class="role" id="sidebarUserRole">School Administrator</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <button class="btn btn-primary menu-btn rounded-3 px-3 py-2 me-3" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <a class="navbar-brand text-dark fw-bold" href="{{ url('/dashboard') }}">Dashboard</a>
            <div class="date-display">
                <i class="far fa-calendar-alt me-1"></i> 
                <span id="liveDateTime"></span>
            </div>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
                    <li class="nav-item">
                        <div class="greeting-wrapper">
                            <i class="fas fa-sun greeting-icon" id="greetingIcon"></i>
                            <span class="greeting-text" id="greetingMessage">Loading...</span>
                        </div>
                    </li>
                    <li class="nav-item">
                        <button class="darkmode-toggle" id="darkModeToggle">
                            <i class="fas fa-moon" id="darkModeIcon"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link icon-btn position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell fs-5"></i>
                            <span class="notification-badge" id="notificationCount">3</span>
                        </a>
                        <ul class="dropdown-menu notification-dropdown dropdown-menu-end">
                            <div class="notification-header">
                                <i class="fas fa-bell me-2"></i> Notifications
                                <span class="float-end text-primary small" style="cursor:pointer;" id="markAllReadBtn">Mark all read</span>
                            </div>
                            <div id="notificationList">
                                <li class="notification-item"><div>📊 New attendance record</div><small class="text-muted">5 min ago</small></li>
                                <li class="notification-item"><div>💰 Fee payment received</div><small class="text-muted">1 hour ago</small></li>
                                <li class="notification-item"><div>👩‍🏫 New teacher assigned</div><small class="text-muted">3 hours ago</small></li>
                            </div>
                            <div class="text-center p-2"><a href="#" class="small text-decoration-none">View all notifications</a></div>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar" id="userAvatarInitials">AD</div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header text-muted small px-4 py-2"><i class="fas fa-user-circle me-1"></i> <span id="userNameHeader">Administrator</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i> Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content app-content">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="copyright">&copy; {{ date('Y') }} EduNexus USMS. All rights reserved.</div>
            <div><span id="footerGreeting" class="text-muted small"></span></div>
        </div>
    </footer>

    {{-- Bootstrap 5 modals supplied by individual pages --}}
    @stack('modals')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Bootstrap 5.3.3 JavaScript: load exactly once in the master layout --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        (function() {
            // Preloader
            const preloader = document.getElementById('preloader');
            const body = document.body;
            
            function hidePreloaderAndRevealContent() {
                if (preloader) {
                    preloader.classList.add('preloader-hidden');
                    body.classList.remove('preloader-active');
                    body.classList.add('preloader-done');
                    setTimeout(() => {
                        if (preloader.style.display !== 'none') {
                            preloader.style.display = 'none';
                        }
                    }, 600);
                }
            }
            
            body.classList.add('preloader-active');
            
            if (document.readyState === 'complete') {
                setTimeout(hidePreloaderAndRevealContent, 300);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(hidePreloaderAndRevealContent, 500);
                });
                setTimeout(hidePreloaderAndRevealContent, 2500);
            }
            
            // Logout functionality
            window.performLogout = function() {
                const overlay = document.createElement('div');
                overlay.className = 'logout-overlay';
                overlay.innerHTML = `
                    <div class="logout-spinner"></div>
                    <div class="logout-text">Logging out, please wait...</div>
                `;
                document.body.appendChild(overlay);
                sessionStorage.clear();
                const logoutForm = document.getElementById('logout-form-real') || document.getElementById('logout-form');
                if (logoutForm && logoutForm.action && logoutForm.action !== '#') {
                    logoutForm.submit();
                } else {
                    setTimeout(() => { window.location.href = '/login'; }, 800);
                }
            };
            
            let logoutRealForm = document.getElementById('logout-form-real');
            if (!logoutRealForm) {
                logoutRealForm = document.createElement('form');
                logoutRealForm.id = 'logout-form-real';
                logoutRealForm.method = 'POST';
                logoutRealForm.action = '{{ route("logout") }}';
                if (logoutRealForm.action === '') logoutRealForm.action = '/logout';
                logoutRealForm.style.display = 'none';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                logoutRealForm.appendChild(csrfInput);
                document.body.appendChild(logoutRealForm);
            }
            
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) performLogout();
                });
            }
            
            // Live date time
            function updateDateTime() {
                const now = new Date();
                const options = { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' };
                const formatted = now.toLocaleDateString('en-US', options).replace(',', ' |');
                const dateSpan = document.getElementById('liveDateTime');
                if (dateSpan) dateSpan.innerText = formatted;
            }
            updateDateTime();
            setInterval(updateDateTime, 60000);
            
            // Time greeting
            function getTimeBasedGreeting() {
                const hour = new Date().getHours();
                if (hour < 12) return { text: 'Good Morning', icon: 'fa-sun', emoji: '🌅' };
                if (hour < 18) return { text: 'Good Afternoon', icon: 'fa-cloud-sun', emoji: '☀️' };
                return { text: 'Good Evening', icon: 'fa-moon', emoji: '🌙' };
            }
            
            const userNameFromBlade = '{{ Auth::user()->name ?? "Educator" }}';
            const displayUserName = (userNameFromBlade.includes('Guest') || userNameFromBlade === '' || userNameFromBlade === 'Educator') ? 'John Doe' : userNameFromBlade;
            
            function updateGreetings() {
                const greeting = getTimeBasedGreeting();
                const fullMessage = `${greeting.text}, ${displayUserName}! ${greeting.emoji}`;
                const msgEl = document.getElementById('greetingMessage');
                const iconEl = document.getElementById('greetingIcon');
                const footerGreet = document.getElementById('footerGreeting');
                if (msgEl) msgEl.textContent = fullMessage;
                if (iconEl) iconEl.className = `fas ${greeting.icon} greeting-icon`;
                if (footerGreet) footerGreet.textContent = `✨ Have a great ${greeting.text.toLowerCase()}! ✨`;
                
                const avatarDiv = document.getElementById('userAvatarInitials');
                const sidebarAvatar = document.getElementById('sidebarUserInitials');
                const userNameSpan = document.getElementById('userNameHeader');
                const sidebarUserName = document.getElementById('sidebarUserName');
                
                let initials = displayUserName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2);
                if (initials.length === 0) initials = 'JD';
                if (avatarDiv) avatarDiv.textContent = initials;
                if (sidebarAvatar) sidebarAvatar.textContent = initials;
                if (userNameSpan) userNameSpan.textContent = displayUserName;
                if (sidebarUserName) sidebarUserName.textContent = displayUserName;
            }
            
            // Dark mode
            function initDarkMode() {
                const toggle = document.getElementById('darkModeToggle');
                const icon = document.getElementById('darkModeIcon');
                const isDark = localStorage.getItem('darkMode') === 'true';
                if (isDark) {
                    document.body.classList.add('dark-mode');
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
                if (toggle) {
                    toggle.addEventListener('click', () => {
                        document.body.classList.toggle('dark-mode');
                        const darkActive = document.body.classList.contains('dark-mode');
                        localStorage.setItem('darkMode', darkActive);
                        if (darkActive) {
                            icon.classList.remove('fa-moon');
                            icon.classList.add('fa-sun');
                        } else {
                            icon.classList.remove('fa-sun');
                            icon.classList.add('fa-moon');
                        }
                        icon.style.transform = 'rotate(20deg)';
                        setTimeout(() => { if(icon) icon.style.transform = ''; }, 200);
                    });
                }
            }
            
            // Notifications
            const markBtn = document.getElementById('markAllReadBtn');
            if (markBtn) {
                markBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notifContainer = document.getElementById('notificationList');
                    const badge = document.getElementById('notificationCount');
                    if (notifContainer) {
                        notifContainer.style.opacity = '0.5';
                        setTimeout(() => {
                            notifContainer.innerHTML = '<div class="text-center py-4 text-muted small"><i class="fas fa-check-circle me-2"></i>All caught up!</div>';
                            notifContainer.style.opacity = '1';
                            if (badge) {
                                badge.textContent = '0';
                                badge.style.display = 'none';
                            }
                        }, 200);
                    }
                });
            }
            
            // Initialize everything
            updateGreetings();
            initDarkMode();
            setInterval(updateGreetings, 60000);

            /*
             * Bootstrap integrity guard.
             * Bootstrap's bundle is loaded once by this master layout.
             * Page blades must NOT load another Bootstrap CSS/JS version.
             */
            window.USMSBootstrap = window.bootstrap || null;

            // Bootstrap 5 manages modal/backdrop lifecycle itself.
            // Do not manually remove .modal-backdrop or modal-open here;
            // doing so can break modal transitions and focus handling.
        })();
    </script>
    @stack('scripts')
</body>
</html>
