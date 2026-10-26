@extends('students.layouts.app')

@section('title', 'My Profile - EduNexus')

@section('content')

@php
    $currentAssignment = $student->classAssignments->where('is_current', true)->first();

    $graduatedAssignment = $student->classAssignments
        ->where('status', 'graduated')
        ->where('is_current', false)
        ->sortByDesc('updated_at')
        ->first();

    $hasGraduated = !is_null($graduatedAssignment);
    $state = $hasGraduated ? 'graduated' : ($student->is_active ? 'active' : 'inactive');
    $statusLabel = $hasGraduated ? 'Graduated' : ($student->is_active ? 'Active student' : 'Inactive');
    $statusIcon = $hasGraduated ? 'fa-graduation-cap' : ($student->is_active ? 'fa-circle-check' : 'fa-circle-pause');

    $formatDate = fn ($date, $fallback = '—') => $date ? \Carbon\Carbon::parse($date)->format('d M Y') : $fallback;

    $className = $currentAssignment?->studentClass?->name;
    $yearName  = $currentAssignment?->academicYear?->name;
    $dobAge    = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age : null;

    $initials = collect(explode(' ', trim($student->full_name ?? '')))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // [label, value, empty-text]
    $personal = [
        ['First name',  $student->first_name],
        ['Middle name', $student->middle_name],
        ['Last name',   $student->last_name],
        ['Gender',      $student->gender],
        ['Date of birth', $student->date_of_birth ? $formatDate($student->date_of_birth) . ' (' . $dobAge . ' years)' : null],
        ['Nationality', $student->nationality],
        ['Religion',    $student->religion],
        ['Residential address', $student->address],
    ];

    $contacts = [
        [
            'title' => 'Father / Parent', 'icon' => 'fa-person', 'show' => true,
            'rows' => [
                ['Name', $student->father_name], ['Phone', $student->father_phone],
                ['Email', $student->father_email], ['Occupation', $student->father_occupation],
            ],
        ],
        [
            'title' => 'Mother / Parent', 'icon' => 'fa-person-dress', 'show' => true,
            'rows' => [
                ['Name', $student->mother_name], ['Phone', $student->mother_phone],
                ['Email', $student->mother_email], ['Occupation', $student->mother_occupation],
            ],
        ],
        [
            'title' => 'Registered guardian', 'icon' => 'fa-user-shield',
            'show' => $student->guardian_name || $student->guardian_phone || $student->guardian_email || $student->guardian_relationship,
            'rows' => [
                ['Name', $student->guardian_name], ['Relationship', $student->guardian_relationship],
                ['Phone', $student->guardian_phone], ['Email', $student->guardian_email],
            ],
        ],
    ];
@endphp

