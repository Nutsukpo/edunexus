{{--
    Student Portal Sidebar
    Requires: Bootstrap 5, Font Awesome 5/6
    Optional variables (e.g. via a View Composer):
      $feesDue            (bool)  – shows a "Due" pill on School Fees
      $grievanceCount     (int)   – open grievances count
      $unreadAnnouncements(int)   – unread announcements count
    Your page content wrapper should use:  margin-left: 260px  on md+ screens
    (e.g. <main class="portal-content">).
--}}

@php
    $user = Auth::user();
    $userName = $user->name ?? 'Student';
    $initials = collect(explode(' ', trim($userName)))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $sections = [
        'Overview' => [
            ['label' => 'Dashboard', 'icon' => 'fa-house', 'href' => route('students.dashboard'), 'active' => request()->routeIs('students.dashboard')],
            ['label' => 'My Profile', 'icon' => 'fa-user', 'href' => url('/student/profile'), 'active' => request()->is('student/profile*')],
        ],
        'Academics' => [
            ['label' => 'Attendance', 'icon' => 'fa-calendar-check', 'href' => url('/student/attendance'), 'active' => request()->is('student/attendance*')],
            ['label' => 'Results', 'icon' => 'fa-chart-line', 'href' => url('/student/results'), 'active' => request()->is('student/results*')],
            ['label' => 'Academic History', 'icon' => 'fa-clock-rotate-left', 'href' => url('/student/class-history'), 'active' => request()->is('student/class-history*')],
            ['label' => 'Timetable', 'icon' => 'fa-table-cells', 'href' => url('/student/timetable'), 'active' => request()->is('student/timetable*')],
        ],
        'Services' => [
            ['label' => 'School Fees', 'icon' => 'fa-wallet', 'href' => url('/student/fees'), 'active' => request()->is('student/fees*'),
             'badge' => !empty($feesDue) ? 'Due' : null, 'badgeClass' => 'is-danger'],
            ['label' => 'Announcements', 'icon' => 'fa-bullhorn', 'href' => url('/student/announcements'), 'active' => request()->is('student/announcements*'),
             'badge' => !empty($unreadAnnouncements) ? $unreadAnnouncements : null, 'badgeClass' => ''],
            ['label' => 'Grievance', 'icon' => 'fa-comment-dots', 'href' => url('/student-grievance'), 'active' => request()->is('student-grievance*'),
             'badge' => !empty($grievanceCount) ? $grievanceCount : null, 'badgeClass' => ''],
        ],
    ];
@endphp

