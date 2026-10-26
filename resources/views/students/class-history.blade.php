@extends('students.layouts.app')

@section('title', 'Class History')

@section('content')

@php
    $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : null;
    $recordCount = $classHistory->count();
@endphp

<style>
    .ch {
        --navy: #0b2447; --navy-2: #123a73; --blue: #1d6fd6; --sky: #4da3ff;
        --green: #16a36a; --amber: #e29a0b; --violet: #6d5bd0;
        --ink: #14213d; --muted: #667388; --faint: #98a3b5; --line: #e7ebf1; --bg: #f4f6fa;
        --radius: 16px; --shadow: 0 1px 2px rgba(16,36,72,.04), 0 6px 20px rgba(16,36,72,.05);
        background: var(--bg); color: var(--ink); min-height: 100vh;
        padding: 28px clamp(16px, 3vw, 36px) 56px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .ch *, .ch *::before, .ch *::after { box-sizing: border-box; }
    .ch h1, .ch h2, .ch h3, .ch p { margin: 0; }

    /* ---------- Hero ---------- */
    .ch-hero {
        position: relative; overflow: hidden; color: #fff; border-radius: 20px; padding: 30px 34px; margin-bottom: 24px;
        background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #1b5aa8 100%);
        box-shadow: 0 12px 32px rgba(11,36,71,.22);
    }
    .ch-hero::after {
        content: ""; position: absolute; right: -80px; top: -120px; width: 320px; height: 320px; border-radius: 50%;
        background: radial-gradient(circle, rgba(77,163,255,.35), transparent 70%); pointer-events: none;
    }
    .ch-hero-row { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 22px; flex-wrap: wrap; }
    .ch-crumbs { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: 16px; }
    .ch-crumbs i { font-size: 9px; }
    .ch-hero h1 { font-size: clamp(22px, 2.6vw, 30px); font-weight: 700; letter-spacing: -.01em; margin-bottom: 6px; }
    .ch-hero p { font-size: 14px; color: rgba(255,255,255,.75); }
    .ch-chip {
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-size: 13px;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
    }
    .ch-chip i { color: var(--sky); }

    /* ---------- KPIs ---------- */
    .ch-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 22px; }
    .ch-kpi {
        --tone: var(--blue); --tint: #e8f1fd;
        display: flex; align-items: center; gap: 16px; padding: 20px 22px;
        background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .ch-kpi:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(16,36,72,.10); }
    .ch-kpi.is-green  { --tone: var(--green);  --tint: #e3f6ee; }
    .ch-kpi.is-slate  { --tone: #5b6b82;       --tint: #edf0f5; }
    .ch-kpi.is-violet { --tone: var(--violet); --tint: #eeebfa; }
    .ch-kpi-icon { width: 46px; height: 46px; flex-shrink: 0; border-radius: 13px; display: grid; place-items: center; font-size: 18px; background: var(--tint); color: var(--tone); }
    .ch-kpi-label { display: block; font-size: 11.5px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); }
    .ch-kpi-value { display: block; font-size: 28px; font-weight: 700; line-height: 1.15; letter-spacing: -.02em; }

    /* ---------- Panel ---------- */
    .ch-panel { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .ch-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 22px 26px; border-bottom: 1px solid var(--line); }
    .ch-eyebrow { display: block; margin-bottom: 4px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--blue); }
    .ch-panel-head h2 { font-size: 18px; font-weight: 700; letter-spacing: -.01em; }
    .ch-panel-head p { margin-top: 4px; font-size: 12.5px; color: var(--muted); }
    .ch-count { padding: 6px 13px; border-radius: 999px; font-size: 12px; font-weight: 600; color: #4a5a72; background: #eef1f6; white-space: nowrap; }

    /* Current class highlight */
    .ch-current {
        display: flex; align-items: center; gap: 18px; margin: 22px 26px 4px; padding: 20px 22px; border-radius: 14px;
        background: linear-gradient(120deg, #eef6ff, #f6faff); border: 1px solid #cfe2fb;
    }
    .ch-current-icon { width: 52px; height: 52px; flex-shrink: 0; border-radius: 15px; display: grid; place-items: center; font-size: 20px; color: #fff; background: linear-gradient(135deg, #4da3ff, #1d6fd6); box-shadow: 0 6px 16px rgba(29,111,214,.3); }
    .ch-current small { display: flex; align-items: center; gap: 7px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--blue); }
    .ch-live { width: 7px; height: 7px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 3px rgba(22,163,106,.2); }
    .ch-current strong { display: block; margin: 4px 0 6px; font-size: 21px; letter-spacing: -.01em; }
    .ch-meta { display: flex; flex-wrap: wrap; gap: 6px 18px; font-size: 12.5px; color: var(--muted); }
    .ch-meta i { margin-right: 6px; color: var(--faint); }

    /* ---------- Timeline ---------- */
    .ch-timeline { list-style: none; margin: 0; padding: 26px 26px 30px; }
    .ch-step { position: relative; display: grid; grid-template-columns: 44px minmax(0, 1fr); gap: 18px; padding-bottom: 22px; }
    .ch-step:last-child { padding-bottom: 0; }
    .ch-step::before { content: ""; position: absolute; left: 21px; top: 44px; bottom: 0; width: 2px; background: var(--line); }
    .ch-step:last-child::before { display: none; }
    .ch-marker { position: relative; z-index: 1; width: 44px; height: 44px; border-radius: 13px; display: grid; place-items: center; font-size: 16px; background: #e8f1fd; color: var(--blue); border: 3px solid #fff; box-shadow: 0 0 0 1px var(--line); }
    .ch-step.is-current .ch-marker { background: #e3f6ee; color: var(--green); }
    .ch-item {
        display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 20px; border-radius: 14px;
        border: 1px solid var(--line); background: #fff; transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ch-item:hover { border-color: #cfdcef; box-shadow: 0 6px 18px rgba(16,36,72,.06); }
    .ch-step.is-current .ch-item { border-color: #bfe8d4; background: #f7fcf9; }
    .ch-item h3 { font-size: 15.5px; font-weight: 700; margin-bottom: 6px; }
    .ch-details { display: flex; flex-wrap: wrap; gap: 6px 18px; font-size: 12.5px; color: var(--muted); }
    .ch-details i { margin-right: 6px; color: var(--faint); }
    .ch-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .ch-badge.current { color: #0f7d51; background: #dff5ea; }
    .ch-badge.done { color: #4a5a72; background: #eef1f6; }
    .ch-badge i { font-size: 9px; }

    /* ---------- Empty ---------- */
    .ch-empty { padding: 72px 24px; text-align: center; }
    .ch-empty-icon { width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 20px; display: grid; place-items: center; font-size: 28px; color: #9aa7bb; background: #eef1f6; }
    .ch-empty h3 { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
    .ch-empty p { font-size: 13.5px; color: var(--muted); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1100px) { .ch-kpis { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) {
        .ch { padding: 18px 14px 90px; }
        .ch-hero { padding: 22px 20px; border-radius: 16px; }
        .ch-kpis { gap: 12px; }
        .ch-kpi { flex-direction: column; align-items: flex-start; gap: 10px; padding: 16px; }
        .ch-panel-head { padding: 18px; }
        .ch-current { margin: 18px 18px 0; flex-direction: column; align-items: flex-start; }
        .ch-timeline { padding: 20px 18px 24px; }
        .ch-step { grid-template-columns: 36px minmax(0, 1fr); gap: 12px; }
        .ch-marker { width: 36px; height: 36px; font-size: 14px; border-radius: 11px; }
        .ch-step::before { left: 17px; top: 36px; }
        .ch-item { flex-direction: column; align-items: flex-start; gap: 10px; padding: 14px 16px; }
    }
    @media (prefers-reduced-motion: reduce) { .ch-kpi, .ch-item { transition: none; } }
</style>

<div class="ch">

    {{-- ================= HERO ================= --}}
    <header class="ch-hero">
        <div class="ch-crumbs">
            <span>Student Portal</span><i class="fas fa-chevron-right"></i><span>Class History</span>
        </div>
        <div class="ch-hero-row">
            <div>
                <h1>Class history</h1>
                <p>Your academic class progression and current placement.</p>
            </div>
            <span class="ch-chip"><i class="fas fa-id-card"></i> Student ID: {{ $student->student_id }}</span>
        </div>
    </header>

    {{-- ================= KPIs ================= --}}
    <section class="ch-kpis">
        <div class="ch-kpi">
            <span class="ch-kpi-icon"><i class="fas fa-layer-group"></i></span>
            <div><span class="ch-kpi-label">Class records</span><span class="ch-kpi-value">{{ $totalClasses }}</span></div>
        </div>
        <div class="ch-kpi is-green">
            <span class="ch-kpi-icon"><i class="fas fa-school"></i></span>
            <div><span class="ch-kpi-label">Current class</span><span class="ch-kpi-value">{{ $currentClasses }}</span></div>
        </div>
        <div class="ch-kpi is-slate">
            <span class="ch-kpi-icon"><i class="fas fa-check-double"></i></span>
            <div><span class="ch-kpi-label">Completed</span><span class="ch-kpi-value">{{ $completedClasses }}</span></div>
        </div>
        <div class="ch-kpi is-violet">
            <span class="ch-kpi-icon"><i class="fas fa-calendar-days"></i></span>
            <div><span class="ch-kpi-label">Academic years</span><span class="ch-kpi-value">{{ $totalYears }}</span></div>
        </div>
    </section>

    {{-- ================= HISTORY ================= --}}
    <section class="ch-panel">
        <div class="ch-panel-head">
            <div>
                <span class="ch-eyebrow">Academic progression</span>
                <h2>Class assignment history</h2>
                <p>Your complete class assignment record.</p>
            </div>
            <span class="ch-count">{{ $recordCount }} {{ $recordCount === 1 ? 'record' : 'records' }}</span>
        </div>

        {{-- Current class --}}
        @if($currentAssignment)
            <div class="ch-current">
                <span class="ch-current-icon"><i class="fas fa-school"></i></span>
                <div>
                    <small><i class="ch-live"></i> Current class</small>
                    <strong>{{ optional($currentAssignment->studentClass)->name ?? 'Class not assigned' }}</strong>
                    <div class="ch-meta">
                        @if(optional($currentAssignment->academicYear)->name)
                            <span><i class="fas fa-calendar"></i>{{ $currentAssignment->academicYear->name }}</span>
                        @endif
                        @if($currentAssignment->assigned_date)
                            <span><i class="fas fa-clock"></i>Assigned {{ $fmt($currentAssignment->assigned_date) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline --}}
        @if($classHistory->isNotEmpty())
            <ol class="ch-timeline">
                @foreach($classHistory as $assignment)
                    @php
                        $isCurrent    = (bool) $assignment->is_current;
                        $className    = optional($assignment->studentClass)->name ?? 'Class not assigned';
                        $academicYear = optional($assignment->academicYear)->name ?? 'Academic year not set';
                    @endphp

                    <li class="ch-step {{ $isCurrent ? 'is-current' : '' }}">
                        <span class="ch-marker"><i class="fas {{ $isCurrent ? 'fa-school' : 'fa-graduation-cap' }}"></i></span>

                        <div class="ch-item">
                            <div>
                                <h3>{{ $className }}</h3>
                                <div class="ch-details">
                                    <span><i class="fas fa-calendar"></i>{{ $academicYear }}</span>
                                    @if($assignment->assigned_date)
                                        <span><i class="fas fa-calendar-plus"></i>Assigned {{ $fmt($assignment->assigned_date) }}</span>
                                    @endif
                                    @if($assignment->promotion_date)
                                        <span><i class="fas fa-arrow-up"></i>Promotion {{ $fmt($assignment->promotion_date) }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($isCurrent)
                                <span class="ch-badge current"><i class="fas fa-circle"></i> Current</span>
                            @else
                                <span class="ch-badge done"><i class="fas fa-check"></i> Completed</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <div class="ch-empty">
                <div class="ch-empty-icon"><i class="fas fa-school"></i></div>
                <h3>No class history available</h3>
                <p>Your class assignments will appear here once they are recorded.</p>
            </div>
        @endif
    </section>

</div>

@endsection