<style>
    .sp {
        --navy: #0b2447; --navy-2: #123a73; --blue: #1d6fd6; --sky: #4da3ff;
        --green: #16a36a; --amber: #e29a0b; --red: #d64545; --violet: #6d5bd0;
        --ink: #14213d; --muted: #667388; --faint: #98a3b5; --line: #e7ebf1; --bg: #f4f6fa;
        --radius: 16px; --shadow: 0 1px 2px rgba(16,36,72,.04), 0 6px 20px rgba(16,36,72,.05);
        background: var(--bg); color: var(--ink); min-height: 100vh;
        padding: 28px clamp(16px, 3vw, 36px) 56px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .sp *, .sp *::before, .sp *::after { box-sizing: border-box; }
    .sp h1, .sp h2, .sp h3, .sp p { margin: 0; }

    /* ---------- Hero ---------- */
    .sp-hero {
        position: relative; overflow: hidden; color: #fff; border-radius: 20px; padding: 30px 34px; margin-bottom: 24px;
        background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #1b5aa8 100%);
        box-shadow: 0 12px 32px rgba(11,36,71,.22);
    }
    .sp-hero::after {
        content: ""; position: absolute; right: -80px; top: -120px; width: 320px; height: 320px; border-radius: 50%;
        background: radial-gradient(circle, rgba(77,163,255,.35), transparent 70%); pointer-events: none;
    }
    .sp-crumbs { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: 18px; }
    .sp-crumbs i { font-size: 9px; }
    .sp-hero-row { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
    .sp-id { display: flex; align-items: center; gap: 22px; min-width: 0; }
    .sp-avatar {
        position: relative; width: 96px; height: 96px; flex-shrink: 0; border-radius: 24px;
        display: grid; place-items: center; font-size: 30px; font-weight: 700;
        background: rgba(255,255,255,.14); border: 2px solid rgba(255,255,255,.3);
    }
    .sp-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 22px; }
    .sp-state { position: absolute; right: -4px; bottom: -4px; width: 20px; height: 20px; border-radius: 50%; border: 3px solid var(--navy-2); }
    .sp-state.active { background: #22c55e; } .sp-state.inactive { background: var(--red); } .sp-state.graduated { background: #8b7cf0; }
    .sp-hero h1 { font-size: clamp(22px, 2.6vw, 30px); font-weight: 700; letter-spacing: -.01em; margin-bottom: 12px; }
    .sp-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .sp-chip { display: inline-flex; align-items: center; gap: 7px; padding: 6px 12px; border-radius: 999px; font-size: 12.5px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.16); }
    .sp-chip i { color: var(--sky); font-size: 12px; }
    .sp-chip.status-active { background: rgba(34,197,94,.18); border-color: rgba(34,197,94,.4); }
    .sp-chip.status-active i { color: #6ee7a8; }
    .sp-chip.status-inactive { background: rgba(214,69,69,.22); border-color: rgba(214,69,69,.45); }
    .sp-chip.status-inactive i { color: #ffa1a1; }
    .sp-chip.status-graduated { background: rgba(139,124,240,.24); border-color: rgba(139,124,240,.5); }
    .sp-chip.status-graduated i { color: #c9c0ff; }
    .sp-print {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 600;
        color: #fff; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25); transition: background .15s ease;
    }
    .sp-print:hover { background: rgba(255,255,255,.22); }

    /* ---------- Layout ---------- */
    .sp-layout { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 22px; align-items: start; }
    .sp-aside { position: sticky; top: 90px; display: grid; gap: 18px; }
    .sp-main { display: grid; gap: 22px; }
    .sp-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow); }
    .sp-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
    .sp-card-title { display: flex; align-items: center; gap: 12px; }
    .sp-card-icon { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; background: #e8f1fd; color: var(--blue); }
    .sp-card-title h2 { font-size: 16px; font-weight: 700; letter-spacing: -.01em; }
    .sp-card-title p { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .sp-tag { padding: 4px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--blue); background: #eef4fd; }

    /* ---------- Aside ---------- */
    .sp-status { display: flex; gap: 14px; align-items: flex-start; }
    .sp-status-icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: 13px; display: grid; place-items: center; font-size: 17px; background: #e8f1fd; color: var(--blue); }
    .sp-status.graduated .sp-status-icon { background: #eeebfa; color: var(--violet); }
    .sp-status.warning .sp-status-icon { background: #fdf1d9; color: var(--amber); }
    .sp-status small { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
    .sp-status strong { display: block; font-size: 17px; margin: 3px 0; letter-spacing: -.01em; }
    .sp-status span { display: block; font-size: 12.5px; color: var(--muted); line-height: 1.5; }
    .sp-facts { list-style: none; margin: 0; padding: 0; }
    .sp-facts li { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid var(--line); font-size: 13px; }
    .sp-facts li:first-child { border-top: 0; padding-top: 0; }
    .sp-facts li:last-child { padding-bottom: 0; }
    .sp-facts span { color: var(--muted); }
    .sp-facts strong { text-align: right; }

    /* ---------- Fields ---------- */
    .sp-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 20px; }
    .sp-field { padding: 11px 14px; border-radius: 12px; transition: background .15s ease; }
    .sp-field:hover { background: #f6f8fc; }
    .sp-label { display: block; margin-bottom: 4px; font-size: 10.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--faint); }
    .sp-value { display: block; font-size: 14px; font-weight: 600; line-height: 1.45; word-break: break-word; }
    .sp-value.empty { color: #b3bccb; font-weight: 400; }
    .sp-value.good { color: var(--green); } .sp-value.warn { color: var(--amber); } .sp-value.accent { color: var(--blue); }

    .sp-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .sp-stat { padding: 14px 16px; border-radius: 12px; background: #f7f9fc; border: 1px solid var(--line); }
    .sp-stat .sp-value { font-size: 15px; }
    .sp-divider { height: 1px; background: var(--line); margin: 18px 0 8px; }

    /* ---------- Contacts ---------- */
    .sp-contacts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .sp-contact { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
    .sp-contact-head { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #f7f9fc; border-bottom: 1px solid var(--line); font-size: 13px; font-weight: 700; }
    .sp-contact-head i { color: var(--blue); }
    .sp-contact .sp-grid { grid-template-columns: 1fr; gap: 0; padding: 6px; }
    .sp-contact.wide { grid-column: 1 / -1; }
    .sp-contact.wide .sp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1100px) {
        .sp-layout { grid-template-columns: 1fr; }
        .sp-aside { position: static; grid-template-columns: 1fr 1fr; }
        .sp-stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 767px) {
        .sp { padding: 18px 14px 90px; }
        .sp-hero { padding: 22px 20px; border-radius: 16px; }
        .sp-id { align-items: flex-start; gap: 14px; }
        .sp-avatar { width: 68px; height: 68px; border-radius: 18px; font-size: 22px; }
        .sp-avatar img { border-radius: 16px; }
        .sp-aside, .sp-grid, .sp-contacts, .sp-contact.wide .sp-grid, .sp-stats { grid-template-columns: 1fr; }
        .sp-card { padding: 20px; }
        .sp-tag { display: none; }
        .sp-print { display: none; }
    }

    /* ---------- Print ---------- */
    @media print {
        .sp { background: #fff; padding: 0; min-height: 0; }
        .sp-hero { background: var(--navy) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; box-shadow: none; }
        .sp-print, .sp-crumbs { display: none; }
        .sp-card { box-shadow: none; break-inside: avoid; }
        .sp-aside { position: static; }
        .sp-field:hover { background: transparent; }
    }
</style>

<div class="sp">

    {{-- ================= HERO ================= --}}
    <header class="sp-hero">
        <div class="sp-crumbs">
            <span>Student Portal</span><i class="fas fa-chevron-right"></i><span>My Profile</span>
        </div>

        <div class="sp-hero-row">
            <div class="sp-id">
                <div class="sp-avatar">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}">
                    @else
                        {{ $initials ?: 'S' }}
                    @endif
                    <span class="sp-state {{ $state }}"></span>
                </div>
                <div>
                    <h1>{{ $student->full_name }}</h1>
                    <div class="sp-chips">
                        <span class="sp-chip"><i class="fas fa-id-card"></i>{{ $student->student_id }}</span>
                        <span class="sp-chip"><i class="fas fa-school"></i>{{ $className ?? 'No class assigned' }}</span>
                        <span class="sp-chip status-{{ $state }}"><i class="fas {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>

            <button type="button" class="sp-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print profile
            </button>
        </div>
    </header>

    <div class="sp-layout">

        {{-- ================= ASIDE ================= --}}
        <aside class="sp-aside">

            <div class="sp-card">
                @if($currentAssignment && $currentAssignment->studentClass)
                    <div class="sp-status">
                        <span class="sp-status-icon"><i class="fas fa-school"></i></span>
                        <div>
                            <small>Current class</small>
                            <strong>{{ $currentAssignment->studentClass->name }}</strong>
                            <span><i class="fas fa-calendar-check"></i> Since {{ $formatDate($currentAssignment->created_at) }}</span>
                        </div>
                    </div>
                @elseif($hasGraduated)
                    <div class="sp-status graduated">
                        <span class="sp-status-icon"><i class="fas fa-graduation-cap"></i></span>
                        <div>
                            <small>Academic status</small>
                            <strong>Graduated</strong>
                            <span>From <b>{{ $graduatedAssignment->studentClass->name ?? 'N/A' }}</b></span>
                            <span><i class="fas fa-calendar"></i> {{ $formatDate($graduatedAssignment->updated_at) }}</span>
                        </div>
                    </div>
                @else
                    <div class="sp-status warning">
                        <span class="sp-status-icon"><i class="fas fa-triangle-exclamation"></i></span>
                        <div>
                            <small>Academic status</small>
                            <strong>No class assigned</strong>
                            <span>Please contact the school administration.</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="sp-card">
                <ul class="sp-facts">
                    <li><span>Student ID</span><strong class="sp-value accent">{{ $student->student_id }}</strong></li>
                    <li><span>Admission date</span><strong>{{ $formatDate($student->admission_date) }}</strong></li>
                    <li><span>Academic year</span><strong>{{ $yearName ?? '—' }}</strong></li>
                    <li><span>Status</span><strong>{{ $statusLabel }}</strong></li>
                </ul>
            </div>
        </aside>

        {{-- ================= MAIN ================= --}}
        <main class="sp-main">

            {{-- Personal --}}
            <section class="sp-card">
                <div class="sp-card-head">
                    <div class="sp-card-title">
                        <span class="sp-card-icon"><i class="fas fa-user"></i></span>
                        <div><h2>Personal information</h2><p>Your basic personal details</p></div>
                    </div>
                    <span class="sp-tag">Profile</span>
                </div>

                <div class="sp-grid">
                    @foreach($personal as [$label, $value])
                        <div class="sp-field">
                            <span class="sp-label">{{ $label }}</span>
                            <span class="sp-value {{ $value ? '' : 'empty' }}">{{ $value ?: 'Not provided' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Academic --}}
            <section class="sp-card">
                <div class="sp-card-head">
                    <div class="sp-card-title">
                        <span class="sp-card-icon"><i class="fas fa-graduation-cap"></i></span>
                        <div><h2>Academic information</h2><p>Your school and enrollment details</p></div>
                    </div>
                    <span class="sp-tag">Academic</span>
                </div>

                <div class="sp-stats">
                    <div class="sp-stat">
                        <span class="sp-label">Student ID</span>
                        <span class="sp-value accent">{{ $student->student_id }}</span>
                    </div>
                    <div class="sp-stat">
                        <span class="sp-label">Admission date</span>
                        <span class="sp-value">{{ $formatDate($student->admission_date) }}</span>
                    </div>
                    <div class="sp-stat">
                        <span class="sp-label">Current class</span>
                        <span class="sp-value {{ $className ? 'accent' : 'empty' }}">{{ $className ?? 'Not assigned' }}</span>
                    </div>
                    <div class="sp-stat">
                        <span class="sp-label">Academic year</span>
                        <span class="sp-value {{ $yearName ? '' : 'empty' }}">{{ $yearName ?? 'Not available' }}</span>
                    </div>
                </div>

                @if($hasGraduated)
                    <div class="sp-divider"></div>
                    <div class="sp-grid">
                        <div class="sp-field">
                            <span class="sp-label">Graduated from</span>
                            <span class="sp-value">{{ $graduatedAssignment->studentClass->name ?? 'N/A' }}</span>
                        </div>
                        <div class="sp-field">
                            <span class="sp-label">Graduation date</span>
                            <span class="sp-value good">{{ $formatDate($graduatedAssignment->updated_at) }}</span>
                        </div>
                    </div>
                @endif
            </section>

            {{-- Accessibility --}}
            <section class="sp-card">
                <div class="sp-card-head">
                    <div class="sp-card-title">
                        <span class="sp-card-icon"><i class="fas fa-universal-access"></i></span>
                        <div><h2>Accessibility information</h2><p>Additional support information</p></div>
                    </div>
                    <span class="sp-tag">Optional</span>
                </div>

                <div class="sp-grid">
                    <div class="sp-field">
                        <span class="sp-label">Disability status</span>
                        @if($student->has_disability)
                            <span class="sp-value warn"><i class="fas fa-circle-info"></i> Yes</span>
                        @else
                            <span class="sp-value good"><i class="fas fa-circle-check"></i> No</span>
                        @endif
                    </div>
                    <div class="sp-field">
                        <span class="sp-label">Disability type</span>
                        <span class="sp-value {{ $student->disability_type ? '' : 'empty' }}">{{ $student->disability_type ?: 'None recorded' }}</span>
                    </div>
                </div>
            </section>

            {{-- Parents & guardian --}}
            <section class="sp-card">
                <div class="sp-card-head">
                    <div class="sp-card-title">
                        <span class="sp-card-icon"><i class="fas fa-people-roof"></i></span>
                        <div><h2>Parents &amp; guardian</h2><p>Registered family and emergency contacts</p></div>
                    </div>
                    <span class="sp-tag">Contacts</span>
                </div>

                <div class="sp-contacts">
                    @foreach($contacts as $contact)
                        @continue(!$contact['show'])
                        <div class="sp-contact {{ $contact['title'] === 'Registered guardian' ? 'wide' : '' }}">
                            <div class="sp-contact-head"><i class="fas {{ $contact['icon'] }}"></i>{{ $contact['title'] }}</div>
                            <div class="sp-grid">
                                @foreach($contact['rows'] as [$label, $value])
                                    <div class="sp-field">
                                        <span class="sp-label">{{ $label }}</span>
                                        <span class="sp-value {{ $value ? '' : 'empty' }}">{{ $value ?: 'Not provided' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

        </main>
    </div>
</div>

@endsection