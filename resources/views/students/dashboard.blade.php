@extends('students.layouts.app')

@section('title', 'Student Dashboard')

@php
    $attendancePct = min(100, max(0, (float) ($attendancePercentage ?? 0)));
    $balance       = (float) ($feeBalance ?? 0);
    $feesTotal     = (float) ($totalFees ?? 0);
    $feesPaid      = (float) ($amountPaid ?? 0);
    $feePct        = $feesTotal > 0 ? min(100, ($feesPaid / $feesTotal) * 100) : 0;
    $className     = $assignment?->studentClass?->name ?? null;
    $firstName     = $student->first_name ?? $student->student_id;
    $hour          = (int) now()->format('G');
    $greeting      = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $gradeFor      = fn ($s) => $s >= 80 ? 'A' : ($s >= 70 ? 'B' : ($s >= 60 ? 'C' : ($s >= 50 ? 'D' : 'F')));
@endphp

@section('content')
<div class="sd">

    {{-- ================= HERO ================= --}}
    <header class="sd-hero">
        <div class="sd-hero-main">
            <div class="sd-avatar">
                @if(!empty($student->photo))
                    <img src="{{ asset('storage/' . $student->photo) }}" alt="Student photo">
                @else
                    <i class="fas fa-user-graduate"></i>
                @endif
            </div>
            <div>
                <div class="sd-eyebrow">{{ $greeting }}</div>
                <h1>Welcome back, {{ $firstName }}</h1>
                <div class="sd-chips">
                    <span class="sd-chip"><i class="fas fa-id-card"></i>{{ $student->student_id }}</span>
                    <span class="sd-chip"><i class="fas fa-school"></i>{{ $className ?? 'Class not assigned' }}</span>
                    <span class="sd-chip"><i class="fas fa-calendar"></i>{{ $academicYear?->name ?? 'Academic year not configured' }}</span>
                </div>
            </div>
        </div>

        <div class="sd-term">
            <span class="sd-term-label">Current term</span>
            <strong>{{ $term?->name ?? '—' }}</strong>
            <small>{{ $academicYear?->name ?? '—' }}</small>
            <span class="sd-term-note"><i class="fas fa-shield-halved"></i> Set by school administration</span>
        </div>
    </header>

    {{-- ================= KPI ROW ================= --}}
    <section class="sd-kpis">

        <article class="sd-kpi is-green">
            <div class="sd-kpi-head">
                <span class="sd-kpi-icon"><i class="fas fa-calendar-check"></i></span>
                <span class="sd-kpi-label">Attendance rate</span>
            </div>
            <div class="sd-kpi-value">{{ number_format($attendancePct, 1) }}<small>%</small></div>
            <div class="sd-bar"><span style="width: {{ $attendancePct }}%"></span></div>
            <div class="sd-kpi-foot">
                <span><strong>{{ ($present ?? 0) + ($late ?? 0) }}</strong> attended</span>
                <span>{{ $total ?? 0 }} sessions</span>
            </div>
        </article>

        <article class="sd-kpi is-amber">
            <div class="sd-kpi-head">
                <span class="sd-kpi-icon"><i class="fas fa-wallet"></i></span>
                <span class="sd-kpi-label">Fee balance</span>
            </div>
            <div class="sd-kpi-value sm {{ $balance > 0 ? 'text-due' : 'text-clear' }}">GH₵ {{ number_format($balance, 2) }}</div>
            <div class="sd-kpi-foot">
                <span>Paid <strong>GH₵ {{ number_format($feesPaid, 2) }}</strong></span>
                <a href="{{ route('students.fees') }}">View account <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>

        <article class="sd-kpi is-blue">
            <div class="sd-kpi-head">
                <span class="sd-kpi-icon"><i class="fas fa-book-open"></i></span>
                <span class="sd-kpi-label">Subjects</span>
            </div>
            <div class="sd-kpi-value">{{ $subjects ?? 0 }}</div>
            <div class="sd-kpi-foot">
                <span>Currently enrolled</span>
                <a href="{{ route('students.results') }}">Results <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>

        <article class="sd-kpi is-violet">
            <div class="sd-kpi-head">
                <span class="sd-kpi-icon"><i class="fas fa-chart-column"></i></span>
                <span class="sd-kpi-label">Total sessions</span>
            </div>
            <div class="sd-kpi-value">{{ $total ?? 0 }}</div>
            <div class="sd-kpi-foot">
                <span><strong>{{ $present ?? 0 }}</strong> present</span>
                <a href="{{ route('students.attendance') }}">Attendance <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>
    </section>

    {{-- ================= ATTENDANCE + FEES ================= --}}
    <section class="sd-grid-2">

        <div class="sd-panel">
            <div class="sd-panel-head">
                <div>
                    <span class="sd-panel-eyebrow">Attendance</span>
                    <h2>Attendance overview</h2>
                    <p>Your performance for {{ $term?->name ?? 'the current period' }}.</p>
                </div>
                <a href="{{ route('students.attendance') }}" class="sd-link">View details <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="sd-att">
                <div class="sd-ring" style="--pct: {{ $attendancePct }}">
                    <div class="sd-ring-inner">
                        <strong>{{ number_format($attendancePct, 1) }}%</strong>
                        <span>Attendance</span>
                    </div>
                </div>

                <div class="sd-att-stats">
                    <div class="sd-stat s-present"><i></i><div><strong>{{ $present ?? 0 }}</strong><small>Present</small></div></div>
                    <div class="sd-stat s-late"><i></i><div><strong>{{ $late ?? 0 }}</strong><small>Late</small></div></div>
                    <div class="sd-stat s-absent"><i></i><div><strong>{{ $absent ?? 0 }}</strong><small>Absent</small></div></div>
                    <div class="sd-stat s-excused"><i></i><div><strong>{{ $excused ?? 0 }}</strong><small>Excused</small></div></div>
                </div>
            </div>
        </div>

        <div class="sd-panel">
            <div class="sd-panel-head">
                <div>
                    <span class="sd-panel-eyebrow">Financial account</span>
                    <h2>Fee account</h2>
                    <p>Current school fee position.</p>
                </div>
                <a href="{{ route('students.fees') }}" class="sd-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>

            @if(!empty($feeAccount))
                <div class="sd-fee-main {{ $balance > 0 ? 'is-due' : 'is-clear' }}">
                    <span>Outstanding balance</span>
                    <strong>GH₵ {{ number_format($balance, 2) }}</strong>
                    @if($balance <= 0)
                        <small class="text-clear"><i class="fas fa-circle-check"></i> Account fully settled</small>
                    @else
                        <small class="text-due"><i class="fas fa-circle-exclamation"></i> Balance outstanding</small>
                    @endif
                </div>

                <div class="sd-fee-split">
                    <div><span>Total fees</span><strong>GH₵ {{ number_format($feesTotal, 2) }}</strong></div>
                    <div><span>Amount paid</span><strong class="text-clear">GH₵ {{ number_format($feesPaid, 2) }}</strong></div>
                </div>

                <div class="sd-fee-progress">
                    <div><span>Payment progress</span><strong>{{ number_format($feePct, 1) }}%</strong></div>
                    <div class="sd-bar lg"><span style="width: {{ $feePct }}%"></span></div>
                </div>
            @else
                <div class="sd-empty">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <h3>No fee account</h3>
                    <p>No fee account has been allocated to you yet.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ================= LOWER GRID ================= --}}
    <section class="sd-grid-3">

        {{-- Academic info --}}
        <div class="sd-panel sd-span">
            <div class="sd-panel-head">
                <div>
                    <span class="sd-panel-eyebrow">Academic profile</span>
                    <h2>My academic information</h2>
                </div>
            </div>
            <ul class="sd-info">
                <li><span class="sd-info-icon"><i class="fas fa-graduation-cap"></i></span><div><small>Current class</small><strong>{{ $className ?? 'Not assigned' }}</strong></div></li>
                <li><span class="sd-info-icon"><i class="fas fa-calendar-days"></i></span><div><small>Academic year</small><strong>{{ $academicYear?->name ?? '—' }}</strong></div></li>
                <li><span class="sd-info-icon"><i class="fas fa-layer-group"></i></span><div><small>Current term</small><strong>{{ $term?->name ?? '—' }}</strong></div></li>
                <li><span class="sd-info-icon"><i class="fas fa-book"></i></span><div><small>Enrolled subjects</small><strong>{{ $subjects ?? 0 }}</strong></div></li>
            </ul>
        </div>

        {{-- Announcements --}}
        <div class="sd-panel">
            <div class="sd-panel-head">
                <div>
                    <span class="sd-panel-eyebrow">School updates</span>
                    <h2>Announcements</h2>
                </div>
                <a href="{{ url('/student/announcements') }}" class="sd-link">All <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="sd-list">
                @forelse(($announcements ?? []) as $announcement)
                    <div class="sd-item">
                        <span class="sd-item-icon amber"><i class="fas fa-bullhorn"></i></span>
                        <div class="sd-item-body">
                            <h3>{{ $announcement->title }}</h3>
                            @if($announcement->content)
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 110) }}</p>
                            @endif
                            <small><i class="far fa-calendar"></i> {{ $announcement->created_at?->format('d M Y') }}</small>
                        </div>
                    </div>
                @empty
                    <div class="sd-empty compact">
                        <i class="fas fa-bell-slash"></i>
                        <p>No announcements available.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent results --}}
        <div class="sd-panel">
            <div class="sd-panel-head">
                <div>
                    <span class="sd-panel-eyebrow">Academic performance</span>
                    <h2>Recent results</h2>
                </div>
                @if(isset($recentResults) && count($recentResults))
                    <a href="{{ route('students.results') }}" class="sd-link">All results <i class="fas fa-arrow-right"></i></a>
                @endif
            </div>

            <div class="sd-list">
                @if(isset($recentResults) && count($recentResults))
                    @foreach($recentResults as $result)
                        @php
                            $score = $result->score ?? 0;
                            $grade = $result->grade ?? $gradeFor($score);
                        @endphp
                        <div class="sd-item between">
                            <div class="sd-item-left">
                                <span class="sd-item-icon blue"><i class="fas fa-book-open"></i></span>
                                <div>
                                    <strong>{{ $result->subject?->name ?? 'Subject' }}</strong>
                                    <small>{{ $result->assessment?->name ?? 'Assessment' }}</small>
                                </div>
                            </div>
                            <div class="sd-score">
                                <strong>{{ $score }}%</strong>
                                <span class="sd-grade g-{{ strtolower($grade) }}">{{ $grade }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="sd-empty compact">
                        <i class="fas fa-chart-line"></i>
                        <p>No results available yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
    .sd {
        --navy: #0b2447; --navy-2: #123a73; --blue: #1d6fd6; --sky: #4da3ff;
        --green: #16a36a; --amber: #e29a0b; --red: #d64545; --violet: #6d5bd0;
        --ink: #14213d; --muted: #667388; --line: #e7ebf1; --bg: #f4f6fa; --card: #fff;
        --radius: 16px; --shadow: 0 1px 2px rgba(16,36,72,.04), 0 6px 20px rgba(16,36,72,.05);
        background: var(--bg); color: var(--ink);
        min-height: 100vh; padding: 28px clamp(16px, 3vw, 36px) 56px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .sd *, .sd *::before, .sd *::after { box-sizing: border-box; }
    .sd h1, .sd h2, .sd h3, .sd p { margin: 0; }
    .sd .text-due { color: var(--red); }
    .sd .text-clear { color: var(--green); }

    /* ---------- Hero ---------- */
    .sd-hero {
        display: flex; justify-content: space-between; align-items: center; gap: 28px; flex-wrap: wrap;
        padding: 30px 34px; margin-bottom: 24px; color: #fff;
        border-radius: 20px; position: relative; overflow: hidden;
        background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #1b5aa8 100%);
        box-shadow: 0 12px 32px rgba(11,36,71,.22);
    }
    .sd-hero::after {
        content: ""; position: absolute; right: -80px; top: -120px; width: 320px; height: 320px; border-radius: 50%;
        background: radial-gradient(circle, rgba(77,163,255,.35), transparent 70%); pointer-events: none;
    }
    .sd-hero-main { display: flex; align-items: center; gap: 22px; position: relative; z-index: 1; }
    .sd-avatar {
        width: 78px; height: 78px; flex-shrink: 0; border-radius: 20px; overflow: hidden;
        display: grid; place-items: center; font-size: 28px;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25);
    }
    .sd-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .sd-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.7); margin-bottom: 4px; }
    .sd-hero h1 { font-size: clamp(22px, 2.6vw, 30px); font-weight: 700; letter-spacing: -.01em; margin-bottom: 14px; }
    .sd-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .sd-chip {
        display: inline-flex; align-items: center; gap: 7px; padding: 6px 12px; border-radius: 999px;
        font-size: 12.5px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.16);
    }
    .sd-chip i { color: var(--sky); font-size: 12px; }

    .sd-term {
        position: relative; z-index: 1; min-width: 210px; padding: 16px 20px; border-radius: 14px;
        background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.18); backdrop-filter: blur(6px);
    }
    .sd-term-label { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.65); }
    .sd-term strong { display: block; font-size: 20px; margin: 4px 0 2px; }
    .sd-term small { display: block; font-size: 12.5px; color: rgba(255,255,255,.75); }
    .sd-term-note { display: block; margin-top: 10px; font-size: 10.5px; color: var(--sky); }

    /* ---------- KPIs ---------- */
    .sd-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 22px; }
    .sd-kpi {
        --tone: var(--blue); --tint: #e8f1fd;
        background: var(--card); border: 1px solid var(--line); border-radius: var(--radius);
        padding: 20px 22px; box-shadow: var(--shadow); transition: transform .18s ease, box-shadow .18s ease;
    }
    .sd-kpi:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(16,36,72,.10); }
    .sd-kpi.is-green  { --tone: var(--green);  --tint: #e3f6ee; }
    .sd-kpi.is-amber  { --tone: var(--amber);  --tint: #fdf1d9; }
    .sd-kpi.is-blue   { --tone: var(--blue);   --tint: #e8f1fd; }
    .sd-kpi.is-violet { --tone: var(--violet); --tint: #eeebfa; }
    .sd-kpi-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .sd-kpi-icon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; background: var(--tint); color: var(--tone); }
    .sd-kpi-label { font-size: 12px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
    .sd-kpi-value { font-size: 32px; font-weight: 700; line-height: 1.1; letter-spacing: -.02em; }
    .sd-kpi-value small { font-size: 17px; font-weight: 600; color: var(--muted); margin-left: 2px; }
    .sd-kpi-value.sm { font-size: 24px; padding: 4px 0; }
    .sd-kpi-foot { display: flex; justify-content: space-between; gap: 10px; margin-top: 16px; font-size: 12px; color: var(--muted); }
    .sd-kpi-foot strong { color: var(--ink); }
    .sd-kpi-foot a { color: var(--blue); font-weight: 600; text-decoration: none; white-space: nowrap; }
    .sd-kpi-foot a:hover { text-decoration: underline; }
    .sd-kpi-foot i { font-size: 10px; margin-left: 2px; }
    .sd-kpi .sd-bar { margin-top: 14px; }

    .sd-bar { height: 6px; border-radius: 99px; background: #e9eef4; overflow: hidden; }
    .sd-bar.lg { height: 8px; }
    .sd-bar span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--green), #35c58a); }

    /* ---------- Panels ---------- */
    .sd-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 22px; }
    .sd-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
    .sd-panel { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow); }
    .sd-panel-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 22px; }
    .sd-panel-eyebrow { display: block; margin-bottom: 4px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--blue); }
    .sd-panel-head h2 { font-size: 18px; font-weight: 700; letter-spacing: -.01em; }
    .sd-panel-head p { margin-top: 4px; font-size: 12.5px; color: var(--muted); }
    .sd-link { font-size: 12.5px; font-weight: 600; color: var(--blue); text-decoration: none; white-space: nowrap; padding: 6px 10px; border-radius: 8px; }
    .sd-link:hover { background: #eef4fd; }
    .sd-link i { font-size: 10px; margin-left: 3px; }

    /* ---------- Attendance ---------- */
    .sd-att { display: flex; align-items: center; gap: 36px; }
    .sd-ring {
        --pct: 0; width: 160px; height: 160px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center;
        background: conic-gradient(var(--green) calc(var(--pct) * 1%), #e9eef4 0);
    }
    .sd-ring-inner { width: 124px; height: 124px; border-radius: 50%; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: inset 0 0 0 1px var(--line); }
    .sd-ring-inner strong { font-size: 26px; font-weight: 700; letter-spacing: -.02em; }
    .sd-ring-inner span { font-size: 11px; color: var(--muted); }
    .sd-att-stats { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .sd-stat { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; background: #f7f9fc; border: 1px solid var(--line); }
    .sd-stat i { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .sd-stat strong { display: block; font-size: 20px; line-height: 1.1; }
    .sd-stat small { font-size: 11.5px; color: var(--muted); }
    .s-present i { background: var(--green); } .s-late i { background: var(--amber); }
    .s-absent i { background: var(--red); } .s-excused i { background: var(--sky); }

    /* ---------- Fees ---------- */
    .sd-fee-main { padding: 18px 20px; border-radius: 14px; margin-bottom: 14px; border: 1px solid var(--line); }
    .sd-fee-main.is-due { background: #fdf3f2; border-color: #f6d5d2; }
    .sd-fee-main.is-clear { background: #eef9f3; border-color: #cdeedd; }
    .sd-fee-main span { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
    .sd-fee-main strong { display: block; margin: 6px 0; font-size: 30px; letter-spacing: -.02em; }
    .sd-fee-main.is-due strong { color: var(--red); } .sd-fee-main.is-clear strong { color: var(--green); }
    .sd-fee-main small { font-size: 12px; font-weight: 600; }
    .sd-fee-split { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
    .sd-fee-split > div { padding: 14px 16px; }
    .sd-fee-split > div + div { border-left: 1px solid var(--line); }
    .sd-fee-split span { display: block; font-size: 11.5px; color: var(--muted); }
    .sd-fee-split strong { display: block; margin-top: 4px; font-size: 15px; }
    .sd-fee-progress { margin-top: 18px; }
    .sd-fee-progress > div:first-child { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 12px; color: var(--muted); }
    .sd-fee-progress strong { color: var(--ink); }

    /* ---------- Academic info ---------- */
    .sd-info { list-style: none; margin: 0; padding: 0; }
    .sd-info li { display: flex; align-items: center; gap: 14px; padding: 13px 0; border-top: 1px solid var(--line); }
    .sd-info li:first-child { border-top: 0; padding-top: 0; }
    .sd-info-icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; display: grid; place-items: center; background: #e8f1fd; color: var(--blue); }
    .sd-info small { display: block; font-size: 11.5px; color: var(--muted); }
    .sd-info strong { display: block; margin-top: 2px; font-size: 14px; }

    /* ---------- Lists ---------- */
    .sd-item { display: flex; gap: 13px; padding: 14px 0; border-top: 1px solid var(--line); }
    .sd-item:first-child { border-top: 0; padding-top: 0; }
    .sd-item.between { justify-content: space-between; align-items: center; }
    .sd-item-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .sd-item-icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; display: grid; place-items: center; }
    .sd-item-icon.amber { background: #fdf1d9; color: #b97d00; }
    .sd-item-icon.blue { background: #e8f1fd; color: var(--blue); }
    .sd-item-body h3 { font-size: 13.5px; font-weight: 600; margin-bottom: 3px; }
    .sd-item-body p { font-size: 12px; line-height: 1.5; color: var(--muted); margin-bottom: 5px; }
    .sd-item-body small { font-size: 11px; color: #8b96a8; }
    .sd-item-left strong { display: block; font-size: 13.5px; }
    .sd-item-left small { display: block; margin-top: 2px; font-size: 11.5px; color: var(--muted); }
    .sd-score { display: flex; align-items: center; gap: 10px; }
    .sd-score > strong { font-size: 13.5px; }
    .sd-grade { min-width: 30px; text-align: center; padding: 5px 8px; border-radius: 8px; font-size: 11.5px; font-weight: 700; }
    .g-a, .g-b { background: #e3f6ee; color: var(--green); }
    .g-c, .g-d { background: #fdf1d9; color: #a97500; }
    .g-e, .g-f { background: #fde8e7; color: var(--red); }

    /* ---------- Empty ---------- */
    .sd-empty { min-height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
    .sd-empty.compact { min-height: 140px; }
    .sd-empty i { font-size: 26px; margin-bottom: 10px; color: #b3bccb; }
    .sd-empty h3 { font-size: 15px; margin-bottom: 4px; }
    .sd-empty p { font-size: 12.5px; color: var(--muted); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1280px) {
        .sd-kpis { grid-template-columns: repeat(2, 1fr); }
        .sd-grid-3 { grid-template-columns: 1fr 1fr; }
        .sd-span { grid-column: span 2; }
    }
    @media (max-width: 960px) {
        .sd-grid-2, .sd-grid-3 { grid-template-columns: 1fr; }
        .sd-span { grid-column: auto; }
        .sd-term { width: 100%; }
    }
    @media (max-width: 640px) {
        .sd { padding: 18px 14px 90px; }
        .sd-hero { padding: 22px 20px; border-radius: 16px; }
        .sd-hero-main { align-items: flex-start; gap: 14px; }
        .sd-avatar { width: 56px; height: 56px; font-size: 21px; border-radius: 14px; }
        .sd-kpis { grid-template-columns: 1fr; }
        .sd-att { flex-direction: column; gap: 22px; }
        .sd-att-stats { width: 100%; }
        .sd-panel { padding: 20px; }
        .sd-panel-head { flex-direction: column; gap: 6px; }
    }
    @media (prefers-reduced-motion: reduce) { .sd-kpi { transition: none; } }
</style>
@endpush
@endsection