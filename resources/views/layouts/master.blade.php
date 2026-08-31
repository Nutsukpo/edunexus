<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TalhaPremier USMS')</title>

    {{-- =========================================================
         CORE LIBRARIES
         Bootstrap is retained for the rest of the application.
         The sidebar itself DOES NOT use Bootstrap Offcanvas/Collapse.
    ========================================================== --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="icon" type="image/png" href="{{ asset('img/Talha.jpeg') }}">

    @yield('styles')
    @stack('styles')

    <style>
        :root {
            --brand-dark: #062654;
            --brand: #1557a6;
            --brand-light: #3d8bd9;
            --brand-soft: #eaf3ff;
            --page-bg: #f5f8fc;
            --border: #e4ebf3;
            --text: #172033;
            --muted: #718096;
            --sidebar-width: 310px;
            --navbar-height: 70px;
            --speed: .22s;
        }

        * { box-sizing: border-box; }

        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { text-decoration: none; }

        /* =========================================================
           PRELOADER
        ========================================================== */
        #preloader {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f3f8ff, #fff);
            transition: opacity .35s ease, visibility .35s ease;
        }

        #preloader.preloader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-container { text-align: center; }

        .loader {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            border: 5px solid rgba(21,87,166,.12);
            border-top-color: var(--brand);
            border-right-color: var(--brand-light);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        .loader-dots {
            display: flex;
            justify-content: center;
            gap: 7px;
            margin-bottom: 14px;
        }

        .loader-dots .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--brand);
            animation: pulse 1.2s infinite ease-in-out;
        }

        .loader-dots .dot:nth-child(2) { animation-delay: .15s; }
        .loader-dots .dot:nth-child(3) { animation-delay: .3s; }

        .loader-text {
            display: inline-block;
            padding: 7px 18px;
            border-radius: 30px;
            background: var(--brand-soft);
            color: var(--brand-dark);
            font-weight: 800;
        }

        .loader-subtext {
            margin-top: 8px;
            color: var(--muted);
            font-size: .72rem;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse {
            0%,100% { opacity: .3; transform: scale(.8); }
            50% { opacity: 1; transform: scale(1); }
        }

        /* =========================================================
           NAVBAR
        ========================================================== */
        .app-navbar {
            position: sticky;
            top: 0;
            z-index: 2000;
            min-height: var(--navbar-height);
            background: rgba(255,255,255,.97);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
        }

        .navbar-inner {
            min-height: var(--navbar-height);
            padding: 0 1.1rem;
        }

        .sidebar-trigger {
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand-light));
            box-shadow: 0 7px 18px rgba(21,87,166,.22);
            cursor: pointer;
        }

        .sidebar-trigger:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .navbar-title {
            color: var(--text);
            font-size: 1rem;
            font-weight: 800;
        }

        .date-display,
        .greeting-wrapper {
            padding: .42rem .8rem;
            border-radius: 30px;
            background: #f1f5f9;
            color: #526174;
            font-size: .73rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .greeting-wrapper {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .greeting-icon { color: #e8a317; }

        .darkmode-toggle,
        .icon-btn {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #536174;
            cursor: pointer;
        }

        .darkmode-toggle:hover,
        .icon-btn:hover {
            background: #eef3f8;
            color: var(--brand);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand-light));
            color: #fff;
            font-size: .85rem;
            font-weight: 800;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dc3545;
            color: #fff;
            font-size: .58rem;
            font-weight: 800;
        }

        .notification-dropdown {
            width: 340px;
            padding: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 18px 45px rgba(15,23,42,.14);
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
        }

        .notification-item {
            padding: .8rem 1rem;
            border-bottom: 1px solid #eef2f6;
        }

        /* =========================================================
           NEW SIDEBAR ARCHITECTURE
           IMPORTANT:
           - NOT Bootstrap Offcanvas.
           - NOT Bootstrap Collapse.
           - Dropdown state is controlled only by this page's JS.
           - Clicking a dropdown NEVER closes the drawer.
           - No document click handler closes the drawer.
           - No backdrop click closes the drawer.
           - The drawer closes only from the close button or menu button.
           - A normal link navigates to a new page; that page starts closed.
        ========================================================== */

        .sidebar-shell {
            position: fixed;
            inset: 0;
            z-index: 5000;
            pointer-events: none;
            visibility: hidden;
        }

        .sidebar-shell.is-open {
            pointer-events: auto;
            visibility: visible;
        }

        .sidebar-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: min(var(--sidebar-width), 92vw);
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #061f46 0%, #0b3974 50%, #125cae 100%);
            box-shadow: 18px 0 50px rgba(0,0,0,.28);
            transform: translateX(-105%);
            transition: transform var(--speed) ease;
        }

        .sidebar-shell.is-open .sidebar-panel {
            transform: translateX(0);
        }

        .sidebar-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(3,14,30,.38);
            opacity: 0;
            transition: opacity var(--speed) ease;
        }

        .sidebar-shell.is-open .sidebar-backdrop {
            opacity: 1;
        }

        .sidebar-head {
            flex: 0 0 auto;
            min-height: 82px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 1rem 1rem .9rem;
            border-bottom: 1px solid rgba(255,255,255,.09);
            background: rgba(0,0,0,.12);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .sidebar-brand-icon {
            width: 45px;
            height: 45px;
            flex: 0 0 45px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #4b9be0, #174d94);
            box-shadow: 0 7px 20px rgba(0,0,0,.18);
        }

        .sidebar-brand-text { min-width: 0; }

        .sidebar-brand-text h5 {
            margin: 0;
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
        }

        .sidebar-brand-text h5 span { color: #7fc2f7; }

        .sidebar-brand-text small {
            display: block;
            margin-top: 3px;
            color: rgba(255,255,255,.54);
            font-size: .57rem;
        }

        .sidebar-close {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.06);
            cursor: pointer;
        }

        .sidebar-close:hover {
            color: #fff;
            background: rgba(255,255,255,.12);
        }

        .sidebar-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: .9rem .8rem 1rem;
        }

        .sidebar-content::-webkit-scrollbar { width: 5px; }
        .sidebar-content::-webkit-scrollbar-track { background: rgba(255,255,255,.03); }
        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.17);
            border-radius: 10px;
        }

        .nav-section {
            margin-bottom: .85rem;
        }

        .nav-section-title {
            padding: .25rem .7rem .4rem;
            color: rgba(255,255,255,.35);
            font-size: .57rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .nav-link-main,
        .nav-link-child {
            width: 100%;
            border: 0;
            display: flex;
            align-items: center;
            gap: 11px;
            color: rgba(255,255,255,.78);
            background: transparent;
            cursor: pointer;
            text-align: left;
        }

        .nav-link-main {
            min-height: 44px;
            padding: .65rem .78rem;
            border-radius: 12px;
            font-size: .8rem;
            font-weight: 600;
        }

        .nav-link-main:hover {
            color: #fff;
            background: rgba(255,255,255,.09);
        }

        .nav-link-main.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(61,139,217,.42), rgba(21,87,166,.55));
            border-left: 3px solid #82c5f6;
        }

        .nav-link-main > i:first-child,
        .nav-link-child > i:first-child {
            width: 21px;
            text-align: center;
            color: rgba(255,255,255,.55);
        }

        .nav-link-main:hover > i:first-child,
        .nav-link-main.active > i:first-child {
            color: #8dccf8;
        }

        .nav-chevron {
            margin-left: auto;
            font-size: .65rem;
            transition: transform .18s ease;
        }

        .nav-group.is-expanded > .nav-link-main .nav-chevron {
            transform: rotate(180deg);
        }

        .nav-children {
            display: none;
            margin: .15rem 0 0 .72rem;
            padding-left: .55rem;
            border-left: 1px solid rgba(255,255,255,.10);
        }

        .nav-group.is-expanded > .nav-children {
            display: block;
        }

        .nav-link-child {
            min-height: 38px;
            padding: .48rem .65rem;
            border-radius: 0 9px 9px 0;
            font-size: .75rem;
        }

        .nav-link-child:hover {
            color: #fff;
            background: rgba(255,255,255,.07);
        }

        .nav-link-child.active {
            color: #fff;
            background: rgba(61,139,217,.18);
            border-left: 2px solid #82c5f6;
        }

        .sidebar-user {
            flex: 0 0 auto;
            padding: .8rem .95rem;
            border-top: 1px solid rgba(255,255,255,.08);
            background: rgba(0,0,0,.17);
        }

        .sidebar-user-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #3d8bd9, #174d94);
            font-size: .78rem;
            font-weight: 800;
        }

        .sidebar-user-name {
            margin: 0;
            color: #fff;
            font-size: .76rem;
            font-weight: 700;
        }

        .sidebar-user-role {
            margin: 2px 0 0;
            color: rgba(255,255,255,.5);
            font-size: .58rem;
        }

        /* =========================================================
           CONTENT / FOOTER
        ========================================================== */
        .app-content {
            width: 100%;
            min-height: calc(100vh - var(--navbar-height));
            padding: 1.25rem;
        }

        .footer {
            padding: 1rem 1.4rem;
            background: #fff;
            border-top: 1px solid var(--border);
        }

        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* =========================================================
           LOGOUT
        ========================================================== */
        .logout-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 18px;
            background: rgba(0,0,0,.82);
            backdrop-filter: blur(7px);
        }

        .logout-spinner {
            width: 52px;
            height: 52px;
            border: 4px solid rgba(255,255,255,.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .logout-text {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
        }

        /* =========================================================
           DARK MODE
        ========================================================== */
        body.dark-mode {
            --page-bg: #0f172a;
            --text: #e5edf7;
            --muted: #94a3b8;
            --border: #26344a;
            background: #0f172a;
            color: #e5edf7;
        }

        body.dark-mode .app-navbar,
        body.dark-mode .footer {
            background: #111b2d;
            border-color: #26344a;
        }

        body.dark-mode .navbar-title,
        body.dark-mode .icon-btn,
        body.dark-mode .darkmode-toggle {
            color: #e5edf7;
        }

        body.dark-mode .date-display,
        body.dark-mode .greeting-wrapper {
            background: #1e293b;
            color: #cbd5e1;
        }

        body.dark-mode .dropdown-menu {
            background: #172033;
            border-color: #26344a;
        }

        body.dark-mode .dropdown-item { color: #e5edf7; }
        body.dark-mode .dropdown-item:hover { background: #26344a; }

        @media (max-width: 991.98px) {
            :root {
                --navbar-height: 64px;
                --sidebar-width: 292px;
            }

            .navbar-inner { min-height: 64px; }
            .greeting-text, .date-display { display: none; }
            .app-content { padding: 1rem; }
        }

        @media (max-width: 575.98px) {
            :root { --sidebar-width: 285px; }

            .app-content { padding: .75rem; }

            .navbar-title {
                max-width: 130px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .notification-dropdown {
                width: min(340px, calc(100vw - 20px));
            }

            .sidebar-trigger,
            .darkmode-toggle,
            .icon-btn,
            .user-avatar {
                width: 39px;
                height: 39px;
            }
        }
    </style>
</head>

<body class="preloader-active">

    {{-- =========================================================
         PRELOADER
    ========================================================== --}}
    <div id="preloader" aria-hidden="true">
        <div class="loader-container">
            <div class="loader"></div>
            <div class="loader-dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            <div class="loader-text">Talha Prem USMS</div>
            <div class="loader-subtext">
                Loading at {{ now()->format('h:i:s A') }} ...
            </div>
        </div>
    </div>

    {{-- =========================================================
         SIDEBAR
         TOTALLY INDEPENDENT OF BOOTSTRAP OFFCANVAS/COLLAPSE.
         It starts hidden on EVERY PAGE LOAD.
    ========================================================== --}}
    <aside class="sidebar-shell" id="sidebarShell" aria-hidden="true">

        {{-- This backdrop is visual only. It DOES NOT close the sidebar. --}}
        <div class="sidebar-backdrop" aria-hidden="true"></div>

        <div class="sidebar-panel" id="sidebarPanel">

            <div class="sidebar-head">
                <div class="sidebar-brand">
                    <div class="sidebar-brand-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="sidebar-brand-text">
                        <h5>Talha<span>Premier</span></h5>
                        <small>Universal School Management System</small>
                    </div>
                </div>

                <button type="button"
                        class="sidebar-close"
                        id="sidebarClose"
                        aria-label="Close navigation">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sidebar-content">

                {{-- DASHBOARD --}}
                <div class="nav-section">
                    <div class="nav-section-title">Dashboard</div>

                    <a href="{{ url('/dashboard') }}"
                       class="nav-link-main {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                {{-- STUDENT MANAGEMENT --}}
                <div class="nav-section nav-group" data-menu-group="students">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/students" class="nav-link-child">
                            <i class="fas fa-list"></i>
                            <span>Admissions</span>
                        </a>

                        <a href="/student-class-assignments" class="nav-link-child">
                            <i class="fas fa-building"></i>
                            <span>Students Class Enrolled</span>
                        </a>

                        <a href="/student-progressions" class="nav-link-child">
                            <i class="fas fa-level-up-alt"></i>
                            <span>Promotions</span>
                        </a>

                        <a href="/graduated-students" class="nav-link-child">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Graduated Students</span>
                        </a>
                    </div>
                </div>

                {{-- ACADEMICS --}}
                <div class="nav-section nav-group" data-menu-group="academics">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-book-open"></i>
                        <span>Academics</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/student-classes" class="nav-link-child">
                            <i class="fas fa-building"></i>
                            <span>Class/Form</span>
                        </a>

                        <a href="/lesson-notes" class="nav-link-child">
                            <i class="fas fa-sticky-note"></i>
                            <span>Lesson Notes</span>
                        </a>

                        <a href="/academic-years" class="nav-link-child">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Academic Years</span>
                        </a>

                        <a href="/terms" class="nav-link-child">
                            <i class="fas fa-calendar-check"></i>
                            <span>Terms</span>
                        </a>

                        <a href="/subjects" class="nav-link-child">
                            <i class="fas fa-book"></i>
                            <span>Subjects</span>
                        </a>

                        <a href="/timetables" class="nav-link-child">
                            <i class="fas fa-clock"></i>
                            <span>Timetable</span>
                        </a>
                    </div>
                </div>

                {{-- ASSESSMENT --}}
                <div class="nav-section nav-group" data-menu-group="assessment">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Assessment</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/scores" class="nav-link-child">
                            <i class="fas fa-chart-line"></i>
                            <span>Subject Scores</span>
                        </a>

                        <a href="/assessment-forms" class="nav-link-child">
                            <i class="fas fa-upload"></i>
                            <span>Assessment Form</span>
                        </a>
                    </div>
                </div>

                {{-- RESULTS --}}
                <div class="nav-section nav-group" data-menu-group="results">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-chart-bar"></i>
                        <span>Results</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/broadsheet" class="nav-link-child">
                            <i class="fas fa-table"></i>
                            <span>Class Results</span>
                        </a>

                        <a href="/subject-results" class="nav-link-child">
                            <i class="fas fa-chart-line"></i>
                            <span>Subject Results</span>
                        </a>

                        <a href="/report-cards" class="nav-link-child">
                            <i class="fas fa-id-card"></i>
                            <span>Report Cards</span>
                        </a>
                    </div>
                </div>

                {{-- ATTENDANCE --}}
                <div class="nav-section nav-group" data-menu-group="attendance">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/attendance-sessions" class="nav-link-child">
                            <i class="fas fa-user-check"></i>
                            <span>Student Attendance</span>
                        </a>

                        <a href="/staff-attendance" class="nav-link-child">
                            <i class="fas fa-users"></i>
                            <span>Staff Attendance</span>
                        </a>

                        <a href="/staffattendance-live-map" class="nav-link-child">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Attendance Map</span>
                        </a>
                    </div>
                </div>

                {{-- APPROVALS --}}
                <div class="nav-section nav-group" data-menu-group="approvals">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-check-double"></i>
                        <span>Approvals</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/payroll-period-approvals" class="nav-link-child">
                            <i class="fas fa-paypal"></i>
                            <span>Payroll</span>
                        </a>

                        <a href="/leave-approvals" class="nav-link-child">
                            <i class="fas fa-clock"></i>
                            <span>Leaves</span>
                        </a>

                        <a href="/approvals" class="nav-link-child">
                            <i class="fas fa-sticky-note"></i>
                            <span>Lesson Note</span>
                        </a>

                        <a href="/bill-sheet-approvals" class="nav-link-child">
                            <i class="fas fa-file-invoice"></i>
                            <span>Bill Sheets</span>
                        </a>
                    </div>
                </div>

                {{-- FINANCE --}}
                <div class="nav-section nav-group" data-menu-group="finance">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-wallet"></i>
                        <span>Fees &amp; Payments</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">

                        <!-- <a href="/fee-categories" class="nav-link-child">
                            <i class="fas fa-credit-card"></i>
                            <span>Fee Categories</span>
                        </a> -->

                        <!-- <a href="/class-fee-structures" class="nav-link-child">
                            <i class="fas fa-refresh"></i>
                            <span>Fee Structure</span>
                        </a> -->

                        <a href="/bill-sheets" class="nav-link-child">
                            <i class="fas fa-file-invoice"></i>
                            <span>BillSheet</span>
                        </a>

                        <a href="/fee-payments" class="nav-link-child">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payments</span>
                        </a>

                        <a href="/payroll-periods" class="nav-link-child">
                            <i class="fas fa-paypal"></i>
                            <span>Payroll</span>
                        </a>

                        <a href="/payslips" class="nav-link-child">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>PaySlip</span>
                        </a>

                        <a href="/salary-structures" class="nav-link-child">
                            <i class="fas fa-coins"></i>
                            <span>Salary Structures</span>
                        </a>
                    </div>
                </div>

                {{-- ASSET MANAGEMENT --}}
                <div class="nav-section nav-group" data-menu-group="assets">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-boxes-stacked"></i>
                        <span>Asset Manager</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/assets" class="nav-link-child">
                            <i class="fas fa-archive"></i>
                            <span>Store Records</span>
                        </a>

                        <a href="#" class="nav-link-child">
                            <i class="fas fa-upload"></i>
                            <span>Upload Docs</span>
                        </a>
                    </div>
                </div>

                {{-- COMMUNICATION --}}
                <div class="nav-section nav-group" data-menu-group="communication">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-comments"></i>
                        <span>Communication</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/discussions" class="nav-link-child">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                        </a>

                        <a href="/announcements" class="nav-link-child">
                            <i class="fas fa-bullhorn"></i>
                            <span>Announcements</span>
                        </a>

                        <a href="/grievance" class="nav-link-child">
                            <i class="fas fa-calendar"></i>
                            <span>Staff Gravience</span>
                        </a>

                        <!-- <a href="/student-grievance" class="nav-link-child">
                            <i class="fas fa-calendar"></i>
                            <span>Student Gravience</span>
                        </a> -->
                    </div>
                </div>

                {{-- ADMINISTRATION --}}
                <div class="nav-section nav-group" data-menu-group="administration">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-users-cog"></i>
                        <span>Administration</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/staff" class="nav-link-child">
                            <i class="fas fa-users"></i>
                            <span>Staff</span>
                        </a>

                        <a href="/departments" class="nav-link-child">
                            <i class="fas fa-building"></i>
                            <span>Departments</span>
                        </a>

                        <a href="/leaves" class="nav-link-child">
                            <i class="fas fa-clock"></i>
                            <span>Leave</span>
                        </a>

                        <a href="/staff-appraisals" class="nav-link-child">
                            <i class="fas fa-list-check"></i>
                            <span>Appraisals</span>
                        </a>
                    </div>
                </div>

                {{-- REPORTS --}}
                <div class="nav-section nav-group" data-menu-group="reports">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-chart-pie"></i>
                        <span>Reports</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/staffattendance/monthly-report" class="nav-link-child">
                            <i class="fas fa-archive"></i>
                            <span>Staff-Attendance</span>
                        </a>

                        <a href="/attendance/monthly-report" class="nav-link-child">
                            <i class="fas fa-upload"></i>
                            <span>Student-Attendance</span>
                        </a>

                        <a href="/fee-payment-reports" class="nav-link-child">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Student-Fee Payment</span>
                        </a>
                    </div>
                </div>

                {{-- SYSTEM --}}
                <div class="nav-section nav-group" data-menu-group="settings">
                    <button type="button" class="nav-link-main nav-toggle">
                        <i class="fas fa-cogs"></i>
                        <span>Settings</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>

                    <div class="nav-children">
                        <a href="/users" class="nav-link-child">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                        </a>

                        <a href="/roles-permissions" class="nav-link-child">
                            <i class="fas fa-user-shield"></i>
                            <span>Roles &amp; Permissions</span>
                        </a>

                        <a href="/attendance-settings" class="nav-link-child">
                            <i class="fas fa-database"></i>
                            <span>Attendance Settings</span>
                        </a>

                        <a href="/settings" class="nav-link-child">
                            <i class="fas fa-tools"></i>
                            <span>System Settings</span>
                        </a>
                    </div>
                </div>

            </div>

            {{-- SIDEBAR USER FOOTER --}}
            <div class="sidebar-user">
                <div class="sidebar-user-inner">
                    <div class="sidebar-user-avatar" id="sidebarUserInitials">AD</div>
                    <div>
                        <p class="sidebar-user-name" id="sidebarUserName">Administrator</p>
                        <p class="sidebar-user-role" id="sidebarUserRole">School Administrator</p>
                    </div>
                </div>
            </div>

        </div>
    </aside>

    {{-- =========================================================
         NAVBAR
    ========================================================== --}}
    <nav class="navbar app-navbar">
        <div class="container-fluid navbar-inner">

            <div class="d-flex align-items-center gap-3">

                {{-- ONLY SIDEBAR OPEN BUTTON --}}
                <button type="button"
                        class="sidebar-trigger"
                        id="sidebarTrigger"
                        aria-label="Open navigation"
                        aria-controls="sidebarShell"
                        aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>

                <a href="{{ url('/dashboard') }}" class="navbar-title">
                    Dashboard
                </a>

                <div class="date-display">
                    <i class="far fa-calendar-alt me-1"></i>
                    <span id="liveDateTime"></span>
                </div>

            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">

                <div class="greeting-wrapper">
                    <i class="fas fa-sun greeting-icon" id="greetingIcon"></i>
                    <span class="greeting-text" id="greetingMessage">Loading...</span>
                </div>

                <button type="button"
                        class="darkmode-toggle"
                        id="darkModeToggle"
                        aria-label="Toggle dark mode">
                    <i class="fas fa-moon" id="darkModeIcon"></i>
                </button>

                {{-- NOTIFICATIONS --}}
                <div class="dropdown">
                    <button type="button"
                            class="icon-btn position-relative"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="Notifications">
                        <i class="fas fa-bell fs-5"></i>
                        <span class="notification-badge" id="notificationCount">3</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="notification-header">
                            <i class="fas fa-bell me-2"></i>
                            Notifications
                            <span class="float-end text-primary small"
                                  style="cursor:pointer"
                                  id="markAllReadBtn">
                                Mark all read
                            </span>
                        </div>

                        <div id="notificationList">
                            <div class="notification-item">
                                <div>📊 New attendance record</div>
                                <small class="text-muted">5 min ago</small>
                            </div>

                            <div class="notification-item">
                                <div>💰 Fee payment received</div>
                                <small class="text-muted">1 hour ago</small>
                            </div>

                            <div class="notification-item">
                                <div>👩‍🏫 New staff assigned</div>
                                <small class="text-muted">3 hours ago</small>
                            </div>
                        </div>

                        <div class="text-center p-2">
                            <a href="#" class="small text-decoration-none">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                {{-- USER MENU --}}
                <div class="dropdown">
                    <a href="#"
                       class="nav-link p-0"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <div class="user-avatar" id="userAvatarInitials">AD</div>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="dropdown-header text-muted small px-4 py-2">
                            <i class="fas fa-user-circle me-1"></i>
                            <span id="userNameHeader">Administrator</span>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i>
                                My Profile
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-key me-2"></i>
                                Change Password
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </nav>

    {{-- PAGE CONTENT --}}
    <main class="app-content">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="footer-content">
            <div class="copyright small">
                &copy; {{ date('Y') }} EduNexus USMS. All rights reserved.
            </div>
            <div>
                <span id="footerGreeting" class="text-muted small"></span>
            </div>
        </div>
    </footer>

    {{-- Page-specific Bootstrap modals --}}
    @stack('modals')

    {{-- =========================================================
         JAVASCRIPT
         Bootstrap remains available for child-page components.
         SIDEBAR JAVASCRIPT IS COMPLETELY CUSTOM.
    ========================================================== --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (() => {
            'use strict';

            /* =====================================================
               PRELOADER
            ====================================================== */
            const preloader = document.getElementById('preloader');

            function hidePreloader() {
                if (!preloader) return;

                preloader.classList.add('preloader-hidden');
                document.body.classList.remove('preloader-active');

                window.setTimeout(() => {
                    if (preloader) preloader.style.display = 'none';
                }, 400);
            }

            window.addEventListener('load', () => {
                window.setTimeout(hidePreloader, 300);
            }, { once: true });

            window.setTimeout(hidePreloader, 2500);

            /* =====================================================
               SIDEBAR
               CRITICAL DESIGN RULE:
               NOTHING HERE CLOSES THE SIDEBAR WHEN A DROPDOWN
               OR CHILD LINK IS CLICKED.

               A page link causes normal browser navigation.
               The newly loaded page creates a fresh closed sidebar.
            ====================================================== */
            const sidebarShell = document.getElementById('sidebarShell');
            const sidebarTrigger = document.getElementById('sidebarTrigger');
            const sidebarClose = document.getElementById('sidebarClose');

            function openSidebar() {
                if (!sidebarShell) return;

                sidebarShell.classList.add('is-open');
                sidebarShell.setAttribute('aria-hidden', 'false');

                if (sidebarTrigger) {
                    sidebarTrigger.setAttribute('aria-expanded', 'true');
                }

                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (!sidebarShell) return;

                sidebarShell.classList.remove('is-open');
                sidebarShell.setAttribute('aria-hidden', 'true');

                if (sidebarTrigger) {
                    sidebarTrigger.setAttribute('aria-expanded', 'false');
                }

                document.body.style.overflow = '';
            }

            if (sidebarTrigger) {
                sidebarTrigger.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (sidebarShell.classList.contains('is-open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (sidebarClose) {
                sidebarClose.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    closeSidebar();
                });
            }

            /*
             * IMPORTANT:
             * There is intentionally NO click listener on document,
             * sidebarShell, sidebarPanel, sidebar children, or links
             * that closes the sidebar.
             *
             * The visual backdrop is also intentionally NOT clickable.
             */

            /* =====================================================
               SIDEBAR DROPDOWNS
               CUSTOM JS ONLY — NO BOOTSTRAP COLLAPSE.
            ====================================================== */
            document.querySelectorAll('.nav-toggle').forEach(toggle => {
                toggle.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();

                    const group = toggle.closest('.nav-group');
                    if (!group) return;

                    /*
                     * Only this dropdown's open/closed state changes.
                     * The sidebar itself is NEVER touched here.
                     */
                    group.classList.toggle('is-expanded');
                });
            });

            /* =====================================================
               ACTIVE MENU
               Highlight current page without closing the drawer.
            ====================================================== */
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

            document.querySelectorAll('.nav-link-child[href]').forEach(link => {
                const href = link.getAttribute('href');

                if (!href || href === '#') return;

                try {
                    const linkPath = new URL(href, window.location.origin)
                        .pathname
                        .replace(/\/+$/, '') || '/';

                    if (
                        currentPath === linkPath ||
                        (linkPath !== '/' && currentPath.startsWith(linkPath + '/'))
                    ) {
                        link.classList.add('active');

                        const group = link.closest('.nav-group');
                        if (group) group.classList.add('is-expanded');
                    }
                } catch (error) {
                    /* Invalid navigation target: leave it untouched. */
                }
            });

            /* =====================================================
               ESCAPE KEY
               ESC is treated like the explicit close button.
               It does NOT affect dropdowns.
            ====================================================== */
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' &&
                    sidebarShell &&
                    sidebarShell.classList.contains('is-open')) {
                    closeSidebar();
                }
            });

            /* =====================================================
               LOGOUT
            ====================================================== */
            window.performLogout = function () {
                const overlay = document.createElement('div');

                overlay.className = 'logout-overlay';
                overlay.innerHTML = `
                    <div class="logout-spinner"></div>
                    <div class="logout-text">Logging out, please wait...</div>
                `;

                document.body.appendChild(overlay);
                sessionStorage.clear();

                const logoutForm =
                    document.getElementById('logout-form-real') ||
                    document.getElementById('logout-form');

                if (logoutForm && logoutForm.action && logoutForm.action !== '#') {
                    logoutForm.submit();
                } else {
                    window.setTimeout(() => {
                        window.location.href = '/login';
                    }, 800);
                }
            };

            let logoutRealForm = document.getElementById('logout-form-real');

            if (!logoutRealForm) {
                logoutRealForm = document.createElement('form');
                logoutRealForm.id = 'logout-form-real';
                logoutRealForm.method = 'POST';
                logoutRealForm.action = @json(route('logout'));
                logoutRealForm.style.display = 'none';

                const csrfToken =
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;

                logoutRealForm.appendChild(csrfInput);
                document.body.appendChild(logoutRealForm);
            }

            const logoutBtn = document.getElementById('logoutBtn');

            if (logoutBtn) {
                logoutBtn.addEventListener('click', event => {
                    event.preventDefault();

                    if (window.confirm('Are you sure you want to logout?')) {
                        window.performLogout();
                    }
                });
            }

            /* =====================================================
               LIVE DATE / TIME
            ====================================================== */
            function updateDateTime() {
                const element = document.getElementById('liveDateTime');
                if (!element) return;

                const now = new Date();

                element.textContent = now.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(',', ' |');
            }

            updateDateTime();
            window.setInterval(updateDateTime, 60000);

            /* =====================================================
               GREETING / USER
            ====================================================== */
            const userNameFromBlade = @json(Auth::user()->name ?? 'Administrator');

            const displayUserName =
                userNameFromBlade &&
                userNameFromBlade !== 'Guest'
                    ? userNameFromBlade
                    : 'Administrator';

            function getGreeting() {
                const hour = new Date().getHours();

                if (hour < 12) {
                    return { text: 'Good Morning', icon: 'fa-sun', emoji: '🌅' };
                }

                if (hour < 18) {
                    return { text: 'Good Afternoon', icon: 'fa-cloud-sun', emoji: '☀️' };
                }

                return { text: 'Good Evening', icon: 'fa-moon', emoji: '🌙' };
            }

            function updateGreetings() {
                const greeting = getGreeting();

                const message = document.getElementById('greetingMessage');
                const icon = document.getElementById('greetingIcon');
                const footer = document.getElementById('footerGreeting');
                const avatar = document.getElementById('userAvatarInitials');
                const sidebarAvatar = document.getElementById('sidebarUserInitials');
                const headerName = document.getElementById('userNameHeader');
                const sidebarName = document.getElementById('sidebarUserName');

                if (message) {
                    message.textContent =
                        `${greeting.text}, ${displayUserName}! ${greeting.emoji}`;
                }

                if (icon) {
                    icon.className = `fas ${greeting.icon} greeting-icon`;
                }

                if (footer) {
                    footer.textContent =
                        `✨ Have a great ${greeting.text.toLowerCase()}! ✨`;
                }

                const initials = displayUserName
                    .trim()
                    .split(/\s+/)
                    .map(part => part.charAt(0))
                    .join('')
                    .toUpperCase()
                    .substring(0, 2) || 'AD';

                if (avatar) avatar.textContent = initials;
                if (sidebarAvatar) sidebarAvatar.textContent = initials;
                if (headerName) headerName.textContent = displayUserName;
                if (sidebarName) sidebarName.textContent = displayUserName;
            }

            updateGreetings();
            window.setInterval(updateGreetings, 60000);

            /* =====================================================
               DARK MODE
            ====================================================== */
            function initDarkMode() {
                const toggle = document.getElementById('darkModeToggle');
                const icon = document.getElementById('darkModeIcon');

                if (!toggle || !icon) return;

                const isDark = localStorage.getItem('darkMode') === 'true';

                if (isDark) {
                    document.body.classList.add('dark-mode');
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }

                toggle.addEventListener('click', () => {
                    document.body.classList.toggle('dark-mode');

                    const darkActive =
                        document.body.classList.contains('dark-mode');

                    localStorage.setItem('darkMode', darkActive);

                    icon.classList.toggle('fa-moon', !darkActive);
                    icon.classList.toggle('fa-sun', darkActive);
                });
            }

            initDarkMode();

            /* =====================================================
               NOTIFICATIONS
            ====================================================== */
            const markAllReadBtn =
                document.getElementById('markAllReadBtn');

            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();

                    const list = document.getElementById('notificationList');
                    const count = document.getElementById('notificationCount');

                    if (!list) return;

                    list.innerHTML = `
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-check-circle me-2"></i>
                            All caught up!
                        </div>
                    `;

                    if (count) {
                        count.textContent = '0';
                        count.style.display = 'none';
                    }
                });
            }

        })();
    </script>

    @stack('scripts')
</body>
</html>