<style>
    :root {
        --sb-width: 260px;
        --sb-bg: #0b2447;
        --sb-bg-2: #123a73;
        --sb-text: rgba(255, 255, 255, .72);
        --sb-text-strong: #fff;
        --sb-accent: #4da3ff;
        --sb-hover: rgba(255, 255, 255, .07);
        --sb-active: rgba(77, 163, 255, .16);
        --sb-border: rgba(255, 255, 255, .09);
    }

    .portal-content { margin-left: 0; }
    @media (min-width: 768px) { .portal-content { margin-left: var(--sb-width); } }

    .portal-sidebar {
        position: fixed; inset: 0 auto 0 0;
        width: var(--sb-width);
        display: flex; flex-direction: column;
        background: linear-gradient(180deg, var(--sb-bg) 0%, var(--sb-bg-2) 100%);
        color: var(--sb-text);
        z-index: 1040;
        transition: transform .28s ease;
        box-shadow: 2px 0 24px rgba(11, 36, 71, .18);
    }

    /* Brand */
    .sb-brand {
        display: flex; align-items: center; gap: .75rem;
        padding: 1.25rem 1.25rem 1.1rem;
        text-decoration: none; color: var(--sb-text-strong);
        border-bottom: 1px solid var(--sb-border);
    }
    .sb-brand-icon {
        width: 40px; height: 40px; flex-shrink: 0;
        display: grid; place-items: center;
        border-radius: 11px; font-size: 1.1rem;
        background: linear-gradient(135deg, #4da3ff, #1d6fd6);
        box-shadow: 0 4px 12px rgba(29, 111, 214, .45);
    }
    .sb-brand-title { font-size: .95rem; font-weight: 700; letter-spacing: .06em; line-height: 1.2; }
    .sb-brand-sub { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: var(--sb-text); }

    /* Nav */
    .sb-nav { flex: 1; overflow-y: auto; padding: .5rem .75rem 1rem; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.2) transparent; }
    .sb-section-label {
        padding: 1.1rem .75rem .45rem;
        font-size: .66rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .14em;
        color: rgba(255, 255, 255, .42);
    }
    .sb-list { list-style: none; margin: 0; padding: 0; }
    .sb-link {
        position: relative;
        display: flex; align-items: center; gap: .8rem;
        padding: .6rem .75rem; margin-bottom: 2px;
        border-radius: 10px;
        color: var(--sb-text); text-decoration: none;
        font-size: .9rem; font-weight: 500;
        transition: background .15s ease, color .15s ease;
    }
    .sb-link i { width: 20px; text-align: center; font-size: .95rem; opacity: .85; }
    .sb-link:hover { background: var(--sb-hover); color: var(--sb-text-strong); }
    .sb-link.active { background: var(--sb-active); color: var(--sb-text-strong); }
    .sb-link.active i { color: var(--sb-accent); opacity: 1; }
    .sb-link.active::before {
        content: ""; position: absolute; left: -.75rem; top: 20%; bottom: 20%;
        width: 3px; border-radius: 0 3px 3px 0; background: var(--sb-accent);
    }
    .sb-pill {
        margin-left: auto; min-width: 22px; padding: .12rem .5rem;
        border-radius: 999px; text-align: center;
        font-size: .68rem; font-weight: 600;
        background: rgba(255, 255, 255, .16); color: #fff;
    }
    .sb-pill.is-danger { background: #ef4444; }

    /* Footer */
    .sb-footer { padding: .9rem .75rem 1rem; border-top: 1px solid var(--sb-border); }
    .sb-user {
        display: flex; align-items: center; gap: .7rem;
        padding: .6rem .7rem; margin-bottom: .7rem;
        border-radius: 12px; background: rgba(255, 255, 255, .06);
    }
    .sb-avatar {
        width: 38px; height: 38px; flex-shrink: 0;
        display: grid; place-items: center; border-radius: 50%;
        background: linear-gradient(135deg, #4da3ff, #1d6fd6);
        color: #fff; font-size: .8rem; font-weight: 700;
    }
    .sb-user-name { color: #fff; font-size: .86rem; font-weight: 600; line-height: 1.2;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
    .sb-user-role { font-size: .7rem; color: var(--sb-text); }
    .sb-logout {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: .5rem;
        padding: .55rem; border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, .22); background: transparent;
        color: #fff; font-size: .85rem; font-weight: 500;
        transition: background .15s ease, border-color .15s ease;
    }
    .sb-logout:hover { background: rgba(239, 68, 68, .18); border-color: rgba(239, 68, 68, .55); }

    /* Mobile */
    .sb-toggle {
        position: fixed; bottom: 20px; right: 20px; z-index: 1050;
        width: 52px; height: 52px; border: 0; border-radius: 50%;
        display: grid; place-items: center;
        background: linear-gradient(135deg, #1d6fd6, #0b2447); color: #fff;
        box-shadow: 0 6px 20px rgba(11, 36, 71, .35);
    }
    .sb-overlay {
        position: fixed; inset: 0; z-index: 1039;
        background: rgba(8, 20, 40, .55); backdrop-filter: blur(2px);
        opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s ease;
    }
    @media (max-width: 767.98px) {
        .portal-sidebar { transform: translateX(-100%); }
        body.sb-open .portal-sidebar { transform: translateX(0); }
        body.sb-open .sb-overlay { opacity: 1; visibility: visible; }
        body.sb-open { overflow: hidden; }
    }
    @media (prefers-reduced-motion: reduce) {
        .portal-sidebar, .sb-link, .sb-overlay { transition: none; }
    }
</style>

<!-- Mobile toggle -->
<button id="sidebarToggle" class="sb-toggle d-md-none" type="button"
        aria-label="Toggle navigation" aria-controls="studentSidebar" aria-expanded="false">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<aside id="studentSidebar" class="portal-sidebar" aria-label="Student navigation">

    <a href="{{ route('students.dashboard') }}" class="sb-brand">
        <span class="sb-brand-icon"><i class="fas fa-graduation-cap"></i></span>
        <span>
            <span class="d-block sb-brand-title">EDUNEXUS USMS</span>
            <span class="d-block sb-brand-sub">Student Portal</span>
        </span>
    </a>

    <nav class="sb-nav">
        @foreach ($sections as $title => $items)
            <div class="sb-section-label">{{ $title }}</div>
            <ul class="sb-list">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ $item['href'] }}"
                           class="sb-link {{ $item['active'] ? 'active' : '' }}"
                           @if ($item['active']) aria-current="page" @endif>
                            <i class="fas {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if (!empty($item['badge']))
                                <span class="sb-pill {{ $item['badgeClass'] ?? '' }}">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials ?: 'S' }}</div>
            <div class="min-w-0">
                <div class="sb-user-name" title="{{ $userName }}">{{ $userName }}</div>
                <div class="sb-user-role">Student</div>
            </div>
        </div>

        <button type="button" class="sb-logout"
                onclick="document.getElementById('logout-form').submit();">
            <i class="fas fa-right-from-bracket"></i> Log out
        </button>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="sb-overlay d-md-none" id="sidebarOverlay"></div>

<script>
    (function () {
        var body = document.body;
        var toggle = document.getElementById('sidebarToggle');
        var overlay = document.getElementById('sidebarOverlay');

        function setOpen(open) {
            body.classList.toggle('sb-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () { setOpen(!body.classList.contains('sb-open')); });
        overlay.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
        window.addEventListener('resize', function () { if (window.innerWidth >= 768) setOpen(false); });
    })();
</script>